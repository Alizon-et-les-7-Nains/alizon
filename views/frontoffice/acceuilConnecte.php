<?php
require_once "../../controllers/pdo.php";
require_once "../../controllers/prix.php";
session_start();

// ============================================================================
// CONFIGURATION INITIALE
// ============================================================================

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../views/frontoffice/connexionClient.php');
    exit;
}

$idClient = $_SESSION['user_id'];

// ============================================================================
// FONCTIONS DE GESTION DU PANIER
// ============================================================================

function getCurrentCart($pdo, $idClient) {
    $stmt = $pdo->query("SELECT idPanier FROM _panier WHERE idClient = " . intval($idClient) . " ORDER BY idPanier DESC LIMIT 1");
    $panier = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : false;

    $cart = [];

    if ($panier) {
        $idPanier = intval($panier['idPanier']); 

        $sql = "SELECT p.idProduit, p.nom, p.prix, pa.quantiteProduit as qty, i.URL as img
                FROM _produitAuPanier pa
                JOIN _produit p ON pa.idProduit = p.idProduit
                LEFT JOIN _imageDeProduit i ON p.idProduit = i.idProduit
                WHERE pa.idPanier = " . intval($idPanier);
        $stmt = $pdo->query($sql);
        $cart = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
    }
    
    return $cart;
}

function updateQuantityInDatabase($pdo, $idClient, $idProduit, $delta) {
    $idProduit = intval($idProduit);
    $idClient = intval($idClient);

    $sql = "SELECT quantiteProduit FROM _produitAuPanier 
            WHERE idProduit = $idProduit AND idPanier IN (
                SELECT idPanier FROM _panier WHERE idClient = $idClient
            )";
    $stmt = $pdo->query($sql);
    $current = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : false;

    if ($current) {
        $newQty = max(0, intval($current['quantiteProduit']) + intval($delta));
        
        if ($newQty > 0) {
            $sql = "UPDATE _produitAuPanier SET quantiteProduit = $newQty 
                    WHERE idProduit = $idProduit AND idPanier IN (
                        SELECT idPanier FROM _panier WHERE idClient = $idClient
                    )";
            $res = $pdo->query($sql);
            $success = $res !== false;
        } else {
            $success = removeFromCartInDatabase($pdo, $idClient, $idProduit);
        }
        
        return $success;
    }
    return false;
}

function removeFromCartInDatabase($pdo, $idClient, $idProduit) {
    $idProduit = intval($idProduit);
    $idClient = intval($idClient);

    $sql = "DELETE FROM _produitAuPanier 
            WHERE idProduit = $idProduit AND idPanier IN (
                SELECT idPanier FROM _panier WHERE idClient = $idClient
            )";
    $res = $pdo->query($sql);
    return $res !== false;
}

function createOrderInDatabase($pdo, $idClient, $adresseLivraison, $villeLivraison, $regionLivraison, $numeroCarte, $codePostal = '', $nomCarte = 'Client inconnu', $dateExp = '12/30', $cvv = '000') {
    try {
        $pdo->beginTransaction();

        $idClient = intval($idClient);

        // Recupération du panier actuel
        $stmt = $pdo->query("SELECT * FROM _panier WHERE idClient = $idClient ORDER BY idPanier DESC LIMIT 1");
        $panier = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : false;
        if (!$panier) throw new Exception("Aucun panier trouvé pour ce client.");

        $idPanier = intval($panier['idPanier']);

        // Calcul total
        $sqlTotals = "
            SELECT SUM(p.prix * pap.quantiteProduit) AS sousTotal, SUM(pap.quantiteProduit) AS nbArticles
            FROM _produitAuPanier pap
            JOIN _produit p ON pap.idProduit = p.idProduit
            WHERE pap.idPanier = $idPanier
        ";
        $stmtTotals = $pdo->query($sqlTotals);
        $totals = $stmtTotals ? $stmtTotals->fetch(PDO::FETCH_ASSOC) : [];
        $sousTotal = floatval($totals['sousTotal'] ?? 0);
        $nbArticles = intval($totals['nbArticles'] ?? 0);

        // LES DONNÉES SONT DÉJÀ CHIFFRÉES DEPUIS LE FRONT - on les stocke directement
        $carteQ = $pdo->quote($numeroCarte); // Déjà chiffré
        $cvvQ = $pdo->quote($cvv); // Déjà chiffré

        // Verification existante carte (avec données chiffrées)
        $checkCarte = $pdo->query("SELECT numeroCarte FROM _carteBancaire WHERE numeroCarte = $carteQ");

        if ($checkCarte->rowCount() === 0) {
            $nomCarteQ = $pdo->quote($nomCarte);
            $dateExpQ = $pdo->quote($dateExp);
            $sqlInsertCarte = "
                INSERT INTO _carteBancaire (numeroCarte, nom, dateExpiration, cvv)
                VALUES ($carteQ, $nomCarteQ, $dateExpQ, $cvvQ)
            ";
            if ($pdo->query($sqlInsertCarte) === false) {
                throw new Exception("Erreur lors de l'ajout de la carte bancaire : " . implode(', ', $pdo->errorInfo()));
            }
        }

        // Création de l'adresse
        $adresseQ = $pdo->quote($adresseLivraison);
        $villeQ = $pdo->quote($villeLivraison);
        $regionQ = $pdo->quote($regionLivraison);
        $codePostalQ = $pdo->quote($codePostal);

        $sqlAdresse = "
            INSERT INTO _adresse (adresse, region, codePostal, ville, pays)
            VALUES ($adresseQ, $regionQ, $codePostalQ, $villeQ, 'France')
        ";
        if ($pdo->query($sqlAdresse) === false) {
            throw new Exception("Erreur lors de l'ajout de l'adresse : " . implode(', ', $pdo->errorInfo()));
        }
        $idAdresse = $pdo->lastInsertId();

        // Création de la commande
        $montantHT = $sousTotal;
        $montantTTC = $sousTotal * 1.20;

        $sqlCommande = "
            INSERT INTO _commande (
                dateCommande, etatLivraison, montantCommandeTTC, montantCommandeHt,
                quantiteCommande, nomTransporteur, dateExpedition,
                idAdresseLivr, idAdresseFact, numeroCarte, idPanier
            ) VALUES (
                NOW(), 'En préparation', $montantTTC, $montantHT,
                $nbArticles, 'Colissimo', NULL,
                $idAdresse, $idAdresse, $carteQ, $idPanier
            )
        ";
        if ($pdo->query($sqlCommande) === false) {
            throw new Exception("Erreur lors de la création de la commande : " . implode(', ', $pdo->errorInfo()));
        }

        $idCommande = $pdo->lastInsertId();

        // produits vers _contient
        $sqlContient = "
            INSERT INTO _contient (idProduit, idCommande, prixProduitHt, tauxTva, quantite)
            SELECT pap.idProduit, $idCommande, p.prix, COALESCE(t.pourcentageTva, 20.0), pap.quantiteProduit
            FROM _produitAuPanier pap
            JOIN _produit p ON pap.idProduit = p.idProduit
            LEFT JOIN _tva t ON p.typeTva = t.typeTva
            WHERE pap.idPanier = $idPanier
        ";
        if ($pdo->query($sqlContient) === false) {
            throw new Exception("Erreur lors de la copie des produits : " . implode(', ', $pdo->errorInfo()));
        }

        // Vider le panier
        if ($pdo->query("DELETE FROM _produitAuPanier WHERE idPanier = $idPanier") === false) {
            throw new Exception("Erreur lors du vidage du panier : " . implode(', ', $pdo->errorInfo()));
        }

        $pdo->commit();
        return $idCommande;

    } catch (Exception $e) {
        $pdo->rollBack();
        throw new Exception("Erreur lors de la création de la commande : " . $e->getMessage());
    }
}


// ============================================================================
// GESTION DES ACTIONS AJAX
// ============================================================================

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    try {
        switch ($_POST['action']) {
            case 'updateQty':
                $idProduit = $_POST['idProduit'] ?? '';
                $delta = intval($_POST['delta'] ?? 0);
                if ($idProduit && $delta != 0) {
                    $success = updateQuantityInDatabase($pdo, $idClient, $idProduit, $delta);
                    echo json_encode(['success' => $success]);
                } else {
                    echo json_encode(['success' => false, 'error' => 'Paramètres invalides']);
                }
                break;

            case 'removeItem':
                $idProduit = $_POST['idProduit'] ?? '';
                if ($idProduit) {
                    $success = removeFromCartInDatabase($pdo, $idClient, $idProduit);
                    echo json_encode(['success' => $success]);
                } else {
                    echo json_encode(['success' => false, 'error' => 'ID produit manquant']);
                }
                break;

            case 'getCart':
                $cart = getCurrentCart($pdo, $idClient);
                echo json_encode($cart);
                break;

            default:
                echo json_encode(['success' => false, 'error' => 'Action non reconnue']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

// ============================================================================
// RÉCUPÉRATION DES DONNÉES POUR LA PAGE
// ============================================================================

// recuperation panier courent
$cart = getCurrentCart($pdo, $idClient);

// ============================================================================
// AFFICHAGE DE LA PAGE
// ============================================================================
?>

<?php 
    const PRODUIT_CONSULTE_MAX_SIZE = 4;

    // Récupération du cookie existant ou création d'un tableau vide
    if (isset($_COOKIE['produitConsulte']) && !empty($_COOKIE['produitConsulte'])) {
        $tabIDProduitConsulte = unserialize($_COOKIE['produitConsulte']);
        if (!is_array($tabIDProduitConsulte)) {
            $tabIDProduitConsulte = [];
        }
    } else {
        $tabIDProduitConsulte = [];
    }

    // Fonction pour ajouter un produit consulté
    function ajouterProduitConsulter(&$tabIDProduit, $idProduitConsulte) {
        $key = array_search($idProduitConsulte, $tabIDProduit);
        if ($key !== false) {
            unset($tabIDProduit[$key]);
            $tabIDProduit = array_values($tabIDProduit);
        }
        
        if (count($tabIDProduit) >= PRODUIT_CONSULTE_MAX_SIZE) {
            array_shift($tabIDProduit);
        }
        
        $tabIDProduit[] = $idProduitConsulte;
        
        setcookie("produitConsulte", serialize($tabIDProduit), time() + (60*60*24*90), "/");
    }

    // Gestion de l'ajout d'un produit via GET
    if (isset($_GET['addRecent']) && !empty($_GET['addRecent'])) {
        $idProduitAjoute = intval($_GET['addRecent']);
        ajouterProduitConsulter($tabIDProduitConsulte, $idProduitAjoute);
        
        if (isset($_GET['id'])) {
            header("Location: produit.php?id=" . intval($_GET['id']));
            exit;
        }
    }

// ============================================================================
// AFFICHAGE DE LA PAGE
// ============================================================================
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../public/style.css">
    <title>Alizon - Accueil</title>
</head>

<body class="acceuil">
    <?php include '../../views/frontoffice/partials/headerConnecte.php'; ?>

    <section class="banniere">
        <h1>Plus de promotion à venir !</h1>
        <img src="../../public/images/defaultImageProduit.png" alt="Image de produit par défaut">
    </section>

    <main>
        <!-- SECTION NOUVEAUTÉS -->
        <section>
            <div class="nomCategorie">
                <h2>Nouveautés</h2>
                <hr>
            </div>
            <div class="listeArticle">
                <?php 
                $stmt = $pdo->prepare("SELECT * FROM _produit WHERE dateAjout >= DATE_SUB(NOW(), INTERVAL 2 WEEK)");
                $stmt->execute();
                $produitNouveaute = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                if (count($produitNouveaute) > 0) {
                    foreach ($produitNouveaute as $value) {
                        $idProduit = $value['idProduit'];
                        
                        $stmtImg = $pdo->prepare("SELECT URL FROM _imageDeProduit WHERE idProduit = :idProduit");
                        $stmtImg->execute([':idProduit' => $idProduit]);
                        $imageResult = $stmtImg->fetch(PDO::FETCH_ASSOC);
                        $image = !empty($imageResult) ? $imageResult['URL'] : '../../public/images/defaultImageProduit.png';
                        ?>
                <article style="margin-top: 5px;">
                    <img src="<?php echo htmlspecialchars($image); ?>" class="imgProduit"
                        onclick="window.location.href='?addRecent=<?php echo $idProduit; ?>&id=<?php echo $idProduit; ?>'"
                        alt="Image du produit">
                    <h2 class="nomProduit"
                        onclick="window.location.href='?addRecent=<?php echo $idProduit; ?>&id=<?php echo $idProduit; ?>'">
                        <?php echo htmlspecialchars($value['nom']); ?></h2>
                    <div class="notation">
                        <span><?php echo number_format($value['note'], 1); ?></span>
                        <?php for ($i=0; $i < number_format($value['note'], 0); $i++) { ?>
                        <img src="../../public/images/etoile.svg" alt="Note" class="etoile">
                        <?php } ?>
                    </div>
                    <div class="infoProd">
                        <div class="prix">
                            <h2><?php echo formatPrice($value['prix']); ?></h2>
                        </div>
                        <div>
                            <button class="plus" data-id="<?= htmlspecialchars($value['idProduit'] ?? '') ?>">
                                <img src="../../public/images/btnAjoutPanier.svg" alt="Bouton ajout panier">
                            </button>
                        </div>
                    </div>
                </article>
                <?php } 
                } else { ?>
                <h1>Aucun produit disponible</h1>
                <?php } ?>
            </div>
        </section>

        <!-- SECTION CHARCUTERIES -->
        <section>
            <div class="nomCategorie">
                <h2>Charcuteries</h2>
                <hr>
            </div>
            <div class="listeArticle">
                <?php 
                $stmt = $pdo->prepare("SELECT * FROM _produit WHERE typeProd = :typeProd");
                $stmt->execute([':typeProd' => 'charcuterie']);
                $produitCharcuterie = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                if (count($produitCharcuterie) > 0) {
                    foreach ($produitCharcuterie as $value) {
                        $idProduit = $value['idProduit'];
                        
                        $stmtImg = $pdo->prepare("SELECT URL FROM _imageDeProduit WHERE idProduit = :idProduit");
                        $stmtImg->execute([':idProduit' => $idProduit]);
                        $imageResult = $stmtImg->fetch(PDO::FETCH_ASSOC);
                        $image = !empty($imageResult) ? $imageResult['URL'] : '../../public/images/defaultImageProduit.png';
                        ?>
                <article style="margin-top: 5px;">
                    <img src="<?php echo htmlspecialchars($image); ?>" class="imgProduit"
                        onclick="window.location.href='?addRecent=<?php echo $idProduit; ?>&id=<?php echo $idProduit; ?>'"
                        alt="Image du produit">
                    <h2 class="nomProduit"
                        onclick="window.location.href='?addRecent=<?php echo $idProduit; ?>&id=<?php echo $idProduit; ?>'">
                        <?php echo htmlspecialchars($value['nom']); ?></h2>
                    <div class="notation">
                        <span><?php echo number_format($value['note'], 1); ?></span>
                        <?php for ($i=0; $i < number_format($value['note'], 0); $i++) { ?>
                        <img src="../../public/images/etoile.svg" alt="Note" class="etoile">
                        <?php } ?>
                    </div>
                    <div class="infoProd">
                        <div class="prix">
                            <h2><?php echo formatPrice($value['prix']); ?></h2>
                        </div>
                        <div>
                            <button class="plus" data-id="<?= htmlspecialchars($value['idProduit'] ?? '') ?>">
                                <img src="../../public/images/btnAjoutPanier.svg" alt="Bouton ajout panier">
                            </button>
                        </div>
                    </div>
                </article>
                <?php } 
                } else { ?>
                <h1>Aucun produit disponible</h1>
                <?php } ?>
            </div>
        </section>

        <!-- SECTION ALCOOLS -->
        <section>
            <div class="nomCategorie">
                <h2>Alcools</h2>
                <hr>
            </div>
            <div class="listeArticle">
                <?php 
                $stmt = $pdo->prepare("SELECT * FROM _produit WHERE typeProd = :typeProd");
                $stmt->execute([':typeProd' => 'alcools']);
                $produitAlcool = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                if (count($produitAlcool) > 0) {
                    foreach ($produitAlcool as $value) {
                        $idProduit = $value['idProduit'];
                        
                        $stmtImg = $pdo->prepare("SELECT URL FROM _imageDeProduit WHERE idProduit = :idProduit");
                        $stmtImg->execute([':idProduit' => $idProduit]);
                        $imageResult = $stmtImg->fetch(PDO::FETCH_ASSOC);
                        $image = !empty($imageResult) ? $imageResult['URL'] : '../../public/images/defaultImageProduit.png';
                        ?>
                <article style="margin-top: 5px;">
                    <img src="<?php echo htmlspecialchars($image); ?>" class="imgProduit"
                        onclick="window.location.href='?addRecent=<?php echo $idProduit; ?>&id=<?php echo $idProduit; ?>'"
                        alt="Image du produit">
                    <h2 class="nomProduit"
                        onclick="window.location.href='?addRecent=<?php echo $idProduit; ?>&id=<?php echo $idProduit; ?>'">
                        <?php echo htmlspecialchars($value['nom']); ?></h2>
                    <div class="notation">
                        <span><?php echo number_format($value['note'], 1); ?></span>
                        <?php for ($i=0; $i < number_format($value['note'], 0); $i++) { ?>
                        <img src="../../public/images/etoile.svg" alt="Note" class="etoile">
                        <?php } ?>
                    </div>
                    <div class="infoProd">
                        <div class="prix">
                            <h2><?php echo formatPrice($value['prix']); ?></h2>
                        </div>
                        <div>
                            <button class="plus" data-id="<?= htmlspecialchars($value['idProduit'] ?? '') ?>">
                                <img src="../../public/images/btnAjoutPanier.svg" alt="Bouton ajout panier">
                            </button>
                        </div>
                    </div>
                </article>
                <?php } 
                } else { ?>
                <h1>Aucun produit disponible</h1>
                <?php } ?>
            </div>
        </section>

        <!-- SECTION CONSULTES RECEMMENT -->
        <section>
            <div class="nomCategorie">
                <h2>Consultés récemment</h2>
                <hr>
            </div>
            <div class="listeArticle">
                <?php
                if (!empty($tabIDProduitConsulte) && count($tabIDProduitConsulte) > 0) {
                    $produitsRecents = array_reverse($tabIDProduitConsulte);
                    
                    foreach ($produitsRecents as $idProduitRecent) {
                        $stmtRecent = $pdo->prepare("SELECT * FROM _produit WHERE idProduit = :idProduit");
                        $stmtRecent->execute([':idProduit' => $idProduitRecent]);
                        $produitRecent = $stmtRecent->fetch(PDO::FETCH_ASSOC);
                        
                        if ($produitRecent) {
                            $idProduit = $produitRecent['idProduit'];
                            
                            $stmtImg = $pdo->prepare("SELECT URL FROM _imageDeProduit WHERE idProduit = :idProduit");
                            $stmtImg->execute([':idProduit' => $idProduit]);
                            $imageResult = $stmtImg->fetch(PDO::FETCH_ASSOC);
                            $image = !empty($imageResult) ? $imageResult['URL'] : '../../public/images/defaultImageProduit.png';
                            ?>
                <article style="margin-top: 5px;">
                    <img src="<?php echo htmlspecialchars($image); ?>" class="imgProduit"
                        onclick="window.location.href='?addRecent=<?php echo $idProduit; ?>&id=<?php echo $idProduit; ?>'"
                        alt="Image du produit">
                    <h2 class="nomProduit"
                        onclick="window.location.href='?addRecent=<?php echo $idProduit; ?>&id=<?php echo $idProduit; ?>'">
                        <?php echo htmlspecialchars($produitRecent['nom']); ?></h2>
                    <div class="notation">
                        <span><?php echo number_format($produitRecent['note'], 1); ?></span>
                        <?php for ($i=0; $i < number_format($produitRecent['note'], 0); $i++) { ?>
                        <img src="../../public/images/etoile.svg" alt="Note" class="etoile">
                        <?php } ?>
                    </div>
                    <div class="infoProd">
                        <div class="prix">
                            <h2><?php echo formatPrice($produitRecent['prix']); ?></h2>
                        </div>
                        <div>
                            <button class="plus" data-id="<?= htmlspecialchars($value['idProduit'] ?? '') ?>">
                                <img src="../../public/images/btnAjoutPanier.svg" alt="Bouton ajout panier">
                            </button>
                        </div>
                    </div>
                </article>
                <?php }
                    }
                } else { ?>
                <h1>Aucun produit récemment consultés !</h1>
                <?php } ?>
            </div>
        </section>
    </main>

    <!-- CARTE CONFIRMATION D'AJOUT / MODIFICATION DES QUANTITES -->
    <section class="confirmationAjout">
        <h4>Article ajouté au panier</h4>
            <article>
                <div class="imgProduit">
                    <?php 

                            $idProduit = $item['idProduit'] ?? 0;

                            $stmtImg = $pdo->prepare("SELECT URL FROM _imageDeProduit WHERE idProduit = :idProduit");
                            $stmtImg->execute([':idProduit' => $idProduit]);
                            $imageResult = $stmtImg->fetch(PDO::FETCH_ASSOC);
                            $image = !empty($imageResult) ? $imageResult['URL'] : '../../public/images/defaultImageProduit.png';    
                        ?>
                    <img src="<?= htmlspecialchars($image) ?>" alt="<?= htmlspecialchars($item['nom'] ?? '') ?>">
                </div>
                <div class="infoProduit">
                    <div>
                        <h2><?= htmlspecialchars($item['nom'] ?? 'N/A') ?></h2>
                        <h4>En stock</h4>
                    </div>
                    <div class="quantiteProduit">
                        <button class="minus popup-minus" data-id="<?= htmlspecialchars($item['idProduit'] ?? '') ?>">
                            <img src="../../public/images/minusDarkBlue.svg" alt="Symbole moins">
                        </button>
                        <p class="quantite"><?= htmlspecialchars($item['qty'] ?? 'N/A') ?></p>
                        <button class="plus popup-plus" data-id="<?= htmlspecialchars($item['idProduit'] ?? '') ?>">
                            <img src="../../public/images/plusDarkBlue.svg" alt="Symbole plus">
                        </button>
                    </div>
                </div>
                <div class="prixOpt">
                    <?= htmlspecialchars($item['prix'] ?? 'N/A') ?>
                </div>
            </article>
    </section>

    <?php include '../../views/frontoffice/partials/footerConnecte.php'; ?>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const popup = document.querySelector(".confirmationAjout");
            const popupPlus = document.querySelector(".confirmationAjout .popup-plus");
            const popupMinus = document.querySelector(".confirmationAjout .popup-minus");

            document.querySelectorAll("button.plus").forEach((btn) => {
                btn.addEventListener("click", function() {
                    const idProduit = this.getAttribute("data-id");

                    if (!idProduit) return;

                    if (popupPlus) popupPlus.setAttribute("data-id", idProduit);
                    if (popupMinus) popupMinus.setAttribute("data-id", idProduit);

                    if (popup) {
                    popup.style.display = "block"; 
                    setTimeout(function() {
                        popup.style.display = "none"; 
                    }, 5000);
                    }
                });
            });
        });
    </script>
    <script src="../scripts/frontoffice/paiement-ajax.js"></script>
    <script src="../../public/amd-shim.js"></script>
    <script src="../../public/script.js"></script>

</body>

</html>
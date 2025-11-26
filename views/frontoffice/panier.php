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


function getPrixProduitAvecRemise($pdo, $idProduit) {
    $sql = "SELECT 
            p.prix,
            remise.tauxRemise
           FROM _produit p 
           LEFT JOIN _remise remise ON p.idProduit = remise.idProduit 
                AND CURDATE() BETWEEN remise.debutRemise AND remise.finRemise
           WHERE p.idProduit = ?";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$idProduit]);
    $produit = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($produit && !empty($produit['tauxRemise'])) {
        return $produit['prix'] * (1 - $produit['tauxRemise']/100);
    }
    
    return $produit['prix'];
}

function getCurrentCart($pdo, $idClient) {
    $stmt = $pdo->query("SELECT idPanier FROM _panier WHERE idClient = " . intval($idClient) . " ORDER BY idPanier DESC LIMIT 1");
    $panier = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : false;

    $cart = [];

    if ($panier) {
        $idPanier = intval($panier['idPanier']); 

        $sql = "SELECT p.idProduit, p.stock, p.dateReassort, p.nom, p.prix, pa.quantiteProduit as qty, i.URL as img
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

        // Calcul total AVEC REMISES
        $sousTotal = 0;
        $nbArticles = 0;
        
        $sqlItems = "SELECT pap.idProduit, pap.quantiteProduit 
                     FROM _produitAuPanier pap 
                     WHERE pap.idPanier = $idPanier";
        $stmtItems = $pdo->query($sqlItems);
        $items = $stmtItems ? $stmtItems->fetchAll(PDO::FETCH_ASSOC) : [];
        
        foreach ($items as $item) {
            $prixProduit = getPrixProduitAvecRemise($pdo, $item['idProduit']);
            $quantite = $item['quantiteProduit'];
            $sousTotal += $prixProduit * $quantite;
            $nbArticles += $quantite;
        }

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

        // produits vers _contient AVEC PRIX REMISE
        $sqlContient = "
            INSERT INTO _contient (idProduit, idCommande, prixProduitHt, tauxTva, quantite)
            SELECT pap.idProduit, $idCommande, 
                   " . getPrixProduitAvecRemise($pdo, 'p.idProduit') . ", 
                   COALESCE(t.pourcentageTva, 20.0), 
                   pap.quantiteProduit
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
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../public/style.css">
    <link rel="icon" href="/public/images/logoBackoffice.svg">
    <title>Alizon - Votre panier</title>
</head>

<body class="panier">
    <?php include "../../views/frontoffice/partials/headerConnecte.php"; ?>

    <main>
        <section class="listeProduit">
            <?php foreach ($cart as $item) { 
                $prixAvecRemise = getPrixProduitAvecRemise($pdo, $item['idProduit']);
                $estEnRemise = $prixAvecRemise != $item['prix'];
            ?>
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
                        <h2><a style="text-decoration: none;" href="./produit.php?id=<?php echo $idProduit ?>"><?= htmlspecialchars($item['nom'] ?? 'N/A') ?></a></h2>
                        <?php 
                        if ($item['stock'] > 0) {
                            echo '<h4 class="stockDisponible">En stock</h4>';
                        } else {
                            if ($item['dateReassort'] !== null) {
                                echo '<p style="color: #259525;">Rupture de stock - Réapprovisionnement prévu le ' . htmlspecialchars($produit['dateReassort']) . '</p>';
                            } else {
                                echo '<p style="color: #ff4444;">Rupture de stock - Pas de réapprovisionnement prévu</p>';
                            }
                        } ?>
                    </div>
                    <div class="quantiteProduit">
                        <button class="minus" data-id="<?= htmlspecialchars($item['idProduit'] ?? '') ?>">
                            <img src="../../public/images/minusDarkBlue.svg" alt="Symbole moins">
                        </button>
                        <p class="quantite"><?= htmlspecialchars($item['qty'] ?? 'N/A') ?></p>
                        <button class="plus" data-id="<?= htmlspecialchars($item['idProduit'] ?? '') ?>">
                            <img src="../../public/images/plusDarkBlue.svg" alt="Symbole plus">
                        </button>
                    </div>
                </div>
                <div class="prixOpt">
                    <div>
                        <?php if ($estEnRemise): ?>
                            <p class="prix-remise">
                                <span class="prix-original" style="text-decoration: line-through; color: #999;">
                                    <?= formatPrice($item['prix']) ?>
                                </span>
                                <span class="prix-actuel" style="color: #ff4444; font-weight: bold;">
                                    <?= formatPrice($prixAvecRemise) ?>
                                </span>
                            </p>
                        <?php else: ?>
                            <p><?= formatPrice($item['prix']) ?></p>
                        <?php endif; ?>
                    </div>
                    <button class="delete" data-id="<?= htmlspecialchars($item['idProduit'] ?? '') ?>">
                        <img src="../../public/images/binDarkBlue.svg" alt="Enlever produit">
                    </button>
                </div>
            </article>
            <?php } if (empty($cart)) { ?>
            <h1 class="aucunProduit">Aucun produit</h1>
            <?php } else { ?>
        </section>
        <section class="recapPanier">
            <h1>Votre panier</h1>
            <div class="cardRecap">
                <article>
                    <?php  
                        $stmt = $pdo->query("SELECT idPanier FROM _panier WHERE idClient = $idClient ORDER BY idPanier DESC LIMIT 1");
                        $panier = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : false;
                        
                        if ($panier) {
                            $idPanier = intval($panier['idPanier']);
                            
                            $sqlItems = "SELECT pap.idProduit, pap.quantiteProduit as qty 
                                         FROM _produitAuPanier pap 
                                         WHERE pap.idPanier = $idPanier";
                            $stmtItems = $pdo->query($sqlItems);
                            $items = $stmtItems ? $stmtItems->fetchAll(PDO::FETCH_ASSOC) : [];
                            
                            $nbArticles = 0;
                            $prixHT = 0;
                            $prixTotalTvaPanier = 0;
                            $sousTotal = 0;
                            
                            foreach ($items as $item) {
                                $prixProduit = getPrixProduitAvecRemise($pdo, $item['idProduit']);
                                $quantite = $item['qty'];
                                
                                $sqlTva = "SELECT COALESCE(t.pourcentageTva, 20.0) as tva 
                                           FROM _produit p 
                                           LEFT JOIN _tva t ON p.typeTva = t.typeTva 
                                           WHERE p.idProduit = " . intval($item['idProduit']);
                                $stmtTva = $pdo->query($sqlTva);
                                $tvaResult = $stmtTva ? $stmtTva->fetch(PDO::FETCH_ASSOC) : [];
                                $tauxTva = $tvaResult['tva'] ?? 20.0;
                                
                                $nbArticles += $quantite;
                                $prixHT += $prixProduit * $quantite;
                                $prixTotalTvaPanier += ($prixProduit * $quantite * $tauxTva / 100);
                                $sousTotal += ($prixProduit * $quantite * (1 + $tauxTva / 100));
                            }
                        }
                    ?>

                    <h2><b>Récapitulatif de votre panier</b></h2>
                    <div class="infoCommande">
                        <section>
                            <h2>Nombres d'articles</h2>
                            <h2 class="val"><?= $nbArticles ?? 0 ?></h2>
                        </section>
                        <section>
                            <h2>Prix HT</h2>
                            <h2 class="val"><?= number_format($prixHT ?? 0, 2) ?>€</h2>
                        </section>
                        <section>
                            <h2>TVA</h2>
                            <h2 class="val"><?= number_format($prixTotalTvaPanier ?? 0, 2) ?>€</h2>
                        </section>
                        <section>
                            <h2>Total</h2>
                            <h2 class="val"><?= number_format($sousTotal ?? 0, 2) ?>€</h2>
                        </section>
                    </div>
                </article>
                <a href="../../views/frontoffice/pagePaiement.php">
                    <p>Passer la commande</p>
                </a>
            </div>
            <form method="POST" action="../../controllers/viderPanier.php">
                <button class="viderPanierCookie viderPanier" name="idUtilisateur" value="<?= htmlspecialchars($_SESSION['user_id'] ?? '') ?>">Vider le panier</button>
            </form>
        </section>
        <?php } ?>
    </main>

    <?php include "../../views/frontoffice/partials/footerConnecte.php"; ?>

    <script src="../scripts/frontoffice/paiement-ajax.js"></script>
    <script src="../../public/amd-shim.js"></script>
    <script src="../../public/script.js"></script>
</body>

</html>
<?php
require_once "../../controllers/pdo.php";
require_once "../../controllers/prix.php";
session_start();

ob_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../views/frontoffice/connexionClient.php');
    exit;
}

$idClient = $_SESSION['user_id'];

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
    
    // Récupérer le panier actuel
    $stmtPanier = $pdo->prepare("SELECT idPanier FROM _panier WHERE idClient = ? ORDER BY idPanier DESC LIMIT 1");
    $stmtPanier->execute([$idClient]);
    $panier = $stmtPanier->fetch(PDO::FETCH_ASSOC);
    
    if (!$panier) {
        // Créer un nouveau panier si nécessaire
        $stmtCreate = $pdo->prepare("INSERT INTO _panier (idClient) VALUES (?)");
        $stmtCreate->execute([$idClient]);
        $idPanier = $pdo->lastInsertId();
    } else {
        $idPanier = $panier['idPanier'];
    }

    // Vérifier si le produit existe déjà dans le panier
    $sql = "SELECT quantiteProduit FROM _produitAuPanier 
            WHERE idProduit = ? AND idPanier = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$idProduit, $idPanier]);
    $current = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($current) {
        // Produit existe : mettre à jour la quantité
        $newQty = max(0, intval($current['quantiteProduit']) + intval($delta));
        
        if ($newQty > 0) {
            $sql = "UPDATE _produitAuPanier SET quantiteProduit = ? 
                    WHERE idProduit = ? AND idPanier = ?";
            $stmt = $pdo->prepare($sql);
            $success = $stmt->execute([$newQty, $idProduit, $idPanier]);
        } else {
            // Supprimer si quantité = 0
            $sql = "DELETE FROM _produitAuPanier WHERE idProduit = ? AND idPanier = ?";
            $stmt = $pdo->prepare($sql);
            $success = $stmt->execute([$idProduit, $idPanier]);
        }
    } else {
        // Produit n'existe pas : l'ajouter si delta > 0
        if ($delta > 0) {
            $sql = "INSERT INTO _produitAuPanier (idProduit, idPanier, quantiteProduit) VALUES (?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $success = $stmt->execute([$idProduit, $idPanier, $delta]);
        } else {
            $success = false;
        }
    }
    
    return $success;

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
// GESTION DES COOKIES
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

    // Récupérer les promotions

    $stmt = $pdo->prepare("SELECT * FROM _promotion");
    $stmt->execute();
    $arrayProduit = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($arrayProduit) === 0) {
        $choixAleatoirePromo = "N/A";
    } else {
        $choixAleatoirePromo = $arrayProduit[array_rand($arrayProduit)]['idProduit'];
    }

    // Récupérer les promotions

    $stmt = $pdo->prepare("SELECT * FROM _promotion");
    $stmt->execute();
    $arrayProduit = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($arrayProduit) === 0) {
        $choixAleatoirePromo = "N/A";
    } else {
        $choixAleatoirePromo = $arrayProduit[array_rand($arrayProduit)]['idProduit'];
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

    <link rel="icon" type="image/png" href="/public/images/favicon.png">
    <link rel="icon" href="/public/images/logoBackoffice.svg">

    <title>Alizon - Accueil</title>
</head>

<body class="acceuil">
    <?php include '../../views/frontoffice/partials/headerConnecte.php'; ?>

    <section class="banniere">
        <?php if($choixAleatoirePromo == "N/A") { ?>
            <h1>Plus de promotion à venir !</h1>
            <img src="../../public/images/defaultImageProduit.png" alt="Image de produit par défaut">
        <?php } else { 
                     
            $cheminSysteme = "/var/www/html/images/baniere/" . $choixAleatoirePromo . ".jpg";

            if (file_exists($cheminSysteme)) {
                $image = "/images/baniere/" . $choixAleatoirePromo . ".jpg";
            } else {
                $stmtImg = $pdo->prepare("SELECT URL FROM _imageDeProduit WHERE idProduit = :idProduit");
                $stmtImg->execute([':idProduit' => $choixAleatoirePromo]);
                $imageResult = $stmtImg->fetch(PDO::FETCH_ASSOC);
                $image = !empty($imageResult) ? $imageResult['URL'] : '../../public/images/defaultImageProduit.png';
            }

            $stmt = $pdo->prepare("SELECT * FROM _produit WHERE idProduit = :idProduit");
            $stmt->execute([':idProduit' => $choixAleatoirePromo]);
            $produitEnPromo = $stmt->fetch(PDO::FETCH_ASSOC);
            ?>
            
            <h1 style="cursor: pointer;" onclick="window.location.href='?addRecent=<?php echo $choixAleatoirePromo; ?>&id=<?php echo $choixAleatoirePromo; ?>'"><?php echo htmlspecialchars($produitEnPromo['nom']); ?></h1>
            <img style="cursor: pointer;" onclick="window.location.href='?addRecent=<?php echo $choixAleatoirePromo; ?>&id=<?php echo $choixAleatoirePromo; ?>'" src="<?php echo htmlspecialchars($image); ?>" alt="Image du produit">

        <?php } ?>
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
                $stmt = $pdo->prepare("SELECT * FROM _produit ORDER BY idProduit DESC LIMIT 10;");
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
                            <?php 
                                $prix = $value['prix'];
                                $poids = $value['poids'];
                                $prixAuKg = $prix/$poids;
                                $prixAuKg = round($prixAuKg,2) ?>
                            <h4 style="margin: 0;"><?php echo htmlspecialchars($prixAuKg); ?>€ / kg</h4>
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
                            <?php 
                                $prix = $value['prix'];
                                $poids = $value['poids'];
                                $prixAuKg = $prix/$poids;
                                $prixAuKg = round($prixAuKg,2) ?>
                            <h4 style="margin: 0;"><?php echo htmlspecialchars($prixAuKg); ?>€ / kg</h4>
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
                            <?php 
                                $prix = $value['prix'];
                                $poids = $value['poids'];
                                $prixAuKg = $prix/$poids;
                                $prixAuKg = round($prixAuKg,2) ?>
                            <h4 style="margin: 0;"><?php echo htmlspecialchars($prixAuKg); ?>€ / kg</h4>
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
                            <?php 
                                $prix = $produitRecent['prix'];
                                $poids = $produitRecent['poids'];
                                $prixAuKg = $prix/$poids;
                                $prixAuKg = round($prixAuKg,2) ?>
                            <h4 style="margin: 0;"><?php echo htmlspecialchars($prixAuKg); ?>€ / kg</h4>
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

    <section class="confirmationAjout">
        <h4>Produit ajouté au panier !</h4>
    </section>
    
    <?php include '../../views/frontoffice/partials/footerConnecte.php'; ?>

    <script>
        const popupConfirmation = document.querySelector(".confirmationAjout");
        const boutonsAjout = document.querySelectorAll(".plus");

        boutonsAjout.forEach(btn => {
            btn.addEventListener("click", function(e) {

                // Afficher le popup
                popupConfirmation.style.display = "block";
                console.log("Clique bouton ajouter panier");

                // Cacher après 1,5 secondes
                setTimeout(() => {
                    popupConfirmation.style.display = "none";
                }, 5000);
            });
        });
    </script>

    <script src="../scripts/frontoffice/paiement-ajax.js"></script>
    <script src="../../public/amd-shim.js"></script>
    <script src="../../public/script.js"></script>

</body>

</html>

<?php
ob_end_flush();
?>

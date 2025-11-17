<?php
require_once "../../controllers/pdo.php";
require_once "../../controllers/prix.php";
require_once "../../controllers/pdo.php";

ob_start();

// ============================================================================
// CONFIGURATION INITIALE
// ============================================================================

// ID utilisateur connecté (à remplacer par la gestion de session)
$idClient = 2; 

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
        }
        
        return $success;
    }
    return false;
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
                        <article style="margin-top: 5px;" onclick="window.location.href='?addRecent=<?php echo $idProduit; ?>&id=<?php echo $idProduit; ?>'">
                            <img src="<?php echo htmlspecialchars($image); ?>" class="imgProduit" alt="Image du produit">
                            <h2 class="nomProduit"><?php echo htmlspecialchars($value['nom']); ?></h2>
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
                                    <a href="" onclick="event.stopPropagation();" class="btnAjoutPanier plus" data-id="<?= htmlspecialchars($value['idProduit'] ?? '') ?>">
                                        <img src="../../public/images/btnAjoutPanier.svg" alt="Bouton ajout panier">
                                    </a>
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
                        <article style="margin-top: 5px;" onclick="window.location.href='?addRecent=<?php echo $idProduit; ?>&id=<?php echo $idProduit; ?>'">
                            <img src="<?php echo htmlspecialchars($image); ?>" class="imgProduit" alt="Image du produit">
                            <h2 class="nomProduit"><?php echo htmlspecialchars($value['nom']); ?></h2>
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
                                    <a href="" onclick="event.stopPropagation();" class="btnAjoutPanier plus" data-id="<?= htmlspecialchars($value['idProduit'] ?? '') ?>">
                                        <img src="../../public/images/btnAjoutPanier.svg" alt="Bouton ajout panier">
                                    </a>
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
                        <article style="margin-top: 5px;" onclick="window.location.href='?addRecent=<?php echo $idProduit; ?>&id=<?php echo $idProduit; ?>'">
                            <img src="<?php echo htmlspecialchars($image); ?>" class="imgProduit" alt="Image du produit">
                            <h2 class="nomProduit"><?php echo htmlspecialchars($value['nom']); ?></h2>
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
                                    <a href="" onclick="event.stopPropagation();" class="btnAjoutPanier plus" data-id="<?= htmlspecialchars($value['idProduit'] ?? '') ?>">
                                        <img src="../../public/images/btnAjoutPanier.svg" alt="Bouton ajout panier">
                                    </a>
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
                            <article style="margin-top: 5px;" onclick="window.location.href='?addRecent=<?php echo $idProduit; ?>&id=<?php echo $idProduit; ?>'">
                                <img src="<?php echo htmlspecialchars($image); ?>" class="imgProduit" alt="Image du produit">
                                <h2 class="nomProduit"><?php echo htmlspecialchars($produitRecent['nom']); ?></h2>
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
                                        <a href="" onclick="event.stopPropagation();" class="btnAjoutPanier plus" data-id="<?= htmlspecialchars($value['idProduit'] ?? '') ?>">
                                            <img src="../../public/images/btnAjoutPanier.svg" alt="Bouton ajout panier">
                                        </a>
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

    <?php include '../../views/frontoffice/partials/footerConnecte.php'; ?>

    <script>
        // Ajouts au panier des produitsRecents
        const btnAjout = document.querySelectorAll('.btnAjoutPanier');

        btnAjout.forEach(btn => {
            btn.addEventListener('click', (event) => {
                event.preventDefault();
                alert("Produit ajouté au panier !");
            });
        });
    </script>
</body>
</html>

<?php

ob_end_flush();
?>

<?php
ob_start();

require_once "../../controllers/pdo.php";
require_once "../../controllers/prix.php";

    const PRODUIT_CONSULTE_MAX_SIZE = 4;

    const PRODUIT_DANS_PANIER_MAX_SIZE = 10;

    // Récupération du cookie existant ou création d'un tableau vide
    if (((isset($_COOKIE['produitConsulte'])) && (isset($_COOKIE['produitPanier']))) && (!empty($_COOKIE['produitConsulte']) && !empty($_COOKIE['produitPanier']))) {
        $tabIDProduitConsulte = unserialize($_COOKIE['produitConsulte']);
        $tabIDProduitPanier = unserialize($_COOKIE['produitPanier']);
        if (!is_array($tabIDProduitConsulte)) {
            $tabIDProduitConsulte = [];
            $tabIDProduitPanier = [];
        }
    } else {
        $tabIDProduitConsulte = [];
        $tabIDProduitPanier = [];
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

    function ajouterProduitPanier(&$tabIDProduitPanier, $idProduit, $quantite = 1) {
        if (isset($tabIDProduitPanier[$idProduit])) {
            $tabIDProduitPanier[$idProduit] += $quantite;
        } else {
            if (count($tabIDProduitPanier) >= PRODUIT_DANS_PANIER_MAX_SIZE) {
                $message = "Impossible d'ajouter plus de ".PRODUIT_DANS_PANIER_MAX_SIZE." produits différents. Connectez-vous pour en ajouter plus.";
                echo "<script>alert(".json_encode($message).");</script>";
                return false;
            }
            $tabIDProduitPanier[$idProduit] = $quantite;
        }
        
        setcookie("produitPanier", serialize($tabIDProduitPanier), time() + (60*60*24*90), "/");
        return true;
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

    if (isset($_GET['addPanier']) && !empty($_GET['addPanier'])) {
        $idProduitAjoute = intval($_GET['addPanier']);
        $quantite = isset($_GET['qty']) ? intval($_GET['qty']) : 1;
        ajouterProduitPanier($tabIDProduitPanier, $idProduitAjoute);
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
  <link rel="icon" href="/public/images/logoBackoffice.svg">
  <link rel="stylesheet" href="../../public/style.css">
  <title>Alizon - Accueil</title>
</head>
<body class="acceuil">
    <?php include '../../views/frontoffice/partials/headerDeconnecte.php'; ?>

    <section class="banniere">
        <?php if($choixAleatoirePromo == "N/A") { ?>
            <h1>Plus de promotion à venir !</h1>
            <img src="../../public/images/defaultImageProduit.png" alt="Image de produit par défaut">
        <?php } else { 
                     
            $cheminSysteme = "/docker/data/web/html/images/baniere/" . $choixAleatoirePromo . ".jpg";

            if (file_exists($cheminSysteme)) {
                $image = "../../public/images/baniere/" . $choixAleatoirePromo . ".jpg";
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
            
            <h1><?php echo htmlspecialchars($produitEnPromo['nom']); ?></h1>
            <img src="<?php echo htmlspecialchars($image); ?>" alt="Image du produit">

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
                            <img src="<?php echo htmlspecialchars($image); ?>" class="imgProduit" onclick="window.location.href='?addRecent=<?php echo $idProduit; ?>&id=<?php echo $idProduit; ?>'" alt="Image du produit">
                            <h2 class="nomProduit" onclick="window.location.href='?addRecent=<?php echo $idProduit; ?>&id=<?php echo $idProduit; ?>'"><?php echo htmlspecialchars($value['nom']); ?></h2>
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
                                    <button class="plus" onclick="window.location.href='?addPanier=<?php echo $idProduit; ?>&id=<?php echo $idProduit; ?>'">
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
                            <img src="<?php echo htmlspecialchars($image); ?>" class="imgProduit" onclick="window.location.href='?addRecent=<?php echo $idProduit; ?>&id=<?php echo $idProduit; ?>'" alt="Image du produit">
                            <h2 class="nomProduit" onclick="window.location.href='?addRecent=<?php echo $idProduit; ?>&id=<?php echo $idProduit; ?>'"><?php echo htmlspecialchars($value['nom']); ?></h2>
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
                                    <button class="plus" onclick="window.location.href='?addPanier=<?php echo $idProduit; ?>&id=<?php echo $idProduit; ?>'">
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
                            <img src="<?php echo htmlspecialchars($image); ?>" class="imgProduit" onclick="window.location.href='?addRecent=<?php echo $idProduit; ?>&id=<?php echo $idProduit; ?>'" alt="Image du produit">
                            <h2 class="nomProduit" onclick="window.location.href='?addRecent=<?php echo $idProduit; ?>&id=<?php echo $idProduit; ?>'"><?php echo htmlspecialchars($value['nom']); ?></h2>
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
                                    <button class="plus" onclick="window.location.href='?addPanier=<?php echo $idProduit; ?>&id=<?php echo $idProduit; ?>'">
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
                                <img src="<?php echo htmlspecialchars($image); ?>" class="imgProduit" onclick="window.location.href='?addRecent=<?php echo $idProduit; ?>&id=<?php echo $idProduit; ?>'" alt="Image du produit">
                                <h2 class="nomProduit" onclick="window.location.href='?addRecent=<?php echo $idProduit; ?>&id=<?php echo $idProduit; ?>'"><?php echo htmlspecialchars($produitRecent['nom']); ?></h2>
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
                                        <button class="plus" onclick="window.location.href='?addPanier=<?php echo $idProduit; ?>&id=<?php echo $idProduit; ?>'">
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

    <?php include '../../views/frontoffice/partials/footerDeconnecte.php'; ?>

    <script src="../scripts/frontoffice/paiement-ajax.js"></script>
    <script src="../../public/amd-shim.js"></script>
    <script src="../../public/script.js"></script>

</body>
</html>

<?php
ob_end_flush();
?>

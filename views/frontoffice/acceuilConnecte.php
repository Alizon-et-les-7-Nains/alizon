<?php require_once "../../controllers/prix.php" ?>
<?php require_once "../../controllers/pdo.php" ?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="../../public/style.css">
  <title>Alizon - Acceuil</title>
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
                        <article style="margin-top: 5px;" onclick="window.location.href='produit.php?id=<?php echo $idProduit; ?>'">
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
                                    <a href="" onclick="event.stopPropagation();"><img src="../../public/images/btnAjoutPanier.svg" alt="Bouton ajout panier"></a>
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
                        <article style="margin-top: 5px;" onclick="window.location.href='produit.php?id=<?php echo $idProduit; ?>'">
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
                                    <a href="" onclick="event.stopPropagation();"><img src="../../public/images/btnAjoutPanier.svg" alt="Bouton ajout panier"></a>
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
                        <article style="margin-top: 5px;" onclick="window.location.href='produit.php?id=<?php echo $idProduit; ?>'">
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
                                    <a href="" onclick="event.stopPropagation();"><img src="../../public/images/btnAjoutPanier.svg" alt="Bouton ajout panier"></a>
                                </div>
                            </div>
                        </article>
                    <?php } 
                } else { ?>
                    <h1>Aucun produit disponible</h1>
                <?php } ?>
            </div>
        </section>


        <!-- SECTION CONSULTÉS RÉCEMMENT -->
        <section>
            <div class="nomCategorie">
                <h2>Consultés récemment</h2>
                <hr>
            </div>
            <div class="listeArticle">
                <?php
                // TODO: Implémenter la logique des produits récemment consultés
                // Pour l'instant, on affiche un message par défaut
                $hasRecentProducts = false;
                
                if (!$hasRecentProducts) { ?>
                    <h1>Aucun produit récemment consultés !</h1>
                <?php } ?>
            </div>
        </section>
    </main>

    <?php include '../../views/frontoffice/partials/footerConnecte.php'; ?>
</body>
</html>
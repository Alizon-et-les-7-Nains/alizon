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
        <section>
            <div class="nomCategorie">
                <h2>Nouveautés</h2>
                <hr>
            </div>
            <div class="listeArticle">
                <?php 
                $stmt = $pdo->prepare("SELECT * FROM _produit WHERE dateAjout >= NOW() - INTERVAL 1 WEEK");
                $stmt->execute();
                $produitNouveaute = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                foreach ($produitNouveaute as $value) {
                    $idProduit = $value['idProduit'];
                    
                    $stmtImg = $pdo->prepare("SELECT URL FROM _imageDeProduit WHERE idProduit = :idProduit");
                    $stmtImg->execute([':idProduit' => $idProduit]);
                    $imageResult = $stmtImg->fetch(PDO::FETCH_ASSOC);
                    $image = !empty($imageResult) ? $imageResult['URL'] : '../../public/images/defaultImageProduit.png';
                    ?>
                    <article onclick="window.location.href='produit.php?id=<?php echo $idProduit; ?>'">
                        <img src="<?php echo htmlspecialchars($image); ?>" class="imgProduit" alt="Image du produit">
                        <h2><?php echo htmlspecialchars($value['nom']); ?></h2>
                        <div class="notation">
                            <img src="../../public/images/etoile.svg" alt="Note" class="etoile">
                            <span><?php echo number_format($value['note'], 1); ?></span>
                        </div>
                        <div class="infoProd">
                            <div class="prix">
                                <h2><?php echo formatPrice($value['prix']); ?> €</h2>
                            </div>
                            <div>
                                <a href="" onclick="event.stopPropagation();"><img src="../../public/images/btnAjoutPanier.svg" alt="Bouton ajout panier"></a>
                            </div>
                        </div>
                    </article>
                <?php } ?>
            </div>
        </section>

        <section>
            <div class="nomCategorie">
                <h2>Charcuteries</h2>
                <hr>
            </div>
            <div class="listeArticle">
                <?php 
                $stmt = $pdo->prepare("SELECT * FROM _produit WHERE typeProd = :typeProd");
                $stmt->execute([':typeProd' => 'alimentaire']);
                $produitCharcuterie = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                foreach ($produitCharcuterie as $value) {
                    $idProduit = $value['idProduit'];
                    
                    $stmtImg = $pdo->prepare("SELECT URL FROM _imageDeProduit WHERE idProduit = :idProduit");
                    $stmtImg->execute([':idProduit' => $idProduit]);
                    $imageResult = $stmtImg->fetch(PDO::FETCH_ASSOC);
                    $image = !empty($imageResult) ? $imageResult['URL'] : '../../public/images/defaultImageProduit.png';
                    ?>
                    <article onclick="window.location.href='produit.php?id=<?php echo $idProduit; ?>'">
                        <img src="<?php echo htmlspecialchars($image); ?>" class="imgProduit" alt="Image du produit">
                        <h2><?php echo htmlspecialchars($value['nom']); ?></h2>
                        <div class="notation">
                            <img src="../../public/images/etoile.svg" alt="Note" class="etoile">
                            <span><?php echo number_format($value['note'], 1); ?></span>
                        </div>
                        <div class="infoProd">
                            <div class="prix">
                                <h2><?php echo formatPrice($value['prix']); ?> €</h2>
                            </div>
                            <div>
                                <a href="" onclick="event.stopPropagation();"><img src="../../public/images/btnAjoutPanier.svg" alt="Bouton ajout panier"></a>
                            </div>
                        </div>
                    </article>
                <?php } ?>
            </div>
        </section>

        <section>
            <div class="nomCategorie">
                <h2>Alcools</h2>
                <hr>
            </div>
            <div class="listeArticle">
                <?php 
                $stmt = $pdo->prepare("SELECT * FROM _produit WHERE typeProd = :typeProd");
                $stmt->execute([':typeProd' => 'boisson']);
                $produitAlcool = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                foreach ($produitAlcool as $value) {
                    $idProduit = $value['idProduit'];
                    
                    $stmtImg = $pdo->prepare("SELECT URL FROM _imageDeProduit WHERE idProduit = :idProduit");
                    $stmtImg->execute([':idProduit' => $idProduit]);
                    $imageResult = $stmtImg->fetch(PDO::FETCH_ASSOC);
                    $image = !empty($imageResult) ? $imageResult['URL'] : '../../public/images/defaultImageProduit.png';
                    ?>
                    <article onclick="window.location.href='produit.php?id=<?php echo $idProduit; ?>'">
                        <img src="<?php echo htmlspecialchars($image); ?>" class="imgProduit" alt="Image du produit">
                        <h2><?php echo htmlspecialchars($value['nom']); ?></h2>
                        <div class="notation">
                            <img src="../../public/images/etoile.svg" alt="Note" class="etoile">
                            <span><?php echo number_format($value['note'], 1); ?></span>
                        </div>
                        <div class="infoProd">
                            <div class="prix">
                                <h2><?php echo formatPrice($value['prix']); ?> €</h2>
                            </div>
                            <div>
                                <a href="" onclick="event.stopPropagation();"><img src="../../public/images/btnAjoutPanier.svg" alt="Bouton ajout panier"></a>
                            </div>
                        </div>
                    </article>
                <?php } ?>
            </div>
        </section>

        <section>
            <div class="nomCategorie">
                <h2>Consultés récemment</h2>
                <hr>
            </div>
            <div class="listeArticle">
                <?php
                $hasRecentProducts = false;
                if (!$hasRecentProducts) { ?>
                    <h1>Aucun produit récemment consultés !</h1>
                <?php } ?>
            </div>
        </section>
    </main>

    <?php include '../../views/frontoffice/partials/footerDeconnecte.php'; ?>
</body>
</html>

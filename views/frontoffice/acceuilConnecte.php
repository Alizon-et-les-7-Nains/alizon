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
                <?php for ($i=0 ; $i < 0 ; $i++) { ?>
                    <article>
                        <img src="../../public/images/defaultImageProduitCard.png" class="imgProduit" alt="Image du produit">
                        <h2>Assortiment de rillettes de thon la compagne bretonne - 300g</h2>
                        <div class="infoProd">
                            <div class="prix">
                                <h2>29.99€</h2>
                                <h3>99.72 € / Kg</h3>
                            </div>
                            <div>
                                <a href=""><img src="../../public/images/btnAjoutPanier.svg" alt="Bouton ajout panier"></a>
                            </div>
                        </div>
                    </article>
                <?php } if ($i==0) { ?>
                    <h1>Aucun produit disponible pour le moment !</h1>
                <?php } ?>
            </div>
        </section>

        <section>
            <div class="nomCategorie">
                <h2>Charcuteries</h2>
                <hr>
            </div>
            <div class="listeArticle">
                <?php for ($i=0 ; $i < 0 ; $i++) { ?>
                    <article>
                        <img src="../../public/images/defaultImageProduitCard.png" class="imgProduit" alt="Image du produit">
                        <h2>Assortiment de rillettes de thon la compagne bretonne - 300g</h2>
                        <div class="infoProd">
                            <div class="prix">
                                <h2>29.99€</h2>
                                <h3>99.72 € / Kg</h3>
                            </div>
                            <div>
                                <a href=""><img src="../../public/images/btnAjoutPanier.svg" alt="Bouton ajout panier"></a>
                            </div>
                        </div>
                    </article>
                <?php } if ($i==0) { ?>
                    <h1>Aucun produit disponible pour le moment !</h1>
                <?php } ?>
            </div>
        </section>

        <section>
            <div class="nomCategorie">
                <h2>Alcools</h2>
                <hr>
            </div>
            <div class="listeArticle">
                <?php for ($i=0 ; $i < 0 ; $i++) { ?>
                    <article>
                        <img src="../../public/images/defaultImageProduitCard.png" class="imgProduit" alt="Image du produit">
                        <h2>Assortiment de rillettes de thon la compagne bretonne - 300g</h2>
                        <div class="infoProd">
                            <div class="prix">
                                <h2>29.99€</h2>
                                <h3>99.72 € / Kg</h3>
                            </div>
                            <div>
                                <a href=""><img src="../../public/images/btnAjoutPanier.svg" alt="Bouton ajout panier"></a>
                            </div>
                        </div>
                    </article>
                <?php } if ($i==0) { ?>
                    <h1>Aucun produit disponible pour le moment !</h1>
                <?php } ?>
            </div>
        </section>

        <section>
            <div class="nomCategorie">
                <h2>Consultés récemment</h2>
                <hr>
            </div>
            <div class="listeArticle">
                <?php for ($i=0 ; $i < 0 ; $i++) { ?>
                    <article>
                        <img src="../../public/images/defaultImageProduitCard.png" class="imgProduit" alt="Image du produit">
                        <h2>Assortiment de rillettes de thon la compagne bretonne - 300g</h2>
                        <div class="infoProd">
                            <div class="prix">
                                <h2>29.99€</h2>
                                <h3>99.72 € / Kg</h3>
                            </div>
                            <div>
                                <a href=""><img src="../../public/images/btnAjoutPanier.svg" alt="Bouton ajout panier"></a>
                            </div>
                        </div>
                    </article>
                <?php } if ($i==0) { ?>
                    <h1>Aucun produit récemment consultés !</h1>
                <?php } ?>
            </div>
        </section>
    </main>

    <?php include '../../views/frontoffice/partials/footerDeconnecte.php'; ?>
</body>
</html>
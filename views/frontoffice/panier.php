<?php 
require_once "../../controllers/prix.php";
require_once "../../controllers/pdo.php";
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="../../public/style.css">
  <title>Alizon - Panier</title>
</head>
<body class="panier">
    <?php include '../../views/frontoffice/partials/headerConnecte.php'; ?>

    <main>
        <section class="listeProduit">
            <?php for ($i = 0; $i < 4; $i++) { ?> 
                <article>
                    <div class="imgProduit">
                        <input type="checkbox" name="selectionProduit" id="selectionProduit">
                        <img src="../../public/images/defaultImageProduitCard.png" alt="Image du produit">
                    </div>
                    <div class="infoProduit">
                        <div>
                            <h2>Lot de rilettes bretonne</h2>
                            <h4>En stock</h4>
                        </div>
                        <div class="quantiteProduit">
                            <img class="btnMinus" src="../../public/images/minusDarkBlue.svg" alt="Symbole moins" style="cursor: pointer;"> 
                            <p class="quantite">0</p> 
                            <img class="btnPlus" src="../../public/images/plusDarkBlue.svg" alt="Symbole plus" style="cursor: pointer;"> 
                        </div>
                    </div>
                    <div class="prixOpt">
                        <h2><b>29.99€</b></h2>
                        <img src="../../public/images/binDarkBlue.svg" alt="Enlever produit" class="btnPoubelle" style="cursor: pointer;">
                    </div>
                </article>
            <?php } if ($i==0) { ?>
                <h1 class="aucunProduit">Aucun produit</h1>
            <?php } else { ?>
        </section>
        <section class="recapPanier">
            <h1>Votre panier</h1>
            <div class="cardRecap">
                <article>
                    <h2><b>Récapitulatif de votre panier</b></h2>
                    <div class="infoCommande">
                        <section>
                            <h2>Nombres d'articles</h2>
                            <h2 class="val">0</h2>
                        </section>
                        <section>
                            <h2>Prix HT</h2>
                            <h2 class="val">0€</h2>
                        </section>
                        <section>
                            <h2>TVA</h2>
                            <h2 class="val">0€</h2>
                        </section>
                        <section>
                            <h2>Total</h2>
                            <h2 class="val">0€</h2>
                        </section>
                    </div>
                </article>
                <a href=""><p>Passer la commande</p></a>
            </div>
            <a href="" class="viderPanier">Vider le panier</a>
        </section>
        <?php } ?>
    </main>

    <?php include "../../views/frontoffice/partials/footerConnecte.php"; ?>

    <script>
        const btnPlus = document.querySelectorAll('.btnPlus');
        const btnMinus = document.querySelectorAll('.btnMinus');
        const btnPoubelle = document.querySelectorAll('.btnPoubelle');

        btnPlus.forEach(btn => {
            btn.addEventListener('click', function() {
                const quantiteElement = this.parentElement.querySelector('.quantite');
                let quantite = parseInt(quantiteElement.textContent);
                quantite++;
                quantiteElement.textContent = quantite;
            });
        });

        btnMinus.forEach(btn => {
            btn.addEventListener('click', function() {
                const quantiteElement = this.parentElement.querySelector('.quantite');
                let quantite = parseInt(quantiteElement.textContent);
                if (quantite > 0) {
                    quantite--;
                    quantiteElement.textContent = quantite;
                }
            });
        });

        btnPoubelle.forEach(btn => {
            btn.addEventListener('click', function() {
                // Remonter à l'article parent puis chercher la quantité
                const article = this.closest('article');
                const quantiteElement = article.querySelector('.quantite');
                quantiteElement.textContent = '0';
            });
        });
    </script>
</body>
</html>
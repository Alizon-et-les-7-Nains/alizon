<!DOCTYPE html>
<html lang="en">
<head>
    <!-- sass --watch views/styles/main.scss:public/style.css -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page du produit</title>
    <link rel="stylesheet" href="../../public/style.css">
</head>
<body class="pageProduit">
<header>
<?php include "../../views/frontoffice/partials/headerConnecte.php" ?>
</header>
<main>
<section class="infoHautProduit">
    <article class="rectangleProduit">
        <img src="../../public/images/flecheGauche.svg" alt="">
        <div>
            <img id="imageBouteille" src="../../public/images/Image_bouteille.svg" alt="">
            <div id="lesCercles">
                <div class="cercleNoir"></div>
                <div class="cercleNoir"></div>
                <div class="cercleNoir"></div>
            </div>
        </div>
        <img src="../../public/images/flecheDroite.svg" alt="">
    </article>
    <article class="infoPreviewProduit">
        <h1>Cidre coco d'issé swdxqs wdcqswx df dfdf</h1>
        <div id="prix">
            <h1>29.99€</h1>
            <h3>40.99€</h3>
        </div>
        <h2>Description de l'article :</h2>
        <p id="descriptionCourte">Lorem ipsum dolor sit amet consectetur adipisicing elit. Ducimus enim iure ratione voluptates
            eius doloremque obcaecati dignissimos ea porro exercitationem ex omnis reiciendis neque explicabo,
            libero quidem placeat, accusantium sit.</p>
        <p class="voirPlus">Voir plus sur le produit</p>
        <div class="version">
            <h3>Version :</h3>
            <p>50cl</p>
            <p>1L</p>
            <p>1.5L</p>
        </div>
        <h3>Choisir un type de produit :</h3>
        <div>
            <img src="../../public/images/Image_bouteille.svg" alt="">
            <img src="../../public/images/Image_bouteille.svg" alt="">
            <img src="../../public/images/Image_bouteille.svg" alt="">
            <img src="../../public/images/Image_bouteille.svg" alt="">
        </div>
    </article>
    <article class="actionsProduit">
    <h2>Vendu par Loïc Raison</h2>
    <p class="underline" id="plusDarticles"><a href="">Plus d'article de Loïc Raison</a></p>
    <br>
    <hr>
    <div class="ligneActions">
        <img src="../../../images/camion.png" alt="">
        <p>Livraison <b>GRATUITE</b> - Expédié par <b>mondial relais</b>. Arrivée entre le <b>mar. 21 septembre - ven. 24 septembre</b></p>
    </div>
    <div class="ligneActions">
        <img src="../../../images/emplacement.png" alt="">
        <p>Livré a <u><b>Clermont-ferrand 63000</b>, 10 place saint-michel</u></p>   
    </div>
    <div class="ligneActions">
        <img src="../../../images/tec.png" alt="">
        <p>Consulter les <b><u>conditions générales de vente</u></b></p>
    </div>
    <hr>
    <br>
        <div class="bouton" id="quantite">
            <p>Quantité</p>
            <div>
                <img src="../../public/images/moins.svg" alt="" id="moins">
                <p>1</p>
                <img src="../../public/images/plus.svg" alt="" id="plus">
            </div>  
        </div>
        <button class="bouton">Ajouter au panier</button>
        <button class="bouton">Acheter maintenant</button>
</article>
</section>
<hr>
<section class="informations">
    <input type="checkbox" id="activeVoirPlus">
    <div class="conteneurTexte">
        <h2>Plus d'informations sur l'article</h2>
        <article>
            <h3>📌 Titre du produit</h3>
            <p>Cidre brut artisanal – 75 cl – Fermentation naturelle</p>
        </article>
        <article>
            <h3>📝 Description courte</h3>
            <p>Cidre brut traditionnel, issu de pommes récoltées en Bretagne, fermenté naturellement, goût fruité et légèrement acidulé.</p>
        </article>
        <article>
            <h3>📂 Rubriques de la fiche produit</h3>
            <div>
                <h4>Caractéristiques principales :</h4>
                <ul>
                    <li><p>Type : Cidre brut</p></li>
                    <li><p>Contenance : 75 cl</p></li>
                    <li><p>Alcool : 5 % vol.</p></li>
                    <li><p>Origine : Bretagne, France</p></li>
                    <li><p>Fabrication : fermentation naturelle, sans additif chimique</p></li>
                </ul>
            </div>
            <div>
                <h4>Notes de dégustation :</h4>    
                <ul>
                    <li>Robe dorée et pétillante</li>
                    <li>Arômes fruités de pomme fraîche</li>
                    <li>Légère acidité équilibrée par une pointe de douceur</li>
                    <li>Fines bulles, rafraîchissant en bouche</li>
                </ul>  
            </div>
            <div>
                <h4>Notes de dégustation :</h4>    
                <ul>
                    <li>Robe dorée et pétillante</li>
                    <li>Arômes fruités de pomme fraîche</li>
                    <li>Légère acidité équilibrée par une pointe de douceur</li>
                    <li>Fines bulles, rafraîchissant en bouche</li>
                </ul>  
            </div>
            <div>
                <h4>Notes de dégustation :</h4>    
                <ul>
                    <li>Robe dorée et pétillante</li>
                    <li>Arômes fruités de pomme fraîche</li>
                    <li>Légère acidité équilibrée par une pointe de douceur</li>
                    <li>Fines bulles, rafraîchissant en bouche</li>
                </ul>  
            </div>
            <div>
                <h4>Notes de dégustation :</h4>    
                <ul>
                    <li>Robe dorée et pétillante</li>
                    <li>Arômes fruités de pomme fraîche</li>
                    <li>Légère acidité équilibrée par une pointe de douceur</li>
                    <li>Fines bulles, rafraîchissant en bouche</li>
                </ul>  
            </div>
        </article>
    </div> 
    <label for="activeVoirPlus" class="voirPlus"> </label> 
</section>
<hr>
</main>
<footer>
    <?php include '../../views/frontoffice/partials/footerConnecte.php'; ?>
</footer> 
</body>
</html>
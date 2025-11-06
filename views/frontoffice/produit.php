<!DOCTYPE html>
<html lang="en">
<head>
    <!-- sass --watch views/styles/main.scss:public/style/css -->
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
        <img src="flecheGauche.svg" alt="">
        <div>
            <img id="imageBouteille" src="../../public/images/Image_bouteille.svg" alt="">
            <div id="lesCercles">
                <div class="cercleNoir"></div>
                <div class="cercleNoir"></div>
                <div class="cercleNoir"></div>
            </div>
        </div>
        <img src="flecheDroite.svg" alt="">
    </article>
    <article class="infoPreviewProduit">
        <h1>Cidre coco d'issé</h1>
        <div id="prix">
            <h2>29.99€</h2>
            <h3>40.99€</h3>
        </div>
        <h2>Description de l'article :</h2>
        <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Ducimus enim iure ratione voluptates
            eius doloremque obcaecati dignissimos ea porro exercitationem ex omnis reiciendis neque explicabo,
            libero quidem placeat, accusantium sit.</p>
        <p>Voir plus sur le produit</p>
        <div>
            <h3>Version :</h3>
            <p>50cl</p>
            <p>1L</p>
            <p>1.5L</p>
        </div>
        <h3>Choisir un type de produit</h3>
        <div>
            <img src="../../public/images/Image_bouteille.svg" alt="">
            <img src="../../public/images/Image_bouteille.svg" alt="">
            <img src="../../public/images/Image_bouteille.svg" alt="">
            <img src="../../public/images/Image_bouteille.svg" alt="">
        </div>
    </article>
    <article class="actionsProduit">
        <h2>Vendu par Loïc Raison</h2>
        <p>Plus d'article de Loïc Raison</p>
        <hr>
        <img src="localisation.svg" alt="">
        <p>Livré a Clermont-ferrand 63000, 10 place saint-michel</p>
        <img src="Conditions.svg" alt="">
        <p>Consulter les conditions générales de vente</p>
        <hr>
        <div>
            <div>
                <h3>Quantité</h3>
                <div>
                    <img src="moins.svg" alt="">
                    <h3>1</h3>
                    <img src="plus.svg" alt="">
                </div>  
            </div>
            <button>Ajouter au panier</button>
            <button>Acheter maintenant</button>
        </div>
    </article>
</section>
<hr>
<section>
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
                <li>Type : Cidre brut</li>
                <li>Contenance : 75 cl</li>
                <li>Alcool : 5 % vol.</li>
                <li>Origine : Bretagne, France</li>
                <li>Fabrication : fermentation naturelle, sans additif chimique</li>
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
            <h4>Accords mets & boissons :</h4>
            <ul>
                <li>Type : Cidre brut</li>
                <li>Contenance : 75 cl</li>
                <li>Alcool : 5 % vol.</li>
                <li>Origine : Bretagne, France</li>
                <li>Fabrication : fermentation naturelle, sans additif chimique</li>
            </ul> 
        </div>
        <div>
            <h4>Informations logistiques :</h4> 
            <ul>
                <li>Idéal avec crêpes, galettes de sarrasin, fromages affinés</li>
                <li>Se consomme frais, entre 8 et 10 °C</li>
            </ul> 
        </div>
    </article>
</section>
<h3 class="VoirPlus" >Voir plus sur le produit</h3>
<hr>
</main>
<footer>
    <?php include '../../views/frontoffice/partials/footerConnecte.php'; ?>
</footer>
</html>
<?php
// Connexion simple à la base
// try {
//     $pdo = new PDO("mysql:host=localhost;dbname=saedb;charset=utf8mb4", "username", "password", [
//     PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, 
//     PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
// ]);

// } catch(PDOException $e) {
//     die("Erreur connexion : " . $e->getMessage());
// }

// // Récupérer l'ID depuis l'URL
// $productId = intval($_GET['id']) ?? 0;

// if($productId == 0) {
//     die("Produit non spécifié");
// }

// // REQUÊTE SIMPLE : Récupérer le produit ET le vendeur en une fois

// $sqlProduit = "SELECT 
//                 p.idProduit,
//                 p.nom AS nom_produit,        // ← Alias clair
//                 p.description, 
//                 p.prix,
//                 p.stock,
//                 v.prenom AS prenom_vendeur,  // ← Alias clair  
//                 v.nom AS nom_vendeur,        // ← Alias clair
//                 v.raisonSocial
//                FROM _produit p 
//                JOIN _vendeur v ON p.idVendeur = v.codeVendeur 
//                WHERE p.idProduit = $productId";

// $result = $pdo->query($sqlProduit);
// $produit = $result->fetch(PDO::FETCH_ASSOC);

// if (!$produit) {
//     echo "<p>Produit introuvable.</p>";
//     exit;
// }

// // Récupérer les images
// $sqlImages = "SELECT i.* 
//               FROM _image i
//               JOIN _imageDeProduit ip ON i.URL = ip.URL
//               WHERE ip.idProduit = $productId";

// $resultImages = $pdo->query($sqlImages);
// $images = $resultImages->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <!-- sass --watch views/styles/main.scss:public/style.css -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($produit['nom_produit'])?></title>
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
            <img id="imageBouteille"
            src="../../../images/<?php echo htmlspecialchars($images[0]['url']); ?>"alt="Pas d'image trouvée">
            <div id="lesCercles">
                <div class="cercleNoir"></div>
                <div class="cercleNoir"></div>
                <div class="cercleNoir"></div>
            </div>
        </div>
        <img src="../../public/images/flecheDroite.svg" alt="">
    </article>
    <article class="infoPreviewProduit">
        <h1><?php echo htmlspecialchars($produit['nom_produit']); ?></h1>
        <div id="prix">
            <h1><?php echo number_format($produit['prix'], 2, ',', ' '); ?>€</h1>
            <h3>40.99€</h3>
        </div>
        <h2>Description de l'article :</h2>
        <p></p>
        <!-- <p id="descriptionCourte">Lorem ipsum dolor sit amet consectetur adipisicing elit. Ducimus enim iure ratione voluptates
            eius doloremque obcaecati dignissimos ea porro exercitationem ex omnis reiciendis neque explicabo,
            libero quidem placeat, accusantium sit.</p> -->
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
    <h2>Vendu par <?php echo htmlspecialchars($produit['prenom_vendeur'] . ' ' . $produit['nom_vendeur']); ?></h2>
    <p class="underline" id="plusDarticles"><a href="">Plus d'article de Loïc Raison</a></p>
    <br>
    <hr>
    <div class="ligneActions">
        <img src="../../../images/camion.png" alt="">
        <p>Livraison <b>GRATUITE</b> - Expédié par <b>mondial relais</b>. Arrivée entre le <b>mar. 21 septembre - ven. 24 septembre</b></p>
    </div>
    <div class="ligneActions">
        <img src="../../../images/emplacement.png" alt="">
        <p>Livré a <a href=""><b>Clermont-ferrand 63000</b>, 10 place saint-michel</a></p>   
    </div>
    <div class="ligneActions">
        <img src="../../../images/tec.png" alt="">
        <p>Consulter les <b><a href="">conditions générales de vente</a></b></p>
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
        <?php 
        echo htmlspecialchars($produit['description']);
        ?>
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
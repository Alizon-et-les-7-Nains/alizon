<?php 
// Initialisation de la connexion avec le serveur / BDD
include "../../controllers/pdo.php";

// Récupération de toutes les catégories de produits distinctes pour le menu de navigation
$query = $pdo->prepare("SELECT DISTINCT typeProd FROM _produit p WHERE typeProd IS NOT NULL;");
$query->execute();
$listeCategories = $query->fetchAll(PDO::FETCH_ASSOC);

?>

<!-- Header pour utilisateurs non connectés - Version simplifiée -->
<header class="headerFront">

    <!-- Partie principale du header : logo, barre de recherche, icônes -->
    <div class="headerMain">
        <!-- Logo et nom de la marque Alizon -->
        <div class="logoNom">
            <a href="../frontoffice/accueilDeconnecte.php"><img src="../../../public/images/logoAlizonHeader.png" alt="Logo Alizon"></a>
            <h1><a href="../frontoffice/accueilDeconnecte.php"><b>Alizon</b></a></h1>
        </div>

        <!-- Barre de recherche de produits -->
        <div class="searchBar">
            <div class="search-wrapper">
                <i id="validerRecherche" class="bi bi-search"></i>
                <input type="search" name="recherche" id="searchbar" placeholder="Rechercher">
            </div>
        </div>

        <!-- Icônes d'accès rapide : panier et menu burger -->
        <div class="icons">
            <a href="../frontoffice/panierDeconnecte.php"><img src="../../../public/images/cartLightBlue.svg" alt=""></a>
            <a href="javascript:void(0);" onclick="menuBurger();"><img src="../../../public/images/burgerLightBlue.svg" alt=""></a>
        </div>
    </div>

    <!-- Carrousel horizontal des catégories de produits -->
    <div class="carousel">
        <div class="group">
            <?php 
                // Affichage dynamique de toutes les catégories (sans liens actifs pour utilisateurs non connectés) 
                foreach ($listeCategories as $categorie) { ?>
                    <a class="categorie" style="cursor: pointer;"><?php echo $categorie['typeProd']; ?></a>
            <?php } ?>
        </div>
    </div>

    <!-- Menu burger simplifié : panier et connexion uniquement -->
    <section id="burgerIcon">
        <div id="triangle-codeHeader"></div>
        <a href="../frontoffice/panierDeconnecte.php">Mon panier</a>
        <a href="../frontoffice/connexionClient.php">Connexion</a>
    </section>

</header>

<!-- Script pour gérer l'affichage du menu burger -->
<script>
// Fonction pour afficher/masquer le menu burger
function menuBurger() {
    var burgerIcon = document.getElementById("burgerIcon");
    burgerIcon.style.display = (burgerIcon.style.display === "flex") ? "none" : "flex";
}
</script>

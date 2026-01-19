<?php 
// Initialisation de la connexion avec le serveur / BDD
include "../../controllers/pdo.php";

// Récupération de toutes les catégories de produits distinctes pour le menu de navigation
$query = $pdo->prepare("SELECT DISTINCT typeProd FROM _produit p WHERE typeProd IS NOT NULL;");
$query->execute();
$listeCategories = $query->fetchAll(PDO::FETCH_ASSOC);

?>

<!-- Header pour utilisateurs connectés avec barre de recherche et navigation -->
<header class="headerFront">

    <!-- Partie principale du header : logo, barre de recherche, icônes -->
    <div class="headerMain">
        <!-- Logo et nom de la marque Alizon -->
        <div class="logoNom">
            <a href="../frontoffice/accueilConnecte.php"><img src="../../../public/images/logoAlizonHeader.png" alt="Logo Alizon"></a>
            <h1><a href="../frontoffice/accueilConnecte.php" class="alizonMarque"><b>Alizon</b></a></h1>
        </div>
        <!-- Barre de recherche de produits -->
        <div class="searchBar">
            <div class="search-wrapper">
                <input type="search" name="recherche" id="searchbar" placeholder="Rechercher">
                <img id ="recherche" src="../../../public/images/searchDarkBlue.svg" alt="">
            </div>
        </div>
        <!-- Icônes d'accès rapide : panier et menu burger -->
        <div class="icons">
            <a href="../frontoffice/panier.php"><img src="../../../public/images/cartLightBlue.svg" alt=""></a>
            <a href="javascript:void(0);" onclick="menuBurger();"><img src="../../../public/images/burgerLightBlue.svg" alt="Burger"></a>
        </div>
    </div>
    <!-- Carrousel horizontal des catégories de produits -->
    <div class="carousel">
        <div class="group">
            <?php 
                // Affichage dynamique de toutes les catégories récupérées 
                foreach ($listeCategories as $categorie) { 
                    // Remplacement des espaces par des underscores pour l'URL
                    $nomCat = str_replace(" ", "_", $categorie['typeProd']);
                    ?>
                    <a class="categorie" style="cursor: pointer;" href="http://10.253.5.104/views/frontoffice/catalogue.php?categorie=<?= $nomCat ?>"><?php echo $categorie['typeProd']; ?></a>
            <?php } ?>
        </div>
    </div>


    <!-- Menu burger déroulant avec liens de navigation utilisateur -->
    <section id="burgerIcon">
        <div id="triangle-codeHeader"></div>
        <a href="../frontoffice/compteClient.php">Mon compte</a>
        <a href="../frontoffice/commandes.php">Mes commandes</a>
        <a href="../frontoffice/notifications.php">Mes notifications</a>
        <a href="../frontoffice/mesAvis.php">Mes commentaires</a>
        <a href="../frontoffice/legalesConnecte.php" class="separation">Mentions légales</a>
        <a href="../../../controllers/deconnexion.php">Déconnexion</a>
    </section>

</header>

<!-- Scripts pour gérer les interactions du header -->
<script>
// Fonction pour afficher/masquer le menu burger
function menuBurger() {
    var burgerIcon = document.getElementById("burgerIcon");
    burgerIcon.style.display = (burgerIcon.style.display === "flex") ? "none" : "flex";
}

// Gestion de la recherche via l'icône loupe
const loupe = document.getElementById('recherche');
const searchbar = document.getElementById('searchbar');

// Redirection vers la page catalogue avec le terme de recherche
loupe.addEventListener('click', () => {
    const recherche = searchbar.value;
    window.location.href = `catalogue.php?search=${encodeURIComponent(recherche)}`;
});
</script>
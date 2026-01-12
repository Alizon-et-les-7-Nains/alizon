
<header class="headerFront">

    <div class="headerMain">
        <div class="logoNom">
            <img src="../../../public/images/logoAlizonHeader.png" alt="Logo Alizon">
            <h1><a href="../frontoffice/accueilConnecte.php" class="alizonMarque"><b>Alizon</b></a></h1>
        </div>

        <div class="searchBar">
            <div class="search-wrapper">
                <i id="validerRecherche" class="bi bi-search"></i>
                <input type="search" name="recherche" id="searchbar" placeholder="Rechercher">
                <img id ="recherche" src="../../../public/images/searchDarkBlue.svg" alt="">
            </div>
        </div>

        <div class="icons">
            <a href="../frontoffice/panier.php"><img src="../../../public/images/cartLightBlue.svg" alt=""></a>
            <a href="javascript:void(0);" onclick="menuBurger();"><img src="../../../public/images/burgerLightBlue.svg" alt="Burger"></a>
        </div>
    </div>

    <div class="carousel">
        <div class="group">
            <?php 
                $categorie = ($pdo->query("SELECT * FROM _categorie"))->fetchAll(PDO::FETCH_ASSOC);
                foreach ($categorie as $value) { ?>
                    <a class="categorie"><?php echo $value['nomCategorie']; ?></a>
            <?php } ?>
        </div>
    </div>


    <section id="burgerIcon">
        <div id="triangle-codeHeader"></div>
        <a href="../frontoffice/compteClient.php">Mon compte</a>
        <a href="../frontoffice/commandes.php">Mes commandes</a>
        <a href="../frontoffice/mesAvis.php">Mes commentaires</a>
        <a href="../frontoffice/panier.php">Mon panier</a>
        <a href="../../../controllers/deconnexion.php">Déconnexion</a>
    </section>

</header>

<script>
function menuBurger() {
    var burgerIcon = document.getElementById("burgerIcon");
    burgerIcon.style.display = (burgerIcon.style.display === "flex") ? "none" : "flex";
}

const loupe = document.getElementById('recherche');
const searchbar = document.getElementById('searchbar');

loupe.addEventListener('click', () => {
    const recherche = searchbar.value;
    window.location.href = `catalogue.php?search=${encodeURIComponent(recherche)}`;
});
</script>
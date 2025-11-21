<header class="headerFront">

    <div class="headerMain">
        <div class="logoNom">
            <img src="../../../public/images/logoAlizonHeader.png" alt="Logo Alizon">
            <h1><a href="../frontoffice/accueilConnecte.php"><b>Alizon</b></a></h1>
        </div>

        <div class="searchBar">
            <div class="search-wrapper">
                <i id="validerRecherche" class="bi bi-search"></i>
                <input type="search" name="recherche" id="searchbar" placeholder="Rechercher">
            </div>
        </div>

        <div class="icons">
            <a href="../frontoffice/notification.php"><img src="../../../public/images/bellLightBlue.svg" alt=""></a>
            <a href="../frontoffice/panier.php"><img src="../../../public/images/cartLightBlue.svg" alt=""></a>
            <a href="javascript:void(0);" onclick="menuBurger();"><img src="../../../public/images/burgerLightBlue.svg" alt=""></a>
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
        <a href="../frontoffice/commentaires.php">Mes commentaires</a>
        <a href="../frontoffice/listeDeSouhait.php">Liste de souhait</a>
        <a href="../frontoffice/commandes.php">Mes commandes</a>
        <a href="../frontoffice/panier.php">Mon panier</a>
        <a href="../frontoffice/connexionClient.php">Déconnexion</a>
    </section>

</header>

<script>
function menuBurger() {
    var burgerIcon = document.getElementById("burgerIcon");
    burgerIcon.style.display = (burgerIcon.style.display === "flex") ? "none" : "flex";
}
</script>

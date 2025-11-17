<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../../public/style.css">
    <title>header</title>
</head>

<body>
    <header class="headerFront">

        <div class="headerMain">
            <div class="logoNom">
                <img src="../../../public/images/logoAlizonHeader.png" alt="Logo Alizon">
                <h1><a href="../frontoffice/acceuilConnecte.php"><b>Alizon</b></a></h1>
            </div>
        <div class="searchBar">

        <div class="searchBar">
            <div class="search-wrapper">
                <i class="bi bi-search"></i>
                <input type="search" name="recherche" id="searchbar" placeholder="Rechercher">
            </div>
        </div>

        </div>
            <div class="icons">
                <a href="../notification.php"><img src="../../../public/images/bellLightBlue.svg" alt=""></a>
                <a href="../panier.php"><img src="../../../public/images/cartLightBlue.svg" alt=""></a>
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
            <a href="../compteClient.php">Mon compte</a>
            <a href="../commentaires.php">Mes commentaires</a>
            <a href="../listeDeSouhait.php">Liste de souhait</a>
            <a href="../commandes.php">Mes commandes</a>
            <a href="../panier.php">Mon panier</a>
            <a href="../connexionClient.php">Déconnexion</a>
        </section>

    </header>

    <script>
        function menuBurger() {
            var burgerIcon = document.getElementById("burgerIcon");
            if (burgerIcon.style.display === "flex") {
                burgerIcon.style.display = "none";
            } else {
                burgerIcon.style.display = "flex";
            }
        }
    </script>

</body>

</html>
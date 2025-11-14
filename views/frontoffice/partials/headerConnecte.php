<!DOCTYPE html>
<html lang="en">

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

<<<<<<< HEAD
                <img src="../../../public/images/logoAlizonHeader.png" alt="Logo Alizon">
                <h1><a href="../public/acceuil.php"><b>Alizon</b></a></h1>
=======
        <img src="../../../public/images/logoAlizonHeader.png" alt="Logo Alizon">
        <h1><a href="../frontoffice/acceuilConnecte.php"><b>Alizon</b></a></h1>
>>>>>>> origin/main

            </div>
            <div class="searchBar">

<<<<<<< HEAD
                <div class="searchBar">
                    <div class="search-wrapper">
                        <i class="bi bi-search"></i>
                        <input type="search" name="recherche" id="searchbar" placeholder="Rechercher">
                    </div>
                </div>

            </div>
            <div class="icons">
                <a href=""><img src="../../../public/images/bellLightBlue.svg" alt=""></a>
                <a href=""><img src="../../../public/images/cartLightBlue.svg" alt=""></a>
                <a href=""><img src="../../../public/images/burgerLightBlue.svg" alt=""></a>
            </div>
        </div>
=======
      </div>
      <div class="icons">
        <a href="../frontoffice/notification.php""><img src="../../../public/images/bellLightBlue.svg" alt=""></a>
        <a href="../frontoffice/panier.php""><img src="../../../public/images/cartLightBlue.svg" alt=""></a>
        <a href=""><img src="../../../public/images/burgerLightBlue.svg" alt=""></a>
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
>>>>>>> origin/main

        <div class="carousel">
            <div class="group">
                <?php for ($i=0 ; $i < 15 ; $i++) { ?>
                <a class="categorie">Categorie</a>
                <?php } ?>
            </div>
        </div>

    </header>
</body>

</html>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">  
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../../public/style/styleTemplate.css">
    <title>header</title>
</head>
<body>
    <header>
        <div class="headerMain">
        <div class="logoNom">

            <img src="../public/images/logoAlizonHeader.png" alt="Logo Alizon">
            <h1><a href="./acceuil.php"><b>Alizon</b></a></h1>

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
            <a href=""><img src="../public/images/bellLightBlue.svg" alt=""></a>
            <a href=""><img src="../public/images/cartLightBlue.svg" alt=""></a>
            <a href=""><img src="../public/images/burgerLightBlue.svg" alt=""></a>
        </div>
        </div>

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
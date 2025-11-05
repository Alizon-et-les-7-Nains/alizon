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

        <img src="../../../public/images/logoAlizonHeader.png" alt="Logo Alizon">
        <h1><a href="../public/acceuil.php"><b>Alizon</b></a></h1>

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
        <div class="seConnecter">
            <a href=""><img src="../../../public/images/utilLightBlue.svg" alt=""></a>
            <p>Se connecter</p>
        </div>
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
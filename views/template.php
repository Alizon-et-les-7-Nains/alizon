<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">  
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="../public/css/styleTemplate.css">
  <title>Alizon - Acceuil</title>
</head>
<body>

  <!-- -------------------------------------------- DEBUT HEADER -------------------------------------------- -->
  <header>

    <div class="headerMain">
      <div class="logoNom">

        <img src="./images/logoAlizonHeader.png" alt="Logo Alizon">
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
        <a href=""><img src="./images/bellLightBlue.svg" alt=""></a>
        <a href=""><img src="./images/cartLightBlue.svg" alt=""></a>
        <a href=""><img src="./images/burgerLightBlue.svg" alt=""></a>
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
  <!-- -------------------------------------------- FIN HEADER -------------------------------------------- -->

  <!-- -------------------------------------------- DEBUT FOOTER -------------------------------------------- -->
  <footer>
    <div class="footerPC">
      <div>
        <a href="">Conditions générales de vente</a>
        <a href="">Mentions légales</a>
        <p>© 2025 Alizon Tous droits réservés.</p>
      </div>
      <i class="bi bi-envelope fs-2"></i>
    </div>
    <div class="footerTel">
      <a href=""><img src="./images/homeLightBlue.svg" alt="" class="homeLightBlue"></a>
      <a href=""><img src="./images/searchLightBlue.svg" alt="" class="searchLightBlue"></a>
      <a href=""><img src="./images/cartLightBlue.svg" alt="" class="cartLightBlue"></a>
      <a href=""><img src="./images/burgerLightBlue.svg" alt="" class="burgerLightBlue"></a>
    </div>
  </footer>
  <!-- -------------------------------------------- FIN FOOTER -------------------------------------------- -->

</body>
</html>
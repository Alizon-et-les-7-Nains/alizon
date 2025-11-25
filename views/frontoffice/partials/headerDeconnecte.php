
    <header class="headerFront">
    
        <div class="headerMain">
        <a href="../accueilDeonnecte.php">
            <div class="logoNom">
                <img src="../../../public/images/logoAlizonHeader.png" alt="Logo Alizon">
                <h1><a href="../frontoffice/accueilDeconnecte.php"><b>Alizon</b></a></h1>
            </div>
        </a>
        <div class="searchBar">

        <div class="searchBar">
            <div class="search-wrapper">
                <i class="bi bi-search"></i>
                <input type="search" name="recherche" id="searchbar" placeholder="Rechercher">
            </div>
        </div>

        </div>
            <div class="icons">
                <a href="../frontoffice/panierDeconnecte.php"><img src="../../../public/images/cartLightBlue.svg" alt=""></a>
                <div class="seConnecter">
                    <a href="../frontoffice/connexionClient.php"><img src="../../../public/images/utilLightBlue.svg" alt=""></a>
                    <p>Se connecter</p>
                </div>
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

    </header>

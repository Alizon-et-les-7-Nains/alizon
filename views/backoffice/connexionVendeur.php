<?php
    $currentPage = basename(__FILE__);
    require_once "../../controllers/pdo.php"; 

    // Récupération des messages d'erreur s'il y en a (comme sur ta page inscription)
    $message = $_SESSION['message'] ?? ''; 
    unset($_SESSION['message']);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../public/style.css">
    <title>Connexion à un compte vendeur</title>
</head>
<?php
        require_once "./partials/header.php";
?>
<body>
    <main class="connexionVendeur">
        <img class="triskiel" src="../../public/images/triskiel gris.svg" alt="">

        <div class="pdp_title">
            <img src="../../public/images/pdp_user.svg" alt="photo de profil">
            <h1>Connexion à votre compte vendeur Alizon</h1>
        </div>

        <div class="information_connexion container">
            <form method="post" class="form-vendeur" id="monForm" action="../../controllers/connexionCompteVendeur.php">
                
                <?php if (!empty($message)) : ?>
                    <div class="alert alert-danger text-center" role="alert">
                        <?= $message ?>
                    </div>
                <?php endif; ?>

                <div class="row g-3 justify-content-center">
                    <div class="col-md-8 col-lg-6">
                        <input type="email" name="email" placeholder="Adresse E-Mail" required class="form-control">
                    </div>

                    <div class="w-100"></div> <div class="col-md-8 col-lg-6">
                        <input type="password" name="mdp" placeholder="Mot de passe" required class="form-control">
                    </div>

                    <div class="col-12 d-flex flex-column align-items-center mt-4">
                        <button type="submit" class="btn_connexion">Se connecter</button>
                        
                        <a class="mdp_oublie mt-3" href="motDePasseOublie.php">Mot de passe oublié ?</a>
                        <a class="inscription_lien mt-2" href="creerCompteVendeur.php">Pas encore vendeur ? Inscrivez-vous ici</a>
                    </div>
                </div>
            </form>
        </div>

        <p class="text-footer">
            Alizon traite les données recueillies à des fins de gestion de la relation client et des commandes.
            Pour en savoir plus, reportez-vous à la Politique de protection de vos données personnelles.
        </p>
    </main>
    <?php require_once "./partials/footer.php"; ?>
</body>
</html>
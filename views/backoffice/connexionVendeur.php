<?php 
    $currentPage = basename(__FILE__);
    require_once "../../controllers/pdo.php"; 

    $message = $_SESSION['message'] ?? ''; 
    unset($_SESSION['message']);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../public/style.css">
    <title>Connexion à un compte vendeur</title>
</head>


<body>
    <?php require_once "./partials/header.php"; ?>
    <main class="connexionVendeur">
        <img class="triskiel" src="../../public/images/triskiel gris.svg" alt="">

        <div class="pdp_title">
            <img src="../../public/images/pdp_user.svg" alt="Avatar" onerror="this.style.display='none'; this.nextElementSibling.style.display='block'">
            <h1>Connexion à votre compte vendeur Alizon</h1>
        </div>

        <div class="information_connexion">
            <form method="post" class="form-vendeur" id="monForm" action="../../controllers/connexionCompteVendeur.php">
                
                <?php if (!empty($message)) : ?>
                    <div class="alert alert-danger text-center" role="alert">
                        <?= $message ?>
                    </div>
                <?php endif; ?>

                <div class="inputs-container">
                    <div class="mb-4">
                        <input type="text" name="pseudo" placeholder="Nom d'utilisateur" required class="form-control custom-input">
                    </div>

                    <div class="mb-2">
                        <input type="password" name="mdp" placeholder="Mot de passe" required class="form-control custom-input">
                    </div>
                </div>

                <div class="liens-utiles">
                    <a href="CreerCompteVendeur.php">Pas encore vendeur ? Inscrivez vous ici</a>
                    <a href="#">Mot de passe oublié ? Cliquez ici</a>
                </div>

                <div class="actions">
                    <button type="submit" class="btn_connexion">Se connecter</button>
                </div>
            </form>
        </div>

        <p class="text-footer">
            Alizon, en tant que responsable de traitement, traite les données recueillies à des fins de gestion de la relation client, 
            gestion des commandes et des livraisons, personnalisation des services, prévention de la fraude, marketing et publicité ciblée. 
            Pour en savoir plus, reportez-vous à la Politique de protection de vos données personnelles.
        </p>
    </main>
    
    <?php require_once "./partials/footer.php"; ?>
</body>
</html>
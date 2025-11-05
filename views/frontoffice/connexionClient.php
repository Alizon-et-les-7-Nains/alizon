<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../public/style.css">
    <title>Page de connexion</title>
</head>
<body class="pageConnexionCLient">
    <?php include '../../views/frontoffice/partials/headerDeconnecte.php'; ?>

    <main> 
        <h2>Connexion à votre compte Alizon</h2>

        <input type="email" placeholder="Adresse mail ou numéro de téléphone*" class="inputConnexionClient">
        <input type="password" placeholder="Mot de passe*" class="inputConnexionClient">

        <a href="#">Pas encore client ? Inscrivez-vous ici</a>
        <a href="#">Mot de passe oublié ? Cliquez ici</a>
        <button class="boutonConnexionClient">Se connecter</button>

        <div class="social-login">
          <div class="social-row">
            <span class="hr-line" aria-hidden="true"></span>

            <div class="social-icons" aria-label="Connexion via">
              <a href="#" class="social-btn" title="Google">
                <img src="../../public/images/google.svg" alt="Google">
              </a>
              <a href="#" class="social-btn" title="Microsoft">
                <img src="../../public/images/microsoft.svg" alt="Microsoft">
              </a>
              <a href="#" class="social-btn" title="Apple">
                <img src="../../public/images/apple.svg" alt="Apple">
              </a>
              <a href="#" class="social-btn" title="Facebook">
                <img src="../../public/images/facebook.svg" alt="Facebook">
              </a>
            </div>

            <span class="hr-line" aria-hidden="true"></span>
          </div>

          <h3 class="social-title">Ou connectez-vous grâce à un des ces services</h3>
        </div>

        <p>Alizon, en tant que responsable de traitement, traite les données recueillies à des fins de gestion de la relation client, gestion des commandes et des livraisons, personnalisation des services, prévention de la fraude, marketing et publicité ciblée. 
Pour en savoir plus, reportez-vous à la Politique de protection de vos données personnelles</p>
    </main>

 <?php include '../../views/frontoffice/partials/footerDeconnecte.php'; ?>  
</body>
</html>
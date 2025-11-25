<?php
$fromBackoffice = strpos($_SERVER['REQUEST_URI'], '/views/backoffice/') !== false;
$homeLink = $fromBackoffice ? '/views/backoffice/accueil.php' : '/views/frontoffice/accueilConnecte.php';
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../public/style.css">
    <link rel="icon" href="/public/images/logoBackoffice.svg">
    <title>Alizon - Erreur</title>
</head>

<body class="page404">

    <img class="bgImgs bleu" src="../../public/images/symboleBleu.png" alt="symboleBleu">
    <img class="bgImgs rose" src="../../public/images/symboleRose.png" alt="symboleRose">

    <main>

        <div>
            <h1>Ur gudenn zo !</h1>
            <h2>Cette page à pris le large</h2>
        </div>

        <div>
            <img src="../../public/images/404.png" alt="Image d'erreur 404"><br>
        </div>

        <div>
            <a href="<?= $homeLink ?>">Retourner à bon port</a>
        </div> 

    </main>

</body>

</html>
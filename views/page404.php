<?php
    $requestUri = $_SERVER['REQUEST_URI'] ?? '';
    $fromBackoffice = strpos($requestUri, '/backoffice/') !== false;
    $fromFrontoffice = strpos($requestUri, '/frontoffice/') !== false;

    if ($fromBackoffice) {
        $homeLink = '/views/backoffice/accueil.php';
    } elseif ($fromFrontoffice) {
        $homeLink = '/views/frontoffice/accueilDeconnecte.php';
    } else {
        $homeLink = null;
    }
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/public/style.css">
    <link rel="icon" href="/public/images/logoBackoffice.svg">
    <title>Alizon - Erreur</title>
</head>

<body class="page404">
    <img class="bgImgs bleu" src="/public/images/symboleBleu.png" alt="symboleBleu">
    <img class="bgImgs rose" src="/public/images/symboleRose.png" alt="symboleRose">

    <main>

        <div>
            <h1>Ur gudenn zo !</h1>
            <h2>Cette page à pris le large</h2>
        </div>

        <div>
            <img src="/public/images/404.png" alt="Image d'erreur 404"><br>
        </div>

        <div>
            <?php if ($homeLink): ?>
                <a href="<?= $homeLink ?>">Retourner à bon port</a>
            <?php else: ?>
                <h2>Retourner à bon port</h2>
                <a href="/views/frontoffice/accueilDeconnecte.php">Frontoffice</a>
                <span style="margin: 0 15px;"></span>
                <a href="/views/backoffice/accueil.php">Backoffice</a>
            <?php endif; ?>
        </div>

    </main>

</body>

</html>
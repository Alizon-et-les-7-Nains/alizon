<?php
    echo '<pre style="background:white;color:black;padding:20px;z-index:9999;position:relative;">';
    print_r($_SERVER['REQUEST_URI']);
    echo '</pre>';
    $requestUri = $_SERVER['REQUEST_URI'] ?? '';
    $fromBackoffice = strpos($requestUri, '/backoffice/') !== false;
    $fromFrontoffice = strpos($requestUri, '/frontoffice/') !== false;

    if ($fromBackoffice) {
        $homeLink = '/views/backoffice/accueil.php';
    } elseif ($fromFrontoffice) {
        $homeLink = '/views/frontoffice/accueilConnecte.php';
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
                <a href="/views/frontoffice/accueilConnecte.php">Frontoffice</a>
                <a href="/views/backoffice/accueil.php">Backoffice</a>
            <?php endif; ?>
        </div>

    </main>

</body>

</html>
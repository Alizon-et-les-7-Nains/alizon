<?php
    require_once '../../controllers/pdo.php';

    require_once '../../controllers/prix.php';
    require_once '../../controllers/date.php';

    require_once '../../controllers/auth.php';
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alizon</title>

    <link rel="stylesheet" href="../../public/style.css">
    <link rel="icon" href="/public/images/logoBackoffice.svg">
</head>

<body class="backoffice">
    <?php require_once './partials/header.php' ?>

    <?php
        $currentPage = basename(__FILE__);
        require_once './partials/aside.php';
    ?>

    <main class="acceuilBackoffice">
        <section class="avis">
            <h1>Avis Populaires</h1>
            <article>
                <?php
    $avis = ($pdo->query(file_get_contents('../../queries/backoffice/derniersAvis.sql')))->fetchAll(PDO::FETCH_ASSOC);
    if (count($avis) == 0) echo "<h2>Aucun avis</h>";
    foreach ($avis as $avi) {
        $imagesAvis = ($pdo->query(str_replace('$idClient', $avi['idClient'], str_replace('$idProduit', $avi['idProduit'], file_get_contents('../../queries/imagesAvis.sql')))))->fetchAll(PDO::FETCH_ASSOC);
        $imageClient = "/images/photoProfilClient/photo_profil]";
        $html = "
        <table>
            <tr>
                <th rowspan=2>
                    <figure>
                        <img src='$imageClient'>
                        <figcaption>" . $avi['nomClient'] . "</figcaption>
                    </figure>
                    <figure>
                        <figcaption>" . str_replace('.', ',', $avi['note']) . "</figcaption>
                        <img src='/public/images/etoile.svg'>
                    </figure>
                </th>
                <th>" . $avi['nomProduit'] . " - " . $avi['titreAvis'] . "</th>
                <td>Le" . formatDate($avi['dateAvis']) . "</td>
            </tr>
            <tr>
                <td colspan='2'>" . $avi['contenuAvis'] . "</td>
            </tr>
            <tr>
                <td></td>
                <td colspan='2'>";   
                    foreach ($imagesAvis as $imageAvi) {
                        $html .= "<img src='" . $imageAvi['URL'] . "' class='imageAvis'>";
                    }
                $html .= "</td>
            </tr>
        </table>
        ";
        echo $html;
    }
?>
            </article>
            <a href="./avis.php" title="Voir plus"><img src="/public/images/infoDark.svg"></a>
        </section>
        <?php require_once './partials/retourEnHaut.php' ?>
    </main>

    <?php require_once './partials/footer.php' ?>

    <script src="../../public/amd-shim.js"></script>
    <script src="../../public/script.js"></script>
</body>

</html>
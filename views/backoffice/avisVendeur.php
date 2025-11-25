<?php
require_once '../../controllers/pdo.php';
require_once '../../controllers/prix.php';
require_once '../../controllers/date.php';
require_once '../../controllers/auth.php';

$query = "
    SELECT 
        a.idProduit,
        a.idClient,
        a.titreAvis,
        a.contenuAvis,
        a.note,
        a.dateAvis,
        p.nom as nomProduit,
        c.prenom,
        c.nom as nomClient,
        c.pseudo
    FROM saedb._avis a
    JOIN saedb._produit p ON a.idProduit = p.idProduit
    JOIN saedb._client c ON a.idClient = c.idClient
    WHERE p.idVendeur = :idVendeur
    ORDER BY a.dateAvis DESC
";

$stmt = $pdo->prepare($query);
$stmt->bindValue(':idVendeur', $_SESSION['id'], PDO::PARAM_INT); 
$stmt->execute();
$avis = $stmt->fetchAll(PDO::FETCH_ASSOC);
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

    <?php require_once './partials/header.php'; ?>

    <?php
        $currentPage = basename(__FILE__);
        require_once './partials/aside.php';
    ?>

    <main>
        <section class="avis-vendeur">
            <h1>Avis Populaires</h1>

            <article>

                <?php if (count($avis) == 0): ?>
                <h2>Aucun avis</h2>
                <?php endif; ?>

                <?php foreach ($avis as $avi): ?>

                <?php
                        $imagesAvis = (
                            $pdo->query(
                                str_replace(
                                    '$idClient',
                                    $avi['idClient'],
                                    str_replace('$idProduit', $avi['idProduit'], file_get_contents('../../queries/imagesAvis.sql'))
                                )
                            )
                        )->fetchAll(PDO::FETCH_ASSOC);

                        $imageClient = "/public/images/photoProfilClient/photo_profil" . $avi['idClient'] . ".svg";
                    ?>

                <table class="avi">
                    <tr>
                        <th rowspan="3" class="col-gauche">
                            <figure class="profil-client">
                                <img src="<?= $imageClient ?>" onerror="this.style.display='none'">
                                <figcaption><?= $avi['pseudo'] ?></figcaption>
                            </figure>
                        </th>

                        <td class="ligne">
                            <figure class="etoiles">
                                <figcaption><?= str_replace('.', ',', $avi['note']) ?></figcaption>
                                <img src="/public/images/etoile.svg">
                            </figure>
                            <?= $avi['titreAvis'] ?> - <?= $avi['nomProduit'] ?>
                        </td>
                        <td class="ligne">
                            <p class=" date-avis">Avis déposé le <?= formatDate($avi['dateAvis']) ?></p>
                        </td>

                    </tr>

                    <tr>
                        <td class=" ligne text" colspan="2">
                            <?= $avi['contenuAvis'] ?>
                        </td>
                    </tr>

                    <tr>
                        <td class="ligne" colspan="2">
                            <?php foreach ($imagesAvis as $imageAvi): ?>
                            <img src="<?= $imageAvi['URL'] ?>" class="imageAvis">
                            <?php endforeach; ?>
                        </td>
                    </tr>
                </table>

                <?php endforeach; ?>
            </article>

            <a href="./avis.php" title="Voir plus">
                <img src="/public/images/infoDark.svg">
            </a>
        </section>

        <?php require_once './partials/retourEnHaut.php'; ?>
    </main>

    <?php require_once './partials/footer.php'; ?>

    <script src="../../public/amd-shim.js"></script>
    <script src="../../public/script.js"></script>

</body>

</html>
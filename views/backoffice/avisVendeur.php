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

// Prépare la requête des images (on évite str_replace et on utilise des paramètres nommés)
$imagesQuery = "SELECT URL FROM saedb._images WHERE idProduit = :idProduit AND idClient = :idClient";
$imagesStmt = $pdo->prepare($imagesQuery);
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
                    // Récupère les images pour cet avis
                    $imagesStmt->execute([
                        ':idProduit' => $avi['idProduit'],
                        ':idClient'  => $avi['idClient']
                    ]);
                    $imagesAvis = $imagesStmt->fetchAll(PDO::FETCH_ASSOC);

                    // Construis le chemin de la photo profil client (affiche none via onerror si absent)
                    $imageClient = "/images/photoProfilClient/photo_profil" . $avi['idClient'] . ".svg";
                    ?>

                <table class="avi">
                    <tr>
                        <th rowspan="3" class="col-gauche">
                            <figure class="profil-client">
                                <img src="<?php echo htmlspecialchars($imageClient, ENT_QUOTES); ?>"
                                    onerror="this.style.display='none'">
                                <figcaption><?= htmlspecialchars($avi['pseudo'] ?? $avi['nomClient'], ENT_QUOTES) ?>
                                </figcaption>
                            </figure>
                        </th>

                        <td class="ligne">
                            <figure class="etoiles">
                                <figcaption><?= str_replace('.', ',', htmlspecialchars($avi['note'], ENT_QUOTES)) ?>
                                </figcaption>
                                <img src="/public/images/etoile.svg" alt="étoile">
                            </figure>
                            <?= htmlspecialchars($avi['titreAvis'], ENT_QUOTES) ?> -
                            <?= htmlspecialchars($avi['nomProduit'], ENT_QUOTES) ?>
                        </td>
                        <td class="ligne">
                            <p class="date-avis">Avis déposé le <?= formatDate($avi['dateAvis']) ?></p>
                        </td>
                    </tr>

                    <tr>
                        <td class="ligne text" colspan="2">
                            <?= nl2br(htmlspecialchars($avi['contenuAvis'], ENT_QUOTES)) ?>
                        </td>
                    </tr>

                    <tr>
                        <td class="ligne" colspan="2">
                            <?php if (!empty($imagesAvis)): ?>
                            <?php foreach ($imagesAvis as $imageAvi): ?>
                            <img src="<?= htmlspecialchars($imageAvi['URL'], ENT_QUOTES) ?>" class="imageAvis"
                                onerror="this.style.display='none'">
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                </table>

                <?php endforeach; ?>
            </article>

            <a href="./avis.php" title="Voir plus">
                <img src="/public/images/infoDark.svg" alt="info">
            </a>
        </section>

        <?php require_once './partials/retourEnHaut.php'; ?>
    </main>

    <?php require_once './partials/footer.php'; ?>

    <script src="../../public/amd-shim.js"></script>
    <script src="../../public/script.js"></script>

</body>

</html>
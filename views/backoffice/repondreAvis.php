<?php
require_once '../../controllers/pdo.php';
require_once '../../controllers/prix.php';
require_once '../../controllers/date.php';
require_once '../../controllers/auth.php';


$idProduit = $_GET['idProd']; 
$idClient = $_GET['idCli']; 

// Selectionné les champs nécéssaires à la création
// De la carte d'un avis
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
        WHERE a.idProduit = :idProduit AND a.idClient = :idClient";

$stmt = $pdo->prepare($query);
$stmt->bindValue(':idProduit', $idProduit, PDO::PARAM_INT); 
$stmt->bindValue(':idClient', $idClient, PDO::PARAM_INT); 
$stmt->execute();
$avis = $stmt->fetchAll(PDO::FETCH_ASSOC);
$avis = $avis[0];


// Requête pour séléctionner le contenu d'un avis, s'il éxiste.
$query = "SELECT contenuAvis
FROM _reponseAvis
WHERE idProduit = :idProduit AND idClient = :idClient";
$stmt = $pdo->prepare($query);
$stmt->bindValue(':idProduit', $idProduit, PDO::PARAM_INT); 
$stmt->bindValue(':idClient', $idClient, PDO::PARAM_INT); 
$stmt->execute();
$contenuAvis = $stmt->fetchColumn();
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alizon - Répondre avis</title>

    <link rel="stylesheet" href="../../public/style.css">
    <link rel="icon" href="/public/images/logoBackoffice.svg">
</head>

<body class="backoffice">

    <?php require_once './partials/header.php'; ?>

    <?php
        $currentPage = basename(__FILE__);
        require_once './partials/aside.php';
    ?>

    <main class="repondreAvis">
        <section >
            <article>
                <?php
                        // Création de la carte d'un avis avec son produit associé 

                        $query = "select URL from _imageAvis where idProduit = :idProduit AND idClient = :idClient";
                        $stmt = $pdo->prepare($query);
                        $stmt->bindValue(':idProduit', $idProduit, PDO::PARAM_INT); 
                        $stmt->bindValue(':idClient', $idClient, PDO::PARAM_INT); 
                        $stmt->execute();
                        $imagesAvis = $stmt->fetchAll(PDO::FETCH_ASSOC);

                        $imageClient = "/images/photoProfilClient/photo_profil" . $idClient . ".svg";
                    ?>

                <table class="avi">
                    <tr>
                        <th rowspan="3" class="col-gauche">
                            <figure class="profil-client">
                                <img src=" <?= $imageClient ?>" onerror="this.style.display='none'">
                                <figcaption><?= $avis['pseudo'] ?? $avis['nomClient'] ?></figcaption>
                            </figure>
                        </th>

                        <td class="ligne">
                            <figure class="etoiles">
                                <figcaption><?= str_replace('.', ',', $avis['note']) ?></figcaption>
                                <img src=" /public/images/etoile.svg">
                            </figure>
                            <?= $avis['titreAvis'] ?> - <?= $avis['nomProduit'] ?>
                        </td>
                        <td class="ligne">
                            <p class=" date-avis">Avis déposé le <?= formatDate($avis['dateAvis']) ?></p>
                        </td>

                    </tr>

                    <tr>
                        <td class=" ligne text" colspan="2">
                            <?= $avis['contenuAvis'] ?>
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
            </article>
        </section>
        <section>
            <!-- Zone d'écriture de la réponse -->
            <form action="../../controllers/reponseCommentaire.php?idCli=<?php echo $idClient;?>&idProd=<?php echo $idProduit;?>" method="POST">
                    <label>Message :</label>   
                    <textarea id="message" name="message" placeholder="Que pensez vous de cet avis..." rows="15" required ><?php if($contenuAvis){
                                echo($contenuAvis); // Pré remplissage de l'avis, s'il y en a déjà un
                            }
                        ?></textarea>
                    <button type="submit" class="buttonValider"> Valider </button>
            </form>
        </section>
        <?php require_once './partials/retourEnHaut.php'; ?>
    </main>

    <?php require_once './partials/footer.php'; ?>

    <script src="../../public/amd-shim.js"></script>
    <script src="../../public/script.js"></script>

</body>

</html>

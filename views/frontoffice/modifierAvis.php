<?php 
require_once "../../controllers/pdo.php";
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../frontoffice/connexionClient.php");
    exit();
}

$id_client = $_SESSION['user_id'];

$pdo->query("SELECT * from _avis WHERE idClient = $id_client");
$mesAvis = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier Avis; ?></title>
    <link rel="icon" href="/public/images/logoBackoffice.svg">
    <link rel="stylesheet" href="../../public/style.css">
</head>
<body class="modifierAvis">
    <header>
        <?php include '../../views/frontoffice/partials/headerConnecte.php'; ?>
    </header>
    <main>
        <h1> Mes Commentaires</h1>

        <?php
    $avisSTMT = $pdo->prepare(file_get_contents('../../queries/backoffice/derniersAvis.sql'));
    $avisSTMT->execute([':idVendeur' => $_SESSION['id']]);
    $avis = $avisSTMT->fetchAll(PDO::FETCH_ASSOC);
    if (count($avis) == 0) echo "<h2>Aucun avis</h2>";
    foreach ($avis as $avi) {
        $imagesAvis = ($pdo->query(str_replace('$idClient', $avi['idClient'], str_replace('$idProduit', $avi['idProduit'], file_get_contents('../../queries/imagesAvis.sql')))))->fetchAll(PDO::FETCH_ASSOC);
        $imageClient = "/images/photoProfilClient/photo_profil" . $avi['idClient'] . ".svg";
        $html = "
        <table>
            <tr>
                <th rowspan=2>
                    <figure>
                        <img src='$imageClient' onerror=" . '"this.style.display=' . "'none'" . '"' . ">
                        <figcaption>" . $avi['pseudo'] . "</figcaption>
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
    </main>

</body>
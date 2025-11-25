<?php 
require_once "../../controllers/pdo.php";
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../frontoffice/connexionClient.php");
    exit();
}

$id_client = $_SESSION['user_id'];

$stmt = $pdo->query("SELECT * FROM _avis WHERE idClient = $id_client");
$mesAvis = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>


<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier Avis</title>

    <link rel="icon" href="/public/images/logoBackoffice.svg">
    <link rel="stylesheet" href="../../public/style.css">
</head>
<body class="modifierAvis">
    <header>
        <?php include './partials/headerConnecte.php'; ?>
    </header>
    <main>
        <h1> Mes Commentaires</h1>

        <?php
            if (count($mesAvis) == 0) echo "<h2>Aucun avis</h2>";
            foreach ($mesAvis as $avis) {
                $imagesAvis = ($pdo->query(str_replace('$idClient', $avis['idClient'], str_replace('$idProduit', $avis['idProduit'], file_get_contents('../../queries/imagesAvis.sql')))))->fetchAll(PDO::FETCH_ASSOC);
                $imageClient = "/images/photoProfilClient/photo_profil" . $avis['idClient'] . ".svg";
                $html = "
                <table>
                    <tr>
                        <th rowspan=2>
                            <figure>
                                <img src='$imageClient' onerror=" . '"this.style.display=' . "'none'" . '"' . ">
                                <figcaption>" . $avis['pseudo'] . "</figcaption>
                            </figure>
                            <figure>
                                <figcaption>" . str_replace('.', ',', $avis['note']) . "</figcaption>
                                <img src='/public/images/etoile.svg'>
                            </figure>
                        </th>
                        <th>" . $avis['nomProduit'] . " - " . $avis['titreAvis'] . "</th>
                        <td>Le" . formatDate($avis['dateAvis']) . "</td>
                    </tr>
                    <tr>
                        <td colspan='2'>" . $avis['contenuAvis'] . "</td>
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
    <?php include './partials/footerConnecte.php'; ?>
</body>
<?php 
require_once "../../controllers/pdo.php";
require_once "../../controllers/date.php";

session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../frontoffice/connexionClient.php");
    exit();
}

$id_client = $_SESSION['user_id'];

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes notifications</title>

    <link rel="icon" href="/public/images/logoBackoffice.svg">
    <link rel="stylesheet" href="../../public/style.css">
</head>
<body>
    <?php include './partials/headerConnecte.php'; ?>

    <main class="mesAvis">
        <h1>Mes Notifications</h1>



        <?php require_once '../backoffice/partials/retourEnHaut.php' ?>
        <?php include '../../views/frontoffice/partials/footerConnecte.php'; ?>
    </main>

</body>
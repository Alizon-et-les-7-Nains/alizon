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
var_dump($mesAvis);
exit;

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

        
    </main>

</body>
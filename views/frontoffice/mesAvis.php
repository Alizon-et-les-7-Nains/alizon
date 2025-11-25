<?php 
require_once "../../controllers/pdo.php";
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../frontoffice/connexionClient.php");
    exit();
}

$id_client = $_SESSION['user_id'];

// Récupérer les avis du client
$stmt = $pdo->query("SELECT * FROM _avis WHERE idClient = $id_client");
$mesAvis = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier Avis</title>
    <link rel="stylesheet" href="../../public/style.css">
</head>
<body>

<h1>Mes Commentaires</h1>

<?php
if (count($mesAvis) === 0) {
    echo "<h2>Aucun avis</h2>";
}

foreach ($mesAvis as $avis) {
    
    // Charger la requête SQL d'images
    $sql = file_get_contents('../../queries/imagesAvis.sql');

    // Remplacer les variables
    $sql = str_replace('$idProduit', $avis['idProduit'], $sql);
    $sql = str_replace('$idClient', $avis['idClient'], $sql);

    // Récupérer les images
    $imagesAvis = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

    echo "<pre>";
    print_r($imagesAvis);
    echo "</pre>";
}
?>

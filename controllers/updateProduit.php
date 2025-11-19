<?php 
require_once 'pdo.php';
session_start();

$idProd = $_GET['id']; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare("UPDATE _produit SET nom = :nom, description = :description, prix = :prix, poids = :poids, mots_cles = :mots_cles WHERE idProd = :idProd");
    $img = $pdo->prepare("UPDATE _imageDeProduit SET URL = :url WHERE idProd = :idProd");
    $stmt->execute([
        ':nom' => $_POST['nom'],
        ':description' => $_POST['description'],
        ':prix' => $_POST['prix'],
        ':poids' => $_POST['poids'],
        ':mot_cles' => $_POST['mots_cles'],
        ':idProd' => $idProd
    ]);

    $img->execute([
        ':url' => $_POST['url'],
        ':idProd' => $idProd
    ]);
}

header("Location: ../views/frontoffice/compteClient.php"); 
exit();
?>
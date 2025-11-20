<?php 
require_once 'pdo.php';
session_start();

$idProd = $_GET['id']; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare("UPDATE _produit SET nom = :nom, description = :description, prix = :prix, poids = :poids, mots_cles = :mot_cles WHERE idProduit = :idProduit");
    $img = $pdo->prepare("UPDATE _imageDeProduit SET URL = :url WHERE idProduit = :idProduit");
    $stmt->execute([
        ':nom' => $_POST['nom'],
        ':description' => $_POST['description'],
        ':prix' => $_POST['prix'],
        ':poids' => $_POST['poids'],
        ':mot_cles' => $_POST['mots_cles'],
        ':idProduit' => $idProd
    ]);

    $img->execute([
        ':url' => $_POST['url'],
        ':idProduit' => $idProd
    ]);
}

header("Location: ../views/frontoffice/compteClient.php"); 
exit();
?>
<?php 
require_once 'pdo.php';
session_start();

$idProd = $_GET['id']; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare("UPDATE _produit SET nom = :nom, description = :description, prix = :prix, poids = :poids, mots_cles = :mot_cles WHERE idProduit = :idProduit");
    $imgDeProd = $pdo->prepare("UPDATE _imageDeProduit SET URL = :url WHERE idProduit = :idProduit");
    $stmt->execute([
        ':nom' => $_POST['nom'],
        ':description' => $_POST['description'],
        ':prix' => $_POST['prix'],
        ':poids' => $_POST['poids'],
        ':mot_cles' => $_POST['mots_cles'],
        ':idProduit' => $idProd
    ]);

$fileName = $_FILES['url']['name'];
$tmpPath = $_FILES['url']['tmp_name'];

move_uploaded_file($tmpPath, "../public/images/$fileName");
$url = "../public/images/$fileName";
try{
        $imgDeProd->execute([
            ':url' => $url,
            ':idProduit' => $idProd
        ]);
    }
catch(PDOException $e){
    echo "Erreur SQL : " . $e->getMessage();
}
}
    

header("Location: ../views/backoffice/accueil.php"); 
exit();
?>
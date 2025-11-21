<?php 
require_once 'pdo.php';
session_start();

$idProd = $_GET['id']; 
// Si il y a eu un formulare de remplie, on fait une requête permettant de supprimer le produit
// La première requête permet de mettre à jour les informations du produit sur lequel le formulaire à été rempli

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare("DELETE FROM _produit WHERE idProduit = :idProduit");
    $imgDeProd = $pdo->prepare("DELETE FROM _imageDeProduit WHERE idProduit = :idProduit");
    $supPanier = $pdo->prepare("DELETE FROM _produitAuPanier WHERE idProduit = :idProduit");

try{
    $supPanier->execute([
        ':idProduit' => $idProd
    ]);
}
catch(PDOException $e){
    echo "Erreur SQL : " . $e->getMessage();
}

try{
    $imgDeProd->execute([
        ':idProduit' => $idProd
    ]);
    }
catch(PDOException $e){
    echo "Erreur SQL : " . $e->getMessage();
}
}

try{
    $stmt->execute([
        ':idProduit' => $idProd
    ]);
}
catch(PDOException $e){
    echo "Erreur SQL : " . $e->getMessage();
}

    

//header("Location: ../views/backoffice/accueil.php"); 
exit();
?>
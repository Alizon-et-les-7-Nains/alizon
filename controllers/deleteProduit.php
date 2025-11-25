<?php 
require_once 'pdo.php';
session_start();

$idProd = $_GET['id']; 
// Si il y a eu un formulare de remplie, on fait une requête permettant de supprimer le produit

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare("DELETE FROM _produit WHERE idProduit = :idProduit");
    $imgDeProd = $pdo->prepare("DELETE FROM _imageDeProduit WHERE idProduit = :idProduit");
    $supPanier = $pdo->prepare("DELETE FROM _produitAuPanier WHERE idProduit = :idProduit");
    $supAvis = $pdo->prepare("DELETE FROM _avis WHERE idProduit = :idProduit");


try{
    $supAvis->execute([
        ':idProduit' => $idProd
    ]);
}
catch(PDOException $e){
    echo "Erreur SQL : " . $e->getMessage();
}

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
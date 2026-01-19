<?php 
require_once 'pdo.php';
session_start();
$idProd = $_GET['idProd'];  
$idClient = $_GET['idCli'];  

// Suppression du commentaire
$stmt = $pdo->prepare("DELETE FROM _reponseAvis WHERE idClient = :idClient AND idProduit = :idProduit");

try{
    $stmt->execute([
        ':idClient' => $idClient,
        ':idProduit' => $idProd
    ]);
}
catch(PDOException $e){
    echo "Erreur SQL : " . $e->getMessage();
}

header("Location: ../views/backoffice/avis.php"); 
exit();

?>
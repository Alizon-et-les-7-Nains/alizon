<?php 
require_once 'pdo.php';
session_start();
$idProd = $_GET['id'];  
$idClient = $_SESSION['user_id'];  

$stmt = $pdo->prepare("DELETE FROM _avis WHERE idClient = :idClient AND idProduit = :idProduit");

try{
    $stmt->execute([
        ':idClient' => $idClient,
        ':idProduit' => $idProd
    ]);
}
catch(PDOException $e){
    echo "Erreur SQL : " . $e->getMessage();
}


header("Location: ../views/frontoffice/mesAvis.php"); 
exit();

?>
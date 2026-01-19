<?php 
require_once 'pdo.php';
session_start();

$idProd = $_GET['id']; 


    $supImgAvis = $pdo->prepare("DELETE * FROM _imageAvis WHERE idProduit = :idProduit");
    $supAvis = $pdo->prepare("DELETE * FROM _avis WHERE idProduit = :idProduit");
    $supContient = $pdo->prepare("DELETE * FROM _contient WHERE idProduit = :idProduit");
    $supPanier = $pdo->prepare("DELETE * FROM _produitAuPanier WHERE idProduit = :idProduit");
    $imgDeProd = $pdo->prepare("DELETE * FROM _imageDeProduit WHERE idProduit = :idProduit");
    $stmt = $pdo->prepare("DELETE * FROM _produit WHERE idProduit = :idProduit");

    try {
        $supImgAvis->execute([':idProduit' => $idProd]);
        $supAvis->execute([':idProduit' => $idProd]);
        $supContient->execute([':idProduit' => $idProd]);   
        $supPanier->execute([':idProduit' => $idProd]);
        $imgDeProd->execute([':idProduit' => $idProd]);
        $stmt->execute([':idProduit' => $idProd]);
        
        header("Location: ../views/backoffice/accueil.php"); 
        exit();
    } catch(PDOException $e) {
        die("Erreur SQL : " . $e->getMessage());
    }

?>
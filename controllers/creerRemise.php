<?php

require_once 'pdo.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') { 

    if(isset($_POST['dateLimite']) && isset($_POST['id'])) {
        $idProd = intval($_POST['id']); 
        $dateLimite = $_POST['dateLimite'];
        $aUneRemise =  $_POST['aUneRemise'];
        $tauxRemise = $_POST['reduction'];
        $dateSql = DateTime::createFromFormat('d/m/Y', $dateLimite)->format('Y-m-d');

        if(!$aUneRemise){
            
            $stmt = $pdo->prepare("INSERT INTO _remise(idProduit, tauxRemise, debutRemise, finRemise) VALUES (:idProd, :tauxRemise, CURDATE(), :dateLimite)");
            $stmt->execute([':idProd' => $idProd, ':tauxRemise'=>$tauxRemise,':dateLimite' => $dateSql]);
        } else {
            $stmt = $pdo->prepare("UPDATE _remise SET tauxRemise = :tauxRemise, finRemise = :dateLimite)");
            $stmt->execute([':tauxRemise'=>$tauxRemise,':dateLimite' => $dateSql]);
        }
    }

    header('Location: ../views/backoffice/produits.php');
    exit;
}

?>
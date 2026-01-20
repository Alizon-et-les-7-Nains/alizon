<?php

require_once 'pdo.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') { 

    if(isset($_POST['dateLimite']) && isset($_POST['id'])) {
        // On récupère les données nécéssaire au produit
        $idProd = intval($_POST['id']); 
        $dateLimite = $_POST['dateLimite'];
        $aUneRemise =  $_POST['aUneRemise'];
        $tauxRemise = $_POST['reduction'];
        $dateSql = DateTime::createFromFormat('d/m/Y', $dateLimite)->format('Y-m-d');

        if($aUneRemise == 'false'){    
            // Si il n'a pas de remise alors on créer une nouvelle remise dans la table remise 
            $stmt = $pdo->prepare("INSERT INTO _remise(idProduit, tauxRemise, debutRemise, finRemise) VALUES (:idProd, :tauxRemise, CURDATE(), :dateLimite)");
            $stmt->execute([':idProd' => $idProd, ':tauxRemise'=>$tauxRemise,':dateLimite' => $dateSql]);
        } else {
            // Sinon on modifie l'ancienne remise pour mettre la nouvelle
            $stmt = $pdo->prepare("UPDATE _remise SET tauxRemise = :tauxRemise, finRemise = :dateLimite WHERE idProduit = :idProd");
            $stmt->execute([':idProd' => $idProd, ':tauxRemise'=>$tauxRemise,':dateLimite' => $dateSql]);
        }
    }

    header('Location: ../views/backoffice/produits.php');
    exit;
}

?>
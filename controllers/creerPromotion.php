<?php

require_once 'pdo.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') { 

    if(isset($_POST['date_limite']) && isset($_POST['id'])) {
        $idProd = intval($_POST['id']); 
        $dateLimite = $_POST['date_limite'];
        try {
            $dateSql = DateTime::createFromFormat('d/m/Y', $dateLimite)->format('Y-m-d');
        } catch (error) {
            header('Location: ../views/backoffice/produits?error=1.php');
            exit;
        }

        $stmt = $pdo->prepare("INSERT INTO _promotion(idProduit, debutPromotion, finPromotion) VALUES (:idProd, CURDATE(), :dateLimite)");

        $stmt->execute([':idProd' => $idProd,':dateLimite' => $dateSql]);
    }

    header('Location: ../views/backoffice/produits.php');
    exit;
}

?>
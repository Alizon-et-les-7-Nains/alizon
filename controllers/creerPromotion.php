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
            header('Location: ../views/backoffice/produits.php?error=1&idProduit='.$id.'.php');
            exit;
        }

        if(isset($_FILES['baniere']['tmp_name']) && !empty($_FILES['baniere']['tmp_name'])) {
            $ext = pathinfo($_FILES['baniere']['name'], PATHINFO_EXTENSION);

            if(!in_array(strtolower($ext), ['jpg', 'jpeg', 'png'])) {
                header('Location: ../views/backoffice/produits.php?error=2&idProduit='.$idProd);
                exit;
            }

            $baniereData = file_get_contents($_FILES['baniere']['tmp_name']);
            move_uploaded_file($_FILES['baniere']['tmp_name'], './images/baniere/' . $idProd . $_FILES['baniere']['name']);
        }
            
        $stmt = $pdo->prepare("INSERT INTO _promotion(idProduit, debutPromotion, finPromotion) VALUES (:idProd, CURDATE(), :dateLimite)");

        $stmt->execute([':idProd' => $idProd,':dateLimite' => $dateSql]);
    }

    header('Location: ../views/backoffice/produits.php');
    exit;
}

?>
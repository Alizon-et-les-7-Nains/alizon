<?php

require_once 'pdo.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') { 

    if(isset($_POST['date_limite']) && isset($_POST['id'])) {
        $idProd = intval($_POST['id']); 
        $dateLimite = $_POST['date_limite'];

        $photoPath = '/var/www/html/images/baniere/'.$idProd;

        $extensionsPossibles = ['jpg'];
        $extension = '';

        $d = DateTime::createFromFormat('d/m/Y', $dateLimite);

        if (!$d) {
            $d = DateTime::createFromFormat('Y-m-d', $dateLimite);
        }

        if (!$d) {
             header('Location: ../views/backoffice/produits.php?error=1&idProduit='.$idProd);
             exit;
        }

        try {
            $dateSql = $d->format('Y-m-d');
            $stmt = $pdo->prepare("INSERT INTO _promotion(idProduit, debutPromotion, finPromotion) VALUES (:idProd, CURDATE(), :dateLimite)");
            $stmt->execute([':idProd' => $idProd,':dateLimite' => $dateSql]);
        } catch (Exception $e) {
            header('Location: ../views/backoffice/produits.php?error=1&idProduit='.$idProd);
            exit;
        }

        foreach ($extensionsPossibles as $ext) {
            if (file_exists($photoPath . '.' . $ext)) {
                $extension = '.' . $ext;
                break;
            }
        }

        if ($extension !== '') {
            $oldFile = $photoPath . $extension;
            if (file_exists($oldFile)) {
                unlink($oldFile);
            }
        }

        if (isset($_FILES['baniere']) && $_FILES['baniere']['tmp_name'] != '') {
            $extension = pathinfo($_FILES['baniere']['name'], PATHINFO_EXTENSION);
            $extension = '.'.$extension;
            move_uploaded_file($_FILES['baniere']['tmp_name'], $photoPath.$extension);
        }

    }

    header('Location: ../views/backoffice/produits.php');
    exit;
}

?>
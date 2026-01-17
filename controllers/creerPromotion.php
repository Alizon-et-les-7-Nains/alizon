<?php

require_once 'pdo.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') { 

    if (isset($_POST['supprimer_banniere']) && $_POST['supprimer_banniere'] == "1") {

        $idProd = intval($_POST['id']); 
        $photoPath = '/var/www/html/images/baniere/'.$idProd.".jpg";
        if (file_exists($photoPath)) {
            unlink($photoPath);
            header('Location: ../views/backoffice/produits.php');
            exit;
        } else {
            header('Location: ../views/backoffice/produits.php?error=4&idProduit='.$idProd);
            exit;
        }
        
    }

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

        $stmt = $pdo->prepare("SELECT * FROM _promotion WHERE idProduit = :idProd");
        $stmt->execute([':idProd' => $idProd]);
        $res = $stmt->fetchAll(PDO::FETCH_ASSOC); 

        $dateSql = $d->format('Y-m-d');

        // Récupération du nom du produit pour la notification
        $stmtProduit = $pdo->prepare("SELECT nom FROM _produit WHERE idProduit = ?");
        $stmtProduit->execute([$idProd]);
        $produit = $stmtProduit->fetch(PDO::FETCH_ASSOC);
        $nomProduit = $produit['nom'] ?? 'Un produit';

        // Création de la notification globale
        $stmt = $pdo->prepare("
            INSERT INTO _notification (idClient, contenuNotif, titreNotif, dateNotif, est_vendeur) 
            VALUES (34, ?, ?, ?, 0)
        ");
        $stmt->execute([
            "Découvrez {$nomProduit}, notre nouveau produit mis en avant. Ne manquez pas cette sélection !",
            "🌟 Nouveau produit vedette !",
            date('Y-m-d H:i:s'),
        ]);

        if(count($res) >= 1) {
            $stmt = $pdo->prepare("UPDATE _promotion SET finPromotion = :finPromotion WHERE idProduit = :idProd");
            $stmt->execute([':finPromotion' => $dateSql,':idProd' => $idProd]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO _promotion(idProduit, debutPromotion, finPromotion) VALUES (:idProd, CURDATE(), :dateLimite)");
            $stmt->execute([':idProd' => $idProd,':dateLimite' => $dateSql]);
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
<?php
require_once 'pdo.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $message = trim($_POST['message'] ?? '');
    $idProduit = $_GET['idProd']; 
    $idClient = $_GET['idCli']; 

    // 1. Enregistrement de la réponse du vendeur
    $sqlAvis = "INSERT INTO _reponseAvis (idProduit, idClient, contenuAvis) 
                VALUES (:idProduit, :idClient, :contenu)
                ON DUPLICATE KEY UPDATE contenuAvis = VALUES(contenuAvis)";

    $stmt = $pdo->prepare($sqlAvis);
    $stmt->execute([
        ':idProduit' => $idProduit,
        ':idClient' => $idClient,
        ':contenu' => $message,
    ]);

    // 2. Récupération du nom du produit pour personnaliser la notification
    $stmtProd = $pdo->prepare("SELECT nom FROM _produit WHERE idProduit = ?");
    $stmtProd->execute([$idProduit]);
    $nomProduit = $stmtProd->fetchColumn();

    // 3. Création de la notification pour le client
    $titreNotif = "Réponse à votre avis";
    $contenuNotif = "Le vendeur a répondu à votre avis sur le produit : " . $nomProduit . ". [ID_PROD:" . $idProduit . "]";
    
    $sqlNotif = "INSERT INTO _notification (idClient, titreNotif, contenuNotif, dateNotif, est_vendeur) 
                 VALUES (?, ?, ?, NOW(), 0)";
    $pdo->prepare($sqlNotif)->execute([$idClient, $titreNotif, $contenuNotif]);

    header("Location: ../views/backoffice/avis.php"); 
    exit();
}
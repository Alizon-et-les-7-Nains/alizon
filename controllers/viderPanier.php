<?php

require_once 'pdo.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<script>console.log('Debug vider le panier connecte');</script>";
    $idUtilisateur = $_POST['idUtilisateur'];  

    $stmt = $pdo->prepare("DELETE FROM _panier WHERE idClient = :idClient");
    $stmt->execute([':idClient' => $idUtilisateur]);
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {  
    echo "<script>console.log('Debug vider le panier deconnecte');</script>";

    setcookie("produitPanier", "", time() - 3600, "/");

    // Repondre avec un statut de succes
    http_response_code(200);
    echo json_encode(['status' => 'success', 'message' => 'Panier vidé avec succès.']);
}

?>
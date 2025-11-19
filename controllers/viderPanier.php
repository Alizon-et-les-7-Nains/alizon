<?php

require_once 'pdo.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idUtilisateur = $_POST['idUtilisateur'];  

    $stmt = $pdo->prepare("DELETE FROM _panier WHERE idClient = :idClient");
    $stmt->execute([':idClient' => $idUtilisateur]);

    echo "<script>console.log('Debug vider le panier connecte');</script>";
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {  
    setcookie("produitPanier", "", time() - 3600, "/");

    // Statut OK
    http_response_code(200);

    // Redirection
    header('Location: /views/frontoffice/panierDeconnecte.php');
    exit;
}

?>
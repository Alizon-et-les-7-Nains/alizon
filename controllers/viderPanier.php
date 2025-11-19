<?php

require_once 'pdo.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['idUtilisateur'])) {
        $idUtilisateur = intval($_POST['idUtilisateur']);  

        $stmt = $pdo->prepare("SELECT idPanier FROM _panier WHERE idClient = :idClient ORDER BY idPanier DESC LIMIT 1");
        $stmt->execute([':idClient' => $idUtilisateur]);
        $panier = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($panier) {
            $idPanier = $panier['idPanier'];

            $deleteStmt = $pdo->prepare("DELETE FROM _produitAuPanier WHERE idPanier = :idPanier");
            $deleteStmt->execute([':idPanier' => $idPanier]);
        }
    }

    header('Location: ../views/frontoffice/panier.php');
    exit;
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
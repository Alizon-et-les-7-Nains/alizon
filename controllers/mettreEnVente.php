<?php
require_once 'pdo.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idproduit = $_POST['idproduit'];
    $stmt = $pdo->prepare("UPDATE saedb._produit SET envente = true WHERE idproduit = :idproduit");
    $stmt->execute([
        ':idproduit' => $idproduit
    ]);
}

header("Location: ../views/backoffice/produits.php"); 
exit()
?>

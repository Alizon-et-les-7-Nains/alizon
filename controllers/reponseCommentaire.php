<?php
require_once 'pdo.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupération et nettoyage des données
    $message = trim($_POST['message'] ?? '');
}

$idProduit = $_GET['idProd']; 
$idClient = $_GET['idCli']; 



$sqlAvis = "INSERT INTO _reponseAvis (idProduit, idClient, contenuAvis, dateAvis) 
VALUES (:idProduit, :idClient, :contenu, CURDATE())
ON DUPLICATE KEY UPDATE idProduit = VALUES(idProduit), idClient = VALUES(idClient), contenuAvis = VALUES(contenuAvis), dateAvis = CURDATE()";

$stmt = $pdo->prepare($sqlAvis);
$stmt->execute([
    ':idProduit' => $idProduit,
    ':idClient' => $idClient,
    ':contenu' => $message,
]);

header("Location: ../views/backoffice/avis.php"); 
exit();
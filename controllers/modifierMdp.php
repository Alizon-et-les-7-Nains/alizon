<?php
require_once 'pdo.php';
session_start();

// On récupère l'id du client qui veut modifier son mdp
$idClient = $_SESSION['user_id']; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // On récupère le nouveau mdp
    $nouveauMdp = $_POST['nouveauMdp'];
    // On l'update dans la table du client
    $stmt = $pdo->prepare("UPDATE _client SET mdp = :nouveauMdp WHERE idClient = :idClient");
    $stmt->execute([
        ':nouveauMdp' => $nouveauMdp,
        ':idClient' => $idClient
    ]);
}

header("Location: ../views/frontoffice/compteClient.php"); 
exit()
?>

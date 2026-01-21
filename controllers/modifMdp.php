<?php
session_start();
require_once '../../controllers/pdo.php';

$id_client = $_SESSION['user_id'];

$ancienMdp = $_POST['ancienMdp'] ?? '';
$nouveauMdp = $_POST['nouveauMdp'] ?? '';
$confirmationMdp = $_POST['confirmationMdp'] ?? '';

if ($nouveauMdp !== $confirmationMdp) {
    die("Les mots de passe ne correspondent pas");
}

// Récupération du hash actuel
$stmt = $pdo->prepare("SELECT mdp FROM saedb._client WHERE idClient = ?");
$stmt->execute([$id_client]);
$client = $stmt->fetch(PDO::FETCH_ASSOC);

// Vérification de l'ancien mot de passe
if (!password_verify($ancienMdp, $client['mdp'])) {
    die("Ancien mot de passe incorrect");
}

// Hash du nouveau mot de passe
$nouveauHash = password_hash($nouveauMdp, PASSWORD_DEFAULT);

// Mise à jour
$stmt = $pdo->prepare("
    UPDATE saedb._client 
    SET mdp = :mdp 
    WHERE idClient = :idClient
");

$stmt->execute([
    ':mdp' => $nouveauHash,
    ':idClient' => $id_client
]);

header("Location: ../frontoffice/compteClient.php");
exit();

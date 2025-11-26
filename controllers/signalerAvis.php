<?php
ini_set('display_errors', 0);
error_reporting(0);

header('Content-Type: application/json');

require_once 'pdo.php';
session_start();

// On vérif la connexion
if (!isset($_SESSION['user_id']) && !isset($_SESSION['id'])) {
    echo json_encode(['success' => false, 'message' => 'Vous devez être connecté pour signaler un avis.']);
    exit;
}

// On identifie le signaleur
$idSignaleur = $_SESSION['user_id'] ?? $_SESSION['id'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée.']);
    exit;
}

// On récupère les données
$idProduit = intval($_POST['idProduit'] ?? 0);
$idClientAvis = intval($_POST['idClientAvis'] ?? 0);
$titre = htmlspecialchars(trim($_POST['titre'] ?? ''));
$message = htmlspecialchars(trim($_POST['message'] ?? ''));

if (empty($titre) || empty($message)) {
    echo json_encode(['success' => false, 'message' => 'Veuillez remplir le titre et le message.']);
    exit;
}

try {
    $sql = "INSERT INTO _signalement 
        (idProduitSignale, idClientSignale, idSignaleur, titre, message, dateSignalement) 
        VALUES (?, ?, ?, ?, ?, NOW())";

    $stmt = $pdo->prepare($sql);
    $result = $stmt->execute([$idProduit, $idClientAvis, $idSignaleur, $titre, $message]);

    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Signalement envoyé avec succès.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'enregistrement.']);
    }
} catch (PDOException $e) {
    error_log($e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erreur SQL : ' . $e->getMessage()]);
}
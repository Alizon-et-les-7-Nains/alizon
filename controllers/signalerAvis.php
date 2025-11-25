<?php
session_start();
include '../../controllers/pdo.php';

header('Content-Type: application/json');

function sendJson($success, $message) {
    echo json_encode(['success' => $success, 'message' => $message]);
    exit;
}

// On vérifie la connexion
if (!isset($_SESSION['user_id']) && !isset($_SESSION['id'])) {
    sendJson(false, 'Vous devez être connecté pour signaler un avis.');
}

// On identifie le signaleur
$idSignaleur = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : $_SESSION['id'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJson(false, 'Méthode non autorisée.');
}

// On récupère les données
$idProduit = filter_input(INPUT_POST, 'idProduit', FILTER_VALIDATE_INT);
$idClientAvis = filter_input(INPUT_POST, 'idClientAvis', FILTER_VALIDATE_INT);
$titre = htmlspecialchars(trim(filter_input(INPUT_POST, 'titre', FILTER_SANITIZE_STRING)));
$message = htmlspecialchars(trim(filter_input(INPUT_POST, 'message', FILTER_SANITIZE_STRING)));

// Vérif des champs *
if (!$idProduit || !$idClientAvis || empty($titre) || empty($message)) {
    sendJson(false, 'Veuillez remplir tous les champs correctement.');
}

// Insertion dans la table _signalement
try {
    $sql = "INSERT INTO _signalement (idProduitSignale, idClientSignale, idClientSignaleur, titre, message, dateSignalement)
            VALUES (?, ?, ?, ?, ?, NOW())";
    $stmt = $pdo->prepare($sql);
    $result = $stmt->execute([$idProduit, $idClientAvis, $idSignaleur, $titre, $message]);

    if ($result) {
        sendJson(true, 'Signalement envoyé avec succès.');
    } else {
        sendJson(false, 'Erreur lors de l\'enregistrement.');
    }
} catch (PDOException $e) {
    error_log($e->getMessage());
    sendJson(false, 'Une erreur technique est survenue.');
}
?>

<?php
include 'pdo.php'; 
session_start();

header('Content-Type: application/json');

// On vérif la connexion
if (!isset($_SESSION['user_id']) && !isset($_SESSION['id'])) {
    echo json_encode(['success' => false, 'message' => 'Vous devez être connecté pour signaler un avis.']);
    exit;
}

// On identifie le signaleur
$idSignaleur = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : $_SESSION['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // On recup les données
    $idProduit = intval($_POST['idProduit']);
    $idClientAvis = intval($_POST['idClientAvis']); // Auteur de l'avis
    $titre = htmlspecialchars(trim($_POST['titre']));
    $message = htmlspecialchars(trim($_POST['message'])); // Message

    if (empty($titre) || empty($message)) {
        echo json_encode(['success' => false, 'message' => 'Veuillez remplir le titre et le message.']);
        exit;
    }

    try {
        // Insertion dans _signalement
        $sql = "INSERT INTO _signalement (idProduitSignale, idClientSignale, idClientSignaleur, titre, message, dateSignalement) 
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
        echo json_encode(['success' => false, 'message' => 'Une erreur technique est survenue.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée.']);
}
?>
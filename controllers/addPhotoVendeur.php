<?php
require_once 'pdo.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['photoProfil'])) {
    $code_vendeur = $_POST['codeVendeur'] ?? null;
    
    if (!$code_vendeur) {
        echo json_encode(['success' => false, 'message' => 'Code vendeur manquant']);
        exit;
    }

    $photoDir = '/var/www/html/images/photoProfilVendeur/';
    $photoFilename = 'photo_profil' . $code_vendeur . '.png';
    $photoPath = $photoDir . $photoFilename;

    // Supprimer l'ancienne photo
    if (file_exists($photoPath)) {
        unlink($photoPath);
    }

    // Déplacer la nouvelle photo
    if (move_uploaded_file($_FILES['photoProfil']['tmp_name'], $photoPath)) {
        echo json_encode(['success' => true, 'message' => 'Photo mise à jour']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'upload']);
    }
}
?>
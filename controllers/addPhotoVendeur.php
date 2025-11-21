<?php
session_start();
require_once 'pdo.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['photoProfil'])) {
    $code_vendeur = $_POST['codeVendeur'] ?? null;
    
    if (!$code_vendeur) {
        echo json_encode(['success' => false, 'message' => 'Code vendeur manquant']);
        exit;
    }

    // Chemin relatif plutôt qu'absolu pour plus de compatibilité
    $photoDir = __DIR__ . '/var/www/html/images/photoProfilVendeur/';
    
    // Créer le dossier s'il n'existe pas
    if (!is_dir($photoDir)) {
        mkdir($photoDir, 0755, true);
    }
    
    $photoFilename = 'photo_profil' . $code_vendeur . '.png';
    $photoPath = $photoDir . $photoFilename;

    // Vérifier les erreurs d'upload
    if ($_FILES['photoProfil']['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['success' => false, 'message' => 'Erreur upload: ' . $_FILES['photoProfil']['error']]);
        exit;
    }

    // Vérifier le type de fichier
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
    if (!in_array($_FILES['photoProfil']['type'], $allowedTypes)) {
        echo json_encode(['success' => false, 'message' => 'Type de fichier non autorisé']);
        exit;
    }

    // Supprimer l'ancienne photo si elle existe
    if (file_exists($photoPath)) {
        unlink($photoPath);
    }

    // Déplacer la nouvelle photo
    if (move_uploaded_file($_FILES['photoProfil']['tmp_name'], $photoPath)) {
        echo json_encode(['success' => true, 'message' => 'Photo mise à jour']);
    } else {
        $error = error_get_last();
        echo json_encode(['success' => false, 'message' => 'Erreur déplacement: ' . ($error['message'] ?? 'Inconnue')]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Données manquantes']);
}
?>
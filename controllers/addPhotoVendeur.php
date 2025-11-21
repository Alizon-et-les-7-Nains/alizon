<?php
require_once 'pdo.php';

// Debug: logger la requête
error_log("Upload photo - Méthode: " . $_SERVER['REQUEST_METHOD']);
error_log("FILES: " . print_r($_FILES, true));
error_log("POST: " . print_r($_POST, true));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Vérifier si un fichier a été uploadé
    if (!isset($_FILES['photoProfil']) || $_FILES['photoProfil']['error'] === UPLOAD_ERR_NO_FILE) {
        echo json_encode(['success' => false, 'message' => 'Aucun fichier reçu']);
        exit;
    }
    
    $code_vendeur = $_POST['codeVendeur'] ?? null;
    
    if (!$code_vendeur) {
        echo json_encode(['success' => false, 'message' => 'Code vendeur manquant']);
        exit;
    }

    // Chemin pour stocker les photos
    $photoDir = __DIR__ . '/../../images/photoProfilVendeur/';
    
    // Créer le dossier s'il n'existe pas
    if (!is_dir($photoDir)) {
        if (!mkdir($photoDir, 0755, true)) {
            echo json_encode(['success' => false, 'message' => 'Impossible de créer le dossier']);
            exit;
        }
    }
    
    $photoFilename = 'photo_profil' . $code_vendeur . '.png';
    $photoPath = $photoDir . $photoFilename;

    // Vérifier les erreurs d'upload
    if ($_FILES['photoProfil']['error'] !== UPLOAD_ERR_OK) {
        $uploadErrors = [
            UPLOAD_ERR_INI_SIZE => 'Fichier trop volumineux',
            UPLOAD_ERR_FORM_SIZE => 'Fichier trop volumineux',
            UPLOAD_ERR_PARTIAL => 'Upload partiel',
            UPLOAD_ERR_NO_FILE => 'Aucun fichier',
            UPLOAD_ERR_NO_TMP_DIR => 'Dossier temporaire manquant',
            UPLOAD_ERR_CANT_WRITE => 'Erreur d\'écriture',
            UPLOAD_ERR_EXTENSION => 'Extension non autorisée'
        ];
        $errorMsg = $uploadErrors[$_FILES['photoProfil']['error']] ?? 'Erreur inconnue';
        echo json_encode(['success' => false, 'message' => 'Erreur upload: ' . $errorMsg]);
        exit;
    }

    // Vérifier le type de fichier
    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
    $fileType = mime_content_type($_FILES['photoProfil']['tmp_name']);
    
    if (!in_array($fileType, $allowedTypes)) {
        echo json_encode(['success' => false, 'message' => 'Type de fichier non autorisé: ' . $fileType]);
        exit;
    }

    // Vérifier la taille (max 5MB)
    if ($_FILES['photoProfil']['size'] > 5 * 1024 * 1024) {
        echo json_encode(['success' => false, 'message' => 'Fichier trop volumineux (max 5MB)']);
        exit;
    }

    // Supprimer l'ancienne photo si elle existe
    if (file_exists($photoPath)) {
        if (!unlink($photoPath)) {
            echo json_encode(['success' => false, 'message' => 'Erreur suppression ancienne photo']);
            exit;
        }
    }

    // Convertir et redimensionner l'image si nécessaire
    try {
        // Créer une image à partir du fichier uploadé
        switch ($fileType) {
            case 'image/jpeg':
            case 'image/jpg':
                $image = imagecreatefromjpeg($_FILES['photoProfil']['tmp_name']);
                break;
            case 'image/png':
                $image = imagecreatefrompng($_FILES['photoProfil']['tmp_name']);
                break;
            case 'image/gif':
                $image = imagecreatefromgif($_FILES['photoProfil']['tmp_name']);
                break;
            default:
                throw new Exception('Type non supporté');
        }
        
        if (!$image) {
            throw new Exception('Impossible de créer l\'image');
        }
        
        // Sauvegarder en PNG
        if (imagepng($image, $photoPath)) {
            imagedestroy($image);
            echo json_encode(['success' => true, 'message' => 'Photo mise à jour avec succès']);
        } else {
            throw new Exception('Erreur sauvegarde image');
        }
        
    } catch (Exception $e) {
        // Fallback: utiliser move_uploaded_file si la conversion échoue
        if (move_uploaded_file($_FILES['photoProfil']['tmp_name'], $photoPath)) {
            echo json_encode(['success' => true, 'message' => 'Photo mise à jour (fallback)']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erreur: ' . $e->getMessage()]);
        }
    }
    
} else {
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
}
?>
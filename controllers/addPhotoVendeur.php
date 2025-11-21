<?php
require_once 'pdo.php';

// Endpoint pour uploader la photo de profil vendeur
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['photoProfil'])) {
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée ou pas de fichier']);
    exit;
}

$code_vendeur = $_POST['codeVendeur'] ?? null;
if (!$code_vendeur) {
    echo json_encode(['success' => false, 'message' => 'Code vendeur manquant']);
    exit;
}

// Chemin final (public) — s'assurer que le dossier existe
$photoDir = __DIR__ . '/../../public/images/photoDeProfil/';
if (!is_dir($photoDir) && !mkdir($photoDir, 0755, true)) {
    echo json_encode(['success' => false, 'message' => 'Impossible de créer le dossier images']);
    exit;
}

$photoFilename = 'photo_profil_vendeur' . $code_vendeur . '.png';
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
    $err = $uploadErrors[$_FILES['photoProfil']['error']] ?? 'Erreur upload';
    echo json_encode(['success' => false, 'message' => $err]);
    exit;
}

// Vérifier type et taille
$allowed = ['image/jpeg','image/png','image/gif','image/webp'];
$type = mime_content_type($_FILES['photoProfil']['tmp_name']);
if (!in_array($type, $allowed)) {
    echo json_encode(['success' => false, 'message' => 'Type non autorisé: '.$type]);
    exit;
}
if ($_FILES['photoProfil']['size'] > 5 * 1024 * 1024) {
    echo json_encode(['success' => false, 'message' => 'Taille maximale 5MB']);
    exit;
}

// Supprimer ancienne photo si existe
if (file_exists($photoPath)) {
    @unlink($photoPath);
}

// Essayer de convertir et sauvegarder en PNG pour uniformiser
try {
    switch ($type) {
        case 'image/jpeg':
        case 'image/jpg':
            $img = imagecreatefromjpeg($_FILES['photoProfil']['tmp_name']);
            break;
        case 'image/png':
            $img = imagecreatefrompng($_FILES['photoProfil']['tmp_name']);
            break;
        case 'image/gif':
            $img = imagecreatefromgif($_FILES['photoProfil']['tmp_name']);
            break;
        case 'image/webp':
            if (function_exists('imagecreatefromwebp')) {
                $img = imagecreatefromwebp($_FILES['photoProfil']['tmp_name']);
            } else {
                $img = null;
            }
            break;
        default:
            $img = null;
    }

    if ($img) {
        // Optionnel: redimensionner pour garder un format carré (160x160 par ex.)
        $w = imagesx($img);
        $h = imagesy($img);
        $size = 320; // résolution cible
        $thumb = imagecreatetruecolor($size, $size);
        imagealphablending($thumb, false);
        imagesavealpha($thumb, true);
        // Remplir de transparent
        $transparent = imagecolorallocatealpha($thumb, 0, 0, 0, 127);
        imagefilledrectangle($thumb, 0, 0, $size, $size, $transparent);
        // Calculer crop central
        $min = min($w, $h);
        $srcX = intval(($w - $min) / 2);
        $srcY = intval(($h - $min) / 2);
        imagecopyresampled($thumb, $img, 0, 0, $srcX, $srcY, $size, $size, $min, $min);
        if (!imagepng($thumb, $photoPath)) {
            throw new Exception('Erreur sauvegarde image');
        }
        imagedestroy($thumb);
        imagedestroy($img);
        echo json_encode(['success' => true, 'message' => 'Photo mise à jour']);
        exit;
    }
} catch (Throwable $e) {
    // continue vers fallback
}

// Fallback : move_uploaded_file
if (move_uploaded_file($_FILES['photoProfil']['tmp_name'], $photoPath)) {
    echo json_encode(['success' => true, 'message' => 'Photo mise à jour (fallback)']);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Impossible de sauvegarder le fichier']);
exit;
?>
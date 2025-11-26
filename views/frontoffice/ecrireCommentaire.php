<?php 
session_start();
require_once "../../controllers/pdo.php";

$productId = intval($_GET['id'] ?? 0);

if ($productId === 0) {
    die("Produit non spécifié.");
}

$sqlProduit = "SELECT p.nom AS nom_produit FROM _produit p WHERE p.idProduit = ?";
$stmtProduit = $pdo->prepare($sqlProduit);
$stmtProduit->execute([$productId]);
$produit = $stmtProduit->fetch(PDO::FETCH_ASSOC);

if (!$produit) {
    die("Produit introuvable.");
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $clientId = intval($_SESSION['user_id'] ?? 0);
        $note = intval($_POST['note'] ?? 0);
        $sujet = trim($_POST['sujet'] ?? '');
        $message = trim($_POST['message'] ?? '');

        if ($clientId === 0) {
            $errors[] = "Vous devez être connecté pour laisser un avis.";
        }
        if ($productId === 0) {
            $errors[] = "Produit invalide.";
        }
        if ($note === 0 || $note < 1 || $note > 5) {
            $errors[] = "Veuillez sélectionner une note entre 1 et 5 étoiles.";
        }
        if (empty($sujet)) {
            $errors[] = "Le sujet est obligatoire.";
        }
        if (empty($message)) {
            $errors[] = "Le message est obligatoire.";
        }
        if (strlen($message) < 10) {
            $errors[] = "Le message doit contenir au moins 10 caractères.";
        }

        if (empty($errors)) {
            $fileName = null;
            
            if (!empty($_FILES['photo']['name'])) {
                $targetDir = $_SERVER['DOCUMENT_ROOT'] . "/images/imagesAvis/";
                
                // Créer le dossier s'il n'existe pas
                if (!is_dir($targetDir)) {
                    mkdir($targetDir, 0755, true);
                }
                
                $fileExtension = strtolower(pathinfo($_FILES["photo"]["name"], PATHINFO_EXTENSION));
                $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                
                if (in_array($fileExtension, $allowedExtensions)) {
                    $fileName = uniqid('avis_') . '.' . $fileExtension;
                    $targetFile = $targetDir . $fileName;

                    // ⚠️ LIGNE MANQUANTE - Envoi effectif du fichier vers le serveur
                    if (!move_uploaded_file($_FILES["photo"]["tmp_name"], $targetFile)) {
                        $errors[] = "Erreur lors de l'upload de l'image.";
                        $fileName = null;
                    } else {
                        $fileName = "/images/imagesAvis/" . $fileName;
                    }
                } else {
                    $errors[] = "Format d'image non autorisé. Utilisez JPG, PNG ou GIF.";
                }
            }

            if (empty($errors)) {
                $sqlAvis = "INSERT INTO _avis (idProduit, idClient, titreAvis, contenuAvis, note, dateAvis) 
                            VALUES (:idProduit, :idClient, :titre, :contenu, :note, CURDATE())";
                $stmt = $pdo->prepare($sqlAvis);
                $stmt->execute([
                    ':idProduit' => $productId,
                    ':idClient' => $clientId,
                    ':titre' => $sujet,
                    ':contenu' => $message,
                    ':note' => $note
                ]);

                if ($fileName) {
                    $sqlImageAvis = "INSERT INTO _imageAvis (idProduit, idClient, URL) 
                                    VALUES (:idProduit, :idClient, :urlImage)";
                    $stmtImageAvis = $pdo->prepare($sqlImageAvis);
                    $stmtImageAvis->execute([
                        ':idProduit' => $productId,
                        ':idClient' => $clientId,
                        ':urlImage' => $fileName 
                    ]);
                }

                header("Location: produit.php?id=" . $productId);
                exit;
            }
        }
    } catch(PDOException $e) {
        $errors[] = "Erreur lors de l'insertion de l'avis : " . $e->getMessage();
    }
}
?>
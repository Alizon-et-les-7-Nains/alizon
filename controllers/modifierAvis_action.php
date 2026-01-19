<?php
require_once "pdo.php";
session_start();

/* ==========================
   Sécurité
========================== */
if (!isset($_SESSION['user_id'])) {
    header("Location: ../frontoffice/connexionClient.php");
    exit();
}

if (!isset($_POST['idProduit'])) {
    die("ID produit manquant.");
}

$idClient  = $_SESSION['user_id'];
$idProduit = (int)$_POST['idProduit'];

/* ==========================
   Fonction MAJ note produit
========================== */
function updateNoteProduit(PDO $pdo, int $idProduit): void {
    $stmt = $pdo->prepare("
        SELECT AVG(note) AS moyenne
        FROM _avis
        WHERE idProduit = ?
    ");
    $stmt->execute([$idProduit]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    $moyenne = $result['moyenne'] ?? 0;

    $stmt2 = $pdo->prepare("
        UPDATE _produit
        SET note = ?
        WHERE idProduit = ?
    ");
    $stmt2->execute([$moyenne, $idProduit]);
}

/* ==========================
   Récupérer l'avis existant
========================== */
$stmt = $pdo->prepare("
    SELECT titreAvis, note, contenuAvis
    FROM _avis
    WHERE idProduit = ? AND idClient = ?
");
$stmt->execute([$idProduit, $idClient]);
$avis = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$avis) {
    die("Avis introuvable.");
}

/* ==========================
   Valeurs (modification partielle)
========================== */
$titre = trim($_POST['titreAvis'] ?? '');
$contenu = trim($_POST['contenuAvis'] ?? '');

$titre   = $titre !== '' ? $titre : $avis['titreAvis'];
$contenu = $contenu !== '' ? $contenu : $avis['contenuAvis'];

$note = isset($_POST['note']) && $_POST['note'] !== ''
    ? floatval($_POST['note'])
    : $avis['note'];

/* ==========================
   Mise à jour de l'avis
========================== */
$stmt = $pdo->prepare("
    UPDATE _avis
    SET titreAvis = ?, note = ?, contenuAvis = ?, dateAvis = CURRENT_DATE
    WHERE idProduit = ? AND idClient = ?
");
$stmt->execute([$titre, $note, $contenu, $idProduit, $idClient]);

updateNoteProduit($pdo, $idProduit);

/* ==========================
   Gestion de l'image (optionnelle)
========================== */
if (!empty($_FILES['url']['name'])) {

    if ($_FILES['url']['error'] !== UPLOAD_ERR_OK) {
        die("Erreur lors de l'envoi de l'image.");
    }

    $allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
    if (!in_array($_FILES['url']['type'], $allowedTypes)) {
        die("Format d'image non autorisé.");
    }

    $uploadDir = "../public/uploads/avis/";
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $extension = pathinfo($_FILES['url']['name'], PATHINFO_EXTENSION);
    $fileName = uniqid("avis_", true) . "." . $extension;
    $filePath = $uploadDir . $fileName;

    if (!move_uploaded_file($_FILES['url']['tmp_name'], $filePath)) {
        die("Impossible d'enregistrer l'image.");
    }

    // URL stockée en base
    $imageUrl = "/public/uploads/avis/" . $fileName;

    // Vérifier si une image existe déjà
    $stmtImg = $pdo->prepare("
        SELECT idClient
        FROM _imageAvis
        WHERE idClient = ? AND idProduit = ?
    ");
    $stmtImg->execute([$idClient, $idProduit]);

    if ($stmtImg->fetch()) {
        // UPDATE
        $stmtUpdate = $pdo->prepare("
            UPDATE _imageAvis
            SET url = ?
            WHERE idClient = ? AND idProduit = ?
        ");
        $stmtUpdate->execute([$imageUrl, $idClient, $idProduit]);
    } else {
        // INSERT
        $stmtInsert = $pdo->prepare("
            INSERT INTO _imageAvis (idClient, idProduit, url)
            VALUES (?, ?, ?)
        ");
        $stmtInsert->execute([$idClient, $idProduit, $imageUrl]);
    }
}


header("Location: ../views/frontoffice/mesAvis.php");
exit();

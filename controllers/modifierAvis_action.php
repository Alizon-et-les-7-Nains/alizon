<?php
require_once "pdo.php";
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../frontoffice/connexionClient.php");
    exit();
}

$note = isset($_POST['note']) && $_POST['note'] !== "" ? floatval($_POST['note']) : null;

if ($note === null) {
    die("Veuillez sélectionner une note.");
}

$idClient = $_SESSION['user_id'];

// Vérifier que tous les champs existent
if (!isset($_POST['idProduit'], $_POST['titreAvis'], $_POST['note'], $_POST['contenuAvis'])) {
    die("Formulaire incomplet.");
}

$idProduit = intval($_POST['idProduit']);
$titre = trim($_POST['titreAvis']);
$note = floatval($_POST['note']);
$contenu = trim($_POST['contenuAvis']);

// Mise à jour
$stmt = $pdo->prepare("
    UPDATE _avis 
    SET titreAvis = ?, note = ?, contenuAvis = ?, dateAvis = CURRENT_DATE
    WHERE idProduit = ? AND idClient = ?
");

$ok = $stmt->execute([$titre, $note, $contenu, $idProduit, $idClient]);

if ($ok) {
    header("Location: ../views/frontoffice/mesAvis.php");
    exit();
} else {
    echo "Erreur lors de la mise à jour.";
}

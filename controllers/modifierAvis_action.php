<?php
require_once "pdo.php";
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../frontoffice/connexionClient.php");
    exit();
}

function updateNoteProduit(PDO $pdo, int $idProduit) {
    $stmt = $pdo->prepare("
        SELECT AVG(note) AS moyenne
        FROM _avis
        WHERE idProduit = ?
    ");
    $stmt->execute([$idProduit]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    $moyenne = $result['moyenne'] !== null ? $result['moyenne'] : 0;

    $stmt2 = $pdo->prepare("
        UPDATE _produit
        SET note = ?
        WHERE idProduit = ?
    ");
    $stmt2->execute([$moyenne, $idProduit]);
}


$note = isset($_POST['note']) && $_POST['note'] !== "" ? floatval($_POST['note']) : null;

if ($note === null) {
    die("Veuillez sélectionner une note.");
}

$idClient = $_SESSION['user_id'];

if (!isset($_POST['idProduit'], $_POST['titreAvis'], $_POST['note'], $_POST['contenuAvis'])) {
    die("Formulaire incomplet.");
}

$idProduit = intval($_POST['idProduit']);
$titre = trim($_POST['titreAvis']);
$note = floatval($_POST['note']);
$contenu = trim($_POST['contenuAvis']);

$stmt = $pdo->prepare("
    UPDATE _avis 
    SET titreAvis = ?, note = ?, contenuAvis = ?, dateAvis = CURRENT_DATE
    WHERE idProduit = ? AND idClient = ?
");

$ok = $stmt->execute([$titre, $note, $contenu, $idProduit, $idClient]);


updateNoteProduit($pdo, $idProduit);

if ($ok) {
    header("Location: ../views/frontoffice/mesAvis.php");
    exit();
} else {
    echo "Erreur lors de la mise à jour.";
}

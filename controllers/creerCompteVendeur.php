<?php
session_start();
require_once "pdo.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../views/backoffice/CreerCompteVendeur.php');
    exit;
}

// Récupération des données
$nom = trim($_POST['nom'] ?? '');
$prenom = trim($_POST['prenom'] ?? '');
$email = trim($_POST['email'] ?? '');
$noTelephone = trim($_POST['noTelephone'] ?? '');
$pseudo = trim($_POST['pseudo'] ?? '');
$dateNaissance = $_POST['dateNaissance'] ?? '';
$noSiren = trim($_POST['noSiren'] ?? '');
$raisonSocial = trim($_POST['raisonSocial'] ?? '');
$mdp_clair = $_POST['mdp'] ?? '';
$confirmer_mdp = $_POST['confirmer_mdp'] ?? '';
$adresse = $_POST['idAdresse'] ?? '';
$ville = $_POST['ville'] ?? '';
$region = $_POST['region'] ?? '';
$complAdresse = $_POST['complAdresse'] ?? '';
$codePostal = $_POST['codePostal'] ?? '';
$pays = $_POST['pays'] ?? '';


$lat = $_POST['lat'] !== '' ? (float)$_POST['lat'] : null;
$lng = $_POST['lng'] !== '' ? (float)$_POST['lng'] : null;

$errors = [];

// --- VALIDATIONS ---
if ($mdp_clair !== $confirmer_mdp) {
    $errors[] = "Les mots de passe ne correspondent pas.";
}
if (strlen($mdp_clair) < 12) {
    $errors[] = "Le mot de passe doit contenir au moins 12 caractères.";
}

$telephone_clean = preg_replace('/[^0-9]/', '', $noTelephone);
if (!preg_match('/^0[67][0-9]{8}$/', $telephone_clean)) {
    $errors[] = "Le numéro de téléphone doit commencer par 06 ou 07.";
}

// --- VÉRIFICATION UNICITÉ ---
if (empty($errors)) {
    try {
        $sql_check = "SELECT COUNT(*) FROM _vendeur WHERE pseudo = ? OR email = ? OR noSiren = ?";
        $stmt_check = $pdo->prepare($sql_check);
        $stmt_check->execute([$pseudo, $email, $noSiren]);
        if ($stmt_check->fetchColumn() > 0) {
            $errors[] = "Un compte avec ce pseudo, cet email ou ce numéro SIREN existe déjà.";
        }
    } catch (PDOException $e) {
        $errors[] = "Erreur de base de données : " . $e->getMessage();
    }
}

// --- INSERTION ---
if (empty($errors)) {
    $mdp_hash = password_hash($mdp_clair, PASSWORD_DEFAULT);
    try {
        $sql_adresse = "INSERT INTO _adresseVendeur(adresse, region, codePostal, ville, pays, complementAdresse latitude, longitude) 
        VALUEs (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt_adresse = $pdo->prepare($sql_adresse);
        $stmt_adresse->execute([$adresse, $region, $codePostal, $ville, $pays, $complAdresse, $lat, $lng]);
        $idAdr = $pdo->lastInsertId(); 

        $sql_insert = "INSERT INTO _vendeur (nom, prenom, email, noTelephone, pseudo, 
                          dateNaissance, idAdresse, noSiren, raisonSocial, mdp) 
                       VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt_insert = $pdo->prepare($sql_insert);
        $stmt_insert->execute([
            $nom,
            $prenom,
            $email,
            $telephone_clean,
            $pseudo,
            $dateNaissance,
            $idAdr,        
            $noSiren,
            $raisonSocial,
            $mdp_hash
        ]);

        $_SESSION['message'] = "Votre compte vendeur a été créé avec succès.";
        header('Location: ../views/backoffice/connexion.php');
        exit;

    } catch (PDOException $e) {
        $errors[] = "Erreur lors de l'insertion : " . $e->getMessage();
    }
}
// --- REDIRECTION EN CAS D'ERREUR ---
$_SESSION['form_errors'] = $errors;
$_SESSION['form_data'] = $_POST;
header('Location: ../views/backoffice/CreerCompteVendeur.php');
exit;
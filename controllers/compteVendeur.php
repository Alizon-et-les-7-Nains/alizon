<?php 
require_once 'pdo.php';
require_once 'auth.php';

$code_vendeur = $_SESSION['id'];
$idAdresse = 1;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Récupération des données du formulaire
    $pseudo = $_POST['pseudo'] ?? '';
    $nom = $_POST['nom'] ?? '';
    $prenom = $_POST['prenom'] ?? '';
    $email = $_POST['email'] ?? '';
    $dateNaissance = $_POST['dateNaissance'] ?? '';
    $telephone = $_POST['telephone'] ?? '';
    $codePostal = $_POST['codePostal'] ?? '';
    $adresse = $_POST['adresse'] ?? '';
    $pays = $_POST['pays'] ?? '';
    $ville = $_POST['ville'] ?? '';
    $region = $_POST['region'] ?? '';
    $raisonSociale = $_POST['raisonSociale'] ?? '';
    $noSiren = $_POST['noSiren'] ?? '';

    // Gestion des valeurs NULL pour dateNaissance
    $dateNaissance = ($dateNaissance === '') ? null : $dateNaissance;

    // Mise à jour du vendeur
    $stmt = $pdo->prepare("
        UPDATE _vendeur 
        SET pseudo = :pseudo, 
            nom = :nom, 
            prenom = :prenom, 
            email = :email, 
            dateNaissance = :dateNaissance,
            noTelephone = :telephone,
            raisonSocial = :raisonSocial,
            noSiren = :noSiren
        WHERE codeVendeur = :code_vendeur
    ");

    $stmt->execute([
        ':pseudo' => $pseudo,
        ':nom' => $nom,
        ':prenom' => $prenom,
        ':email' => $email,
        ':dateNaissance' => $dateNaissance,
        ':telephone' => $telephone,
        ':raisonSocial' => $raisonSociale,
        ':noSiren' => $noSiren,
        ':code_vendeur' => $code_vendeur
    ]);

    // Mise à jour de l'adresse
    $stmt = $pdo->prepare("
        UPDATE _adresseVendeur 
        SET adresse = :adresse,
            pays = :pays,
            ville = :ville, 
            codePostal = :codePostal,
            region = :region
        WHERE idAdresse = :idAdresse
    ");

    $stmt->execute([
        ':adresse' => $adresse,
        ':pays' => $pays,
        ':ville' => $ville,
        ':codePostal' => $codePostal,
        ':region' => $region,
        ':idAdresse' => $idAdresse
    ]);
}

    header('Location: ../views/backoffice/compteVendeur.php');
    exit;
?>
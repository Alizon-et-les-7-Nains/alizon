<?php
    require_once "pdo.php";

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nom_contact = $_POST['nom'] ?? '';
    $prenom = $_POST['prenom_contact'] ?? '';
    $email = $_POST['email'] ?? '';
    $noTelephone = $_POST['num_tel'] ?? '';
    $pseudo = $_POST['nom_utilisateur'] ?? '';
    $mdp = $_POST['mdp'] ?? '';
    $dateNaissance = $_POST['date_naissance'] ?? '';
    $noSiren = $_POST['num_siren'] ?? '';
    $idAdresse = $_POST['adresse_entreprise'] ?? '';
    $raisonSocial = $_POST['raisonSocial'] ?? '';

    $sql = "INSERT INTO _vendeur 
        (nom, prenom, email, tel, utilisateur, mdp, dateNaissance, siren, adresse, sociale)
        VALUES (:nom, :prenom, :email, :tel, :utilisateur, :mdp, :dateNaissance, :siren, :adresse, :sociale)";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        ':nom' => $nom,
        ':prenom' => $prenom,
        ':email' => $email,
        ':tel' => $noTelephone,
        ':utilisateur' => $pseudo, 
        ':mdp' => $mdp,
        ':dateNaissance' => $dateNaissance,
        ':siren' => $noSiren,
        ':adresse' => $idAdresse,
        ':sociale' => $raisonSocial,
    ]);
    }


    session_start();  
    $id_session = session_id();
    $_SESSION['id_session'] = $id_session;
    header('Location: ../views/backoffice/accueil.php');
    exit();
?>
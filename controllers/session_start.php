<?php
    require_once "pdo.php";

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $pseudo = $_POST['pseudo'] ?? '';
    $prenom = $_POST['prenom'] ?? '';
    $nom = $_POST['nom'] ?? '';
    $email = $_POST['email'] ?? '';
    $num_tel = $_POST['telephone'] ?? '';
    $mdp = $_POST['mdp'] ?? '';
    $date_naissance = $_POST['birthdate'] ?? '';

    $sql = "INSERT INTO _client 
        (dateNaissance, prenom, nom, email, mdp, noTelephone, pseudo)
        VALUES (:dateNaissance, :prenom, :nom, :email, :mdp, :noTelephone, :pseudo)";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        ':dateNaissance' => $date_naissance,
        ':prenom' => $prenom,
        ':nom' => $nom,
        ':email' => $email,
        ':mdp' => $mdp,
        ':noTelephone' => $num_tel,
        ':pseudo' => $pseudo,
    ]);
    }

    $lastId = $pdo->lastInsertId();

    session_start();  
    $_SESSION['user_id'] = $lastId;

    header('Location: ../views/frontoffice/accueilConnecte.php');
    exit();
?>
<?php
    session_start();  
    require_once "pdo.php";

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        $nom = $_POST['nom'] ?? '';
        $prenom = $_POST['prenom'] ?? '';
        $email = $_POST['email'] ?? '';
        $noTelephone = $_POST['noTelephone'] ?? '';
        $pseudo = $_POST['pseudo'] ?? '';
        $mdp = $_POST['mdp'] ?? '';
        $dateNaissance = $_POST['dateNaissance'] ?? '';
        $noSiren = $_POST['noSiren'] ?? '';
        $adresse = $_POST['adresse'] ?? '';
        $raisonSocial = $_POST['raisonSocial'] ?? '';

        $sql = "INSERT INTO _vendeur 
            (nom, prenom, email, noTelephone, pseudo, mdp, dateNaissance, noSiren, adresse, raisonSocial)
            VALUES (:nom, :prenom, :email, :noTelephone, :pseudo, :mdp, :dateNaissance, :noSiren, :adresse, :raisonSocial)";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            ':nom' => $nom,
            ':prenom' => $prenom,
            ':email' => $email,
            ':noTelephone' => $noTelephone,
            ':pseudo' => $pseudo, 
            ':mdp' => $mdp,
            ':dateNaissance' => $dateNaissance,
            ':noSiren' => $noSiren,
            ':adresse' => $adresse,
            ':raisonSocial' => $raisonSocial,
        ]);
    }

    $id_session = session_id();
    $_SESSION['id_session'] = $id_session;
    header('Location: ../views/backoffice/accueil.php');
    exit();
?>
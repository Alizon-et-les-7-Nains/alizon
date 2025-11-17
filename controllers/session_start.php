<?php
    require_once "pdo.php";

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                
        $pseudo = $_POST['pseudo'] ?? '';
        $prenom = $_POST['prenom'] ?? '';
        $nom = $_POST['nom'] ?? '';
        $email = $_POST['email'] ?? '';
        $num_tel = $_POST['telephone'] ?? '';
        $mdp = $_POST['motdepasse'] ?? '';
        $date_naissance = $_POST['birthdate'] ?? '';
        
        $nouveauClient = ("INSERT INTO _client (dateNaissance, prenom, nom, email, mdp, noTelephone, pseudo)
        VALUES ('$date_naissance', '$prenom', '$nom', '$email', '$mdp', '$num_tel', '$pseudo')");

        if ($pdo->query($nouveauClient) === false) {
            throw new Exception("Erreur lors de la création d'un : " . implode(', ', $pdo->errorInfo()));
        }

                
        }

    session_start();  
    $id_session = session_id();
    $_SESSION['id_session'] = $id_session;
    header('Location: ../views/frontoffice/acceuilConnecte.php');
    exit();
?>
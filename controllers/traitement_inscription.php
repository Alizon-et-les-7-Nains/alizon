<?php
require_once "pdo.php";
session_start();

// Vérification si le formulaire a été soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupération et nettoyage des données
    $pseudo = trim($_POST['pseudo'] ?? '');
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $birthdate = trim($_POST['birthdate'] ?? '');
    $telephone = trim($_POST['telephone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $mdp_clair = $_POST['mdp'] ?? '';
    $cmdp = $_POST['cmdp'] ?? '';
    
    // Validation des données
    $errors = [];
    
    // Vérifier que les mots de passe correspondent
    if ($mdp_clair !== $cmdp) {
        $errors[] = "Les mots de passe ne correspondent pas.";
    }
    
    // Validation du format de date (jj/mm/aaaa)
    if (!preg_match('/^([0][1-9]|[1-2][0-9]|3[0-1])\/(0[1-9]|1[0-2])\/[0-9]{4}$/', $birthdate)) {
        $errors[] = "Format de date invalide. Utilisez jj/mm/aaaa.";
    }
    
    // Validation du téléphone
    if (!preg_match('/^0[67](\s[0-9]{2}){4}$/', $telephone) && !preg_match('/^0[67]([0-9]{2}){4}$/', $telephone)) {
        $errors[] = "Numéro de téléphone invalide. Doit commencer par 06 ou 07.";
    }
    
    // Nettoyer le téléphone (enlever les espaces)
    $telephone_clean = preg_replace('/\s+/', '', $telephone);
    
    // Vérifier si l'email ou le téléphone existe déjà
    $sql_check = "SELECT COUNT(*) FROM _client WHERE email = ? OR noTelephone = ?";
    $stmt_check = $pdo->prepare($sql_check);
    $stmt_check->execute([$email, $telephone_clean]);
    $count = $stmt_check->fetchColumn();
    
    if ($count > 0) {
        $errors[] = "Un compte avec cet email ou ce numéro de téléphone existe déjà.";
    }
    
    // Si aucune erreur, procéder à l'inscription
    if (empty($errors)) {
        // Hachage du mot de passe avec password_hash
        $mdp_hash = password_hash($mdp_clair, PASSWORD_DEFAULT);
        
        // Convertir la date au format MySQL
        $date_mysql = DateTime::createFromFormat('d/m/Y', $birthdate)->format('Y-m-d');
        
        try {
            // Insertion dans la base de données
            $sql_insert = "INSERT INTO _client (pseudo, nom, prenom, dateNaissance, noTelephone, email, mdp) 
                          VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt_insert = $pdo->prepare($sql_insert);
            $stmt_insert->execute([
                $pseudo,
                $nom,
                $prenom,
                $date_mysql,
                $telephone_clean,
                $email,
                $mdp_hash
            ]);
            
            // Récupérer l'ID du nouveau client
            $id_client = $pdo->lastInsertId();
            
            // Connecter automatiquement l'utilisateur
            $_SESSION['user_id'] = $id_client;
            $_SESSION['user_email'] = $email;
            $_SESSION['user_name'] = $prenom . ' ' . $nom;
            $_SESSION['user_prenom'] = $prenom;
            $_SESSION['user_nom'] = $nom;
            
            // Redirection vers la page d'accueil connecté
            header('Location: ../views/frontoffice/accueilConnecte.php');
            exit;
            
        } catch (PDOException $e) {
            $errors[] = "Erreur lors de l'inscription : " . $e->getMessage();
        }
    }
    
    // Si erreurs, les stocker en session et rediriger vers le formulaire
    if (!empty($errors)) {
        $_SESSION['inscription_errors'] = $errors;
        $_SESSION['inscription_data'] = $_POST;
        var_dump($errors);
        var_dump($_POST);
        //header('Location: ../views/frontoffice/inscription.php');
        exit;
    }
} else {
    // Si accès direct au fichier, rediriger vers la page d'inscription
    //header('Location: ../views/frontoffice/inscription.php');
    exit;
}?>
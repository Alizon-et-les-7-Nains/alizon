<?php
session_start();
require_once "pdo.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupération des données
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $noTelephone = trim($_POST['noTelephone'] ?? '');
    $pseudo = trim($_POST['pseudo'] ?? '');
    $dateNaissance = $_POST['dateNaissance'] ?? '';
    $noSiren = trim($_POST['noSiren'] ?? '');
    $idAdresse = trim($_POST['idAdresse'] ?? '');
    $raisonSocial = trim($_POST['raisonSocial'] ?? '');
    $mdp_clair = $_POST['mdp'] ?? '';
    $confirmer_mdp = $_POST['confirmer_mdp'] ?? '';
    
    $errors = [];
    
    // Validation des mots de passe
    if ($mdp_clair !== $confirmer_mdp) {
        $errors[] = "Les mots de passe ne correspondent pas.";
    }
    
    // Validation de la force du mot de passe
    if (strlen($mdp_clair) < 12) {
        $errors[] = "Le mot de passe doit contenir au moins 12 caractères.";
    }
    if (!preg_match('/[a-z]/', $mdp_clair)) {
        $errors[] = "Le mot de passe doit contenir au moins une minuscule.";
    }
    if (!preg_match('/[A-Z]/', $mdp_clair)) {
        $errors[] = "Le mot de passe doit contenir au moins une majuscule.";
    }
    if (!preg_match('/[0-9]/', $mdp_clair)) {
        $errors[] = "Le mot de passe doit contenir au moins un chiffre.";
    }
    if (!preg_match('/[^a-zA-Z0-9]/', $mdp_clair)) {
        $errors[] = "Le mot de passe doit contenir au moins un caractère spécial.";
    }
    
    // Validation du SIREN (9 chiffres)
    if (!preg_match('/^[0-9]{9}$/', $noSiren)) {
        $errors[] = "Le numéro SIREN doit contenir exactement 9 chiffres.";
    }
    
    // Validation de l'email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "L'adresse email n'est pas valide.";
    }
    
    // Validation du téléphone
    $telephone_clean = preg_replace('/[^0-9]/', '', $noTelephone);
    if (!preg_match('/^0[67][0-9]{8}$/', $telephone_clean)) {
        $errors[] = "Le numéro de téléphone n'est pas valide. Il doit commencer par 06 ou 07.";
    }
    
    // Vérifier l'unicité du pseudo et de l'email
    $sql_check = "SELECT COUNT(*) FROM _vendeur WHERE pseudo = ? OR email = ? OR noSiren = ?";
    $stmt_check = $pdo->prepare($sql_check);
    $stmt_check->execute([$pseudo, $email, $noSiren]);
    $count = $stmt_check->fetchColumn();
    
    if ($count > 0) {
        $errors[] = "Un compte avec ce pseudo, cet email ou ce numéro SIREN existe déjà.";
    }
    
    // Si aucune erreur, procéder à l'inscription
    if (empty($errors)) {
        // Hachage du mot de passe
        $mdp_hash = password_hash($mdp_clair, PASSWORD_DEFAULT);
        
        try {
            // Insertion du vendeur (valide = 0 par défaut)
            $sql_insert = "INSERT INTO _vendeur (nom, prenom, email, noTelephone, pseudo, 
                          dateNaissance, noSiren, idAdresse, raisonSocial, mdp, valide, dateCreation) 
                          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0, NOW())";
            
            $stmt_insert = $pdo->prepare($sql_insert);
            $stmt_insert->execute([
                $nom,
                $prenom,
                $email,
                $telephone_clean,
                $pseudo,
                $dateNaissance,
                $noSiren,
                $idAdresse,
                $raisonSocial,
                $mdp_hash
            ]);
            
            // Redirection avec message de succès
            $_SESSION['message'] = "Votre compte vendeur a été créé avec succès. Il doit être validé par un administrateur avant de pouvoir vous connecter.";
            header('Location: ../views/backoffice/connexion.php');
            exit;
            
        } catch (PDOException $e) {
            $errors[] = "Erreur lors de la création du compte : " . $e->getMessage();
        }
    }
    
    // S'il y a des erreurs, les stocker en session
    if (!empty($errors)) {
        $_SESSION['form_errors'] = $errors;
        $_SESSION['form_data'] = [
            'nom' => $nom,
            'prenom' => $prenom,
            'email' => $email,
            'noTelephone' => $noTelephone,
            'pseudo' => $pseudo,
            'dateNaissance' => $dateNaissance,
            'noSiren' => $noSiren,
            'idAdresse' => $idAdresse,
            'raisonSocial' => $raisonSocial
        ];
        header('Location: ../views/backoffice/CreerCompteVendeur.php');
        exit;
    }
} else {
    header('Location: ../views/backoffice/CreerCompteVendeur.php');
    exit;
}
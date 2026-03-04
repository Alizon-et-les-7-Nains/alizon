<?php
session_start();
require_once "pdo.php";

// Vérifier que le vendeur est connecté
if (!isset($_SESSION['idVendeur'])) {
    header('Location: ../views/backoffice/connexion.php');
    exit;
}

$idVendeur = $_SESSION['idVendeur'];

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
    $adresse = $_POST['adresse'] ?? '';
    $codePostal = $_POST['codePostal'] ?? '';
    $ville = $_POST['ville'] ?? '';
    $region = $_POST['region'] ?? '';
    $pays = $_POST['pays'] ?? '';
    $lat = $_POST['lat'] !== '' ? (float)$_POST['lat'] : null;
    $lng = $_POST['lng'] !== '' ? (float)$_POST['lng'] : null;


    $errors = [];
    
    // Validation des mots de passe (seulement si fourni)
    if (!empty($mdp_clair) || !empty($confirmer_mdp)) {
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
    
    // Vérifier l'unicité du pseudo, email et SIREN (en excluant le vendeur actuel)
    $sql_check = "SELECT COUNT(*) FROM _vendeur WHERE (pseudo = ? OR email = ? OR noSiren = ?) AND idVendeur != ?";
    $stmt_check = $pdo->prepare($sql_check);
    $stmt_check->execute([$pseudo, $email, $noSiren, $idVendeur]);
    $count = $stmt_check->fetchColumn();
    
    if ($count > 0) {
        $errors[] = "Un compte avec ce pseudo, cet email ou ce numéro SIREN existe déjà.";
    }
    
    // Si aucune erreur, procéder à la modification
    if (empty($errors)) {
        try {
            // Mise à jour du vendeur
            if (!empty($mdp_clair)) {
                // Si un nouveau mot de passe est fourni, le hacher et l'inclure dans l'UPDATE
                $mdp_hash = password_hash($mdp_clair, PASSWORD_DEFAULT);
                $sql_update = "UPDATE _vendeur SET nom = ?, prenom = ?, email = ?, noTelephone = ?, 
                              pseudo = ?, dateNaissance = ?, noSiren = ?, idAdresse = ?, 
                              raisonSocial = ?, mdp = ? WHERE idVendeur = ?";
                
                $stmt_update = $pdo->prepare($sql_update);
                $stmt_update->execute([
                    $nom,
                    $prenom,
                    $email,
                    $telephone_clean,
                    $pseudo,
                    $dateNaissance,
                    $noSiren,
                    $idAdresse,
                    $raisonSocial,
                    $mdp_hash,
                    $idVendeur
                ]);             
            } else {
                // Sans modifier le mot de passe
                $sql_update = "UPDATE _vendeur SET nom = ?, prenom = ?, email = ?, noTelephone = ?, 
                              pseudo = ?, dateNaissance = ?, noSiren = ?, idAdresse = ?, 
                              raisonSocial = ? WHERE idVendeur = ?";
                
                $stmt_update = $pdo->prepare($sql_update);
                $stmt_update->execute([
                    $nom,
                    $prenom,
                    $email,
                    $telephone_clean,
                    $pseudo,
                    $dateNaissance,
                    $noSiren,
                    $idAdresse,
                    $raisonSocial,
                    $idVendeur
                ]);
            }

            $sql_update = "UPDATE _adresseVendeur SET adresse = ?, codePostal = ?, ville = ?,
                           region = ?, pays = ? WHERE idAdresse = ?";

            $stmt_update = $pdo->prepare($sql_update);
            $stmt_update->execute([
                $adresse,
                $codePostal,
                $ville,
                $region,
                $pays,
                $idAdresse
            ]);  
            
            // Redirection avec message de succès
            $_SESSION['message'] = "Votre compte a été modifié avec succès.";
            header('Location: ../views/backoffice/compteVendeur.php');
            exit;
            
        } catch (PDOException $e) {
            $errors[] = "Erreur lors de la modification du compte : " . $e->getMessage();
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
        header('Location: ../views/backoffice/compteVendeur.php');
        exit;
    }
} else {
    header('Location: ../views/backoffice/compteVendeur.php');
    exit;
}?>
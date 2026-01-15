<?php
session_start();
require_once "pdo.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $pseudo = trim($_POST['pseudo'] ?? '');
    $mdp_clair = $_POST['mdp'] ?? '';
    
    // Rechercher le vendeur par pseudo
    $sql = "SELECT codeVendeur, pseudo, mdp, nom, prenom, email, noTelephone, dateNaissance, 
                   noSiren, idAdresse, raisonSocial
            FROM _vendeur 
            WHERE pseudo = ?";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$pseudo]);
    $vendeur = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($vendeur) {
        // Vérifier le mot de passe avec password_verify
        if (password_verify($mdp_clair, $vendeur['mdp'])) {
                $_SESSION['vendeur_id'] = $vendeur['codeVendeur'];
                $_SESSION['vendeur_pseudo'] = $vendeur['pseudo'];
                $_SESSION['vendeur_nom'] = $vendeur['nom'];
                $_SESSION['vendeur_prenom'] = $vendeur['prenom'];
                $_SESSION['vendeur_email'] = $vendeur['email'];
                $_SESSION['vendeur_telephone'] = $vendeur['noTelephone'];
                $_SESSION['vendeur_raison_social'] = $vendeur['raisonSocial'];
                $_SESSION['vendeur_siren'] = $vendeur['noSiren'];
                $_SESSION['vendeur_adresse'] = $vendeur['idAdresse'];
                
                header('Location: ../views/backoffice/accueilVendeur.php');
                exit;

        } else {
            // Mot de passe incorrect
            header('Location: ../views/backoffice/connexion.php?error=1');
            exit;
        }
    } else {
        // Pseudo incorrect
        header('Location: ../views/backoffice/connexion.php?error=1');
        exit;
    }
} else {
    header('Location: ../views/backoffice/connexion.php');
    exit;
}?>
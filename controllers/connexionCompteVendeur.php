<?php
require_once "pdo.php";
session_start();

$pseudo = '';
$password_chiffre = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // On récupère les données
    $pseudo = trim($_POST['pseudo']);
    $password_chiffre = trim($_POST['password_chiffre']); // mdp chiffré par JS

    // Debug
    error_log("Tentative connexion Vendeur: " . $pseudo);

    // On cherche par pseudo les différents vendeur existants
    $sql = "SELECT codeVendeur, raisonSocial, noSiren, prenom, nom, email, mdp, dateNaissance, pseudo, noTelephone, idAdresse
        FROM _vendeur WHERE pseudo = ?";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$pseudo]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {

        // Vérification
        $mdp_bd = $user['mdp'];
        
        // json_encode pour chiffrer les mdp
        $mdp_input_normalized = json_encode($password_chiffre);
        $mdp_bd_normalized = json_encode($mdp_bd);
        
        // On retire les " ajoutés par json_encode
        $mdp_input_normalized = trim($mdp_input_normalized, '"');
        $mdp_bd_normalized = trim($mdp_bd_normalized, '"');
        
        // On compare
        if ($mdp_input_normalized === $mdp_bd_normalized) {
            
            // On enregistre les infos du vendeur en session
            $_SESSION['codeVendeur'] = $user['codeVendeur'];
            $_SESSION['pseudo'] = $user['pseudo'];
            $_SESSION['raisonSocial'] = $user['raisonSocial'];

            // Redirection
            header('Location: ../views/backoffice/accueil.php'); 
            exit;

        } else {
            // Mot de passe incorrect
            $_SESSION['message'] = "Mot de passe incorrect.";
            header('Location: ' . $_SERVER['HTTP_REFERER']); // On retourne à la page précédente
            exit;
        }
    } else {
        // Pseudo inconnu
        $_SESSION['message'] = "Aucun compte vendeur trouvé avec ce pseudo.";
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit;
    }
    
} else {
    // Si quelqu'un essaie d'accéder à la page sans soumettre le formulaire
    header('Location: ../views/backoffice/connexionVendeur.php');
    exit;
}
?>
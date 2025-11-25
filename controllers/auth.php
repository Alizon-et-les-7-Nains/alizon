<?php
// auth.php - Toujours en première ligne
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['id']) || empty($_SESSION['id'])) {
    header("Location: ../views/backoffice/connexion.php");
    exit();
}

// Vérifier le type d'utilisateur si nécessaire
if (isset($_SESSION['user_type']) && $_SESSION['user_type'] !== 'vendeur') {
    header("Location: ../views/backoffice/connexion.php");
    exit();
}
?>
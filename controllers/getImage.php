<?php
session_start();

// Vérifie si une photo est stockée dans la session
if (!empty($_SESSION['photo'])) {
    // Définir le type MIME de l'image
    header('Content-Type: image/jpeg');
    // Définir la taille pour le navigateur (optionnel mais propre)
    header('Content-Length: ' . strlen($_SESSION['photo']));
    // Envoyer le binaire de l'image
    echo $_SESSION['photo'];
    exit;
}

// Si aucune photo, renvoyer une image par défaut
header('Content-Type: image/png');
readfile('../../public/images/defaultImageProduit.png');
exit;
?>
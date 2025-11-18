<?php

require_once 'pdo.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Vider le panier en supprimant le cookie
    setcookie("produitPanier", "", time() - 3600, "/"); // Supprimer le cookie en le mettant à une date passée

    // Répondre avec un statut de succès
    http_response_code(200);
    echo json_encode(['status' => 'success', 'message' => 'Panier vidé avec succès.']);
} else if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    echo "Debug vider le panier connecte";
}

?>
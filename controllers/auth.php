<?php
session_start();
ob_start();

require_once 'pdo.php';

try {
    $pdo->beginTransaction();
    $isValidSTMT = $pdo->prepare(file_get_contents(__DIR__ . '/../queries/backoffice/auth.sql'));
    $isValidSTMT->execute([':id' => $_SESSION['id']]);
    $isValid = $isValidSTMT->fetchColumn();
    error_log("auth");

    if (!$_SESSION['session_id'] || password_verify($_SESSION['pass'], $isValid['mdp'])) {
        header('Location: ../backoffice/connexion.php?error=3');
        die();
    }
} catch (Exception $e) {
    header('Location: ../backoffice/connexion.php?error=0');
    die();
}

?>
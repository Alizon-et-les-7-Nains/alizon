<?php

ob_start();

require_once 'pdo.php';

session_start();

try {
    $pdo->beginTransaction();
    $isValidSTMT = $pdo->prepare(file_get_contents(__DIR__ . '/../queries/backoffice/auth.sql'));
    $isValidSTMT->execute([':id' => $_SESSION['id'], ':pass' => $_SESSION['pass']]);
    $isValid = $isValidSTMT->fetchColumn();

    if (!$_SESSION['session_id'] || !$isValid) {
        header('Location: ../backoffice/connexion.php?error=3');
        die();
    }
} catch (Exception $e) {
    header('Location: ../backoffice/connexion.php?error=0');
    die();
}

?>
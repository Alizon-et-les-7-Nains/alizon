<?php

require_once 'pdo.php';

session_start();

try {
    $pdo->beginTransaction();
    $isValidSTMT = $pdo->prepare(file_get_contents('../queries/backoffice/connexion.sql'));
    $isValidSTMT->execute([':pseudo' => $_SESSION['pseudo'], ':mdp' => $_SESSION['mdp']]);
    $isValid = $isValidSTMT->fetchColumn();

    if (!$_SESSION['id_session'] || !$isValid) {
        header('Location: ../views/backoffice/connexions.php');
        die();
    }
} catch (Exception $e) {

}

?>
<?php
session_start();
ob_start();

require_once 'pdo.php';

try {
    $pdo->beginTransaction();
    $hashPasswordSTMT = $pdo->prepare(file_get_contents(__DIR__ . '/../queries/backoffice/auth.sql'));
    $hashPasswordSTMT->execute([':id' => $_SESSION['id']]);
    $hashPassword = $hashPasswordSTMT->fetch(PDO::FETCH_ASSOC);
    error_log("auth");
    var_dump($hashPassword);
    var_dump($_SESSION['session_id']);
    if (!$_SESSION['session_id'] || !password_verify($_SESSION['pass'], $hashPassword['mdp'])) {
        //header('Location: ../backoffice/connexion.php?error=3');
        die();
    }
} catch (Exception $e) {
    header('Location: ../backoffice/connexion.php?error=0');
    die();
}

?>
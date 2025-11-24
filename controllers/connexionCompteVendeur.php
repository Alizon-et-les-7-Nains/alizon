<?php

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once 'pdo.php';

    try {
        $pdo->beginTransaction();

        $isValidSTMT = $pdo->prepare(file_get_contents('../queries/backoffice/connexion.sql'));
        $isValidSTMT->execute([':pseudo' => $_POST['pseudo'], ':mdp' => $_POST['mdp']]);
        $isValid = $isValidSTMT->fetchColumn();

        if ($valid) {
            $vendeurSTMT = $pdo->prepare(file_get_contents('../queries/backoffice/vendeur.sql'));
            $vendeurSTMT->execute([':pseudo' => $_POST['pseudo'], ':mdp' => $_POST['mdp']]);
            $vendeur = $vendeurSTMT->fetchAll(PDO::FETCH_ASSOC);

            session_start();
            $_SESSION['id'] = $vendeur[0]['codeVendeur'];
            $_SESSION['pass'] = $_POST['mdp'];
        } else {
            $pdo->rollback();
            header('Location: ../views/backoffice/connexion.php?error=1');
            die();
        }
    } catch (Exception $e) {
        header('Location: ../views/backoffice/connexion.php?error=0');
        die();
    }
}

?>
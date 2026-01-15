<?php 
ob_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once 'pdo.php';
    error_log("connexion");

    try {
        var_dump($_POST['mdp']);
        $pdo->beginTransaction();
        $mdpHash = password_hash($_POST['mdp'], PASSWORD_DEFAULT);
        var_dump($mdpHash);
        $isValidSTMT = $pdo->prepare(file_get_contents('../queries/backoffice/connexion.sql'));
        $isValidSTMT->execute([':pseudo' => $_POST['pseudo'], ':mdp' => $mdpHash]);
        $isValid = $isValidSTMT->fetchColumn();

        if ($isValid) {
            $vendeurSTMT = $pdo->prepare(file_get_contents('../queries/backoffice/vendeur.sql'));
            $vendeurSTMT->execute([':pseudo' => $_POST['pseudo'], ':mdp' => $_POST['mdp']]);
            $vendeur = $vendeurSTMT->fetch(PDO::FETCH_ASSOC);
            $pdo->commit();

            session_start();
            $id_session = session_id();
            $_SESSION['session_id'] = $id_session;
            $_SESSION['id'] = $vendeur['codeVendeur'];
            $_SESSION['pass'] = $_POST['mdp'];

            header('Location: ../views/backoffice/accueil.php');
        } else {
            $pdo->rollback();
            header('Location: ../views/backoffice/connexion.php?error=1');
}
    } catch (Exception $e) {
        header('Location: ../views/backoffice/connexion.php?error=0');
        die();
    }
}

?>
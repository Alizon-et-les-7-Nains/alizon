<?php 
ob_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once 'pdo.php';
    error_log("connexion");

    try {
        $pdo->beginTransaction();

        $hashPasswordSTMT = $pdo->prepare(file_get_contents('../queries/backoffice/connexion.sql'));
        $hashPasswordSTMT->execute([':pseudo' => $_POST['pseudo']]);
        $hashPassword = $hashPasswordSTMT->fetch(PDO::FETCH_ASSOC);
        if(password_verify($_POST['mdp'], $hashPassword['mdp'])){
            $vendeurSTMT = $pdo->prepare(file_get_contents('../queries/backoffice/vendeur.sql'));
            $vendeurSTMT->execute([':pseudo' => $_POST['pseudo']]);
            $vendeur = $vendeurSTMT->fetch(PDO::FETCH_ASSOC);
            $pdo->commit();

            session_start();
            $id_session = session_id();
            $_SESSION['session_id'] = $id_session;
            $_SESSION['id'] = $vendeur['codeVendeur'];
            $_SESSION['pass'] = $hashPassword['mdp'];

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
<?php 
ob_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    session_start();
    require_once 'pdo.php';
    error_log("connexion");

    try {
        $hashPasswordSTMT = $pdo->prepare(file_get_contents('../queries/backoffice/connexion.sql'));
        $hashPasswordSTMT->execute([':pseudo' => $_POST['pseudo']]);
        $hashPassword = $hashPasswordSTMT->fetch(PDO::FETCH_ASSOC);

        if ($hashPassword && password_verify($_POST['mdp'], $hashPassword['mdp'])) {
            $vendeurSTMT = $pdo->prepare(file_get_contents('../queries/backoffice/vendeur.sql'));
            $vendeurSTMT->execute([':pseudo' => $_POST['pseudo']]);
            $vendeur = $vendeurSTMT->fetch(PDO::FETCH_ASSOC);

            if (!empty($vendeur['otp_enabled'])) {
                session_regenerate_id(true);
                $_SESSION['bo_a2f_required'] = true;
                $_SESSION['bo_pending_vendeur_id'] = $vendeur['codeVendeur'];
                header('Location: ../views/backoffice/connexion.php');
                exit;
            }

            session_regenerate_id(true);
            $_SESSION['session_id'] = session_id();
            $_SESSION['id'] = $vendeur['codeVendeur'];
            $_SESSION['pass'] = $hashPassword['mdp'];

            header('Location: ../views/backoffice/accueil.php');
            exit;
        } else {
            header('Location: ../views/backoffice/connexion.php?error=1');
            exit;
        }
    } catch (Exception $e) {
        header('Location: ../views/backoffice/connexion.php?error=0');
        die();
    }
}

?>
<?php
session_start();
require_once "../../controllers/pdo.php";
require_once '/var/www/html/vendor/autoload.php';

use OTPHP\TOTP;

// Vérifier que l'utilisateur vient de la page de connexion
if (!isset($_SESSION['2fa_user_id'])) {
    header('Location: connexionClient.php');
    exit;
}

$error = '';

// Fonction de déchiffrement (copier depuis connexionClient.php)
function dechiffrement($data) {
    $key = 'la_super_cle_secrete';
    $method = 'aes-256-cbc';
    $data = base64_decode($data);
    $iv_length = openssl_cipher_iv_length($method);
    $iv = substr($data, 0, $iv_length);
    $encrypted = substr($data, $iv_length);
    return openssl_decrypt($encrypted, $method, $key, 0, $iv);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = trim($_POST['code']);
    
    if (!empty($code)) {
        // Récupérer le secret chiffré
        $encrypted_secret = $_SESSION['2fa_secret'];
        
        // Déchiffrer le secret
        $secret = dechiffrement($encrypted_secret);
        
        if ($secret) {
            // Créer une instance TOTP avec le secret
            $totp = TOTP::create($secret);
            
            // Vérifier le code
            if ($totp->verify($code)) {
                // Code valide - créer la session complète
                $_SESSION['user_id'] = $_SESSION['2fa_user_id'];
                $_SESSION['user_email'] = $_SESSION['2fa_user_email'];
                $_SESSION['user_name'] = $_SESSION['2fa_user_name'];
                $_SESSION['user_prenom'] = $_SESSION['2fa_user_prenom'];
                $_SESSION['user_nom'] = $_SESSION['2fa_user_nom'];
                
                // Nettoyer les variables temporaires
                unset($_SESSION['2fa_user_id']);
                unset($_SESSION['2fa_user_email']);
                unset($_SESSION['2fa_user_name']);
                unset($_SESSION['2fa_user_prenom']);
                unset($_SESSION['2fa_user_nom']);
                unset($_SESSION['2fa_secret']);
                
                // Rediriger vers l'accueil
                header('Location: ../../views/frontoffice/accueilConnecte.php');
                exit;
            } else {
                $error = "Code invalide. Veuillez réessayer.";
            }
        } else {
            $error = "Erreur lors du déchiffrement du secret.";
        }
    } else {
        $error = "Veuillez entrer le code de vérification.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../public/style.css">
    <title>Vérification 2FA</title>
</head>
<body>
    <main>
        <h2>Vérification à deux facteurs</h2>
        <p>Veuillez entrer le code généré par votre application d'authentification.</p>
        
        <?php if ($error): ?>
            <div style="color: red; margin-bottom: 15px;"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div>
                <label for="code">Code de vérification :</label>
                <input type="text" id="code" name="code" required autocomplete="off" pattern="[0-9]{6}" maxlength="6">
            </div>
            <button type="submit">Vérifier</button>
        </form>
        
        <p><a href="connexionClient.php">Retour à la connexion</a></p>
    </main>
</body>
</html>
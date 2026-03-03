<?php 
// Démarrer la session pour gérer l'authentification
session_start();
// Inclure le fichier de connexion à la base de données
require_once "../../controllers/pdo.php";
require_once '/var/www/html/vendor/autoload.php';

use OTPHP\TOTP;

// Fonction de chiffrement pour le secret OTP
function chiffremnent($data) {
    $key = 'la_super_cle_secrete'; // En production, utilisez une clé sécurisée depuis les variables d'environnement
    $method = 'aes-256-cbc';
    $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($method));
    $encrypted = openssl_encrypt($data, $method, $key, 0, $iv);
    return base64_encode($iv . $encrypted);
}

// Fonction de déchiffrement pour le secret OTP (utile pour la vérification)
function dechiffrement($data) {
    $key = 'la_super_cle_secrete';
    $method = 'aes-256-cbc';
    $data = base64_decode($data);
    $iv_length = openssl_cipher_iv_length($method);
    $iv = substr($data, 0, $iv_length);
    $encrypted = substr($data, $iv_length);
    return openssl_decrypt($encrypted, $method, $key, 0, $iv);
}

// Gérer les requêtes AJAX pour l'authentification à deux facteurs
$input_data = json_decode(file_get_contents("php://input"), true);

if (isset($input_data['activate'])) {
    header('Content-Type: application/json');
    
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'Utilisateur non connecté']);
        exit;
    }

    if ($input_data['activate']) {
        try {
            // Générer un secret TOTP pour l'activation
            $totp = TOTP::create();
            $totp->setLabel($_SESSION['user_email'] ?? 'Utilisateur');
            $totp->setIssuer('MonSite');
            
            $secret = $totp->getSecret();
            
            // Chiffrer et sauvegarder le secret dans la base de données
            $encrypted_secret = chiffremnent($secret);
            $sql = "UPDATE _client SET otp_secret = ?, otp_enabled = 1 WHERE idClient = ?";
            $stmt = $pdo->prepare($sql);
            $success = $stmt->execute([$encrypted_secret, $_SESSION['user_id']]);
            
            if ($success) {
                // Générer l'URL OTP Auth pour le QR code
                $otpauthUrl = $totp->getProvisioningUri();
                
                echo json_encode([
                    'success' => true, 
                    'otpauthUrl' => $otpauthUrl,
                    'message' => 'Authentification à deux facteurs activée avec succès'
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Erreur lors de la sauvegarde du secret']);
            }
        } catch (Exception $e) {
            error_log("Erreur activation 2FA: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'activation']);
        }
    } else {
        // Désactiver la 2FA
        try {
            $sql = "UPDATE _client SET otp_enabled = 0, otp_secret = NULL WHERE idClient = ?";
            $stmt = $pdo->prepare($sql);
            $success = $stmt->execute([$_SESSION['user_id']]);
            
            if ($success) {
                echo json_encode(['success' => true, 'message' => 'Authentification à deux facteurs désactivée']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Erreur lors de la désactivation']);
            }
        } catch (Exception $e) {
            error_log("Erreur désactivation 2FA: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Erreur lors de la désactivation']);
        }
    }
    exit;
}

// Initialiser les variables pour le formulaire de connexion
$error = '';
$email_tel = '';
$password = '';

// Vérifier si la requête est en POST (formulaire soumis)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($input_data['activate'])) {
    // Récupérer et nettoyer les données du formulaire
    $email_tel = trim($_POST['email_tel']);
    $password_clair = trim($_POST['password_clair']); 
    
    // Log de debug pour tracer les tentatives de connexion
    error_log("Tentative connexion: " . $email_tel);
    
    // Déterminer si l'entrée est un email ou un téléphone
    if (filter_var($email_tel, FILTER_VALIDATE_EMAIL)) {
        // C'est un email : requête avec email
        $sql = "SELECT idClient, email, mdp, noTelephone, prenom, nom, otp_enabled, otp_secret FROM _client WHERE email = ?";
    } else {
        // C'est un téléphone : nettoyer et requête avec numéro
        $tel_clean = preg_replace('/[^0-9]/', '', $email_tel);
        $sql = "SELECT idClient, email, mdp, noTelephone, prenom, nom, otp_enabled, otp_secret FROM _client WHERE REPLACE(noTelephone, ' ', '') = ?";
        $email_tel = $tel_clean;
    }
    
    // Préparer et exécuter la requête
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$email_tel]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Vérifier si un utilisateur a été trouvé
    if ($user) {
        // Log du hash du mot de passe pour debug
        error_log("MDP en BD (hash): " . $user['mdp']);
        
        // Vérifier le mot de passe avec password_verify (hachage sécurisé)
        if (password_verify($password_clair, $user['mdp'])) {
            
            // Vérifier si l'utilisateur a activé la 2FA
            if ($user['otp_enabled'] == 1 && !empty($user['otp_secret'])) {
                // Stocker les infos temporairement pour la vérification 2FA
                $_SESSION['2fa_user_id'] = $user['idClient'];
                $_SESSION['2fa_user_email'] = $user['email'];
                $_SESSION['2fa_user_name'] = $user['prenom'] . ' ' . $user['nom'];
                $_SESSION['2fa_user_prenom'] = $user['prenom'];
                $_SESSION['2fa_user_nom'] = $user['nom'];
                $_SESSION['2fa_secret'] = $user['otp_secret'];
                
                // Rediriger vers la page de vérification 2FA
                header('Location: verification_2fa.php');
                exit;
            } else {
                // Connexion réussie sans 2FA : créer la session utilisateur
                $_SESSION['user_id'] = $user['idClient'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_name'] = $user['prenom'] . ' ' . $user['nom'];
                $_SESSION['user_prenom'] = $user['prenom'];
                $_SESSION['user_nom'] = $user['nom'];
                
                // Rediriger vers la page d'accueil connecté
                header('Location: ../../views/frontoffice/accueilConnecte.php');
                exit;
            }
        } else {
            // Mot de passe incorrect
            $error = "Mot de passe incorrect";
        }
    } else {
        // Aucun compte correspondant trouvé
        $error = "Aucun compte trouvé avec ces identifiants";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../public/style.css">
    <link rel="icon" href="/public/images/logoBackoffice.svg">
    <title>Connexion</title>
</head>

<body class="pageConnexionCLient">

    <header class="headerFront">

        <div class="headerMain">
            <div class="logoNom">
                <img src="../../../public/images/logoAlizonHeader.png" alt="Logo Alizon">
                <h1><a href="../frontoffice/accueilDeconnecte.php"><b>Alizon</b></a></h1>
            </div>
        </div>

    </header>

    <main>
        <div class="profile">
            <img src="../../public/images/utilLightBlue.svg" alt="">
        </div>
        <h2>Connexion à votre compte Alizon</h2>

        <?php if ($error): ?>
        <div class="error-message">
            <?php echo htmlspecialchars($error); ?>
        </div>
        <?php endif; ?>

        <form id="loginForm" method="POST" action="">
            <div class="inputLabelGroup">
                <label>Adresse mail ou numéro de téléphone*</label>
                <input type="text" name="email_tel" placeholder="Adresse mail ou numéro de téléphone*"
                    class="inputConnexionClient" value="<?php echo htmlspecialchars($email_tel); ?>" required>
            </div>
            <div class="inputLabelGroup">
                <label>Mot de passe*</label>
                <input type="password" id="password_input" name="password_clair" placeholder="Mot de passe*"
                    class="inputConnexionClient" required>
            </div>

            <div class="authenTwofacts">
                <input type="checkbox" id="remember_me" name="remember_me">
                <label for="remember_me">Activer l'authentification à deux facteurs</label>
            </div>

            <div>
                <a href="inscription.php">Pas encore client ? Inscrivez-vous ici</a>
                <button type="submit" class="boutonConnexionClient">Se connecter</button>
            </div>
        </form>

        <?php require_once '../backoffice/partials/retourEnHaut.php' ?>
    </main>

    <?php include '../../views/frontoffice/partials/footerDeconnecte.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/qrcode/build/qrcode.min.js"></script>
    <script src="../scripts/frontoffice/connexionClient.js"></script>
</body>

</html>
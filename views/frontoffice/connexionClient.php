<?php 
// Démarrer la session pour gérer l'authentification
session_start();
// Inclure le fichier de connexion à la base de données
require_once "../../controllers/pdo.php";
require_once '/var/www/html/vendor/autoload.php';

use OTPHP\TOTP;

// Fonctions de chiffrement/déchiffrement
function dechiffrement($data) {
    $key = 'la_super_cle_secrete';
    $data = base64_decode($data);
    $iv_length = openssl_cipher_iv_length('aes-256-cbc');
    $iv = substr($data, 0, $iv_length);
    $encrypted = substr($data, $iv_length);
    return openssl_decrypt($encrypted, 'aes-256-cbc', $key, 0, $iv);
}

// Traiter la vérification du code OTP via JSON
$data = json_decode(file_get_contents('php://input'), true);

// Vérifier le statut de blocage
if (isset($data['checkBlock']) && isset($_SESSION['user_id'])) {
    $blockedUntil = $_SESSION['otp_blocked_until'] ?? 0;
    $currentTime = time();
    
    if ($blockedUntil > $currentTime) {
        echo json_encode([
            'blocked' => true,
            'remainingTime' => $blockedUntil - $currentTime
        ]);
    } else {
        echo json_encode(['blocked' => false]);
    }
    exit;
}

if (isset($data['otp']) && isset($_SESSION['user_id'])) {
    $code = $data['otp'];
    
    // Initialiser les variables de blocage si elles n'existent pas
    if (!isset($_SESSION['otp_failed_attempts'])) {
        $_SESSION['otp_failed_attempts'] = 0;
    }
    if (!isset($_SESSION['otp_blocked_until'])) {
        $_SESSION['otp_blocked_until'] = 0;
    }
    if (!isset($_SESSION['otp_block_duration'])) {
        $_SESSION['otp_block_duration'] = 30; // Durée de blocage initiale en secondes
    }
    
    // Vérifier si l'utilisateur est actuellement bloqué
    $currentTime = time();
    if ($_SESSION['otp_blocked_until'] > $currentTime) {
        echo json_encode([
            'success' => false,
            'blocked' => true,
            'remainingTime' => $_SESSION['otp_blocked_until'] - $currentTime,
            'message' => 'Trop de tentatives échouées. Veuillez patienter.'
        ]);
        exit;
    }
    
    // Récupérer le secret OTP de l'utilisateur
    $sql = "SELECT otp_secret FROM _client WHERE idClient = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$_SESSION['user_id']]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result && $result['otp_secret']) {
        // Déchiffrer le secret
        $secret = dechiffrement($result['otp_secret']);
        
        // Vérifier le code OTP
        $totp = TOTP::create($secret);
        $isValid = $totp->verify($code);
        
        if ($isValid) {
            // Réinitialiser les compteurs en cas de succès
            $_SESSION['otp_failed_attempts'] = 0;
            $_SESSION['otp_blocked_until'] = 0;
            $_SESSION['otp_block_duration'] = 30;
            
            echo json_encode(['success' => true]);
            exit;
        } else {
            // Incrémenter le compteur d'échecs
            $_SESSION['otp_failed_attempts']++;
            
            // Vérifier si on atteint 3 tentatives échouées
            if ($_SESSION['otp_failed_attempts'] >= 3) {
                // Bloquer l'utilisateur
                $_SESSION['otp_blocked_until'] = $currentTime + $_SESSION['otp_block_duration'];
                
                echo json_encode([
                    'success' => false,
                    'blocked' => true,
                    'remainingTime' => $_SESSION['otp_block_duration'],
                    'message' => 'Trop de tentatives échouées. Veuillez patienter ' . $_SESSION['otp_block_duration'] . ' secondes.'
                ]);
                
                // Augmenter la durée de blocage pour la prochaine fois
                $_SESSION['otp_block_duration'] += 30;
                
                // Réinitialiser le compteur de tentatives
                $_SESSION['otp_failed_attempts'] = 0;
                
                exit;
            }
            
            // Retourner le nombre de tentatives restantes
            echo json_encode([
                'success' => false,
                'blocked' => false,
                'attemptsLeft' => 3 - $_SESSION['otp_failed_attempts'],
                'message' => 'Code incorrect'
            ]);
            exit;
        }
    }
    
    echo json_encode(['success' => false, 'message' => 'Erreur de vérification']);
    exit;
}

// Initialiser les variables
$error = '';
$email_tel = '';
$password = '';
$popupA2f = $_SESSION['a2f_required'] ?? false;
unset($_SESSION['a2f_required']);

// Vérifier si la requête est en POST (formulaire soumis)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupérer et nettoyer les données du formulaire
    $email_tel = trim($_POST['email_tel']);
    $password_clair = trim($_POST['password_clair']); 
    
    // Log de debug pour tracer les tentatives de connexion
    error_log("Tentative connexion: " . $email_tel);
    
    // Déterminer si l'entrée est un email ou un téléphone
    if (filter_var($email_tel, FILTER_VALIDATE_EMAIL)) {
        // C'est un email : requête avec email
        $sql = "SELECT idClient, email, mdp, noTelephone, prenom, nom FROM _client WHERE email = ?";
    } else {
        // C'est un téléphone : nettoyer et requête avec numéro
        $tel_clean = preg_replace('/[^0-9]/', '', $email_tel);
        $sql = "SELECT idClient, email, mdp, noTelephone, prenom, nom FROM _client WHERE REPLACE(noTelephone, ' ', '') = ?";
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
            // Connexion réussie : créer la session utilisateur
            $_SESSION['user_id'] = $user['idClient'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_name'] = $user['prenom'] . ' ' . $user['nom'];
            $_SESSION['user_prenom'] = $user['prenom'];
            $_SESSION['user_nom'] = $user['nom'];
            
            // Rediriger vers la page d'accueil connecté
            //header('Location: ../../views/frontoffice/accueilConnecte.php');
            $sql = "SELECT otp_enabled FROM _client WHERE idClient = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$_SESSION['user_id']]);
            $otp_enabled = $stmt->fetchColumn();
            if (!$otp_enabled) {
                $popupA2f = false;
                header('Location: ../../views/frontoffice/accueilConnecte.php');
                exit();
            } else {
                $_SESSION['a2f_required'] = true;
                header('Location: ../../views/frontoffice/connexionClient.php');
                exit();
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
        <div class="error-message" style="color: red; margin-bottom: 15px;">
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
            <div>
                <a href="inscription.php">Pas encore client ? Inscrivez-vous ici</a>
                <button type="submit" class="boutonConnexionClient">Se connecter</button>
            </div>
        </form>

        <?php require_once '../backoffice/partials/retourEnHaut.php' ?>

        <?php if ($popupA2f): ?>
            <div class="bodyPopupA2f">
                <div class="popupA2f">
                    <div class="croixFermerLaPage" onclick="fermerPopupA2F()">
                        <div></div><div></div>
                    </div>
                    <h1>Authentification à double facteur</h1>
                    <p style="margin-bottom: 20px; color: #666;">Entrez le code à 6 chiffres de votre application d'authentification</p>
                    <form id="formA2F">
                        <div>
                            <input type="text" name="num1" id="num1" maxlength="1" pattern="[0-9]" autocomplete="off">
                            <input type="text" name="num2" id="num2" maxlength="1" pattern="[0-9]" autocomplete="off">
                            <input type="text" name="num3" id="num3" maxlength="1" pattern="[0-9]" autocomplete="off">
                            <input type="text" name="num4" id="num4" maxlength="1" pattern="[0-9]" autocomplete="off">
                            <input type="text" name="num5" id="num5" maxlength="1" pattern="[0-9]" autocomplete="off">
                            <input type="text" name="num6" id="num6" maxlength="1" pattern="[0-9]" autocomplete="off">
                        </div>
                        <p class="erreur" id="erreurCodeA2F" style="display: none; color: red; margin-top: 15px;">Code incorrect</p>
                        <button type="submit">Vérifier</button>
                    </form>
                </div>
            </div>
        <?php endif; ?>
    </main>
    <script src="../scripts/frontoffice/authCode.js"></script>
    <?php include '../../views/frontoffice/partials/footerDeconnecte.php'; ?>
</body>
</html>
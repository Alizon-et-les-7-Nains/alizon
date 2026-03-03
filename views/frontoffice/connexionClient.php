<?php 
// Démarrer la session pour gérer l'authentification
session_start();
// Inclure le fichier de connexion à la base de données
require_once "../../controllers/pdo.php";
require_once '/var/www/html/vendor/autoload.php';

use OTPHP\TOTP;


// Initialiser les variables
$error = '';
$email_tel = '';
$password = '';

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
            header('Location: ../../views/frontoffice/accueilConnecte.php');
            exit;
        } else {
            // Mot de passe incorrect
            $error = "Mot de passe incorrect";
        }
    } else {
        // Aucun compte correspondant trouvé
        $error = "Aucun compte trouvé avec ces identifiants";
    }
}

function chiffremnent($data) {
    $key = 'la_super_cle_secrete';
    $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
    $encrypted = openssl_encrypt($data, 'aes-256-cbc', $key, 0, $iv);
    return base64_encode($iv . $encrypted);
}

function a2f() {
    $totp = TOTP::create();

    $totp->setLabel('TestUser');
    $totp->setIssuer('MonSite');

    // Ajouter le secret à la BDD chiffré
    $sql = "UPDATE _client SET otp_secret = ? WHERE idClient = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([chiffremnent($totp->getSecret()), $_SESSION['user_id']]);
}


$data = json_decode(file_get_contents("php://input"), true);

if (isset($data['activate'])) {

    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false]);
        exit;
    }

    $activate = $data['activate'] ? 1 : 0;

    $sql = "UPDATE _client SET otp_enabled = ? WHERE idClient = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$activate, $_SESSION['user_id']]);

    echo json_encode(['success' => true]);
    exit;
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
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script src="../scripts/frontoffice/connexionClient.js"></script>
</body>

</html> 
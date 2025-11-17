<?php 
require_once "../../controllers/pdo.php";
session_start();

$error = '';
$email_tel = '';
$password = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email_tel = trim($_POST['email_tel']);
    $password_chiffre = trim($_POST['password_chiffre']); 
    
    // Debug simple
    error_log("Tentative connexion: " . $email_tel);
    error_log("MDP chiffré reçu: " . $password_chiffre);
    
    // Email ou téléphone
    if (filter_var($email_tel, FILTER_VALIDATE_EMAIL)) {
        $sql = "SELECT idClient, email, mdp, noTelephone, prenom, nom FROM _client WHERE email = ?";
    } else {
        $tel_clean = preg_replace('/[^0-9]/', '', $email_tel);
        $sql = "SELECT idClient, email, mdp, noTelephone, prenom, nom FROM _client WHERE REPLACE(noTelephone, ' ', '') = ?";
        $email_tel = $tel_clean;
    }
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$email_tel]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        error_log("MDP en BD: " . $user['mdp']);
        error_log("Comparaison: " . ($password_chiffre === $user['mdp'] ? 'OK' : 'ÉCHEC'));
        
        if ($password_chiffre === $user['mdp']) {
            // Connexion réussie
            $_SESSION['user_id'] = $user['idClient'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_name'] = $user['prenom'] . ' ' . $user['nom'];
            $_SESSION['user_prenom'] = $user['prenom'];
            $_SESSION['user_nom'] = $user['nom'];
            
            header('Location: ../../views/frontoffice/acceuilConnecte.php');
            exit;
        } else {
            $error = "Mot de passe incorrect";
        }
    } else {
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
    <title>Connexion</title>
</head>

<body class="pageConnexionCLient">
    <?php include '../../views/frontoffice/partials/headerDeconnecte.php'; ?>

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
            <input type="text" name="email_tel" placeholder="Adresse mail ou numéro de téléphone*"
                class="inputConnexionClient" value="<?php echo htmlspecialchars($email_tel); ?>" required>

            <input type="password" id="password_input" placeholder="Mot de passe*" class="inputConnexionClient"
                required>

            <input type="hidden" name="password_chiffre" id="password_chiffre">

            <div>
                <a href="inscriptionClient.php">Pas encore client ? Inscrivez-vous ici</a>
                <a href="#">Mot de passe oublié ? Cliquez ici</a>
                <button type="submit" class="boutonConnexionClient">Se connecter</button>
            </div>
        </form>

        <!-- Section test temporaire -->
        <div style="margin-top: 20px; padding: 10px; background: #f0f0f0;">
            <h3>Test de connexion</h3>
            <p><strong>Email test:</strong> alice.laurent@outlook.fr</p>
            <p><strong>Mot de passe:</strong> [utilisez le script test_chiffrement.php pour le trouver]</p>
        </div>
    </main>

    <?php include '../../views/frontoffice/partials/footerDeconnecte.php'; ?>

    <script src="../../controllers/Chiffrement.js"></script>
    <script>
    document.getElementById('loginForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const passwordClair = document.getElementById('password_input').value;

        if (typeof vignere !== 'undefined') {
            const passwordChiffre = vignere(passwordClair, cle, 1);
            document.getElementById('password_chiffre').value = passwordChiffre;
            console.log("Envoi - Mot de passe clair:", passwordClair);
            console.log("Envoi - Mot de passe chiffré:", passwordChiffre);
        }

        this.submit();
    });
    </script>
</body>

</html>
<?php 
require_once "../../controllers/pdo.php";
session_start();

$error = '';
$email_tel = '';
$password = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email_tel = trim($_POST['email_tel']);
    $password_chiffre = trim($_POST['password_chiffre']); 
    
    //email ou un numéro de téléphone
    if (filter_var($email_tel, FILTER_VALIDATE_EMAIL)) {
        //email
        $sql = "SELECT idClient, email, mdp, noTelephone, prenom, nom FROM _client WHERE email = ?";
    } else {
        // numéro de téléphone
        $tel_clean = preg_replace('/[^0-9]/', '', $email_tel);
        $sql = "SELECT idClient, email, mdp, noTelephone, prenom, nom FROM _client WHERE REPLACE(noTelephone, ' ', '') = ?";
        $email_tel = $tel_clean;
    }
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$email_tel]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        // Comparer les mots de passe chiffrés 
        if ($password_chiffre === $user['mdp']) {
            // Connexion réussie
            $_SESSION['user_id'] = $user['idClient'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_name'] = $user['prenom'] . ' ' . $user['nom'];
            $_SESSION['user_prenom'] = $user['prenom'];
            $_SESSION['user_nom'] = $user['nom'];
            
            // Redirection vers la page d'accueil apres connexion
            header('Location: ../../views/frontoffice/acceuil.php'); // corriger la faute de frappe ici
            exit;
        } else {
            $error = "Identifiants incorrects";
        }
    } else {
        $error = "Identifiants incorrects";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../public/style.css">
    <title>Page de connexion</title>
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

            <!-- Champ caché pour le mot de passe chiffré -->
            <input type="hidden" name="password_chiffre" id="password_chiffre">

            <div>
                <a href="inscriptionClient.php">Pas encore client ? Inscrivez-vous ici</a>
                <a href="#">Mot de passe oublié ? Cliquez ici</a>
                <button type="submit" class="boutonConnexionClient">Se connecter</button>
            </div>
        </form>

        <p class="petitTexte">
            Alizon, en tant que responsable de traitement, traite les données recueillies à
            des fins de gestion de la relation client, gestion des commandes et des livraisons,
            personnalisation des services, prévention de la fraude, marketing et publicité ciblée.
            Pour en savoir plus, reportez-vous à la Politique de protection de vos données personnelles
        </p>
    </main>

    <?php include '../../views/frontoffice/partials/footerDeconnecte.php'; ?>

    <script src="../../controllers/Chiffrement.js"></script>

    <script>
    document.querySelector('.boutonConnexionClient').addEventListener('submit', function(e) {
        e.preventDefault();

        const passwordClair = document.getElementById('password_input').value;
        const passwordChiffre = vignere(passwordClair, cle, 1);

        document.getElementById('password_chiffre').value = passwordChiffre;

        this.submit();
    });
    </script>
</body>

</html>
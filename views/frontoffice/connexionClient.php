<?php 
require_once "../../controllers/pdo.php";
session_start();

$error = '';
$email_tel = '';
$password = '';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email_tel = trim($_POST['email_tel']);
    $password = trim($_POST['password']);
    
    // Vérifier si c'est un email ou un numéro de téléphone
    if (filter_var($email_tel, FILTER_VALIDATE_EMAIL)) {
        // C'est un email
        $sql = "SELECT idClient, email, mdp, noTelephone, prenom, nom FROM _client WHERE email = ?";
    } else {
        // C'est un numéro de téléphone 
        $tel_clean = preg_replace('/[^0-9]/', '', $email_tel);
        $sql = "SELECT idClient, email, mdp, noTelephone, prenom, nom FROM _client WHERE REPLACE(noTelephone, ' ', '') = ?";
        $email_tel = $tel_clean;
    }
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$email_tel]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user && password_verify($password, $user['mdp'])) {
        // Connexion réussie
        $_SESSION['user_id'] = $user['idClient'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_name'] = $user['prenom'] . ' ' . $user['nom'];
        
        // Redirection vers la page d'accueil ou profil
        header('Location: ../../views/frontoffice/accueil.php');
        exit;
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

        <form method="POST" action="">
            <input type="text" name="email_tel" placeholder="Adresse mail ou numéro de téléphone*"
                class="inputConnexionClient" value="<?php echo htmlspecialchars($email_tel); ?>" required>

            <input type="password" name="password" placeholder="Mot de passe*" class="inputConnexionClient" required>

            <div>
                <a href="#">Pas encore client ? Inscrivez-vous ici</a>
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
</body>

</html>
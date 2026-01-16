<?php 
// Inclure le fichier de connexion à la base de données
require_once "../../controllers/pdo.php";
// Démarrer la session pour gérer l'authentification
session_start();

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
    </main>

    <?php include '../../views/frontoffice/partials/footerDeconnecte.php'; ?>
</body>

</html>
<?php
$message = "";
$data = []; 
$nom_contact = '';
$prenom_contact = '';
$email = '';
$num_tel = '';
$nom_utilisateur = '';
$num_siren = '';
$adresse_entreprise = '';
$raison_sociale = '';
$date_naissance = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nom_contact        = htmlspecialchars(trim($_POST['nom_contact'] ?? ''));
    $prenom_contact     = htmlspecialchars(trim($_POST['prenom_contact'] ?? ''));
    $email              = htmlspecialchars(trim($_POST['email'] ?? ''));
    $num_tel            = htmlspecialchars(trim($_POST['num_tel'] ?? ''));
    $nom_utilisateur    = htmlspecialchars(trim($_POST['nom_utilisateur'] ?? ''));
    $mdp                = $_POST['mdp'] ?? '';
    $confimer_mdp       = $_POST['confimer_mdp'] ?? '';
    $num_siren          = htmlspecialchars(trim($_POST['num_siren'] ?? ''));
    $adresse_entreprise = htmlspecialchars(trim($_POST['adresse_entreprise'] ?? ''));
    $raison_sociale     = htmlspecialchars(trim($_POST['raison_sociale'] ?? ''));
    $date_naissance     = htmlspecialchars(trim($_POST['date_naissance'] ?? ''));
    
    $form_is_valid = true;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"> 	
    <link rel="stylesheet" href="../../public/style.css">
    <title>Création d'un compte vendeur</title>
    
    <style>
        .status-red { color: red; }
        .status-green { color: green; }
        #password-requirements ul, #req-match-container ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        #password-requirements li, #req-match-container li {
            margin-bottom: 5px;
        }
    </style>
</head>
    <?php
        require_once "./partials/headerMain.php";
    ?>
<body>
    <main class="CreerCompteVendeur">
        <img class="triskiel" src="../../public/images/triskiel gris.svg" alt="">

        <div class="haut_de_page">
            <img src="../../public/images/pdp_user.svg" alt="photo de profil">
            <h1>Création de votre compte vendeur</h1>
        </div>

        <div class="container">
            <form method="post" class="form-vendeur">
                    <?php if (!empty($message)) : ?>
                        <p class="message"><?= $message ?></p>
                    <?php endif; ?>

                    <div class="form-group">
                        <input type="text" name="nom_contact" placeholder="Nom de contact" required>
                        <input type="text" name="email" placeholder="Adresse E-Mail" required>
                        <input type="text" name="nom_utilisateur" placeholder="Nom d'utilisateur" required>
                    </div>

                    <div class="form-group">
                        <input type="text" name="prenom_contact" placeholder="Prénom de contact" required>
                        <input type="text" name="num_tel" placeholder="Numéro de téléphone" required>
                        <input type="password" name="mdp" id="mdp" placeholder="Mot de passe" required>
                    </div>
                    
                    <div id="password-requirements-container">
                        <ul id="password-requirements">
                            <li id="req-length" class="status-red"><i class="bi bi-x-circle-fill" style="margin-right: 5px;"></i>Au moins 12 caractères</li>
                            <li id="req-lowercase" class="status-red"><i class="bi bi-x-circle-fill" style="margin-right: 5px;"></i>Une minuscule</li>
                            <li id="req-uppercase" class="status-red"><i class="bi bi-x-circle-fill" style="margin-right: 5px;"></i>Une majuscule</li>
                            <li id="req-number" class="status-red"><i class="bi bi-x-circle-fill" style="margin-right: 5px;"></i>Un chiffre (0-9)</li>
                            <li id="req-special" class="status-red"><i class="bi bi-x-circle-fill" style="margin-right: 5px;"></i>Un caractère spécial (@, !, #, ...)</li>
                            <li id="req-match" class="status-red"><i class="bi bi-x-circle-fill" style="margin-right: 5px;"></i>Les mots de passe correspondent</li>
                        </ul>
                    </div>
                    
                    <div class="form-group">
                        <input type="date" name="date_naissance" required>
                        <input type="text" name="num_siren" placeholder="Numéro SIREN" required>
                        
                        <input type="password" name="confimer_mdp" id="confimer_mdp" placeholder="Confirmer le mot de passe" required>
                    </div>

                    <div class="form-group">
                        <input type="text" name="adresse_entreprise" placeholder="Adresse de l'entreprise" required>
                        <input type="text" name="raison_sociale" placeholder="Raison sociale" required>
                    </div>

                    <p class="code_vendeur"> Code vendeur : <strong>VD640</strong> </p>
                    <a href="ConnexionVendeur.php">Déjà vendeur ? Connectez vous ici</a>
                    
                    <button type="submit" id="btn_inscription" class="btn_inscription" disabled>S'inscrire</button>

                    <div class="inscriptions_autres">
                        <p>Ou inscrivez-vous grâce à un de ces services</p>
                        <div class="logos">
                            <a href="#"><img src="../../public/images/google.svg" alt="Logo google"></a>
                            <a href="#"><img src="../../public/images/microsoft.svg" alt="Logo microsoft"></a>
                            <a href="#"><img src="../../public/images/apple.svg" alt="Logo apple"></a>
                            <a href="#"><img src="../../public/images/facebook.svg" alt="Logo facebook"></a>
                        </div>
                    </div>
            </form>
        </div>
        <p>
            Alizon, en tant que responsable de traitement, traite les données recueillies à des fins de gestion de la relation client, gestion des commandes et des livraisons, 
            personnalisation des services, prévention de la fraude, marketing et publicité ciblée. 
            Pour en savoir plus, reportez-vous à la Politique de protection de vos données personnelles
        </p>
        
        <!-- VALIDATION EN TEMPS RÉEL -->
        <script>
            // Eléments du DOM
            const passwordInput = document.getElementById('mdp');
            const confirmPasswordInput = document.getElementById('confimer_mdp');
            const submitButton = document.getElementById('btn_inscription');

            // Eléments de critères
            const reqLength = document.getElementById('req-length');
            const reqLowercase = document.getElementById('req-lowercase');
            const reqUppercase = document.getElementById('req-uppercase');
            const reqNumber = document.getElementById('req-number');
            const reqSpecial = document.getElementById('req-special');
            const reqMatch = document.getElementById('req-match');

            // Critères de validation
            const rules = {
                length: {
                    element: reqLength,
                    regex: /^.{12,}$/, 
                    message: 'Au moins 12 caractères'
                },
                lowercase: {
                    element: reqLowercase,
                    regex: /[a-z]/, 
                    message: 'Une minuscule'
                },
                uppercase: {
                    element: reqUppercase,
                    regex: /[A-Z]/, 
                    message: 'Une majuscule'
                },
                number: {
                    element: reqNumber,
                    regex: /[0-9]/, 
                    message: 'Un chiffre (0-9)'
                },
                special: {
                    element: reqSpecial,
                    regex: /[^a-zA-Z0-9]/, 
                    message: 'Un caractère spécial (@, !, #, ...)'
                }
            };

            // Mise à jour de l'affichage des critères ( de croix à coche )
            function updateRequirement(rule, password) {
                const isValid = rule.regex.test(password);
                const iconClass = isValid ? 'bi-check-circle-fill' : 'bi-x-circle-fill';
                const statusClass = isValid ? 'status-green' : 'status-red';

                // Mise à jour de la classe et du contenu de la liste
                rule.element.className = statusClass;
                rule.element.innerHTML = `<i class="bi ${iconClass}" style="margin-right: 5px;"></i>${rule.message}`;
                return isValid;
            }

            // Valide tous les critères et met à jour le bouton d'inscription.
            function validatePassword() {
                const password = passwordInput.value;
                const confirmPassword = confirmPasswordInput.value;
                let allValid = true;

                // Validation de chaque règle
                for (const key in rules) {
                    if (!updateRequirement(rules[key], password)) {
                        allValid = false;
                    }
                }

                // Validation de la correspondance des mots de passe
                const passwordsMatch = password.length > 0 && password === confirmPassword;
                const matchIconClass = passwordsMatch ? 'bi-check-circle-fill' : 'bi-x-circle-fill';
                const matchStatusClass = passwordsMatch ? 'status-green' : 'status-red';

                reqMatch.className = matchStatusClass;
                reqMatch.innerHTML = `<i class="bi ${matchIconClass}" style="margin-right: 5px;"></i>Les mots de passe correspondent`;
                
                if (!passwordsMatch) {
                    allValid = false;
                }

                // Activation/Désactivation du bouton
                submitButton.disabled = !allValid;
                
                return allValid;
            }

            // Lie les fonctions aux événements
            passwordInput.addEventListener('input', validatePassword);
            confirmPasswordInput.addEventListener('input', validatePassword);
            
            // Empêcher la soumission si la validation JavaScript échoue
            document.querySelector('form').addEventListener('submit', function(e) {
                if (!validatePassword()) {
                    e.preventDefault();
                }
            });

            // Initialisation au chargement de la page 
            validatePassword(); 
        </script>
    </main>
</body>
</html>
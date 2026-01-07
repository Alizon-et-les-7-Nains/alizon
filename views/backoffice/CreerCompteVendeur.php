<?php 
    $currentPage = basename(__FILE__);
    require_once "../../controllers/pdo.php"; 

    $nom = $_SESSION['form_data']['nom'] ?? '';
    $prenom = $_SESSION['form_data']['prenom'] ?? '';
    $email = $_SESSION['form_data']['email'] ?? '';
    $noTelephone = $_SESSION['form_data']['noTelephone'] ?? '';
    $pseudo = $_SESSION['form_data']['pseudo'] ?? '';
    $dateNaissance = $_SESSION['form_data']['dateNaissance'] ?? '';
    $noSiren = $_SESSION['form_data']['noSiren'] ?? '';
    $idAdresse = $_SESSION['form_data']['idAdresse'] ?? '';
    $raisonSocial = $_SESSION['form_data']['raisonSocial'] ?? '';
    $message = $_SESSION['form_data']['message'] ?? ''; 
    unset($_SESSION['form_data']);
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../public/style.css">
    <title>Alizon</title>
</head>
<?php
        require_once "./partials/header.php";
?>

<body>
    <main class="CreerCompteVendeur">
        <img class="triskiel" src="../../public/images/triskiel gris.svg" alt="">

        <div class="haut_de_page">
            <img src="../../public/images/pdp_user.svg" alt="photo de profil">
            <h1>Création de votre compte vendeur</h1>
        </div>

        <div class="container">
            <form method="post" class="form-vendeur" id="monForm" action="../../controllers/creerCompteVendeur.php" enctype="multipart/form-data">
                <?php if (!empty($message)) : ?>
                <p class="message"><?= $message ?></p>
                <?php endif; ?>

                <div class="row g-3">
                    
                    <div class="col-md-6">
                        <label>Nom de contact</label>
                        <input type="text" name="nom" required class="form-control"
                            value="<?= $nom ?>">
                    </div>
                    <div class="col-md-6">
                        <label>Prénom de contact</label>
                        <input type="text" name="prenom"required
                            class="form-control" value="<?= $prenom?>">
                    </div>
                    <div class="col-md-6">
                        <label>Adresse E-Mail</label>
                        <input type="email" name="email" required class="form-control"
                            value="<?= $email ?>">
                    </div>
                    <div class="col-md-6">
                        <label>Numéro de téléphone</label>
                        <input type="tel" name="noTelephone" required class="form-control"
                            value="<?= $noTelephone ?>">
                    </div>

                    <div class="col-md-6">
                        <label>Date de naissance</label>
                        <input type="date" name="dateNaissance" required class="form-control"
                            value="<?= $dateNaissance ?>">
                    </div>

                    <div class="col-md-6">
                        <label>Nom d'utilisateur</label>
                        <input type="text" name="pseudo" required
                            class="form-control" value="<?= $pseudo ?>">
                    </div>

                    <div class="col-md-6">
                        <label>Numéro de SIREN</label>
                        <input type="text" name="noSiren" required class="form-control"
                            value="<?= $noSiren ?>">
                    </div>
                    <div class="col-md-6">
                        <label>Adresse de l'entreprise</label>
                        <input type="text" name="idAdresse" required
                            class="form-control" value="<?= $idAdresse ?>">
                    </div>

                    <div class="col-md-12">
                        <label>Raison sociale</label>
                        <input type="text" name="raisonSocial" required
                            class="form-control" value="<?= $raisonSocial ?>">
                    </div>

                    <div class="col-md-6">
                        <label>Mot de passe</label>
                        <input type="password" name="mdp" id="mdp" required
                            class="form-control">
                                       
                        <div id="password-requirements-container" class="mt-2 hidden">
                            <ul id="password-requirements">
                                <li id="req-length" class="status-red"><i class="bi bi-x-circle-fill"
                                        style="margin-right: 5px;"></i>Au moins 12 caractères</li>
                                <li id="req-lowercase" class="status-red"><i class="bi bi-x-circle-fill"
                                        style="margin-right: 5px;"></i>Une minuscule</li>
                                <li id="req-uppercase" class="status-red"><i class="bi bi-x-circle-fill"
                                        style="margin-right: 5px;"></i>Une majuscule</li>
                                <li id="req-number" class="status-red"><i class="bi bi-x-circle-fill"
                                        style="margin-right: 5px;"></i>Un chiffre (0-9)</li>
                                <li id="req-special" class="status-red"><i class="bi bi-x-circle-fill"
                                        style="margin-right: 5px;"></i>Un caractère spécial (@, !, #, ...)</li>
                                <li id="req-match" class="status-red"><i class="bi bi-x-circle-fill"
                                        style="margin-right: 5px;"></i>Les mots de passe correspondent</li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <label>Confirmer le mot de passe</label>
                        <input type="password" name="confimer_mdp" id="confimer_mdp"
                             required class="form-control">
                    </div>

                    <div class="col-12 d-flex flex-column align-items-center mt-3">
                        <p class="code_vendeur"> Code vendeur : <strong>VD640</strong> </p>
                        <a class="connexion_lien" href="connexion.php">Déjà vendeur ? Connectez vous ici</a>

                        <button type="submit" id="btn_inscription" class="btn_inscription" disabled>S'inscrire</button>
                    </div>

                </div>
            </form>
        </div>
        <p class="text-footer">
            Alizon, en tant que responsable de traitement, traite les données recueillies à des fins de gestion de la relation client, gestion des commandes et des livraisons, 
            personnalisation des services, prévention de la fraude, marketing et publicité ciblée. 
            Pour en savoir plus, reportez-vous à la Politique de protection de vos données personnelles
        </p>
        
        <script>
            // Eléments 
            const passwordInput = document.getElementById('mdp');
            const confirmPasswordInput = document.getElementById('confimer_mdp');
            const submitButton = document.getElementById('btn_inscription');
            const passwordRequirementsContainer = document.getElementById('password-requirements-container');

            // Critères pour le mdp
            const reqLength = document.getElementById('req-length');
            const reqLowercase = document.getElementById('req-lowercase');
            const reqUppercase = document.getElementById('req-uppercase');
            const reqNumber = document.getElementById('req-number');
            const reqSpecial = document.getElementById('req-special');
            const reqMatch = document.getElementById('req-match');

            // Critères de validation
            const rules = {
                length: { element: reqLength, regex: /^.{12,}$/, message: 'Au moins 12 caractères' },
                lowercase: { element: reqLowercase, regex: /[a-z]/, message: 'Une minuscule' },
                uppercase: { element: reqUppercase, regex: /[A-Z]/, message: 'Une majuscule' },
                number: { element: reqNumber, regex: /[0-9]/, message: 'Un chiffre (0-9)' },
                special: { element: reqSpecial, regex: /[^a-zA-Z0-9]/, message: 'Un caractère spécial (@, !, #, ...)' }
            };

            // Gestion de l'état d'erreur
            function toggleErrorStyle(inputElement) {
                if (inputElement.value.trim() === '') {
                    inputElement.classList.add('input-error');
                } else {
                    inputElement.classList.remove('input-error');
                }
            }

            // Affichage des critères avec les coches et les croix
            function updateRequirement(rule, password) {
                const isValid = rule.regex.test(password);
                const iconClass = isValid ? 'bi-check-circle-fill' : 'bi-x-circle-fill';
                const statusClass = isValid ? 'status-green' : 'status-red';

                rule.element.className = statusClass;
                rule.element.innerHTML = `<i class="bi ${iconClass}" style="margin-right: 5px;"></i>${rule.message}`;
                return isValid;
            }

            // Valide tous les critères et rends le btn inscription ok.
            function validatePassword() {
                const password = passwordInput.value;
                const confirmPassword = confirmPasswordInput.value;
                let allValid = true;

                // Valide chaque règle
                for (const key in rules) {
                    if (!updateRequirement(rules[key], password)) {
                        allValid = false;
                    }
                }

                // Correspondance entre les mdp
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

            passwordInput.addEventListener('blur', () => {
                // Masque les critères si le champ est vide
                if (passwordInput.value.length === 0) {
                    passwordRequirementsContainer.classList.add('hidden');
                }
                toggleErrorStyle(passwordInput);
            });
            
            confirmPasswordInput.addEventListener('blur', () => {
                // Gère l'état vide du champ Confirmer mdp
                toggleErrorStyle(confirmPasswordInput);
            });


            passwordInput.addEventListener('focus', () => {
                passwordRequirementsContainer.classList.remove('hidden');
                // Enlève l'erreur quand l'utilisateur revient dessus
                passwordInput.classList.remove('input-error');
                validatePassword(); 
            });

            passwordInput.addEventListener('input', () => {
                passwordInput.classList.remove('input-error');
                validatePassword(); 
            });
            
            confirmPasswordInput.addEventListener('input', () => {
                confirmPasswordInput.classList.remove('input-error');
                validatePassword(); 
            });
            
            // Empêcher la soumission du formulaire si la validation échoue
            document.querySelector('form').addEventListener('submit', function(e) {
                // Vérifier si les champs sont vides au moment de la soumission du form
                toggleErrorStyle(passwordInput);
                toggleErrorStyle(confirmPasswordInput);
                
                if (!validatePassword() || passwordInput.value.trim() === '' || confirmPasswordInput.value.trim() === '') {
                    e.preventDefault();
                }
            });

            validatePassword(); 
        </script>

        <?php require_once './partials/retourEnHaut.php' ?>
    </main>
    <?php require_once "./partials/footer.php"; ?>
    <script src="../../controllers/Chiffrement.js"></script>
    <script>
        document.querySelector('form').addEventListener('submit', function(e) {
            const passwordInput = document.getElementById('mdp');
            if (passwordInput && typeof vignere !== 'undefined') {
                passwordInput.value = vignere(passwordInput.value, cle, 1);
            }
        });
    </script>
</body>
</html>
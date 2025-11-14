<?php
    if (isset($_COOKIE[session_name()])) {
        session_start(['read_and_close' => true]);
    }

    $pseudo = $_POST['pseudo'] ?? null;
    $prenom = $_POST['prenom'] ?? null;
    $nom = $_POST['nom'] ?? null;
    $email = $_POST['email'] ?? null;
    $num_tel = $_POST['num_tel'] ?? null;
    $mdp = $_POST['mdp'] ?? null;
    $date_naissance = $_POST['date_naissance'] ?? null;

?>
<?php require_once "../../controllers/pdo.php" ?> 
<?php require_once "../../controllers/prix.php" ?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">  
  <link rel="stylesheet" href="../../public/style.css">
  
  <title>Alizon - Acceuil</title>
</head>
<body class="inscription">

  <?php include './partials/headerConnecte.php'; ?>

    <h2>Inscription</h2>
  

      <main>
        <form id="monForm" action="../backend/session_start.php" method="post" enctype="multipart/form-data">

          <!-- Pseudo -->
          <input type="text" placeholder="Pseudo*" id="pseudo" name="pseudo" required />
          <br />
        <div id="refactor">
          <!-- Nom -->
          <input type="text" placeholder="Nom*" id="nom" name="nom" required />
          <br />

          <!-- Prénom -->
          <input type="text" placeholder="Prénom*" id="prenom" name="prenom" required />
          <br />
        </div>
        <div id="refactor">
            <!-- Date de naissance -->
            <input type="text" placeholder="Date de naissance*" id="birthdate" name="birthdate" required/>
            <br />

            <!-- Téléphone -->
            <input type="tel" placeholder="Téléphone*" id="telephone" name="telephone" required/>
            <br />
        </div>
           <!-- Email -->
          <input type="email" placeholder="Email*" id="email*" name="email" required/>
          <br />
          <!-- Mot de passe -->
          <input type="password" placeholder="Mot de passe*" id="mdp" name="motdepasse" required />
          <br />
          <div id="password-requirements-container" class="mt-2 hidden">
              <ul id="password-requirements">
                  <li id="req-length" class="status-red"><i class="bi bi-x-circle-fill" style="margin-right: 5px;">   </i>Au moins 12 caractères</li>
                  <li id="req-lowercase" class="status-red"><i class="bi bi-x-circle-fill" style="margin-right: 5px;"></i>Une minuscule</li>
                  <li id="req-uppercase" class="status-red"><i class="bi bi-x-circle-fill" style="margin-right: 5px;"></i>Une majuscule</li>
                  <li id="req-number" class="status-red"><i class="bi bi-x-circle-fill" style="margin-right: 5px;">   </i>Un chiffre (0-9)</li>
                  <li id="req-special" class="status-red"><i class="bi bi-x-circle-fill" style="margin-right: 5px;">  </i>Un caractère spécial (@, !, #, ...)</li>
                  <li id="req-match" class="status-red"><i class="bi bi-x-circle-fill" style="margin-right: 5px;">    </i>Les mots de passe correspondent</li>
               </ul>
          </div>

          <!-- Confirmer Mot de passe -->
          <input type="password" placeholder="Confirmer le mot de passe*" id="cmdp" name="cmdp" required />
          <br />

          
          <!-- Bouton de soumission -->
          <input id="submitButton" type="submit" value="S'inscrire"/>
        </form> 
        <script>
            // Eléments du DOM
            const pseudoInput = document.getElementById('pseudo');
            const prenomInput = document.getElementById('prenom');
            const nomInput = document.getElementById('nom');
            const birthDateInput = document.getElementById('birthdate');
            const emailInput = document.getElementById('email');
            const phoneNumberInput = document.getElementById('telephone');
            const passwordInput = document.getElementById('mdp');
            const confirmPasswordInput = document.getElementById('cmdp');
            const submitButton = document.getElementById('submitButton');
            const passwordRequirementsContainer = document.getElementById('password-requirements-container');

            // Eléments de critères
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


            
            confirmPasswordInput.addEventListener('blur', () => {
                // Gérer l'état vide/erreur du champ Confirmer MDP
                toggleErrorStyle(confirmPasswordInput);
            });


            passwordInput.addEventListener('focus', () => {
                passwordRequirementsContainer.classList.remove('hidden');
                passwordInput.classList.remove('input-error'); // Enlève l'erreur quand l'utilisateur revient
                validatePassword(); 
            });

            birthDateInput.addEventListener('focus', () =>{
                birthDateInput.classList.remove('input-error');
                validateBirthDate();
            });

            phoneNumberInput.addEventListener('focus', () =>{
                phoneNumberInput.classList.remove('input-error');
                validatePhoneNumber();
            });

            birthDateInput.addEventListener('input', () => {
                birthDateInput.classList.remove('input-error');
            });

            phoneNumberInput.addEventListener('input', () => {
                phoneNumberInput.classList.remove('input-error');
            });

            passwordInput.addEventListener('input', () => {
                passwordInput.classList.remove('input-error');
                validatePassword(); 
            });

            confirmPasswordInput.addEventListener('input', () => {
                confirmPasswordInput.classList.remove('input-error');
                validatePassword(); 
            });

            //////////////////////////////////////////////////////////////////
            //                                                              //
            //        Fonction de validation des critères du formulaire     //
            //                                                              //
            //////////////////////////////////////////////////////////////////
            
            function validateForm(){
                let allValid = true;
                if(!validatePassword()){
                    allValid = false;
                }
                if(!validateBirthDate()){
                    allValid = false;
                }
                if(!validatePhoneNumber()){
                    allValid = false;
                }
                submitButton.disabled = !allValid;
                
                if(allValid){
                    $_POST['pseudo'] = $nomInput.value;        
                    $_POST['prenom'] = $prenomInput.value; 
                    $_POST['email'] = $emailInput.value;          
                    $_POST['num_tel'] = $phoneNumberInput.value;          
                    $_POST['nom'] = $pseudoInput.value;
                    $_POST['mdp'] = $mdpInput.value;              
                    $_POST['confimer_mdp'] = $confirmPasswordInput.value       
                    $_POST['date_naissance'] = $birthDateInput.value;     
                    return true;
                }
                return false
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
                submitButton.disabled = !allValid;

                return allValid;
            }


            function validateBirthDate() { 
                const birthDate = birthDateInput.value.trim();
                if (!/^([0][1-9]|[1-2][0-9]|3[0-1])\/(0[1-9]|1[0-2])\/[0-9]{4}$/.test(birthDate)) {
                    return false;
                }
                return true;
            }

            function validatePhoneNumber() { 
                const phoneNumber = phoneNumberInput.value.trim();
                if (!/^0[67](\s[0-9]{2}){4}$/.test(phoneNumber)) {
                    return false;
                }
                return true;
            }

            //////////////////////////////////////////////////////////////////
            //                                                              //
            //    Fonction de mise à jour des critères des mots de passe    //
            //                                                              //
            //////////////////////////////////////////////////////////////////
            

            // Gestion de l'état d'erreur visuel
            function toggleErrorStyle(inputElement) {
                if (inputElement.value.trim() === '') {
                    inputElement.classList.add('input-error');
                } else {
                    inputElement.classList.remove('input-error');
                }
            }

            // Mise à jour de l'affichage des critères
            function updateRequirement(rule, password) {
                const isValid = rule.regex.test(password);
                const iconClass = isValid ? 'bi-check-circle-fill' : 'bi-x-circle-fill';
                const statusClass = isValid ? 'status-green' : 'status-red';

                rule.element.className = statusClass;
                rule.element.innerHTML = `<i class="bi ${iconClass}" style="margin-right: 5px;"></i>${rule.message}`;
                return isValid;
            }

            //////////////////////////////////////////////////////////////////
            //                                                              //
            //    Bloquage de la validation du formulaire si les champs     //
            //               ne sont pas aux bons formats                   //
            //                                                              //
            //////////////////////////////////////////////////////////////////
            
            document.querySelector('form').addEventListener('submit', function(e) {
                const isPasswordValid = validatePassword();
                const isBirthDateValid = validateBirthDate();
                const isPhoneValid = validatePhoneNumber();

                const passwordEmpty = passwordInput.value.trim() === '';
                const confirmEmpty = confirmPasswordInput.value.trim() === '';
                const birthDateEmpty = birthDateInput.value.trim() === '';
                const phoneEmpty = phoneNumberInput.value.trim() === '';

                toggleErrorStyle(passwordInput);
                toggleErrorStyle(confirmPasswordInput);
                toggleErrorStyle(birthDateInput);
                toggleErrorStyle(phoneNumberInput);
                
                if (!isPasswordValid || passwordEmpty || confirmEmpty) {
                    e.preventDefault();
                }
                if(!isBirthDateValid || birthDateEmpty){
                    birthDateInput.classList.add('input-error');
                    e.preventDefault();
                } else {
                    birthDateInput.classList.remove('input-error');
                }
                if(!isPhoneValid || phoneEmpty){
                    phoneNumberInput.classList.add('input-error');
                    e.preventDefault();
                } else {
                    phoneNumberInput.classList.remove('input-error');
                }
            });

            validateForm(); 

            if(validateForm()){
                <?php
                $nouveauClient = "INSERT INTO _client
                                  (dateNaissance, prenom, nom, email, mdp, noTelephone, pseudo)
                                  VALUES ($date_naissance, $prenom  , $nom, $email, $mdp , $num_tel, $pseudo ;";            
                ?>
            }
        </script>
      </main>

    <?php include '../../views/frontoffice/partials/footerDeconnecte.php'; ?>

</body>
</html>
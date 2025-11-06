<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <link rel="stylesheet" href="../../public/style.css">
  
  <title>Alizon - Acceuil</title>
</head>
<body class="inscription">

  <?php include '../../views/frontoffice/partials/headerConnecte.php'; ?>

    <h2>Inscription</h2>
  

      <main>
        <form id="monForm" action="inscription.php" method="post" enctype="multipart/form-data">

          <!-- Pseudo -->
          <input type="text" placeholder="Pseudo*" id="pseudo" name="pseudo" required />
          <br />

          <!-- Nom -->
          <input type="text" placeholder="Nom*" id="nom" name="nom" required />
          <br />

          <!-- Prénom -->
          <input type="text" placeholder="Prénom*" id="prenom" name="prenom" required />
          <br />

          <!-- Date de naissance -->
          <input type="text" placeholder="Date de naissance :" id="date" name="date" required/>
          <br />
          
          <!-- Email -->
          <input type="email" placeholder="Email*" id="email" name="email" required/>
          <br />

          <!-- Téléphone -->
          <input type="tel" placeholder="Numéro de téléphone" id="telephone" name="telephone" />
          <br />

          <!-- Mot de passe -->
          <input type="password" placeholder="Mot de passe*" id="mdp" name="motdepasse" required />
          <br />

          <!-- Confirmer Mot de passe -->
          <input type="password" placeholder="Confirmer le mot de passe*" id="cmdp" name="cmdp" required />
          <br />

          
          <!-- Bouton de soumission -->
          <input id="button" type="submit" value="S'inscrire"/>
        </form> 

        <script>
            function testMdp(mdp) { 
                return (
                  mdp.length >= 12 &&
                  /[0-9]/.test(mdp) &&
                  /[!@#$%^&*()_+€?/-]/.test(mdp) &&
                  /[A-Z]/.test(mdp) &&
                  /[a-z]/.test(mdp)
                );
              }

              function confirmMdp(mdp) {
                return mdp === document.getElementById("cmdp").value;
              }

              function validerFormulaire(event) {
                const mdpInput = document.getElementById("mdp");
                const cmdpInput = document.getElementById("cmdp");

                // Réinitialise les erreurs
                mdpInput.setCustomValidity("");
                cmdpInput.setCustomValidity("");

                let champCorrects = true;

                if (!testMdp(mdpInput.value)) {
                  mdpInput.setCustomValidity("Votre mot de passe n'est pas assez sécurisé");
                  champCorrects = false;
                }

                if (!confirmMdp(mdpInput.value)) {
                  cmdpInput.setCustomValidity("Les deux mots de passe ne correspondent pas");
                  champCorrects = false;
                }

                if (!champCorrects) {
                    event.preventDefault();
                    // Affiche les erreurs sans boucle infinie
                    if (!testMdp(mdpInput.value)) {
                      mdpInput.reportValidity();
                    } else if (!confirmMdp(mdpInput.value)) {
                      cmdpInput.reportValidity();
                    }
                    return false;
                  
                }
              }

              document
                .getElementById("monForm")
                .addEventListener("submit", validerFormulaire);
          </script>
      </main>


  <?php include '../../views/frontoffice/partials/footerConnecte.php'; ?>

</body>
</html>

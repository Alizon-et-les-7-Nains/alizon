// compteVendeur.js
let modeEdition = false;

function activerModeEdition() {
  modeEdition = true;

  // Activer tous les champs de saisie
  const inputs = document.querySelectorAll("input[readonly]");
  inputs.forEach((input) => {
    input.removeAttribute("readonly");
    input.style.backgroundColor = "white";
    input.style.color = "#212529";
  });

  // Masquer le bouton Modifier et afficher Annuler/Sauvegarder
  document.querySelector(".boutonModifierProfil").style.display = "none";
  document.querySelector(".boutonAnnuler").style.display = "block";
  document.querySelector(".boutonSauvegarder").style.display = "block";
  document.querySelector(".boutonModifierMdp").style.display = "none";
  document.querySelector(".boutonSupprimerCompte").style.display = "none";
}

function desactiverModeEdition() {
  modeEdition = false;

  // Désactiver tous les champs de saisie
  const inputs = document.querySelectorAll('input:not([type="password"])');
  inputs.forEach((input) => {
    input.setAttribute("readonly", "true");
    input.style.backgroundColor = "#f8f9fa";
    input.style.color = "#6c757d";
  });

  // Réinitialiser les valeurs originales (vous pourriez vouloir recharger depuis la BDD)
  // Pour l'instant, on va simplement réafficher les boutons
  document.querySelector(".boutonModifierProfil").style.display = "block";
  document.querySelector(".boutonAnnuler").style.display = "none";
  document.querySelector(".boutonSauvegarder").style.display = "none";
  document.querySelector(".boutonModifierMdp").style.display = "block";
  document.querySelector(".boutonSupprimerCompte").style.display = "block";
}

function popUpModifierMdp() {
  // Implémentez la logique pour la modification du mot de passe
  alert("Fonctionnalité de modification du mot de passe à implémenter");
}

function supprimerCompte() {
  if (
    confirm(
      "Êtes-vous sûr de vouloir supprimer votre compte ? Cette action est irréversible."
    )
  ) {
    // Implémentez la logique de suppression du compte
    alert("Fonctionnalité de suppression de compte à implémenter");
  }
}

function boutonAnnuler() {
  desactiverModeEdition();
  // Optionnel: recharger la page pour réinitialiser les valeurs
  // location.reload();
}

// Événements
document.addEventListener("DOMContentLoaded", function () {
  // Bouton Modifier
  document
    .querySelector(".boutonModifierProfil")
    .addEventListener("click", activerModeEdition);

  // Bouton Annuler
  document
    .querySelector(".boutonAnnuler")
    .addEventListener("click", boutonAnnuler);

  // Validation du formulaire avant soumission
  document.querySelector("form").addEventListener("submit", function (e) {
    if (modeEdition) {
      // Valider les champs ici si nécessaire
      const noSiren = document.getElementById("noSiren").value;
      if (noSiren && !/^\d{9}$/.test(noSiren)) {
        e.preventDefault();
        alert("Le numéro SIREN doit contenir exactement 9 chiffres.");
        return;
      }

      const telephone = document.getElementById("telephone").value;
      if (telephone && !/^\d{10}$/.test(telephone)) {
        e.preventDefault();
        alert("Le numéro de téléphone doit contenir exactement 10 chiffres.");
        return;
      }

      // Si tout est valide, désactiver le mode édition
      desactiverModeEdition();
    }
  });
});

// Fonction pour valider le mot de passe (à utiliser dans la popup de modification)
function validerMotDePasse(motDePasse) {
  const minLength = 12;
  const hasLowercase = /[a-z]/.test(motDePasse);
  const hasUppercase = /[A-Z]/.test(motDePasse);
  const hasNumber = /\d/.test(motDePasse);
  const hasSpecialChar = /[!@#$%^&*(),.?":{}|<>]/.test(motDePasse);

  return (
    motDePasse.length >= minLength &&
    hasLowercase &&
    hasUppercase &&
    hasNumber &&
    hasSpecialChar
  );
}

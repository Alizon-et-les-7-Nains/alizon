let modeEdition = false;
let modeModificationMdp = false;
let anciennesValeurs = {};
let ancienneImageSrc = null;

let ajoutPhoto = document.createElement("input");
ajoutPhoto.type = "file";
ajoutPhoto.id = "photoProfil";
ajoutPhoto.name = "photoProfil";
ajoutPhoto.accept = "image/*";
ajoutPhoto.style.display = "none";
ajoutPhoto.autocomplete = "off";

let conteneur = document.querySelector(".header-compte");
let imageProfile = document.getElementById("imageProfile");

function afficherErreur(champId, afficher) {
  const champ = document.getElementById(champId);
  if (!champ || !champ.parentElement) return;
  const erreurElement = champ.parentElement.querySelector(".field-error");
  if (!erreurElement) return;

  if (afficher) {
    champ.classList.add("error");
    erreurElement.classList.add("show");
    erreurElement.style.display = "block";
  } else {
    champ.classList.remove("error");
    erreurElement.classList.remove("show");
    erreurElement.style.display = "none";
  }
}

function validerChamp(champId, valeur) {
  valeur = valeur == null ? "" : String(valeur);
  switch (champId) {
    case "nom":
    case "prenom":
    case "adresse":
    case "ville":
    case "region":
    case "raisonSociale":
    case "pseudo":
      if (!valeur.trim()) {
        afficherErreur(champId, true);
        return false;
      }
      afficherErreur(champId, false);
      return true;

    case "noSiren":
      if (valeur && !/^\d{9}$/.test(valeur)) {
        afficherErreur(champId, true);
        return false;
      }
      afficherErreur(champId, false);
      return true;

    case "telephone":
      if (valeur) {
        const telephoneNormalized = valeur.replace(/\s+/g, "");
        if (!/^0\d{9}$/.test(telephoneNormalized)) {
          afficherErreur(champId, true);
          return false;
        }
      }
      afficherErreur(champId, false);
      return true;

    case "email":
      if (valeur && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(valeur)) {
        afficherErreur(champId, true);
        return false;
      }
      afficherErreur(champId, false);
      return true;

    case "codePostal":
      if (valeur && !/^\d{5}$/.test(valeur)) {
        afficherErreur(champId, true);
        return false;
      }
      afficherErreur(champId, false);
      return true;

    case "dateNaissance":
      if (valeur) {
        const dateNaissance = new Date(valeur);
        if (isNaN(dateNaissance.getTime())) {
          afficherErreur(champId, true);
          return false;
        }
        const aujourdhui = new Date();
        let age = aujourdhui.getFullYear() - dateNaissance.getFullYear();
        const mois = aujourdhui.getMonth() - dateNaissance.getMonth();
        if (
          mois < 0 ||
          (mois === 0 && aujourdhui.getDate() < dateNaissance.getDate())
        ) {
          age--;
        }
        if (age < 18) {
          afficherErreur(champId, true);
          return false;
        }
      }
      afficherErreur(champId, false);
      return true;

    case "ancienMdp":
      if (modeModificationMdp && !valeur) {
        afficherErreur(champId, true);
        return false;
      }
      afficherErreur(champId, false);
      return true;

    case "nouveauMdp":
      if (modeModificationMdp) {
        if (!valeur) {
          afficherErreur(champId, true);
          return false;
        } else if (!validerCriteresMotDePasse(valeur)) {
          afficherErreur(champId, true);
          return false;
        }
      }
      afficherErreur(champId, false);
      return true;

    case "confirmationMdp":
      if (modeModificationMdp) {
        const nouveauMdpEl = document.getElementById("nouveauMdp");
        const nouveauMdp = nouveauMdpEl ? nouveauMdpEl.value : "";
        if (valeur !== nouveauMdp) {
          afficherErreur(champId, true);
          return false;
        }
      }
      afficherErreur(champId, false);
      return true;

    default:
      return true;
  }
}

function activerModeEdition() {
  modeEdition = true;

  // Sauvegarder les anciennes valeurs (text, email, tel, date)
  const inputsToSave = document.querySelectorAll(
    'input[type="text"], input[type="email"], input[type="tel"], input[type="date"]'
  );
  anciennesValeurs = {}; // reset
  inputsToSave.forEach((input) => {
    if (input.id) {
      anciennesValeurs[input.id] = input.value;
    }
  });

  // Activer tous les champs de saisie ciblés
  const inputsEditables = document.querySelectorAll(
    'input[type="text"], input[type="email"], input[type="tel"], input[type="date"]'
  );
  inputsEditables.forEach((input) => {
    if (!input.id) return;
    input.removeAttribute("readonly");
    input.style.backgroundColor = "white";
    input.style.color = "#212529";

    // Ajouter écouteurs s'ils ne sont pas déjà attachés
    const clean = input.cloneNode(true);
    input.parentNode.replaceChild(clean, input);
    clean.addEventListener("input", function () {
      validerChamp(this.id, this.value);
    });
    clean.addEventListener("blur", function () {
      validerChamp(this.id, this.value);
    });
  });

  // Activer la modification de la photo de profil (comme client)
  // Sauvegarder la source actuelle de l'image pour restauration si annulation
  if (imageProfile && imageProfile.src) {
    ancienneImageSrc = imageProfile.src;
  } else {
    ancienneImageSrc = null;
  }

  // Réinitialiser l'input fichier et l'ajouter
  ajoutPhoto.value = "";
  conteneur.appendChild(ajoutPhoto);

  if (imageProfile) {
    imageProfile.style.cursor = "pointer";
    imageProfile.onclick = () => ajoutPhoto.click();
  }

  // Masquer le bouton Modifier et afficher Annuler/Sauvegarder
  const btnModifier = document.querySelector(".boutonModifierProfil");
  const btnAnnuler = document.querySelector(".boutonAnnuler");
  const btnSauvegarder = document.querySelector(".boutonSauvegarder");
  const btnModifierMdp = document.querySelector(".boutonModifierMdp");

  if (btnModifier) btnModifier.style.display = "none";
  if (btnAnnuler) btnAnnuler.style.display = "block";
  if (btnSauvegarder) btnSauvegarder.style.display = "block";
  if (btnModifierMdp) btnModifierMdp.style.display = "none";
}

function desactiverModeEdition() {
  modeEdition = false;
  modeModificationMdp = false;

  // Cacher toutes les erreurs
  const erreurs = document.querySelectorAll(".field-error");
  erreurs.forEach((erreur) => {
    erreur.classList.remove("show");
    erreur.style.display = "none";
  });

  // Retirer classes d'erreur des inputs
  const allInputs = document.querySelectorAll("input");
  allInputs.forEach((input) => input.classList.remove("error"));

  // Désactiver tous les champs de saisie (incl. password)
  const inputsEditables = document.querySelectorAll(
    'input[type="text"], input[type="email"], input[type="tel"], input[type="date"], input[type="password"]'
  );
  inputsEditables.forEach((input) => {
    input.setAttribute("readonly", "true");
    input.style.backgroundColor = "#f8f9fa";
    input.style.color = "#6c757d";

    // Vider les champs de mot de passe
    if (input.type === "password") {
      input.value = "";
    }

    const newInput = input.cloneNode(true);
    input.parentNode.replaceChild(newInput, input);
  });

  if (document.getElementById("photoProfil")) {
    document.getElementById("photoProfil").remove();
  }

  if (imageProfile) {
    imageProfile.style.cursor = "default";
    imageProfile.onclick = null;
  }

  // Réafficher les boutons
  const btnModifier = document.querySelector(".boutonModifierProfil");
  const btnAnnuler = document.querySelector(".boutonAnnuler");
  const btnSauvegarder = document.querySelector(".boutonSauvegarder");

  if (btnModifier) btnModifier.style.display = "block";
  if (btnAnnuler) btnAnnuler.style.display = "none";
  if (btnSauvegarder) btnSauvegarder.style.display = "none";

}

function activerModificationMdp() {
  modeModificationMdp = true;

  const champsMdp = document.querySelectorAll('input[type="password"]');
  champsMdp.forEach((input) => {
    input.removeAttribute("readonly");
    input.style.backgroundColor = "white";
    input.style.color = "#212529";

    const clean = input.cloneNode(true);
    input.parentNode.replaceChild(clean, input);
    clean.addEventListener("input", function () {
      validerChamp(this.id, this.value);
    });
    clean.addEventListener("blur", function () {
      validerChamp(this.id, this.value);
    });
  });

  // Afficher/masquer les boutons pour indiquer qu'une édition est en cours
  const btnModifier = document.querySelector(".boutonModifierProfil");
  const btnAnnuler = document.querySelector(".boutonAnnuler");
  const btnSauvegarder = document.querySelector(".boutonSauvegarder");
  const btnModifierMdp = document.querySelector(".boutonModifierMdp");

  if (btnModifier) btnModifier.style.display = "none";
  if (btnAnnuler) btnAnnuler.style.display = "block";
  if (btnSauvegarder) btnSauvegarder.style.display = "block";
}

function desactiverModificationMdp() {
  modeModificationMdp = false;

  ["ancienMdp", "nouveauMdp", "confirmationMdp"].forEach((champId) => {
    afficherErreur(champId, false);
  });

  const champsMdp = document.querySelectorAll('input[type="password"]');
  champsMdp.forEach((input) => {
    input.setAttribute("readonly", "true");
    input.style.backgroundColor = "#f8f9fa";
    input.style.color = "#6c757d";
    input.value = "";

    const newInput = input.cloneNode(true);
    input.parentNode.replaceChild(newInput, input);
  });

  // Restaurer l'état des boutons : si on n'est pas en mode édition complet,
  // masquer Sauvegarder/Annuler et afficher le bouton Modifier principal.
  const btnModifier = document.querySelector(".boutonModifierProfil");
  const btnAnnuler = document.querySelector(".boutonAnnuler");
  const btnSauvegarder = document.querySelector(".boutonSauvegarder");
  const btnModifierMdp = document.querySelector(".boutonModifierMdp");

  if (!modeEdition) {
    if (btnModifier) btnModifier.style.display = "block";
    if (btnAnnuler) btnAnnuler.style.display = "none";
    if (btnSauvegarder) btnSauvegarder.style.display = "none";
  }

  if (btnModifierMdp) {
    btnModifierMdp.style.display = "inline-block";
  }
}

function toggleModificationMdp() {
  if (modeModificationMdp) {
    desactiverModificationMdp();
  } else {
    // Activer seulement les champs mot de passe sans activer le mode édition complet
    activerModificationMdp();
  }
}

function restaurerAnciennesValeurs() {
  const inputs = document.querySelectorAll(
    'input[type="text"], input[type="email"], input[type="tel"], input[type="date"]'
  );
  inputs.forEach((input) => {
    if (input.id && anciennesValeurs.hasOwnProperty(input.id)) {
      input.value = anciennesValeurs[input.id];
      validerChamp(input.id, input.value);
    }
  });

  // Restaurer l'image de profil si elle a été sauvegardée
  if (
    imageProfile &&
    typeof ancienneImageSrc !== "undefined" &&
    ancienneImageSrc !== null
  ) {
    imageProfile.src = ancienneImageSrc;
  }

  // Réinitialiser l'input fichier si présent
  const photoInput = document.getElementById("photoProfil");
  if (photoInput) {
    try {
      photoInput.value = "";
    } catch (e) {
      // certains navigateurs n'autorisent pas la réinitialisation programmatique
    }
  }

  // On remet à null la valeur sauvegardée (restauration effectuée)
  ancienneImageSrc = null;
}

function validerFormulaire() {
  let formulaireValide = true;

  const champs = [
    "nom",
    "prenom",
    "dateNaissance",
    "adresse",
    "codePostal",
    "ville",
    "region",
    "telephone",
    "email",
    "raisonSociale",
    "noSiren",
    "pseudo",
  ];

  for (const champId of champs) {
    const el = document.getElementById(champId);
    const valeur = el ? el.value : "";
    if (!validerChamp(champId, valeur)) {
      formulaireValide = false;
    }
  }

  if (modeModificationMdp) {
    const ancienEl = document.getElementById("ancienMdp");
    const nouveauEl = document.getElementById("nouveauMdp");
    const confirmEl = document.getElementById("confirmationMdp");
    const ancienMdp = ancienEl ? ancienEl.value : "";
    const nouveauMdp = nouveauEl ? nouveauEl.value : "";
    const confirmationMdp = confirmEl ? confirmEl.value : "";

    if (
      !validerChamp("ancienMdp", ancienMdp) ||
      !validerChamp("nouveauMdp", nouveauMdp) ||
      !validerChamp("confirmationMdp", confirmationMdp)
    ) {
      formulaireValide = false;
    }

    // Vérifier l'ancien mot de passe si possible (variables présentes)
    if (formulaireValide) {
      try {
        if (
          typeof vignere === "function" &&
          typeof mdpCrypte !== "undefined" &&
          typeof cle !== "undefined"
        ) {
          const mdpDecrypte = vignere(mdpCrypte, cle, -1);
          if (ancienMdp !== mdpDecrypte) {
            afficherErreur("ancienMdp", true);
            formulaireValide = false;
          }
        }
        // else: skip client-side check if not available
      } catch (error) {
        console.error("Erreur lors du chiffrement/déchiffrement:", error);
        alert("Erreur lors de la validation du mot de passe.");
        formulaireValide = false;
      }
    }
  }

  return formulaireValide;
}

function validerCriteresMotDePasse(mdp) {
  if (!mdp || typeof mdp !== "string") return false;
  if (mdp.length < 12) return false;
  if (!/[a-z]/.test(mdp)) return false;
  if (!/[A-Z]/.test(mdp)) return false;
  if (!/\d/.test(mdp)) return false;
  if (!/[^a-zA-Z0-9]/.test(mdp)) return false;
  return true;
}

function afficherMessageCriteresMdp() {
  const nouveauMdp = document.getElementById("nouveauMdp");
  const reglesMdp = document.querySelector(".mpd-rules");
  if (!nouveauMdp || !reglesMdp) return;

  nouveauMdp.addEventListener("input", function () {
    const mdp = this.value || "";
    const regles = reglesMdp.querySelectorAll("li");
    if (regles.length >= 4) {
      regles[0].style.color = mdp.length >= 12 ? "green" : "inherit";
      regles[1].style.color =
        /[a-z]/.test(mdp) && /[A-Z]/.test(mdp) ? "green" : "inherit";
      regles[2].style.color = /\d/.test(mdp) ? "green" : "inherit";
      regles[3].style.color = /[^a-zA-Z0-9]/.test(mdp) ? "green" : "inherit";
    }
  });
}

ajoutPhoto.addEventListener("change", function () {
  const fichier = this.files[0];
  if (fichier) {
    const reader = new FileReader();
    reader.onload = function (e) {
      if (imageProfile) {
        imageProfile.src = e.target.result;
      }
    };
    reader.readAsDataURL(fichier);
  }
});

function boutonAnnuler() {
  restaurerAnciennesValeurs();
  desactiverModeEdition();
}

// Événements DOM
document.addEventListener("DOMContentLoaded", function () {
  // Initialisation - cacher toutes les erreurs au chargement
  const erreurs = document.querySelectorAll(".field-error");
  erreurs.forEach((erreur) => {
    erreur.style.display = "none";
  });

  desactiverModeEdition();
  afficherMessageCriteresMdp();

  const btnModifier = document.querySelector(".boutonModifierProfil");
  const btnAnnuler = document.querySelector(".boutonAnnuler");
  const btnModifierMdp = document.querySelector(".boutonModifierMdp");
  const form = document.querySelector("form");

  if (btnModifier) btnModifier.addEventListener("click", activerModeEdition);
  if (btnAnnuler) btnAnnuler.addEventListener("click", boutonAnnuler);
  if (btnModifierMdp)
    btnModifierMdp.addEventListener("click", function (e) {
      e.preventDefault();
      toggleModificationMdp();
    });

  if (form) {
    form.addEventListener("submit", function (e) {
      if (!validerFormulaire()) {
        e.preventDefault();
        alert(
          "Veuillez corriger les erreurs dans le formulaire avant de sauvegarder."
        );
        return false;
      }

      // Afficher un indicateur de chargement
      const boutonSauvegarder = document.querySelector(".boutonSauvegarder");
      if (boutonSauvegarder) {
        boutonSauvegarder.dataset._originalText = boutonSauvegarder.textContent;
        boutonSauvegarder.textContent = "Sauvegarde...";
        boutonSauvegarder.disabled = true;
      }

      // Réactiver temporairement les champs pour l'envoi du formulaire
      const inputsReadonly = document.querySelectorAll("input[readonly]");
      inputsReadonly.forEach((input) => input.removeAttribute("readonly"));

      // Le formulaire peut maintenant être soumis normalement
      console.log("Formulaire validé, soumission en cours...");
    });
  }

  // Empêcher la soumission du formulaire avec Enter sauf en mode édition
  document.querySelectorAll("input").forEach((input) => {
    input.addEventListener("keydown", function (e) {
      if (e.key === "Enter" && !modeEdition) {
        e.preventDefault();
      }
    });
  });
});

// Export pour les tests (Node)
if (typeof module !== "undefined" && module.exports) {
  module.exports = {
    activerModeEdition,
    desactiverModeEdition,
    activerModificationMdp,
    desactiverModificationMdp,
    validerFormulaire,
    validerCriteresMotDePasse,
    validerChamp,
    afficherErreur,
    restaurerAnciennesValeurs,
    toggleModificationMdp,
  };
}

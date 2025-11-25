
let modeEdition = false;
let modeModificationMdp = false;
let anciennesValeurs = {};
let ancienneImageSrc = null;

// Input fichier invisible utilisé pour la modification de la photo de profil
let ajoutPhoto = document.createElement("input");
ajoutPhoto.type = "file";
ajoutPhoto.id = "photoProfil";
ajoutPhoto.name = "photoProfil";
ajoutPhoto.accept = "image/*";
ajoutPhoto.style.display = "none";
ajoutPhoto.autocomplete = "off";

// Références DOM réutilisées
let conteneur = document.querySelector(".header-compte");
let imageProfile = document.getElementById("imageProfile");

// Affiche ou masque le message d'erreur pour un champ donné
// - `champId`: id de l'input à contrôler
// - `afficher`: boolean, true pour afficher l'erreur, false pour la masquer
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

// Valide un champ en fonction de son `id` et de la valeur fournie.
// - La validation est activée uniquement en mode édition ou modification
//   du mot de passe.
// - Retourne true si le champ est valide, false sinon, et affiche/masque
//   le message d'erreur correspondant via `afficherErreur`.
function validerChamp(champId, valeur) {
  valeur = valeur == null ? "" : String(valeur);

  // En mode consultation, on ne valide pas
  if (!modeEdition && !modeModificationMdp) {
    afficherErreur(champId, false);
    return true;
  }

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

// Passe l'interface en mode édition profil (modification des champs
// utilisateur). Sauvegarde les valeurs actuelles pour pouvoir les
// restaurer si l'utilisateur annule.
function activerModeEdition() {
  modeEdition = true;
  modeModificationMdp = false;

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

    // Ajouter écouteurs s'ils ne sont pas déjà attachés: on clone l'input
    // pour supprimer d'éventuels handlers existants, puis on attache les
    // nouveaux écouteurs nécessaires à la validation en direct.
    const clean = input.cloneNode(true);
    input.parentNode.replaceChild(clean, input);
    clean.addEventListener("input", function () {
      validerChamp(this.id, this.value);
    });
    clean.addEventListener("blur", function () {
      validerChamp(this.id, this.value);
    });
  });

  // Désactiver les champs mot de passe pendant la modification du profil
  const champsMdp = document.querySelectorAll('input[type="password"]');
  champsMdp.forEach((input) => {
    input.setAttribute("readonly", "true");
    input.style.backgroundColor = "#f8f9fa";
    input.style.color = "#6c757d";
    input.value = "";
  });

  // Activer la modification de la photo de profil (via l'input fichier
  // invisible créé plus haut). On conserve la source actuelle pour
  // pouvoir la restaurer.
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

  // Mettre à jour l'interface: cacher certains boutons et afficher ceux
  // liés à l'annulation/sauvegarde
  const btnModifier = document.querySelector(".boutonModifierProfil");
  const btnModifierMdp = document.querySelector(".boutonModifierMdp");
  const btnAnnuler = document.querySelector(".boutonAnnuler");
  const btnSauvegarder = document.querySelector(".boutonSauvegarder");

  if (btnModifier) btnModifier.style.display = "none";
  if (btnModifierMdp) btnModifierMdp.style.display = "none";
  if (btnAnnuler) btnAnnuler.style.display = "block";
  if (btnSauvegarder) btnSauvegarder.style.display = "block";
}

function toggleModificationMdp() {
  if (modeModificationMdp) {
    desactiverModificationMdp();
  } else {
    // Désactiver d'abord le mode édition si actif
    if (modeEdition) {
      desactiverModeEdition();
    }
    activerModificationMdp();
  }
}

// Passe l'interface en mode modification du mot de passe. Tous les champs
// non-password sont rendus readonly pour éviter les modifications
// simultanées du profil et du mot de passe.
function activerModificationMdp() {
  modeModificationMdp = true;
  modeEdition = false;

  const champsMdp = document.querySelectorAll('input[type="password"]');
  champsMdp.forEach((input) => {
    input.removeAttribute("readonly");
    input.style.backgroundColor = "white";
    input.style.color = "#212529";
    input.required = true;

    const clean = input.cloneNode(true);
    input.parentNode.replaceChild(clean, input);
    clean.addEventListener("input", function () {
      validerChamp(this.id, this.value);
    });
    clean.addEventListener("blur", function () {
      validerChamp(this.id, this.value);
    });
  });

  // Désactiver tous les autres champs
  const autresChamps = document.querySelectorAll(
    'input[type="text"], input[type="email"], input[type="tel"], input[type="date"]'
  );
  autresChamps.forEach((input) => {
    input.setAttribute("readonly", "true");
    input.style.backgroundColor = "#f8f9fa";
    input.style.color = "#6c757d";
  });

  // Désactiver la modification de photo
  if (document.getElementById("photoProfil")) {
    document.getElementById("photoProfil").remove();
  }
  if (imageProfile) {
    imageProfile.style.cursor = "default";
    imageProfile.onclick = null;
  }

  // Mettre à jour l'interface
  const btnModifier = document.querySelector(".boutonModifierProfil");
  const btnModifierMdp = document.querySelector(".boutonModifierMdp");
  const btnAnnuler = document.querySelector(".boutonAnnuler");
  const btnSauvegarder = document.querySelector(".boutonSauvegarder");

  if (btnModifier) btnModifier.style.display = "none";
  if (btnModifierMdp) {
    btnModifierMdp.textContent = "Annuler modification";
    btnModifierMdp.classList.add("annuler-mdp");
  }
  if (btnAnnuler) btnAnnuler.style.display = "block";
  if (btnSauvegarder) btnSauvegarder.style.display = "block";
}

// Désactive le mode modification du mot de passe et remet l'interface à
// l'état de consultation des mots de passe.
function desactiverModificationMdp() {
  modeModificationMdp = false;

  // Réinitialiser les champs mot de passe
  ["ancienMdp", "nouveauMdp", "confirmationMdp"].forEach((champId) => {
    afficherErreur(champId, false);
  });

  const champsMdp = document.querySelectorAll('input[type="password"]');
  champsMdp.forEach((input) => {
    input.setAttribute("readonly", "true");
    input.style.backgroundColor = "#f8f9fa";
    input.style.color = "#6c757d";
    input.value = "";
    input.required = false;

    const newInput = input.cloneNode(true);
    input.parentNode.replaceChild(newInput, input);
  });

  // Mettre à jour l'interface
  const btnModifier = document.querySelector(".boutonModifierProfil");
  const btnModifierMdp = document.querySelector(".boutonModifierMdp");
  const btnAnnuler = document.querySelector(".boutonAnnuler");
  const btnSauvegarder = document.querySelector(".boutonSauvegarder");

  if (btnModifier) btnModifier.style.display = "block";
  if (btnModifierMdp) {
    btnModifierMdp.textContent = "Modifier le mot de passe";
    btnModifierMdp.classList.remove("annuler-mdp");
  }
  if (btnAnnuler) btnAnnuler.style.display = "none";
  if (btnSauvegarder) btnSauvegarder.style.display = "none";
}

// Désactive le mode édition et restaure l'interface en mode consultation.
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

  // RÉAFFICHER tous les boutons dans l'état initial
  const btnModifier = document.querySelector(".boutonModifierProfil");
  const btnModifierMdp = document.querySelector(".boutonModifierMdp");
  const btnAnnuler = document.querySelector(".boutonAnnuler");
  const btnSauvegarder = document.querySelector(".boutonSauvegarder");

  if (btnModifier) btnModifier.style.display = "block";
  if (btnModifierMdp) {
    btnModifierMdp.style.display = "inline-block";
    btnModifierMdp.textContent = "Modifier le mot de passe";
    btnModifierMdp.classList.remove("annuler-mdp");
  }
  if (btnAnnuler) btnAnnuler.style.display = "none";
  if (btnSauvegarder) btnSauvegarder.style.display = "none";
}

// Restaurer les valeurs précédemment sauvegardées (avant édition)
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

// Valide l'ensemble du formulaire selon le mode actif (édition ou mdp).
// - Retourne true si tout est valide.
function validerFormulaire() {
  let formulaireValide = true;

  // Si on n'est pas en mode édition, on ne valide pas
  if (!modeEdition && !modeModificationMdp) {
    return true;
  }

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

  // Valider les champs normaux seulement en mode édition
  if (modeEdition) {
    for (const champId of champs) {
      const el = document.getElementById(champId);
      const valeur = el ? el.value : "";
      if (!validerChamp(champId, valeur)) {
        formulaireValide = false;
      }
    }
  }

  // Valider les champs mot de passe seulement en mode modification mdp
  if (modeModificationMdp) {
    const ancienEl = document.getElementById("ancienMdp");
    const nouveauEl = document.getElementById("nouveauMdp");
    const confirmEl = document.getElementById("confirmationMdp");
    const ancienMdp = ancienEl ? ancienEl.value : "";
    const nouveauMdp = nouveauEl ? nouveauEl.value : "";
    const confirmationMdp = confirmEl ? confirmEl.value : "";

    // Vérifier que tous les champs sont remplis
    if (!ancienMdp || !nouveauMdp || !confirmationMdp) {
      if (!ancienMdp) afficherErreur("ancienMdp", true);
      if (!nouveauMdp) afficherErreur("nouveauMdp", true);
      if (!confirmationMdp) afficherErreur("confirmationMdp", true);
      formulaireValide = false;
    } else {
      // Valider chaque champ individuellement
      if (
        !validerChamp("ancienMdp", ancienMdp) ||
        !validerChamp("nouveauMdp", nouveauMdp) ||
        !validerChamp("confirmationMdp", confirmationMdp)
      ) {
        formulaireValide = false;
      }

    }
  }

  return formulaireValide;
}

// Vérifie les règles de robustesse du mot de passe localement.
// - Retourne true si toutes les conditions sont satisfaites.
function validerCriteresMotDePasse(mdp) {
  if (!mdp || typeof mdp !== "string") return false;
  if (mdp.length < 12) return false;
  if (!/[a-z]/.test(mdp)) return false;
  if (!/[A-Z]/.test(mdp)) return false;
  if (!/\d/.test(mdp)) return false;
  if (!/[^a-zA-Z0-9]/.test(mdp)) return false;
  return true;
}

// Met à jour visuellement la liste des règles de mot de passe pendant la
// saisie pour donner un retour en temps réel à l'utilisateur.
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
        // Afficher l'aperçu de la nouvelle image sélectionnée
        imageProfile.src = e.target.result;
      }
    };
    reader.readAsDataURL(fichier);
  }
});

function boutonAnnuler() {
  if (modeModificationMdp) {
    desactiverModificationMdp();
  } else {
    restaurerAnciennesValeurs();
    desactiverModeEdition();
  }
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
      console.log("=== DÉBUT SOUMISSION ===");
      console.log("Mode édition:", modeEdition);
      console.log("Mode modification mdp:", modeModificationMdp);

      // Si on n'est ni en mode édition, ni en mode mdp, on bloque.
      if (!modeEdition && !modeModificationMdp) {
        e.preventDefault(); // Bloquer la soumission (ne devrait pas arriver)
        return;
      }

      // Valider le formulaire
      if (!validerFormulaire()) {
        console.log(" Validation échouée");
        // Empêcher la soumission UNIQUEMENT si la validation échoue
        e.preventDefault();
        alert(
          "Veuillez corriger les erreurs dans le formulaire avant de sauvegarder."
        );
        return; // Arrêter l'exécution
      }

      // Si on arrive ici, la validation est RÉUSSIE.
      console.log(" Validation réussie");

      // Retirer les attributs 'readonly' des champs mot de passe s'ils existent
      const champsMdp = document.querySelectorAll('input[type="password"]');
      champsMdp.forEach((input) => input.removeAttribute("readonly"));

      // Afficher un indicateur de chargement
      const boutonSauvegarder = document.querySelector(".boutonSauvegarder");
      if (boutonSauvegarder) {
        boutonSauvegarder.dataset._originalText = boutonSauvegarder.textContent;
        boutonSauvegarder.textContent = "Sauvegarde...";
        boutonSauvegarder.disabled = true;
      }

      console.log(" Soumission du formulaire...");
    });
  }

  // Empêcher la soumission du formulaire avec Enter sauf en mode édition
  document.querySelectorAll("input").forEach((input) => {
    input.addEventListener("keydown", function (e) {
      if (e.key === "Enter" && !modeEdition && !modeModificationMdp) {
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

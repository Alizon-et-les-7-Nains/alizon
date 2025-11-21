let modeEdition = false;
let modeModificationMdp = false;
let anciennesValeurs = {};

function afficherErreur(champId, afficher) {
  const champ = document.getElementById(champId);
  const erreurElement = champ.parentElement.querySelector(".field-error");

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
        const aujourdhui = new Date();
        const age = aujourdhui.getFullYear() - dateNaissance.getFullYear();
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
        const nouveauMdp = document.getElementById("nouveauMdp").value;
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

  // Sauvegarder les anciennes valeurs
  const inputs = document.querySelectorAll(
    'input[type="text"], input[type="email"], input[type="tel"], input[type="date"]'
  );
  inputs.forEach((input) => {
    anciennesValeurs[input.id] = input.value;
  });

  // Activer tous les champs de saisie
  const inputsEditables = document.querySelectorAll(
    'input[type="text"], input[type="email"], input[type="tel"], input[type="date"]'
  );
  inputsEditables.forEach((input) => {
    if (input.id !== "") {
      input.removeAttribute("readonly");
      input.style.backgroundColor = "white";
      input.style.color = "#212529";

      // Ajouter les écouteurs d'événements pour la validation en temps réel
      input.addEventListener("input", function () {
        validerChamp(this.id, this.value);
      });

      input.addEventListener("blur", function () {
        validerChamp(this.id, this.value);
      });
    }
  });

  // Masquer le bouton Modifier et afficher Annuler/Sauvegarder
  document.querySelector(".boutonModifierProfil").style.display = "none";
  document.querySelector(".boutonAnnuler").style.display = "block";
  document.querySelector(".boutonSauvegarder").style.display = "block";
  document.querySelector(".boutonModifierMdp").style.display = "none";
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

  const inputs = document.querySelectorAll("input");
  inputs.forEach((input) => {
    input.classList.remove("error");
  });

  // Désactiver tous les champs de saisie
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

    // Retirer les écouteurs d'événements
    const newInput = input.cloneNode(true);
    input.parentNode.replaceChild(newInput, input);
  });

  // Réafficher les boutons
  document.querySelector(".boutonModifierProfil").style.display = "block";
  document.querySelector(".boutonAnnuler").style.display = "none";
  document.querySelector(".boutonSauvegarder").style.display = "none";
  document.querySelector(".boutonModifierMdp").style.display = "block";

  // Remettre le texte original du bouton modifier mot de passe
  const boutonMdp = document.querySelector(".boutonModifierMdp");
  boutonMdp.textContent = "Modifier le mot de passe";
  boutonMdp.classList.remove("annuler-mdp");
}

function activerModificationMdp() {
  modeModificationMdp = true;

  // Activer les champs de mot de passe
  const champsMdp = document.querySelectorAll('input[type="password"]');
  champsMdp.forEach((input) => {
    input.removeAttribute("readonly");
    input.style.backgroundColor = "white";
    input.style.color = "#212529";

    // Ajouter les écouteurs d'événements pour la validation en temps réel
    input.addEventListener("input", function () {
      validerChamp(this.id, this.value);
    });

    input.addEventListener("blur", function () {
      validerChamp(this.id, this.value);
    });
  });

  // Changer le texte du bouton
  document.querySelector(".boutonModifierMdp").textContent =
    "Annuler modification mot de passe";
  document.querySelector(".boutonModifierMdp").classList.add("annuler-mdp");
}

function desactiverModificationMdp() {
  modeModificationMdp = false;

  // Cacher les erreurs de mot de passe
  ["ancienMdp", "nouveauMdp", "confirmationMdp"].forEach((champId) => {
    afficherErreur(champId, false);
  });

  // Désactiver et vider les champs de mot de passe
  const champsMdp = document.querySelectorAll('input[type="password"]');
  champsMdp.forEach((input) => {
    input.setAttribute("readonly", "true");
    input.style.backgroundColor = "#f8f9fa";
    input.style.color = "#6c757d";
    input.value = "";

    // Retirer les écouteurs d'événements
    const newInput = input.cloneNode(true);
    input.parentNode.replaceChild(newInput, input);
  });

  // Remettre le texte original du bouton
  document.querySelector(".boutonModifierMdp").textContent =
    "Modifier le mot de passe";
  document.querySelector(".boutonModifierMdp").classList.remove("annuler-mdp");
}

function toggleModificationMdp() {
  if (modeModificationMdp) {
    desactiverModificationMdp();
  } else {
    // Si on est déjà en mode édition générale, activer juste la modification mdp
    if (!modeEdition) {
      // Sinon, activer le mode édition pour permettre la modification
      activerModeEdition();
    }
    activerModificationMdp();
  }
}

function restaurerAnciennesValeurs() {
  const inputs = document.querySelectorAll(
    'input[type="text"], input[type="email"], input[type="tel"], input[type="date"]'
  );
  inputs.forEach((input) => {
    if (anciennesValeurs[input.id]) {
      input.value = anciennesValeurs[input.id];
      // Valider la valeur restaurée
      validerChamp(input.id, input.value);
    }
  });
}

function validerFormulaire() {
  let formulaireValide = true;

  // Valider tous les champs
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

  champs.forEach((champId) => {
    const valeur = document.getElementById(champId).value;
    if (!validerChamp(champId, valeur)) {
      formulaireValide = false;
    }
  });

  // Validation du mot de passe si modification en cours
  if (modeModificationMdp) {
    const ancienMdp = document.getElementById("ancienMdp").value;
    const nouveauMdp = document.getElementById("nouveauMdp").value;
    const confirmationMdp = document.getElementById("confirmationMdp").value;

    if (
      !validerChamp("ancienMdp", ancienMdp) ||
      !validerChamp("nouveauMdp", nouveauMdp) ||
      !validerChamp("confirmationMdp", confirmationMdp)
    ) {
      formulaireValide = false;
    }

    // Vérifier l'ancien mot de passe (déchiffrement côté client pour la validation)
    if (formulaireValide) {
      try {
        const mdpDecrypte = vignere(mdpCrypte, cle, -1);
        if (ancienMdp !== mdpDecrypte) {
          afficherErreur("ancienMdp", true);
          formulaireValide = false;
        }
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
  // Longueur minimale de 12 caractères
  if (mdp.length < 12) {
    return false;
  }

  // Au moins une minuscule
  if (!/[a-z]/.test(mdp)) {
    return false;
  }

  // Au moins une majuscule
  if (!/[A-Z]/.test(mdp)) {
    return false;
  }

  // Au moins un chiffre
  if (!/\d/.test(mdp)) {
    return false;
  }

  // Au moins un caractère spécial
  if (!/[^a-zA-Z0-9]/.test(mdp)) {
    return false;
  }

  return true;
}

function afficherMessageCriteresMdp() {
  const nouveauMdp = document.getElementById("nouveauMdp");
  const reglesMdp = document.querySelector(".mpd-rules");

  if (nouveauMdp && reglesMdp) {
    nouveauMdp.addEventListener("input", function () {
      const mdp = this.value;

      // Mettre à jour l'affichage des règles
      const regles = reglesMdp.querySelectorAll("li");
      regles[0].style.color = mdp.length >= 12 ? "green" : "inherit";
      regles[1].style.color =
        /[a-z]/.test(mdp) && /[A-Z]/.test(mdp) ? "green" : "inherit";
      regles[2].style.color = /\d/.test(mdp) ? "green" : "inherit";
      regles[3].style.color = /[^a-zA-Z0-9]/.test(mdp) ? "green" : "inherit";
    });
  }
}

function boutonAnnuler() {
  restaurerAnciennesValeurs();
  desactiverModeEdition();
}

function uploadPhotoProfil(file) {
  const formData = new FormData();
  formData.append("photoProfil", file);
  formData.append("codeVendeur", codeVendeur);

  console.debug("Upload photo en cours", {
    codeVendeur,
    name: file.name,
    size: file.size,
  });

  fetch("../../controllers/addPhotoVendeur.php", {
    method: "POST",
    body: formData,
  })
    .then((response) => {
      if (!response.ok) throw new Error("HTTP " + response.status);
      return response.json();
    })
    .then((data) => {
      if (data && data.success) {
        console.log("Photo mise à jour avec succès");
        showNotification("Photo de profil mise à jour avec succès", "success");
      } else {
        console.error("Erreur lors du téléchargement:", data && data.message);
        showNotification(
          "Erreur: " + (data && data.message ? data.message : "Erreur serveur"),
          "error"
        );
      }
    })
    .catch((err) => {
      console.error("Erreur upload photo:", err);
      showNotification("Erreur lors du téléchargement de la photo", "error");
    });
}

function initialiserGestionPhoto() {
  const photoProfil = document.querySelector(".photo-profil");
  const inputFile = document.getElementById("uploadPhoto");

  if (photoProfil && inputFile) {
    photoProfil.addEventListener("click", function () {
      if (modeEdition) {
        inputFile.click();
      }
    });

    inputFile.addEventListener("change", function (e) {
      const file = e.target.files[0];
      if (file) {
        // Vérifier le type de fichier
        if (!file.type.match("image.*")) {
          alert("Veuillez sélectionner une image.");
          return;
        }

        // Vérifier la taille du fichier (max 5MB)
        if (file.size > 5 * 1024 * 1024) {
          alert("L'image est trop volumineuse. Taille maximale : 5MB.");
          return;
        }

        // Afficher la preview immédiatement
        const reader = new FileReader();
        reader.onload = function (e) {
          const img = photoProfil.querySelector("img");
          img.src = e.target.result;
        };
        reader.readAsDataURL(file);

        // Uploader la photo
        uploadPhotoProfil(file);
      }
    });
  }
}

function showNotification(message, type) {
  // Créer une notification simple
  const notification = document.createElement("div");
  notification.style.cssText = `
    position: fixed;
    top: 20px;
    right: 20px;
    padding: 15px 20px;
    border-radius: 5px;
    color: white;
    font-weight: bold;
    z-index: 10000;
    transition: opacity 0.3s;
    background-color: ${type === "success" ? "#28a745" : "#dc3545"};
  `;
  notification.textContent = message;

  document.body.appendChild(notification);

  // Supprimer après 3 secondes
  setTimeout(() => {
    notification.style.opacity = "0";
    setTimeout(() => {
      if (notification.parentNode) {
        notification.parentNode.removeChild(notification);
      }
    }, 300);
  }, 3000);
}

function afficherMessagesSession() {
  // Vérifier s'il y a des messages de session à afficher
  const urlParams = new URLSearchParams(window.location.search);
  const success = urlParams.get("success");
  const error = urlParams.get("error");

  if (success) {
    showNotification(success, "success");
    // Nettoyer l'URL
    const newUrl = window.location.pathname;
    window.history.replaceState({}, document.title, newUrl);
  }

  if (error) {
    showNotification("Erreur: " + error, "error");
    // Nettoyer l'URL
    const newUrl = window.location.pathname;
    window.history.replaceState({}, document.title, newUrl);
  }
}

// Événements
document.addEventListener("DOMContentLoaded", function () {
  // Initialisation - cacher toutes les erreurs au chargement
  const erreurs = document.querySelectorAll(".field-error");
  erreurs.forEach((erreur) => {
    erreur.style.display = "none";
  });

  desactiverModeEdition();
  afficherMessageCriteresMdp();
  initialiserGestionPhoto();
  afficherMessagesSession();

  // Bouton Modifier
  document
    .querySelector(".boutonModifierProfil")
    .addEventListener("click", activerModeEdition);

  // Bouton Annuler
  document
    .querySelector(".boutonAnnuler")
    .addEventListener("click", boutonAnnuler);

  // Bouton Modifier mot de passe
  document
    .querySelector(".boutonModifierMdp")
    .addEventListener("click", toggleModificationMdp);

  // Validation du formulaire avant soumission
  document.querySelector("form").addEventListener("submit", function (e) {
    if (!validerFormulaire()) {
      e.preventDefault();
      return false;
    }

    // Afficher un indicateur de chargement
    const boutonSauvegarder = document.querySelector(".boutonSauvegarder");
    const texteOriginal = boutonSauvegarder.textContent;
    boutonSauvegarder.textContent = "Sauvegarde...";
    boutonSauvegarder.disabled = true;

    // Réactiver temporairement les champs pour l'envoi du formulaire
    const inputs = document.querySelectorAll("input[readonly]");
    inputs.forEach((input) => {
      input.removeAttribute("readonly");
    });

    // La désactivation du mode édition se fera après la soumission réussie
    // car le formulaire va recharger la page
  });

  // Empêcher la soumission du formulaire avec Enter sauf en mode édition
  document.querySelectorAll("input").forEach((input) => {
    input.addEventListener("keypress", function (e) {
      if (e.key === "Enter" && !modeEdition) {
        e.preventDefault();
      }
    });
  });
});

// Export pour les tests
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
  };
}

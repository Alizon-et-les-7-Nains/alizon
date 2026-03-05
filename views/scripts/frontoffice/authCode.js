// Gestion de l'authentification à deux facteurs
const form = document.querySelector("#formA2F");
const inputs = form ? form.querySelectorAll('input[type="text"]') : [];
let isBlocked = false;
let blockTimer = null;

// Vérifier le statut de blocage au chargement de la page
if (form) {
  checkBlockStatus();
}

// Automatiser le passage entre les champs
if (inputs.length > 0) {
  inputs.forEach((input, index) => {
    // Focus sur le premier champ au chargement
    if (index === 0 && !isBlocked) {
      input.focus();
    }

    // Passer au champ suivant après saisie
    input.addEventListener("input", (e) => {
      if (isBlocked) {
        e.target.value = "";
        return;
      }

      const value = e.target.value;

      // Ne garder que les chiffres
      if (!/^[0-9]$/.test(value)) {
        e.target.value = "";
        return;
      }

      // Passer au champ suivant si rempli
      if (value && index < inputs.length - 1) {
        inputs[index + 1].focus();
      } else if (value && index === inputs.length - 1) {
        // Petite pause pour permettre l'affichage du dernier chiffre
        setTimeout(() => {
          // Vérifier que tous les champs sont remplis avant de soumettre
          const allFilled = Array.from(inputs).every(
            (inp) => inp.value.length === 1,
          );
          if (allFilled && !isBlocked) {
            form.requestSubmit(); // Utiliser requestSubmit() au lieu de submit()
          }
        }, 50);
      }
    });

    // Gérer la touche Backspace pour revenir en arrière
    input.addEventListener("keydown", (e) => {
      if (e.key === "Backspace" && !e.target.value && index > 0) {
        inputs[index - 1].focus();
      }
    });

    // Gérer le collage d'un code complet
    input.addEventListener("paste", (e) => {
      e.preventDefault();
      const pastedData = e.clipboardData.getData("text").replace(/\D/g, "");

      if (pastedData.length === 6) {
        inputs.forEach((inp, i) => {
          inp.value = pastedData[i] || "";
        });

        // Focus sur le dernier champ et soumettre
        inputs[5].focus();

        // Petite pause puis soumettre
        setTimeout(() => {
          form.requestSubmit();
        }, 50);
      }
    });
  });

  // Gérer la soumission du formulaire
  form.addEventListener("submit", async (e) => {
    e.preventDefault();

    if (isBlocked) {
      return;
    }

    // Récupérer le code complet
    const code = Array.from(inputs)
      .map((inp) => inp.value)
      .join("");

    // Vérifier que tous les champs sont remplis
    if (code.length !== 6) {
      const erreur = document.querySelector("#erreurCodeA2F");
      erreur.textContent = "Veuillez entrer les 6 chiffres";
      erreur.style.display = "block";
      return;
    }

    // Vérifier que tous les caractères sont des chiffres
    if (!/^\d{6}$/.test(code)) {
      const erreur = document.querySelector("#erreurCodeA2F");
      erreur.textContent = "Le code doit contenir uniquement des chiffres";
      erreur.style.display = "block";
      return;
    }

    try {
      const response = await fetch(window.location.href, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({ otp: code }),
      });

      const data = await response.json();

      if (data.success) {
        // Code correct : rediriger vers l'accueil
        window.location.href = "../../views/frontoffice/accueilConnecte.php";
      } else if (data.blocked) {
        // Utilisateur bloqué
        blockUser(data.remainingTime);
      } else {
        // Code incorrect : afficher l'erreur
        const erreur = document.querySelector("#erreurCodeA2F");
        erreur.textContent = data.message || "Code incorrect";

        if (data.attemptsLeft !== undefined) {
          erreur.textContent += ` (${data.attemptsLeft} tentative${data.attemptsLeft > 1 ? "s" : ""} restante${data.attemptsLeft > 1 ? "s" : ""})`;
        }

        erreur.style.display = "block";

        // Réinitialiser les champs
        inputs.forEach((inp) => (inp.value = ""));
        if (!isBlocked) {
          inputs[0].focus();
        }
      }
    } catch (err) {
      console.error("Erreur fetch:", err);
      const erreur = document.querySelector("#erreurCodeA2F");
      erreur.textContent = "Erreur de connexion";
      erreur.style.display = "block";
    }
  });
}

// Fonction pour vérifier le statut de blocage au chargement
async function checkBlockStatus() {
  try {
    const response = await fetch(window.location.href, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({ checkBlock: true }),
    });

    const data = await response.json();

    if (data.blocked) {
      blockUser(data.remainingTime);
    }
  } catch (err) {
    console.error("Erreur vérification blocage:", err);
  }
}

// Fonction pour bloquer l'utilisateur
function blockUser(remainingTime) {
  isBlocked = true;

  // Désactiver tous les champs
  inputs.forEach((inp) => {
    inp.disabled = true;
    inp.value = "";
  });

  // Désactiver le bouton de soumission
  const submitBtn = form.querySelector('button[type="submit"]');
  if (submitBtn) {
    submitBtn.disabled = true;
  }

  // Afficher le message de blocage avec compte à rebours
  const erreur = document.querySelector("#erreurCodeA2F");
  erreur.style.display = "block";
  erreur.style.color = "#ff4444";
  erreur.style.fontWeight = "bold";

  updateBlockMessage(remainingTime);

  // Mettre à jour le compte à rebours chaque seconde
  let timeLeft = remainingTime;
  blockTimer = setInterval(() => {
    timeLeft--;

    if (timeLeft <= 0) {
      clearInterval(blockTimer);
      unblockUser();
    } else {
      updateBlockMessage(timeLeft);
    }
  }, 1000);
}

// Fonction pour mettre à jour le message de blocage
function updateBlockMessage(seconds) {
  const erreur = document.querySelector("#erreurCodeA2F");
  erreur.textContent = `Trop de tentatives échouées. Veuillez patienter ${seconds} seconde${seconds > 1 ? "s" : ""}...`;
}

// Fonction pour débloquer l'utilisateur
function unblockUser() {
  isBlocked = false;

  // Réactiver tous les champs
  inputs.forEach((inp) => {
    inp.disabled = false;
  });

  // Réactiver le bouton de soumission
  const submitBtn = form.querySelector('button[type="submit"]');
  if (submitBtn) {
    submitBtn.disabled = false;
  }

  // Cacher le message d'erreur
  const erreur = document.querySelector("#erreurCodeA2F");
  erreur.style.display = "none";
  erreur.style.color = "red";
  erreur.style.fontWeight = "normal";

  // Focus sur le premier champ
  inputs[0].focus();
}

// Fonction pour fermer la popup A2F
function fermerPopupA2F() {
  // Nettoyer le timer si actif
  if (blockTimer) {
    clearInterval(blockTimer);
  }

  // Vider les champs et afficher un message
  inputs.forEach((inp) => (inp.value = ""));
  // Rediriger vers la page de connexion
  window.location.href = "connexionClient.php";
}

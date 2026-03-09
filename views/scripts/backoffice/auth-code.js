// Gestion de l'authentification à deux facteurs pour backoffice
const form = document.querySelector("#formA2F");
const inputs = form ? form.querySelectorAll('input[type="text"]') : [];
let isBlocked = false;
let blockTimer = null;
let otpTimer = null;
const OTP_DURATION_SECONDS = 30;

// Vérifier le statut de blocage au chargement de la page
if (form) {
  initOtpCountdown();
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
            form.requestSubmit();
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
        const redirectUrl =
          form.dataset.successRedirect || "../../views/backoffice/accueil.php";
        window.location.href = redirectUrl;
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

function ensureOtpTimerStyles() {
  if (document.getElementById("otpTimerStyles")) {
    return;
  }

  const style = document.createElement("style");
  style.id = "otpTimerStyles";
  style.textContent = `
    .big-circle {
      width: 100%;
      display: flex;
      justify-content: center;
      margin: 10px 0 15px;
    }

    .circle {
      width: 100px;
      height: 100px;
      background: conic-gradient(#e4d9ff 360deg, #ffffff 0deg);
      border-radius: 50%;
      position: relative;
      border: #273469 solid 5px;
      transform: scaleX(-1);
    }

    .time {
      font-size: 20px;
      font-weight: bold;
      z-index: 10;
      position: absolute;
      top: 50%;
      left: 50%;
      color: #273469;
      transform: translate(-50%, -50%) scaleX(-1);
    }
  `;

  document.head.appendChild(style);
}

function getSecondsUntilOtpChange() {
  const epoch = Math.floor(Date.now() / 1000);
  const remainder = epoch % OTP_DURATION_SECONDS;
  return remainder === 0
    ? OTP_DURATION_SECONDS
    : OTP_DURATION_SECONDS - remainder;
}

function renderOtpCountdown() {
  const circle = document.querySelector(".circle");
  const time = document.querySelector(".time");
  if (!circle || !time) {
    return;
  }

  const secondsLeft = getSecondsUntilOtpChange();
  const degrees = secondsLeft * (360 / OTP_DURATION_SECONDS);

  circle.style.background = `conic-gradient(#e4d9ff ${degrees}deg, #ffffff 0deg)`;
  time.textContent = `${secondsLeft}s`;
}

function initOtpCountdown() {
  if (!form || form.querySelector(".big-circle")) {
    return;
  }

  ensureOtpTimerStyles();

  const wrapper = document.createElement("div");
  wrapper.className = "big-circle";
  wrapper.innerHTML = `
    <div class="circle">
      <div class="time">30s</div>
    </div>
  `;

  const errorElement = document.querySelector("#erreurCodeA2F");
  if (errorElement) {
    form.insertBefore(wrapper, errorElement);
  } else {
    form.appendChild(wrapper);
  }

  renderOtpCountdown();

  if (otpTimer) {
    clearInterval(otpTimer);
  }

  otpTimer = setInterval(() => {
    renderOtpCountdown();
  }, 1000);
}

// Fonction pour fermer la popup A2F
async function fermerPopupA2F() {
  // Nettoyer le timer si actif
  if (blockTimer) {
    clearInterval(blockTimer);
  }
  if (otpTimer) {
    clearInterval(otpTimer);
  }

  // Vider les champs
  inputs.forEach((inp) => (inp.value = ""));

  try {
    await fetch(window.location.href, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({ cancelA2F: true }),
    });
  } catch (err) {
    console.error("Erreur annulation A2F:", err);
  }

  // Rediriger vers la page de connexion
  window.location.href = "connexion.php";
}

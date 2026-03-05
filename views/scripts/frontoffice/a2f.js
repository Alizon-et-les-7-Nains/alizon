// ===== GESTION DE L'AUTHENTIFICATION À DEUX FACTEURS (A2F) =====
let a2fCurrentSlide = 0;
let a2fTotalSlides = 4;

function handleA2FToggle(isEnabled) {
  if (isEnabled) {
    // Si l'A2F est déjà activée, proposer de la désactiver
    desactiverA2F();
  } else {
    // Sinon, ouvrir le popup pour activer l'A2F
    ouvrirPopupA2F();
  }
}

function ouvrirPopupA2F() {
  const overlay = document.createElement("div");
  overlay.className = "overlayPopUpCompteClient";
  overlay.innerHTML = `
        <main class="mainPopUpA2F">
            <div class="croixFermerLaPage">
                <div></div>
                <div></div>
            </div>

            <!-- Carousel -->
            <div class="carousel-container">
                <!-- Slide 1 -->
                <div class="carousel-slide active" data-index="0">
                    <h2>Activation de l'A2F</h2>
                    <p>
                        L'authentification à deux facteurs (A2F) ajoute une couche de
                        sécurité essentielle à votre compte. Après votre mot de passe, un
                        code temporaire à usage unique vous sera demandé.
                    </p>
                </div>

                <!-- Slide 2 -->
                <div class="carousel-slide" data-index="1">
                    <h2>Comment ça marche ?</h2>
                    <p>
                        Un code à 6 chiffres, valable 30 secondes, est généré sur votre
                        application d'authentification. Ce code change constamment, le
                        rendant inutilisable pour quiconque ne possède pas votre
                        téléphone.
                    </p>
                </div>

                <!-- Slide 3 -->
                <div class="carousel-slide" data-index="2">
                    <h2>Applications compatibles</h2>
                    <p>Choisissez l'une de ces applications gratuites :</p>
                    <div class="apps">
                        <img src="../../public/images/google.png" alt="Google Authenticator" title="Google Authenticator">
                        <img src="../../public/images/microsoft.png" alt="Microsoft Authenticator" title="Microsoft Authenticator">
                        <img src="../../public/images/apple.png" alt="Authy" title="Authy">
                    </div>
                </div>

                <!-- Slide 4 -->
                <div class="carousel-slide" data-index="3">
                    <h2>Scannez le QR Code</h2>
                    <p>
                        Ouvrez votre application, sélectionnez "Ajouter un compte" et
                        scannez ce code.
                    </p>
                    <div id="qrCodeContainer">
                        <!-- QR Code sera généré ici -->
                        <p>Génération du QR code en cours...</p>
                    </div>
                    <p>
                        <small>Ce code sera demandé à votre prochaine connexion.</small>
                    </p>
                    <button onclick="activerA2F()" class="boutonModiferProfil" style="margin-top: 20px; width: auto; padding: 10px 30px;">Activer l'A2F</button>
                </div>

                <!-- Flèches de navigation -->
                <button class="carousel-btn prev" id="a2fPrevBtn">❮</button>
                <button class="carousel-btn next" id="a2fNextBtn">❯</button>

                <!-- Indicateurs (dots) -->
                <div class="carousel-dots" id="a2fCarouselDots">
                    <span class="dot active" data-index="0"></span>
                    <span class="dot" data-index="1"></span>
                    <span class="dot" data-index="2"></span>
                    <span class="dot" data-index="3"></span>
                </div>
            </div>
        </main>
    `;

  document.body.appendChild(overlay);
  a2fCurrentSlide = 0;

  // Fermeture du popup
  overlay.querySelector(".croixFermerLaPage").addEventListener("click", () => {
    overlay.remove();
  });

  // Fermeture en cliquant sur l'overlay
  overlay.addEventListener("click", (e) => {
    if (e.target === overlay) {
      overlay.remove();
    }
  });

  // Initialisation de la navigation
  initA2FCarouselNavigation(overlay);

  // Génération du QR code
  genererQRCodeA2F(overlay);
}

function initA2FCarouselNavigation(overlay) {
  const slides = overlay.querySelectorAll(".carousel-slide");
  const prevBtn = overlay.querySelector("#a2fPrevBtn");
  const nextBtn = overlay.querySelector("#a2fNextBtn");
  const dots = overlay.querySelectorAll(".dot");

  function showSlide(index) {
    if (index >= slides.length) {
      a2fCurrentSlide = 0;
    } else if (index < 0) {
      a2fCurrentSlide = slides.length - 1;
    } else {
      a2fCurrentSlide = index;
    }

    slides.forEach((slide, i) => {
      slide.classList.toggle("active", i === a2fCurrentSlide);
    });

    dots.forEach((dot, i) => {
      dot.classList.toggle("active", i === a2fCurrentSlide);
    });
  }

  prevBtn.addEventListener("click", () => showSlide(a2fCurrentSlide - 1));
  nextBtn.addEventListener("click", () => showSlide(a2fCurrentSlide + 1));

  dots.forEach((dot, index) => {
    dot.addEventListener("click", () => showSlide(index));
  });

  // Navigation clavier
  const keyHandler = (e) => {
    if (!overlay.isConnected) {
      document.removeEventListener("keydown", keyHandler);
      return;
    }
    if (e.key === "ArrowRight") {
      showSlide(a2fCurrentSlide + 1);
    } else if (e.key === "ArrowLeft") {
      showSlide(a2fCurrentSlide - 1);
    } else if (e.key === "Escape") {
      overlay.remove();
    }
  };
  document.addEventListener("keydown", keyHandler);
}

function genererQRCodeA2F(overlay) {
  // Appel AJAX pour générer le secret et obtenir l'URL du QR code
  fetch(window.location.href, {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify({ generateQR: true }),
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success && data.otpauthUrl) {
        const container = overlay.querySelector("#qrCodeContainer");
        container.innerHTML = ""; // Vider le conteneur

        // Créer le canvas pour le QR code
        const canvas = document.createElement("canvas");
        container.appendChild(canvas);

        // Générer le QR code
        QRCode.toCanvas(
          canvas,
          data.otpauthUrl,
          {
            width: 200,
            margin: 2,
            color: {
              dark: "#273469",
              light: "#FFFFFF",
            },
          },
          function (error) {
            if (error) {
              console.error("Erreur génération QR code:", error);
              container.innerHTML =
                '<p class="erreur">Erreur lors de la génération du QR code</p>';
            }
          },
        );

        // Stocker le secret pour l'activation
        overlay.dataset.secret = data.secret;
      } else {
        overlay.querySelector("#qrCodeContainer").innerHTML =
          '<p class="erreur">Erreur lors de la génération du QR code</p>';
      }
    })
    .catch((error) => {
      console.error("Erreur:", error);
      overlay.querySelector("#qrCodeContainer").innerHTML =
        '<p class="erreur">Erreur de connexion</p>';
    });
}

function activerA2F() {
  // Créer un popup avec la même structure que lors de la connexion
  const codePopup = document.createElement("div");
  codePopup.className = "bodyPopupA2f";
  codePopup.innerHTML = `
    <div class="popupA2f">
      <div class="croixFermerLaPage" onclick="fermerPopupActivationA2F(this)">
        <div></div><div></div>
      </div>
      <h1>Authentification à double facteur</h1>
      <p style="margin-bottom: 20px; color: #666;">Entrez le code à 6 chiffres de votre application d'authentification</p>
      <form id="formA2FActivation">
        <div>
          <input type="text" name="num1" id="num1" maxlength="1" pattern="[0-9]" autocomplete="off">
          <input type="text" name="num2" id="num2" maxlength="1" pattern="[0-9]" autocomplete="off">
          <input type="text" name="num3" id="num3" maxlength="1" pattern="[0-9]" autocomplete="off">
          <input type="text" name="num4" id="num4" maxlength="1" pattern="[0-9]" autocomplete="off">
          <input type="text" name="num5" id="num5" maxlength="1" pattern="[0-9]" autocomplete="off">
          <input type="text" name="num6" id="num6" maxlength="1" pattern="[0-9]" autocomplete="off">
        </div>
        <p class="erreur" id="erreurCodeA2FActivation" style="display: none; color: red; margin-top: 15px;"></p>
        <button type="submit">Vérifier</button>
      </form>
    </div>
  `;

  document.body.appendChild(codePopup);

  const form = document.getElementById("formA2FActivation");
  const inputs = form.querySelectorAll('input[type="text"]');
  const erreurElement = document.getElementById("erreurCodeA2FActivation");

  // Automatiser le passage entre les champs (même logique que authCode.js)
  inputs.forEach((input, index) => {
    // Focus sur le premier champ au chargement
    if (index === 0) {
      input.focus();
    }

    // Passer au champ suivant après saisie
    input.addEventListener("input", (e) => {
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
          if (allFilled) {
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

    // Récupérer le code complet
    const code = Array.from(inputs)
      .map((inp) => inp.value)
      .join("");

    // Vérifier que tous les champs sont remplis
    if (code.length !== 6) {
      erreurElement.textContent = "Veuillez entrer les 6 chiffres";
      erreurElement.style.display = "block";
      return;
    }

    // Envoyer le code pour vérification et activation
    try {
      const response = await fetch(window.location.href, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({ verifyAndActivate: true, code: code }),
      });

      const data = await response.json();

      if (data.success) {
        // Fermer les popups
        codePopup.remove();
        const mainPopup = document.querySelector(".overlayPopUpCompteClient");
        if (mainPopup) {
          mainPopup.remove();
        }
        // Afficher un message de succès
        alert("Authentification à deux facteurs activée avec succès !");
        // Recharger la page pour mettre à jour le bouton
        window.location.reload();
      } else {
        // Afficher l'erreur
        erreurElement.textContent = data.message || "Code incorrect";
        erreurElement.style.display = "block";

        // Réinitialiser les champs
        inputs.forEach((inp) => (inp.value = ""));
        inputs[0].focus();
      }
    } catch (error) {
      console.error("Erreur:", error);
      erreurElement.textContent = "Erreur de connexion";
      erreurElement.style.display = "block";
    }
  });
}

function fermerPopupActivationA2F(element) {
  const popup = element.closest(".bodyPopupA2f");
  if (popup) {
    popup.remove();
  }
}

function desactiverA2F() {
  if (
    confirm(
      "Êtes-vous sûr de vouloir désactiver l'authentification à deux facteurs ?",
    )
  ) {
    fetch(window.location.href, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({ activate: false }),
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          // Recharger la page pour mettre à jour l'affichage
          window.location.reload();
        } else {
          alert("Erreur lors de la désactivation");
        }
      })
      .catch((error) => {
        console.error("Erreur:", error);
        alert("Erreur de connexion");
      });
  }
}

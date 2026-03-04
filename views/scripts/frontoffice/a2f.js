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
                      <img src="/public/images/google.png" alt="Google Authenticator" title="Google Authenticator">
                      <img src="/public/images/microsoft.png" alt="Microsoft Authenticator" title="Microsoft Authenticator">
                      <img src="/public/images/apple.png" alt="Authy" title="Authy">
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
  const code = prompt("Entrez le code à 6 chiffres de votre application :");

  if (!code || !/^\d{6}$/.test(code.trim())) {
    alert("Veuillez entrer un code valide à 6 chiffres.");
    return;
  }

  fetch(window.location.href, {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify({ verifyAndActivate: true, code: code.trim() }),
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        // Fermer le popup
        const overlay = document.querySelector(".overlayPopUpCompteClient");
        if (overlay) {
          overlay.remove();
        }
        // Afficher un message de succès
        alert("Authentification à deux facteurs activée avec succès !");
        // Recharger la page pour mettre à jour le bouton
        window.location.reload();
      } else {
        alert(data.message || "Erreur lors de l'activation de l'A2F");
      }
    })
    .catch((error) => {
      console.error("Erreur:", error);
      alert("Erreur de connexion");
    });
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

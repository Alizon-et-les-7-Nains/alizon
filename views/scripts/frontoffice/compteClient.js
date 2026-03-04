function setError(element, message) {
  if (!element) return;
  element.classList.add("invalid");

  const container = element.parentElement;
  if (!container) return;

  let err = container.querySelector(".error-message");
  if (!err) {
    err = document.createElement("small");
    err.className = "error-message";
    container.appendChild(err);
  }
  err.textContent = message;
}

function clearError(element) {
  if (!element) return;
  element.classList.remove("invalid");

  const container = element.parentElement;
  if (!container) return;

  const err = container.querySelector(".error-message");
  if (err) err.textContent = "";
}

function validerMdp(mdp) {
  if (mdp.length < 12) return false;

  const maj = /[A-Z]/.test(mdp);
  const chiffre = /[0-9]/.test(mdp);
  const special = /[^a-zA-Z0-9]/.test(mdp);

  return maj && chiffre && special;
}

function fermerPopUp() {
  const overlay = document.querySelector(".overlayPopUpCompteClient");
  if (overlay) overlay.remove();
}

function popUpModifierMdp() {
  const overlay = document.createElement("div");
  overlay.className = "overlayPopUpCompteClient";
  overlay.innerHTML = `
    <main class="mainPopUpCompteClient">
      <div class="croixFermerLaPage">
        <div></div><div></div>
      </div>

      <h1>Modification de votre mot de passe</h1>

      <section>
        <div class="formulaireMdp">
          <form id="formMdp" method="POST" action="../../controllers/modifMdp.php">
            <div class="input">
              <input type="password" name="ancienMdp" placeholder="Ancien mot de passe" required>
            </div>

            <div class="input">
              <input type="password" name="nouveauMdp" placeholder="Nouveau mot de passe" required>
            </div>

            <div class="input">
              <input type="password" name="confirmationMdp" placeholder="Confirmer le nouveau mot de passe" required>
            </div>

            <article><p>✔ Longueur minimale de 12 caractères</p></article>
            <article><p>✔ Au moins une majuscule</p></article>
            <article><p>✔ Au moins un chiffre</p></article>
            <article><p>✔ Au moins un caractère spécial</p></article>

            <button type="submit">Valider</button>
          </form>
        </div>
      </section>
    </main>
  `;

  document.body.appendChild(overlay);

  overlay
    .querySelector(".croixFermerLaPage")
    .addEventListener("click", fermerPopUp);

  const form = overlay.querySelector("#formMdp");
  const ancienMdp = form.querySelector('input[name="ancienMdp"]');
  const nouveauMdp = form.querySelector('input[name="nouveauMdp"]');
  const confirmationMdp = form.querySelector('input[name="confirmationMdp"]');

  form.addEventListener("submit", function (event) {
    let ok = true;

    if (!ancienMdp.value.trim()) {
      setError(ancienMdp, "Champ obligatoire");
      ok = false;
    } else {
      clearError(ancienMdp);
    }

    if (!validerMdp(nouveauMdp.value)) {
      setError(
        nouveauMdp,
        "Mot de passe trop faible (voir les règles ci-dessous)",
      );
      ok = false;
    } else {
      clearError(nouveauMdp);
    }

    if (nouveauMdp.value !== confirmationMdp.value) {
      setError(confirmationMdp, "Les mots de passe ne correspondent pas");
      ok = false;
    } else {
      clearError(confirmationMdp);
    }

    if (!ok) {
      event.preventDefault();
    }
  });
}

function popUpSupprimerMdp(id_client) {
  const overlay = document.createElement("div");
  overlay.className = "overlayPopUpCompteClient";
  overlay.innerHTML = `
    <main class="mainPopUpCompteClient">
      <div class="croixFermerLaPage">
        <div></div><div></div>
      </div>

      <h1>Êtes-vous sur de vouloir supprimer votre compte ?</h1>

      <section>
        <div class="formulaireMdp">
          <form id="formMdp" method="POST" action="../../controllers/supprCompte.php">

            <div class="inputB">
              <input style="width:330px !important;" type="text" id="champValidation" name="confirmationSuppression" placeholder="Écrivez supprimer pour valider la suppression" required>
              <button type="submit" id="btnValidation" class="boutonSupprimerMdpI" disabled>Valider</button>
              <input type="hidden" name="id_client" value="${id_client}">
            </div>

          </form>

          <article><p>Ce n’est pas une suppression, mais un blocage des données accompagné d’une anonymisation.</p></article>

        </div>
      </section>

    </main>
  `;

  document.body.appendChild(overlay);

  overlay
    .querySelector(".croixFermerLaPage")
    .addEventListener("click", fermerPopUp);

  const champValidation = document.getElementById("champValidation");
  const btnValidation = document.getElementById("btnValidation");

  champValidation.addEventListener("input", function (e) {
    if (champValidation.value.toLowerCase() == "supprimer") {
      btnValidation.classList.remove("boutonSupprimerMdpI");
      btnValidation.classList.add("boutonSupprimerMdp");
      btnValidation.disabled = false;
    } else {
      btnValidation.classList.add("boutonSupprimerMdpI");
      btnValidation.classList.remove("boutonSupprimerMdp");
      btnValidation.disabled = true;
    }
  });
}

function verifierChamp() {
  const bouton = document.querySelector(".boutonModiferProfil");
  const champs = document.querySelectorAll("section input");
  let tousRemplis = true;

  if (champs.length === 0) {
    if (bouton) bouton.disabled = false;
    return;
  }

  for (let i = 0; i < champs.length; i++) {
    clearError(champs[i]);
  }

  for (let i = 0; i < champs.length; i++) {
    let valeur = champs[i].value.trim();

    if (
      (i === 0 || i === 1 || i === 2 || i === 3 || i === 9 || i === 10) &&
      valeur === ""
    ) {
      tousRemplis = false;
      setError(champs[i], "Ce champ est obligatoire");
      continue;
    }

    if (i === 3 && valeur !== "") {
      if (
        !/^([0][1-9]|[12][0-9]|[3][01])\/([0][1-9]|[1][012])\/([1][9][0-9][0-9]|[2][0][0-1][0-9]|[2][0][2][0-5])$/.test(
          valeur,
        )
      ) {
        tousRemplis = false;
        setError(champs[i], "Format attendu : jj/mm/aaaa");
      }
    }
    if (i === 6 && valeur !== "") {
      if (!/^[0-9]{5}$/.test(valeur)) {
        tousRemplis = false;
        setError(champs[i], "5 chiffres requis");
      }
    }

    if (i === 9 && valeur !== "") {
      if (
        !/^0[0-9](\s[0-9]{2}){4}$/.test(valeur) &&
        !/^0[0-9]([0-9]{2}){4}$/.test(valeur)
      ) {
        tousRemplis = false;
        setError(champs[i], "Format attendu : 06 01 02 03 04 ou 0601020304");
      }
    }

    if (i === 10 && valeur !== "") {
      if (!/^[a-zA-Z0-9._-]+@[a-zA-Z0-9._-]+\.[a-zA-Z]{2,}$/.test(valeur)) {
        tousRemplis = false;
        setError(champs[i], "Email invalide (ex: nom@domaine.fr)");
      }
    }
  }

  if (bouton) {
    bouton.disabled = !tousRemplis;
  }
}

let enModif = false;

// Création de l'input pour la photo de profil
let ajoutPhoto = document.createElement("input");
ajoutPhoto.type = "file";
ajoutPhoto.id = "photoProfil";
ajoutPhoto.name = "photoProfil";
ajoutPhoto.accept = "image/*";
ajoutPhoto.style.display = "none";
ajoutPhoto.autocomplete = "off";

let conteneur = document.getElementById("titreCompte");
let imageProfile = document.getElementById("imageProfile");
let bnModifier = document.getElementsByClassName("boutonModiferProfil");
let bnModifMdp = document.getElementsByClassName("boutonModifierMdp");
let bnAnnuler = document.getElementsByClassName("boutonAnnuler");

function modifierProfil(event) {
  event.preventDefault();

  if (!enModif) {
    // Remplacer les <p> par des <input> pour modification
    let elems = document.querySelectorAll("section p");
    const nomsChamps = [
      "pseudo",
      "prenom",
      "nom",
      "dateNaissance",
      "adresse1",
      "adresse2",
      "codePostal",
      "ville",
      "pays",
      "telephone",
      "email",
    ];

    for (let i = 0; i < elems.length; i++) {
      let texteActuel = elems[i].innerText;
      let input = document.createElement("input");
      input.value = texteActuel;
      input.name = nomsChamps[i];
      input.id = nomsChamps[i];
      input.autocomplete = nomsChamps[i];

      // Définir le type d'input approprié
      if (i === 9) input.type = "tel";
      else if (i === 10) input.type = "email";
      else input.type = "text";

      switch (i) {
        case 0:
          input.placeholder = "Pseudo*";
          break;
        case 1:
          input.placeholder = "Nom*";
          break;
        case 2:
          input.placeholder = "Prénom*";
          break;
        case 3:
          input.placeholder = "Date de naissance*";
          break;
        case 4:
          input.placeholder = "Adresse";
          break;
        case 5:
          input.placeholder = "Complément d'adresse";
          break;
        case 6:
          input.placeholder = "Code postal";
          break;
        case 7:
          input.placeholder = "Ville";
          break;
        case 8:
          input.placeholder = "Pays";
          break;
        case 9:
          input.placeholder = "Numéro de téléphone*";
          break;
        case 10:
          input.placeholder = "Email*";
          break;
      }

      elems[i].parentNode.replaceChild(input, elems[i]);
    }

    // Modifier le bouton "Modifier" en "Enregistrer"
    bnModifier[0].innerHTML = "Enregistrer";
    bnModifier[0].style.backgroundColor = "#64a377";
    bnModifier[0].style.color = "#FFFEFA";

    conteneur.appendChild(ajoutPhoto);

    imageProfile.style.cursor = "pointer";
    imageProfile.onclick = () => ajoutPhoto.click();

    enModif = true;

    bnAnnuler[0].style.display = "block";
    bnAnnuler[0].style.color = "white";

    const inputs = document.querySelectorAll("section input");

    for (let i = 0; i < inputs.length; i++) {
      inputs[i].addEventListener("input", verifierChamp);
    }

    verifierChamp();
  } else {
    let form = document.querySelector("form");
    form.submit();
  }
}

bnModifier[0].addEventListener("click", modifierProfil);

const valeursInitiales = Array.from(document.querySelectorAll("section p"));
let imageProfileOriginalSrc = imageProfile ? imageProfile.src : "";

ajoutPhoto.addEventListener("change", function () {
  const file = this.files[0];
  if (file) {
    const reader = new FileReader();
    reader.onload = function (e) {
      imageProfile.src = e.target.result;
    };
    reader.readAsDataURL(file);
  }
});

function boutonAnnuler() {
  const champs = document.querySelectorAll("section input");
  for (let i = 0; i < champs.length; i++) {
    clearError(champs[i]);
  }

  let inputs = document.querySelectorAll("section input");

  for (let i = 0; i < inputs.length; i++) {
    let p = document.createElement("p");
    p.innerText = valeursInitiales[i].innerText;

    let currentParent = inputs[i].parentNode;
    currentParent.replaceChild(p, inputs[i]);
  }

  // Restaurer la preview de l'image à l'original
  if (imageProfile && typeof imageProfileOriginalSrc !== "undefined") {
    imageProfile.src = imageProfileOriginalSrc;
  }

  // Si l'input file existe, réinitialiser sa valeur puis le supprimer
  const photoInput = document.getElementById("photoProfil");
  if (photoInput) {
    try {
      photoInput.value = "";
    } catch (e) {
      // certains navigateurs bloquent l'affectation de value pour security, on ignore
    }
    photoInput.remove();
  }

  enModif = false;

  bnModifier[0].innerHTML = "Modifier";
  bnModifier[0].style.backgroundColor = "#e4d9ff";
  bnModifier[0].style.color = "#273469";
  bnModifier[0].disabled = false;

  bnAnnuler[0].style.display = "none";

  imageProfile.style.cursor = "default";
  imageProfile.onclick = null;
}

// ===== GESTION DE L'AUTHENTIFICATION À DEUX FACTEURS (A2F) =====
let a2fCurrentSlide = 0;
let a2fTotalSlides = 4;

function handleA2FToggle() {
  const checkbox = document.getElementById("remember_me");
  if (checkbox.checked) {
    // Si déjà activé, désactiver
    desactiverA2F();
  } else {
    // Sinon, ouvrir le popup pour activer
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
                        <img src="../../public/images/google-authenticator.png" alt="Google Authenticator" title="Google Authenticator">
                        <img src="../../public/images/microsoft-authenticator.png" alt="Microsoft Authenticator" title="Microsoft Authenticator">
                        <img src="../../public/images/authy.png" alt="Authy" title="Authy">
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
    body: JSON.stringify({ activate: true }),
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
  fetch(window.location.href, {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify({ activate: true }),
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        // Cocher la checkbox
        document.getElementById("remember_me").checked = true;
        // Fermer le popup
        fermerPopUp();
        // Afficher un message de succès
        alert("Authentification à deux facteurs activée avec succès !");
      } else {
        alert("Erreur lors de l'activation de l'A2F");
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
          document.getElementById("remember_me").checked = false;
          alert("Authentification à deux facteurs désactivée");
        } else {
          alert("Erreur lors de la désactivation");
          // Re-cocher la checkbox si erreur
          document.getElementById("remember_me").checked = true;
        }
      })
      .catch((error) => {
        console.error("Erreur:", error);
        alert("Erreur de connexion");
        document.getElementById("remember_me").checked = true;
      });
  } else {
    // Si l'utilisateur annule, on remet la checkbox à son état initial
    document.getElementById("remember_me").checked = true;
  }
}

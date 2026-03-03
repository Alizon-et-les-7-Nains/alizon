const a2f = document.querySelector('.authenTwofacts input[type="checkbox"]');

// Créer l'élément popup mais ne pas l'ajouter tout de suite
const qrCodePopup = document.createElement("div");
qrCodePopup.classList.add("qr-code-popup");
qrCodePopup.innerHTML = `
    <div class="qr-code-content">
        <h2>Scannez ce QR code avec votre application d'authentification</h2>
        <div id="qrcode-container"></div>
        <button id="closePopup">Fermer</button>
    </div>
`;

a2f.addEventListener("change", function () {
  if (this.checked) {
    console.log("Activation de l'authentification à deux facteurs");

    // Désactiver la checkbox pendant le traitement
    this.disabled = true;

    // Envoyer une requête AJAX pour activer l'authentification à deux facteurs
    fetch("connexionClient.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({ activate: true }),
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {

          // Popup avec QR code pour configurer l'authentification à deux facteurs
          document.body.appendChild(qrCodePopup);
          
          // Générer le QR code avec l'URL otpauth
          const qrcodeContainer = document.getElementById('qrcode-container');
          qrcodeContainer.innerHTML = ''; // Vider le conteneur
          
          // URL OTPAuth pour le QR code (version de secours si le serveur ne renvoie pas l'URL)
          const otpauthUrl = data.otpauthUrl || "otpauth://totp/MonSite:TestUser?secret=JBSWY3DPEHPK3PXP&issuer=MonSite";
          
          // Utiliser la bibliothèque QRCode pour générer le QR code
          if (typeof QRCode !== 'undefined') {
            new QRCode(qrcodeContainer, {
              text: otpauthUrl,
              width: 200,
              height: 200
            });
          } else {
            // Fallback si QRCode n'est pas disponible
            console.error("Bibliothèque QRCode non chargée");
            qrcodeContainer.innerHTML = '<p>Impossible de générer le QR code</p>';
          }

          // Fermeture de la popup
          const closeButton = qrCodePopup.querySelector("#closePopup");
          closeButton.addEventListener("click", function () {
            qrCodePopup.remove();
          });
        } else {
          alert(
            "Une erreur est survenue lors de l'activation de l'authentification à deux facteurs.",
          );
          this.checked = false;
        }
      })
      .catch((error) => {
        console.error("Erreur:", error);
        alert(
          "Une erreur est survenue lors de l'activation de l'authentification à deux facteurs.",
        );
        this.checked = false;
      })
      .finally(() => {
        this.disabled = false;
      });
  } else {
    // Désactiver la checkbox pendant le traitement
    this.disabled = true;

    // Envoyer une requête AJAX pour désactiver l'authentification à deux facteurs
    fetch("connexionClient.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({ activate: false }),
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          alert("L'authentification à deux facteurs a été désactivée.");
        } else {
          alert(
            "Une erreur est survenue lors de la désactivation de l'authentification à deux facteurs.",
          );
          this.checked = true;
        }
      })
      .catch((error) => {
        console.error("Erreur:", error);
        alert(
          "Une erreur est survenue lors de la désactivation de l'authentification à deux facteurs.",
        );
        this.checked = true;
      })
      .finally(() => {
        this.disabled = false;
      });
  }
});
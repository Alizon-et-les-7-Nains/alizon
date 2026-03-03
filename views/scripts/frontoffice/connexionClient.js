
const a2f = document.querySelector('.authenTwofacts input[type="checkbox"]');

// Create popup element but don't append it yet
const qrCodePopup = document.createElement("div");
qrCodePopup.classList.add("qr-code-popup");
qrCodePopup.innerHTML = `
    <div class="qr-code-content">
        <h2>Scannez ce QR code avec votre application d'authentification</h2>
        <img src="" alt="QR Code">
        <button id="closePopup">Fermer</button>
    </div>
`;

a2f.addEventListener("change", function () {
  if (this.checked) {
    console.log("Activation de l'authentification à deux facteurs");

    // Disable checkbox while processing
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
          alert("L'authentification à deux facteurs a été activée.");

          // Pop up avec qr code pour configurer l'authentification à deux facteurs
          document.body.appendChild(qrCodePopup);

          // Génération du QR code avec l'URL reçue du serveur
          if (data.otpauthUrl) {
            QRCode.toDataURL(data.otpauthUrl, function (err, url) {
              if (err) {
                console.error("Erreur génération QR code:", err);
                throw err;
              }
              const qrCodeImage = qrCodePopup.querySelector("img");
              qrCodeImage.src = url;
            });
          } else {
            // Fallback pour le développement
            const otpauthUrl =
              "otpauth://totp/MonSite:TestUser?secret=JBSWY3DPEHPK3PXP&issuer=MonSite";
            QRCode.toDataURL(otpauthUrl, function (err, url) {
              if (err) throw err;
              const qrCodeImage = qrCodePopup.querySelector("img");
              qrCodeImage.src = url;
            });
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
    // Disable checkbox while processing
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

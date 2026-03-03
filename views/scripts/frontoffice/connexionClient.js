import "https://cdn.jsdelivr.net/npm/qrcode/build/qrcode.min.js";

const a2f = document.querySelector('.authenTwofacts input[type="checkbox"]');

const qrCodePopup = document.createElement("div");
qrCodePopup.classList.add("qr-code-popup");
qrCodePopup.innerHTML = `
            <div class="qr-code-content">
                <h2>Scannez ce QR code avec votre application d'authentification</h2>
                <img src="generate_qr_code.php" alt="QR Code">
                <button id="closePopup">Fermer</button>
            </div>
        `;

a2f.addEventListener("change", function () {
  if (this.checked) {
    console.log("Activation de l'authentification à deux facteurs");
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
        } else {
          alert(
            "Une erreur est survenue lors de l'activation de l'authentification à deux facteurs.",
          );
          this.checked = false; // Revenir à l'état précédent
        }
      })
      .catch((error) => {
        console.error("Erreur:", error);
        alert(
          "Une erreur est survenue lors de l'activation de l'authentification à deux facteurs.",
        );
        this.checked = false; // Revenir à l'état précédent
      });

    // Pop up avec qr code pour configurer l'authentification à deux facteurs
    document.body.appendChild(qrCodePopup);

    // generation du QR code
    const otpauthUrl =
      "otpauth://totp/MonSite:TestUser?secret=SECRET_KEY&issuer=MonSite";
    toDataURL(otpauthUrl, function (err, url) {
      if (err) throw err;
      const qrCodeImage = qrCodePopup.querySelector("img");
      qrCodeImage.src = url;

      document
        .querySelector("#closePopup")
        .addEventListener("click", function () {
          qrCodePopup.remove();
        });
    });
  } else {
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
          this.checked = true; // Revenir à l'état précédent
        }
      })
      .catch((error) => {
        console.error("Erreur:", error);
        alert(
          "Une erreur est survenue lors de la désactivation de l'authentification à deux facteurs.",
        );
        this.checked = true; // Revenir à l'état précédent
      });
  }
});

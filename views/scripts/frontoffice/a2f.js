function handleA2FToggle() {
  const a2f = document.getElementById("remember_me");

  if (!a2f) {
    console.error("Checkbox A2F non trouvée");
    return;
  }

  const isActivating = !a2f.checked;

  if (isActivating) {
    console.log("Activation de l'authentification à deux facteurs");
    a2f.disabled = true;

    fetch(window.location.href, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({ activate: true }),
    })
      .then((response) => {
        if (!response.ok) {
          throw new Error("Erreur réseau");
        }
        return response.json();
      })
      .then((data) => {
        if (data.success) {
          const qrCodePopup = document.createElement("div");
          qrCodePopup.classList.add("qr-code-popup");
          qrCodePopup.innerHTML = `
            <div class="qr-code-content">
              <h2>Scannez ce QR code avec votre application d'authentification</h2>
              <canvas id="qrcode-container"></canvas>
              <p>Ou saisissez manuellement cette clé secrète :</p>
              <div class="secret-text">${data.secret}</div>
              <button id="closePopup">Fermer</button>
            </div>
          `;

          document.body.appendChild(qrCodePopup);

          const qrcodeContainer =
            qrCodePopup.querySelector("#qrcode-container");

          setTimeout(() => {
            if (typeof QRCode !== "undefined") {
              QRCode.toCanvas(
                qrcodeContainer,
                data.otpauthUrl,
                { width: 250, height: 250 },
                function (error) {
                  if (error) {
                    console.error(error);
                    qrcodeContainer.innerHTML =
                      '<p style="color: red;">Erreur de chargement du QR code</p>';
                  }
                },
              );
            } else {
              console.error("Bibliothèque QRCode non chargée");
              qrcodeContainer.innerHTML =
                '<p style="color: red;">Erreur de chargement du QR code</p>';
            }
          }, 0);

          qrCodePopup
            .querySelector("#closePopup")
            .addEventListener("click", () => {
              qrCodePopup.remove();
              a2f.checked = true;
            });
        } else {
          alert("Une erreur est survenue lors de l'activation.");
        }
      })
      .catch((error) => {
        console.error("Erreur:", error);
        alert("Une erreur est survenue lors de l'activation.");
      })
      .finally(() => {
        a2f.disabled = false;
      });
  } else {
    console.log("Désactivation de l'authentification à deux facteurs");
    if (
      confirm(
        "Êtes-vous sûr de vouloir désactiver l'authentification à deux facteurs ?",
      )
    ) {
      a2f.disabled = true;
      fetch(window.location.href, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ activate: false }),
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.success) {
            alert("L'authentification à deux facteurs a été désactivée.");
            a2f.checked = false;
          } else {
            alert("Une erreur est survenue lors de la désactivation.");
          }
        })
        .catch((error) => {
          console.error("Erreur:", error);
          alert("Une erreur est survenue lors de la désactivation.");
        })
        .finally(() => {
          a2f.disabled = false;
        });
    }
  }
}

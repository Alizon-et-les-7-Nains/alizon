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

function popUpDetailsCommande() {

    const overlay = document.createElement("div");
    overlay.className = "overlayPopUpErreur";
    
    overlay.innerHTML = `
        <main class="popUpErreur">
              <div class="croixFermerLaPage">
                  <div></div>
                  <div></div>
              </div>
              <h1>Détails de la commande ID : X</h1>
        </main>`;

    document.body.appendChild(overlay);

    const croixFermer = overlay.querySelector(".croixFermerLaPage");
    const btnFermer = overlay.querySelector(".btnFermer");

    croixFermer.addEventListener("click", fermerPopUp);
    if (btnFermer) btnFermer.addEventListener("click", fermerPopUp);
    
    overlay.addEventListener("click", (e) => {
        if (e.target === overlay) {
            fermerPopUp();
        }
    });
}

function fermerPopUpRemise() {
    const overlay = document.querySelector(".overlayPopUpErreur");
    if (overlay) overlay.remove();
}
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

function popUpDetailsCommande(id, dateCommande = "N/A", adresseFact = "N/A", adresseLivr = "N/A", statut = "N/A", transporteur="N/A", HT = "N/A", TTC = "N/A") {

    const overlay = document.createElement("div");
    overlay.className = " ";
    
    overlay.innerHTML = `
        <main class="popUpDetails">
              <div class="croixFermerLaPage">
                  <div></div>
                  <div></div>
              </div>
              <h1>Détails de la commande # ${id}</h1>
              <div class="conteneurSections">
                  <div>
                    <h2>Date de la commande :</h2>
                    <p>${dateCommande}</p>
                    <h2>Adresse de facturation :</h2>
                    <p>${adresseFact}</p>
                    <h2>Adresse de livraison :</h2>
                    <p>${adresseLivr}</p>
                    <h2>Statut :</h2>
                    <p>${statut}</p>
                    <h2>Expédié par :</h2>
                    <p>${transporteur}</p>
                  </div>
                  <div>
                    <h2>Nom et prénom de la personne facturée :</h2>
                    <p>N/A</p>
                    <h2>Méthode de paiement :</h2>
                    <p>Visa</p>
                    <h2>Carte utilisée :</h2>
                    <p>Finissant par </p>
                    <h2>Montant HT :</h2>
                    <p>${HT} €</p>
                    <h2>Montant total TTC :</h2>
                    <p>${TTC} €</p>
                  </div>
              </div>
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
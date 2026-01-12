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

function popUpDetailsCommande(id, dateCommande = "N/A", adresseFact = "N/A", adresseLivr = "N/A", statut = "N/A", transporteur="N/A", HT = "N/A", TTC = "N/A", nom = "N/A") {

    const overlay = document.createElement("div");
    overlay.className = "overlayPopUpDetails";
    
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
                    <h2>Nom et prénom figurant sur la carte :</h2>
                    <p>${nom}</p>
                    <h2>Méthode de paiement :</h2>
                    <p>Visa</p>
                    <h2>Carte utilisée :</h2>
                    <p>Finissant par 4242</p>
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

    croixFermer.addEventListener("click", fermerPopUpDetailsCommande);
    if (btnFermer) btnFermer.addEventListener("click", fermerPopUpDetailsCommande);
    
    overlay.addEventListener("click", (e) => {
        if (e.target === overlay) {
            fermerPopUpDetailsCommande();
        }
    });
}

function fermerPopUpDetailsCommande() {
    const overlay = document.querySelector(".overlayPopUpDetails");
    if (overlay) overlay.remove();
}

// Suivi de commande 

function popUpSuiviCommande(id) {

    const overlay = document.createElement("div");
    overlay.className = "overlayPopUpSuiviCommande";
    
    overlay.innerHTML = `
        <main class="popUpSuivi">
              <div class="croixFermerLaPage">
                  <div></div>
                  <div></div>
              </div>
              <h1>Suivi de la commande # ${id}</h1>
        </main>`;

    document.body.appendChild(overlay);

    const croixFermer = overlay.querySelector(".croixFermerLaPage");
    const btnFermer = overlay.querySelector(".btnFermer");

    croixFermer.addEventListener("click", fermerPopUpSuiviCommande);
    if (btnFermer) btnFermer.addEventListener("click", fermerPopUpSuiviCommande);
    
    overlay.addEventListener("click", (e) => {
        if (e.target === overlay) {
            fermerPopUpDetailsCommande();
        }
    });
}

function fermerPopUpSuiviCommande() {
    const overlay = document.querySelector(".overlayPopUpSuiviCommande");
    if (overlay) overlay.remove();
}

// Confirmer annulation commande 

function popUpAnnulerCommande(id) {

    const overlay = document.createElement("div");
    overlay.className = "overlayPopUpAnnulerCommande";
    
    overlay.innerHTML = `
        <main class="popUpSuivi">
            <div class="croixFermerLaPage">
                <div></div>
                <div></div>
            </div>
            <h1>Annuler la commande # ${id} ?</h1>
            <h3>Cliquer sur "Confirmer" pour annuler votre commande</h3>
            <button type="submit" style="color: #ffffff; background-color: #f14e4e;">Confirmer</button>
         </main>`;

    document.body.appendChild(overlay);

    const croixFermer = overlay.querySelector(".croixFermerLaPage");
    const btnFermer = overlay.querySelector(".btnFermer");

    croixFermer.addEventListener("click", fermerPopUpAnnulerCommande);
    if (btnFermer) btnFermer.addEventListener("click", fermerPopUpAnnulerCommande);
    
    overlay.addEventListener("click", (e) => {
        if (e.target === overlay) {
            fermerPopUpDetailsCommande();
        }
    });
}

function fermerPopUpAnnulerCommande() {
    const overlay = document.querySelector(".overlayPopUpAnnulerCommande");
    if (overlay) overlay.remove();
}
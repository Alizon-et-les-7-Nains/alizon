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
    overlay.className = "overlayPopUpDetailsCommande";
    overlay.innerHTML = `
        <main>

            <div class="croixFermerLaPage">
                <div></div>
                <div></div>
            </div>

            <h1>Détails de ma commande</h1>
        
        </main>`;
    document.body.appendChild(overlay);

    const croixFermer = overlay.querySelector(".croixFermerLaPage");
    croixFermer.addEventListener("click", fermerPopUpDetailsCommande);
}

function fermerPopUpDetailsCommande() {
    const overlay = document.querySelector(".overlayPopUpDetailsCommande");
    if (overlay) overlay.remove();
}

function verifDate(input){
    let valeur = input.value.trim();
    //On récupère la date du jour
    let dateDuJour = new Date();
    dateDuJour = dateDuJour.toLocaleDateString();
    let tabVal = valeur.split("/");
    let tabDate = dateDuJour.split("/");

    let jourVal = parseInt(tabVal[0]);
    let moisVal = parseInt(tabVal[1]);
    let anVal  = parseInt(tabVal[2]);

    let jourAjd = parseInt(tabDate[0]);
    let moisAjd = parseInt(tabDate[1]);
    let anAjd   = parseInt(tabDate[2]);

    if (!/^([0][1-9]|[12][0-9]|[3][01])\/([0][1-9]|[1][012])\/([1][9][0-9][0-9]|[2][0][0-1][0-9]|[2][0][2][0-5])$/.test(valeur)) {
        setError(input, "Format attendu : jj/mm/aaaa");
    } else {
        clearError(input);
    }

    if(valeur.length == 10){
        let erreur = false;
            
        if (anVal < anAjd){
            erreur = true;
        }
        else if (anVal === anAjd && moisVal < moisAjd){
            erreur = true;
        }
        else if (anVal === anAjd && moisVal === moisAjd && jourVal <= jourAjd){
            erreur = true;
        }

        if (erreur) {
            setError(input, "La date limite doit dépasser la date du jour");
        } else {
            clearError(input);
        }
    }

}
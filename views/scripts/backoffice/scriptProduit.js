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

function fermerPopUpPromouvoir() {
    const overlay = document.querySelector(".overlaypopUpPromouvoir");
    if (overlay) overlay.remove();
}

function fermerPopUpInfoCalcul() {
    const overlay = document.querySelector(".overlayPopUpInfoCalcul");
    if (overlay) overlay.remove();
}

function fermerPopUpRemise() {
    const overlay = document.querySelector(".overlayPopUpRemise");
    if (overlay) overlay.remove();
}


function popUpInfoCalcul() {
    const overlay = document.createElement("div");
    overlay.className = "overlayPopUpInfoCalcul";
    overlay.innerHTML = `
        <main class="popUpInfoCalcul">

        <div class="croixFermerLaPage">
            <div></div>
            <div></div>
        </div>

        <h1>Comment sont calculés les prix ?</h1>

        <h2>Prix initial de la promotion :</h2>
        <p>10% du prix du produit/jour</p>

        <h2>Prix de la bannière :</h2>
        <p>5€/jour</p>`;
    document.body.appendChild(overlay);

    const croixFermer = overlay.querySelector(".croixFermerLaPage");
    croixFermer.addEventListener("click", fermerPopUpInfoCalcul);
}

function verifDate(val){
    let valeur = element.value.trim();
    if (!/^([0][1-9]|[12][0-9]|[3][01])\/([0][1-9]|[1][012])\/([1][9][0-9][0-9]|[2][0][0-1][0-9]|[2][0][2][0-5])$/.test(valeur)) {
        setError(val, "Format attendu : jj/mm/aaaa");
    } else {
        clearError(val);
    }
}

function popUpRemise(){
        const overlay = document.createElement("div");
        overlay.className = "overlayPopUpRemise";
        overlay.innerHTML = `
        <main class="popUpRemise">
            <div class="page">
                <div class="croixFermerLaPage">
                    <div></div>
                    <div></div>
                </div>
                <div class="titreEtProduit">
                    <h1> Ajouter une remise pour ce produit </h1>
                    <section>
                        <article>
                            <img class="produit" src="/public/images/rillettes.png" alt="">
                            <div class="nomEtEvaluation">
                                <p>Rillettes</p>
                                <div class="evaluation">
                                    <div class="etoiles">
                                        <img src="/public/images/etoile.svg" alt="">
                                        <p>3</p>
                                    </div>
                                    <p>200 évaluation</p>
                                </div>
                            </div>
                            <div>
                                <p class="prix"> 29.99 €</p>
                                <p class="prixAuKg"> 99.72€ / kg</p>
                            </div>
                        </article>
                    </section>
                </div>
                <div class="ligne"></div>
                <section class="section2">
                    <input type="text" name="dateLimite" id="dateLimite" placeholder="Date limite">
                    <div>
                        <input type="text" name="nouveauPrix" id="nouveauPrix" placeholder="Nouveau prix">
                        <input type="reduction" name="" id="reduction" placeholder="Reduction(%)">
                    </div>
                    <h2>Récapitulatif :</h2>
                    <p>Abaissement de <strong> 15€ </strong></p>
                    <button>Appliquer la remise </button>
                </section>
            </div>
        </main>`;
    document.body.appendChild(overlay);

    const croixFermer = overlay.querySelector(".croixFermerLaPage");
    croixFermer.addEventListener("click", fermerPopUpRemise);

    const dateLimite = overlay.querySelector("#dateLimite");
    dateLimite.addEventListener("input", () => verifDate(dateLimite));
}

function popUpPromouvoir(id, nom, imgURL, prix, nbEval, note) {
    const prixProduit = parseFloat(prix); // Sécurise le prix en nombre
    const overlay = document.createElement("div");
    overlay.className = "overlaypopUpPromouvoir";

    // On utilise <input type="date"> pour simplifier la vie
    overlay.innerHTML = `
        <main class="popUpPromouvoir">
            <div class="page">
                <div class="croixFermerLaPage"><div></div><div></div></div>
                <div class="titreEtProduit">
                    <h1>Ajouter une promotion</h1>
                    <section>
                        <article style="padding: 20px;">
                            <img class="produit" src="${imgURL}" alt="Produit">
                            <div class="nomEtEvaluation">
                                <p>${nom}</p>
                                <div class="evaluation">
                                    <p>★ ${note} (${nbEval} avis)</p>
                                </div>
                            </div>
                            <p class="prix">${prixProduit} €</p>
                        </article>
                    </section>
                </div>
                
                <form method="POST" enctype="multipart/form-data" action="../../controllers/creerPromotion.php">
                    <section class="section2">
                        <label>Date de fin :</label>
                        <input type="date" id="dateLimite" name="date_limite" min="${new Date().toISOString().split('T')[0]}" required>
                        
                        <h2>Ajouter une bannière (+5€/j) :</h2>
                        <div class="ajouterBaniere" id="zoneBaniere" style="cursor:pointer">
                            <input type="file" id="baniere" name="baniere" accept="image/*" style="display:none">
                            <img id="previewImg" src="../../public/images/iconeAjouterBaniere.svg" style="max-height: 50px;">
                        </div>

                        <div class="sousTotal">
                            <p>Durée : <span id="nbJours">0</span> jours</p>
                            <p><strong>Total à payer : <span id="prixTotal">0</span> €</strong></p>
                        </div>

                        <div class="infoCalcul">
                            <img src="../../public/images/iconeInfo.svg">
                            <p>Comment sont calculés les prix ?</p>
                        </div>

                        <div class="deuxBoutons">
                            <input type="hidden" name="id" value="${id}">
                            <button type="submit" id="btnValider" disabled style="opacity: 0.5;">Promouvoir</button>
                        </div>
                    </section>
                </form>
            </div>
        </main>`;

    document.body.appendChild(overlay);

    const croixFermer = overlay.querySelector(".croixFermerLaPage");
    croixFermer.addEventListener("click", fermerPopUpRemise);

    const dateInput = overlay.querySelector("#dateLimite");
    const fileInput = overlay.querySelector("#baniere");
    const previewImg = overlay.querySelector("#previewImg");
    const btnValider = overlay.querySelector("#btnValider");
    const spanJours = overlay.querySelector("#nbJours");
    const spanTotal = overlay.querySelector("#prixTotal");
    const zoneBaniere = overlay.querySelector("#zoneBaniere");

    zoneBaniere.onclick = () => fileInput.click();
    
    fileInput.onchange = (e) => {
        if (e.target.files && e.target.files[0]) {
            const reader = new FileReader();
            reader.onload = (ev) => previewImg.src = ev.target.result;
            reader.readAsDataURL(e.target.files[0]);
            calculerPrix();
        }
    };

    function calculerPrix() {
        if (!dateInput.value) return;

        const dateFin = new Date(dateInput.value);
        const today = new Date();
        today.setHours(0,0,0,0);

        const diffTime = dateFin - today;
        const jours = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

        if (jours > 0) {
            const coutPromo = (prixProduit * 0.10) * jours;
            const coutBaniere = (fileInput.files.length > 0) ? (5 * jours) : 0;
            
            spanJours.innerText = jours;
            spanTotal.innerText = (coutPromo + coutBaniere).toFixed(2);
            
            btnValider.disabled = false;
            btnValider.style.opacity = "1";
        } else {
            spanJours.innerText = "0";
            spanTotal.innerText = "0";
            btnValider.disabled = true;
            btnValider.style.opacity = "0.5";
        }
    }

    dateInput.addEventListener("input", calculerPrix);

    overlay.querySelector('.infoCalcul').onclick = popUpInfoCalcul; 
}
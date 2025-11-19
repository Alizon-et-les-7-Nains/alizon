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



function popUpPromouvoir() {
    const overlay = document.createElement("div");
    overlay.className = "overlaypopUpPromouvoir";
    overlay.innerHTML = `
        <main class="popUpPromouvoir">
            <div class="page">
                <div class="croixFermerLaPage">
                    <div></div>
                    <div></div>
                </div>
                <div class="titreEtProduit">
                    <h1> Ajouter une promotion pour ce produit </h1>
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
                    <input type="text" placeholder="Date limite">
                    <h2><strong> Ajouter une bannière : </strong> (optionnel)</h2>
                    <div class="ajouterBaniere">
                        <input type="file" id="baniere" name="baniere" accept="image/*">
                        <img src="../../public/images/iconeAjouterBaniere.svg" alt="">
                    </div>
                    <p class="supprimer">supprimer ...</p>
                    <p><strong>Format accepté </strong>: 21:4 (1440x275 pixels minimum)</p>
                    <h2><strong>Sous total : </strong></h2>
                    <div class="sousTotal">
                        <p>Promotion : 3€</p>
                        <p>Baniere : 0€</p>
                        <p>Durée : 0 jours</p>
                        <p><strong>Total : 3€</strong></p>
                    </div>
                    <div class="infoCalcul">
                        <img src="../../public/images/iconeInfo.svg" alt="">
                        <p class="supprimer"> Comment sont calculés les prix ? </p>
                    </div>
                    <div class="deuxBoutons">
                        <button>Ajouter une remise</button>
                        <button>Promouvoir</button>
                    </div>
                </section>
            </div>
        </main>`;
    document.body.appendChild(overlay);

    const croixFermer = overlay.querySelector(".croixFermerLaPage");
    croixFermer.addEventListener("click", fermerPopUpPromouvoir);

    function cliqueBaniere(){
        document.getElementById('baniere').click();
    }

    document.querySelector('.ajouterBaniere').addEventListener('click', cliqueBaniere);


    const infoCalcBtn = overlay.querySelector('.infoCalcul');
    infoCalcBtn.addEventListener('click', popUpInfoCalcul);
}
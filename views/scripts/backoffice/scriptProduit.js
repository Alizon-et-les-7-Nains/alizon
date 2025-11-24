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

function verifDate(input){
    let valeur = input.value.trim();
    if (!/^([0][1-9]|[12][0-9]|[3][01])\/([0][1-9]|[1][012])\/([1][9][0-9][0-9]|[2][0][0-1][0-9]|[2][0][2][0-5])$/.test(valeur)) {
        setError(input, "Format attendu : jj/mm/aaaa");
    } else {
        clearError(input);
    }
}

function popUpRemise(id, nom, imgURL, prix, nbEval, note){
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
                            <img class="produit" src="${imgURL}" alt="">
                            <div class="nomEtEvaluation">
                                <p>${nom}</p>
                                <div class="evaluation">
                                    <div class="etoiles">
                                        <img src="/public/images/etoile.svg" alt="">
                                        <p>${note}</p>
                                    </div>
                                    <p>${nbEval} évaluation</p>
                                </div>
                            </div>
                            <div>
                                <p class="prix"> ${prix} €</p>
                                <p class="prixAuKg"> 99.72€ / kg</p>
                            </div>
                        </article>
                    </section>
                </div>
                <div class="ligne"></div>
                <form method="POST" action="../../controllers/creerRemise.php">
                    <div>
                        <input type="text" name="dateLimite" id="dateLimite" placeholder="Date limite">
                    </div>
                    <div>
                        <input type="float" name="nouveauPrix" id="nouveauPrix" placeholder="Nouveau prix">
                        <input type="float" name="reduction" id="reduction" placeholder="Reduction(%)">
                    </div>
                    <h2>Récapitulatif :</h2>
                    <p class = "recap"> </p>
                    <input type="hidden" name="id" value="${id}">
                    <button type="submit">Appliquer la remise </button>
                </form>
            </div>
        </main>`;
    document.body.appendChild(overlay);

    const croixFermer = overlay.querySelector(".croixFermerLaPage");
    croixFermer.addEventListener("click", fermerPopUpRemise);

    const dateLimite = overlay.querySelector("#dateLimite");
    dateLimite.addEventListener("input", () => verifDate(dateLimite));

    function updatePrixFromReduction(prix, nouveauPrixInput, reductionInput, recap) {
        if (reductionInput.value !== "") {
            const nouveauPrix = (prix * (100 - reductionInput.value) / 100).toFixed(2);
            nouveauPrixInput.value = nouveauPrix;
            recap.textContent = "Abaissement de " + (prix - nouveauPrix).toFixed(2) + "€";
        } else {
            nouveauPrixInput.value = "";
            recap.textContent = "Abaissement de 0€";
        }
    }

    function updateReductionFromPrix(prix, nouveauPrixInput, reductionInput, recap) {
        if (nouveauPrixInput.value !== "") {
            const reduction = (100 - (nouveauPrixInput.value) * 100 / prix).toFixed(2);
            reductionInput.value = reduction;
            recap.textContent = "Abaissement de " + (prix - nouveauPrixInput.value).toFixed(2) + "€";
        } else {
            reductionInput.value = "";
            recap.textContent = "Abaissement de 0€";
        }
    }
    
    const nouveauPrix = overlay.querySelector("#nouveauPrix");
    const reduction = overlay.querySelector("#reduction");
    const recap = overlay.querySelector(".recap");

    nouveauPrix.addEventListener("input", () => updateReductionFromPrix(prix, nouveauPrix, reduction, recap));
    reduction.addEventListener("input", () => updatePrixFromReduction(prix, nouveauPrix, reduction, recap));


}

function popUpPromouvoir(id, nom, imgURL, prix, nbEval, note) {

    console.log("ID reçu :", id);
    console.log("Nom reçu :", nom);

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
                        <article style="padding-right: 20px; padding-top: 20px; padding-left: 20px; padding-bottom: 20px;">
                            <img class="produit" src="${imgURL}" alt="Image du produit">
                            <div class="nomEtEvaluation">
                                <p>${nom}</p>
                                <div class="evaluation">
                                    <div class="etoiles">
                                        <img src="/public/images/etoile.svg" alt="Image notation étoile">
                                        <p>${note}</p>
                                    </div>
                                    <p>${nbEval} évaluation</p>
                                </div>
                            </div>
                            <div>
                                <p class="prix"> ${prix} €</p>
                            </div>
                        </article>
                    </section>
                </div>
                
            <div class="ligne"></div>
                <form method="POST" enctype="multipart/form-data" action="../../controllers/creerPromotion.php">
                    <section class="section2">
                        <div>
                            <input type="text" id="dateLimite" name="date_limite" class="dateLimite" placeholder="Date limite : Jour/Mois/Année">
                        </div>
                        <h2><strong> Ajouter une bannière : </strong> (optionnel)</h2>
                        <div class="ajouterBaniere">
                            <input type="file" id="baniere" name="baniere" accept="image/*">  
                        </div>
                        <p class="supprimer">supprimer ...</p>
                        <p><strong>Format accepté </strong>: 21:4 (1440x275 pixels minimum)</p>
                        <h2><strong>Sous total : </strong></h2>
                        <div class="sousTotal">
                            <div class="prixRes">
                                <p>Promotion : </p>
                                <p><strong class="dataPromo">0</strong></p>
                                <p><strong>€</strong></p>
                            </div>
                            <div class="prixRes">
                                <p>Baniere : </p>
                                <p><strong class="dataBaniere">0</strong></p>
                                <p><strong>€</strong></p>
                            </div>
                            <div class="prixRes">
                                <p>Durée : </p>
                                <p><strong class="dataDuree">0</strong></p>
                                <p><strong>&nbsp jours</strong></p>
                            </div>
                            <div class="prixRes">
                                <p>Total : </p>
                                <p><strong class="dataTotal">0</strong></p>
                                <p><strong>€</strong></p>
                            </div>
                        </div>
                        <div class="infoCalcul">
                            <img src="../../public/images/iconeInfo.svg" alt="">
                            <p class="supprimer"> Comment sont calculés les prix ? </p>
                        </div>
                        <div class="deuxBoutons">
                            <input type="hidden" name="id" value="${id}">
                            <button type="submit">Promouvoir</button>
                        </div>
                    </section>
                </form>
            </div>
        </main>`;
    document.body.appendChild(overlay);

    const croixFermer = overlay.querySelector(".croixFermerLaPage");
    croixFermer.addEventListener("click", fermerPopUpPromouvoir);

    function cliqueBaniere(){
        document.getElementById('baniere').click();
    }

    document.querySelector('.ajouterBaniere').addEventListener('click', cliqueBaniere);

    const dateLimite = overlay.querySelector("#dateLimite");
    let dateLimiteVal = dateLimite.value;
    dateLimite.addEventListener("change", () => { 
        clearError(dateLimite); 
        verifDate(dateLimite); 
        console.log("Date limite :", dateLimiteVal); 
    });

    const infoCalcBtn = overlay.querySelector('.infoCalcul');
    infoCalcBtn.addEventListener('click', popUpInfoCalcul);

    // Section calcul de prix 
    const txtPromo = document.querySelector('.dataPromo');
    const txtBaniere = document.querySelector('.dataBaniere');
    const txtDuree = document.querySelector('.dataDuree');
    const txtTotal = document.querySelector('.dataTotal');

    function parseFrDate(date) {
        const [d, m, y] = date.split("/").map(Number);
        return new Date(y, m - 1, d);
    }

    function diffDays(d1, d2) {
        return Math.round((d2 - d1) / (1000 * 60 * 60 * 24));
    }

    dateLimite.addEventListener('change', () => {
        const dateLimiteValue = dateLimite.value;
        const currentDate = new Date();

        const d2 = parseFrDate(dateLimiteValue);
        const nbJourDiff = diffDays(currentDate, d2);

        const nbJours = Math.max(0, nbJourDiff);
        const coutParJour = prix * 0.1;
        const totalPromo = (coutParJour * nbJours).toFixed(2);

        if(totalPromo == NaN) {
            totalPromo = 0;
        }

        txtPromo.textContent = totalPromo;
        txtDuree.textContent = nbJours;
        txtTotal.textContent = totalPromo; // Ajouter plus tard prix de la bannière
    });
}
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
    //pop up sur comment est calculer le prix 
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

    //date au format dd/mm/aaaa
    if (!/^([0][1-9]|[12][0-9]|[3][01])\/([0][1-9]|[1][012])\/([1][9][0-9][0-9]|[2][0][0-1][0-9]|[2][0][2][0-5])$/.test(valeur)) {
        setError(input, "Format attendu : jj/mm/aaaa");
    } else {
        clearError(input);
    }

    //il faut que la date d'expiration soit ulterieur que la date courente
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


function popUpAnnulerRemise(id, nom) {

    const url = new URL(window.location);
    url.searchParams.set('annulationProduit', id);
    window.history.pushState({}, '', url);

    const overlay = document.createElement("div");
    overlay.className = "overlayPopUpErreur";
    
    overlay.innerHTML = `
        <main class="popUpErreur" style="text-align : center;">
            <form method="POST" action="../../controllers/annulerRemise.php">
                <div class="croixFermerLaPage">
                    <div></div>
                    <div></div>
                </div>
                <h1>Souhaitez-vous vraiment annuler la remise pour ce produit ?</h1>
                <p><strong>${nom}</strong></p>
                <input type="hidden" name="annulationProduit" value="${id}">
                <button type="submit" style="color: #ffffff; background-color: #f14e4e;">Annuler la remise</button>
            </form>
        </main>`;

    document.body.appendChild(overlay);

    const fermerPopUp = () => {
        overlay.remove();
        const url = new URL(window.location);
        url.searchParams.delete('annulationProduit');
        window.history.replaceState({}, '', url);
    };

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


function popUpModifierRemise(id, nom, imgURL, prix, nbEval, note, prixAuKg, aUneRemise){
        //popup de Modification de la Remise 
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
                    <h1> Modifier une remise pour ce produit </h1>
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
                                <p class="prixAuKg"> ${prixAuKg}€ / kg</p>
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
                    <input type="hidden" name="aUneRemise" value="${aUneRemise}">
                    <div class="deuxBoutons">
                        <button class="boutonSup" type="button" onclick="popUpAnnulerRemise(${id}, '${nom}')">Annuler la remise</button>
                        <button class="bouton" type="submit">Appliquer la remise</button>
                    </div>
                    </form>
            </div>
        </main>`;
    document.body.appendChild(overlay);

    const bouton = overlay.querySelector(".bouton");
    bouton.disabled = true;
    bouton.style.cursor = "default";

    const croixFermer = overlay.querySelector(".croixFermerLaPage");
    croixFermer.addEventListener("click", fermerPopUpRemise);

    const dateLimite = overlay.querySelector("#dateLimite");
    dateLimite.addEventListener("input", () => verifDate(dateLimite));

    function updatePrixFromReduction(prixOriginal, inputNouveauPrix, inputReduction, recap) {
        const valeurReduction = parseFloat(inputReduction.value);
        //On verifie que le pourcentage n'est pas vide ou que la valeur soit inférieur à 100 ou qu'elle soit supérieur à 0
        if (inputReduction.value === "" || valeurReduction <= 0 || valeurReduction > 100) {
            setError(inputReduction, "Réduction entre 1% et 100%");
            inputNouveauPrix.value = "";
            recap.textContent = "Abaissement de 0€";
            return;
        } else {
            clearError(inputReduction);
        }

        //calcul du nouveau prix et du recap
        const calculNouveauPrix = (prixOriginal * (100 - valeurReduction) / 100).toFixed(2);
        inputNouveauPrix.value = calculNouveauPrix;
        recap.textContent = "Abaissement de " + (prixOriginal - calculNouveauPrix).toFixed(2) + "€";

        const prixCalc = parseFloat(inputNouveauPrix.value);
        if (prixCalc < 0 || prixCalc > prixOriginal) {
            setError(inputNouveauPrix, `Prix entre 0 et ${prixOriginal}€`);
        } else {
            clearError(inputNouveauPrix);
        }
    }

    function updateReductionFromPrix(prixOriginal, inputNouveauPrix, inputReduction, recap) {
        const valeurNouveauPrix = parseFloat(inputNouveauPrix.value);
        
        //On verifie que le prix n'est pas vide ou que la valeur soit inférieur au prix de base ou qu'elle soit supérieur à 0
        if (inputNouveauPrix.value === "" || valeurNouveauPrix < 0 || valeurNouveauPrix > prixOriginal) {
            setError(inputNouveauPrix, `Prix entre 0 et ${prixOriginal}€`);
            inputReduction.value = "";
            recap.textContent = "Abaissement de 0€";
            return;
        } else {
            clearError(inputNouveauPrix);
        }

        //calcul du noiveau prix et du pourcentage
        const calculReduction = (100 - (valeurNouveauPrix * 100 / prixOriginal)).toFixed(2);
        inputReduction.value = calculReduction;
        recap.textContent = "Abaissement de " + (prixOriginal - valeurNouveauPrix).toFixed(2) + "€";

        if (calculReduction <= 0 || calculReduction > 100) {
            setError(inputReduction, "Réduction entre 1% et 100%");
        } else {
            clearError(inputReduction);
        }
    }
    
    const nouveauPrix = overlay.querySelector("#nouveauPrix");
    const reduction = overlay.querySelector("#reduction");
    const recap = overlay.querySelector(".recap");

    nouveauPrix.addEventListener("input", () => updateReductionFromPrix(prix, nouveauPrix, reduction, recap));
    reduction.addEventListener("input", () => updatePrixFromReduction(prix, nouveauPrix, reduction, recap));

    //modification de l'état du bouton
    function champsVide(){
        const bouton = overlay.querySelector(".bouton");

        if(dateLimite.value == "" || nouveauPrix.value == "" || reduction.value == ""){
            bouton.disabled = true;
            bouton.style.cursor = "default";
        } else {
            bouton.disabled = false;
            bouton.style.cursor = "pointer";
        }
    }

    dateLimite.addEventListener("input", champsVide);
    nouveauPrix.addEventListener("input", champsVide);
    reduction.addEventListener("input", champsVide);
}


function popUpRemise(id, nom, imgURL, prix, nbEval, note, prixAuKg, aUneRemise){
        //idem que la fonction précédente sans le bouton de supression
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
                                <p class="prixAuKg"> ${prixAuKg}€ / kg</p>
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
                    <input type="hidden" name="aUneRemise" value="${aUneRemise}">
                    <div class="deuxBoutons">
                        <button class="bouton" type="submit">Appliquer la remise</button>
                    </div>
                    </form>
            </div>
        </main>`;
    document.body.appendChild(overlay);

    const bouton = overlay.querySelector(".bouton");
    bouton.disabled = true;
    bouton.style.cursor = "default";

    const croixFermer = overlay.querySelector(".croixFermerLaPage");
    croixFermer.addEventListener("click", fermerPopUpRemise);

    const dateLimite = overlay.querySelector("#dateLimite");
    dateLimite.addEventListener("input", () => verifDate(dateLimite));

    function updatePrixFromReduction(prixOriginal, inputNouveauPrix, inputReduction, recap) {
        const valeurReduction = parseFloat(inputReduction.value);

        if (inputReduction.value === "" || valeurReduction <= 0 || valeurReduction > 100) {
            setError(inputReduction, "Réduction entre 1% et 100%");
            inputNouveauPrix.value = "";
            recap.textContent = "Abaissement de 0€";
            return;
        } else {
            clearError(inputReduction);
        }

        const calculNouveauPrix = (prixOriginal * (100 - valeurReduction) / 100).toFixed(2);
        inputNouveauPrix.value = calculNouveauPrix;
        recap.textContent = "Abaissement de " + (prixOriginal - calculNouveauPrix).toFixed(2) + "€";

        const prixCalc = parseFloat(inputNouveauPrix.value);
        if (prixCalc < 0 || prixCalc > prixOriginal) {
            setError(inputNouveauPrix, `Prix entre 0 et ${prixOriginal}€`);
        } else {
            clearError(inputNouveauPrix);
        }
    }

    function updateReductionFromPrix(prixOriginal, inputNouveauPrix, inputReduction, recap) {
        const valeurNouveauPrix = parseFloat(inputNouveauPrix.value);

        if (inputNouveauPrix.value === "" || valeurNouveauPrix < 0 || valeurNouveauPrix > prixOriginal) {
            setError(inputNouveauPrix, `Prix entre 0 et ${prixOriginal}€`);
            inputReduction.value = "";
            recap.textContent = "Abaissement de 0€";
            return;
        } else {
            clearError(inputNouveauPrix);
        }

        const calculReduction = (100 - (valeurNouveauPrix * 100 / prixOriginal)).toFixed(2);
        inputReduction.value = calculReduction;
        recap.textContent = "Abaissement de " + (prixOriginal - valeurNouveauPrix).toFixed(2) + "€";

        if (calculReduction <= 0 || calculReduction > 100) {
            setError(inputReduction, "Réduction entre 1% et 100%");
        } else {
            clearError(inputReduction);
        }
    }
    
    const nouveauPrix = overlay.querySelector("#nouveauPrix");
    const reduction = overlay.querySelector("#reduction");
    const recap = overlay.querySelector(".recap");

    nouveauPrix.addEventListener("input", () => updateReductionFromPrix(prix, nouveauPrix, reduction, recap));
    reduction.addEventListener("input", () => updatePrixFromReduction(prix, nouveauPrix, reduction, recap));

    function champsVide(){
        const bouton = overlay.querySelector(".bouton");

        if(dateLimite.value == "" || nouveauPrix.value == "" || reduction.value == ""){
            bouton.disabled = true;
            bouton.style.cursor = "default";
        } else {
            bouton.disabled = false;
            bouton.style.cursor = "pointer";
        }
    }

    dateLimite.addEventListener("input", champsVide);
    nouveauPrix.addEventListener("input", champsVide);
    reduction.addEventListener("input", champsVide);


}

function popUpErreur(id, code) {
    console.log("Erreur ID produit:", id, "Code erreur:", code);

    const messages = {
        1: "Une erreur est survenue lors du traitement de la date. Respectez le format jj/mm/aaaa et réessayez.",
        2: "Le format de l'image n'est pas valide. Revérifiez les critères et réessayez.",
        3: "Erreur inattendue lors de l'annulation de la promotion. Veuillez réessayer.",
        4: "Erreur lors de la suppression de la bannière. Veuillez réessayer.",
        404: "Le produit demandé est introuvable.",
        'default': "Une erreur inattendue s'est produite. Veuillez réessayer."
    };

    const messageErreur = messages[code] || messages['default'];

    const overlay = document.createElement("div");
    overlay.className = "overlayPopUpErreur";
    
    overlay.innerHTML = `
        <main class="popUpErreur">
            <div class="croixFermerLaPage">
                <div></div>
                <div></div>
            </div>
            <h1>Oups !</h1>
            <p><strong>${messageErreur}</strong></p>
            <button class="btnFermer">Compris</button>
        </main>`;

    document.body.appendChild(overlay);

    const fermerPopUp = () => {
        overlay.remove();
        window.history.replaceState({}, document.title, window.location.pathname);
    };

    const croixFermer = overlay.querySelector(".croixFermerLaPage");
    const btnFermer = overlay.querySelector(".btnFermer");

    croixFermer.addEventListener("click", fermerPopUp);
    btnFermer.addEventListener("click", fermerPopUp);
    
    overlay.addEventListener("click", (e) => {
        if (e.target === overlay) {
            fermerPopUp();
        }
    });
}

function popUpAnnulerPromotion(id, nom) {

    const url = new URL(window.location);
    url.searchParams.set('annulationProduit', id);
    window.history.pushState({}, '', url);

    const overlay = document.createElement("div");
    overlay.className = "overlayPopUpErreur";
    
    overlay.innerHTML = `
        <main class="popUpErreur" style="text-align : center;">
            <form method="POST" action="../../controllers/annulerPromotion.php">
                <div class="croixFermerLaPage">
                    <div></div>
                    <div></div>
                </div>
                <h1>Souhaitez-vous vraiment annuler la promotion pour ce produit ?</h1>
                <p><strong>${nom}</strong></p>
                <input type="hidden" name="annulationProduit" value="${id}">
                <button type="submit" style="color: #ffffff; background-color: #f14e4e;">Annuler la promotion</button>
            </form>
        </main>`;

    document.body.appendChild(overlay);

    const fermerPopUp = () => {
        overlay.remove();
        const url = new URL(window.location);
        url.searchParams.delete('annulationProduit');
        window.history.replaceState({}, '', url);
    };

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

function popUpModifierPromotion(id, nom, imgURL, prix, nbEval, note, prixAuKg, dateFinPromo, defImg) {

    const overlay = document.createElement("div");
    
    overlay.className = "overlaypopUpPromouvoir";
    overlay.innerHTML = `
        <?php $d = DateTime::createFromFormat('d/m/Y', $dateFinPromo); ?>
        <main class="popUpPromouvoir">
            <div class="page">
                <div class="croixFermerLaPage">
                    <div></div>
                    <div></div>
                </div>
                <div class="titreEtProduit">
                    <h1> Modifier une promotion pour ce produit </h1>
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
                                <p class="prixAuKg"> ${prixAuKg}€ / kg</p>
                            </div>
                        </article>
                    </section>
                </div>
                
            <div class="ligne"></div>
                <form method="POST" enctype="multipart/form-data" action="../../controllers/creerPromotion.php">
                    <section class="section2">
                        <h2><strong> Date limite de la promotion : </strong> (optionnel)</h2>
                        <div>
                            <input value="${dateFinPromo}" type="text" id="dateLimite" name="date_limite" class="dateLimite" placeholder="Jour/Mois/Année">
                        </div>
                        <h2><strong> Bannière actuelle : </strong></h2>
                        <div style="background-image: url(${defImg})"  class="ajouterBaniere">
                            <input type="file" id="baniere" name="baniere" accept="image/*">  
                        </div>
                        
                        <div style="margin-top: 10px; display: flex; align-items: center; gap: 10px;">
                            <input type="checkbox" id="supprimer_banniere" name="supprimer_banniere" value="1">
                            <label for="supprimer_banniere" style="color: #F14E4E; cursor: pointer; font-weight: bold;">
                                Supprimer la bannière actuelle
                            </label>
                        </div>

                        <h2><strong>Sous total : </strong></h2>
                        <div class="sousTotal">
                            <div class="prixRes">
                                <p>Promotion : </p>
                                <p><strong class="dataPromo">0</strong></p>
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
                        <div style="margin-top: 10px;" class="infoCalcul">
                            <img src="../../public/images/iconeInfo.svg" alt="">
                            <p class="supprimer"> Comment sont calculés les prix ? </p>
                        </div>
                        <div class="deuxBoutons">
                            <input type="hidden" name="id" value="${id}">
                            <button type="button" onclick="popUpAnnulerPromotion(${id}, '${nom}')" style="color: white; background-color: #F14E4E;">Retirer la promotion</button>
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
        let totalPromo = (coutParJour * nbJours).toFixed(2);

        if(totalPromo == NaN) {
            totalPromo = 0;
        }

        txtPromo.textContent = totalPromo;
        txtDuree.textContent = nbJours;
        txtTotal.textContent = totalPromo; // Ajouter plus tard prix de la bannière
    });
}

function popUpPromouvoir(id, nom, imgURL, prix, nbEval, note, prixAuKg, dateFinPromo = new Date().toLocaleDateString('fr-FR', { timeZone: 'UTC' })) {

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
                                <p class="prixAuKg"> ${prixAuKg}€ / kg</p>
                            </div>
                        </article>
                    </section>
                </div>
                
            <div class="ligne"></div>
                <form method="POST" enctype="multipart/form-data" action="../../controllers/creerPromotion.php">
                    <section class="section2">
                        <h2><strong> Date limite de la promotion : </strong> (optionnel)</h2>
                        <div>
                            <input value="${dateFinPromo}" type="text" id="dateLimite" name="date_limite" class="dateLimite" placeholder="Jour/Mois/Année">
                        </div>
                        <h2><strong> Ajouter une bannière : </strong> (optionnel)</h2>
                        <div class="ajouterBaniere">
                            <input type="file" id="baniere" name="baniere" accept="image/*">  
                        </div>
                        <p class="supprimer">supprimer ...</p>
                        <p><strong>Format accepté </strong>: .jpg uniquement</p>
                        <h2><strong>Sous total : </strong></h2>
                        <div class="sousTotal">
                            <div class="prixRes">
                                <p>Promotion : </p>
                                <p><strong class="dataPromo">0</strong></p>
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
        let totalPromo = (coutParJour * nbJours).toFixed(2);

        if(totalPromo == NaN) {
            totalPromo = 0;
        }

        txtPromo.textContent = totalPromo;
        txtDuree.textContent = nbJours;
        txtTotal.textContent = totalPromo; // Ajouter plus tard prix de la bannière
    });
}

// POP UP DE CONFIRMATION DE RETRAIT
function popUpConfirmerRetrait(id, nom) {
    const overlay = document.createElement("div");
    overlay.className = "overlayPopUpErreur";
    
    overlay.innerHTML = `
        <main class="popUpErreur" style="text-align : center;">
            <form method="POST" action="../../controllers/RetirerDeLaVente.php">
                <div class="croixFermerLaPage">
                    <div></div>
                    <div></div>
                </div>
                <h1>Souhaitez-vous vraiment retirer ce produit de la vente ?</h1>
                <p><strong>${nom}</strong></p>
                <input type="hidden" name="idproduit" value="${id}">
                <button type="submit" 
                style="
                    color: #ffffff; 
                    background-color: #f14e4e; 
                    border: none; 
                    padding: 10px 20px; 
                    border-radius: 5px; 
                    cursor: pointer; 
                    margin-top: 20px;">
                    Confirmer le retrait
                </button>
            </form>
        </main>`;

    document.body.appendChild(overlay);

    const fermerPopUp = () => {
        overlay.remove();
    };

    overlay.querySelector(".croixFermerLaPage").addEventListener("click", fermerPopUp);
    
    // Fermer si on clique à l'extérieur de la modale
    overlay.addEventListener("click", (e) => {
        if (e.target === overlay) {
            fermerPopUp();
        }
    });
}
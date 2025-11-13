function fermerPopUp(overlay){
    overlay.remove();
}

function popUpModifierMdp(){
    const overlay = document.createElement("div");
    overlay.className = "overlayPopUpCompteClient";
    overlay.innerHTML = `
                <main class="mainPopUpCompteClient">
                <div class="croixFermerLaPage">
                    <div></div>
                    <div></div>
                </div> 
                <h1>Modification de votre mot de passe</h1>
                <section>
                    <div class="formulaireMdp">
                        <form action="">
                            <input type="password" placeholder="Ancien mot de passe">
                            <input type="password" placeholder="Nouveau mot de passe">
                            <input type="password" placeholder="Confirmer le nouveau mot de passe">
                        
                            <article>
                                <div class="croix">
                                    <div></div>
                                    <div></div>
                                </div> 
                                <p>Longueur minimale de 12 charactères</p>
                            </article>
    
                            <article>
                                <div class="croix">
                                    <div></div>
                                    <div></div>
                                </div> 
                                <p>Au moins une minuscule / majuscule</p>
                            </article>
    
                            <article>
                                <div class="croix">
                                    <div></div>
                                    <div></div>
                                </div> 
                                <p>Au moins un chiffre</p>
                            </article>
    
                            <article>
                                <div class="croix">
                                    <div></div>
                                    <div></div>
                                </div>  
                                <p>Au moins un charactères spéciale</p>
                            </article>
                        </div>
                            <button type="button" onclick="fermerFenetre()">Valider</button>
                        </form>
                    </section>
                </main>`;
    document.body.appendChild(overlay);

    let croixFermerLaPage = document.getElementsByClassName("croixFermerLaPage");
    croixFermerLaPage = croixFermerLaPage[0];
    croixFermerLaPage.addEventListener("click",fermerPopUp(overlay));

    let input = document.querySelectorAll("input");
    let ancienMdp = input[0];
    let nouveauMdp = input[1];
    let confirmationMdp = input[2];
    let button = document.querySelectorAll("button");
    let valider = button[0];
    valider.addEventListener("click",fermerPopUp(overlay));

    ancienMdpChiffree = vignere(ancienMdp.value, cle, 1);

    if (ancienMdpChiffree === mdpChiffree && nouveauMdp.value === confirmationMdp.value){
        valider.disabled = false;
        valider.cursor = "pointer";
    } else {
        valider.disabled = true;
        valider.cursor = "default";
    }

    
}

function verifierChamp() {
    const bouton = document.querySelector(".boutonModiferProfil");
    const champs = document.querySelectorAll("section input");
    let tousRemplis = true;
    
    for (let i = 0; i < champs.length; i++) {
        let valeur = champs[i].value.trim();
        
        // Le champ adresse2 est optionnel
        if (i !== 5 && valeur === "") {
            tousRemplis = false;
            break;
        }
        
        // Validation spécifique pour le numéro de téléphone
        if (i === 9) { 
            if (!/^0[67](\s[0-9]{2}){4}$/.test(valeur)) {
                tousRemplis = false;
                break;
            }
        }
        
        // Validation spécifique pour l'email
        if (i === 10) {
            if (!/^[a-zA-Z0-9._-]+@[a-zA-Z0-9._-]+\.[a-zA-Z]{2,}$/.test(valeur)) {
                tousRemplis = false;
                break;
            }
        }            
    }
    
    bouton.disabled = !tousRemplis;
}
let enModif = false;

// Création de l'input pour la photo de profil
let ajoutPhoto = document.createElement("input");
ajoutPhoto.type = "file";
ajoutPhoto.id = "photoProfil";
ajoutPhoto.name = "photoProfil";
ajoutPhoto.accept = "image/*";
ajoutPhoto.style.display = "none";
ajoutPhoto.autocomplete = "off";

let conteneur = document.getElementById("titreCompte");
let imageProfile = document.getElementById("imageProfile");
let bnModifier = document.getElementsByClassName("boutonModiferProfil");
let bnModifMdp = document.getElementsByClassName("boutonModifierMdp");
let bnAnnuler = document.getElementsByClassName("boutonAnnuler");

function modifierProfil(event) {
    
    // Empêche le comportement par défaut du bouton
    event.preventDefault();
    
    if (!enModif) {
        // Remplacer les <p> par des <input> pour modification
        let elems = document.querySelectorAll("section p");
        const nomsChamps = [
            "pseudo", "prenom", "nom", "dateNaissance",
            "adresse1", "adresse2", "codePostal", "ville", "pays",
            "telephone", "email"
        ];
        
        for (let i = 0; i < elems.length; i++) {
            let texteActuel = elems[i].innerText;
            let input = document.createElement("input");
            input.value = texteActuel;
            input.name = nomsChamps[i];
            input.id = nomsChamps[i];
            input.autocomplete = nomsChamps[i];
            
            // Définir le type d'input approprié
            if (i === 9) input.type = "tel";
            else if (i === 10) input.type = "email";
            else input.type = "text";
            
            switch(i) {
                case 0:
                input.placeholder = "Entrez votre pseudo";
                break;
                case 1:
                input.placeholder = "Entrez votre nom";
                break;
                case 2:
                input.placeholder = "Entrez votre prénom";
                break;
                case 3:
                input.placeholder = "Entrez votre date de naissance jj/mm/aaaa";
                break;
                case 4:
                input.placeholder = "Entrez votre adresse";
                break;
                case 6:
                input.placeholder = "Entrez votre code postal";
                break;
                case 7:
                input.placeholder = "Entrez votre ville";
                break;
                case 8:
                input.placeholder = "Entrez votre pays";
                break;
                case 9:
                input.placeholder = "Entrez votre numéro de téléphone";
                break;
                case 10:
                input.placeholder = "Entrez votre email";
                break;
            }
            
            elems[i].parentNode.replaceChild(input, elems[i]);
        }
        
        // Modifier le bouton "Modifier" en "Enregistrer"
        bnModifier[0].innerHTML = "Enregistrer";
        bnModifier[0].style.backgroundColor = "#64a377";
        bnModifier[0].style.color = "#FFFEFA";
        conteneur.appendChild(ajoutPhoto);
        
        imageProfile.style.cursor = "pointer";
        imageProfile.onclick = () => ajoutPhoto.click();
        
        enModif = true;
        
        bnAnnuler[0].style.display = "block";
        bnAnnuler[0].style.color = "white";
        
        document.querySelector("section").addEventListener("input", verifierChamp);
        verifierChamp();
        
    } 
    
    else {
        // Soumettre le formulaire pour enregistrer les modifications
        document.querySelector("form").submit();
    }
}

bnModifier[0].addEventListener("click", modifierProfil);

function fermerFenetre(){
    window.close();
}

const valeursInitiales = Array.from(document.querySelectorAll("section p"))

function boutonAnnuler() {
    
    let inputs = document.querySelectorAll("section input");
    
    for (let i = 0; i < inputs.length; i++) {
        let p = document.createElement("p");
        p.innerText = valeursInitiales[i].innerText;
        inputs[i].parentNode.replaceChild(p, inputs[i]);
    }
    
    if (document.getElementById("photoProfil")) {
        document.getElementById("photoProfil").remove();
    }
    
    enModif = false;
    
    bnModifier[0].innerHTML = "Modifier";
    bnModifier[0].style.backgroundColor = "#e4d9ff";
    bnModifier[0].style.color = "#273469";
    bnModifier[0].disabled = false; 
    
    bnAnnuler[0].style.display = "none";
    
    imageProfile.style.cursor = "default";
    imageProfile.onclick = null;
    
}


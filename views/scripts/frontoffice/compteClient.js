function fermerPopUp() {
    const overlay = document.querySelector(".overlayPopUpCompteClient");
    if (overlay) overlay.remove();
}

function popUpModifierMdp() {
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
                    <form id="formMdp" method="POST" action="../../controllers/modifierMdp.php">
                        <input type="password" name="ancienMdp" placeholder="Ancien mot de passe">
                        <input type="password" name="nouveauMdp" placeholder="Nouveau mot de passe">
                        <input type="password" name="confirmationMdp" placeholder="Confirmer le nouveau mot de passe">

                        <article>
                            <div class="croix"><div></div><div></div></div>
                            <p>Longueur minimale de 12 caractères</p>
                        </article>
                        <article>
                            <div class="croix"><div></div><div></div></div>
                            <p>Au moins une minuscule / majuscule</p>
                        </article>
                        <article>
                            <div class="croix"><div></div><div></div></div>
                            <p>Au moins un chiffre</p>
                        </article>
                        <article>
                            <div class="croix"><div></div><div></div></div>
                            <p>Au moins un caractère spécial</p>
                        </article>

                        <button type="submit" disabled>Valider</button>
                    </form>
                </div>
            </section>
        </main>`;
    document.body.appendChild(overlay);

    // Fermer pop-up
    overlay.querySelector(".croixFermerLaPage").addEventListener("click", fermerPopUp);

    const ancienMdp = overlay.querySelector("input[name='ancienMdp']");
    const nouveauMdp = overlay.querySelector("input[name='nouveauMdp']");
    const confirmationMdp = overlay.querySelector("input[name='confirmationMdp']");
    const valider = overlay.querySelector("button");

    function verifierMdp() {
        const ancienMdpChiffree = vignere(ancienMdp.value, cle, 1);
        const nouveauMdpChiffree = vignere(nouveauMdp.value, cle, 1);
        const confirmationMdpChiffree = vignere(confirmationMdp.value, cle, 1);

        if (ancienMdpChiffree === mdp &&
            nouveauMdpChiffree === confirmationMdpChiffree &&
            nouveauMdpChiffree !== "") {
            valider.disabled = false;
            valider.style.cursor = "pointer";
        } else {
            valider.disabled = true;
            valider.style.cursor = "default";
        }
    }

    [ancienMdp, nouveauMdp, confirmationMdp].forEach(input => input.addEventListener("input", verifierMdp));
}

function setError(element, message) {
    if (!element) return;
    element.classList.add("invalid");

    let container = element.closest(".input-container") || element.parentElement;
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

    let container = element.closest(".input-container") || element.parentElement;
    if (!container) return;

    const err = container.querySelector(".error-message");
    if (err) err.textContent = "";
}

function verifierChamp() {
    const bouton = document.querySelector(".boutonModiferProfil");
    const champs = document.querySelectorAll("section input");
    let tousRemplis = true;

    champs.forEach((champ, i) => {
        const valeur = champ.value.trim();
        clearError(champ);

        // Le champ adresse2 est optionnel
        if (i !== 5 && valeur === "") {
            tousRemplis = false;
            setError(champ, "Le champ obligatoire est vide");
        }

        // Validation date de naissance
        if (i === 3 && !/^([0][1-9]|[12][0-9]|[3][01])\/([0][1-9]|[1][012])\/([1][9][0-9]{2}|20[0-2][0-5])$/.test(valeur)) {
            tousRemplis = false;
            setError(champ, "Format attendu : jj/mm/aaaa");
        }

        // Validation téléphone
        if (i === 9 && !/^0[67](\s[0-9]{2}){4}$/.test(valeur)) {
            tousRemplis = false;
            setError(champ, "Format attendu : 06 01 02 03 04");
        }

        // Validation email
        if (i === 10 && !/^[a-zA-Z0-9._-]+@[a-zA-Z0-9._-]+\.[a-zA-Z]{2,}$/.test(valeur)) {
            tousRemplis = false;
            setError(champ, "Email invalide (ex: nom@domaine.fr)");
        }
    });

    bouton.disabled = !tousRemplis;
}

let enModif = false;
const ajoutPhoto = document.createElement("input");
ajoutPhoto.type = "file";
ajoutPhoto.id = "photoProfil";
ajoutPhoto.name = "photoProfil";
ajoutPhoto.accept = "image/*";
ajoutPhoto.style.display = "none";

const conteneur = document.getElementById("titreCompte");
const imageProfile = document.getElementById("imageProfile");
const bnModifier = document.querySelector(".boutonModiferProfil");
const bnAnnuler = document.querySelector(".boutonAnnuler");

// Stockage des valeurs initiales
const valeursInitiales = Array.from(document.querySelectorAll("section p")).map(p => p.innerText);

function modifierProfil(event) {
    event.preventDefault();

    const section = document.querySelector("section");

    if (!enModif) {
        const elems = Array.from(section.querySelectorAll("p"));
        const nomsChamps = [
            "pseudo","prenom","nom","dateNaissance",
            "adresse1","adresse2","codePostal","ville","pays",
            "telephone","email"
        ];

        elems.forEach((p, i) => {
            const container = document.createElement("div");
            container.className = "input-container";

            const input = document.createElement("input");
            input.value = p.innerText;
            input.name = nomsChamps[i];
            input.id = nomsChamps[i];
            input.autocomplete = nomsChamps[i];

            if (i === 9) input.type = "tel";
            else if (i === 10) input.type = "email";
            else input.type = "text";

            container.appendChild(input);
            p.parentNode.replaceChild(container, p);
        });

        // Modifier le bouton
        bnModifier.textContent = "Enregistrer";
        bnModifier.style.backgroundColor = "#64a377";
        bnModifier.style.color = "#FFFEFA";

        conteneur.appendChild(ajoutPhoto);
        imageProfile.style.cursor = "pointer";
        imageProfile.onclick = () => ajoutPhoto.click();

        enModif = true;
        bnAnnuler.style.display = "block";
        bnAnnuler.style.color = "white";

        section.addEventListener("input", verifierChamp);
        verifierChamp();
    } else {
        document.querySelector("form").submit();
    }
}

bnModifier.addEventListener("click", modifierProfil);

function boutonAnnuler() {
    const inputs = document.querySelectorAll("section input");

    inputs.forEach((input, i) => {
        const p = document.createElement("p");
        p.innerText = valeursInitiales[i];

        const parent = input.parentNode;
        if (parent.classList.contains("input-container")) {
            parent.parentNode.replaceChild(p, parent);
        } else {
            parent.replaceChild(p, input);
        }
    });

    if (document.getElementById("photoProfil")) {
        document.getElementById("photoProfil").remove();
    }

    enModif = false;
    bnModifier.textContent = "Modifier";
    bnModifier.style.backgroundColor = "#e4d9ff";
    bnModifier.style.color = "#273469";
    bnModifier.disabled = false;
    bnAnnuler.style.display = "none";

    imageProfile.style.cursor = "default";
    imageProfile.onclick = null;
}

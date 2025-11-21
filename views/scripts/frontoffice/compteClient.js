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

function validerMdp(mdp) {
  if (mdp.length < 12) {
    return false;
  }

  const contientUneMaj = /[A-Z]/.test(mdp);
  const contientUnChiffre = /[0-9]/.test(mdp);
  const contientUnCharSpe = /[^a-zA-Z0-9]/.test(mdp);

  return contientUneMaj && contientUnChiffre && contientUnCharSpe;
}

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
                            <div class="input"><input type="password" name="ancienMdp" placeholder="Ancien mot de passe"></div>
                            <div class="input"><input type="password" name="nouveauMdp" placeholder="Nouveau mot de passe"></div>
                            <div class="input"><input type="password" name="confirmationMdp" placeholder="Confirmer le nouveau mot de passe"></div>
                            
                        
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
                            <button type="submit">Valider</button>
                        </form>
                    </section>
                </main>`;
  document.body.appendChild(overlay);

  let croixFermerLaPage = overlay.getElementsByClassName("croixFermerLaPage");
  croixFermerLaPage = croixFermerLaPage[0];
  croixFermerLaPage.addEventListener("click", fermerPopUp);

  let form = overlay.querySelector("form");
  let button = overlay.querySelectorAll("button");
  let valider = button[0];
  let input = overlay.querySelectorAll("input");

  let ancienMdp = input[0];
  let nouveauMdp = input[1];
  let confirmationMdp = input[2];

  function verifMdp(event) {
    let testAncien = false;
    let testNouveau = false;
    let testConfirm = false;

    const ancien = vignere(ancienMdp.value, cle, 1);
    const nouveau = vignere(nouveauMdp.value, cle, 1);
    const confirm = vignere(confirmationMdp.value, cle, 1);

    if (ancien !== mdp) {
      setError(ancienMdp, "L'ancien mot de passe est incorrect");
    } else {
      clearError(ancienMdp);
      testAncien = true;
    }

    if (!validerMdp(vignere(nouveau, cle, -1))) {
      setError(
        nouveauMdp,
        "Mot de passe incorrect, il doit respecter les conditions ci-dessous"
      );
    } else {
      clearError(nouveauMdp);
      testNouveau = true;
    }

    if (nouveau !== confirm) {
      setError(confirmationMdp, "Les mots de passe ne correspondent pas");
    } else {
      clearError(confirmationMdp);
      testConfirm = true;
    }

    if (!(testAncien && testNouveau && testConfirm)) {
      event.preventDefault();
    } else {
      nouveauMdp.value = nouveau;
      confirmationMdp.value = confirm;
      form.submit();
    }
  }

  valider.addEventListener("click", verifMdp);
}

function verifierChamp() {
  const bouton = document.querySelector(".boutonModiferProfil");
  const champs = document.querySelectorAll("section input");
  let tousRemplis = true;

  if (champs.length === 0) {
    if (bouton) bouton.disabled = false;
    return;
  }

  for (let i = 0; i < champs.length; i++) {
      clearError(champs[i]);
  }

  for (let i = 0; i < champs.length; i++) {
    let valeur = champs[i].value.trim();
    
    if (( i === 0 || i === 1 || i === 2 || i === 3 ||i === 9 ||i === 10) && valeur === "") {
      tousRemplis = false;
      setError(champs[i], "Ce champ est obligatoire");
      continue;
    } 

    if (i === 3 && valeur !== "") {
      if (!/^([0][1-9]|[12][0-9]|[3][01])\/([0][1-9]|[1][012])\/([1][9][0-9][0-9]|[2][0][0-1][0-9]|[2][0][2][0-5])$/.test(valeur)) {
        tousRemplis = false;
        setError(champs[i], "Format attendu : jj/mm/aaaa");
      }
    }

    if (i === 9 && valeur !== "") {
      if (!/^0[67](\s[0-9]{2}){4}$/.test(valeur)) {
        tousRemplis = false;
        setError(champs[i], "Format attendu : 06 01 02 03 04");
      }
    }

    if (i === 10 && valeur !== "") {
      if (!/^[a-zA-Z0-9._-]+@[a-zA-Z0-9._-]+\.[a-zA-Z]{2,}$/.test(valeur)) {
        tousRemplis = false;
        setError(champs[i], "Email invalide (ex: nom@domaine.fr)");
      }
    }
  }

  if (bouton) {
    bouton.disabled = !tousRemplis;
  }
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
  event.preventDefault();

  if (!enModif) {
    // Remplacer les <p> par des <input> pour modification
    let elems = document.querySelectorAll("section p");
    const nomsChamps = [
      "pseudo",
      "prenom",
      "nom",
      "dateNaissance",
      "adresse1",
      "adresse2",
      "codePostal",
      "ville",
      "pays",
      "telephone",
      "email",
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

      switch (i) {
        case 0:
          input.placeholder = "Pseudo*";
          break;
        case 1:
          input.placeholder = "Nom*";
          break;
        case 2:
          input.placeholder = "Prénom*";
          break;
        case 3:
          input.placeholder = "Date de naissance*";
          break;
        case 4:
          input.placeholder = "Adresse";
          break;
        case 5:
          input.placeholder = "Complément d'adresse";
          break;
        case 6:
          input.placeholder = "Code postal";
          break;
        case 7:
          input.placeholder = "Ville";
          break;
        case 8:
          input.placeholder = "Pays";
          break;
        case 9:
          input.placeholder = "Numéro de téléphone*";
          break;
        case 10:
          input.placeholder = "Email*";
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

    const inputs = document.querySelectorAll("section input");

    for (let i = 0; i < inputs.length; i++) {
        inputs[i].addEventListener("input", verifierChamp);
    }

    verifierChamp();
    

  } else {
      let form = document.querySelector("form");
      form.submit();
  }
}

bnModifier[0].addEventListener("click", modifierProfil);

const valeursInitiales = Array.from(document.querySelectorAll("section p"));

function boutonAnnuler() {
  const champs = document.querySelectorAll("section input");
  for (let i = 0; i < champs.length; i++) {
      clearError(champs[i]);
  }

  let inputs = document.querySelectorAll("section input");

  for (let i = 0; i < inputs.length; i++) {
    let p = document.createElement("p");
    p.innerText = valeursInitiales[i].innerText;

    let currentParent = inputs[i].parentNode;
    currentParent.replaceChild(p, inputs[i]);
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
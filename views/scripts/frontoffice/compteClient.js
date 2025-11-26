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
  
  const contientMin = /[a-z]/.test(mdp);
  const contientMaj = /[A-Z]/.test(mdp);
  const contientUnChiffre = /[0-9]/.test(mdp);
  const contientUnCharSpe = /[^a-zA-Z0-9]/.test(mdp);
  
  return (contientMin || contientMaj) && contientUnChiffre && contientUnCharSpe;
}

function fermerPopUp() {
  const overlay = document.querySelector(".overlayPopUpCompteClient");
  if (overlay) overlay.remove();
}

function popUpModifierMdp() {
  const overlay = document.createElement("div");
  overlay.className = "overlayPopUpCompteClient";

  const style = document.createElement('style');
  style.innerHTML = `
    .critere-valide p { color: #198754 !important; font-weight: bold; }
    .critere-valide .croix { display: none; } /* On cache la croix rouge */
    .critere-valide::before { 
        content: '✓'; color: #198754; font-weight: bold; font-size: 20px; margin-right: 10px;
    }
    .critere-invalide p { color: #dc3545; } /* Rouge si pas bon */
    article { display: flex; align-items: center; margin-bottom: 5px; }
  `;
  overlay.appendChild(style);

  overlay.innerHTML += `
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
                            
                            <div style="margin-top:15px; text-align:left;">
                                <article id="regle-longueur" class="critere-invalide">
                                    <div class="croix"><div></div><div></div></div> 
                                    <p>Longueur minimale de 12 caractères</p>
                                </article>
            
                                <article id="regle-maj-min" class="critere-invalide">
                                    <div class="croix"><div></div><div></div></div> 
                                    <p>Au moins une minuscule / majuscule</p>
                                </article>
            
                                <article id="regle-chiffre" class="critere-invalide">
                                    <div class="croix"><div></div><div></div></div> 
                                    <p>Au moins un chiffre</p>
                                </article>
            
                                <article id="regle-special" class="critere-invalide">
                                    <div class="croix"><div></div><div></div></div> 
                                    <p>Au moins un caractère spécial</p>
                                </article>
                            </div>

                        </div>
                        <button type="submit" disabled style="opacity:0.5; cursor:not-allowed;">Valider</button>
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

  // maj des couleurs
  function updateCriteres() {
      const val = nouveauMdp.value;

      // longueur
      const rLongueur = document.getElementById('regle-longueur');
      rLongueur.className = val.length >= 12 ? 'critere-valide' : 'critere-invalide';

      // min/maj
      const rMajMin = document.getElementById('regle-maj-min');
      if (/[a-z]/.test(val) || /[A-Z]/.test(val)) rMajMin.className = 'critere-valide';
      else rMajMin.className = 'critere-invalide';

      // chiffre
      const rChiffre = document.getElementById('regle-chiffre');
      rChiffre.className = /[0-9]/.test(val) ? 'critere-valide' : 'critere-invalide';

      // caractere special
      const rSpecial = document.getElementById('regle-special');
      rSpecial.className = /[^a-zA-Z0-9]/.test(val) ? 'critere-valide' : 'critere-invalide';

      // btn valider
      const mdpOk = validerMdp(val);
      const confirmOk = (val === confirmationMdp.value && val.length > 0);

      if (mdpOk && confirmOk) {
          valider.disabled = false;
          valider.style.opacity = "1";
          valider.style.cursor = "pointer";
      } else {
          valider.disabled = true;
          valider.style.opacity = "0.5";
          valider.style.cursor = "not-allowed";
      }
  }

  nouveauMdp.addEventListener('input', updateCriteres);
  confirmationMdp.addEventListener('input', updateCriteres);
  
  function verifMdp(event) {
    event.preventDefault();

    // verif ancien mdp
    const ancien = (typeof vignere !== 'undefined') ? vignere(ancienMdp.value, cle, 1) : ancienMdp.value;
    
    if (typeof mdp !== 'undefined' && ancien !== mdp) {
      setError(ancienMdp, "L'ancien mot de passe est incorrect");
      return;
    } else {
      clearError(ancienMdp);
    }
    
    if (!validerMdp(nouveauMdp.value)) {
      setError(nouveauMdp, "Mot de passe incorrect, respectez les critères.");
      return;
    } else {
      clearError(nouveauMdp);
    }
    
    if (nouveauMdp.value !== confirmationMdp.value) {
      setError(confirmationMdp, "Les mots de passe ne correspondent pas");
      return;
    } else {
      clearError(confirmationMdp);
    }
    
    // chiffre les nouveaux mots de passe avant l'envoi
    if (typeof vignere !== 'undefined') {
        nouveauMdp.value = vignere(nouveauMdp.value, cle, 1);
        confirmationMdp.value = vignere(confirmationMdp.value, cle, 1);
    }
    
    form.submit();
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
    
    if ((i === 0 || i === 1 || i === 2 || i === 3 || i === 9 || i === 10) && valeur === "") {
      tousRemplis = false;
      setError(champs[i], "Ce champ est obligatoire");
      continue;
    }
    
    // Regex
    if (i === 3 && valeur !== "") {
      if (!/^([0][1-9]|[12][0-9]|[3][01])\/([0][1-9]|[1][012])\/([1][9][0-9][0-9]|[2][0][0-1][0-9]|[2][0][2][0-5])$/.test(valeur)) {
      tousRemplis = false;
      setError(champs[i], "Format attendu : jj/mm/aaaa");
    }
  }
    // Regex CP
    if (i === 6 && valeur !== "") {
      if (!/^[0-9]{5}$/.test(valeur)) {
          tousRemplis = false;
          setError(champs[i], "5 chiffres requis");
      }
  }
  
  // Regex Tel
  if (i === 9 && valeur !== "") {
    if (
      !/^0[0-9](\s[0-9]{2}){4}$/.test(valeur) &&
      !/^0[0-9]([0-9]{2}){4}$/.test(valeur)
    ) {
      tousRemplis = false;
      setError(champs[i], "Format attendu : 06 01 02 03 04 ou 0601020304");
    }
  }
  
  // Regex Email
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

// input pour la photo de profil
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
      
      input.name = nomsChamps[i];
      input.id = nomsChamps[i];
      input.autocomplete = nomsChamps[i];
      
      if (i === 9) input.type = "tel";
      else if (i === 10) input.type = "email";
      else input.type = "text";
      
      // Placeholders
      switch (i) {
        case 0: input.placeholder = "Pseudo*"; break;
        case 1: input.placeholder = "Nom*"; break;
        case 2: input.placeholder = "Prénom*"; break;
        case 3: input.placeholder = "Date de naissance*"; break;
        case 4: input.placeholder = "Adresse"; break;
        case 5: input.placeholder = "Complément d'adresse"; break;
        case 6: input.placeholder = "Code postal"; break;
        case 7: input.placeholder = "Ville"; break;
        case 8: input.placeholder = "Pays"; break;
        case 9: input.placeholder = "Numéro de téléphone*"; break;
        case 10: input.placeholder = "Email*"; break;
      }
      
      elems[i].parentNode.replaceChild(input, elems[i]);
    }
    
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
let imageProfileOriginalSrc = imageProfile ? imageProfile.src : "";

ajoutPhoto.addEventListener("change", function () {
  const file = this.files[0];
  if (file) {
    const reader = new FileReader();
    reader.onload = function (e) {
      imageProfile.src = e.target.result;
    };
    reader.readAsDataURL(file);
  }
});

function boutonAnnuler() {
  const champs = document.querySelectorAll("section input");
  for (let i = 0; i < champs.length; i++) {
    clearError(champs[i]);
  }
  
  let inputs = document.querySelectorAll("section input");
  
  for (let i = 0; i < inputs.length; i++) {
    let p = document.createElement("p");
    let texteOriginal = valeursInitiales[i].innerText;
    p.innerText = texteOriginal;
    
    let currentParent = inputs[i].parentNode;
    currentParent.replaceChild(p, inputs[i]);
  }
  
  if (imageProfile && typeof imageProfileOriginalSrc !== "undefined") {
    imageProfile.src = imageProfileOriginalSrc;
  }
  
  const photoInput = document.getElementById("photoProfil");
  if (photoInput) {
    try {
      photoInput.value = "";
    } catch (e) {
    }
    photoInput.remove();
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
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
  let tousValides = true;

  if (champs.length === 0) {
    bouton.disabled = false;
    return;
  }

  // Définir quels champs sont obligatoires
  const champsObligatoires = [0, 1, 2, 3, 9, 10]; // pseudo, prenom, nom, dateNaissance, telephone, email
  // Les champs 4, 5, 6, 7, 8 (adresse1, adresse2, codePostal, ville, pays) sont optionnels

  for (let i = 0; i < champs.length; i++) {
    let valeur = champs[i].value.trim();
    let champObligatoire = champsObligatoires.includes(i);

    // Vérifier si le champ obligatoire est vide
    if (champObligatoire && valeur === "") {
      tousValides = false;
      setError(champs[i], "Ce champ est obligatoire");
      continue;
    }

    // Si le champ est vide mais non obligatoire, on passe au suivant
    if (valeur === "" && !champObligatoire) {
      clearError(champs[i]);
      continue;
    }

    // Validations spécifiques pour les champs remplis
    if (i === 3 && valeur !== "") {
      // Validation date de naissance
      if (!/^([0][1-9]|[12][0-9]|[3][01])\/([0][1-9]|[1][012])\/([1][9][0-9][0-9]|[2][0][0-1][0-9]|[2][0][2][0-5])$/.test(valeur)) {
        tousValides = false;
        setError(champs[i], "Format attendu : jj/mm/aaaa");
      } else {
        clearError(champs[i]);
      }
    } else if (i === 9 && valeur !== "") {
      // Validation téléphone
      if (!/^0[0-9](\s[0-9]{2}){4}$/.test(valeur) && !/^0[0-9]([0-9]{2}){4}$/.test(valeur)) {
        tousValides = false;
        setError(champs[i], "Format attendu : 06 01 02 03 04 ou 0601020304");
      } else {
        clearError(champs[i]);
      }
    } else if (i === 10 && valeur !== "") {
      // Validation email
      if (!/^[a-zA-Z0-9._-]+@[a-zA-Z0-9._-]+\.[a-zA-Z]{2,}$/.test(valeur)) {
        tousValides = false;
        setError(champs[i], "Email invalide (ex: nom@domaine.fr)");
      } else {
        clearError(champs[i]);
      }
    } else {
      // Pour tous les autres champs valides
      clearError(champs[i]);
    }
  }

  bouton.disabled = !tousValides;
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

      // Définir les placeholders avec * pour les champs obligatoires
      const placeholders = [
        "Pseudo*",
        "Prénom*",
        "Nom*",
        "Date de naissance*",
        "Adresse",
        "Complément d'adresse",
        "Code postal",
        "Ville",
        "Pays",
        "Numéro de téléphone*",
        "Email*"
      ];
      input.placeholder = placeholders[i];

      elems[i].parentNode.replaceChild(input, elems[i]);
    }

    // Modifier le bouton "Modifier" en "Enregistrer"
    bnModifier[0].innerHTML = "Enregistrer";
    bnModifier[0].style.backgroundColor = "#64a377";
    bnModifier[0].style.color = "#FFFEFA";
    conteneur.appendChild(ajoutPhoto);

    imageProfile.style.cursor = "pointer";
    imageProfile.onclick = () => ajoutPhoto.click();
    bnModifier[0].disabled = false;

    enModif = true;

    bnAnnuler[0].style.display = "block";
    bnAnnuler[0].style.color = "white";

    // Ajouter les événements de validation
    document.querySelector("section").addEventListener("input", verifierChamp);
    document.querySelector("section").addEventListener("blur", verifierChamp, true);
    
    // Vérifier immédiatement les champs
    verifierChamp();
  } else {
    // Vérifier une dernière fois avant de soumettre
    verifierChamp();
    
    // Soumettre uniquement si tous les champs sont valides
    if (!bnModifier[0].disabled) {
      document.querySelector("form").submit();
    }
  }
}

bnModifier[0].addEventListener("click", modifierProfil);

const valeursInitiales = Array.from(document.querySelectorAll("section p"));

function boutonAnnuler() {
  let inputs = document.querySelectorAll("section input");

  for (let i = 0; i < inputs.length; i++) {
    let p = document.createElement("p");
    p.innerText = valeursInitiales[i].innerText;

    let currentParent = inputs[i].parentNode;

    currentParent.replaceChild(p, inputs[i]);
  }

  document.getElementById("photoProfil").remove();

  enModif = false;

  bnModifier[0].innerHTML = "Modifier";
  bnModifier[0].style.backgroundColor = "#e4d9ff";
  bnModifier[0].style.color = "#273469";
  bnModifier[0].disabled = false;

  bnAnnuler[0].style.display = "none";

  imageProfile.style.cursor = "default";
  imageProfile.onclick = null;
}
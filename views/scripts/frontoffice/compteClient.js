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
  //On regarde si il y a plus de 12 char
  if (mdp.length < 12) {
    return false;
  }

  const contientUneMaj = /[A-Z]/.test(mdp);

  const contientUnChiffre = /[0-9]/.test(mdp);

  const contientUnCharSpe = /[^a-zA-Z0-9]/.test(mdp);

  //On regarde si il le mdp a minimum 1 maj 1 chiffre et 1 char spé
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
  //Appel de la fonction fermer la pop up quand on clique sur la croix
  croixFermerLaPage.addEventListener("click", fermerPopUp);

  let form = overlay.querySelector("form");

  let button = overlay.querySelectorAll("button");
  let valider = button[0];

  let input = overlay.querySelectorAll("input");

  //On récupère les 3 inputs
  let ancienMdp = input[0];
  let nouveauMdp = input[1];
  let confirmationMdp = input[2];

  function verifMdp(event) {
    let testAncien = false;
    let testNouveau = false;
    let testConfirm = false;

    //On chiffre les 3 inputs
    const ancien = vignere(ancienMdp.value, cle, 1);
    const nouveau = vignere(nouveauMdp.value, cle, 1);
    const confirm = vignere(confirmationMdp.value, cle, 1);

    //Vérification si l'ancien mdp correspond à celui dans la bdd
    if (ancien !== mdp) {
      setError(ancienMdp, "L'ancien mot de passe est incorrect");
    } else {
      clearError(ancienMdp);
      testAncien = true;
    }

    //Vérification si le nouveau mdp est valide
    if (!validerMdp(vignere(nouveau, cle, -1))) {
      setError(
        nouveauMdp,
        "Mot de passe incorrect, il doit respecter les conditions ci-dessous"
      );
    } else {
      clearError(nouveauMdp);
      testNouveau = true;
    }

    //Vérification si le nouveau mdp correspond à la confirmation
    if (nouveau !== confirm) {
      setError(confirmationMdp, "Les mots de passe ne correspondent pas");
    } else {
      clearError(confirmationMdp);
      testConfirm = true;
    }

    //Désactive le bouton valider si y'a un des cas qui return false Sinon on envoie le nouveau mdp chiffré dans la BDD
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
    bouton.disabled = false;
    return;
  }


  for (let i = 0; i < champs.length; i++) {
    let valeur = champs[i].value.trim();

    // Le champ adresse2 est optionnel
    if (i === 0 && valeur === "") {
      tousRemplis = false;
      setError(champs[i], "Le champs obligatoire est vide");
    }

    // Validation spécifique pour la date de naissance
    if (i === 3) {
      if (
        !/^([0][1-9]|[12][0-9]|[3][01])\/([0][1-9]|[1][012])\/([1][9][0-9][0-9]|[2][0][0-1][0-9]|[2][0][2][0-5])$/.test(
          valeur
        )
      ) {
        tousRemplis = false;
        setError(champs[i], "Format attendu : jj/mm/aaaa");
      }
    }

    // Validation spécifique pour le numéro de téléphone
    if (i === 9) {
      if (!/^0[0-9](\s[0-9]{2}){4}$/.test(valeur) || !/^0[0-9]([0-9]{2}){4}$/.test(valeur)) {
        tousRemplis = false;
        setError(champs[i], "Format attendu : 06 01 02 03 04 ou 0601020304");
      }
    }

    // Validation spécifique pour l'email
    if (i === 10) {
      if (!/^[a-zA-Z0-9._-]+@[a-zA-Z0-9._-]+\.[a-zA-Z]{2,}$/.test(valeur)) {
        tousRemplis = false;
        setError(champs[i], "Email invalide (ex: nom@domaine.fr)");
      }
    }
    //Si c'est pas vide on affiche pas de message d'erreur
    if (i === 4 && i === 5 && i === 6 && i === 7 && i === 8 || valeur !== "") {
      clearError(champs[i]);
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

    document.querySelector("section").addEventListener("input", verifierChamp);
    verifierChamp();
  } else {
    // Soumettre le formulaire pour enregistrer les modifications
    document.querySelector("form").submit();
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

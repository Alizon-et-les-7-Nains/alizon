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
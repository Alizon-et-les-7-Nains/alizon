// paiement-main.js - Initialisation principale
document.addEventListener("DOMContentLoaded", function () {
  console.log("Initialisation de la page de paiement...");

  // Vérifier que nous sommes sur la page de paiement
  if (!document.body.classList.contains("pagePaiement")) {
    return;
  }

  // 1. Initialisation de l'autocomplétion
  const preloadedData = window.__PAYMENT_DATA__ || {};
  const selectedDepartment = { value: null };

  const codePostalInput = document.querySelector(".code-postal-input");
  const villeInput = document.querySelector(".ville-input");

  if (codePostalInput && villeInput && preloadedData.departments) {
    const autocomplete = new AutocompleteManager({
      codePostalInput: codePostalInput,
      villeInput: villeInput,
      maps: {
        departments: preloadedData.departments,
        citiesByCode: preloadedData.citiesByCode || {},
        postals: preloadedData.postals || {},
      },
      selectedDepartment: selectedDepartment,
    });
  }

  // 2. Configuration du formatage des champs de paiement
  const numCarteInput = document.querySelector(".num-carte");
  const carteDateInput = document.querySelector(".carte-date");
  const cvvInput = document.querySelector(".cvv-input");

  if (numCarteInput) {
    numCarteInput.addEventListener("input", function () {
      LuhnValidator.formatCardNumber(this);
    });

    numCarteInput.addEventListener("blur", function () {
      const value = this.value.replace(/\s/g, "");
      if (value.length >= 16) {
        if (!LuhnValidator.validate(value)) {
          LuhnValidator.setFieldError(this, "Numéro de carte invalide");
        } else if (!/^4/.test(value)) {
          LuhnValidator.setFieldError(
            this,
            "Seules les cartes Visa sont acceptées"
          );
        }
      }
    });
  }

  if (carteDateInput) {
    carteDateInput.addEventListener("input", function () {
      LuhnValidator.formatExpirationDate(this);
    });

    carteDateInput.addEventListener("blur", function () {
      if (this.value && !LuhnValidator.validateExpirationDate(this.value)) {
        LuhnValidator.setFieldError(
          this,
          "Date d'expiration invalide ou dépassée"
        );
      }
    });
  }

  if (cvvInput) {
    cvvInput.addEventListener("input", function () {
      LuhnValidator.formatCVV(this);
    });

    cvvInput.addEventListener("blur", function () {
      if (this.value && !LuhnValidator.validateCVV(this.value)) {
        LuhnValidator.setFieldError(this, "CVV invalide (3 chiffres requis)");
      }
    });
  }

  // 3. Validation du formulaire avant soumission
  const payerButtons = document.querySelectorAll(".payer");
  payerButtons.forEach((button) => {
    button.addEventListener("click", function (e) {
      e.preventDefault();

      // Validation Luhn pour la carte
      if (numCarteInput) {
        const cardValue = numCarteInput.value.replace(/\s/g, "");
        if (cardValue.length < 16) {
          LuhnValidator.setFieldError(
            numCarteInput,
            "Numéro de carte incomplet (16 chiffres requis)"
          );
          return;
        }

        if (!LuhnValidator.validate(cardValue)) {
          LuhnValidator.setFieldError(
            numCarteInput,
            "Numéro de carte invalide (algorithme de Luhn)"
          );
          return;
        }

        if (!/^4/.test(cardValue)) {
          LuhnValidator.setFieldError(
            numCarteInput,
            "Seules les cartes Visa sont acceptées"
          );
          return;
        }
      }

      // Validation de la date d'expiration
      if (
        carteDateInput &&
        !LuhnValidator.validateExpirationDate(carteDateInput.value)
      ) {
        LuhnValidator.setFieldError(
          carteDateInput,
          "Date d'expiration invalide ou dépassée"
        );
        return;
      }

      // Validation du CVV
      if (cvvInput && !LuhnValidator.validateCVV(cvvInput.value)) {
        LuhnValidator.setFieldError(
          cvvInput,
          "CVV invalide (3 chiffres requis)"
        );
        return;
      }

      // Si toutes les validations passent, on peut soumettre le formulaire
      // ou déclencher le paiement via PaymentAPI
      console.log("Formulaire validé avec succès !");

      // Exemple d'appel à PaymentAPI.createOrder()
      // const orderData = collectOrderData();
      // PaymentAPI.createOrder(orderData).then(response => {
      //     // Gérer la réponse
      // });
    });
  });

  console.log("Page de paiement initialisée avec succès");
});

// Fonction pour collecter les données du formulaire
function collectOrderData() {
  return {
    adresseLivraison: document.querySelector(".adresse-input")?.value || "",
    villeLivraison: document.querySelector(".ville-input")?.value || "",
    codePostal: document.querySelector(".code-postal-input")?.value || "",
    numeroCarte:
      document.querySelector(".num-carte")?.value.replace(/\s/g, "") || "",
    nomCarte: document.querySelector(".nom-carte")?.value || "",
    dateExpiration: document.querySelector(".carte-date")?.value || "",
    cvv: document.querySelector(".cvv-input")?.value || "",
    idAdresseFacturation: document.getElementById("checkboxFactAddr")?.checked
      ? document.querySelector('[name="idAdresseFacturation"]')?.value
      : null,
  };
}

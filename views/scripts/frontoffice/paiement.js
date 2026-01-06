// paiement.js - Page de paiement principale (remplace paiement-main.js)
class PaymentPage {
  constructor() {
    this.idAdresseFacturation = null;
    this.savedBillingAddress = null;
    this.selectedDepartment = { value: null };
    this.autocomplete = null;
    this.init();
  }

  init() {
    this.setupReferences();
    this.initializeAutocomplete();
    this.setupFormFormatting();
    this.setupEventListeners();
  }

  setupReferences() {
    this.factAddrCheckbox = document.getElementById("checkboxFactAddr");
    this.billingSection = document.getElementById("billingSection");
    this.confirmationPopup = document.getElementById("confirmationPopup");
    this.popupContent = document.getElementById("popupContent");
    this.closePopupBtn = document.querySelector(".close-popup");
    this.payerButtons = document.querySelectorAll(".payer");
    this.cgvCheckbox = document.getElementById("cgvCheckbox");

    // Références aux champs de formulaire
    this.adresseInput = document.querySelector(".adresse-input");
    this.codePostalInput = document.querySelector(".code-postal-input");
    this.villeInput = document.querySelector(".ville-input");
    this.numCarteInput = document.querySelector(".num-carte");
    this.nomCarteInput = document.querySelector(".nom-carte");
    this.carteDateInput = document.querySelector(".carte-date");
    this.cvvInput = document.querySelector(".cvv-input");
  }

  initializeAutocomplete() {
    const preloadedData = window.__PAYMENT_DATA__ || {};

    if (this.codePostalInput && this.villeInput && preloadedData.departments) {
      this.autocomplete = new AutocompleteManager({
        codePostalInput: this.codePostalInput,
        villeInput: this.villeInput,
        maps: {
          departments: preloadedData.departments,
          citiesByCode: preloadedData.citiesByCode || {},
          postals: preloadedData.postals || {},
        },
        selectedDepartment: this.selectedDepartment,
      });
    }
  }

  setupFormFormatting() {
    // Formatage du numéro de carte avec validation Luhn
    if (this.numCarteInput) {
      this.numCarteInput.addEventListener("input", () => {
        LuhnValidator.formatCardNumber(this.numCarteInput);
      });

      this.numCarteInput.addEventListener("blur", () => {
        const value = this.numCarteInput.value.replace(/\s/g, "");
        if (value.length >= 16) {
          this.validateCardNumber();
        }
      });
    }

    // Formatage de la date d'expiration
    if (this.carteDateInput) {
      this.carteDateInput.addEventListener("input", () => {
        LuhnValidator.formatExpirationDate(this.carteDateInput);
      });
    }

    // Formatage du CVV
    if (this.cvvInput) {
      this.cvvInput.addEventListener("input", () => {
        LuhnValidator.formatCVV(this.cvvInput);
      });
    }
  }

  setupEventListeners() {
    // Toggle section facturation
    if (this.factAddrCheckbox && this.billingSection) {
      this.factAddrCheckbox.addEventListener("change", () => {
        this.billingSection.classList.toggle(
          "active",
          this.factAddrCheckbox.checked
        );
        if (!this.factAddrCheckbox.checked) this.idAdresseFacturation = null;
      });
    }

    // Boutons de paiement
    this.payerButtons.forEach((btn) => {
      btn.addEventListener("click", (e) => this.handlePayment(e));
    });

    // Popup
    if (this.closePopupBtn) {
      this.closePopupBtn.addEventListener("click", () => this.hidePopup());
    }

    this.confirmationPopup.addEventListener("click", (e) => {
      if (e.target === this.confirmationPopup) this.hidePopup();
    });
  }

  validateCardNumber() {
    if (!this.numCarteInput) return false;

    const cardValue = this.numCarteInput.value.replace(/\s/g, "");

    if (cardValue.length < 16) {
      LuhnValidator.setFieldError(
        this.numCarteInput,
        "Numéro de carte incomplet (16 chiffres requis)"
      );
      return false;
    }

    if (!LuhnValidator.validate(cardValue)) {
      LuhnValidator.setFieldError(
        this.numCarteInput,
        "Numéro de carte invalide (algorithme de Luhn)"
      );
      return false;
    }

    if (!/^4/.test(cardValue)) {
      LuhnValidator.setFieldError(
        this.numCarteInput,
        "Seules les cartes Visa sont acceptées"
      );
      return false;
    }

    LuhnValidator.clearFieldError(this.numCarteInput);
    return true;
  }

  validateExpirationDate() {
    if (!this.carteDateInput) return false;

    if (!LuhnValidator.validateExpirationDate(this.carteDateInput.value)) {
      LuhnValidator.setFieldError(
        this.carteDateInput,
        "Date d'expiration invalide ou dépassée"
      );
      return false;
    }

    LuhnValidator.clearFieldError(this.carteDateInput);
    return true;
  }

  validateCVV() {
    if (!this.cvvInput) return false;

    if (!LuhnValidator.validateCVV(this.cvvInput.value)) {
      LuhnValidator.setFieldError(
        this.cvvInput,
        "CVV invalide (3 chiffres requis)"
      );
      return false;
    }

    LuhnValidator.clearFieldError(this.cvvInput);
    return true;
  }

  validateBillingFields(adresse, codePostal, ville) {
    let valid = true;

    LuhnValidator.clearFieldError(adresse);
    LuhnValidator.clearFieldError(codePostal);
    LuhnValidator.clearFieldError(ville);

    if (!adresse.value.trim()) {
      LuhnValidator.setFieldError(adresse, "Adresse de facturation requise");
      valid = false;
    }

    if (!codePostal.value.trim()) {
      LuhnValidator.setFieldError(codePostal, "Code postal requis");
      valid = false;
    } else if (!/^\d{5}$/.test(codePostal.value.trim())) {
      LuhnValidator.setFieldError(
        codePostal,
        "Le code postal doit contenir 5 chiffres"
      );
      valid = false;
    }

    if (!ville.value.trim()) {
      LuhnValidator.setFieldError(ville, "Ville requise");
      valid = false;
    }

    return valid;
  }

  async handlePayment(e) {
    e.preventDefault();

    if (!this.validateForm()) {
      return;
    }

    const formData = this.getFormData();
    const cart = window.__PAYMENT_DATA__?.cart || [];

    if (cart.length === 0) {
      this.showMessage("Votre panier est vide", "error");
      return;
    }

    const stockIssues = cart.filter((item) => item.qty > item.stock);
    if (stockIssues.length > 0) {
      let errorMsg = "Stock insuffisant pour:\n";
      stockIssues.forEach((item) => {
        errorMsg += `- ${item.nom} (stock: ${item.stock}, demandé: ${item.qty})\n`;
      });
      alert(errorMsg);
      return;
    }

    this.showConfirmationPopup(formData, cart);
  }

  validateForm() {
    let isValid = true;
    this.clearAllErrors();

    // Validation des champs requis
    const fields = [
      { element: this.adresseInput, errorKey: "adresse", required: true },
      {
        element: this.codePostalInput,
        errorKey: "code-postal",
        required: true,
      },
      { element: this.villeInput, errorKey: "ville", required: true },
      { element: this.nomCarteInput, errorKey: "nom-carte", required: true },
    ];

    fields.forEach((field) => {
      if (!field.element || !field.element.value.trim()) {
        this.showError(
          `${this.getFieldName(field.errorKey)} requis`,
          field.errorKey
        );
        isValid = false;
      }
    });

    // Validation spécifique des champs de paiement
    if (!this.validateCardNumber()) isValid = false;
    if (!this.validateExpirationDate()) isValid = false;
    if (!this.validateCVV()) isValid = false;

    // Validation code postal
    if (this.codePostalInput && this.codePostalInput.value.trim()) {
      const cp = this.codePostalInput.value.trim();
      if (!/^\d{5}$/.test(cp)) {
        this.showError(
          "Le code postal doit contenir 5 chiffres",
          "code-postal"
        );
        isValid = false;
      }
    }

    // Validation conditions générales
    if (!this.cgvCheckbox || !this.cgvCheckbox.checked) {
      this.showError("Veuillez accepter les conditions générales", "cgv");
      isValid = false;
    }

    // Validation adresse de facturation si différente
    if (this.factAddrCheckbox && this.factAddrCheckbox.checked) {
      isValid =
        this.validateBillingFields(
          document.querySelector(".adresse-fact-input"),
          document.querySelector(".code-postal-fact-input"),
          document.querySelector(".ville-fact-input")
        ) && isValid;
    }

    return isValid;
  }

  getFormData() {
    return {
      adresseLivraison: this.adresseInput.value.trim(),
      villeLivraison: this.villeInput.value.trim(),
      codePostal: this.codePostalInput.value.trim(),
      numeroCarte: this.numCarteInput.value.replace(/\s/g, ""),
      nomCarte: this.nomCarteInput.value.trim(),
      dateExpiration: this.carteDateInput.value.trim(),
      cvv: this.cvvInput.value.trim(),
    };
  }

  showConfirmationPopup(formData, cart) {
    let cartHtml = '<div class="cart-summary">';
    let total = 0;

    cart.forEach((item) => {
      const itemTotal = item.prix * item.qty;
      total += itemTotal;

      cartHtml += `
                <div class="cart-item-summary">
                    <img src="${item.img}" alt="${
        item.nom
      }" class="cart-item-image">
                    <div class="cart-item-info">
                        <div class="cart-item-name">${item.nom}</div>
                        <div class="cart-item-details">
                            Quantité: ${item.qty} × ${item.prix.toFixed(
        2
      )}€ = ${itemTotal.toFixed(2)}€
                        </div>
                    </div>
                </div>
            `;
    });

    cartHtml += "</div>";

    const popupHtml = `
            <h2>Confirmation de commande</h2>
            <div class="order-info">
                <p><strong>Adresse de livraison :</strong><br>
                ${formData.adresseLivraison}<br>
                ${formData.codePostal} ${formData.villeLivraison}</p>
                
                <p><strong>Paiement :</strong> Carte Visa se terminant par ${formData.numeroCarte.slice(
                  -4
                )}</p>
            </div>
            
            <h3>Récapitulatif du panier</h3>
            ${cartHtml}
            
            <div class="total-section">
                <h3>Total : ${total.toFixed(2)}€</h3>
            </div>
            
            <div class="popup-buttons">
                <button class="btn-cancel">Annuler</button>
                <button class="btn-confirm">Confirmer la commande</button>
            </div>
        `;

    this.popupContent.innerHTML = popupHtml;
    this.confirmationPopup.style.display = "flex";

    this.setupPopupButtons(formData, cart);
  }

  setupPopupButtons(formData, cart) {
    const confirmBtn = this.popupContent.querySelector(".btn-confirm");
    const cancelBtn = this.popupContent.querySelector(".btn-cancel");

    if (cancelBtn) {
      cancelBtn.addEventListener("click", () => this.hidePopup());
    }

    if (confirmBtn) {
      confirmBtn.addEventListener("click", () =>
        this.processOrder(formData, confirmBtn)
      );
    }
  }

  async processOrder(formData, confirmBtn) {
    confirmBtn.disabled = true;
    confirmBtn.textContent = "Traitement en cours...";

    try {
      const result = await PaymentAPI.createOrder(formData);

      if (result.success) {
        this.showThankYouMessage(result.idCommande);
      } else {
        this.showMessage(
          "Erreur : " +
            (result.error || "Erreur lors de la création de la commande"),
          "error"
        );
        this.hidePopup();
        confirmBtn.disabled = false;
        confirmBtn.textContent = "Confirmer la commande";
      }
    } catch (error) {
      console.error("Erreur lors de la commande:", error);
      this.showMessage("Une erreur est survenue. Veuillez réessayer.", "error");
      confirmBtn.disabled = false;
      confirmBtn.textContent = "Confirmer la commande";
    }
  }

  showThankYouMessage(orderId) {
    const thankYouHtml = `
            <div class="thank-you-popup">
                <h2>Merci pour votre commande !</h2>
                <p>Votre commande a été enregistrée avec succès.</p>
                <div class="order-number">Numéro de commande : ${orderId}</div>
                <p>Vous recevrez un email de confirmation sous peu.</p>
                <button class="btn-home">Retour à l'accueil</button>
            </div>
        `;

    this.popupContent.innerHTML = thankYouHtml;

    const homeBtn = this.popupContent.querySelector(".btn-home");
    if (homeBtn) {
      homeBtn.addEventListener("click", () => {
        window.location.href = "../../views/frontoffice/accueilConnecte.php";
      });
    }
  }

  showError(message, field) {
    const errorEl = document.querySelector(`[data-for="${field}"]`);
    if (errorEl) {
      errorEl.textContent = message;
      errorEl.style.display = "block";
    } else {
      console.error(message);
    }
  }

  clearError(field) {
    const errorEl = document.querySelector(`[data-for="${field}"]`);
    if (errorEl) {
      errorEl.textContent = "";
      errorEl.style.display = "none";
    }
  }

  clearAllErrors() {
    document.querySelectorAll(".error-message").forEach((el) => {
      el.textContent = "";
      el.style.display = "none";
    });
  }

  showMessage(message, type = "info") {
    if (type === "error") {
      alert(message);
    } else {
      alert(message);
    }
  }

  hidePopup() {
    this.confirmationPopup.style.display = "none";
  }

  getFieldName(errorKey) {
    const names = {
      adresse: "Adresse de livraison",
      "code-postal": "Code postal",
      ville: "Ville",
      "num-carte": "Numéro de carte",
      "nom-carte": "Nom sur la carte",
      "carte-date": "Date d'expiration",
      "cvv-input": "CVV",
    };
    return names[errorKey] || errorKey;
  }
}

// Initialisation au chargement de la page
document.addEventListener("DOMContentLoaded", () => {
  new PaymentPage();
});

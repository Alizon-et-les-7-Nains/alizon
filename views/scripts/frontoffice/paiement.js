// Classe principal pour gérer la page de paiement avec le nouveau design
class PaymentPage {
  constructor() {
    this.idAdresseFacturation = null;
    this.savedBillingAddress = null;
    this.init();
  }

  init() {
    this.setupReferences();
    this.setupEventListeners();
  }

  setupReferences() {
    this.factAddrCheckbox = document.getElementById("checkboxFactAddr");
    this.billingSection = document.getElementById("billingSection");
    this.confirmationPopup = document.getElementById("confirmationPopup");
    this.popupContent = document.getElementById("popupContent");
    this.payerButtons = document.querySelectorAll(".cta-button");
    this.cgvCheckbox = document.getElementById("cgvCheckbox");
  }

  setupEventListeners() {
    if (this.factAddrCheckbox && this.billingSection) {
      this.factAddrCheckbox.addEventListener("change", () => {
        this.billingSection.classList.toggle(
          "active",
          this.factAddrCheckbox.checked
        );
        if (!this.factAddrCheckbox.checked) this.idAdresseFacturation = null;
      });
    }

    // Ajoute l'événement paiement sur tous les boutons de paiement
    this.payerButtons.forEach((btn) => {
      btn.addEventListener("click", (e) => this.handlePayment(e));
    });

    // Ferme la popup si clic en dehors
    if (this.confirmationPopup) {
      this.confirmationPopup.addEventListener("click", (e) => {
        if (e.target === this.confirmationPopup) this.hidePopup();
      });
    }
  }

  // Configure le formatage automatique des champs
  setupAutoFormatting() {
    // Formatage automatique du numéro de carte
    const cardNumberInput = document.querySelector(".num-carte");
    if (cardNumberInput) {
      cardNumberInput.addEventListener("input", (e) => {
        this.formatCardNumber(e.target);
      });
    }

    // Formatage automatique de la date d'expiration
    const cardDateInput = document.querySelector(".carte-date");
    if (cardDateInput) {
      cardDateInput.addEventListener("input", (e) => {
        this.formatCardDate(e.target);
      });
    }

    // Validation en temps réel
    this.setupRealTimeValidation();
  }

  // Formate le numéro de carte en groupes de 4 chiffres
  formatCardNumber(input) {
    let value = input.value.replace(/\D/g, "");
    let formatted = "";

    for (let i = 0; i < value.length && i < 16; i++) {
      if (i > 0 && i % 4 === 0) {
        formatted += " ";
      }
      formatted += value[i];
    }

    input.value = formatted;

    // Validation en temps réel
    this.validateCardNumber(input);
  }

  // Formate la date au format MM/AA
  formatCardDate(input) {
    let value = input.value.replace(/\D/g, "");

    if (value.length >= 2) {
      input.value = value.substring(0, 2) + "/" + value.substring(2, 4);
    } else {
      input.value = value;
    }

    // Validation en temps réel
    this.validateCardDate(input);
  }

  // Configure la validation en temps réel
  setupRealTimeValidation() {
    // Validation du code postal
    const postalInput = document.querySelector(".code-postal-input");
    if (postalInput) {
      postalInput.addEventListener("blur", (e) => {
        this.validatePostalCode(e.target);
      });
    }

    // Validation du CVV
    const cvvInput = document.querySelector(".cvv-input");
    if (cvvInput) {
      cvvInput.addEventListener("input", (e) => {
        this.validateCVV(e.target);
      });
    }

    // Validation de tous les champs lors de la saisie
    const inputs = document.querySelectorAll(".form-section input");
    inputs.forEach((input) => {
      input.addEventListener("blur", () => {
        this.validateField(input);
      });
    });
  }

  // Valide un champ individuel
  validateField(input) {
    const value = input.value.trim();

    if (!value && input.required) {
      this.showFieldError(input, "Ce champ est obligatoire");
      return false;
    }

    // Validation spécifique par type de champ
    if (input.classList.contains("code-postal-input")) {
      return this.validatePostalCode(input);
    } else if (input.classList.contains("num-carte")) {
      return this.validateCardNumber(input);
    } else if (input.classList.contains("carte-date")) {
      return this.validateCardDate(input);
    } else if (input.classList.contains("cvv-input")) {
      return this.validateCVV(input);
    }

    this.clearFieldError(input);
    return true;
  }

  // Valide le code postal
  validatePostalCode(input) {
    const value = input.value.trim();

    if (!/^\d{5}$/.test(value)) {
      this.showFieldError(input, "Le code postal doit contenir 5 chiffres");
      return false;
    }

    this.clearFieldError(input);
    return true;
  }

  // Valide le numéro de carte
  validateCardNumber(input) {
    const cleanNumber = input.value.replace(/\s/g, "");

    if (cleanNumber.length !== 16) {
      this.showFieldError(
        input,
        "Le numéro de carte doit contenir 16 chiffres"
      );
      return false;
    }

    if (!/^\d+$/.test(cleanNumber)) {
      this.showFieldError(
        input,
        "Le numéro de carte ne doit contenir que des chiffres"
      );
      return false;
    }

    // Vérification Luhn
    if (!this.luhnCheck(cleanNumber)) {
      this.showFieldError(input, "Numéro de carte invalide");
      return false;
    }

    this.clearFieldError(input);
    return true;
  }

  // Vérification Luhn pour les cartes bancaires
  luhnCheck(cardNumber) {
    let sum = 0;
    let isEven = false;

    for (let i = cardNumber.length - 1; i >= 0; i--) {
      let digit = parseInt(cardNumber.charAt(i), 10);

      if (isEven) {
        digit *= 2;
        if (digit > 9) {
          digit -= 9;
        }
      }

      sum += digit;
      isEven = !isEven;
    }

    return sum % 10 === 0;
  }

  // Valide la date d'expiration
  validateCardDate(input) {
    const value = input.value.trim();

    if (!/^\d{2}\/\d{2}$/.test(value)) {
      this.showFieldError(input, "Format de date invalide (MM/AA)");
      return false;
    }

    const [month, year] = value.split("/").map(Number);
    const currentYear = new Date().getFullYear() % 100;
    const currentMonth = new Date().getMonth() + 1;

    if (month < 1 || month > 12) {
      this.showFieldError(input, "Mois invalide (01-12)");
      return false;
    }

    if (year < currentYear || (year === currentYear && month < currentMonth)) {
      this.showFieldError(input, "Carte expirée");
      return false;
    }

    this.clearFieldError(input);
    return true;
  }

  // Valide le CVV
  validateCVV(input) {
    const value = input.value.trim();

    if (!/^\d{3}$/.test(value)) {
      this.showFieldError(input, "Le CVV doit contenir 3 chiffres");
      return false;
    }

    this.clearFieldError(input);
    return true;
  }

  // Affiche une erreur pour un champ spécifique
  showFieldError(input, message) {
    const errorKey = this.getErrorKeyForInput(input);
    this.showError(message, errorKey);
    input.classList.add("error");
  }

  // Efface l'erreur d'un champ spécifique
  clearFieldError(input) {
    const errorKey = this.getErrorKeyForInput(input);
    this.clearError(errorKey);
    input.classList.remove("error");
  }

  // Obtient la clé d'erreur pour un input
  getErrorKeyForInput(input) {
    if (input.classList.contains("adresse-input")) return "adresse";
    if (input.classList.contains("code-postal-input")) return "code-postal";
    if (input.classList.contains("ville-input")) return "ville";
    if (input.classList.contains("num-carte")) return "num-carte";
    if (input.classList.contains("nom-carte")) return "nom-carte";
    if (input.classList.contains("carte-date")) return "carte-date";
    if (input.classList.contains("cvv-input")) return "cvv-input";
    return "";
  }

  // Valide les champs de l'adresse de facturation
  validateBillingFields() {
    let valid = true;

    this.clearError("adresse-fact");
    this.clearError("code-postal-fact");
    this.clearError("ville-fact");

    if (!adresse.value.trim()) {
      this.showError("Adresse de facturation requise", "adresse-fact");
      valid = false;
    }

    if (!codePostal.value.trim()) {
      this.showError("Code postal requis", "code-postal-fact");
      valid = false;
    } else if (!/^\d{5}$/.test(codePostal.value.trim())) {
      this.showError(
        "Le code postal doit contenir 5 chiffres",
        "code-postal-fact"
      );
      valid = false;
    }

    if (!ville.value.trim()) {
      this.showError("Ville requise", "ville-fact");
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

    const fields = [
      { selector: ".adresse-input", errorKey: "adresse", required: true },
      {
        selector: ".code-postal-input",
        errorKey: "code-postal",
        required: true,
        pattern: /^\d{5}$/,
      },
      { selector: ".ville-input", errorKey: "ville", required: true },
      {
        selector: ".num-carte",
        errorKey: "num-carte",
        required: true,
        pattern: /^(?:\d{4}\s?){3}\d{4}$/,
        validator: (value) => this.validateCardNumberValue(value),
      },
      { selector: ".nom-carte", errorKey: "nom-carte", required: true },
      {
        selector: ".carte-date",
        errorKey: "carte-date",
        required: true,
        pattern: /^\d{2}\/\d{2}$/,
        validator: (value) => this.validateCardDateValue(value),
      },
      {
        selector: ".cvv-input",
        errorKey: "cvv-input",
        required: true,
        pattern: /^\d{3}$/,
      },
    ];

    fields.forEach((field) => {
      const element = document.querySelector(field.selector);
      if (!element) return;

      const value = element.value.trim();

      if (field.required && !value) {
        this.showError(
          `${this.getFieldName(field.errorKey)} requis`,
          field.errorKey
        );
        isValid = false;
      } else if (field.pattern && !field.pattern.test(value)) {
        this.showError(this.getPatternMessage(field.errorKey), field.errorKey);
        isValid = false;
      } else if (field.validator) {
        const validationResult = field.validator(value);
        if (!validationResult.isValid) {
          this.showError(validationResult.message, field.errorKey);
          element.classList.add("error");
          isValid = false;
        } else {
          element.classList.remove("error");
        }
      }
    });

    if (!this.cgvCheckbox || !this.cgvCheckbox.checked) {
      this.showError("Veuillez accepter les conditions générales", "cgv");
      isValid = false;
    }

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

  // Valide le numéro de carte bancaire (version pour validator)
  validateCardNumberValue(cardNumber) {
    const cleanNumber = cardNumber.replace(/\s/g, "");

    if (cleanNumber.length !== 16) {
      return {
        isValid: false,
        message: "Le numéro de carte doit contenir 16 chiffres",
      };
    }

    if (!/^\d+$/.test(cleanNumber)) {
      return {
        isValid: false,
        message: "Le numéro de carte ne doit contenir que des chiffres",
      };
    }

    if (!this.luhnCheck(cleanNumber)) {
      return {
        isValid: false,
        message: "Numéro de carte invalide",
      };
    }

    return { isValid: true };
  }

  // Valide la date d'expiration (version pour validator)
  validateCardDateValue(date) {
    if (!/^\d{2}\/\d{2}$/.test(date)) {
      return { isValid: false, message: "Format de date invalide (MM/AA)" };
    }

    const [month, year] = date.split("/").map(Number);
    const currentYear = new Date().getFullYear() % 100;
    const currentMonth = new Date().getMonth() + 1;

    if (month < 1 || month > 12) {
      return { isValid: false, message: "Mois invalide (01-12)" };
    }

    if (year < currentYear || (year === currentYear && month < currentMonth)) {
      return { isValid: false, message: "Carte expirée" };
    }

    return { isValid: true };
  }

  // Chiffre les données sensibles de la carte bancaire
  encryptCardData(formData) {
    const encryptionKey = window.CLE_CHIFFREMENT || "default_key";

    if (typeof window.vignere !== "function") {
      console.error("Fonction de chiffrement non disponible");
      throw new Error("Erreur de sécurité: chiffrement non disponible");
    }

    // Chiffre les données sensibles
    return {
      ...formData,
      numeroCarte: window.vignere(
        formData.numCarte.replace(/\s+/g, ""),
        encryptionKey,
        1
      ),
      cvv: window.vignere(formData.cvv, encryptionKey, 1),
      nomCarte: window.vignere(formData.nomCarte, encryptionKey, 1),
    };
  }

  // Récupère les données du formulaire
  getFormData() {
    return {
      adresseLivraison: document.querySelector(".adresse-input").value.trim(),
      villeLivraison: document.querySelector(".ville-input").value.trim(),
      codePostal: document.querySelector(".code-postal-input").value.trim(),
      numCarte: document.querySelector(".num-carte").value.replace(/\s+/g, ""),
      nomCarte: document.querySelector(".nom-carte").value.trim(),
      dateExpiration: document.querySelector(".carte-date").value.trim(),
      cvv: document.querySelector(".cvv-input").value.trim(),
    };
  }

  // Affiche la popup de confirmation avec résumé de commande
  async showConfirmationPopup(formData, cart) {
    try {
      const encryptedData = this.encryptCardData(formData);

      // Construit le HTML du panier avec articles et totaux
      let cartHtml = '<div class="cart-summary">';
      let total = window.__PAYMENT_DATA__?.totals?.montantTTC || 0;

      cart.forEach((item) => {
        const itemTotal = item.prix * item.qty;

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
                
                <p><strong>Paiement :</strong> Carte Visa se terminant par ${formData.numCarte.slice(
                  -4
                )}</p>
            </div>
            
            <h3>Récapitulatif du panier</h3>
            ${cartHtml}
            
            <div class="total-section">
                <h3>Total : ${total.toFixed(2)}€</h3>
            </div>
            <form method="POST" action="../../../alizon.php">
              <div class="popup-buttons">
                  <button type = "button" class="btn-cancel">Annuler</button>
                  <button type = "submit" class="btn-confirm">Confirmer la commande</button>
              </div>
              <input type="hidden" name="adresseLivraison" value="${formData.adresseLivraison}">
              <input type="hidden" name="villeLivraison" value="${formData.villeLivraison}">
              <input type="hidden" name="codePostal" value="${formData.codePostal}">
              <input type="hidden" name="numeroCarte" value="${formData.numCarte}">
              <input type="hidden" name="nomCarte" value="${formData.nomCarte}">
              <input type="hidden" name="dateExpiration" value="${formData.dateExpiration}">
              <input type="hidden" name="cvv" value="${formData.cvv}">
              <input type="hidden" name="idAdresseFacturation" value="${this.idAdresseFacturation}">
            </form>
        `;

      this.popupContent.innerHTML = popupHtml;
      this.confirmationPopup.classList.add("show");

    this.setupPopupButtons(formData, cart);
  }

  setupPopupButtons(formData, cart) {
    const cancelBtn = this.popupContent.querySelector(".btn-cancel");

    if (cancelBtn) {
      cancelBtn.addEventListener("click", () => this.hidePopup());
    }

    if (confirmBtn) {
      confirmBtn.addEventListener("click", async () => {
        confirmBtn.disabled = true;
        confirmBtn.textContent = "Traitement en cours...";

        try {
          await this.submitOrder(encryptedData, cart, total);
        } catch (error) {
          console.error("Erreur lors de la soumission:", error);
          this.showMessage(
            "Erreur lors de la création de la commande: " + error.message,
            "error"
          );
          confirmBtn.disabled = false;
          confirmBtn.textContent = "Confirmer la commande";
        }
      });
    }
  }

  // Soumet la commande au serveur
  async submitOrder(encryptedData, cart, total) {
    try {
      // Vérifier si l'adresse de facturation est différente
      if (this.factAddrCheckbox && this.factAddrCheckbox.checked) {
        const billingData = {
          adresse: document.querySelector(".adresse-fact-input").value.trim(),
          codePostal: document
            .querySelector(".code-postal-fact-input")
            .value.trim(),
          ville: document.querySelector(".ville-fact-input").value.trim(),
        };

        // Sauvegarder l'adresse de facturation
        const billingResponse = await this.saveBillingAddress(billingData);
        if (billingResponse.success) {
          this.idAdresseFacturation = billingResponse.idAdresseFacturation;
        }
      }

      // Prépare les données chiffrées pour l'envoi
      const formData = new FormData();
      formData.append("action", "createOrder");
      formData.append("adresseLivraison", encryptedData.adresseLivraison);
      formData.append("villeLivraison", encryptedData.villeLivraison);
      formData.append("codePostal", encryptedData.codePostal);
      formData.append("numeroCarte", encryptedData.numeroCarte);
      formData.append("nomCarte", encryptedData.nomCarte);
      formData.append("dateExpiration", encryptedData.dateExpiration);
      formData.append("cvv", encryptedData.cvv);

      if (this.idAdresseFacturation) {
        formData.append("idAdresseFacturation", this.idAdresseFacturation);
      }

      // Envoie la requête au serveur
      const response = await fetch("pagePaiement.php", {
        method: "POST",
        body: formData,
      });

      const result = await response.json();

      if (result.success) {
        this.showThankYouMessage(result.idCommande);
      } else {
        throw new Error(result.error || "Erreur inconnue");
      }
    } catch (error) {
      throw error;
    }
  }

  // Sauvegarde l'adresse de facturation
  async saveBillingAddress(billingData) {
    const formData = new FormData();
    formData.append("action", "saveBillingAddress");
    formData.append("adresse", billingData.adresse);
    formData.append("codePostal", billingData.codePostal);
    formData.append("ville", billingData.ville);

    const response = await fetch("pagePaiement.php", {
      method: "POST",
      body: formData,
    });

    return await response.json();
  }

  // Affiche le message de remerciement après commande réussie
  showThankYouMessage(orderId) {
    const thankYouHtml = `
      <div class="thank-you-popup">
        <div class="success-icon">✓</div>
        <h2 class="popup-title">Paiement réussi !</h2>
        <p class="popup-text">Merci pour votre commande.</p>
        <p class="popup-text">Un email de confirmation vous a été envoyé.</p>
        <div class="order-number">#${orderId}</div>
        <button class="popup-button" onclick="window.location.href='../../views/frontoffice/accueilConnecte.php'">Retour à l'accueil</button>
      </div>
    `;

    this.popupContent.innerHTML = thankYouHtml;
  }

  // Affiche un message d'erreur associé à un champ
  showError(message, field) {
    const errorEl = document.querySelector(`[data-for="${field}"]`);
    if (errorEl) {
      errorEl.textContent = message;
      errorEl.style.display = "block";
    } else {
      alert(message);
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

  getPatternMessage(errorKey) {
    const messages = {
      "code-postal": "Le code postal doit contenir 5 chiffres",
      "num-carte":
        "Le numéro de carte doit contenir 16 chiffres (espaces autorisés)",
      "carte-date": "Format de date invalide (MM/AA)",
      "cvv-input": "Le CVV doit contenir 3 chiffres",
    };
    return messages[errorKey] || "Format invalide";
  }
}

document.addEventListener("DOMContentLoaded", () => {
  new PaymentPage();
});

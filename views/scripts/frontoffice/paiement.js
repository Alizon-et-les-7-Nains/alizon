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
    this.closePopupBtn = document.querySelector(".close-popup");
    this.payerButtons = document.querySelectorAll(".payer");
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

    this.payerButtons.forEach((btn) => {
      btn.addEventListener("click", (e) => this.handlePayment(e));
    });

    if (this.closePopupBtn) {
      this.closePopupBtn.addEventListener("click", () => this.hidePopup());
    }

    this.confirmationPopup.addEventListener("click", (e) => {
      if (e.target === this.confirmationPopup) this.hidePopup();
    });
  }

  validateBillingFields(adresse, codePostal, ville) {
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
      },
      { selector: ".nom-carte", errorKey: "nom-carte", required: true },
      {
        selector: ".carte-date",
        errorKey: "carte-date",
        required: true,
        pattern: /^\d{2}\/\d{2}$/,
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
    this.confirmationPopup.style.display = "flex";

    this.setupPopupButtons(formData, cart);
  }

  setupPopupButtons(formData, cart) {
    const cancelBtn = this.popupContent.querySelector(".btn-cancel");

    if (cancelBtn) {
      cancelBtn.addEventListener("click", () => this.hidePopup());
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
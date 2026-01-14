// Classe principal pour gérer la page de paiement et validation des formulaires
class PaymentPage {
  constructor() {
    this.idAdresseFacturation = null;
    this.savedBillingAddress = null;
    this.init();
  }

  // Initialise les références DOM et les écouteurs d'événements
  init() {
    this.setupReferences();
    this.setupEventListeners();
    this.setupAutoFormatting();
  }

  // Récupère les éléments DOM nécessaires
  setupReferences() {
    this.factAddrCheckbox = document.getElementById("checkboxFactAddr");
    this.billingSection = document.getElementById("billingSection");
    this.confirmationPopup = document.getElementById("confirmationPopup");
    this.popupContent = document.getElementById("popupContent");
    this.payerButtons = document.querySelectorAll(".payer");
    this.cgvCheckbox = document.getElementById("cgvCheckbox");
  }

  // Configure les écouteurs d'événements pour les interactions utilisateur
  setupEventListeners() {
    // Affiche/masque la section facturation si case cochée
    if (this.factAddrCheckbox && this.billingSection) {
      this.factAddrCheckbox.addEventListener("change", () => {
        this.billingSection.classList.toggle(
          "active",
          this.factAddrCheckbox.checked
        );
        if (!this.factAddrCheckbox.checked) this.idAdresseFacturation = null;
      });
    }

    // Ajoute l'événement paiement sur tous les boutons
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

    // Autocomplétion des villes basée sur le code postal
    const postalInputs = document.querySelectorAll(
      ".code-postal-input, .code-postal-fact-input"
    );
    postalInputs.forEach((input) => {
      input.addEventListener("input", (e) => {
        this.updateCitySuggestions(e.target);
      });
    });
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
  }

  // Formate la date au format MM/AA
  formatCardDate(input) {
    let value = input.value.replace(/\D/g, "");

    if (value.length >= 2) {
      input.value = value.substring(0, 2) + "/" + value.substring(2, 4);
    } else {
      input.value = value;
    }
  }

  // Remplit automatiquement la ville en fonction du code postal
  updateCitySuggestions(postalInput) {
    const postalCode = postalInput.value.trim();
    if (postalCode.length !== 5 || !/^\d{5}$/.test(postalCode)) return;

    // Localise le champ ville correspondant
    let cityInput;
    if (postalInput.classList.contains("code-postal-input")) {
      cityInput = document.querySelector(".ville-input");
    } else if (postalInput.classList.contains("code-postal-fact-input")) {
      cityInput = document.querySelector(".ville-fact-input");
    }

    if (!cityInput || !window.__PAYMENT_DATA__?.postals) return;

    const cities = window.__PAYMENT_DATA__.postals[postalCode];
    if (cities && cities.length > 0 && !cityInput.value) {
      cityInput.value = cities[0];
    }
  }

  // Valide les champs de l'adresse de facturation
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

  // Gère le processus de paiement principal
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

    // Vérifie que le stock est suffisant
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

  // Valide l'ensemble du formulaire de paiement
  validateForm() {
    let isValid = true;
    this.clearAllErrors();

    // Définit les règles de validation pour chaque champ
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
        validator: this.validateCardNumber.bind(this),
      },
      { selector: ".nom-carte", errorKey: "nom-carte", required: true },
      {
        selector: ".carte-date",
        errorKey: "carte-date",
        required: true,
        pattern: /^\d{2}\/\d{2}$/,
        validator: this.validateCardDate.bind(this),
      },
      {
        selector: ".cvv-input",
        errorKey: "cvv-input",
        required: true,
        pattern: /^\d{3}$/,
      },
    ];

    // Applique les validations à chaque champ
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
          isValid = false;
        }
      }
    });

    // Vérifie l'acceptation des CGV
    if (!this.cgvCheckbox || !this.cgvCheckbox.checked) {
      this.showError("Veuillez accepter les conditions générales", "cgv");
      isValid = false;
    }

    // Valide l'adresse de facturation si elle est activée
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

  // Valide le numéro de carte bancaire
  validateCardNumber(cardNumber) {
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

    return { isValid: true };
  }

  // Valide la date d'expiration de la carte
  validateCardDate(date) {
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
      let total = 0;

      cart.forEach((item) => {
        const itemTotal = item.prix * item.qty;
        total += itemTotal;

        cartHtml += `
          <div class="cart-item-summary">
            <img src="${item.img}" alt="${item.nom}" class="cart-item-image">
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

      // Construit le contenu de la popup de confirmation
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
        
        <div class="popup-buttons">
          <button type="button" class="btn-cancel">Annuler</button>
          <button type="button" class="btn-confirm">Confirmer la commande</button>
        </div>
      `;

      this.popupContent.innerHTML = popupHtml;
      this.confirmationPopup.style.display = "flex";

      this.setupPopupButtons(encryptedData, cart, total);
    } catch (error) {
      console.error("Erreur lors du chiffrement:", error);
      this.showMessage(
        "Erreur de sécurité lors du traitement du paiement",
        "error"
      );
    }
  }

  // Configure les boutons d'action de la popup
  setupPopupButtons(encryptedData, cart, total) {
    const cancelBtn = this.popupContent.querySelector(".btn-cancel");
    const confirmBtn = this.popupContent.querySelector(".btn-confirm");

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

  // Affiche le message de remerciement après commande réussie
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

  // Efface un message d'erreur spécifique
  clearError(field) {
    const errorEl = document.querySelector(`[data-for="${field}"]`);
    if (errorEl) {
      errorEl.textContent = "";
      errorEl.style.display = "none";
    }
  }

  // Efface tous les messages d'erreur
  clearAllErrors() {
    document.querySelectorAll(".error-message").forEach((el) => {
      el.textContent = "";
      el.style.display = "none";
    });
  }

  // Affiche un message générique à l'utilisateur
  showMessage(message, type = "info") {
    if (type === "error") {
      alert(message);
    } else {
      alert(message);
    }
  }

  // Masque la popup
  hidePopup() {
    this.confirmationPopup.style.display = "none";
  }

  // Retourne le libellé lisible du champ pour l'affichage
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

  // Retourne les messages d'erreur de validation spécifiques
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

// Initialise la page de paiement au chargement du DOM
document.addEventListener("DOMContentLoaded", () => {
  new PaymentPage();
});

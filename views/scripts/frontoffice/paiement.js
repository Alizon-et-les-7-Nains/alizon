class PaymentPage {
  constructor() {
    this.idAdresseFacturation = null;
    this.savedBillingAddress = null;
    this.init();
  }

  init() {
    this.setupReferences();
    this.setupEventListeners();
    this.setupProgressSteps();
  }

  setupReferences() {
    this.factAddrCheckbox = document.getElementById("checkboxFactAddr");
    this.billingSection = document.getElementById("billingSection");
    this.confirmationPopup = document.getElementById("confirmationPopup");
    this.popupContent = document.getElementById("popupContent");
    this.payerButtons = document.querySelectorAll(".cta-button, .payer");
    this.cgvCheckbox = document.getElementById("cgvCheckbox");
    this.progressSteps = document.querySelectorAll(".step");
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

    // Gestion de la fermeture du popup
    this.confirmationPopup.addEventListener("click", (e) => {
      if (e.target === this.confirmationPopup) this.hidePopup();
    });

    // Ajout des écouteurs pour les champs de carte
    this.setupCardInputListeners();
  }

  setupProgressSteps() {
    // Mettre à jour l'étape active
    const updateActiveStep = () => {
      this.progressSteps.forEach((step) => step.classList.remove("active"));
      this.progressSteps[0]?.classList.add("active"); // Première étape active par défaut
    };

    updateActiveStep();
  }

  setupCardInputListeners() {
    // Formatage du numéro de carte
    const cardNumberInput = document.querySelector(".num-carte");
    if (cardNumberInput) {
      cardNumberInput.addEventListener("input", (e) => {
        let value = e.target.value.replace(/\s+/g, "");
        value = value.replace(/\D/g, "");

        // Ajouter des espaces tous les 4 chiffres
        let formatted = "";
        for (let i = 0; i < value.length; i++) {
          if (i > 0 && i % 4 === 0) formatted += " ";
          formatted += value[i];
        }

        e.target.value = formatted.substring(0, 19); // 16 chiffres + 3 espaces

        // Vérification en temps réel de la carte Visa
        const cleanedValue = value.substring(0, 16);
        if (cleanedValue.length >= 13) {
          if (!this.isVisaCard(cleanedValue)) {
            this.showError("Numéro de carte Visa invalide", "num-carte");
          } else {
            this.clearError("num-carte");
          }
        }
      });
    }

    // Formatage de la date d'expiration
    const expDateInput = document.querySelector(".carte-date");
    if (expDateInput) {
      expDateInput.addEventListener("input", (e) => {
        let value = e.target.value.replace(/\D/g, "");
        if (value.length >= 2) {
          value = value.substring(0, 2) + "/" + value.substring(2, 4);
        }
        e.target.value = value.substring(0, 5);
      });
    }

    // Limiter le CVV à 3 chiffres
    const cvvInput = document.querySelector(".cvv-input");
    if (cvvInput) {
      cvvInput.addEventListener("input", (e) => {
        e.target.value = e.target.value.replace(/\D/g, "").substring(0, 3);
      });
    }
  }

  // === ALGORITHME DE LUHN POUR LA VALIDATION DES CARTES ===

  /**
   * Vérifie la validité d'un numéro de carte avec l'algorithme de Luhn
   *
   * Parametres :
   * cardNumber - Numéro de carte (sans espaces)
   * Retourne
   *  - true si valide, false sinon
   */
  cardVerification(cardNumber) {
    const cleaned = cardNumber.replace(/\s+/g, "");
    if (cleaned.length === 0 || !/^\d+$/.test(cleaned)) return false;

    const digits = cleaned
      .split("")
      .reverse()
      .map((d) => Number(d));

    for (let i = 1; i < digits.length; i += 2) {
      let n = digits[i] * 2;
      if (n > 9) n -= 9;
      digits[i] = n;
    }

    const sum = digits.reduce((a, b) => a + b, 0);
    return sum % 10 === 0;
  }

  /**
   * Vérifie si c'est une carte Visa valide
   * - Doit commencer par 4
   * - Doit être valide via l'algorithme de Luhn
   * - Doit avoir 13, 16 ou 19 chiffres (standards Visa)
   *
   * Parametres :
   * cardNumber - Numéro de carte (sans espaces)
   *
   * Retourne :
   * boolean - true si c'est une Visa valide, false sinon
   */
  isVisaCard(cardNumber) {
    const clean = cardNumber.replace(/\s+/g, "");

    // Vérifie que c'est une carte Visa (commence par 4)
    if (!clean.startsWith("4")) {
      return false;
    }

    // Vérifie la longueur standard Visa
    const isValidLength = [13, 16, 19].includes(clean.length);
    if (!isValidLength) {
      return false;
    }

    // Applique l'algorithme de Luhn
    return this.cardVerification(clean);
  }

  // =======================================================

  /**
   * Valide les champs de l'adresse de facturation
   *
   * Parametres :
   * adresse - Élément input de l'adresse
   * codePostal - Élément input du code postal
   * ville - Élément input de la ville
   *
   * Retourne :
   * boolean - true si tous les champs sont valides, false sinon
   */
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

  /**
   * Gère le processus de paiement
   * Valide le formulaire, vérifie le stock et affiche le popup de confirmation
   *
   * Parametres :
   * e - Événement du clic
   *
   * Retourne :
   * void
   */
  async handlePayment(e) {
    e.preventDefault();

    if (!this.validateForm()) {
      return;
    }

    const formData = this.getFormData();
    const cart = window.__PAYMENT_DATA__?.cart || [];

    if (cart.length === 0) {
      alert("Votre panier est vide");
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

    // Si adresse de facturation différente, sauvegarder d'abord
    if (this.factAddrCheckbox && this.factAddrCheckbox.checked) {
      const billingData = this.getBillingFormData();
      const saved = await this.saveBillingAddress(billingData);
      if (!saved.success) {
        alert("Erreur lors de la sauvegarde de l'adresse de facturation");
        return;
      }
      this.idAdresseFacturation = saved.idAdresseFacturation;
    }

    this.showConfirmationPopup(formData, cart);
  }

  /**
   * Sauvegarde l'adresse de facturation différente via une requête AJAX
   *
   * Parametres :
   * billingData - Objet contenant l'adresse, code postal et ville
   *
   * Retourne :
   * Promise<Object> - Réponse JSON contenant {success, idAdresseFacturation}
   */
  async saveBillingAddress(billingData) {
    try {
      const response = await fetch("pagePaiement.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded",
        },
        body: new URLSearchParams({
          action: "saveBillingAddress",
          ...billingData,
        }),
      });

      return await response.json();
    } catch (error) {
      console.error("Erreur:", error);
      return { success: false, error: error.message };
    }
  }

  /**
   * Valide tous les champs du formulaire de paiement
   * Vérifie les adresses, les données de carte et les conditions générales
   *
   * Retourne :
   * boolean - true si le formulaire est valide, false sinon
   */
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

    // === VALIDATION SPÉCIFIQUE POUR LA CARTE ===
    const numCarteInput = document.querySelector(".num-carte");
    if (numCarteInput && numCarteInput.value.trim()) {
      const cardNumber = numCarteInput.value.replace(/\s+/g, "");

      // Vérifie que c'est une Visa
      if (!cardNumber.startsWith("4")) {
        this.showError(
          "Ceci n'est pas une carte Visa (les cartes Visa commencent par 4)",
          "num-carte"
        );
        isValid = false;
      }

      // Vérifie la longueur
      if (![13, 16, 19].includes(cardNumber.length)) {
        this.showError(
          "Numéro de carte invalide (13, 16 ou 19 chiffres attendus)",
          "num-carte"
        );
        isValid = false;
      }

      // Vérifie l'algorithme de Luhn
      if (!this.cardVerification(cardNumber)) {
        this.showError(
          "Numéro de carte invalide selon l'algorithme de vérification",
          "num-carte"
        );
        isValid = false;
      }
    }

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

  /**
   * Récupère les données du formulaire de livraison et de paiement
   *
   * Retourne :
   * Object - Objet contenant tous les champs du formulaire nettoyés
   */
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

  /**
   * Récupère les données de l'adresse de facturation
   *
   * Retourne :
   * Object - Objet contenant l'adresse, code postal et ville de facturation
   */
  getBillingFormData() {
    return {
      adresse: document.querySelector(".adresse-fact-input").value.trim(),
      codePostal: document
        .querySelector(".code-postal-fact-input")
        .value.trim(),
      ville: document.querySelector(".ville-fact-input").value.trim(),
    };
  }

  /**
   * Affiche le popup de confirmation de commande avec récapitulatif
   * Construit le HTML du panier et affiche les détails de la commande
   *
   * Parametres :
   * formData - Données du formulaire de paiement
   * cart - Array des articles du panier
   *
   * Retourne :
   * void
   */
  showConfirmationPopup(formData, cart) {
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
            <div class="popup-header">
                <h2>Confirmation de commande</h2>
                <button class="close-popup">&times;</button>
            </div>
            <div class="order-info">
                <div class="info-section">
                    <h4>Adresse de livraison</h4>
                    <p>${formData.adresseLivraison}<br>
                    ${formData.codePostal} ${formData.villeLivraison}</p>
                </div>
                
                <div class="info-section">
                    <h4>Paiement</h4>
                    <p>Carte Visa se terminant par ${formData.numCarte.slice(
                      -4
                    )} ✓ Validée par algorithme de Luhn</p>
                </div>
            </div>
            
            <div class="popup-cart">
                <h3>Récapitulatif du panier</h3>
                ${cartHtml}
            </div>
            
            <div class="total-section">
                <div class="total-row">
                    <span>Sous-total</span>
                    <span>${
                      window.__PAYMENT_DATA__?.totals?.sousTotal?.toFixed(2) ||
                      "0.00"
                    } €</span>
                </div>
                <div class="total-row">
                    <span>Livraison</span>
                    <span>${
                      window.__PAYMENT_DATA__?.totals?.livraison?.toFixed(2) ||
                      "0.00"
                    } €</span>
                </div>
                <div class="total-row final">
                    <span>Total TTC</span>
                    <span><strong>${total.toFixed(2)} €</strong></span>
                </div>
            </div>
            
            <div class="popup-buttons">
                <button type="button" class="btn-cancel btn-secondary">Modifier</button>
                <form method="POST" action="../../../alizon.php" class="order-form">
                    <input type="hidden" name="action" value="createOrder">
                    <input type="hidden" name="adresseLivraison" value="${
                      formData.adresseLivraison
                    }">
                    <input type="hidden" name="villeLivraison" value="${
                      formData.villeLivraison
                    }">
                    <input type="hidden" name="codePostal" value="${
                      formData.codePostal
                    }">
                    <input type="hidden" name="numeroCarte" value="${
                      formData.numCarte
                    }">
                    <input type="hidden" name="nomCarte" value="${
                      formData.nomCarte
                    }">
                    <input type="hidden" name="dateExpiration" value="${
                      formData.dateExpiration
                    }">
                    <input type="hidden" name="cvv" value="${formData.cvv}">
                    <input type="hidden" name="idAdresseFacturation" value="${
                      this.idAdresseFacturation || ""
                    }">
                    <button type="submit" class="btn-confirm btn-primary">Confirmer et payer</button>
                </form>
            </div>
        `;

    this.popupContent.innerHTML = popupHtml;
    this.confirmationPopup.classList.add("show");

    // Ajouter écouteur pour le bouton de fermeture
    const closeBtn = this.popupContent.querySelector(".close-popup");
    if (closeBtn) {
      closeBtn.addEventListener("click", () => this.hidePopup());
    }

    // Ajouter écouteur pour le bouton Annuler/Modifier
    const cancelBtn = this.popupContent.querySelector(".btn-cancel");
    if (cancelBtn) {
      cancelBtn.addEventListener("click", () => this.hidePopup());
    }
  }

  /**
   * Affiche un message d'erreur pour un champ spécifique
   *
   * Parametres :
   * message - Message d'erreur à afficher
   * field - Clé du champ (attribut data-for)
   *
   * Retourne :
   * void
   */
  showError(message, field) {
    const errorEl = document.querySelector(`[data-for="${field}"]`);
    if (errorEl) {
      errorEl.textContent = message;
      errorEl.style.display = "block";
      errorEl.classList.add("show");
    } else {
      console.error(`Champ d'erreur non trouvé: ${field}`, message);
    }
  }

  /**
   * Efface le message d'erreur pour un champ spécifique
   *
   * Parametres :
   * field - Clé du champ (attribut data-for)
   *
   * Retourne :
   * void
   */
  clearError(field) {
    const errorEl = document.querySelector(`[data-for="${field}"]`);
    if (errorEl) {
      errorEl.textContent = "";
      errorEl.style.display = "none";
      errorEl.classList.remove("show");
    }
  }

  /**
   * Efface tous les messages d'erreur de la page
   *
   * Retourne :
   * void
   */
  clearAllErrors() {
    document.querySelectorAll(".error-message").forEach((el) => {
      el.textContent = "";
      el.style.display = "none";
      el.classList.remove("show");
    });
  }

  /**
   * Masque le popup de confirmation
   *
   * Retourne :
   * void
   */
  hidePopup() {
    this.confirmationPopup.classList.remove("show");
  }

  /**
   * Retourne le nom lisible d'un champ pour les messages d'erreur
   *
   * Parametres :
   * errorKey - Clé du champ
   *
   * Retourne :
   * string - Nom du champ lisible
   */
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

  /**
   * Retourne le message d'erreur de format pour un champ spécifique
   *
   * Parametres :
   * errorKey - Clé du champ
   *
   * Retourne :
   * string - Message d'erreur de format
   */
  getPatternMessage(errorKey) {
    const messages = {
      "code-postal": "Le code postal doit contenir 5 chiffres",
      "num-carte": "Le numéro de carte doit contenir 16 chiffres",
      "carte-date": "Format de date invalide (MM/AA)",
      "cvv-input": "Le CVV doit contenir 3 chiffres",
    };
    return messages[errorKey] || "Format invalide";
  }
}

// ============================================================================
// Initialisation de la page
// ============================================================================
document.addEventListener("DOMContentLoaded", () => {
  new PaymentPage();
});

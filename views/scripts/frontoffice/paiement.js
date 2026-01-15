class PaymentPage {
  constructor() {
    this.idAdresseFacturation = null;
    this.init();
  }

  init() {
    this.setupReferences();
    this.setupEventListeners();
    this.setupAutoFormatting();
  }

  setupReferences() {
    this.factAddrCheckbox = document.getElementById("checkboxFactAddr");
    this.billingSection = document.getElementById("billingSection");
    this.confirmationPopup = document.getElementById("confirmationPopup");
    this.popupContent = document.getElementById("popupContent");
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
        if (!this.factAddrCheckbox.checked) {
          this.idAdresseFacturation = null;
        }
      });
    }

    this.payerButtons.forEach((btn) => {
      btn.addEventListener("click", (e) => this.handlePayment(e));
    });

    if (this.confirmationPopup) {
      this.confirmationPopup.addEventListener("click", (e) => {
        if (e.target === this.confirmationPopup) this.hidePopup();
      });
    }
  }

  setupAutoFormatting() {
    const cardNumberInput = document.querySelector(".num-carte");
    if (cardNumberInput) {
      cardNumberInput.addEventListener("input", (e) =>
        this.formatCardNumber(e.target)
      );
    }

    const cardDateInput = document.querySelector(".carte-date");
    if (cardDateInput) {
      cardDateInput.addEventListener("input", (e) =>
        this.formatCardDate(e.target)
      );
    }

    const postalInputs = document.querySelectorAll(
      ".code-postal-input, .code-postal-fact-input"
    );
    postalInputs.forEach((input) => {
      input.addEventListener("input", (e) =>
        this.updateCitySuggestions(e.target)
      );
    });
  }

  formatCardNumber(input) {
    let value = input.value.replace(/\D/g, "").slice(0, 16);
    input.value = value.replace(/(.{4})/g, "$1 ").trim();
  }

  formatCardDate(input) {
    let value = input.value.replace(/\D/g, "").slice(0, 4);
    if (value.length >= 3) {
      input.value = value.slice(0, 2) + "/" + value.slice(2);
    } else {
      input.value = value;
    }
  }

  updateCitySuggestions(postalInput) {
    const postalCode = postalInput.value.trim();
    if (!/^\d{5}$/.test(postalCode)) return;

    let cityInput = postalInput.classList.contains("code-postal-input")
      ? document.querySelector(".ville-input")
      : document.querySelector(".ville-fact-input");

    if (!cityInput || cityInput.value) return;

    const cities = window.__PAYMENT_DATA__?.postals?.[postalCode];
    if (cities && cities.length > 0) {
      cityInput.value = cities[0];
    }
  }

  validateForm() {
    let isValid = true;
    this.clearAllErrors();

    const fields = [
      { sel: ".adresse-input", key: "adresse" },
      { sel: ".code-postal-input", key: "code-postal", regex: /^\d{5}$/ },
      { sel: ".ville-input", key: "ville" },
      {
        sel: ".num-carte",
        key: "num-carte",
        regex: /^(?:\d{4} ){3}\d{4}$/,
      },
      { sel: ".nom-carte", key: "nom-carte" },
      { sel: ".carte-date", key: "carte-date", regex: /^\d{2}\/\d{2}$/ },
      { sel: ".cvv-input", key: "cvv-input", regex: /^\d{3}$/ },
    ];

    fields.forEach((f) => {
      const el = document.querySelector(f.sel);
      if (!el || !el.value.trim()) {
        this.showError(`${this.getFieldName(f.key)} requis`, f.key);
        isValid = false;
      } else if (f.regex && !f.regex.test(el.value.trim())) {
        this.showError(this.getPatternMessage(f.key), f.key);
        isValid = false;
      }
    });

    if (!this.cgvCheckbox?.checked) {
      this.showError("Veuillez accepter les conditions générales", "cgv");
      isValid = false;
    }

    if (this.factAddrCheckbox?.checked) {
      isValid =
        this.validateBillingFields(
          document.querySelector(".adresse-fact-input"),
          document.querySelector(".code-postal-fact-input"),
          document.querySelector(".ville-fact-input")
        ) && isValid;
    }

    return isValid;
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

    if (!/^\d{5}$/.test(codePostal.value.trim())) {
      this.showError("Code postal invalide", "code-postal-fact");
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
    if (!this.validateForm()) return;

    const formDataJs = this.getFormData();
    const cart = window.__PAYMENT_DATA__?.cart || [];

    if (cart.length === 0) {
      alert("Votre panier est vide");
      return;
    }

    this.showConfirmationPopup(formDataJs, cart);
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
    let total = 0;
    const itemsHtml = cart
      .map((item) => {
        const t = item.prix * item.qty;
        total += t;
        return `
          <div>
            ${item.nom} × ${item.qty} = ${t.toFixed(2)}€
          </div>`;
      })
      .join("");

    this.popupContent.innerHTML = `
      <h2>Confirmation de commande</h2>
      <p>${formData.adresseLivraison}, ${formData.codePostal} ${
      formData.villeLivraison
    }</p>
      <p>Carte se terminant par ${formData.numCarte.slice(-4)}</p>
      ${itemsHtml}
      <h3>Total : ${total.toFixed(2)}€</h3>
      <button class="btn-cancel">Annuler</button>
      <button class="btn-confirm">Confirmer</button>
    `;

    this.confirmationPopup.style.display = "flex";

    this.popupContent
      .querySelector(".btn-cancel")
      .addEventListener("click", () => this.hidePopup());

    this.popupContent
      .querySelector(".btn-confirm")
      .addEventListener("click", () => this.submitOrder(formData));
  }

  async submitOrder(data) {
    const form = new FormData();
    form.append("action", "createOrder");
    Object.entries(data).forEach(([k, v]) => form.append(k, v));

    const res = await fetch("pagePaiement.php", {
      method: "POST",
      body: form,
    });

    const json = await res.json();
    if (!json.success) {
      alert(json.error);
      return;
    }

    this.showThankYouMessage(json.idCommande);
  }

  showThankYouMessage(id) {
    this.popupContent.innerHTML = `
      <h2>Commande validée</h2>
      <p>Numéro : ${id}</p>
      <button onclick="location.href='../../views/frontoffice/accueilConnecte.php'">
        Retour accueil
      </button>
    `;
  }

  hidePopup() {
    this.confirmationPopup.style.display = "none";
  }

  showError(msg, key) {
    const el = document.querySelector(`[data-for="${key}"]`);
    if (el) {
      el.textContent = msg;
      el.style.display = "block";
    }
  }

  clearError(key) {
    const el = document.querySelector(`[data-for="${key}"]`);
    if (el) el.style.display = "none";
  }

  clearAllErrors() {
    document
      .querySelectorAll(".error-message")
      .forEach((e) => (e.style.display = "none"));
  }

  getFieldName(k) {
    return {
      adresse: "Adresse",
      "code-postal": "Code postal",
      ville: "Ville",
      "num-carte": "Numéro de carte",
      "nom-carte": "Nom sur la carte",
      "carte-date": "Date d'expiration",
      "cvv-input": "CVV",
    }[k];
  }

  getPatternMessage(k) {
    return {
      "code-postal": "Code postal invalide",
      "num-carte": "Numéro de carte invalide",
      "carte-date": "Date invalide",
      "cvv-input": "CVV invalide",
    }[k];
  }
}

document.addEventListener("DOMContentLoaded", () => new PaymentPage());

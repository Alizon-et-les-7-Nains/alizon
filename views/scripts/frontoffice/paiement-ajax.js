// ============================================================================
// FICHIER PRINCIPAL - Initialisation et coordination
// ============================================================================

// API de paiement - Communication avec le backend
class PaymentAPI {
  static async updateQuantity(idProduit, delta) {
    try {
      const response = await fetch("", {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded",
        },
        body: `action=updateQty&idProduit=${encodeURIComponent(
          idProduit
        )}&delta=${delta}`,
      });

      if (!response.ok) {
        throw new Error(`Erreur HTTP: ${response.status}`);
      }

      const result = await response.json();
      if (result.success) {
        // Rechargement simple de la page pour voir les changements
        window.location.reload();
      } else {
        alert("Erreur lors de la mise à jour de la quantité");
      }
    } catch (error) {
      console.error("Erreur lors de la mise à jour:", error);
      alert("Erreur réseau lors de la mise à jour");
    }
  }

  static async removeItem(idProduit) {
    if (!confirm("Supprimer ce produit du panier ?")) {
      return;
    }

    try {
      const response = await fetch("", {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded",
        },
        body: `action=removeItem&idProduit=${encodeURIComponent(idProduit)}`,
      });

      if (!response.ok) {
        throw new Error(`Erreur HTTP: ${response.status}`);
      }

      const result = await response.json();
      if (result.success) {
        // Rechargement simple de la page pour voir les changements
        window.location.reload();
      } else {
        alert("Erreur lors de la suppression du produit");
      }
    } catch (error) {
      console.error("Erreur lors de la suppression:", error);
      alert("Erreur réseau lors de la suppression");
    }
  }

  static async createOrder(orderData) {
    try {
      const response = await fetch("", {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded",
        },
        body: `action=createOrder&${new URLSearchParams(orderData).toString()}`,
      });

      if (!response.ok) {
        throw new Error(`Erreur HTTP: ${response.status}`);
      }

      const result = await response.json();
      return result;
    } catch (error) {
      console.error("Erreur lors de la création de commande:", error);
      return { success: false, error: "Erreur réseau" };
    }
  }

  // Nouvelle méthode pour sauvegarder l'adresse de facturation
  static async saveBillingAddress(adresseData) {
    try {
      const response = await fetch("", {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded",
        },
        body: `action=saveBillingAddress&${new URLSearchParams(
          adresseData
        ).toString()}`,
      });

      if (!response.ok) {
        throw new Error(`Erreur HTTP: ${response.status}`);
      }

      const result = await response.json();
      return result;
    } catch (error) {
      console.error("Erreur lors de la sauvegarde de l'adresse:", error);
      return { success: false, error: "Erreur réseau" };
    }
  }
}

// Gestionnaire d'adresse de facturation
class BillingAddressManager {
  static init() {
    this.setupBillingAddressOverlay();
    this.setupCheckboxListener();
  }

  static setupBillingAddressOverlay() {
    // Créer l'overlay pour l'adresse de facturation
    const overlay = document.createElement("div");
    overlay.className = "addr-fact-overlay";
    overlay.style.display = "none";
    overlay.innerHTML = `
      <div class="addr-fact-content">
        <h2>Adresse de facturation</h2>
        <div class="form-group">
          <label>Adresse *</label>
          <input class="adresse-fact-input" type="text" placeholder="Adresse complète" required>
        </div>
        <div class="form-group">
          <label>Code Postal *</label>
          <input class="code-postal-fact-input" type="text" placeholder="Code postal" required>
        </div>
        <div class="form-group">
          <label>Ville *</label>
          <input class="ville-fact-input" type="text" placeholder="Ville" required>
        </div>
        <div class="button-group">
          <button id="validerAddrFact" class="btn-valider">Valider</button>
          <button id="closeAddrFact" class="btn-fermer">Annuler</button>
        </div>
      </div>
    `;

    document.body.appendChild(overlay);
    this.setupOverlayEvents(overlay);
  }

  static setupOverlayEvents(overlay) {
    const closeBtn = overlay.querySelector("#closeAddrFact");
    const validerBtn = overlay.querySelector("#validerAddrFact");
    const inputs = overlay.querySelectorAll("input");

    // Fermer l'overlay
    closeBtn.addEventListener("click", () => {
      overlay.style.display = "none";
      this.uncheckBillingCheckbox();
    });

    // Valider l'adresse
    validerBtn.addEventListener("click", async () => {
      const adresse = overlay.querySelector(".adresse-fact-input").value.trim();
      const codePostal = overlay
        .querySelector(".code-postal-fact-input")
        .value.trim();
      const ville = overlay.querySelector(".ville-fact-input").value.trim();

      // Validation
      if (!adresse || !codePostal || !ville) {
        alert("Veuillez remplir tous les champs obligatoires");
        return;
      }

      try {
        const result = await PaymentAPI.saveBillingAddress({
          adresse,
          codePostal,
          ville,
        });

        if (result.success) {
          alert("Adresse de facturation enregistrée avec succès !");
          overlay.style.display = "none";
          this.uncheckBillingCheckbox();

          // Stocker l'ID de l'adresse pour la commande
          if (result.idAdresse) {
            window.billingAddressId = result.idAdresse;
          }
        } else {
          alert("Erreur: " + (result.error || "Erreur inconnue"));
        }
      } catch (error) {
        alert("Erreur réseau lors de l'enregistrement");
      }
    });

    // Fermer en cliquant en dehors
    overlay.addEventListener("click", (e) => {
      if (e.target === overlay) {
        overlay.style.display = "none";
        this.uncheckBillingCheckbox();
      }
    });

    // Empêcher la fermeture en cliquant dans le contenu
    overlay
      .querySelector(".addr-fact-content")
      .addEventListener("click", (e) => {
        e.stopPropagation();
      });

    // Gestion de la touche Entrée
    inputs.forEach((input) => {
      input.addEventListener("keypress", (e) => {
        if (e.key === "Enter") {
          validerBtn.click();
        }
      });
    });
  }

  static setupCheckboxListener() {
    const checkbox = document.querySelector("#checkboxFactAddr");
    if (checkbox) {
      checkbox.addEventListener("change", (e) => {
        const isChecked = e.target.checked;
        const overlay = document.querySelector(".addr-fact-overlay");

        if (isChecked) {
          overlay.style.display = "flex";
          // Focus sur le premier champ
          overlay.querySelector("input").focus();
        } else {
          overlay.style.display = "none";
          // Supprimer l'ID d'adresse stocké
          delete window.billingAddressId;
        }
      });
    }
  }

  static uncheckBillingCheckbox() {
    const checkbox = document.querySelector("#checkboxFactAddr");
    if (checkbox) {
      checkbox.checked = false;
      delete window.billingAddressId;
    }
  }

  static getBillingAddressId() {
    return window.billingAddressId || null;
  }
}

// Fonction utilitaire pour afficher les popups
function showPopup(message, type = "success") {
  alert(message); // Version simple, vous pouvez remplacer par votre système de popup
}

// Fonction pour récupérer les données du formulaire de commande
function getOrderData() {
  const adresseLivraison =
    document.querySelector(".adresse-input")?.value || "";
  const villeLivraison = document.querySelector(".ville-input")?.value || "";
  const regionLivraison = document.querySelector(".region-input")?.value || "";
  const codePostal = document.querySelector(".code-postal-input")?.value || "";
  const numeroCarte = document.querySelector(".num-carte")?.value || "";
  const nomCarte = document.querySelector(".nom-carte")?.value || "";
  const dateExpiration = document.querySelector(".carte-date")?.value || "";
  const cvv = document.querySelector(".cvv-input")?.value || "";

  const orderData = {
    adresseLivraison,
    villeLivraison,
    regionLivraison,
    codePostal,
    numeroCarte,
    nomCarte,
    dateExpiration,
    cvv,
  };

  // Ajouter l'ID d'adresse de facturation si disponible
  const billingAddressId = BillingAddressManager.getBillingAddressId();
  if (billingAddressId) {
    orderData.idAdresseFacturation = billingAddressId;
  }

  return orderData;
}

// Fonction pour valider le formulaire avant paiement
function validateOrderForm() {
  const requiredFields = [
    { selector: ".adresse-input", name: "Adresse de livraison" },
    { selector: ".ville-input", name: "Ville" },
    { selector: ".code-postal-input", name: "Code postal" },
    { selector: ".num-carte", name: "Numéro de carte" },
    { selector: ".nom-carte", name: "Nom sur la carte" },
    { selector: ".carte-date", name: "Date d'expiration" },
    { selector: ".cvv-input", name: "CVV" },
  ];

  for (const field of requiredFields) {
    const input = document.querySelector(field.selector);
    if (!input || !input.value.trim()) {
      alert(`Le champ "${field.name}" est obligatoire`);
      input?.focus();
      return false;
    }
  }

  // Vérifier les conditions générales
  const conditionsCheckbox = document.querySelector('input[type="checkbox"]');
  if (conditionsCheckbox && !conditionsCheckbox.checked) {
    alert("Veuillez accepter les conditions générales de vente");
    return false;
  }

  return true;
}

// Initialisation au chargement de la page
document.addEventListener("DOMContentLoaded", function () {
  console.log("Initialisation de la page de paiement...");

  // Exposer l'API globalement
  window.PaymentAPI = PaymentAPI;

  // Initialiser la gestion des adresses de facturation
  BillingAddressManager.init();

  // Gestion des boutons +, -, supprimer du aside PHP
  document
    .querySelectorAll("button.plus, button.minus, button.delete")
    .forEach((btn) => {
      btn.addEventListener("click", function (e) {
        e.preventDefault();
        const id = this.getAttribute("data-id");
        if (!id) return;

        if (this.classList.contains("plus")) {
          PaymentAPI.updateQuantity(id, 1);
          console.log("API contacté, ajout produit");
        } else if (this.classList.contains("minus")) {
          PaymentAPI.updateQuantity(id, -1);
        } else if (this.classList.contains("delete")) {
          PaymentAPI.removeItem(id);
        }
      });
    });

  // Gestion des boutons payer
  document.querySelectorAll(".payer").forEach((btn) => {
    btn.addEventListener("click", async function (e) {
      e.preventDefault();

      // Validation du formulaire
      if (!validateOrderForm()) {
        return;
      }

      // Récupérer les données de commande
      const orderData = getOrderData();

      try {
        // Afficher un indicateur de chargement
        btn.disabled = true;
        btn.textContent = "Traitement en cours...";

        const result = await PaymentAPI.createOrder(orderData);

        if (result.success) {
          showPopup(
            `Commande créée avec succès ! Numéro de commande: ${result.idCommande}`
          );
          // Redirection vers la page de confirmation
          setTimeout(() => {
            window.location.href = `confirmation-commande.php?id=${result.idCommande}`;
          }, 2000);
        } else {
          showPopup(
            "Erreur lors de la création de la commande: " +
              (result.error || "Erreur inconnue"),
            "error"
          );
          btn.disabled = false;
          btn.textContent = "Payer";
        }
      } catch (error) {
        showPopup("Erreur réseau lors du paiement", "error");
        btn.disabled = false;
        btn.textContent = "Payer";
      }
    });
  });

  console.log("Page de paiement initialisée avec succès");
});

// Gestion des erreurs globales
window.addEventListener("error", function (e) {
  console.error("Erreur globale:", e.error);
});

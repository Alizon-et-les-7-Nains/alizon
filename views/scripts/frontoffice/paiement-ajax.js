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
}

// Initialisation au chargement de la page
document.addEventListener("DOMContentLoaded", function () {
  console.log("Initialisation de la page de paiement...");

  // Exposer l'API globalement
  window.PaymentAPI = PaymentAPI;

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
        } else if (this.classList.contains("minus")) {
          PaymentAPI.updateQuantity(id, -1);
        } else if (this.classList.contains("delete")) {
          PaymentAPI.removeItem(id);
        }
      });
    });

  // Gestion des boutons payer
  document.querySelectorAll(".payer").forEach((btn) => {
    btn.addEventListener("click", function (e) {
      e.preventDefault();
      showPopup("Confirmation de commande");
    });
  });

  console.log("Page de paiement initialisée avec succès");
});

// Gestion des erreurs globales
// window.addEventListener("error", function (e) {
//   console.error("Erreur globale:", e.error);
// });

console.log("Test");

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
      return result.success;
    } catch (error) {
      console.error("Erreur lors de la mise à jour:", error);
      return false;
    }
  }

  static async removeItem(idProduit) {
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
      return result.success;
    } catch (error) {
      console.error("Erreur lors de la suppression:", error);
      return false;
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

// Fonction pour rafraîchir le panier
async function refreshCart() {
  try {
    // Pour l'instant, on utilise le rechargement simple
    // Vous pourriez implémenter une version AJAX plus tard
    window.location.reload();
  } catch (error) {
    console.error("Erreur lors du rafraîchissement:", error);
    window.location.reload(); // Fallback
  }
}

// Initialisation au chargement de la page
document.addEventListener("DOMContentLoaded", function () {
  console.log("Initialisation de la page de paiement...");

  // Exposer l'API globalement
  window.PaymentAPI = PaymentAPI;

  // Vérifier la disponibilité des données
  if (!window.__PAYMENT_DATA__) {
    console.error("Données de paiement non disponibles");
    return;
  }

  // Initialiser l'aside si disponible
  if (typeof initAside !== "undefined" && document.getElementById("recap")) {
    const cartData = window.__PAYMENT_DATA__.cart || [];
    const asideHandle = initAside("#recap", cartData, refreshCart);

    // Stocker les références globales
    window.__ASIDE_HANDLE__ = asideHandle;
    window.paiementAside = asideHandle;

    console.log("Aside initialisé avec", cartData.length, "produits");
  }

  // Gestion des boutons payer
  document.querySelectorAll(".payer").forEach((btn) => {
    btn.addEventListener("click", function (e) {
      e.preventDefault();

      // Vérifier que l'API est disponible
      if (typeof showPopup === "undefined") {
        alert("Fonctionnalité de paiement non disponible");
        return;
      }

      showPopup("Confirmation de commande");
    });
  });

  console.log("Page de paiement initialisée avec succès");
});

// Gestion des erreurs globales
window.addEventListener("error", function (e) {
  console.error("Erreur globale:", e.error);
});

// ============================================================================
// FICHIER PRINCIPAL - Communication avec le backend
// ============================================================================

// Classe centralisant les appels vers le backend pour la gestion du paiement
class PaymentAPI {
  // Mise à jour de la quantité d'un produit (delta = +1 ou -1)
  static async updateQuantity(idProduit, delta) {
    try {
      // Requête envoyée en POST avec un encodage x-www-form-urlencoded
      const response = await fetch("", {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded",
        },
        body: `action=updateQty&idProduit=${encodeURIComponent(
          idProduit
        )}&delta=${delta}`,
      });

      // Vérifie que le statut HTTP est correct
      if (!response.ok) {
        throw new Error(`Erreur HTTP: ${response.status}`);
      }

      // Convertit la réponse en JSON
      const result = await response.json();

      // Si le backend renvoie success=true, on recharge la page pour afficher les nouvelles quantités
      if (result.success) {
        window.location.reload();
      } else {
        alert("Erreur lors de la mise à jour de la quantité");
      }
    } catch (error) {
      console.error("Erreur lors de la mise à jour:", error);
      alert("Erreur réseau lors de la mise à jour");
    }
  }

  // Suppression d'un produit du panier
  static async removeItem(idProduit) {
    // Fenêtre de confirmation
    if (!confirm("Supprimer ce produit du panier ?")) {
      return;
    }

    try {
      // Requête POST envoyée au serveur
      const response = await fetch("", {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded",
        },
        body: `action=removeItem&idProduit=${encodeURIComponent(idProduit)}`,
      });

      // Gestion des erreurs HTTP
      if (!response.ok) {
        throw new Error(`Erreur HTTP: ${response.status}`);
      }

      // Lecture du JSON
      const result = await response.json();

      // Si la suppression est réussie → rechargement de la page
      if (result.success) {
        window.location.reload();
      } else {
        alert("Erreur lors de la suppression du produit");
      }
    } catch (error) {
      console.error("Erreur lors de la suppression:", error);
      alert("Erreur réseau lors de la suppression");
    }
  }

  // Création de la commande complète
  static async createOrder(orderData) {
    try {
      console.log("Données de commande:", orderData);

      // FormData utilisé pour faciliter l'envoi des données
      const formData = new FormData();
      formData.append("action", "createOrder");

      // On ajoute toutes les données sauf "action" déjà définie
      Object.keys(orderData).forEach((key) => {
        if (key !== "action") {
          formData.append(key, orderData[key]);
        }
      });

      // Requête POST envoyant la commande
      const response = await fetch("", {
        method: "POST",
        body: formData,
      });

      // Vérification du statut
      if (!response.ok) {
        throw new Error(`Erreur HTTP: ${response.status}`);
      }

      // Récupère la réponse en texte brut pour diagnostiquer un JSON mal formé
      const responseText = await response.text();
      console.log("Réponse brute du serveur:", responseText);

      // Tentative de parsing JSON manuel
      let result;
      try {
        result = JSON.parse(responseText);
      } catch (parseError) {
        console.error("Erreur de parsing JSON:", parseError);
        console.error("Contenu reçu:", responseText.substring(0, 500));
        throw new Error("Le serveur n'a pas renvoyé du JSON valide");
      }

      console.log("Résultat de la commande:", result);
      return result;
    } catch (error) {
      console.error("Erreur lors de la création de commande:", error);
      return { success: false, error: error.message };
    }
  }
}

// ============================================================================
// Initialisation des événements au chargement de la page
// ============================================================================
document.addEventListener("DOMContentLoaded", function () {
  console.log("Initialisation de la page de paiement...");

  // Rendre PaymentAPI accessible globalement (ex : dans le HTML)
  window.PaymentAPI = PaymentAPI;

  // Sélectionne tous les boutons liés au panier (plus, minus, delete)
  document
    .querySelectorAll("button.plus, button.minus, button.delete")
    .forEach((btn) => {
      btn.addEventListener("click", function (e) {
        e.preventDefault();

        // Récupère l'ID produit présent dans data-id
        const id = this.getAttribute("data-id");
        if (!id) return;

        // Détection du type d'action via la classe
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

  console.log("Page de paiement initialisée avec succès");
});

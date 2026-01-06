// paiement-api.js - Communication avec le backend
class PaymentAPI {
  // Création d'une commande
  static async createOrder(orderData) {
    try {
      const formData = new FormData();
      formData.append("action", "createOrder");

      // Validation Luhn avant envoi
      const cardNum = orderData.numeroCarte.replace(/\s/g, "");
      if (!LuhnValidator.validate(cardNum)) {
        return {
          success: false,
          error: "Numéro de carte invalide (algorithme de Luhn)",
        };
      }

      if (!/^4/.test(cardNum)) {
        return {
          success: false,
          error: "Seules les cartes Visa sont acceptées",
        };
      }

      // Chiffrement des données sensibles
      const numeroCarteChiffre = window.vignere
        ? window.vignere(cardNum, window.CLE_CHIFFREMENT, 1)
        : cardNum;
      const cvvChiffre = window.vignere
        ? window.vignere(orderData.cvv, window.CLE_CHIFFREMENT, 1)
        : orderData.cvv;

      // Préparation des données
      const fields = [
        "adresseLivraison",
        "villeLivraison",
        "numeroCarte",
        "cvv",
        "nomCarte",
        "dateExpiration",
        "codePostal",
        "idAdresseFacturation",
      ];

      fields.forEach((key) => {
        if (orderData[key] !== undefined && orderData[key] !== null) {
          let value = orderData[key];
          if (key === "numeroCarte") value = numeroCarteChiffre;
          if (key === "cvv") value = cvvChiffre;
          formData.append(key, value);
        }
      });

      // Envoi de la requête
      const response = await fetch("", {
        method: "POST",
        body: formData,
      });

      if (!response.ok) {
        throw new Error(`Erreur HTTP: ${response.status}`);
      }

      const result = await response.json();
      return result;
    } catch (error) {
      console.error("Erreur lors de la création de commande:", error);
      return { success: false, error: error.message };
    }
  }

  // Sauvegarde de l'adresse de facturation
  static async saveBillingAddress(addressData) {
    try {
      const formData = new FormData();
      formData.append("action", "saveBillingAddress");
      formData.append("adresse", addressData.adresse);
      formData.append("codePostal", addressData.codePostal);
      formData.append("ville", addressData.ville);

      const response = await fetch("", {
        method: "POST",
        body: formData,
      });

      const result = await response.json();
      return result;
    } catch (error) {
      console.error("Erreur sauvegarde adresse:", error);
      return { success: false, error: error.message };
    }
  }
}

// Export pour utilisation globale
window.PaymentAPI = PaymentAPI;

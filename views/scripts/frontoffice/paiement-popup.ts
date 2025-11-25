// ============================================================================
// POPUP - Version avec base de données
// ============================================================================

import { CartItem } from "./paiement-types";

declare global {
  interface Window {
    __PAYMENT_DATA__?: {
      cart?: CartItem[];
      [key: string]: any;
    };
    PaymentAPI?: any;
    vignere?: (texte: string, cle: string, sens: number) => string;
    CLE_CHIFFREMENT?: string;
    idAdresseFacturation?: number | null;
  }
}

// Fonction helper pour le chiffrement avec vérification renforcée
const chiffrerAvecVignere = (texte: string, sens: number): string => {
  const cle = window.CLE_CHIFFREMENT || "?zu6j,xX{N12I]0r6C=v57IoASU~?6_y";

  if (typeof window.vignere === "function" && cle && cle.length > 0) {
    return window.vignere(texte, cle, sens);
  }

  console.warn(
    "Fonction vignere non disponible ou clé invalide, retour du texte en clair"
  );
  return texte;
};

// Fonction pour encoder les données de manière sécurisée
const encodeFormData = (data: any): string => {
  const formData = new URLSearchParams();
  formData.append("action", "createOrder");

  Object.keys(data).forEach((key) => {
    if (key !== "action") {
      // Encoder chaque valeur
      const value = String(data[key]);
      formData.append(key, value);
    }
  });

  return formData.toString();
};

export function showPopup(
  message: string,
  type: "error" | "success" | "info" = "info"
) {
  const overlay = document.createElement("div");
  overlay.className = `payment-overlay ${type}`;

  // Récupérer les valeurs des inputs
  const adresseInput = document.querySelector(
    "body.pagePaiement .adresse-input"
  ) as HTMLInputElement | null;
  const codePostalInput = document.querySelector(
    "body.pagePaiement .code-postal-input"
  ) as HTMLInputElement | null;
  const villeInput = document.querySelector(
    "body.pagePaiement .ville-input"
  ) as HTMLInputElement | null;
  const numCarteInput = document.querySelector(
    "body.pagePaiement .num-carte"
  ) as HTMLInputElement | null;
  const nomCarteInput = document.querySelector(
    "body.pagePaiement .nom-carte"
  ) as HTMLInputElement | null;
  const carteDateInput = document.querySelector(
    "body.pagePaiement .carte-date"
  ) as HTMLInputElement | null;
  const cvvInput = document.querySelector(
    "body.pagePaiement .cvv-input"
  ) as HTMLInputElement | null;

  const adresse = adresseInput?.value.trim() || "";
  const codePostal = codePostalInput?.value.trim() || "";
  const ville = villeInput?.value.trim() || "";
  const rawNumCarte = numCarteInput?.value.replace(/\s+/g, "") || "";
  const nomCarte = nomCarteInput?.value.trim() || "";
  const dateCarte = carteDateInput?.value.trim() || "";
  const rawCVV = cvvInput?.value.trim() || "";

  // Vérifier que tous les champs requis sont remplis
  if (
    !adresse ||
    !codePostal ||
    !ville ||
    !rawNumCarte ||
    !nomCarte ||
    !dateCarte ||
    !rawCVV
  ) {
    alert("Veuillez remplir tous les champs obligatoires");
    return;
  }

  // CHIFFREMENT DES DONNÉES SENSIBLES
  const numeroCarteChiffre = chiffrerAvecVignere(rawNumCarte, 1);
  const cvvChiffre = chiffrerAvecVignere(rawCVV, 1);

  const last4 = rawNumCarte.length >= 4 ? rawNumCarte.slice(-4) : rawNumCarte;

  // Déterminer la région à partir du code postal
  let region = "";
  if (codePostal.length >= 2) {
    const codeDept =
      codePostal.length === 5
        ? codePostal.slice(0, 2)
        : codePostal.padStart(2, "0");
    region = `Département ${codeDept}`;
  }

  const preCart = Array.isArray(window.__PAYMENT_DATA__?.cart)
    ? (window.__PAYMENT_DATA__!.cart as any[])
    : [];
  let cartItemsHtml = "";

  if (Array.isArray(preCart) && preCart.length > 0) {
    cartItemsHtml = preCart
      .map(
        (item: any) => `
      <div class="product">
        <img src="${item.img || "/images/default.png"}" alt="${item.nom}" />
        <p class="title">${item.nom}</p>
        <p><strong>Quantité :</strong> ${item.qty}</p>
        <p><strong>Prix total :</strong> ${(item.prix * item.qty).toFixed(
          2
        )} €</p>
      </div>`
      )
      .join("");
  } else {
    cartItemsHtml = `<p class="empty">Panier vide</p>`;
  }

  overlay.innerHTML = `
    <div class="payment-popup" role="dialog" aria-modal="true" data-type="${type}">
      <button class="close-popup" aria-label="Fermer">✕</button>
      <div class="order-summary">
        <h2>Récapitulatif de commande</h2>
        <div class="info">
          <p><strong>Adresse de livraison :</strong> ${adresse} ${codePostal} ${ville}</p>
          <p><strong>Payé avec :</strong> Carte Visa finissant par ${last4}</p>
        </div>
        <h3>Contenu du panier :</h3>
        <div class="cart">${cartItemsHtml}</div>
        <div class="actions">
          <button class="undo">Annuler</button>
          <button class="confirm">Confirmer ma commande</button>
        </div>
      </div>
    </div>
  `;

  document.body.appendChild(overlay);

  // Gestion des événements
  const closeBtn = overlay.querySelector(
    ".close-popup"
  ) as HTMLButtonElement | null;
  const undoBtn = overlay.querySelector(".undo") as HTMLButtonElement | null;
  const confirmBtn = overlay.querySelector(
    ".confirm"
  ) as HTMLButtonElement | null;

  let removeOverlay = () => {
    if (document.body.contains(overlay)) {
      document.body.removeChild(overlay);
    }
  };

  closeBtn?.addEventListener("click", removeOverlay);
  undoBtn?.addEventListener("click", removeOverlay);

  if (!confirmBtn) return;

  confirmBtn.addEventListener("click", async () => {
    const popup = overlay.querySelector(".payment-popup") as HTMLElement | null;
    if (!popup) return;

    // Afficher un indicateur de chargement
    const originalText = confirmBtn.textContent;
    confirmBtn.textContent = "Traitement en cours...";
    confirmBtn.disabled = true;

    try {
      // Vérifier que le chiffrement a fonctionné
      if (!window.vignere) {
        throw new Error("Système de sécurité non disponible");
      }

      // Vérifier que tous les champs requis sont remplis
      if (
        !adresse ||
        !codePostal ||
        !ville ||
        !rawNumCarte ||
        !nomCarte ||
        !dateCarte ||
        !rawCVV
      ) {
        throw new Error("Tous les champs sont obligatoires");
      }

      // Récupérer l'ID de l'adresse de facturation depuis window
      const idAdresseFact = window.idAdresseFacturation || null;

      // Préparer les données pour l'API
      const orderData: any = {
        adresseLivraison: adresse,
        villeLivraison: ville,
        regionLivraison: region,
        numeroCarte: numeroCarteChiffre,
        cvv: cvvChiffre,
        nomCarte: nomCarte,
        dateExpiration: dateCarte,
        codePostal: codePostal,
      };

      // Inclure l'ID de l'adresse de facturation si disponible
      if (idAdresseFact) {
        orderData.idAdresseFacturation = idAdresseFact;
        console.log(
          "Utilisation de l'adresse de facturation ID:",
          idAdresseFact
        );
      }

      // CORRECTION: Utiliser une approche différente pour éviter les problèmes d'encodage
      let result;

      // Essayer d'abord avec PaymentAPI
      if (
        window.PaymentAPI &&
        typeof window.PaymentAPI.createOrder === "function"
      ) {
        console.log("Utilisation de PaymentAPI");
        result = await window.PaymentAPI.createOrder(orderData);
      } else {
        // Fallback: appel fetch direct avec FormData
        console.log("Utilisation de fetch direct");

        // Utiliser FormData qui gère mieux l'encodage
        const formData = new FormData();
        formData.append("action", "createOrder");

        // Ajouter chaque champ
        Object.keys(orderData).forEach((key) => {
          formData.append(key, orderData[key]);
        });

        const response = await fetch("", {
          method: "POST",
          body: formData, // FormData gère automatiquement l'encodage
        });

        if (!response.ok) {
          throw new Error(`Erreur HTTP: ${response.status}`);
        }

        result = await response.json();
      }

      if (result.success) {
        // Afficher le message de succès
        popup.innerHTML = `
        <div class="thank-you">
          <h2>Merci de votre commande !</h2>
          <p>Votre commande a bien été enregistrée.</p>
          <p><strong>Numéro de commande :</strong> ${result.idCommande}</p>
          <button class="close-popup">Retour à l'accueil</button>
        </div>
      `;

        const innerClose = popup.querySelector(
          ".close-popup"
        ) as HTMLButtonElement | null;
        innerClose?.addEventListener("click", () => {
          // Rediriger vers la page d'accueil au lieu de recharger
          window.location.href = "../../views/frontoffice/accueilConnecte.php";
        });
      } else {
        throw new Error(
          result.error || "Erreur inconnue lors de la création de la commande"
        );
      }
    } catch (error) {
      console.error("Erreur complète:", error);

      // Message d'erreur plus précis
      let errorMessage = "Erreur lors de la création de la commande";
      if (error instanceof Error) {
        if (error.message.includes("SyntaxError")) {
          errorMessage = "Erreur de format des données. Veuillez réessayer.";
        } else if (error.message.includes("HTTP")) {
          errorMessage = "Erreur de communication avec le serveur.";
        } else {
          errorMessage = error.message;
        }
      }

      alert(errorMessage);

      // Réactiver le bouton
      confirmBtn.textContent = originalText;
      confirmBtn.disabled = false;
    }
  });

  // Fermer en cliquant en dehors du popup
  overlay.addEventListener("click", (e) => {
    if (e.target === overlay) {
      removeOverlay();
    }
  });

  // Fermer avec la touche Escape
  const handleEscape = (e: KeyboardEvent) => {
    if (e.key === "Escape") {
      removeOverlay();
      document.removeEventListener("keydown", handleEscape);
    }
  };
  document.addEventListener("keydown", handleEscape);

  // Nettoyer l'écouteur lors de la suppression
  const originalRemove = removeOverlay;
  removeOverlay = () => {
    document.removeEventListener("keydown", handleEscape);
    originalRemove();
  };
}

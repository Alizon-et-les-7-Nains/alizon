// ============================================================================
// POPUP - Version avec API unifiée
// ============================================================================

import { CartItem, OrderData } from "./paiement-types";

declare global {
  interface Window {
    __PAYMENT_DATA__?: {
      cart?: CartItem[];
      [key: string]: any;
    };
    __ASIDE_HANDLE__?: any;
    PaymentAPI?: any;
  }
}

export function showPopup(message: string) {
  const overlay = document.createElement("div");
  overlay.className = "payment-overlay";

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

  const adresse = adresseInput?.value.trim() || "";
  const codePostal = codePostalInput?.value.trim() || "";
  const ville = villeInput?.value.trim() || "";
  const rawNumCarte = numCarteInput?.value.replace(/\s+/g, "") || "";
  const last4 = rawNumCarte.length >= 4 ? rawNumCarte.slice(-4) : rawNumCarte;

  // Utiliser les données dynamiques de l'aside
  let currentCart: CartItem[] = [];

  if (
    window.__ASIDE_HANDLE__ &&
    typeof window.__ASIDE_HANDLE__.getCart === "function"
  ) {
    currentCart = window.__ASIDE_HANDLE__.getCart();
    console.log("Panier dynamique récupéré:", currentCart);
  } else if (Array.isArray(window.__PAYMENT_DATA__?.cart)) {
    currentCart = window.__PAYMENT_DATA__!.cart as CartItem[];
    console.log("Panier initial utilisé:", currentCart);
  }

  let cartItemsHtml = "";

  if (Array.isArray(currentCart) && currentCart.length > 0) {
    cartItemsHtml = currentCart
      .map(
        (item: CartItem) => `
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
    cartItemsHtml = `<p class="empty">Panier vide - Impossible de commander</p>`;
  }

  overlay.innerHTML = `
    <div class="payment-popup" role="dialog" aria-modal="true">
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
          <button class="confirm" ${currentCart.length === 0 ? "disabled" : ""}>
            ${
              currentCart.length === 0 ? "Panier vide" : "Confirmer ma commande"
            }
          </button>
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

  closeBtn?.addEventListener("click", () => overlay.remove());
  undoBtn?.addEventListener("click", () => overlay.remove());

  if (!confirmBtn || confirmBtn.disabled) return;

  confirmBtn.addEventListener("click", async () => {
    confirmBtn.disabled = true;
    const prevText = confirmBtn.textContent || "";
    confirmBtn.textContent = "Traitement en cours...";

    try {
      console.log("Création commande via PaymentAPI...");

      if (!window.PaymentAPI) {
        throw new Error("PaymentAPI non disponible");
      }

      const orderData = {
        adresseLivraison: adresse,
        villeLivraison: ville,
        regionLivraison: codePostal,
        numeroCarte: rawNumCarte,
      };

      const result = await window.PaymentAPI.createOrder(orderData);

      if (result && result.success) {
        console.log("Commande créée:", result.idCommande);
        const popup = overlay.querySelector(".payment-popup") as HTMLElement;
        if (!popup) {
          overlay.remove();
          return;
        }

        popup.innerHTML = `
          <div class="thank-you">
            <h2>Merci de votre commande !</h2>
            <p>Votre commande n°${result.idCommande} a bien été enregistrée.</p>
            <button class="close-popup">Fermer</button>
          </div>
        `;

        const innerClose = popup.querySelector(
          ".close-popup"
        ) as HTMLButtonElement | null;
        innerClose?.addEventListener("click", () => {
          overlay.remove();
          window.location.href = "/accueil";
        });
      } else {
        throw new Error(
          result?.error || "Erreur lors de la création de la commande"
        );
      }
    } catch (error) {
      console.error("Erreur création commande:", error);
      alert(
        "Erreur lors de la création de la commande: " + (error as Error).message
      );
      confirmBtn.disabled = false;
      confirmBtn.textContent = prevText;
    }
  });
}

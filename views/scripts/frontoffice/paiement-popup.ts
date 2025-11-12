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
  // FIX: utiliser le même nom de classe que les autres inputs (num-carte-input)
  const numCarteInput = document.querySelector(
    "body.pagePaiement .num-carte-input"
  ) as HTMLInputElement | null;

  const adresse = adresseInput?.value.trim() || "";
  const codePostal = codePostalInput?.value.trim() || "";
  const ville = villeInput?.value.trim() || "";
  const rawNumCarte = numCarteInput?.value.replace(/\s+/g, "") || "";
  const last4 = rawNumCarte.length >= 4 ? rawNumCarte.slice(-4) : rawNumCarte;

  // Utiliser les données du panier depuis window.__PAYMENT_DATA__
  const preCart = Array.isArray(window.__PAYMENT_DATA__?.cart)
    ? (window.__PAYMENT_DATA__!.cart as CartItem[])
    : [];
  let cartItemsHtml = "";

  if (Array.isArray(preCart) && preCart.length > 0) {
    cartItemsHtml = preCart
      .map(
        (item: CartItem) => `
      <div class="product">
        <img src="${item.img || "/images/default.png"}" alt="${item.title}" />
        <p class="title">${item.title}</p>
        <p><strong>Quantité :</strong> ${item.qty}</p>
        <p><strong>Prix total :</strong> ${(item.price * item.qty).toFixed(
          2
        )} €</p>
      </div>`
      )
      .join("");
  } else {
    cartItemsHtml = `<p class="empty">Panier vide</p>`;
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

  closeBtn?.addEventListener("click", () => overlay.remove());
  undoBtn?.addEventListener("click", () => overlay.remove());

  if (!confirmBtn) return;

  confirmBtn.addEventListener("click", async () => {
    confirmBtn.disabled = true;
    const prevText = confirmBtn.textContent || "";
    confirmBtn.textContent = "Traitement en cours...";

    try {
      console.log("Création commande via AJAX direct...");

      // Envoi au même endpoint (vide = même URL) est possible, mais vérifier la réponse
      const response = await fetch("", {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded",
        },
        body: `action=createOrder&adresseLivraison=${encodeURIComponent(
          adresse
        )}&villeLivraison=${encodeURIComponent(
          ville
        )}&regionLivraison=${encodeURIComponent(
          codePostal
        )}&numeroCarte=${encodeURIComponent(rawNumCarte)}`,
      });

      if (!response.ok) {
        throw new Error(`Erreur réseau (${response.status})`);
      }

      const result = await response.json();

      if (result && result.success) {
        console.log("✅ Commande créée en BD:", result.idCommande);
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
          // Redirection après commande
          window.location.href = "/accueil";
        });
      } else {
        throw new Error(result?.error || "Erreur inconnue");
      }
    } catch (error) {
      console.error("Erreur création commande:", error);
      alert(
        "Erreur lors de la création de la commande: " + (error as Error).message
      );
      confirmBtn.disabled = false;
      confirmBtn.textContent = prevText || "Confirmer ma commande";
    }
  });
}

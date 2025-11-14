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
  const cvvInput = document.querySelector(
    "body.pagePaiement .cvv-input"
  ) as HTMLInputElement | null;

  const adresse = adresseInput?.value.trim() || "";
  const codePostal = codePostalInput?.value.trim() || "";
  const ville = villeInput?.value.trim() || "";
  const rawNumCarte = numCarteInput?.value.replace(/\s+/g, "") || "";
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
    ? (window.__PAYMENT_DATA__!.cart as CartItem[])
    : [];
  let cartItemsHtml = "";

  if (Array.isArray(preCart) && preCart.length > 0) {
    cartItemsHtml = preCart
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
    const popup = overlay.querySelector(".payment-popup") as HTMLElement | null;
    if (!popup) return;

    // Afficher un indicateur de chargement
    confirmBtn.textContent = "Traitement en cours...";
    confirmBtn.disabled = true;

    try {
      // Appeler l'API pour créer la commande
      // Encrypt sensitive fields (numero carte + cvv) on client-side using vignere
      // Chiffrement.js exposes `vignere` and `cle` in global scope
      let encryptedNumero = rawNumCarte;
      let encryptedCvv = "";
      try {
        const vg = (window as any).vignere;
        const key = (window as any).cle;
        if (
          typeof vg === "function" &&
          typeof key === "string" &&
          rawNumCarte
        ) {
          encryptedNumero = vg(rawNumCarte, key, 1);
        }
        const rawCvv = cvvInput?.value.replace(/\s+/g, "") || "";
        if (typeof vg === "function" && typeof key === "string" && rawCvv) {
          encryptedCvv = vg(rawCvv, key, 1);
        }
      } catch (err) {
        // If encryption fails, fall back to sending raw values (not ideal but avoids blocking)
        console.error("Erreur chiffrement client:", err);
      }

      const orderData = {
        adresseLivraison: adresse,
        villeLivraison: ville,
        regionLivraison: region,
        numeroCarte: encryptedNumero,
        cvv: encryptedCvv,
        codePostal: codePostal,
      };

      const result = await window.PaymentAPI.createOrder(orderData);

      if (result.success) {
        // Afficher le message de succès
        popup.innerHTML = `
          <div class="thank-you">
            <h2>Merci de votre commande !</h2>
            <p>Votre commande a bien été enregistrée.</p>
            <p><strong>Numéro de commande :</strong> ${result.idCommande}</p>
            <button class="close-popup">Fermer</button>
          </div>
        `;

        const innerClose = popup.querySelector(
          ".close-popup"
        ) as HTMLButtonElement | null;
        innerClose?.addEventListener("click", () => {
          // Recharger la page pour vider le panier
          window.location.reload();
        });
      } else {
        // Afficher l'erreur
        alert(
          "Erreur lors de la création de la commande: " +
            (result.error || "Erreur inconnue")
        );
        confirmBtn.textContent = "Confirmer ma commande";
        confirmBtn.disabled = false;
      }
    } catch (error) {
      console.error("Erreur:", error);
      alert("Erreur réseau lors de la création de la commande");
      confirmBtn.textContent = "Confirmer ma commande";
      confirmBtn.disabled = false;
    }
  });
}

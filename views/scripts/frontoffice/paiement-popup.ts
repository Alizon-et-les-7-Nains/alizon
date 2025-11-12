// ============================================================================
// POPUP
// ============================================================================

export function showPopup(message: string) {
  const overlay = document.createElement("div");
  overlay.className = "payment-overlay";
  // récupérer les valeurs actuelles des inputs saisies par l'utilisateur
  const adresse =
    (
      document.querySelector(
        "body.pagePaiement .adresse-input"
      ) as HTMLInputElement | null
    )?.value.trim() || "";
  const codePostal =
    (
      document.querySelector(
        "body.pagePaiement .code-postal-input"
      ) as HTMLInputElement | null
    )?.value.trim() || "";
  const ville =
    (
      document.querySelector(
        "body.pagePaiement .ville-input"
      ) as HTMLInputElement | null
    )?.value.trim() || "";
  const rawNumCarte =
    (
      document.querySelector(
        "body.pagePaiement .num-carte"
      ) as HTMLInputElement | null
    )?.value.replace(/\s+/g, "") || "";
  const last4 = rawNumCarte.length >= 4 ? rawNumCarte.slice(-4) : rawNumCarte;

  // construire le HTML du panier : prioriser les données préchargées, sinon lecture depuis le DOM
  const preCart = (window as any).__PAYMENT_DATA__?.cart;
  let cartItemsHtml = "";
  if (Array.isArray(preCart) && preCart.length > 0) {
    cartItemsHtml = preCart
      .map(
        (it: any) => `
      <div class="product">
        <img src="${it.img || ""}" alt="${it.title || ""}" />
        <p class="title">${it.title || ""}</p>
        <p><strong>Quantité :</strong> ${it.qty || 0}</p>
        <p><strong>Prix :</strong> ${(it.price || 0).toFixed(2)} €</p>
      </div>`
      )
      .join("");
  } else {
    // fallback : lire les éléments déjà rendus sur la page
    const prods = Array.from(document.querySelectorAll(".produit"));
    if (prods.length > 0) {
      cartItemsHtml = prods
        .map((p) => {
          const title =
            (
              p.querySelector(".titre") as HTMLElement | null
            )?.textContent?.trim() || "";
          const qty =
            (
              p.querySelector(".qty") as HTMLElement | null
            )?.textContent?.trim() || "";
          const prix =
            (
              p.querySelector(".prix") as HTMLElement | null
            )?.textContent?.trim() || "";
          const img =
            (p.querySelector("img") as HTMLImageElement | null)?.src || "";
          return `
        <div class="product">
          <img src="${img}" alt="${title}" />
          <p class="title">${title}</p>
          <p><strong>Quantité :</strong> ${qty}</p>
          <p><strong>Prix unité :</strong> ${prix}</p>
        </div>`;
        })
        .join("");
    }
  }

  overlay.innerHTML = `
    <div class="payment-popup" role="dialog" aria-modal="true">
      <button class="close-popup" aria-label="Fermer" style="position:absolute;right:12px;top:12px">✕</button>
      <div class="order-summary">
        <h2>Récapitulatif de commande</h2>

        <div class="info">
          <p><strong>Adresse de livraison :</strong> ${adresse} ${codePostal} ${ville}</p>
          <p><strong>Payé avec :</strong> Carte Visa finissant par ${last4}</p>
        </div>

        <h3>Contenu du panier :</h3>

        <div class="cart">
          ${cartItemsHtml || `<p class="empty">Panier vide</p>`}
        </div>

      <div class="actions">
        <button class="undo">Annuler</button>
        <button class="confirm">Confirmer ma commande</button>
        </div>
      </div>
    </div>
  `;
  document.body.appendChild(overlay);

  // Fermer via la croix
  const closeBtn = overlay.querySelector(".close-popup") as HTMLElement | null;
  if (closeBtn) {
    closeBtn.addEventListener("click", () => overlay.remove());
  }

  // Annuler : ferme simplement l'overlay
  const undoBtn = overlay.querySelector(".undo") as HTMLElement | null;
  if (undoBtn) {
    undoBtn.addEventListener("click", () => overlay.remove());
  }

  // Confirmer : afficher un message "Merci"
  const confirmBtn = overlay.querySelector(".confirm") as HTMLElement | null;
  if (confirmBtn) {
    confirmBtn.addEventListener("click", () => {
      const popup = overlay.querySelector(
        ".payment-popup"
      ) as HTMLElement | null;
      if (!popup) return;
      popup.innerHTML = `
        <div class="thank-you" >
          <h2>Merci de votre commande !</h2>
          <p>Votre commande a bien été enregistrée.</p>
          <button class="close-popup" style="margin-top:12px">Fermer</button>
        </div>
      `;
      const newClose = popup.querySelector(
        ".close-popup"
      ) as HTMLElement | null;
      if (newClose) newClose.addEventListener("click", () => overlay.remove());
    });
  }
}
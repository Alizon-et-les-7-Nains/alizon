import { CartItem } from "./paiement-types";

declare global {
  interface Window {
    __PAYMENT_DATA__?: {
      cart?: CartItem[];
      [key: string]: any;
    };
    vignere?: (texte: string, cle: string, sens: number) => string;
    CLE_CHIFFREMENT?: string;
  }
}

export function showPopup(
  message: string,
  type: "error" | "success" | "info" = "info",
  options?: {
    cart?: CartItem[];
    address?: string;
    city?: string;
    postalCode?: string;
    cardLast4?: string;
    onConfirm?: () => Promise<void>;
    onCancel?: () => void;
  }
) {
  // Création de l'overlay
  const overlay = document.createElement("div");
  overlay.className = `payment-overlay ${type}`;

  // Récupération du panier depuis les options ou depuis les données globales
  const cartItems = options?.cart || window.__PAYMENT_DATA__?.cart || [];

  // Construction du HTML pour les articles du panier
  let cartItemsHtml = "";

  if (cartItems.length > 0) {
    cartItemsHtml = cartItems
      .map(
        (item: any) => `
      <div class="cart-item-summary">
        <img src="${item.img || "/images/default.png"}" alt="${
          item.nom
        }" class="cart-item-image" />
        <div class="cart-item-info">
          <div class="cart-item-name">${item.nom}</div>
          <div class="cart-item-details">
            <div>Quantité: ${item.qty} × ${item.prix.toFixed(2)}€</div>
            <div>Total: ${(item.prix * item.qty).toFixed(2)}€</div>
          </div>
        </div>
      </div>
    `
      )
      .join("");
  } else {
    cartItemsHtml = `<p class="empty-cart-message">Panier vide</p>`;
  }

  // Calcul du total
  const total = cartItems.reduce(
    (sum: number, item: any) => sum + item.prix * item.qty,
    0
  );

  // Construction du contenu HTML de la popup
  overlay.innerHTML = `
    <div class="order-summary" role="dialog" aria-modal="true" data-type="${type}">
      <h2>Récapitulatif de commande</h2>
      
      <div class="info">
        ${
          options?.address
            ? `
          <p><strong>Adresse de livraison :</strong><br>
          ${options.address}<br>
          ${options.postalCode} ${options.city}</p>
        `
            : ""
        }
        
        ${
          options?.cardLast4
            ? `
          <p><strong>Payé avec :</strong> Carte Visa finissant par ${options.cardLast4}</p>
        `
            : ""
        }
      </div>
      
      <h3>Contenu du panier :</h3>
      <div class="scrollable-cart">
        ${cartItemsHtml}
      </div>
      
      <div class="total-section" style="
        text-align: right;
        margin: 20px 0;
        padding-top: 15px;
        border-top: 2px solid #252b56;
      ">
        <h3 style="margin: 0;">Total : ${total.toFixed(2)}€</h3>
      </div>
      
      <div class="actions">
        <button class="undo" aria-label="Annuler la commande">Annuler</button>
        <button class="confirm" aria-label="Confirmer la commande">Confirmer ma commande</button>
      </div>
    </div>
  `;

  // Ajout de styles pour le panier scrollable
  const style = document.createElement("style");
  style.textContent = `
    .scrollable-cart {
      max-height: 300px;
      overflow-y: auto;
      padding: 15px;
      background: #f9f9f9;
      border-radius: 8px;
      margin: 15px 0;
    }
    
    .cart-item-summary {
      display: flex;
      align-items: center;
      margin-bottom: 15px;
      padding-bottom: 15px;
      border-bottom: 1px solid #eee;
    }
    
    .cart-item-summary:last-child {
      border-bottom: none;
      margin-bottom: 0;
      padding-bottom: 0;
    }
    
    .cart-item-image {
      width: 60px;
      height: 60px;
      object-fit: cover;
      border-radius: 4px;
      margin-right: 15px;
    }
    
    .cart-item-info {
      flex: 1;
    }
    
    .cart-item-name {
      font-weight: 500;
      margin-bottom: 5px;
      color: #252b56;
    }
    
    .cart-item-details {
      font-size: 0.9rem;
      color: #666;
    }
    
    .empty-cart-message {
      text-align: center;
      color: #999;
      padding: 20px;
    }
  `;
  document.head.appendChild(style);

  // Ajout au DOM
  document.body.appendChild(overlay);

  // Récupération des boutons
  const undoBtn = overlay.querySelector(".undo") as HTMLButtonElement | null;
  const confirmBtn = overlay.querySelector(
    ".confirm"
  ) as HTMLButtonElement | null;

  // Fonction de suppression de l'overlay
  const removeOverlay = () => {
    if (document.body.contains(overlay)) {
      document.body.removeChild(overlay);
      document.head.removeChild(style);
    }
  };

  // Gestion des événements
  undoBtn?.addEventListener("click", () => {
    if (options?.onCancel) {
      options.onCancel();
    }
    removeOverlay();
  });

  confirmBtn?.addEventListener("click", async () => {
    if (options?.onConfirm) {
      try {
        confirmBtn.disabled = true;
        confirmBtn.textContent = "Traitement en cours...";
        await options.onConfirm();
      } catch (error) {
        console.error("Erreur lors de la confirmation:", error);
        confirmBtn.disabled = false;
        confirmBtn.textContent = "Confirmer ma commande";
      }
    }
  });

  // Fermeture au clic sur l'overlay
  overlay.addEventListener("click", (e) => {
    if (e.target === overlay) {
      if (options?.onCancel) {
        options.onCancel();
      }
      removeOverlay();
    }
  });

  // Fermeture avec la touche Escape
  const handleEscape = (e: KeyboardEvent) => {
    if (e.key === "Escape") {
      if (options?.onCancel) {
        options.onCancel();
      }
      removeOverlay();
      document.removeEventListener("keydown", handleEscape);
    }
  };
  document.addEventListener("keydown", handleEscape);

  // Nettoyage des event listeners
  const originalRemove = removeOverlay;
  const newRemove = () => {
    document.removeEventListener("keydown", handleEscape);
    originalRemove();
  };

  return {
    close: newRemove,
  };
}

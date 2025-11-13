// ============================================================================
// ASIDE (RECAP) - Version avec API unifiée
// ============================================================================

import { CartItem, AsideHandle } from "./paiement-types";

declare global {
  interface Window {
    PaymentAPI?: any;
  }
}

export function initAside(
  recapSelector: string,
  cart: CartItem[],
  onCartUpdate: () => void
): AsideHandle {
  const container = document.querySelector(recapSelector) as HTMLElement;

  if (!container) {
    console.error("Container aside non trouvé:", recapSelector);
    throw new Error("Container aside non trouvé");
  }

  console.log("Données cart reçues dans aside:", cart);

  function normalizeCartItem(item: any): CartItem {
    return {
      id: String(item.id || item.idProduit || ""),
      nom: String(item.nom || "Produit sans nom"),
      prix: Number(item.prix || 0),
      qty: Number(item.qty || item.quantiteProduit || 0),
      img: item.img || item.URL || "../../public/images/default.png",
    };
  }

  let normalizedCart: CartItem[] = cart.map(normalizeCartItem);

  async function updateQty(id: string, delta: number) {
    try {
      console.log("Mise à jour quantité via API:", id, delta);

      if (!window.PaymentAPI) {
        throw new Error("PaymentAPI non disponible");
      }

      const success = await window.PaymentAPI.updateQuantity(id, delta);

      if (success) {
        console.log("Quantité mise à jour - Mise à jour dynamique");

        const itemIndex = normalizedCart.findIndex((item) => item.id === id);
        if (itemIndex !== -1) {
          const newQty = normalizedCart[itemIndex].qty + delta;

          if (newQty <= 0) {
            normalizedCart.splice(itemIndex, 1);
          } else {
            normalizedCart[itemIndex].qty = newQty;
          }

          render();
          onCartUpdate();
        }
      } else {
        alert("Erreur lors de la mise à jour de la quantité");
      }
    } catch (error) {
      console.error("Erreur:", error);
      alert("Erreur: " + (error as Error).message);
    }
  }

  async function removeItem(id: string) {
    try {
      if (!confirm("Supprimer ce produit du panier ?")) {
        return;
      }

      console.log("Suppression produit via API:", id);

      if (!window.PaymentAPI) {
        throw new Error("PaymentAPI non disponible");
      }

      const success = await window.PaymentAPI.removeItem(id);

      if (success) {
        console.log("Produit supprimé - Mise à jour dynamique");
        normalizedCart = normalizedCart.filter((item) => item.id !== id);
        render();
        onCartUpdate();
      } else {
        alert("Erreur lors de la suppression du produit");
      }
    } catch (error) {
      console.error("Erreur:", error);
      alert("Erreur: " + (error as Error).message);
    }
  }

  function attachListeners() {
    container
      .querySelectorAll<HTMLButtonElement>("button.plus")
      .forEach((btn) => {
        btn.addEventListener("click", (ev) => {
          ev.preventDefault();
          const id = btn.getAttribute("data-id");
          if (id) updateQty(id, 1);
        });
      });

    container
      .querySelectorAll<HTMLButtonElement>("button.minus")
      .forEach((btn) => {
        btn.addEventListener("click", (ev) => {
          ev.preventDefault();
          const id = btn.getAttribute("data-id");
          if (id) updateQty(id, -1);
        });
      });

    container
      .querySelectorAll<HTMLButtonElement>("button.delete")
      .forEach((btn) => {
        btn.addEventListener("click", (ev) => {
          ev.preventDefault();
          const id = btn.getAttribute("data-id");
          if (id) removeItem(id);
        });
      });
  }

  function render() {
    console.log("Rendu du aside avec", normalizedCart.length, "produits");

    if (normalizedCart.length === 0) {
      container.innerHTML = '<div class="empty-cart">Panier vide</div>';
      return;
    }

    let html = "";
    normalizedCart.forEach((item) => {
      const total = item.prix * item.qty;
      html += `
        <div class="produit" data-id="${item.id}">
          <img src="${item.img}" alt="${item.nom}" />
          <div class="infos">
            <p class="titre">${item.nom}</p>
            <p class="prix">${total.toFixed(2)}€</p>
            <div class="gestQte">
              <div class="qte">
                <button class="minus" data-id="${item.id}">-</button>
                <span class="qty" data-id="${item.id}">${item.qty}</span>
                <button class="plus" data-id="${item.id}">+</button>
              </div>
              <button class="delete" data-id="${item.id}">
                <img src="../../public/images/bin.svg" alt="Supprimer">
              </button>
            </div>
          </div>
        </div>
      `;
    });

    container.innerHTML = html;
    attachListeners();
  }

  render();

  return {
    update(newCart: CartItem[]) {
      console.log("Mise à jour aside avec nouveau panier:", newCart);
      normalizedCart = newCart.map(normalizeCartItem);
      render();
      onCartUpdate();
    },
    getElement() {
      return container;
    },
    getCart() {
      return [...normalizedCart];
    },
  };
}

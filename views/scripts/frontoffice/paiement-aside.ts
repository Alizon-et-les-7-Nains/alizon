// ============================================================================
// ASIDE (RECAP)
// ============================================================================

import { CartItem, AsideHandle } from "./paiement-types";

export function initAside(
  recapSelector: string,
  cart: CartItem[],
  updateQty: (id: string, delta: number) => void,
  removeItem: (id: string) => void
): AsideHandle {
  const container = document.querySelector(recapSelector) as HTMLElement | null;

  function attachListeners() {
    if (!container) return;
    container.querySelectorAll("button.plus").forEach((btn) => {
      btn.addEventListener("click", (ev) => {
        const id = (ev.currentTarget as HTMLElement).getAttribute("data-id")!;
        updateQty(id, 1);
      });
    });
    container.querySelectorAll("button.minus").forEach((btn) => {
      btn.addEventListener("click", (ev) => {
        const id = (ev.currentTarget as HTMLElement).getAttribute("data-id")!;
        updateQty(id, -1);
      });
    });
    container.querySelectorAll("button.delete").forEach((btn) => {
      btn.addEventListener("click", (ev) => {
        const id = (ev.currentTarget as HTMLElement).getAttribute("data-id")!;
        removeItem(id);
      });
    });
  }

  function render() {
    if (!container) return;
    container.innerHTML = "";
    if (cart.length === 0) {
      const empty = document.createElement("div");
      empty.className = "empty-cart";
      empty.textContent = "Panier vide";
      container.appendChild(empty);
      return;
    }
    cart.forEach((item) => {
      const row = document.createElement("div");
      row.className = "produit";
      row.setAttribute("data-id", item.id);
      row.innerHTML = `
        <img src="${item.img}" alt="${item.title}" class="mini" />
        <div class="infos">
          <p class="titre">${item.title}</p>
          <p class="prix">${(item.price * item.qty).toFixed(2)} €</p>
          <div class="gestQte">
            <div class="qte">
              <button class="minus" data-id="${item.id}">-</button>
              <span class="qty" data-id="${item.id}">${item.qty}</span>
              <button class="plus" data-id="${item.id}">+</button>
            </div>
            <button class="delete" data-id="${item.id}">
              <img src="../../public/images/bin.svg" alt="">
            </button>
          </div>
        </div>
      `;
      container.appendChild(row);
    });
    attachListeners();
  }

  render();

  return {
    update(newCart: CartItem[]) {
      cart = newCart;
      render();
    },
    getElement() {
      return container;
    },
  };
}
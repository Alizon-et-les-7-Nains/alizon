// ============================================================================
// ASIDE (RECAP) - Version avec base de données
// ============================================================================

import { CartItem, AsideHandle } from "./paiement-types";

export function initAside(
  recapSelector: string,
  cart: CartItem[],
  onCartUpdate: () => void
): AsideHandle {
  const container = document.querySelector(recapSelector) as HTMLElement | null;

  async function updateQty(id: string, delta: number) {
    try {
      console.log("Envoi direct AJAX - Mise à jour quantité:", id, delta);

      const response = await fetch("", {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded",
        },
        body: `action=updateQty&idProduit=${id}&delta=${delta}`,
      });

      const result = await response.json();

      if (result.success) {
        console.log("BD mise à jour - Rechargement");
        window.location.reload();
      } else {
        alert("Erreur: " + (result.error || "Erreur inconnue"));
      }
    } catch (error) {
      console.error("Erreur:", error);
      alert("Erreur réseau");
    }
  }

  async function removeItem(id: string) {
    try {
      console.log("Envoi direct AJAX - Suppression:", id);

      const response = await fetch("", {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded",
        },
        body: `action=removeItem&idProduit=${id}`,
      });

      const result = await response.json();

      if (result.success) {
        console.log("Produit supprimé - Rechargement");
        window.location.reload();
      } else {
        alert("Erreur: " + (result.error || "Erreur inconnue"));
      }
    } catch (error) {
      console.error("Erreur:", error);
      alert("Erreur réseau");
    }
  }

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
        if (confirm("Supprimer ce produit du panier ?")) {
          removeItem(id);
        }
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
        <img src="${item.img || "/images/default.png"}" alt="${
        item.nom
      }" class="mini" />
        <div class="infos">
          <p class="titre">${item.nom}</p>
          <p class="prix">${(item.prix * item.qty).toFixed(2)} €</p>
          <div class="gestQte">
            <div class="qte">
              <button class="minus" data-id="${item.id}">-</button>
              <span class="qty" data-id="${item.id}">${item.qty}</span>
              <button class="plus" data-id="${item.id}">+</button>
            </div>
            <button class="delete" data-id="${item.id}">
              <img src="/public/images/bin.svg" alt="Supprimer">
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
      console.log("Mise à jour de l'aside avec nouveau panier");
      cart = newCart;
      render();
    },
    getElement() {
      return container;
    },
  };
}

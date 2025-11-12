// ============================================================================
// CART MANAGEMENT
// ============================================================================

import { CartItem } from "./paiement-types";

export function updateQty(cart: CartItem[], id: string, delta: number): CartItem[] {
  return cart
    .map((it) => {
      if (it.id === id) {
        const next = Math.max(0, it.qty + delta);
        return { ...it, qty: next };
      }
      return it;
    })
    .filter((it) => it.qty > 0);
}

export function removeItem(cart: CartItem[], id: string): CartItem[] {
  return cart.filter((it) => it.id !== id);
}

export function getDefaultCart(): CartItem[] {
  // Utiliser chemins d'images absolus depuis la racine publique pour éviter les 404
  return [
    {
      id: "rillettes",
      title: "Lot de rillettes bretonne",
      price: 29.99,
      qty: 1,
      img: "/images/rillettes.png",
    },
    {
      id: "confiture",
      title: "Confiture artisanale",
      price: 6.5,
      qty: 2,
      img: "/images/jam.png",
    },
  ];
}

export function getCartFromData(): CartItem[] {
  if (
    (window as any).__PAYMENT_DATA__ &&
    Array.isArray((window as any).__PAYMENT_DATA__.cart)
  ) {
    return (window as any).__PAYMENT_DATA__.cart as CartItem[];
  }
  return getDefaultCart();
}
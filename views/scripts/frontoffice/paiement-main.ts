// ============================================================================
// MAIN PAIEMENT LOGIC
// ============================================================================

import { CartItem, Inputs, Maps } from "./paiement-types";
import { validateAll } from "./paiement-validation";
import { setupAutocomplete } from "./paiement-autocomplete";
import { showPopup } from "./paiement-popup";
import { initAside } from "./paiement-aside";
import { updateQty, removeItem, getCartFromData } from "./paiement-cart";

if (document.body.classList.contains("pagePaiement")) {
  // Éléments
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

  const payerButtons = Array.from(
    document.querySelectorAll("body.pagePaiement .payer")
  ) as HTMLButtonElement[];

  const recapEl = document.getElementById("recap");

  const departments = new Map<string, string>(); // code -> nom du département
  const citiesByCode = new Map<string, Set<string>>();
  const allCities = new Set<string>();
  const postals = new Map<string, Set<string>>();
  const selectedDepartment = { value: null as string | null };

  const preloaded = (window as any).__PAYMENT_DATA__ || {};
  if (preloaded.departments) {
    Object.keys(preloaded.departments).forEach((code) => {
      departments.set(code, preloaded.departments[code]);
    });
  }
  if (preloaded.citiesByCode) {
    Object.keys(preloaded.citiesByCode).forEach((code) => {
      const set = new Set<string>(preloaded.citiesByCode[code]);
      citiesByCode.set(code, set);
      preloaded.citiesByCode[code].forEach((c: string) => allCities.add(c));
    });
  }
  if (preloaded.postals) {
    Object.keys(preloaded.postals).forEach((postal) => {
      const set = new Set<string>(preloaded.postals[postal]);
      postals.set(postal, set);
      preloaded.postals[postal].forEach((c: string) => allCities.add(c));
    });
  }

  // Setup autocomplete handlers
  setupAutocomplete({
    codePostalInput,
    villeInput,
    maps: { departments, citiesByCode, postals, allCities },
    selectedDepartment,
  });

  // Initialiser le panier
  let cart: CartItem[] = getCartFromData();

  function handleUpdateQty(id: string, delta: number) {
    cart = updateQty(cart, id, delta);
    aside.update(cart);

    const span = document.querySelector(`.qty[data-id="${id}"]`);
    const prod = document.querySelector(`.produit[data-id="${id}"]`);
    const item = cart.find((c) => c.id === id);
    if (span && item) {
      span.textContent = String(item.qty);
    }
    if (!item && prod && prod.parentElement) {
      prod.parentElement.removeChild(prod);
    }
  }

  function handleRemoveItem(id: string) {
    cart = removeItem(cart, id);
    aside.update(cart);
    const prod = document.querySelector(`.produit[data-id="${id}"]`);
    if (prod && prod.parentElement) prod.parentElement.removeChild(prod);
  }

  // initialiser l'aside (récapitulatif) et synchroniser avec le panier
  const aside = initAside("#recap", cart, handleUpdateQty, handleRemoveItem);

  // attacher les gestionnaires aux boutons du récap rendu côté serveur (PHP) - now handled in initAside

  payerButtons.forEach((btn) => {
    btn.addEventListener("click", (e) => {
      e.preventDefault();
      const ok = validateAll({
        inputs: {
          adresseInput,
          codePostalInput,
          villeInput,
          numCarteInput,
          nomCarteInput,
          carteDateInput,
          cvvInput,
          recapEl,
        },
        departments,
        postals,
        cart,
        selectedDepartment,
      });
      if (ok) {
        showPopup("Paiement réussi");
      } else {
        const first = document.querySelector(".invalid");
        if (first)
          (first as HTMLElement).scrollIntoView({
            behavior: "smooth",
            block: "center",
          });
      }
    });
  });

  // Masquer les suggestions si on clique en dehors
  document.addEventListener("click", (ev) => {
    const target = ev.target as HTMLElement | null;
    document.querySelectorAll(".suggestions").forEach((s) => {
      if (!target) return;
      const parent = (s.parentElement as HTMLElement) || null;
      if (!parent) return;
      if (target === parent || parent.contains(target)) {
        // click à l'intérieur -> rien
      } else {
        (s as HTMLElement).style.display = "none";
      }
    });
  });
}

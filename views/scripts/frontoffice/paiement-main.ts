// ============================================================================
// MAIN PAIEMENT LOGIC
// ============================================================================

import { CartItem, Inputs, Maps } from "./paiement-types";
import { validateAll } from "./paiement-validation";
import { setupAutocomplete } from "./paiement-autocomplete";
import { showPopup } from "./paiement-popup";

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

  // Initialiser le panier à partir des données injectées côté PHP
  let cart: CartItem[] = [];
  if (preloaded.cart && Array.isArray(preloaded.cart)) {
    cart = preloaded.cart.map((it: any) => ({
      id: String(it.id ?? it.idProduit ?? ""),
      nom: String(it.nom ?? "Produit sans nom"),
      prix: Number(it.prix ?? 0),
      qty: Number(it.qty ?? it.quantiteProduit ?? 0),
      img: it.img ?? it.URL ?? "../../public/images/default.png",
    }));
  }

  // Setup autocomplete handlers
  setupAutocomplete({
    codePostalInput,
    villeInput,
    maps: { departments, citiesByCode, postals, allCities },
    selectedDepartment,
  });

  // Gestion des boutons payer
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

  const addrFactOverlay = document.createElement("div");
  addrFactOverlay.className = "addr-fact-overlay";
  addrFactOverlay.innerHTML = `
  <div class="addr-fact-content">
    <h2>Adresse de facturation</h2>
    <label>Adresse
      <input class="adresse-fact-input" type="text" placeholder="Adresse" aria-label="Adresse de facturation">
    </label>
    <label>Code Postal
      <input class="code-postal-fact-input" type="text" placeholder="Code Postal" aria-label="Code Postal de facturation">
    </label>
    <label>Ville
      <input class="ville-fact-input" type="text" placeholder="Ville" aria-label="Ville de facturation">
    </label>
    <div class="button-group">
      <button id="validerAddrFact" class="btn-valider">Valider</button>
      <button id="closeAddrFact" class="btn-fermer">Fermer</button>
    </div>
  </div>
`;

  // Fonction pour valider l'adresse de facturation
  const validerAddrFactBtn = addrFactOverlay.querySelector(
    "#validerAddrFact"
  ) as HTMLButtonElement | null;

  validerAddrFactBtn?.addEventListener("click", async () => {
    const adresseFactInput = addrFactOverlay.querySelector(
      ".adresse-fact-input"
    ) as HTMLInputElement;
    const codePostalFactInput = addrFactOverlay.querySelector(
      ".code-postal-fact-input"
    ) as HTMLInputElement;
    const villeFactInput = addrFactOverlay.querySelector(
      ".ville-fact-input"
    ) as HTMLInputElement;

    // Validation basique
    if (
      !adresseFactInput.value.trim() ||
      !codePostalFactInput.value.trim() ||
      !villeFactInput.value.trim()
    ) {
      showPopup(
        "Veuillez remplir tous les champs de l'adresse de facturation",
        "error"
      );
      return;
    }

    try {
      // Enregistrer l'adresse de facturation dans la base de données
      const response = await fetch("", {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded",
        },
        body: new URLSearchParams({
          action: "saveBillingAddress",
          adresse: adresseFactInput.value.trim(),
          codePostal: codePostalFactInput.value.trim(),
          ville: villeFactInput.value.trim(),
        }),
      });

      const result = await response.json();

      if (result.success) {
        showPopup("Adresse de facturation enregistrée avec succès");
        document.body.removeChild(addrFactOverlay);

        // Décocher la checkbox après validation
        const factAdresseCheckbox = document.querySelector(
          "#checkboxFactAddr"
        ) as HTMLInputElement;
        if (factAdresseCheckbox) {
          factAdresseCheckbox.checked = false;
        }
      } else {
        showPopup("Erreur lors de l'enregistrement: " + result.error, "error");
      }
    } catch (error) {
      showPopup("Erreur réseau: " + error, "error");
    }
  });

  const closeAddrFactBtn = addrFactOverlay.querySelector(
    "#closeAddrFact"
  ) as HTMLButtonElement | null;

  closeAddrFactBtn?.addEventListener("click", () => {
    document.body.removeChild(addrFactOverlay);
  });

  const factAdresseInput = document.querySelector(
    "#checkboxFactAddr"
  ) as HTMLInputElement;

  factAdresseInput?.addEventListener("change", (e) => {
    const isChecked = (e.target as HTMLInputElement).checked;

    if (isChecked) {
      document.body.appendChild(addrFactOverlay);

      // Focus sur le premier champ
      const firstInput = addrFactOverlay.querySelector(
        "input"
      ) as HTMLInputElement;
      if (firstInput) {
        firstInput.focus();
      }
    } else {
      if (document.body.contains(addrFactOverlay)) {
        document.body.removeChild(addrFactOverlay);
      }
    }
  });
}

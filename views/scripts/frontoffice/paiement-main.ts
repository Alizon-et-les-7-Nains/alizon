<<<<<<< HEAD
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

  const departments = new Map<string, string>();
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

  // Variable pour stocker l'ID de l'adresse de facturation
  let idAdresseFacturation: number | null = null;

  // Création de l'overlay pour l'adresse de facturation
  const addrFactOverlay = document.createElement("div");
  addrFactOverlay.className = "addr-fact-overlay";
  addrFactOverlay.innerHTML = `
    <div class="addr-fact-content">
      <h2>Adresse de facturation</h2>
      <div class="form-group">
        <input class="adresse-fact-input" type="text" placeholder="Adresse complète" required>
      </div>
      <div class="form-group">
        <input class="code-postal-fact-input" type="text" placeholder="Code postal" required>
      </div>
      <div class="form-group">
        <input class="ville-fact-input" type="text" placeholder="Ville" required>
      </div>
      <div class="button-group">
        <button id="closeAddrFact" class="btn-fermer">Annuler</button>
        <button id="validerAddrFact" class="btn-valider">Valider</button>
      </div>
    </div>
  `;

  document.body.appendChild(addrFactOverlay);

  addrFactOverlay.style.display = "none";
  const factAdresseCheckbox = document.querySelector(
    "#checkboxFactAddr"
  ) as HTMLInputElement;
  if (factAdresseCheckbox) {
    factAdresseCheckbox.checked = false;
  }

  // Gestion des événements de l'overlay
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

    // Validation du code postal
    const codePostal = codePostalFactInput.value.trim();
    if (!/^\d{5}$/.test(codePostal)) {
      showPopup("Le code postal doit contenir 5 chiffres", "error");
      return;
    }

    try {
      // Enregistrer l'adresse de facturation dans la base de données
      const formData = new URLSearchParams();
      formData.append("action", "saveBillingAddress");
      formData.append("adresse", adresseFactInput.value.trim());
      formData.append("codePostal", codePostal);
      formData.append("ville", villeFactInput.value.trim());

      console.log("Envoi de la requête saveBillingAddress...");

      const response = await fetch("", {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded",
        },
        body: formData,
      });

      console.log("Réponse reçue:", response.status, response.statusText);

      const result = await response.json();
      console.log("Résultat JSON:", result);

      if (result.success) {
        // STOCKER L'ID DE L'ADRESSE DE FACTURATION
        idAdresseFacturation = result.idAdresseFacturation;

        showPopup(
          result.message || "Adresse de facturation enregistrée avec succès",
          "success"
        );
        addrFactOverlay.style.display = "none";

        console.log(
          "Adresse de facturation enregistrée avec ID:",
          idAdresseFacturation
        );

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
      console.error("Erreur complète:", error);
      showPopup("Erreur réseau lors de l'enregistrement", "error");
    }
  });

  const closeAddrFactBtn = addrFactOverlay.querySelector(
    "#closeAddrFact"
  ) as HTMLButtonElement | null;

  closeAddrFactBtn?.addEventListener("click", () => {
    addrFactOverlay.style.display = "none";
    // Décocher la checkbox
    const factAdresseCheckbox = document.querySelector(
      "#checkboxFactAddr"
    ) as HTMLInputElement;
    if (factAdresseCheckbox) {
      factAdresseCheckbox.checked = false;
    }
  });

  // Fermer en cliquant en dehors du contenu
  addrFactOverlay.addEventListener("click", (e) => {
    if (e.target === addrFactOverlay) {
      addrFactOverlay.style.display = "none";
      const factAdresseCheckbox = document.querySelector(
        "#checkboxFactAddr"
      ) as HTMLInputElement;
      if (factAdresseCheckbox) {
        factAdresseCheckbox.checked = false;
      }
    }
  });

  // Gestion de la checkbox
  const factAdresseInput = document.querySelector(
    "#checkboxFactAddr"
  ) as HTMLInputElement;

  factAdresseInput?.addEventListener("change", (e) => {
    const isChecked = (e.target as HTMLInputElement).checked;

    if (isChecked) {
      addrFactOverlay.style.display = "flex";

      // Focus sur le premier champ
      const firstInput = addrFactOverlay.querySelector(
        "input"
      ) as HTMLInputElement;
      if (firstInput) {
        firstInput.focus();
      }
    } else {
      addrFactOverlay.style.display = "none";
    }
  });

  // Gestion des boutons payer
  payerButtons.forEach((btn) => {
    btn.addEventListener("click", (e) => {
      e.preventDefault();

      // Stocker l'ID de facturation dans window pour qu'il soit accessible par showPopup
      (window as any).idAdresseFacturation = idAdresseFacturation;

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
        showPopup("Validation des informations", "info");
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
=======
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
}
>>>>>>> produitNathan

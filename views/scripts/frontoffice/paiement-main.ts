// Fichier principal gérant la logique du paiement (autocomplétion, validation,
// enregistrement d'adresse de facturation, gestion des boutons "payer", etc.)

import { CartItem, Inputs, Maps } from "./paiement-types";
import { validateAll } from "./paiement-validation";
import { setupAutocomplete } from "./paiement-autocomplete";
import { showPopup } from "./paiement-popup";

// Ne rien faire si on n'est pas sur la page de paiement
if (document.body.classList.contains("pagePaiement")) {
  // ========================================================================
  // Sélection des éléments du DOM
  // ========================================================================
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

  // Tous les boutons "payer" présents sur la page
  const payerButtons = Array.from(
    document.querySelectorAll("body.pagePaiement .payer")
  ) as HTMLButtonElement[];

  // Elément récapitulatif (souvent utilisé pour afficher le résumé de commande)
  const recapEl = document.getElementById("recap");

  // ========================================================================
  // Structures de données pour l'autocomplétion des villes / codes postaux
  // ========================================================================
  const departments = new Map<string, string>();
  const citiesByCode = new Map<string, Set<string>>();
  const allCities = new Set<string>();
  const postals = new Map<string, Set<string>>();
  const selectedDepartment = { value: null as string | null };

  // Données préchargées (injectées côté serveur dans window.__PAYMENT_DATA__)
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

  // ========================================================================
  // Chargement du panier depuis les données préchargées
  // ========================================================================
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

  // ========================================================================
  // Initialisation de l'autocomplétion (module séparé)
  // ========================================================================
  setupAutocomplete({
    codePostalInput,
    villeInput,
    maps: { departments, citiesByCode, postals, allCities },
    selectedDepartment,
  });

  // ========================================================================
  // Overlay pour saisir / enregistrer une adresse de facturation
  // ========================================================================
  // Variable pour stocker l'ID renvoyé par le serveur une fois l'adresse enregistrée
  let idAdresseFacturation: number | null = null;

  // Création dynamique de l'overlay pour l'adresse de facturation
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

  // Ajout au DOM et masquage initial
  document.body.appendChild(addrFactOverlay);
  addrFactOverlay.style.display = "none";

  // S'assurer que la checkbox associée est décochée au chargement
  const factAdresseCheckbox = document.querySelector(
    "#checkboxFactAddr"
  ) as HTMLInputElement;
  if (factAdresseCheckbox) {
    factAdresseCheckbox.checked = false;
  }

  // ========================================================================
  // Gestion des actions dans l'overlay (validation / enregistrement)
  // ========================================================================
  const validerAddrFactBtn = addrFactOverlay.querySelector(
    "#validerAddrFact"
  ) as HTMLButtonElement | null;

  // Clic sur "Valider" -> validation client, envoi au serveur, stockage de l'ID
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

    // Validation basique des champs (non vides)
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

    // Validation simple du format du code postal (5 chiffres)
    const codePostal = codePostalFactInput.value.trim();
    if (!/^\d{5}$/.test(codePostal)) {
      showPopup("Le code postal doit contenir 5 chiffres", "error");
      return;
    }

    try {
      // Préparer les données pour l'envoi au serveur
      const formData = new URLSearchParams();
      formData.append("action", "saveBillingAddress");
      formData.append("adresse", adresseFactInput.value.trim());
      formData.append("codePostal", codePostal);
      formData.append("ville", villeFactInput.value.trim());

      // Logs pour aider au debug réseau côté client
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
        // Stocker l'ID renvoyé par le serveur pour usage ultérieur (ex: paiement)
        idAdresseFacturation = result.idAdresseFacturation;

        showPopup(
          result.message || "Adresse de facturation enregistrée avec succès",
          "success"
        );
        // Fermer l'overlay
        addrFactOverlay.style.display = "none";

        console.log(
          "Adresse de facturation enregistrée avec ID:",
          idAdresseFacturation
        );

        // Décocher la checkbox associée après enregistrement
        const factAdresseCheckbox = document.querySelector(
          "#checkboxFactAddr"
        ) as HTMLInputElement;
        if (factAdresseCheckbox) {
          factAdresseCheckbox.checked = false;
        }
      } else {
        // Afficher une erreur renvoyée par le serveur
        showPopup("Erreur lors de l'enregistrement: " + result.error, "error");
      }
    } catch (error) {
      // Gestion d'erreur réseau / JSON
      console.error("Erreur complète:", error);
      showPopup("Erreur réseau lors de l'enregistrement", "error");
    }
  });

  // Bouton "Annuler" -> fermer l'overlay et décocher la checkbox
  const closeAddrFactBtn = addrFactOverlay.querySelector(
    "#closeAddrFact"
  ) as HTMLButtonElement | null;

  closeAddrFactBtn?.addEventListener("click", () => {
    addrFactOverlay.style.display = "none";
    const factAdresseCheckbox = document.querySelector(
      "#checkboxFactAddr"
    ) as HTMLInputElement;
    if (factAdresseCheckbox) {
      factAdresseCheckbox.checked = false;
    }
  });

  // Cliquer hors du contenu de l'overlay ferme l'overlay
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

  // ========================================================================
  // Gestion de la checkbox qui affiche l'overlay d'adresse de facturation
  // ========================================================================
  const factAdresseInput = document.querySelector(
    "#checkboxFactAddr"
  ) as HTMLInputElement;

  factAdresseInput?.addEventListener("change", (e) => {
    const isChecked = (e.target as HTMLInputElement).checked;

    if (isChecked) {
      // Afficher l'overlay et focus sur le premier champ pour une saisie rapide
      addrFactOverlay.style.display = "flex";

      const firstInput = addrFactOverlay.querySelector(
        "input"
      ) as HTMLInputElement;
      if (firstInput) {
        firstInput.focus();
      }
    } else {
      // Masquer si décoché
      addrFactOverlay.style.display = "none";
    }
  });

  // ========================================================================
  // Gestion de la checkbox des conditions générales (CGV)
  // ========================================================================
  const cgvCheckbox = document.querySelector(
    'input[type="checkbox"][aria-label="conditions générales"]'
  ) as HTMLInputElement | null;

  cgvCheckbox?.addEventListener("change", () => {
    if (cgvCheckbox.checked) {
      // Supprimer un éventuel message d'erreur lié aux CGV et réinitialiser le style
      const conditionsSection = document.querySelector("section.conditions");
      if (conditionsSection) {
        const errorMsg = conditionsSection.querySelector(".error-message-cgv");
        if (errorMsg) errorMsg.remove();
      }
      cgvCheckbox.style.outline = "";
    }
  });

  // ========================================================================
  // Gestion des boutons "Payer" : validation complète puis affichage popup
  // ========================================================================
  payerButtons.forEach((btn) => {
    btn.addEventListener("click", (e) => {
      e.preventDefault();

      // Rendre l'ID de l'adresse de facturation accessible globalement si besoin
      (window as any).idAdresseFacturation = idAdresseFacturation;

      // Appel à la validation globale (module séparé)
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
        // Informations valides -> afficher un message d'état
        showPopup("Validation des informations", "info");
      } else {
        // En cas d'erreur, scroller vers le premier champ invalide
        const first = document.querySelector(".invalid");
        if (first)
          (first as HTMLElement).scrollIntoView({
            behavior: "smooth",
            block: "center",
          });
      }
    });
  });

  // ========================================================================
  // Masquage des listes de suggestions si on clique en dehors
  // ========================================================================
  document.addEventListener("click", (ev) => {
    const target = ev.target as HTMLElement | null;
    document.querySelectorAll(".suggestions").forEach((s) => {
      if (!target) return;
      const parent = (s.parentElement as HTMLElement) || null;
      if (!parent) return;
      if (target === parent || parent.contains(target)) {
        // clic à l'intérieur de la zone -> ne pas masquer
      } else {
        // clic à l'extérieur -> masquer la liste de suggestions
        (s as HTMLElement).style.display = "none";
      }
    });
  });
}

// ============================================================================
// TYPES & VALIDATION
// ============================================================================

type CartItem = {
  id: string;
  title: string;
  price: number;
  qty: number;
  img?: string;
};

function setError(el: Element | null, message: string) {
  if (!el) return;
  el.classList.add("invalid");
  const container = el.parentElement;
  if (!container) return;
  let err = container.querySelector(".error-message") as HTMLElement | null;
  if (!err) {
    err = document.createElement("small");
    err.className = "error-message";
    container.appendChild(err);
  }
  err.textContent = message;
}

function clearError(el: Element | null) {
  if (!el) return;
  el.classList.remove("invalid");
  const container = el.parentElement;
  if (!container) return;
  const err = container.querySelector(".error-message") as HTMLElement | null;
  if (err) err.textContent = "";
}

// Algorithme de Luhn utilisé pour valider la carte
function cardVerification(cardNumber: string): boolean {
  const cleaned = cardNumber.replace(/\s+/g, "");
  if (cleaned.length === 0 || !/^\d+$/.test(cleaned)) return false;
  const digits = cleaned
    .split("")
    .reverse()
    .map((d) => Number(d));
  for (let i = 1; i < digits.length; i += 2) {
    let n = digits[i] * 2;
    if (n > 9) n -= 9;
    digits[i] = n;
  }
  const sum = digits.reduce((a, b) => a + b, 0);
  return sum % 10 === 0;
}

function isVisa(cardNumber: string) {
  const clean = cardNumber.replace(/\s+/g, "");
  return /^4\d{12}(?:\d{3})?$/.test(clean) && cardVerification(clean);
}

type Inputs = {
  adresseInput: HTMLInputElement | null;
  codePostalInput: HTMLInputElement | null;
  villeInput: HTMLInputElement | null;
  numCarteInput: HTMLInputElement | null;
  nomCarteInput: HTMLInputElement | null;
  carteDateInput: HTMLInputElement | null;
  cvvInput: HTMLInputElement | null;
  recapEl: HTMLElement | null;
};

type ValidateAllParams = {
  inputs: Inputs;
  departments: Map<string, string>;
  postals: Map<string, Set<string>>;
  cart: CartItem[];
  // object wrapper so caller and callee can share/mutate the selected department
  selectedDepartment: { value: string | null };
};

function validateAll({
  inputs,
  departments,
  postals,
  cart,
  selectedDepartment,
}: ValidateAllParams): boolean {
  let ok = true;
  const {
    adresseInput,
    codePostalInput,
    villeInput,
    numCarteInput,
    nomCarteInput,
    carteDateInput,
    cvvInput,
    recapEl,
  } = inputs;

  // adresse
  if (!adresseInput || adresseInput.value.trim().length < 5) {
    setError(adresseInput, "Veuillez renseigner une adresse complète.");
    ok = false;
  } else clearError(adresseInput);

  // code postal / département
  if (!codePostalInput || codePostalInput.value.trim().length === 0) {
    setError(
      codePostalInput,
      "Veuillez renseigner un code département ou postal."
    );
    ok = false;
  } else {
    const val = codePostalInput.value.trim();
    if (!/^\d{1,2}$/.test(val) && !/^\d{5}$/.test(val)) {
      setError(
        codePostalInput,
        "Format attendu : 2 chiffres (département) ou 5 chiffres (code postal)."
      );
      ok = false;
    } else {
      if (/^\d{5}$/.test(val)) {
        const code = val.slice(0, 2);
        if (postals.has(val)) {
          clearError(codePostalInput);
          selectedDepartment.value = code;
        } else {
          if (!departments.has(code)) {
            setError(
              codePostalInput,
              "Code département inconnu. Utilisez l'autocomplétion ou vérifiez le code."
            );
            ok = false;
          } else {
            clearError(codePostalInput);
            selectedDepartment.value = code;
          }
        }
      } else {
        const code = val.padStart(2, "0");
        if (!departments.has(code)) {
          setError(
            codePostalInput,
            "Code département inconnu. Utilisez l'autocomplétion ou vérifiez le code."
          );
          ok = false;
        } else {
          clearError(codePostalInput);
          selectedDepartment.value = code;
        }
      }
    }
  }

  // ville: just clear previous errors
  if (villeInput) clearError(villeInput);

  // numéro de carte - messages plus détaillés
  if (!numCarteInput || numCarteInput.value.trim().length === 0) {
    setError(numCarteInput, "Veuillez saisir le numéro de carte.");
    ok = false;
  } else {
    const raw = numCarteInput.value.replace(/\s+/g, "");
    if (!/^\d+$/.test(raw)) {
      setError(
        numCarteInput,
        "Le numéro de carte ne doit contenir que des chiffres et des espaces."
      );
      ok = false;
    } else if (raw.length < 16) {
      setError(numCarteInput, "Le numéro de carte est trop court.");
      ok = false;
    } else if (raw.length > 16) {
      setError(numCarteInput, "Le numéro de carte semble trop long.");
      ok = false;
    } else if (!/^4/.test(raw)) {
      setError(
        numCarteInput,
        "Carte non-Visa détectée (les cartes Visa commencent par 4)."
      );
      ok = false;
    } else if (!cardVerification(raw)) {
      setError(
        numCarteInput,
        "Échec du contrôle de validité. Vérifiez le numéro."
      );
      ok = false;
    } else {
      clearError(numCarteInput);
    }
  }

  // nom
  if (!nomCarteInput || nomCarteInput.value.trim().length < 2) {
    setError(
      nomCarteInput,
      "Nom sur la carte invalide (au moins 2 caractères)."
    );
    ok = false;
  } else if (/\d/.test(nomCarteInput.value)) {
    setError(nomCarteInput, "Le nom ne doit pas contenir de chiffres.");
    ok = false;
  } else clearError(nomCarteInput);

  // date MM/AA ou MM/AAAA
  if (!carteDateInput || carteDateInput.value.trim().length === 0) {
    setError(carteDateInput, "Veuillez renseigner la date d'expiration.");
    ok = false;
  } else {
    const raw = carteDateInput.value.trim();
    const m = raw.split(/[\/\-]/)[0];
    const y = raw.split(/[\/\-]/)[1];
    if (!m || !y) {
      setError(carteDateInput, "Format attendu MM/AA ou MM/AAAA.");
      ok = false;
    } else {
      const mm = parseInt(m, 10);
      let yy = parseInt(y, 10);
      if (y.length === 2) yy += 2000;
      if (!(mm >= 1 && mm <= 12) || isNaN(yy)) {
        setError(carteDateInput, "Date d'expiration invalide.");
        ok = false;
      } else {
        const now = new Date();
        const exp = new Date(yy, mm - 1 + 1, 1);
        if (exp <= now) {
          setError(
            carteDateInput,
            "La date d'expiration doit être supérieure à la date courante."
          );
          ok = false;
        } else clearError(carteDateInput);
      }
    }
  }

  // cvv
  if (!cvvInput || !/^\d{3}$/.test(cvvInput.value.trim())) {
    setError(cvvInput, "CVV invalide (3 chiffres). ");
    ok = false;
  } else clearError(cvvInput);

  // panier
  if (cart.length === 0) {
    if (recapEl) {
      const p = document.createElement("small");
      p.className = "error-message";
      p.textContent = "Le panier est vide.";
      recapEl.appendChild(p);
    }
    ok = false;
  }

  return ok;
}

// ============================================================================
// AUTOCOMPLETE
// ============================================================================

type Maps = {
  departments: Map<string, string>;
  citiesByCode: Map<string, Set<string>>;
  postals: Map<string, Set<string>>;
  allCities: Set<string>;
};

function createSuggestionBox(input: HTMLInputElement) {
  let box = input.parentElement!.querySelector(
    ".suggestions"
  ) as HTMLElement | null;
  if (!box) {
    box = document.createElement("div");
    box.className = "suggestions";
    box.style.position = "absolute";
    box.style.background = "white";
    box.style.border = "1px solid rgba(0,0,0,0.12)";
    box.style.minWidth = "260px";
    box.style.maxWidth = "480px";
    box.style.width = "calc(100% - 12px)";
    box.style.maxHeight = "200px";
    box.style.overflow = "auto";
    box.style.zIndex = "999";
    box.style.boxShadow = "0 6px 18px rgba(0,0,0,0.08)";
    box.style.borderRadius = "6px";
    box.style.padding = "8px 0";
    box.style.fontSize = "1rem";
    box.style.whiteSpace = "normal";
    box.style.display = "none";
    const parent = input.parentElement as HTMLElement;
    if (getComputedStyle(parent).position === "static")
      parent.style.position = "relative";
    parent.appendChild(box);
  }
  box.innerHTML = "";
  return box;
}

function setupAutocomplete(params: {
  codePostalInput: HTMLInputElement | null;
  villeInput: HTMLInputElement | null;
  maps: Maps;
  // wrapper so modules share selectedDepartment
  selectedDepartment: { value: string | null };
}) {
  const { codePostalInput, villeInput, maps, selectedDepartment } = params;

  function showSuggestionsForCode(query: string) {
    if (!codePostalInput) return;
    const box = createSuggestionBox(codePostalInput);
    const q = query.trim().toLowerCase();
    const items: string[] = [];
    maps.departments.forEach((dept, code) => {
      if (code.startsWith(q) || dept.toLowerCase().includes(q))
        items.push(`${code} - ${dept}`);
    });
    maps.postals.forEach((cities, postal) => {
      if (postal.startsWith(q) || postal === q) {
        const sample = Array.from(cities).slice(0, 2).join(", ");
        items.push(`${postal} - ${sample}`);
      }
    });
    if (items.length === 0) {
      box.style.display = "none";
      return;
    }
    box.style.display = "block";
    items.slice(0, 15).forEach((it) => {
      const el = document.createElement("div");
      el.className = "suggestion-item";
      el.textContent = it;
      el.style.padding = "6px 12px";
      el.style.cursor = "pointer";
      el.addEventListener("click", () => {
        const key = it.split(" - ")[0];
        codePostalInput.value = key;
        if (/^\d{5}$/.test(key)) {
          selectedDepartment.value = key.slice(0, 2);
        } else {
          selectedDepartment.value = key.padStart(2, "0");
        }
        box.style.display = "none";
        clearError(codePostalInput);
      });
      box.appendChild(el);
    });
  }

  function showSuggestionsForCity(query: string) {
    if (!villeInput) return;
    const box = createSuggestionBox(villeInput);
    const q = query.trim().toLowerCase();

    let deptKey: string | null = selectedDepartment.value;
    if (!deptKey && codePostalInput) {
      const cp = codePostalInput.value.trim();
      if (/^\d{5}$/.test(cp)) deptKey = cp.slice(0, 2);
      else if (/^\d{1,2}$/.test(cp)) deptKey = cp.padStart(2, "0");
    }

    let candidates: string[] = [];
    if (deptKey && maps.citiesByCode.has(deptKey)) {
      candidates = Array.from(maps.citiesByCode.get(deptKey)!.values());
    } else {
      candidates = Array.from(maps.allCities.values());
    }

    const items = Array.from(
      new Set(candidates.filter((c) => c.toLowerCase().includes(q)))
    );

    box.style.display = "block";
    box.innerHTML = "";

    const typed = villeInput.value.trim();
    if (items.length === 0) {
      const el = document.createElement("div");
      el.className = "suggestion-item";
      el.textContent =
        typed.length > 0
          ? `Utiliser "${typed}" comme ville`
          : "Aucune suggestion";
      el.style.padding = "6px 12px";
      el.style.cursor = "pointer";
      el.addEventListener("click", () => {
        villeInput.value = typed;
        box.style.display = "none";
        clearError(villeInput);
        if (!selectedDepartment.value && deptKey)
          selectedDepartment.value = deptKey;
      });
      box.appendChild(el);
      return;
    }

    if (
      typed.length > 0 &&
      !items.some((i) => i.toLowerCase() === typed.toLowerCase())
    ) {
      const useTyped = document.createElement("div");
      useTyped.className = "suggestion-item";
      useTyped.textContent = `Utiliser "${typed}" comme ville`;
      useTyped.style.padding = "6px 12px";
      useTyped.style.cursor = "pointer";
      useTyped.addEventListener("click", () => {
        villeInput.value = typed;
        box.style.display = "none";
        clearError(villeInput);
        if (!selectedDepartment.value && deptKey)
          selectedDepartment.value = deptKey;
      });
      box.appendChild(useTyped);
    }

    items.slice(0, 20).forEach((it) => {
      const el = document.createElement("div");
      el.className = "suggestion-item";
      el.textContent = it;
      el.style.padding = "6px 12px";
      el.style.cursor = "pointer";
      el.addEventListener("click", () => {
        villeInput.value = it;
        box.style.display = "none";
        clearError(villeInput);
      });
      box.appendChild(el);
    });
  }

  // events
  if (codePostalInput) {
    codePostalInput.addEventListener("input", (e) => {
      const v = (e.target as HTMLInputElement).value;
      if (v.trim().length === 0) {
        const box = codePostalInput.parentElement!.querySelector(
          ".suggestions"
        ) as HTMLElement | null;
        if (box) box.style.display = "none";
        selectedDepartment.value = null;
        return;
      }
      showSuggestionsForCode(v);
    });
    codePostalInput.addEventListener("blur", () => {
      setTimeout(() => {
        const box = codePostalInput.parentElement!.querySelector(
          ".suggestions"
        ) as HTMLElement | null;
        if (box) box.style.display = "none";
      }, 150);
    });
    codePostalInput.addEventListener("change", () => {
      const val = codePostalInput.value.trim();
      if (/^\d{5}$/.test(val)) {
        const code = val.slice(0, 2);
        if (maps.postals.has(val)) {
          selectedDepartment.value = code;
        } else if (maps.departments.has(code)) {
          selectedDepartment.value = code;
        } else {
          selectedDepartment.value = null;
        }
      } else if (/^\d{1,2}$/.test(val)) {
        const code = val.padStart(2, "0");
        if (maps.departments.has(code)) selectedDepartment.value = code;
        else selectedDepartment.value = null;
      } else {
        selectedDepartment.value = null;
      }
      clearError(codePostalInput);
    });
  }

  if (villeInput) {
    villeInput.addEventListener("input", (e) => {
      const v = (e.target as HTMLInputElement).value;
      if (v.trim().length === 0) {
        const box = villeInput.parentElement!.querySelector(
          ".suggestions"
        ) as HTMLElement | null;
        if (box) box.style.display = "none";
        return;
      }
      showSuggestionsForCity(v);
    });
    villeInput.addEventListener("blur", () => {
      setTimeout(() => {
        const box = villeInput.parentElement!.querySelector(
          ".suggestions"
        ) as HTMLElement | null;
        if (box) box.style.display = "none";
      }, 150);
    });
    villeInput.addEventListener("change", () => clearError(villeInput));
  }
}

// ============================================================================
// POPUP
// ============================================================================

function showPopup(message: string) {
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

// ============================================================================
// ASIDE (RECAP)
// ============================================================================

type AsideHandle = {
  update: (cart: CartItem[]) => void;
  getElement: () => HTMLElement | null;
};

function initAside(
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

// ============================================================================
// MAIN PAIEMENT LOGIC
// ============================================================================

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

  // Données de panier : priorité
  // 1) utiliser les données fournies par le serveur via window.__PAYMENT_DATA__.cart
  // 2) sinon tenter de lire le HTML déjà rendu côté serveur dans #recap (utiliser la BDD)
  // 3) sinon démarrer avec un panier vide
  let cart: CartItem[] = [];

  const preCart = (window as any).__PAYMENT_DATA__?.cart;
  if (Array.isArray(preCart) && preCart.length > 0) {
    cart = preCart as CartItem[];
  } else if (recapEl) {
    // lire les éléments déjà rendus par le serveur (PHP) dans #recap
    const produits = Array.from(recapEl.querySelectorAll(".produit"));
    if (produits.length > 0) {
      cart = produits.map((p) => {
        const id = (p.getAttribute("data-id") || "").toString() || "";
        const title =
          (
            p.querySelector(".titre") as HTMLElement | null
          )?.textContent?.trim() || "";
        const qtyText =
          (
            p.querySelector(".qty") as HTMLElement | null
          )?.textContent?.trim() || "1";
        const qty = parseInt(qtyText, 10) || 1;
        const prixText =
          (
            p.querySelector(".prix") as HTMLElement | null
          )?.textContent?.trim() || "0";
        // extract number from strings like "29.99 €"
        const price =
          Number(prixText.replace(/[€\s]/g, "").replace(",", ".")) || 0;
        const img =
          (p.querySelector("img") as HTMLImageElement | null)?.src || "";
        return { id, title, price, qty, img } as CartItem;
      });
    } else {
      cart = [];
    }
  } else {
    cart = [];
  }

  function updateQty(id: string, delta: number) {
    cart = cart
      .map((it) => {
        if (it.id === id) {
          const next = Math.max(0, it.qty + delta);
          return { ...it, qty: next };
        }
        return it;
      })
      .filter((it) => it.qty > 0);

    // mettre à jour l'aside
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

  function removeItem(id: string) {
    cart = cart.filter((it) => it.id !== id);
    aside.update(cart);
    const prod = document.querySelector(`.produit[data-id="${id}"]`);
    if (prod && prod.parentElement) prod.parentElement.removeChild(prod);
  }

  // initialiser l'aside (récapitulatif) et synchroniser avec le panier
  const aside = initAside("#recap", cart, updateQty, removeItem);

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

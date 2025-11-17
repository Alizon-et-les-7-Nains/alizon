"use strict";
Array.from(document.querySelectorAll('main.acceuilBackoffice button.bilan')).forEach((btn) => {
    btn.addEventListener('click', () => {
        if (!btn.classList.contains('here')) {
            document.querySelector('main.acceuilBackoffice button.bilan.here')?.classList.remove('here');
            btn.classList.add('here');
        }
    });
});
Array.from(document.getElementsByClassName('aside-btn')).forEach(asideButton => {
    asideButton.addEventListener('click', () => {
        const category = asideButton.children[0].children[1].innerHTML.toLowerCase();
        if (!asideButton.className.includes('here')) {
            window.location.href = `./${category}.php`;
        }
    });
});
document.querySelector('button#haut')?.addEventListener('click', () => {
    window.scrollTo({ top: 0, behavior: 'smooth' });
});
document
    .querySelector("header.backoffice figure:first-child")
    ?.addEventListener("click", () => {
    window.location.href = "10.253.5.104/views/backoffice/ajouterProduit.php";
});
const modal = document.querySelector("header.backoffice dialog");
document
    .querySelector("header.backoffice figure:nth-child(2)")
    ?.addEventListener("click", () => {
    modal?.showModal();
});
document
    .querySelector("header.backoffice dialog button")
    ?.addEventListener("click", () => {
    modal?.close();
});
document
    .querySelector("header.backoffice dialog nav button:first-child")
    ?.addEventListener("click", () => {
    modal?.close();
});
document
    .querySelector("header.backoffice dialog nav button:last-child")
    ?.addEventListener("click", () => {
    window.location.href = "10.253.5.104/views/backoffice/connexion.php";
});
modal?.addEventListener("click", (e) => {
    if (e.target === modal) {
        modal.close();
    }
});
define("frontoffice/paiement-types", ["require", "exports"], function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
});
// ============================================================================
// VALIDATION FUNCTIONS
// ============================================================================
define("frontoffice/paiement-validation", ["require", "exports"], function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setError = setError;
    exports.clearError = clearError;
    exports.cardVerification = cardVerification;
    exports.isVisa = isVisa;
    exports.validateAll = validateAll;
    function setError(el, message) {
        if (!el)
            return;
        el.classList.add("invalid");
        const container = el.parentElement;
        if (!container)
            return;
        let err = container.querySelector(".error-message");
        if (!err) {
            err = document.createElement("small");
            err.className = "error-message";
            container.appendChild(err);
        }
        err.textContent = message;
    }
    function clearError(el) {
        if (!el)
            return;
        el.classList.remove("invalid");
        const container = el.parentElement;
        if (!container)
            return;
        const err = container.querySelector(".error-message");
        if (err)
            err.textContent = "";
    }
    function cardVerification(cardNumber) {
        const cleaned = cardNumber.replace(/\s+/g, "");
        if (cleaned.length === 0 || !/^\d+$/.test(cleaned))
            return false;
        const digits = cleaned
            .split("")
            .reverse()
            .map((d) => Number(d));
        for (let i = 1; i < digits.length; i += 2) {
            let n = digits[i] * 2;
            if (n > 9)
                n -= 9;
            digits[i] = n;
        }
        const sum = digits.reduce((a, b) => a + b, 0);
        return sum % 10 === 0;
    }
    function isVisa(cardNumber) {
        const clean = cardNumber.replace(/\s+/g, "");
        return /^4\d{12}(?:\d{3})?$/.test(clean) && cardVerification(clean);
    }
    function validateAll({ inputs, departments, postals, cart, selectedDepartment, }) {
        let ok = true;
        const { adresseInput, codePostalInput, villeInput, numCarteInput, nomCarteInput, carteDateInput, cvvInput, recapEl, } = inputs;
        // adresse
        if (!adresseInput || adresseInput.value.trim().length < 5) {
            setError(adresseInput, "Veuillez renseigner une adresse complète.");
            ok = false;
        }
        else
            clearError(adresseInput);
        // code postal / département
        if (!codePostalInput || codePostalInput.value.trim().length === 0) {
            setError(codePostalInput, "Veuillez renseigner un code département ou postal.");
            ok = false;
        }
        else {
            const val = codePostalInput.value.trim();
            if (!/^\d{1,2}$/.test(val) && !/^\d{5}$/.test(val)) {
                setError(codePostalInput, "Format attendu : 2 chiffres (département) ou 5 chiffres (code postal).");
                ok = false;
            }
            else {
                if (/^\d{5}$/.test(val)) {
                    const code = val.slice(0, 2);
                    if (postals.has(val)) {
                        clearError(codePostalInput);
                        selectedDepartment.value = code;
                    }
                    else {
                        if (!departments.has(code)) {
                            setError(codePostalInput, "Code département inconnu. Utilisez l'autocomplétion ou vérifiez le code.");
                            ok = false;
                        }
                        else {
                            clearError(codePostalInput);
                            selectedDepartment.value = code;
                        }
                    }
                }
                else {
                    const code = val.padStart(2, "0");
                    if (!departments.has(code)) {
                        setError(codePostalInput, "Code département inconnu. Utilisez l'autocomplétion ou vérifiez le code.");
                        ok = false;
                    }
                    else {
                        clearError(codePostalInput);
                        selectedDepartment.value = code;
                    }
                }
            }
        }
        // ville: just clear previous errors
        if (villeInput)
            clearError(villeInput);
        // numéro de carte - messages plus détaillés
        if (!numCarteInput || numCarteInput.value.trim().length === 0) {
            setError(numCarteInput, "Veuillez saisir le numéro de carte.");
            ok = false;
        }
        else {
            const raw = numCarteInput.value.replace(/\s+/g, "");
            if (!/^\d+$/.test(raw)) {
                setError(numCarteInput, "Le numéro de carte ne doit contenir que des chiffres et des espaces.");
                ok = false;
            }
            else if (raw.length < 16) {
                setError(numCarteInput, "Le numéro de carte est trop court.");
                ok = false;
            }
            else if (raw.length > 16) {
                setError(numCarteInput, "Le numéro de carte semble trop long.");
                ok = false;
            }
            else if (!/^4/.test(raw)) {
                setError(numCarteInput, "Carte non-Visa détectée (les cartes Visa commencent par 4).");
                ok = false;
            }
            else if (!cardVerification(raw)) {
                setError(numCarteInput, "Échec du contrôle de validité. Vérifiez le numéro.");
                ok = false;
            }
            else {
                clearError(numCarteInput);
            }
        }
        // nom
        if (!nomCarteInput || nomCarteInput.value.trim().length < 2) {
            setError(nomCarteInput, "Nom sur la carte invalide (au moins 2 caractères).");
            ok = false;
        }
        else if (/\d/.test(nomCarteInput.value)) {
            setError(nomCarteInput, "Le nom ne doit pas contenir de chiffres.");
            ok = false;
        }
        else
            clearError(nomCarteInput);
        // date MM/AA ou MM/AAAA
        if (!carteDateInput || carteDateInput.value.trim().length === 0) {
            setError(carteDateInput, "Veuillez renseigner la date d'expiration.");
            ok = false;
        }
        else {
            const raw = carteDateInput.value.trim();
            const m = raw.split(/[\/\-]/)[0];
            const y = raw.split(/[\/\-]/)[1];
            if (!m || !y) {
                setError(carteDateInput, "Format attendu MM/AA ou MM/AAAA.");
                ok = false;
            }
            else {
                const mm = parseInt(m, 10);
                let yy = parseInt(y, 10);
                if (y.length === 2)
                    yy += 2000;
                if (!(mm >= 1 && mm <= 12) || isNaN(yy)) {
                    setError(carteDateInput, "Date d'expiration invalide.");
                    ok = false;
                }
                else {
                    const now = new Date();
                    const exp = new Date(yy, mm - 1 + 1, 1);
                    if (exp <= now) {
                        setError(carteDateInput, "La date d'expiration doit être supérieure à la date courante.");
                        ok = false;
                    }
                    else
                        clearError(carteDateInput);
                }
            }
        }
        // cvv
        if (!cvvInput || !/^\d{3}$/.test(cvvInput.value.trim())) {
            setError(cvvInput, "CVV invalide (3 chiffres). ");
            ok = false;
        }
        else
            clearError(cvvInput);
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
});
// ============================================================================
// AUTOCOMPLETE
// ============================================================================
define("frontoffice/paiement-autocomplete", ["require", "exports", "frontoffice/paiement-validation"], function (require, exports, paiement_validation_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setupAutocomplete = setupAutocomplete;
    function createSuggestionBox(input) {
        let box = input.parentElement.querySelector(".suggestions");
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
            const parent = input.parentElement;
            if (getComputedStyle(parent).position === "static")
                parent.style.position = "relative";
            parent.appendChild(box);
        }
        box.innerHTML = "";
        return box;
    }
    function setupAutocomplete(params) {
        const { codePostalInput, villeInput, maps, selectedDepartment } = params;
        function showSuggestionsForCode(query) {
            if (!codePostalInput)
                return;
            const box = createSuggestionBox(codePostalInput);
            const q = query.trim().toLowerCase();
            const items = [];
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
                    }
                    else {
                        selectedDepartment.value = key.padStart(2, "0");
                    }
                    box.style.display = "none";
                    (0, paiement_validation_1.clearError)(codePostalInput);
                });
                box.appendChild(el);
            });
        }
        function showSuggestionsForCity(query) {
            if (!villeInput)
                return;
            const box = createSuggestionBox(villeInput);
            const q = query.trim().toLowerCase();
            let deptKey = selectedDepartment.value;
            if (!deptKey && codePostalInput) {
                const cp = codePostalInput.value.trim();
                if (/^\d{5}$/.test(cp))
                    deptKey = cp.slice(0, 2);
                else if (/^\d{1,2}$/.test(cp))
                    deptKey = cp.padStart(2, "0");
            }
            let candidates = [];
            if (deptKey && maps.citiesByCode.has(deptKey)) {
                candidates = Array.from(maps.citiesByCode.get(deptKey).values());
            }
            else {
                candidates = Array.from(maps.allCities.values());
            }
            const items = Array.from(new Set(candidates.filter((c) => c.toLowerCase().includes(q))));
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
                    (0, paiement_validation_1.clearError)(villeInput);
                    if (!selectedDepartment.value && deptKey)
                        selectedDepartment.value = deptKey;
                });
                box.appendChild(el);
                return;
            }
            if (typed.length > 0 &&
                !items.some((i) => i.toLowerCase() === typed.toLowerCase())) {
                const useTyped = document.createElement("div");
                useTyped.className = "suggestion-item";
                useTyped.textContent = `Utiliser "${typed}" comme ville`;
                useTyped.style.padding = "6px 12px";
                useTyped.style.cursor = "pointer";
                useTyped.addEventListener("click", () => {
                    villeInput.value = typed;
                    box.style.display = "none";
                    (0, paiement_validation_1.clearError)(villeInput);
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
                    (0, paiement_validation_1.clearError)(villeInput);
                });
                box.appendChild(el);
            });
        }
        // events
        if (codePostalInput) {
            codePostalInput.addEventListener("input", (e) => {
                const v = e.target.value;
                if (v.trim().length === 0) {
                    const box = codePostalInput.parentElement.querySelector(".suggestions");
                    if (box)
                        box.style.display = "none";
                    selectedDepartment.value = null;
                    return;
                }
                showSuggestionsForCode(v);
            });
            codePostalInput.addEventListener("blur", () => {
                setTimeout(() => {
                    const box = codePostalInput.parentElement.querySelector(".suggestions");
                    if (box)
                        box.style.display = "none";
                }, 150);
            });
            codePostalInput.addEventListener("change", () => {
                const val = codePostalInput.value.trim();
                if (/^\d{5}$/.test(val)) {
                    const code = val.slice(0, 2);
                    if (maps.postals.has(val)) {
                        selectedDepartment.value = code;
                    }
                    else if (maps.departments.has(code)) {
                        selectedDepartment.value = code;
                    }
                    else {
                        selectedDepartment.value = null;
                    }
                }
                else if (/^\d{1,2}$/.test(val)) {
                    const code = val.padStart(2, "0");
                    if (maps.departments.has(code))
                        selectedDepartment.value = code;
                    else
                        selectedDepartment.value = null;
                }
                else {
                    selectedDepartment.value = null;
                }
                (0, paiement_validation_1.clearError)(codePostalInput);
            });
        }
        if (villeInput) {
            villeInput.addEventListener("input", (e) => {
                const v = e.target.value;
                if (v.trim().length === 0) {
                    const box = villeInput.parentElement.querySelector(".suggestions");
                    if (box)
                        box.style.display = "none";
                    return;
                }
                showSuggestionsForCity(v);
            });
            villeInput.addEventListener("blur", () => {
                setTimeout(() => {
                    const box = villeInput.parentElement.querySelector(".suggestions");
                    if (box)
                        box.style.display = "none";
                }, 150);
            });
            villeInput.addEventListener("change", () => (0, paiement_validation_1.clearError)(villeInput));
        }
    }
});
// ============================================================================
// POPUP - Version avec base de données
// ============================================================================
define("frontoffice/paiement-popup", ["require", "exports"], function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.showPopup = showPopup;
    // Clé de chiffrement (doit correspondre à celle dans Chiffrement.js)
    const CLE_CHIFFREMENT = "?zu6j,xX{N12I]0r6C=v57IoASU~?6_y";
    function showPopup(message) {
        const overlay = document.createElement("div");
        overlay.className = "payment-overlay";
        // Récupérer les valeurs des inputs
        const adresseInput = document.querySelector("body.pagePaiement .adresse-input");
        const codePostalInput = document.querySelector("body.pagePaiement .code-postal-input");
        const villeInput = document.querySelector("body.pagePaiement .ville-input");
        const numCarteInput = document.querySelector("body.pagePaiement .num-carte");
        const nomCarteInput = document.querySelector("body.pagePaiement .nom-carte");
        const carteDateInput = document.querySelector("body.pagePaiement .carte-date");
        const cvvInput = document.querySelector("body.pagePaiement .cvv-input");
        const adresse = adresseInput?.value.trim() || "";
        const codePostal = codePostalInput?.value.trim() || "";
        const ville = villeInput?.value.trim() || "";
        const rawNumCarte = numCarteInput?.value.replace(/\s+/g, "") || "";
        const nomCarte = nomCarteInput?.value.trim() || "";
        const dateCarte = carteDateInput?.value.trim() || "";
        const rawCVV = cvvInput?.value.trim() || "";
        // CHIFFREMENT DES DONNÉES SENSIBLES
        const numeroCarteChiffre = window.vignere
            ? window.vignere(rawNumCarte, CLE_CHIFFREMENT, 1)
            : rawNumCarte;
        const cvvChiffre = window.vignere
            ? window.vignere(rawCVV, CLE_CHIFFREMENT, 1)
            : rawCVV;
        const last4 = rawNumCarte.length >= 4 ? rawNumCarte.slice(-4) : rawNumCarte;
        // Déterminer la région à partir du code postal
        let region = "";
        if (codePostal.length >= 2) {
            const codeDept = codePostal.length === 5
                ? codePostal.slice(0, 2)
                : codePostal.padStart(2, "0");
            region = `Département ${codeDept}`;
        }
        const preCart = Array.isArray(window.__PAYMENT_DATA__?.cart)
            ? window.__PAYMENT_DATA__.cart
            : [];
        let cartItemsHtml = "";
        if (Array.isArray(preCart) && preCart.length > 0) {
            cartItemsHtml = preCart
                .map((item) => `
      <div class="product">
        <img src="${item.img || "/images/default.png"}" alt="${item.nom}" />
        <p class="title">${item.nom}</p>
        <p><strong>Quantité :</strong> ${item.qty}</p>
        <p><strong>Prix total :</strong> ${(item.prix * item.qty).toFixed(2)} €</p>
      </div>`)
                .join("");
        }
        else {
            cartItemsHtml = `<p class="empty">Panier vide</p>`;
        }
        overlay.innerHTML = `
    <div class="payment-popup" role="dialog" aria-modal="true">
      <button class="close-popup" aria-label="Fermer">✕</button>
      <div class="order-summary">
        <h2>Récapitulatif de commande</h2>
        <div class="info">
          <p><strong>Adresse de livraison :</strong> ${adresse} ${codePostal} ${ville}</p>
          <p><strong>Payé avec :</strong> Carte Visa finissant par ${last4}</p>
        </div>
        <h3>Contenu du panier :</h3>
        <div class="cart">${cartItemsHtml}</div>
        <div class="actions">
          <button class="undo">Annuler</button>
          <button class="confirm">Confirmer ma commande</button>
        </div>
      </div>
    </div>
  `;
        document.body.appendChild(overlay);
        // Gestion des événements
        const closeBtn = overlay.querySelector(".close-popup");
        const undoBtn = overlay.querySelector(".undo");
        const confirmBtn = overlay.querySelector(".confirm");
        closeBtn?.addEventListener("click", () => overlay.remove());
        undoBtn?.addEventListener("click", () => overlay.remove());
        if (!confirmBtn)
            return;
        confirmBtn.addEventListener("click", async () => {
            const popup = overlay.querySelector(".payment-popup");
            if (!popup)
                return;
            // Afficher un indicateur de chargement
            confirmBtn.textContent = "Traitement en cours...";
            confirmBtn.disabled = true;
            try {
                // Appeler l'API pour créer la commande
                const orderData = {
                    adresseLivraison: adresse,
                    villeLivraison: ville,
                    regionLivraison: region,
                    numeroCarte: numeroCarteChiffre, // Version chiffrée
                    cvv: cvvChiffre, // Version chiffrée
                    nomCarte: nomCarte,
                    dateExpiration: dateCarte,
                    codePostal: codePostal,
                };
                const result = await window.PaymentAPI.createOrder(orderData);
                if (result.success) {
                    // Afficher le message de succès
                    popup.innerHTML = `
          <div class="thank-you">
            <h2>Merci de votre commande !</h2>
            <p>Votre commande a bien été enregistrée.</p>
            <p><strong>Numéro de commande :</strong> ${result.idCommande}</p>
            <button class="close-popup">Fermer</button>
          </div>
        `;
                    const innerClose = popup.querySelector(".close-popup");
                    innerClose?.addEventListener("click", () => {
                        // Recharger la page pour vider le panier
                        window.location.reload();
                    });
                }
                else {
                    // Afficher l'erreur
                    alert("Erreur lors de la création de la commande: " +
                        (result.error || "Erreur inconnue"));
                    confirmBtn.textContent = "Confirmer ma commande";
                    confirmBtn.disabled = false;
                }
            }
            catch (error) {
                console.error("Erreur:", error);
                alert("Erreur réseau lors de la création de la commande");
                confirmBtn.textContent = "Confirmer ma commande";
                confirmBtn.disabled = false;
            }
        });
    }
});
// ============================================================================
// MAIN PAIEMENT LOGIC
// ============================================================================
define("frontoffice/paiement-main", ["require", "exports", "frontoffice/paiement-validation", "frontoffice/paiement-autocomplete", "frontoffice/paiement-popup"], function (require, exports, paiement_validation_2, paiement_autocomplete_1, paiement_popup_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    if (document.body.classList.contains("pagePaiement")) {
        // Éléments
        const adresseInput = document.querySelector("body.pagePaiement .adresse-input");
        const codePostalInput = document.querySelector("body.pagePaiement .code-postal-input");
        const villeInput = document.querySelector("body.pagePaiement .ville-input");
        const numCarteInput = document.querySelector("body.pagePaiement .num-carte");
        const nomCarteInput = document.querySelector("body.pagePaiement .nom-carte");
        const carteDateInput = document.querySelector("body.pagePaiement .carte-date");
        const cvvInput = document.querySelector("body.pagePaiement .cvv-input");
        const payerButtons = Array.from(document.querySelectorAll("body.pagePaiement .payer"));
        const recapEl = document.getElementById("recap");
        const departments = new Map(); // code -> nom du département
        const citiesByCode = new Map();
        const allCities = new Set();
        const postals = new Map();
        const selectedDepartment = { value: null };
        const preloaded = window.__PAYMENT_DATA__ || {};
        if (preloaded.departments) {
            Object.keys(preloaded.departments).forEach((code) => {
                departments.set(code, preloaded.departments[code]);
            });
        }
        if (preloaded.citiesByCode) {
            Object.keys(preloaded.citiesByCode).forEach((code) => {
                const set = new Set(preloaded.citiesByCode[code]);
                citiesByCode.set(code, set);
                preloaded.citiesByCode[code].forEach((c) => allCities.add(c));
            });
        }
        if (preloaded.postals) {
            Object.keys(preloaded.postals).forEach((postal) => {
                const set = new Set(preloaded.postals[postal]);
                postals.set(postal, set);
                preloaded.postals[postal].forEach((c) => allCities.add(c));
            });
        }
        // Initialiser le panier à partir des données injectées côté PHP
        let cart = [];
        if (preloaded.cart && Array.isArray(preloaded.cart)) {
            cart = preloaded.cart.map((it) => ({
                id: String(it.id ?? it.idProduit ?? ""),
                nom: String(it.nom ?? "Produit sans nom"),
                prix: Number(it.prix ?? 0),
                qty: Number(it.qty ?? it.quantiteProduit ?? 0),
                img: it.img ?? it.URL ?? "../../public/images/default.png",
            }));
        }
        // Setup autocomplete handlers
        (0, paiement_autocomplete_1.setupAutocomplete)({
            codePostalInput,
            villeInput,
            maps: { departments, citiesByCode, postals, allCities },
            selectedDepartment,
        });
        // Gestion des boutons payer
        payerButtons.forEach((btn) => {
            btn.addEventListener("click", (e) => {
                e.preventDefault();
                const ok = (0, paiement_validation_2.validateAll)({
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
                    (0, paiement_popup_1.showPopup)("Paiement réussi");
                }
                else {
                    const first = document.querySelector(".invalid");
                    if (first)
                        first.scrollIntoView({
                            behavior: "smooth",
                            block: "center",
                        });
                }
            });
        });
        // Masquer les suggestions si on clique en dehors
        document.addEventListener("click", (ev) => {
            const target = ev.target;
            document.querySelectorAll(".suggestions").forEach((s) => {
                if (!target)
                    return;
                const parent = s.parentElement || null;
                if (!parent)
                    return;
                if (target === parent || parent.contains(target)) {
                    // click à l'intérieur -> rien
                }
                else {
                    s.style.display = "none";
                }
            });
        });
        const addrFactOverlay = document.createElement("div");
        addrFactOverlay.className = "addr-fact-overlay";
        addrFactOverlay.innerHTML = `
    <div class="addr-fact-content">
      <h2>Adresse de facturation</h2>
      <label>Adresse
        <input class="adresse-input" type="text" placeholder="Adresse" aria-label="Adresse">
      </label>
      <label>Code Postal
        <input class="code-postal-input" type="text" placeholder="Code Postal" aria-label="Code Postal">
      </label>
      <label>Ville
        <input class="ville-input" type="text" placeholder="Ville" aria-label="Ville">
      </label>
      <button id="closeAddrFact">Fermer</button>
    </div>
  `;
        const closeAddrFactBtn = addrFactOverlay.querySelector("#closeAddrFact");
        closeAddrFactBtn?.addEventListener("click", () => {
            document.body.removeChild(addrFactOverlay);
        });
        const factAdresseInput = document.getElementById("checkboxFactAddr");
        factAdresseInput?.addEventListener("change", (e) => {
            const isChecked = e.target.checked;
            if (isChecked) {
                document.body.appendChild(addrFactOverlay);
            }
        });
    }
});
//# sourceMappingURL=script.js.map
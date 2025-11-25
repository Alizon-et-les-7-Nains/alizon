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
const modalSupprProduit = document.querySelector("main.modifierProduit dialog");
document.querySelector("main.modifierProduit .btn-supprimer")?.addEventListener("click", () => {
    modalSupprProduit?.showModal();
});
document.querySelector("main.modifierProduit dialog button")?.addEventListener("click", () => {
    modalSupprProduit?.close();
});
document.querySelector("main.modifierProduit dialog nav button:first-child")?.addEventListener("click", () => {
    modalSupprProduit?.close();
});
modalSupprProduit?.addEventListener("click", (e) => {
    if (e.target === modalSupprProduit) {
        modalSupprProduit.close();
    }
});
const boutonHaut = document.getElementById('haut');
boutonHaut?.addEventListener('click', () => {
    window.scrollTo({ top: 0, behavior: 'smooth' });
});
window.addEventListener('scroll', () => {
    if (window.scrollY > window.innerHeight) {
        boutonHaut?.classList.add('visible');
    }
    else {
        boutonHaut?.classList.remove('visible');
    }
});
document.querySelector("header.backoffice figure:first-child")?.addEventListener("click", () => {
    window.location.href = "ajouterProduit.php";
});
const modalDeconnexion = document.querySelector("header.backoffice dialog");
document.querySelector("header.backoffice figure:nth-child(2)")?.addEventListener("click", () => {
    modalDeconnexion?.showModal();
});
document.querySelector("header.backoffice dialog button")?.addEventListener("click", () => {
    modalDeconnexion?.close();
});
document.querySelector("header.backoffice dialog nav button:first-child")?.addEventListener("click", () => {
    modalDeconnexion?.close();
});
document.querySelector("header.backoffice dialog nav button:last-child")?.addEventListener("click", () => {
    window.location.href = "connexion.php";
});
modalDeconnexion?.addEventListener("click", (e) => {
    if (e.target === modalDeconnexion) {
        modalDeconnexion.close();
    }
});
document.querySelector('header.backoffice figure:nth-child(3)')?.addEventListener('click', () => {
    window.location.href = 'compteVendeur.php';
});
const btnSettings = Array.from(document.getElementsByClassName('settings'));
const modalReassort = document.querySelector("dialog.reassort");
const inputSeuil = document.getElementById('seuil');
const inputDate = document.getElementById('dateReassort');
const inputReassort = document.getElementById('reassort');
const buttonConfirm = document.getElementById('buttonConfirm');
const errorFieldSeuil = document.getElementById('errorFieldSeuil');
const errorFieldReassort = document.getElementById('errorFieldReassort');
const errorFieldDate = document.getElementById('errorFieldDate');
btnSettings.forEach(btn => {
    btn.addEventListener('mouseover', () => {
        const subDivs = Array.from(btn.children);
        subDivs.forEach(div => {
            if (div instanceof HTMLElement && div.firstElementChild instanceof HTMLElement) {
                const innerDiv = div.firstElementChild;
                innerDiv.style.left = innerDiv.classList.contains('right') ? '4px' : '14px';
            }
        });
    });
});
btnSettings.forEach(btn => {
    btn.addEventListener('mouseout', () => {
        const subDivs = Array.from(btn.children);
        subDivs.forEach(div => {
            if (div instanceof HTMLElement && div.firstElementChild instanceof HTMLElement) {
                const innerDiv = div.firstElementChild;
                innerDiv.style.left = innerDiv.classList.contains('right') ? '14px' : '4px';
            }
        });
    });
});
let idProduit;
btnSettings.forEach(btn => {
    btn.addEventListener('click', () => {
        idProduit = parseInt(btn.id);
        modalReassort.showModal();
        modalReassort.style.display = 'flex';
    });
});
modalReassort?.addEventListener("click", (e) => {
    if (e.target === modalReassort) {
        modalReassort.close();
        modalReassort.style.display = 'none';
    }
});
document.querySelector('modal.reassort input#annuler')?.addEventListener('click', () => {
    modalReassort.close();
    modalReassort.style.display = 'none';
});
function checkInt(value) {
    let valid = true;
    let intValue = parseInt(value);
    if (value != null) {
        if (!intValue || intValue < 0) {
            valid = false;
        }
    }
    else {
        valid = true;
    }
    return valid;
}
function checkDate(date) {
    let valid = true;
    if (date != null) {
        if (date.getTime() < Date.now()) {
            valid = false;
        }
    }
    else {
        valid = true;
    }
    return valid;
}
function allValid() {
    return checkInt(inputSeuil.value) && checkDate(inputDate.valueAsDate) && checkInt(inputReassort.value);
}
inputSeuil.addEventListener('input', () => {
    if (!checkInt(inputSeuil.value)) {
        inputSeuil.style.cssText = 'border-color: #f14e4e !important';
        errorFieldSeuil.style.display = 'block';
    }
    else {
        inputSeuil.style.cssText = 'border-color: #273469 !important';
        errorFieldSeuil.style.display = 'none';
    }
    if (allValid()) {
        buttonConfirm.disabled = true;
    }
    else {
        buttonConfirm.disabled = false;
    }
});
inputDate.addEventListener('input', () => {
    if (!checkDate(inputDate.valueAsDate)) {
        inputDate.style.cssText = 'border-color: #f14e4e !important';
        errorFieldDate.style.display = 'block';
    }
    else {
        inputDate.style.cssText = 'border-color: #273469 !important';
        errorFieldDate.style.display = 'none';
    }
    if (allValid()) {
        buttonConfirm.disabled = true;
    }
    else {
        buttonConfirm.disabled = false;
    }
});
inputReassort.addEventListener('input', () => {
    if (!checkInt(inputReassort.value)) {
        inputReassort.style.cssText = 'border-color: #f14e4e !important';
        errorFieldReassort.style.display = 'block';
    }
    else {
        inputReassort.style.cssText = 'border-color: #273469 !important';
        errorFieldReassort.style.display = 'none';
    }
    if (allValid()) {
        buttonConfirm.disabled = true;
    }
    else {
        buttonConfirm.disabled = false;
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
    // Fonction helper pour le chiffrement avec vérification renforcée
    const chiffrerAvecVignere = (texte, sens) => {
        // Utiliser la clé depuis window ou une valeur par défaut
        const cle = window.CLE_CHIFFREMENT || "?zu6j,xX{N12I]0r6C=v57IoASU~?6_y";
        if (typeof window.vignere === "function" && cle && cle.length > 0) {
            return window.vignere(texte, cle, sens);
        }
        console.warn("Fonction vignere non disponible ou clé invalide, retour du texte en clair");
        return texte;
    };
    function showPopup(message, type = "info") {
        const overlay = document.createElement("div");
        overlay.className = `payment-overlay ${type}`;
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
        // CHIFFREMENT DES DONNÉES SENSIBLES - version sécurisée
        const numeroCarteChiffre = chiffrerAvecVignere(rawNumCarte, 1);
        const cvvChiffre = chiffrerAvecVignere(rawCVV, 1);
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
    <div class="payment-popup" role="dialog" aria-modal="true" data-type="${type}">
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
        let removeOverlay = () => {
            if (document.body.contains(overlay)) {
                document.body.removeChild(overlay);
            }
        };
        closeBtn?.addEventListener("click", removeOverlay);
        undoBtn?.addEventListener("click", removeOverlay);
        if (!confirmBtn)
            return;
        confirmBtn.addEventListener("click", async () => {
            const popup = overlay.querySelector(".payment-popup");
            if (!popup)
                return;
            // Afficher un indicateur de chargement
            const originalText = confirmBtn.textContent;
            confirmBtn.textContent = "Traitement en cours...";
            confirmBtn.disabled = true;
            try {
                // Vérifier que le chiffrement a fonctionné
                if (!window.vignere) {
                    throw new Error("Système de sécurité non disponible");
                }
                // Récupérer l'ID de l'adresse de facturation depuis window
                const idAdresseFact = window.idAdresseFacturation || null;
                // Appeler l'API pour créer la commande
                const orderData = {
                    adresseLivraison: adresse,
                    villeLivraison: ville,
                    regionLivraison: region,
                    numeroCarte: numeroCarteChiffre,
                    cvv: cvvChiffre,
                    nomCarte: nomCarte,
                    dateExpiration: dateCarte,
                    codePostal: codePostal,
                };
                // AJOUT: Inclure l'ID de l'adresse de facturation si disponible
                if (idAdresseFact) {
                    orderData.idAdresseFacturation = idAdresseFact;
                    console.log("Utilisation de l'adresse de facturation ID:", idAdresseFact);
                }
                else {
                    console.log("Aucune adresse de facturation spécifique, utilisation de l'adresse de livraison");
                }
                // Utiliser PaymentAPI s'il existe, sinon faire un fetch direct
                let result;
                if (window.PaymentAPI &&
                    typeof window.PaymentAPI.createOrder === "function") {
                    result = await window.PaymentAPI.createOrder(orderData);
                }
                else {
                    // Fallback: appel direct
                    const response = await fetch("", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/x-www-form-urlencoded",
                        },
                        body: new URLSearchParams(orderData).toString(),
                    });
                    result = await response.json();
                }
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
                        window.location.reload();
                    });
                }
                else {
                    throw new Error(result.error || "Erreur inconnue lors de la création de la commande");
                }
            }
            catch (error) {
                console.error("Erreur:", error);
                alert("Erreur lors de la création de la commande: " +
                    (error instanceof Error ? error.message : String(error)));
                // Réactiver le bouton
                confirmBtn.textContent = originalText;
                confirmBtn.disabled = false;
            }
        });
        // Fermer en cliquant en dehors du popup
        overlay.addEventListener("click", (e) => {
            if (e.target === overlay) {
                removeOverlay();
            }
        });
        // Fermer avec la touche Escape
        const handleEscape = (e) => {
            if (e.key === "Escape") {
                removeOverlay();
                document.removeEventListener("keydown", handleEscape);
            }
        };
        document.addEventListener("keydown", handleEscape);
        // Nettoyer l'écouteur lors de la suppression
        const originalRemove = removeOverlay;
        removeOverlay = () => {
            document.removeEventListener("keydown", handleEscape);
            originalRemove();
        };
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
        const departments = new Map();
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
        // Variable pour stocker l'ID de l'adresse de facturation
        let idAdresseFacturation = null;
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
        const factAdresseCheckbox = document.querySelector("#checkboxFactAddr");
        if (factAdresseCheckbox) {
            factAdresseCheckbox.checked = false;
        }
        // Gestion des événements de l'overlay
        const validerAddrFactBtn = addrFactOverlay.querySelector("#validerAddrFact");
        validerAddrFactBtn?.addEventListener("click", async () => {
            const adresseFactInput = addrFactOverlay.querySelector(".adresse-fact-input");
            const codePostalFactInput = addrFactOverlay.querySelector(".code-postal-fact-input");
            const villeFactInput = addrFactOverlay.querySelector(".ville-fact-input");
            // Validation basique
            if (!adresseFactInput.value.trim() ||
                !codePostalFactInput.value.trim() ||
                !villeFactInput.value.trim()) {
                (0, paiement_popup_1.showPopup)("Veuillez remplir tous les champs de l'adresse de facturation", "error");
                return;
            }
            // Validation du code postal
            const codePostal = codePostalFactInput.value.trim();
            if (!/^\d{5}$/.test(codePostal)) {
                (0, paiement_popup_1.showPopup)("Le code postal doit contenir 5 chiffres", "error");
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
                    (0, paiement_popup_1.showPopup)(result.message || "Adresse de facturation enregistrée avec succès", "success");
                    addrFactOverlay.style.display = "none";
                    console.log("Adresse de facturation enregistrée avec ID:", idAdresseFacturation);
                    // Décocher la checkbox après validation
                    const factAdresseCheckbox = document.querySelector("#checkboxFactAddr");
                    if (factAdresseCheckbox) {
                        factAdresseCheckbox.checked = false;
                    }
                }
                else {
                    (0, paiement_popup_1.showPopup)("Erreur lors de l'enregistrement: " + result.error, "error");
                }
            }
            catch (error) {
                console.error("Erreur complète:", error);
                (0, paiement_popup_1.showPopup)("Erreur réseau lors de l'enregistrement", "error");
            }
        });
        const closeAddrFactBtn = addrFactOverlay.querySelector("#closeAddrFact");
        closeAddrFactBtn?.addEventListener("click", () => {
            addrFactOverlay.style.display = "none";
            // Décocher la checkbox
            const factAdresseCheckbox = document.querySelector("#checkboxFactAddr");
            if (factAdresseCheckbox) {
                factAdresseCheckbox.checked = false;
            }
        });
        // Fermer en cliquant en dehors du contenu
        addrFactOverlay.addEventListener("click", (e) => {
            if (e.target === addrFactOverlay) {
                addrFactOverlay.style.display = "none";
                const factAdresseCheckbox = document.querySelector("#checkboxFactAddr");
                if (factAdresseCheckbox) {
                    factAdresseCheckbox.checked = false;
                }
            }
        });
        // Gestion de la checkbox
        const factAdresseInput = document.querySelector("#checkboxFactAddr");
        factAdresseInput?.addEventListener("change", (e) => {
            const isChecked = e.target.checked;
            if (isChecked) {
                addrFactOverlay.style.display = "flex";
                // Focus sur le premier champ
                const firstInput = addrFactOverlay.querySelector("input");
                if (firstInput) {
                    firstInput.focus();
                }
            }
            else {
                addrFactOverlay.style.display = "none";
            }
        });
        // Gestion des boutons payer
        payerButtons.forEach((btn) => {
            btn.addEventListener("click", (e) => {
                e.preventDefault();
                // Stocker l'ID de facturation dans window pour qu'il soit accessible par showPopup
                window.idAdresseFacturation = idAdresseFacturation;
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
                    (0, paiement_popup_1.showPopup)("Validation des informations", "info");
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
    }
});
//# sourceMappingURL=script.js.map
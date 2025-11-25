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
const inputSeuil = document.getElementById('seuil');
const inputDate = document.getElementById('dateReassort');
const inputReassort = document.getElementById('reassort');
const buttonConfirm = document.getElementById('buttonConfirm');
const errorFieldSeuil = document.getElementById('errorFieldSeuil');
const errorFieldReassort = document.getElementById('errorFieldReassort');
const errorFieldDate = document.getElementById('errorFieldDate');
const buttonCancel = Array.from(document.querySelectorAll('main.backoffice-stocks .annuler'));
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
buttonCancel.forEach((btnCancel) => {
    btnCancel.addEventListener('click', () => {
        Array.from(document.getElementsByTagName('dialog')).forEach(dia => {
            dia.close();
            dia.style.display = 'none';
        });
    });
});
btnSettings.forEach(btn => {
    btn.addEventListener('click', () => {
        const modal = document.querySelector(`main.backoffice-stocks dialog#d-${btn.id}`);
        if (!modal) {
            console.error('Dialog non trouvé');
            return;
        }
        modal.showModal();
        modal.style.display = 'flex';
        modal.addEventListener("click", (e) => {
            if (e.target === modal) {
                modal.close();
                modal.style.display = 'none';
            }
        });
    });
});
function checkInt(value) {
    if (!value)
        return true;
    let intValue = parseInt(value);
    return !isNaN(intValue) && intValue >= 0;
}
function checkDate(date) {
    if (!date)
        return true;
    let now = new Date();
    now.setHours(0, 0, 0, 0);
    return now.getTime() < date.getTime();
}
function allValid() {
    return checkInt(inputSeuil.value) && checkDate(inputDate.valueAsDate) && checkInt(inputReassort.value);
}
inputSeuil?.addEventListener('input', () => {
    if (!checkInt(inputSeuil.value)) {
        inputSeuil.style.cssText = 'border-color: #f14e4e !important';
        errorFieldSeuil.style.display = 'block';
    }
    else {
        inputSeuil.style.cssText = 'border-color: #273469 !important';
        errorFieldSeuil.style.display = 'none';
    }
    buttonConfirm.disabled = !allValid();
});
inputDate?.addEventListener('input', () => {
    if (!checkDate(inputDate.valueAsDate)) {
        inputDate.style.cssText = 'border-color: #f14e4e !important';
        errorFieldDate.style.display = 'block';
    }
    else {
        inputDate.style.cssText = 'border-color: #273469 !important';
        errorFieldDate.style.display = 'none';
    }
    buttonConfirm.disabled = !allValid();
});
inputReassort?.addEventListener('input', () => {
    if (!checkInt(inputReassort.value)) {
        inputReassort.style.cssText = 'border-color: #f14e4e !important';
        errorFieldReassort.style.display = 'block';
    }
    else {
        inputReassort.style.cssText = 'border-color: #273469 !important';
        errorFieldReassort.style.display = 'none';
    }
    buttonConfirm.disabled = !allValid();
});
define("frontoffice/paiement-types", ["require", "exports"], function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
});
define("frontoffice/paiement-validation", ["require", "exports"], function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setError = setError;
    exports.clearError = clearError;
    exports.cardVerification = cardVerification;
    exports.isVisa = isVisa;
    exports.validateAll = validateAll;
    function setError(el, message) {
        // Affiche un message d'erreur associé à un élément (input).
        // - Ajoute la classe `invalid` à l'élément pour le style visuel.
        // - Cherche (ou crée) un élément <small> avec la classe `error-message`
        //   pour y placer le texte d'erreur.
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
        // Efface l'erreur visuelle et le texte associé à un élément.
        // - Retire la classe `invalid` et vide le contenu du message d'erreur.
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
        // Vérification Luhn du numéro de carte.
        // - Supprime les espaces et s'assure que la chaîne n'est composée que de
        //   chiffres.
        // - Applique l'algorithme de Luhn pour valider la somme de contrôle.
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
        // Détecte si le numéro appartient à Visa (commence par 4) et valide via
        // Luhn.
        const clean = cardNumber.replace(/\s+/g, "");
        return /^4\d{12}(?:\d{3})?$/.test(clean) && cardVerification(clean);
    }
    function validateAll({ inputs, departments, postals, cart, selectedDepartment, }) {
        let ok = true;
        const { adresseInput, codePostalInput, villeInput, numCarteInput, nomCarteInput, carteDateInput, cvvInput, recapEl, } = inputs;
        // adresse
        // Validation de l'adresse: présence et longueur minimale raisonnable.
        if (!adresseInput || adresseInput.value.trim().length < 5) {
            setError(adresseInput, "Veuillez renseigner une adresse complète.");
            ok = false;
        }
        else
            clearError(adresseInput);
        // Validation du code postal ou du numéro de département.
        // - Accepte soit 2 chiffres (département) soit 5 chiffres (code postal).
        // - Si c'est un code postal (5 chiffres) on vérifie si la commune
        //   correspond via `postals`; sinon on essaie d'extraire le département
        //   et de vérifier sa présence dans `departments`.
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
        // Pour la ville on se contente d'effacer d'éventuelles erreurs précédentes.
        if (villeInput)
            clearError(villeInput);
        // numéro de carte - messages plus détaillés
        // Validation du numéro de carte: présence, format numérique, longueur,
        // préfixe Visa (commence par 4) et contrôle Luhn via `cardVerification`.
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
        // Validation du nom sur la carte: au moins 2 caractères et sans chiffres.
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
        // Date d'expiration: accepte MM/AA ou MM/AAAA.
        // - Vérifie la présence, le format, la validité du mois puis compare la
        //   date d'expiration à la date actuelle.
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
        // CVV: doit être exactement 3 chiffres.
        if (!cvvInput || !/^\d{3}$/.test(cvvInput.value.trim())) {
            setError(cvvInput, "CVV invalide (3 chiffres). ");
            ok = false;
        }
        else
            clearError(cvvInput);
        // Validation des conditions générales
        const cgvCheckbox = document.querySelector('input[type="checkbox"][aria-label="conditions générales"]');
        if (!cgvCheckbox || !cgvCheckbox.checked) {
            // Trouver le conteneur de la checkbox pour afficher l'erreur
            const conditionsSection = document.querySelector("section.conditions");
            if (conditionsSection) {
                // Supprimer l'ancien message d'erreur s'il existe
                const oldError = conditionsSection.querySelector(".error-message-cgv");
                if (oldError)
                    oldError.remove();
                // Créer le message d'erreur
                const errorMsg = document.createElement("small");
                errorMsg.className = "error-message error-message-cgv";
                errorMsg.style.color = "#f14e4e";
                errorMsg.style.display = "block";
                errorMsg.style.marginTop = "8px";
                errorMsg.textContent =
                    "Vous devez accepter les conditions générales pour continuer.";
                conditionsSection.appendChild(errorMsg);
                // Ajouter une classe pour mettre en évidence
                if (cgvCheckbox) {
                    cgvCheckbox.style.outline = "2px solid #f14e4e";
                }
            }
            ok = false;
        }
        else {
            // Effacer l'erreur si les conditions sont acceptées
            const conditionsSection = document.querySelector("section.conditions");
            if (conditionsSection) {
                const errorMsg = conditionsSection.querySelector(".error-message-cgv");
                if (errorMsg)
                    errorMsg.remove();
            }
            if (cgvCheckbox) {
                cgvCheckbox.style.outline = "";
            }
        }
        // Vérifie que le panier contient au moins un élément.
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
define("frontoffice/paiement-autocomplete", ["require", "exports", "frontoffice/paiement-validation"], function (require, exports, paiement_validation_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setupAutocomplete = setupAutocomplete;
    function createSuggestionBox(input) {
        // Crée la boîte de suggestions attachée au parent de
        // l'input. La boîte est stylée ici en inline pour s'assurer qu'elle
        // apparaisse correctement au-dessus du flux normal de la page.
        // Retourne l'élément `.suggestions` prêt à recevoir des éléments.
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
            // Affiche des suggestions pour le champ code postal / département.
            // - Recherche dans `maps.departments` et `maps.postals` en fonction de la
            //   requête (préfixe ou inclusion).
            // - Construit des éléments cliquables qui remplissent le champ et
            //   mettent à jour `selectedDepartment`.
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
                    // Lors du clic, on récupère la clé (code postal ou numéro de
                    // département) avant le séparateur ` - ` et on met à jour l'input
                    // ainsi que `selectedDepartment`.
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
            // Suggestions pour le champ ville.
            // - Si `selectedDepartment` est renseigné, on privilégie les villes de
            //   ce département via `maps.citiesByCode`, sinon on cherche dans
            //   l'ensemble `maps.allCities`.
            // - Propose d'utiliser la valeur tapée si aucune suggestion n'est
            //   disponible.
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
                // Aucun résultat: proposer d'utiliser la valeur tapée ou indiquer
                // qu'il n'y a pas de suggestion.
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
                // Permettre explicitement d'utiliser la valeur saisie si elle n'est
                // pas exactement égale à une suggestion.
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
define("frontoffice/paiement-popup", ["require", "exports"], function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.showPopup = showPopup;
    // Fonction helper pour chiffrer avec Vigenère si disponible.
    // sens = 1 pour chiffrement, sens = -1 (ou autre) pour déchiffrement selon implémentation globale.
    const chiffrerAvecVignere = (texte, sens) => {
        // Clé par défaut si aucune fournie via window
        const cle = window.CLE_CHIFFREMENT || "?zu6j,xX{N12I]0r6C=v57IoASU~?6_y";
        // Utilise la fonction vignere globale si elle existe et que la clé est valide
        if (typeof window.vignere === "function" && cle && cle.length > 0) {
            return window.vignere(texte, cle, sens);
        }
        // Si pas de fonction de chiffrement, log et retourne le texte en clair.
        console.warn("Fonction vignere non disponible ou clé invalide, retour du texte en clair");
        return texte;
    };
    // Fonction utilitaire pour encoder des données en application/x-www-form-urlencoded
    // (non utilisée dans la version finale qui utilise FormData, mais conservée pour référence)
    const encodeFormData = (data) => {
        const formData = new URLSearchParams();
        formData.append("action", "createOrder");
        Object.keys(data).forEach((key) => {
            if (key !== "action") {
                // Convertit la valeur en chaîne et l'ajoute aux paramètres
                const value = String(data[key]);
                formData.append(key, value);
            }
        });
        return formData.toString();
    };
    // Fonction principale exportée : affiche un popup récapitulatif de commande.
    // message : texte à afficher (pas utilisé intensément ici, conservé pour extensibilité)
    // type : style du popup ("error" | "success" | "info")
    function showPopup(message, type = "info") {
        // Création d'un overlay couvrant la page, classé par type pour le style
        const overlay = document.createElement("div");
        overlay.className = `payment-overlay ${type}`;
        // Lecture des inputs présents dans la page (sélecteurs ciblés pour pagePaiement)
        const adresseInput = document.querySelector("body.pagePaiement .adresse-input");
        const codePostalInput = document.querySelector("body.pagePaiement .code-postal-input");
        const villeInput = document.querySelector("body.pagePaiement .ville-input");
        const numCarteInput = document.querySelector("body.pagePaiement .num-carte");
        const nomCarteInput = document.querySelector("body.pagePaiement .nom-carte");
        const carteDateInput = document.querySelector("body.pagePaiement .carte-date");
        const cvvInput = document.querySelector("body.pagePaiement .cvv-input");
        // Extraction et normalisation des valeurs des champs (trim, suppression d'espaces pour numéro de carte)
        const adresse = adresseInput?.value.trim() || "";
        const codePostal = codePostalInput?.value.trim() || "";
        const ville = villeInput?.value.trim() || "";
        const rawNumCarte = numCarteInput?.value.replace(/\s+/g, "") || "";
        const nomCarte = nomCarteInput?.value.trim() || "";
        const dateCarte = carteDateInput?.value.trim() || "";
        const rawCVV = cvvInput?.value.trim() || "";
        // Vérification simple que tous les champs requis sont renseignés avant d'ouvrir le popup
        if (!adresse ||
            !codePostal ||
            !ville ||
            !rawNumCarte ||
            !nomCarte ||
            !dateCarte ||
            !rawCVV) {
            alert("Veuillez remplir tous les champs obligatoires");
            return;
        }
        // CHIFFREMENT DES DONNÉES SENSIBLES via la fonction chiffrerAvecVignere (si disponible)
        const numeroCarteChiffre = chiffrerAvecVignere(rawNumCarte, 1);
        const cvvChiffre = chiffrerAvecVignere(rawCVV, 1);
        // Conserver les 4 derniers chiffres pour l'affichage dans le récapitulatif
        const last4 = rawNumCarte.length >= 4 ? rawNumCarte.slice(-4) : rawNumCarte;
        // Détermination d'une région simple à partir du code postal (ex : Département XX)
        let region = "";
        if (codePostal.length >= 2) {
            const codeDept = codePostal.length === 5
                ? codePostal.slice(0, 2)
                : codePostal.padStart(2, "0");
            region = `Département ${codeDept}`;
        }
        // Récupération du panier injecté via window.__PAYMENT_DATA__.cart si présent
        const preCart = Array.isArray(window.__PAYMENT_DATA__?.cart)
            ? window.__PAYMENT_DATA__.cart
            : [];
        let cartItemsHtml = "";
        // Construction du HTML du panier (images / titres / quantités / prix)
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
            // Message si panier vide
            cartItemsHtml = `<p class="empty">Panier vide</p>`;
        }
        // Injection du contenu HTML du popup dans l'overlay
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
        // Ajout de l'overlay au DOM
        document.body.appendChild(overlay);
        // Récupération des boutons du popup pour attacher les événements
        const closeBtn = overlay.querySelector(".close-popup");
        const undoBtn = overlay.querySelector(".undo");
        const confirmBtn = overlay.querySelector(".confirm");
        // Fonction utilitaire de suppression de l'overlay du DOM
        let removeOverlay = () => {
            if (document.body.contains(overlay)) {
                document.body.removeChild(overlay);
            }
        };
        // Fermeture simple via bouton fermer ou annuler
        closeBtn?.addEventListener("click", removeOverlay);
        undoBtn?.addEventListener("click", removeOverlay);
        // Si le bouton confirmer n'existe pas, on stoppe
        if (!confirmBtn)
            return;
        // Handler pour le clic sur Confirmer ma commande
        confirmBtn.addEventListener("click", async () => {
            const popup = overlay.querySelector(".payment-popup");
            if (!popup)
                return;
            // Indicateur visuel de traitement : désactive le bouton et change le texte
            const originalText = confirmBtn.textContent;
            confirmBtn.textContent = "Traitement en cours...";
            confirmBtn.disabled = true;
            try {
                // Vérification que la sécurité (vignere) est disponible
                if (!window.vignere) {
                    throw new Error("Système de sécurité non disponible");
                }
                // Re-vérification des champs requis (sécurité côté client)
                if (!adresse ||
                    !codePostal ||
                    !ville ||
                    !rawNumCarte ||
                    !nomCarte ||
                    !dateCarte ||
                    !rawCVV) {
                    throw new Error("Tous les champs sont obligatoires");
                }
                // Récupérer l'ID de l'adresse de facturation si défini globalement
                const idAdresseFact = window.idAdresseFacturation || null;
                // Préparation des données de la commande (inclut les versions chiffrées)
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
                // Inclut l'ID de facturation si disponible
                if (idAdresseFact) {
                    orderData.idAdresseFacturation = idAdresseFact;
                    console.log("Utilisation de l'adresse de facturation ID:", idAdresseFact);
                }
                // Résultat de l'appel vers le serveur ou l'API de paiement
                let result;
                // Si une API de paiement globale est fournie, on l'utilise en priorité
                if (window.PaymentAPI &&
                    typeof window.PaymentAPI.createOrder === "function") {
                    console.log("Utilisation de PaymentAPI");
                    result = await window.PaymentAPI.createOrder(orderData);
                }
                else {
                    // Sinon, fallback vers un fetch POST direct en utilisant FormData pour l'encodage
                    console.log("Utilisation de fetch direct");
                    // FormData gère correctement l'encodage des champs pour un POST multipart/form-data
                    const formData = new FormData();
                    formData.append("action", "createOrder");
                    // Ajout des champs de orderData à la FormData
                    Object.keys(orderData).forEach((key) => {
                        formData.append(key, orderData[key]);
                    });
                    // Appel fetch vers l'URL courante (chaîne vide => la même page) :
                    const response = await fetch("", {
                        method: "POST",
                        body: formData, // FormData gère automatiquement l'encodage
                    });
                    // Vérification du statut HTTP
                    if (!response.ok) {
                        throw new Error(`Erreur HTTP: ${response.status}`);
                    }
                    // Tentative de parsing JSON de la réponse
                    result = await response.json();
                }
                // Gestion de la réponse : si succès, afficher un message de remerciement
                if (result.success) {
                    popup.innerHTML = `
        <div class="thank-you">
          <h2>Merci de votre commande !</h2>
          <p>Votre commande a bien été enregistrée.</p>
          <p><strong>Numéro de commande :</strong> ${result.idCommande}</p>
          <button class="close-popup">Retour à l'accueil</button>
        </div>
      `;
                    // Bouton interne pour rediriger vers l'accueil (ici un chemin relatif)
                    const innerClose = popup.querySelector(".close-popup");
                    innerClose?.addEventListener("click", () => {
                        // Redirection vers la page d'accueil connectée
                        window.location.href = "../../views/frontoffice/accueilConnecte.php";
                    });
                }
                else {
                    // Si result.success falsy, lever une erreur avec le message renvoyé
                    throw new Error(result.error || "Erreur inconnue lors de la création de la commande");
                }
            }
            catch (error) {
                // Log détaillé pour debug
                console.error("Erreur complète:", error);
                // Construire un message d'erreur utilisateur plus lisible
                let errorMessage = "Erreur lors de la création de la commande";
                if (error instanceof Error) {
                    if (error.message.includes("SyntaxError")) {
                        errorMessage = "Erreur de format des données. Veuillez réessayer.";
                    }
                    else if (error.message.includes("HTTP")) {
                        errorMessage = "Erreur de communication avec le serveur.";
                    }
                    else {
                        errorMessage = error.message;
                    }
                }
                // Afficher le message d'erreur au client
                alert(errorMessage);
                // Réactiver le bouton confirmer et restaurer le texte original
                confirmBtn.textContent = originalText;
                confirmBtn.disabled = false;
            }
        });
        // Fermeture du popup en cliquant sur l'overlay (en dehors du popup)
        overlay.addEventListener("click", (e) => {
            if (e.target === overlay) {
                removeOverlay();
            }
        });
        // Gestion de la touche Escape pour fermer le popup
        const handleEscape = (e) => {
            if (e.key === "Escape") {
                removeOverlay();
                document.removeEventListener("keydown", handleEscape);
            }
        };
        document.addEventListener("keydown", handleEscape);
        // Nettoyage : s'assurer que l'écouteur sur keydown est supprimé lorsque l'overlay est retiré
        const originalRemove = removeOverlay;
        removeOverlay = () => {
            document.removeEventListener("keydown", handleEscape);
            originalRemove();
        };
    }
});
// Fichier principal gérant la logique du paiement (autocomplétion, validation,
// enregistrement d'adresse de facturation, gestion des boutons "payer", etc.)
define("frontoffice/paiement-main", ["require", "exports", "frontoffice/paiement-validation", "frontoffice/paiement-autocomplete", "frontoffice/paiement-popup"], function (require, exports, paiement_validation_2, paiement_autocomplete_1, paiement_popup_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    // Ne rien faire si on n'est pas sur la page de paiement
    if (document.body.classList.contains("pagePaiement")) {
        // ========================================================================
        // Sélection des éléments du DOM
        // ========================================================================
        const adresseInput = document.querySelector("body.pagePaiement .adresse-input");
        const codePostalInput = document.querySelector("body.pagePaiement .code-postal-input");
        const villeInput = document.querySelector("body.pagePaiement .ville-input");
        const numCarteInput = document.querySelector("body.pagePaiement .num-carte");
        const nomCarteInput = document.querySelector("body.pagePaiement .nom-carte");
        const carteDateInput = document.querySelector("body.pagePaiement .carte-date");
        const cvvInput = document.querySelector("body.pagePaiement .cvv-input");
        // Tous les boutons "payer" présents sur la page
        const payerButtons = Array.from(document.querySelectorAll("body.pagePaiement .payer"));
        // Elément récapitulatif (souvent utilisé pour afficher le résumé de commande)
        const recapEl = document.getElementById("recap");
        // ========================================================================
        // Structures de données pour l'autocomplétion des villes / codes postaux
        // ========================================================================
        const departments = new Map();
        const citiesByCode = new Map();
        const allCities = new Set();
        const postals = new Map();
        const selectedDepartment = { value: null };
        // Données préchargées (injectées côté serveur dans window.__PAYMENT_DATA__)
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
        // ========================================================================
        // Chargement du panier depuis les données préchargées
        // ========================================================================
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
        // ========================================================================
        // Initialisation de l'autocomplétion (module séparé)
        // ========================================================================
        (0, paiement_autocomplete_1.setupAutocomplete)({
            codePostalInput,
            villeInput,
            maps: { departments, citiesByCode, postals, allCities },
            selectedDepartment,
        });
        // ========================================================================
        // Overlay pour saisir / enregistrer une adresse de facturation
        // ========================================================================
        // Variable pour stocker l'ID renvoyé par le serveur une fois l'adresse enregistrée
        let idAdresseFacturation = null;
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
        const factAdresseCheckbox = document.querySelector("#checkboxFactAddr");
        if (factAdresseCheckbox) {
            factAdresseCheckbox.checked = false;
        }
        // ========================================================================
        // Gestion des actions dans l'overlay (validation / enregistrement)
        // ========================================================================
        const validerAddrFactBtn = addrFactOverlay.querySelector("#validerAddrFact");
        // Clic sur "Valider" -> validation client, envoi au serveur, stockage de l'ID
        validerAddrFactBtn?.addEventListener("click", async () => {
            const adresseFactInput = addrFactOverlay.querySelector(".adresse-fact-input");
            const codePostalFactInput = addrFactOverlay.querySelector(".code-postal-fact-input");
            const villeFactInput = addrFactOverlay.querySelector(".ville-fact-input");
            // Validation basique des champs (non vides)
            if (!adresseFactInput.value.trim() ||
                !codePostalFactInput.value.trim() ||
                !villeFactInput.value.trim()) {
                (0, paiement_popup_1.showPopup)("Veuillez remplir tous les champs de l'adresse de facturation", "error");
                return;
            }
            // Validation simple du format du code postal (5 chiffres)
            const codePostal = codePostalFactInput.value.trim();
            if (!/^\d{5}$/.test(codePostal)) {
                (0, paiement_popup_1.showPopup)("Le code postal doit contenir 5 chiffres", "error");
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
                    (0, paiement_popup_1.showPopup)(result.message || "Adresse de facturation enregistrée avec succès", "success");
                    // Fermer l'overlay
                    addrFactOverlay.style.display = "none";
                    console.log("Adresse de facturation enregistrée avec ID:", idAdresseFacturation);
                    // Décocher la checkbox associée après enregistrement
                    const factAdresseCheckbox = document.querySelector("#checkboxFactAddr");
                    if (factAdresseCheckbox) {
                        factAdresseCheckbox.checked = false;
                    }
                }
                else {
                    // Afficher une erreur renvoyée par le serveur
                    (0, paiement_popup_1.showPopup)("Erreur lors de l'enregistrement: " + result.error, "error");
                }
            }
            catch (error) {
                // Gestion d'erreur réseau / JSON
                console.error("Erreur complète:", error);
                (0, paiement_popup_1.showPopup)("Erreur réseau lors de l'enregistrement", "error");
            }
        });
        // Bouton "Annuler" -> fermer l'overlay et décocher la checkbox
        const closeAddrFactBtn = addrFactOverlay.querySelector("#closeAddrFact");
        closeAddrFactBtn?.addEventListener("click", () => {
            addrFactOverlay.style.display = "none";
            const factAdresseCheckbox = document.querySelector("#checkboxFactAddr");
            if (factAdresseCheckbox) {
                factAdresseCheckbox.checked = false;
            }
        });
        // Cliquer hors du contenu de l'overlay ferme l'overlay
        addrFactOverlay.addEventListener("click", (e) => {
            if (e.target === addrFactOverlay) {
                addrFactOverlay.style.display = "none";
                const factAdresseCheckbox = document.querySelector("#checkboxFactAddr");
                if (factAdresseCheckbox) {
                    factAdresseCheckbox.checked = false;
                }
            }
        });
        // ========================================================================
        // Gestion de la checkbox qui affiche l'overlay d'adresse de facturation
        // ========================================================================
        const factAdresseInput = document.querySelector("#checkboxFactAddr");
        factAdresseInput?.addEventListener("change", (e) => {
            const isChecked = e.target.checked;
            if (isChecked) {
                // Afficher l'overlay et focus sur le premier champ pour une saisie rapide
                addrFactOverlay.style.display = "flex";
                const firstInput = addrFactOverlay.querySelector("input");
                if (firstInput) {
                    firstInput.focus();
                }
            }
            else {
                // Masquer si décoché
                addrFactOverlay.style.display = "none";
            }
        });
        // ========================================================================
        // Gestion de la checkbox des conditions générales (CGV)
        // ========================================================================
        const cgvCheckbox = document.querySelector('input[type="checkbox"][aria-label="conditions générales"]');
        cgvCheckbox?.addEventListener("change", () => {
            if (cgvCheckbox.checked) {
                // Supprimer un éventuel message d'erreur lié aux CGV et réinitialiser le style
                const conditionsSection = document.querySelector("section.conditions");
                if (conditionsSection) {
                    const errorMsg = conditionsSection.querySelector(".error-message-cgv");
                    if (errorMsg)
                        errorMsg.remove();
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
                window.idAdresseFacturation = idAdresseFacturation;
                // Appel à la validation globale (module séparé)
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
                    // Informations valides -> afficher un message d'état
                    (0, paiement_popup_1.showPopup)("Validation des informations", "info");
                }
                else {
                    // En cas d'erreur, scroller vers le premier champ invalide
                    const first = document.querySelector(".invalid");
                    if (first)
                        first.scrollIntoView({
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
            const target = ev.target;
            document.querySelectorAll(".suggestions").forEach((s) => {
                if (!target)
                    return;
                const parent = s.parentElement || null;
                if (!parent)
                    return;
                if (target === parent || parent.contains(target)) {
                    // clic à l'intérieur de la zone -> ne pas masquer
                }
                else {
                    // clic à l'extérieur -> masquer la liste de suggestions
                    s.style.display = "none";
                }
            });
        });
    }
});
//# sourceMappingURL=script.js.map
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
define("frontoffice/paiement-main", ["require", "exports", "frontoffice/paiement-autocomplete"], function (require, exports, paiement_autocomplete_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    if (document.body.classList.contains("pagePaiement")) {
        // Initialisation des données
        const departments = new Map();
        const citiesByCode = new Map();
        const allCities = new Set();
        const postals = new Map();
        const selectedDepartment = { value: null };
        // Chargement des données préchargées
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
        // Récupération des éléments d'entrée
        const codePostalInput = document.querySelector("body.pagePaiement .code-postal-input");
        const villeInput = document.querySelector("body.pagePaiement .ville-input");
        // Initialisation de l'autocomplétion
        if (codePostalInput && villeInput) {
            (0, paiement_autocomplete_1.setupAutocomplete)({
                codePostalInput,
                villeInput,
                maps: { departments, citiesByCode, postals, allCities },
                selectedDepartment,
            });
        }
    }
});
//# sourceMappingURL=script.js.map
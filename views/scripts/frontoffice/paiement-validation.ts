// ============================================================================
// VALIDATION FUNCTIONS
// ============================================================================

import { CartItem, Inputs, ValidateAllParams } from "./paiement-types";

export function setError(el: Element | null, message: string) {
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

export function clearError(el: Element | null) {
  if (!el) return;
  el.classList.remove("invalid");
  const container = el.parentElement;
  if (!container) return;
  const err = container.querySelector(".error-message") as HTMLElement | null;
  if (err) err.textContent = "";
}

export function cardVerification(cardNumber: string): boolean {
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

export function isVisa(cardNumber: string) {
  const clean = cardNumber.replace(/\s+/g, "");
  return /^4\d{12}(?:\d{3})?$/.test(clean) && cardVerification(clean);
}

export function validateAll({
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
// ============================================================================
// AUTOCOMPLETE
// ============================================================================

import { Maps } from "./paiement-types";
import { clearError } from "./paiement-validation";

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

export function setupAutocomplete(params: {
  codePostalInput: HTMLInputElement | null;
  villeInput: HTMLInputElement | null;
  maps: Maps;
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
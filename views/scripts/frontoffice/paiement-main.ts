if (document.body.classList.contains("pagePaiement")) {
  // Initialisation des données
  const departments = new Map<string, string>();
  const citiesByCode = new Map<string, Set<string>>();
  const allCities = new Set<string>();
  const postals = new Map<string, Set<string>>();
  const selectedDepartment = { value: null as string | null };

  // Chargement des données préchargées
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

  // Récupération des éléments d'entrée
  const codePostalInput = document.querySelector(
    "body.pagePaiement .code-postal-input"
  ) as HTMLInputElement | null;
  const villeInput = document.querySelector(
    "body.pagePaiement .ville-input"
  ) as HTMLInputElement | null;
}

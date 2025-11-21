export interface CartItem {
  id: string;
  nom: string;
  prix: number;
  qty: number;
  img: string;
}

export interface Inputs {
  adresseInput: HTMLInputElement | null;
  codePostalInput: HTMLInputElement | null;
  villeInput: HTMLInputElement | null;
  numCarteInput: HTMLInputElement | null;
  nomCarteInput: HTMLInputElement | null;
  carteDateInput: HTMLInputElement | null;
  cvvInput: HTMLInputElement | null;
  recapEl: HTMLElement | null;
}

export interface ValidateAllParams {
  inputs: Inputs;
  departments: Map<string, string>;
  postals: Map<string, Set<string>>;
  cart: CartItem[];
  selectedDepartment: { value: string | null };
}

export interface Maps {
  departments: Map<string, string>;
  citiesByCode: Map<string, Set<string>>;
  postals: Map<string, Set<string>>;
  allCities: Set<string>;
}

export interface OrderData {
  adresseLivraison: string;
  villeLivraison: string;
  regionLivraison: string;
  numeroCarte: string;
}

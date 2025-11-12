// Shared types for the paiement frontoffice scripts
export type CartItem = {
  id: string;
  title: string;
  price: number;
  qty: number;
  img?: string;
};

export type AsideHandle = {
  update: (cart: CartItem[]) => void;
  getElement: () => HTMLElement | null;
};

export type Inputs = {
  adresseInput: HTMLInputElement | null;
  codePostalInput: HTMLInputElement | null;
  villeInput: HTMLInputElement | null;
  numCarteInput: HTMLInputElement | null;
  nomCarteInput: HTMLInputElement | null;
  carteDateInput: HTMLInputElement | null;
  cvvInput: HTMLInputElement | null;
  recapEl: HTMLElement | null;
};

export type ValidateAllParams = {
  inputs: Inputs;
  departments: Map<string, string>;
  postals: Map<string, Set<string>>;
  cart: CartItem[];
  // wrapper so caller and callee can share/mutate the selected department
  selectedDepartment: { value: string | null };
};

export type Maps = {
  departments: Map<string, string>;
  citiesByCode: Map<string, Set<string>>;
  postals: Map<string, Set<string>>;
  allCities: Set<string>;
};

export default {};

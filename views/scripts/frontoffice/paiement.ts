const numCarteInput = document.querySelector(
  "body.pagePaiement .num-carte"
) as HTMLInputElement | null;

// Exemple de numéro de carte pour tester l'algorithme de Luhn
const userCardNumber = "4904 8398 2248 5959";

// Vérification de la validité du numéro de carte bancaire avec l'algorithme de Luhn
function cardVerification(cardNumber: string): boolean {
  cardNumber = cardNumber.replace(/\s+/g, "");
  if (cardNumber.length === 0 || !/^\d+$/.test(cardNumber)) {
    return false;
  }

  const arrayCardNumber: number[] = cardNumber
    .split("")
    .reverse()
    .map((d) => Number(d));

  let somme: number = 0;

  for (let i = 1; i < arrayCardNumber.length; i += 2) {
    let impNb = arrayCardNumber[i] * 2;
    if (impNb > 9) {
      impNb -= 9;
    }
    arrayCardNumber[i] = impNb;
  }

  for (let i = 0; i < arrayCardNumber.length; i++) {
    somme += arrayCardNumber[i];
  }

  return somme % 10 === 0;
}

const isValid = cardVerification(userCardNumber);

console.log("Carte valide:", isValid);

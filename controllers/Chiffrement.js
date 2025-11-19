const alphabet = [];
for (let i = 32; i <= 126; i++) {
  if (i !== 47 && i !== 92) { 
    alphabet.push(String.fromCharCode(i));
  }
}

const alphabetSize = alphabet.length;

function indexInAlphabet(char) {
  return alphabet.indexOf(char);
}

function convert(char, cleChar, sens) {
  const iChar = indexInAlphabet(char);
  if (iChar === -1) return char; 

  const iCle = indexInAlphabet(cleChar);
  if (iCle === -1) return char; 

  let newIndex;
  if (sens === 1) {
    newIndex = (iChar + iCle) % alphabetSize;
  } else {
    newIndex = (iChar - iCle + alphabetSize) % alphabetSize;
  }

  return alphabet[newIndex];
}

const cle = "?zu6j,xX{N12I]0r6C=v57IoASU~?6_y";


function vignere(texte, cle, sens) {
  let result = "";
  let indexCle = 0;

  for (let i = 0; i < texte.length; i++) {
    const char = texte[i];
    const cleChar = cle[indexCle % cle.length];
    const newChar = convert(char, cleChar, sens);
    result += newChar;
    indexCle++;
  }

  return result;
}

window.vignere = vignere;
window.cle = cle;

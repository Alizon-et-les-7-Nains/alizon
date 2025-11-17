function convert (char, cle, sens){
    const codeMin = 32;
    const codeMax = 126;
    let nbChars = codeMax - codeMin + 1;
    
    let valAsciiChar = char.charCodeAt(0);
    let valAsciiCle = cle.charCodeAt(0);
    
    if (valAsciiChar < codeMin || valAsciiChar > codeMax){
        return char;
    }
    
    const decal = valAsciiCle - codeMin;
    let newCode;
    
    if (sens === 1) {
        newCode = (valAsciiChar - codeMin + decal) % nbChars + codeMin;
    } else {
        newCode = (valAsciiChar - codeMin - decal + nbChars) % nbChars + codeMin;
    }
    
    return String.fromCharCode(newCode);
}

function vignere(texte, cle, sens){
    let result = "";
    let indexCLe = 0;
    for (let i = 0 ; i < texte.length ; i ++){
        cleChar = cle[indexCLe % cle.length];
        result += convert(texte[i], cleChar, sens);
        indexCLe ++;
    }
    return result;
}

cle = "?zu6j,xX{N12I]0r6C=v57IoASU~?6_y";
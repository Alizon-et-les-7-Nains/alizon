const rondElements = document.querySelectorAll(".rond")
const traitElements = document.querySelectorAll(".trait");
const demiTraitElements = document.querySelectorAll(".demiTrait");

const croix = document.getElementsByClassName("croixFermerLaPage")[0];

function fermerPopUp() {
    window.location.href = '/views/frontoffice/commandes.php';
}

function changeRondColor(elem){
    elem.style.backgroundColor = "green";
    elem.style.border = "green 5px solid";
}

function changeColor(elem){
    elem.style.backgroundColor = "green";
}

for (let i = 1; i <= etape; i++) {
    switch (i) {
        case 1:
            changeRondColor(rondElements[0]);
            break;
        case 2:
            changeColor(demiTraitElements[0]);
            break;
        case 3:
            changeColor(traitElements[0]);
            changeRondColor(rondElements[1]);
            break;
        case 4:
            changeColor(demiTraitElements[1]);
            break;
        case 5:
            changeColor(traitElements[1]);
            changeRondColor(rondElements[2]);
            break;
        case 6:
            changeColor(demiTraitElements[2]);
            break;
        case 7:
            changeColor(traitElements[2]);
            changeRondColor(rondElements[3]);
            break;
        case 8:
            changeColor(demiTraitElements[3]);
            break;
        case 9:
            changeColor(traitElements[3]);
            changeRondColor(rondElements[4]);
            break;
    }
}

croix.addEventListener("click", fermerPopUp);



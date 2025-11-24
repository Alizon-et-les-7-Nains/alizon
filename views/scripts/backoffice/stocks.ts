const btnSettings: Element[] = Array.from(document.getElementsByClassName('settings'));
const modalReassort: HTMLDialogElement | null = document.querySelector("dialog.reassort") as HTMLDialogElement;
let inputSeuil: HTMLInputElement = document.getElementById('seuil') as HTMLInputElement;
let inputReassort: HTMLInputElement = document.getElementById('reassort') as HTMLInputElement;

btnSettings.forEach(btn => {
    btn.addEventListener('mouseover', () => {
        const subDivs: Element[] = Array.from(btn.children);
        subDivs.forEach(div => {
            if (div instanceof HTMLElement && div.firstElementChild instanceof HTMLElement) {
                const innerDiv = div.firstElementChild;
                innerDiv.style.left = innerDiv.classList.contains('right') ? '4px' : '14px';
            }
        })
    })
})

btnSettings.forEach(btn => {
    btn.addEventListener('mouseout', () => {
        const subDivs: Element[] = Array.from(btn.children);
        subDivs.forEach(div => {
            if (div instanceof HTMLElement && div.firstElementChild instanceof HTMLElement) {
                const innerDiv = div.firstElementChild;
                innerDiv.style.left = innerDiv.classList.contains('right') ? '14px' : '4px';
            }
        })
    })
})

let idProduit: number;

btnSettings.forEach(btn => {
    btn.addEventListener('click', () => {
        idProduit = parseInt(btn.id);
        modalReassort.showModal();
        modalReassort.style.display = 'flex';
    })
})

modalReassort?.addEventListener("click", (e) => {
    if (e.target === modalReassort) {
        modalReassort.close();
        modalReassort.style.display = 'none';
    }
});

document.querySelector('modal.reassort input#annuler')?.addEventListener('click', () => {
    modalReassort.close();
    modalReassort.style.display = 'none';
})


function checkInt(value: string): boolean {
    let valid: boolean;
    let intValue = parseInt(value);
    if (!intValue || intValue < 0) {
        valid = false;
    } else {
        valid = true;
    }
    return valid;
}

inputSeuil.addEventListener('change', () => {
    if (!checkInt(inputSeuil.value)) {
        inputSeuil.style.borderColor = '#f14e4e';
    }
})
inputReassort.addEventListener('change', () => {
    if (!checkInt(inputReassort.value)) {
        inputReassort.style.borderColor = '#f14e4e';
    }
})
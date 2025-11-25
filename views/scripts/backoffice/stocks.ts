const btnSettings: Element[] = Array.from(document.getElementsByClassName('settings'));
const modalReassort: HTMLDialogElement | null = document.querySelector("dialog.reassort") as HTMLDialogElement;
const inputSeuil: HTMLInputElement = document.getElementById('seuil') as HTMLInputElement;
const inputReassort: HTMLInputElement = document.getElementById('reassort') as HTMLInputElement;
const buttonConfirm: HTMLInputElement = document.getElementById('buttonConfirm') as HTMLInputElement;
const errorFieldSeuil: HTMLElement = document.getElementById('errorFieldSeuil') as HTMLElement;
const errorFieldReassort: HTMLElement = document.getElementById('errorFieldReassort') as HTMLElement;

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
    let valid: boolean = true;
    let intValue = parseInt(value);
    if (!intValue || intValue < 0) {
        valid = false;
    }
    return valid;
}

inputSeuil.addEventListener('input', () => {
    if (!checkInt(inputSeuil.value)) {
        inputSeuil.style.borderColor = '#f14e4e';
        errorFieldSeuil.style.display = 'block';
    } else {
        inputSeuil.style.borderColor = '#273469';
        errorFieldSeuil.style.display = 'none';
    }
})
inputReassort.addEventListener('input', () => {
    if (!checkInt(inputReassort.value)) {
        inputReassort.style.borderColor = '#f14e4e';
    } else {
        inputReassort.style.borderColor = '#273469';
        errorFieldReassort.style.display = 'none';
    }
})
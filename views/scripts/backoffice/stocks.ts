const btnSettings: Element[] = Array.from(document.getElementsByClassName('settings'));
const modalReassort: HTMLDialogElement | null = document.querySelector("dialog.reassort") as HTMLDialogElement;

const inputSeuil: HTMLInputElement = document.getElementById('seuil') as HTMLInputElement;
const inputDate: HTMLInputElement = document.getElementById('dateReassort') as HTMLInputElement;
const inputReassort: HTMLInputElement = document.getElementById('reassort') as HTMLInputElement;
const buttonConfirm: HTMLInputElement = document.getElementById('buttonConfirm') as HTMLInputElement;

const errorFieldSeuil: HTMLElement = document.getElementById('errorFieldSeuil') as HTMLElement;
const errorFieldReassort: HTMLElement = document.getElementById('errorFieldReassort') as HTMLElement;
const errorFieldDate: HTMLElement = document.getElementById('errorFieldDate') as HTMLElement;

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
    if (!value) {
        if (!intValue || intValue < 0) {
            valid = false;
        }
    } else {
        valid = true;
    }
    return valid;
}

function checkDate(date: Date | null): boolean {
    let valid: boolean = true;
    if (date != null) {
        if (date.getTime() < Date.now()) {
            valid = false;
        }
    } else {
        valid = true;
    }
    return valid;
}

function allValid(): boolean {
    return checkInt(inputSeuil.value) && checkDate(inputDate.valueAsDate) && checkInt(inputReassort.value);
}

inputSeuil.addEventListener('input', () => {
    if (!checkInt(inputSeuil.value)) {
        inputSeuil.style.cssText = 'border-color: #f14e4e !important';
        errorFieldSeuil.style.display = 'block';
    } else {
        inputSeuil.style.cssText = 'border-color: #273469 !important';
        errorFieldSeuil.style.display = 'none';
    }

    buttonConfirm.disabled = !allValid();
})
inputDate.addEventListener('input', () => {
    if (!checkDate(inputDate.valueAsDate)) {
        inputDate.style.cssText = 'border-color: #f14e4e !important';
        errorFieldDate.style.display = 'block';
        
    } else {
        inputDate.style.cssText = 'border-color: #273469 !important';
        errorFieldDate.style.display = 'none';
    }

    buttonConfirm.disabled = !allValid();
})
inputReassort.addEventListener('input', () => {
    if (!checkInt(inputReassort.value)) {
        inputReassort.style.cssText = 'border-color: #f14e4e !important';
        errorFieldReassort.style.display = 'block';
    } else {
        inputReassort.style.cssText = 'border-color: #273469 !important';
        errorFieldReassort.style.display = 'none';
    }

    buttonConfirm.disabled = !allValid();
})
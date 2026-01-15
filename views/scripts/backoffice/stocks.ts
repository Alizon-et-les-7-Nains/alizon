const buttonsSettings: Element[] = Array.from(document.getElementsByClassName('settings'));

const inputsSeuil: HTMLInputElement[] = Array.from(document.getElementsByClassName('seuil') as HTMLCollectionOf<HTMLInputElement>);
const inputsDate: HTMLInputElement[] = Array.from(document.getElementsByClassName('dateReassort') as HTMLCollectionOf<HTMLInputElement>);
const inputsReassort: HTMLInputElement[] = Array.from(document.getElementsByClassName('reassort') as HTMLCollectionOf<HTMLInputElement>);

const buttonsCancel: HTMLInputElement[] = Array.from(document.getElementsByClassName('buttonCancel') as HTMLCollectionOf<HTMLInputElement>);

function checkInt(value: string): boolean {
    console.log(value);
    if (!value) return true;

    let intValue = parseInt(value);
    console.log(!isNaN(intValue) && intValue >= 0);
    return !isNaN(intValue) && intValue >= 0;
}

function checkDate(date: Date | null): boolean {
    if (!date) return true;

    let now: Date = new Date();
    now.setHours(0, 0, 0, 0);

    console.log(now.getTime() < date.getTime());

    return now.getTime() < date.getTime();
}

function allValid(seuil: HTMLInputElement, date: HTMLInputElement, reassort: HTMLInputElement): boolean {
    console.log(seuil.value, date.valueAsDate, reassort.value)
    return checkInt(seuil.value) && checkDate(date.valueAsDate) && checkInt(reassort.value);
}

inputsSeuil.forEach((inputSeuil: HTMLInputElement) => {
    const id = inputSeuil.classList[1];
    inputSeuil.addEventListener('input', () => {
        if (!checkInt(inputSeuil.value)) {
            inputSeuil.style.borderColor = '#f14e4e';
            const errorLabel = document.querySelector(`label.errorFieldSeuil.${id}`) as HTMLElement;
            if (errorLabel) errorLabel.style.display = 'block';
        } else {
            inputSeuil.style.borderColor = '#273469';
            const errorLabel = document.querySelector(`label.errorFieldSeuil.${id}`) as HTMLElement;
            if (errorLabel) errorLabel.style.display = 'none';
        }

        const button = document.querySelector(`input.buttonConfirm.${id}`) as HTMLButtonElement;
        if (button) {
            button.disabled = !allValid(
                inputSeuil, 
                document.querySelector(`input.dateReassort.${id}`) as HTMLInputElement,
                document.querySelector(`input.reassort.${id}`) as HTMLInputElement
            )
        }
    })
})

inputsDate.forEach((inputDate: HTMLInputElement) => {
    const id = inputDate.classList[1];
    inputDate.addEventListener('input', () => {
        if (!checkDate(inputDate.valueAsDate)) {
            inputDate.style.borderColor = '#f14e4e';
            const errorLabel = document.querySelector(`label.errorFieldDate.${id}`) as HTMLElement;
            if (errorLabel) errorLabel.style.display = 'block';
        } else {
            inputDate.style.borderColor = '#273469';
            const errorLabel = document.querySelector(`label.errorFieldDate.${id}`) as HTMLElement;
            if (errorLabel) errorLabel.style.display = 'none';
        }

        const button = document.querySelector(`input.buttonConfirm.${id}`) as HTMLButtonElement;
        if (button) {
            button.disabled = !allValid(
                document.querySelector(`input.seuil.${id}`) as HTMLInputElement, 
                inputDate,
                document.querySelector(`input.reassort.${id}`) as HTMLInputElement
            )
        }
    })
})

inputsReassort.forEach((inputReassort: HTMLInputElement) => {
    const id = inputReassort.classList[1];
    inputReassort.addEventListener('input', () => {
        if (!checkInt(inputReassort.value)) {
            inputReassort.style.borderColor = '#f14e4e';
            const errorLabel = document.querySelector(`label.errorFieldReassort.${id}`) as HTMLElement;
            if (errorLabel) errorLabel.style.display = 'block';
        } else {
            inputReassort.style.borderColor = '#273469';
            const errorLabel = document.querySelector(`label.errorFieldReassort.${id}`) as HTMLElement;
            if (errorLabel) errorLabel.style.display = 'none';
        }

        const button = document.querySelector(`input.buttonConfirm.${id}`) as HTMLButtonElement;
        if (button) {
            button.disabled = !allValid(
                document.querySelector(`input.seuil.${id}`) as HTMLInputElement, 
                document.querySelector(`input.dateReassort.${id}`) as HTMLInputElement,
                inputReassort
            )
        }
    })
})

buttonsSettings.forEach(btn => {
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

buttonsSettings.forEach(btn => {
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

buttonsSettings.forEach(btn => {
    btn.addEventListener('click', () => {
        const modal = document.querySelector(`main.backoffice-stocks dialog#d-${btn.id}`) as HTMLDialogElement;

        if (!modal) return;
        
        modal.showModal();
        modal.style.display = 'flex';

        modal.addEventListener("click", (e) => {
            if (e.target === modal) {
                modal.close();
                modal.style.display = 'none';
            }
        });
    })
})

buttonsCancel.forEach((btnCancel: HTMLElement) => {
    btnCancel.addEventListener('click', () => {
        Array.from(document.getElementsByTagName('dialog')).forEach(dia => {
            dia.close();
            dia.style.display = 'none';
        })
    })
})
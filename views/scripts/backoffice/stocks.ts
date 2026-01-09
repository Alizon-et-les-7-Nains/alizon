const buttonsSettings: Element[] = Array.from(document.getElementsByClassName('settings'));

const inputsSeuil: HTMLInputElement[] = Array.from(document.getElementsByClassName('seuil') as HTMLCollectionOf<HTMLInputElement>);
const inputsDate: HTMLInputElement[] = Array.from(document.getElementsByClassName('dateReassort') as HTMLCollectionOf<HTMLInputElement>);
const inputsReassort: HTMLInputElement[] = Array.from(document.getElementsByClassName('reassort') as HTMLCollectionOf<HTMLInputElement>);

const buttonsCancel: HTMLInputElement[] = Array.from(document.getElementsByClassName('buttonCancel') as HTMLCollectionOf<HTMLInputElement>);

// const buttonConfirm: HTMLInputElement = document.getElementById('buttonConfirm') as HTMLInputElement;

// const errorFieldSeuil: HTMLElement = document.getElementById('errorFieldSeuil') as HTMLElement;
// const errorFieldReassort: HTMLElement = document.getElementById('errorFieldReassort') as HTMLElement;
// const errorFieldDate: HTMLElement = document.getElementById('errorFieldDate') as HTMLElement;

// function allValid(): boolean {
//     return checkInt(inputSeuil.value) && checkDate(inputDate.valueAsDate) && checkInt(inputReassort.value);
// }

function checkInt(value: string): boolean {
    if (!value) return true;

    let intValue = parseInt(value);
    return !isNaN(intValue) && intValue >= 0;
}

function checkDate(date: Date | null): boolean {
    if (!date) return true;

    let now: Date = new Date();
    now.setHours(0, 0, 0, 0);

    return now.getTime() < date.getTime();
}

function allValid(seuil: HTMLInputElement, date: HTMLInputElement, reassort: HTMLInputElement): boolean {
    return checkInt(seuil.value) && checkDate(date.valueAsDate) && checkInt(reassort.value);
}

// inputsSeuil.forEach((inputSeuil: HTMLInputElement) => {
//     inputSeuil.addEventListener('input', () => {
//         if (!checkInt(inputSeuil.value)) {
//             inputSeuil.style.cssText = 'border-color: #f14e4e !important';
//             (inputSeuil.parentElement?.parentElement?.children.namedItem('errorSeuil') as HTMLElement).style.display = 'block';
//         } else {
//             inputSeuil.style.cssText = 'border-color: #273469 !important';
//             (inputSeuil.parentElement?.parentElement?.children.namedItem('errorSeuil') as HTMLElement).style.display = 'none';
//         }

//         (inputSeuil.parentElement?.parentElement?.children.namedItem('buttonConfirm') as HTMLButtonElement).disabled = !allValid(inputSeuil, inputSeuil.parentElement?.parentElement?.children.namedItem(''));
//     })
// })

// inputSeuil?.addEventListener('input', () => {
//     console.log('input');
//     if (!checkInt(inputSeuil.value)) {
//         inputSeuil.style.cssText = 'border-color: #f14e4e !important';
//         errorFieldSeuil.style.display = 'block';
//     } else {
//         inputSeuil.style.cssText = 'border-color: #273469 !important';
//         errorFieldSeuil.style.display = 'none';
//     }

//     buttonConfirm.disabled = !allValid();
// })
// inputDate?.addEventListener('input', () => {
//     console.log('input');
//     if (!checkDate(inputDate.valueAsDate)) {
//         inputDate.style.cssText = 'border-color: #f14e4e !important';
//         errorFieldDate.style.display = 'block';
        
//     } else {
//         inputDate.style.cssText = 'border-color: #273469 !important';
//         errorFieldDate.style.display = 'none';
//     }

//     buttonConfirm.disabled = !allValid();
// })
// inputReassort?.addEventListener('input', () => {
//     console.log('input');
//     if (!checkInt(inputReassort.value)) {
//         inputReassort.style.cssText = 'border-color: #f14e4e !important';
//         errorFieldReassort.style.display = 'block';
//     } else {
//         inputReassort.style.cssText = 'border-color: #273469 !important';
//         errorFieldReassort.style.display = 'none';
//     }

//     buttonConfirm.disabled = !allValid();
// })

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
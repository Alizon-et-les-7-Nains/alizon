let btnSettings: Element[] = Array.from(document.getElementsByClassName('settings'));
let modalReassort: HTMLDialogElement | null = document.querySelector("dialog.reassort") as HTMLDialogElement;

let inputSeuil: HTMLInputElement = document.getElementById('seuil') as HTMLInputElement;
let inputDate: HTMLInputElement = document.getElementById('dateReassort') as HTMLInputElement;
let inputReassort: HTMLInputElement = document.getElementById('reassort') as HTMLInputElement;
let buttonConfirm: HTMLInputElement = document.getElementById('buttonConfirm') as HTMLInputElement;

let errorFieldSeuil: HTMLElement = document.getElementById('errorFieldSeuil') as HTMLElement;
let errorFieldReassort: HTMLElement = document.getElementById('errorFieldReassort') as HTMLElement;
let errorFieldDate: HTMLElement = document.getElementById('errorFieldDate') as HTMLElement;

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

btnSettings.forEach(btn => {
    btn.addEventListener('click', () => {
        console.log('Calling fetch with idProduit:', btn.id);
        
        fetch('getProduct.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({idProduit: btn.id})
        })
        .then(r => {
            console.log('Response status:', r.status);
            console.log('Response headers:', r.headers);
            return r.text(); // Changez temporairement en .text() au lieu de .json()
        })
        .then(text => {
            console.log('Raw response:', text);
            const data = JSON.parse(text); // Parsez manuellement pour voir l'erreur
            console.log('Parsed data:', data);
            // ... reste du code
        })
        .catch(error => {
            console.error('Fetch error:', error);
        });
    })
})


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

function allValid(): boolean {
    return checkInt(inputSeuil.value) && checkDate(inputDate.valueAsDate) && checkInt(inputReassort.value);
}

inputSeuil?.addEventListener('input', () => {
    if (!checkInt(inputSeuil.value)) {
        inputSeuil.style.cssText = 'border-color: #f14e4e !important';
        errorFieldSeuil.style.display = 'block';
    } else {
        inputSeuil.style.cssText = 'border-color: #273469 !important';
        errorFieldSeuil.style.display = 'none';
    }

    buttonConfirm.disabled = !allValid();
})
inputDate?.addEventListener('input', () => {
    if (!checkDate(inputDate.valueAsDate)) {
        inputDate.style.cssText = 'border-color: #f14e4e !important';
        errorFieldDate.style.display = 'block';
        
    } else {
        inputDate.style.cssText = 'border-color: #273469 !important';
        errorFieldDate.style.display = 'none';
    }

    buttonConfirm.disabled = !allValid();
})
inputReassort?.addEventListener('input', () => {
    if (!checkInt(inputReassort.value)) {
        inputReassort.style.cssText = 'border-color: #f14e4e !important';
        errorFieldReassort.style.display = 'block';
    } else {
        inputReassort.style.cssText = 'border-color: #273469 !important';
        errorFieldReassort.style.display = 'none';
    }

    buttonConfirm.disabled = !allValid();
})
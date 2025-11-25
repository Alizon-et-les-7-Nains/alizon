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
        fetch('../../controllers/getProduct.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({idProduit: btn.id})
        })
        .then(r => r.json())
        .then(data => {
            const dialog = document.createElement('dialog');
            dialog.className = 'reassort';
            dialog.innerHTML = `
                <h1>Paramètres de réassort</h1>
                <form action="" method="post">
                    <input type="number" value="${data.seuilAlerte || ''}" name="seuil" id="seuil">
                    <span id="errorFieldSeuil" style="display:none;">Valeur invalide</span>
                    <input type="date" value="${data.dateReassort || ''}" name="dateReassort" id="dateReassort">
                    <span id="errorFieldDate" style="display:none;">Date invalide</span>
                    <input type="number" name="reassort" id="reassort">
                    <span id="errorFieldReassort" style="display:none;">Valeur invalide</span>
                    <ul>
                        <li><input type="button" value="Annuler" id="annuler"></li>
                        <li><input type="submit" value="Valider" id="buttonConfirm"></li>
                    </ul>
                </form>
            `;
            document.body.appendChild(dialog);
            
            inputSeuil = dialog.querySelector('#seuil') as HTMLInputElement;
            inputDate = dialog.querySelector('#dateReassort') as HTMLInputElement;
            inputReassort = dialog.querySelector('#reassort') as HTMLInputElement;
            buttonConfirm = dialog.querySelector('#buttonConfirm') as HTMLInputElement;
            const annuler = dialog.querySelector('#annuler') as HTMLButtonElement;

            dialog.addEventListener("click", (e) => {
                if (e.target === modalReassort) {
                    dialog.close();
                    dialog.style.display = 'none';
                }
            });
            
            annuler.addEventListener('click', () => {
                dialog.close();
                dialog.remove();
            });
            
            dialog.addEventListener('click', (e) => {
                if (e.target === dialog) {
                    dialog.close();
                    dialog.remove();
                }
            });
            
            dialog.showModal();
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
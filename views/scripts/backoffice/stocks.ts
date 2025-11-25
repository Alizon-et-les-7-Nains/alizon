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

btnSettings.forEach(btn => {
    btn.addEventListener('click', () => {
        fetch('getProduct.php', {
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
                    <input type="date" value="${data.dateReassort || ''}" name="dateReassort" id="dateReassort">
                    <input type="number" name="reassort" id="reassort">
                    <ul>
                        <li><input type="button" value="Annuler" id="annuler"></li>
                        <li><input type="submit" value="Valider" id="buttonConfirm"></li>
                    </ul>
                </form>
            `;
            document.body.appendChild(dialog);
            dialog.showModal();
        });
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
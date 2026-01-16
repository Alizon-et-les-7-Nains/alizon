"use strict";
Array.from(document.querySelectorAll('main.acceuilBackoffice button.bilan')).forEach((btn) => {
    btn.addEventListener('click', () => {
        if (!btn.classList.contains('here')) {
            document.querySelector('main.acceuilBackoffice button.bilan.here')?.classList.remove('here');
            btn.classList.add('here');
        }
    });
});
Array.from(document.getElementsByClassName('aside-btn')).forEach(asideButton => {
    asideButton.addEventListener('click', () => {
        const category = asideButton.children[0].children[1].innerHTML.toLowerCase();
        if (!asideButton.className.includes('here')) {
            window.location.href = `./${category}.php`;
        }
    });
});
document.querySelectorAll('main.commandesBackoffice article').forEach((command) => {
    command.addEventListener('click', () => {
        const modal = document.querySelector(`main.commandesBackoffice dialog#${command.id}`);
        modal?.showModal();
        modal?.addEventListener("click", (e) => {
            if (e.target === modal) {
                modal?.close();
            }
        });
    });
});
const modalSupprProduit = document.querySelector("main.modifierProduit dialog");
document.querySelector("main.modifierProduit .btn-supprimer")?.addEventListener("click", () => {
    modalSupprProduit?.showModal();
});
document.querySelector("main.modifierProduit dialog button")?.addEventListener("click", () => {
    modalSupprProduit?.close();
});
document.querySelector("main.modifierProduit dialog nav button:first-child")?.addEventListener("click", () => {
    modalSupprProduit?.close();
});
modalSupprProduit?.addEventListener("click", (e) => {
    if (e.target === modalSupprProduit) {
        modalSupprProduit.close();
    }
});
const boutonHaut = document.getElementById('haut');
boutonHaut?.addEventListener('click', () => {
    window.scrollTo({ top: 0, behavior: 'smooth' });
});
window.addEventListener('scroll', () => {
    if (window.scrollY > window.innerHeight) {
        boutonHaut?.classList.add('visible');
    }
    else {
        boutonHaut?.classList.remove('visible');
    }
});
document.querySelector("header.backoffice figure:first-child")?.addEventListener("click", () => {
    window.location.href = "ajouterProduit.php";
});
const modalDeconnexion = document.querySelector("header.backoffice dialog");
document.querySelector("header.backoffice figure:nth-child(2)")?.addEventListener("click", () => {
    modalDeconnexion?.showModal();
});
document.querySelector("header.backoffice dialog button")?.addEventListener("click", () => {
    modalDeconnexion?.close();
});
document.querySelector("header.backoffice dialog nav button:first-child")?.addEventListener("click", () => {
    modalDeconnexion?.close();
});
document.querySelector("header.backoffice dialog nav button:last-child")?.addEventListener("click", () => {
    window.location.href = "connexion.php";
});
modalDeconnexion?.addEventListener("click", (e) => {
    if (e.target === modalDeconnexion) {
        modalDeconnexion.close();
    }
});
document.querySelector('header.backoffice figure:nth-child(3)')?.addEventListener('click', () => {
    window.location.href = 'compteVendeur.php';
});
const buttonsSettings = Array.from(document.getElementsByClassName('settings'));
const inputsSeuil = Array.from(document.getElementsByClassName('seuil'));
const inputsDate = Array.from(document.getElementsByClassName('dateReassort'));
const inputsReassort = Array.from(document.getElementsByClassName('reassort'));
const buttonsCancel = Array.from(document.getElementsByClassName('buttonCancel'));
function checkInt(value) {
    console.log(value);
    if (!value)
        return true;
    let intValue = parseInt(value);
    console.log(!isNaN(intValue) && intValue >= 0);
    return !isNaN(intValue) && intValue >= 0;
}
function checkDate(date) {
    if (!date)
        return true;
    let now = new Date();
    now.setHours(0, 0, 0, 0);
    console.log(now.getTime() < date.getTime());
    return now.getTime() < date.getTime();
}
function allValid(seuil, date, reassort) {
    console.log(seuil.value, date.valueAsDate, reassort.value);
    return checkInt(seuil.value) && checkDate(date.valueAsDate) && checkInt(reassort.value);
}
inputsSeuil.forEach((inputSeuil) => {
    const id = inputSeuil.classList[1];
    inputSeuil.addEventListener('input', () => {
        if (!checkInt(inputSeuil.value)) {
            inputSeuil.style.borderColor = '#f14e4e';
            const errorLabel = document.querySelector(`label.errorFieldSeuil.${id}`);
            if (errorLabel)
                errorLabel.style.display = 'block';
        }
        else {
            inputSeuil.style.borderColor = '#273469';
            const errorLabel = document.querySelector(`label.errorFieldSeuil.${id}`);
            if (errorLabel)
                errorLabel.style.display = 'none';
        }
        const button = document.querySelector(`input.buttonConfirm.${id}`);
        if (button) {
            button.disabled = !allValid(inputSeuil, document.querySelector(`input.dateReassort.${id}`), document.querySelector(`input.reassort.${id}`));
        }
    });
});
inputsDate.forEach((inputDate) => {
    const id = inputDate.classList[1];
    inputDate.addEventListener('input', () => {
        if (!checkDate(inputDate.valueAsDate)) {
            inputDate.style.borderColor = '#f14e4e';
            const errorLabel = document.querySelector(`label.errorFieldDate.${id}`);
            if (errorLabel)
                errorLabel.style.display = 'block';
        }
        else {
            inputDate.style.borderColor = '#273469';
            const errorLabel = document.querySelector(`label.errorFieldDate.${id}`);
            if (errorLabel)
                errorLabel.style.display = 'none';
        }
        const button = document.querySelector(`input.buttonConfirm.${id}`);
        if (button) {
            button.disabled = !allValid(document.querySelector(`input.seuil.${id}`), inputDate, document.querySelector(`input.reassort.${id}`));
        }
    });
});
inputsReassort.forEach((inputReassort) => {
    const id = inputReassort.classList[1];
    inputReassort.addEventListener('input', () => {
        if (!checkInt(inputReassort.value)) {
            inputReassort.style.borderColor = '#f14e4e';
            const errorLabel = document.querySelector(`label.errorFieldReassort.${id}`);
            if (errorLabel)
                errorLabel.style.display = 'block';
        }
        else {
            inputReassort.style.borderColor = '#273469';
            const errorLabel = document.querySelector(`label.errorFieldReassort.${id}`);
            if (errorLabel)
                errorLabel.style.display = 'none';
        }
        const button = document.querySelector(`input.buttonConfirm.${id}`);
        if (button) {
            button.disabled = !allValid(document.querySelector(`input.seuil.${id}`), document.querySelector(`input.dateReassort.${id}`), inputReassort);
        }
    });
});
buttonsSettings.forEach(btn => {
    btn.addEventListener('mouseover', () => {
        const subDivs = Array.from(btn.children);
        subDivs.forEach(div => {
            if (div instanceof HTMLElement && div.firstElementChild instanceof HTMLElement) {
                const innerDiv = div.firstElementChild;
                innerDiv.style.left = innerDiv.classList.contains('right') ? '4px' : '14px';
            }
        });
    });
});
buttonsSettings.forEach(btn => {
    btn.addEventListener('mouseout', () => {
        const subDivs = Array.from(btn.children);
        subDivs.forEach(div => {
            if (div instanceof HTMLElement && div.firstElementChild instanceof HTMLElement) {
                const innerDiv = div.firstElementChild;
                innerDiv.style.left = innerDiv.classList.contains('right') ? '14px' : '4px';
            }
        });
    });
});
buttonsSettings.forEach(btn => {
    btn.addEventListener('click', () => {
        const modal = document.querySelector(`main.backoffice-stocks dialog#d-${btn.id}`);
        if (!modal)
            return;
        modal.showModal();
        modal.style.display = 'flex';
        modal.addEventListener("click", (e) => {
            if (e.target === modal) {
                modal.close();
                modal.style.display = 'none';
            }
        });
    });
});
buttonsCancel.forEach((btnCancel) => {
    btnCancel.addEventListener('click', () => {
        Array.from(document.getElementsByTagName('dialog')).forEach(dia => {
            dia.close();
            dia.style.display = 'none';
        });
    });
});
//# sourceMappingURL=script.js.map
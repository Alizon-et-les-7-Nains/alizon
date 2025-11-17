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
document.querySelector('button#haut')?.addEventListener('click', () => {
    window.scrollTo({ top: 0, behavior: 'smooth' });
});
document
    .querySelector("header.backoffice figure:first-child")
    ?.addEventListener("click", () => {
    window.location.href = "10.253.5.104/views/backoffice/ajouterProduit.php";
});
const modal = document.querySelector("header.backoffice dialog");
document
    .querySelector("header.backoffice figure:nth-child(2)")
    ?.addEventListener("click", () => {
    modal?.showModal();
});
document
    .querySelector("header.backoffice dialog button")
    ?.addEventListener("click", () => {
    modal?.close();
});
document
    .querySelector("header.backoffice dialog nav button:first-child")
    ?.addEventListener("click", () => {
    modal?.close();
});
document
    .querySelector("header.backoffice dialog nav button:last-child")
    ?.addEventListener("click", () => {
    window.location.href = "10.253.5.104/views/backoffice/connexion.php";
});
modal?.addEventListener("click", (e) => {
    if (e.target === modal) {
        modal.close();
    }
});
//# sourceMappingURL=script.js.map
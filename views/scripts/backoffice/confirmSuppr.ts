const modalSupprProduit: HTMLDialogElement | null = document.querySelector("main.modifierProduit dialog") as HTMLDialogElement;

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

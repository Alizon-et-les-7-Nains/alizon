const modal: HTMLDialogElement | null = document.querySelector(
  "header.backoffice dialog"
) as HTMLDialogElement;

// Vérifier que modal existe avant d'ajouter les event listeners
if (modal) {
  document
    .querySelector("header.backoffice figure:nth-child(2)")
    ?.addEventListener("click", () => {
      modal.showModal();
    });

  document
    .querySelector("header.backoffice dialog button")
    ?.addEventListener("click", () => {
      modal.close();
    });

  document
    .querySelector("header.backoffice dialog nav button:first-child")
    ?.addEventListener("click", () => {
      modal.close();
    });

  document
    .querySelector("header.backoffice dialog nav button:last-child")
    ?.addEventListener("click", () => {
      window.location.href = "10.253.5.104/views/backoffice/connexion.php";
    });

  // Ajouter l'event listener seulement si modal existe
  modal.addEventListener("click", (e) => {
    if (e.target === modal) {
      modal.close();
    }
  });
} else {
  console.warn("Modal element not found in DOM");
}

console.log("Test");
document
  .querySelector("header.backoffice figure:first-child")
  ?.addEventListener("click", () => {
    window.location.href = "10.253.5.104/views/backoffice/ajouterProduit.php";
  });

const modal: HTMLDialogElement | null = document.querySelector(
  "header.backoffice dialog"
) as HTMLDialogElement;

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

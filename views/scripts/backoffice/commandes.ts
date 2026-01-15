<<<<<<< HEAD
document.querySelectorAll<HTMLElement>('main.commandesBackoffice article').forEach((command) => {
=======
Array.from(document.querySelectorAll('main.backofficeCommandes article')).forEach(command => {
>>>>>>> traitement-images
    command.addEventListener('click', () => {
        const modal = document.querySelector(`main.commandesBackoffice dialog#${command.id}`) as HTMLDialogElement;

        modal?.showModal();

        modal?.addEventListener("click", (e) => {
            if (e.target === modal) {
                modal?.close();
            }
        });
    })
})
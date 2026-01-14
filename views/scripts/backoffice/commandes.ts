document.querySelectorAll<HTMLElement>('main.commandesBackoffice article').forEach((command) => {
    command.addEventListener('click', () => {
        const modal = document.querySelector(`main.commandesBackoffice dialog#${command.id}`) as HTMLDialogElement;

        modal.showModal();

        modal.addEventListener("click", (e) => {
            if (e.target === modal) {
                modal.close();
            }
        });
    })
})
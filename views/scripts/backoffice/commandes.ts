Array.from(document.getElementsByTagName('article')).forEach((command: HTMLElement) => {
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
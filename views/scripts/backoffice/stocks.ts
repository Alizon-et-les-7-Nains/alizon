const btnSettings: Element[] = Array.from(document.getElementsByClassName('settings'));
const modalReassort: HTMLDialogElement | null = document.querySelector("dialog.reassort") as HTMLDialogElement;

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
        modalReassort.showModal();
    })
})

// modalReassort?.addEventListener("click", (e) => {
//     if (e.target === modalReassort) {
//         modalReassort.close();
//     }
// });

// document.querySelector('input#annuler')?.addEventListener('click', () => {
//     modalReassort.close();
// })

// document.addEventListener('DOMContentLoaded', () => {
//     modalReassort?.close();
// });
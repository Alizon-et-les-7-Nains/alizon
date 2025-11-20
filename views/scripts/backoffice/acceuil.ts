Array.from(document.querySelectorAll('main.acceuilBackoffice button.bilan')).forEach((btn: Element) => {
    btn.addEventListener('click', () => {
        if (!btn.classList.contains('here')) {
            document.querySelector('main.acceuilBackoffice button.bilan.here')?.classList.remove('here');
            btn.classList.add('here');
        }
    })
});
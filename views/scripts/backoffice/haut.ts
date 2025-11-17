const boutonHaut: HTMLElement | null = document.getElementById('haut');

boutonHaut?.addEventListener('click', () => {
    window.scrollTo({ top: 0, behavior: 'smooth' });
})

window.addEventListener('scroll', () => {
    if (window.scrollY > window.innerHeight) {
        boutonHaut?.classList.add('visible');
    } else {
        boutonHaut?.classList.remove('visible');
    }
});
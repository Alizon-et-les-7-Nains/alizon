document.addEventListener('DOMContentLoaded', () => {
    const urlParams = new URLSearchParams(window.location.search);
    const productId = urlParams.get('reassort_id');

    if (productId) {
        document.querySelectorAll('dialog[open]').forEach(d => {
            d.close();
            d.style.display = 'none';
        });

        const targetDialog = document.getElementById('d-' + productId);

        if (targetDialog) {
            targetDialog.showModal();
            targetDialog.style.display = 'flex'; 
            targetDialog.scrollIntoView({ behavior: 'smooth', block: 'center' });

            const btnCancel = targetDialog.querySelector('.buttonCancel');
            if (btnCancel) {
                btnCancel.addEventListener('click', () => {
                    targetDialog.close();
                    targetDialog.style.display = 'none';
                    window.history.replaceState({}, document.title, window.location.pathname);
                });
            }
        }
    }
});
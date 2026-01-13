document.addEventListener('DOMContentLoaded', () => {
    const urlParams = new URLSearchParams(window.location.search);
    const productId = urlParams.get('reassort_id');
    
    if (productId) {
        const targetDialog = document.getElementById('d-' + productId);
        if (targetDialog) {
            targetDialog.showModal();
            targetDialog.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }
});
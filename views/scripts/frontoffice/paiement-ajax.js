// API de paiement - Communication avec le backend
class PaymentAPI {
    static async updateQuantity(idProduit, delta) {
        try {
            const response = await fetch('', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=updateQty&idProduit=${idProduit}&delta=${delta}`
            });
            
            const result = await response.json();
            return result.success;
        } catch (error) {
            console.error('Erreur lors de la mise à jour:', error);
            return false;
        }
    }

    static async removeItem(idProduit) {
        try {
            const response = await fetch('', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=removeItem&idProduit=${idProduit}`
            });
            
            const result = await response.json();
            return result.success;
        } catch (error) {
            console.error('Erreur lors de la suppression:', error);
            return false;
        }
    }

    static async createOrder(orderData) {
        try {
            const response = await fetch('', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=createOrder&${new URLSearchParams(orderData).toString()}`
            });
            
            const result = await response.json();
            return result;
        } catch (error) {
            console.error('Erreur lors de la création de commande:', error);
            return { success: false, error: 'Erreur réseau' };
        }
    }
}

// Exposer l'API globalement
window.PaymentAPI = PaymentAPI;

// Fonction pour rafraîchir le panier sans recharger la page
async function refreshCart() {
    try {
        // Option 1: Recharger les données via AJAX
        const response = await fetch('?action=getCart');
        const newCart = await response.json();
        
        // Mettre à jour l'interface
        if (window.paiementAside && window.paiementAside.update) {
            window.paiementAside.update(newCart);
        }
        
        // Option 2: Rechargement simple (plus facile)
        window.location.reload();
        
    } catch (error) {
        console.error('Erreur lors du rafraîchissement:', error);
        window.location.reload(); // Fallback
    }
}

// Initialisation au chargement de la page
document.addEventListener('DOMContentLoaded', function() {
    // Stocker la référence à l'aside pour les mises à jour
    if (window.initAside && document.getElementById('recap')) {
        const cartData = window.__PAYMENT_DATA__?.cart || [];
        window.paiementAside = window.initAside('#recap', cartData, refreshCart);
    }
});
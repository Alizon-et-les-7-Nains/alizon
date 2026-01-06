
class LuhnValidator {
    // Algorithme de Luhn pour valider les cartes bancaires
    static validate(cardNumber) {
        const cleaned = cardNumber.replace(/\s+/g, '');
        if (cleaned.length === 0 || !/^\d+$/.test(cleaned)) {
            return false;
        }
        
        const digits = cleaned.split('').reverse().map(d => Number(d));
        let sum = 0;
        
        for (let i = 0; i < digits.length; i++) {
            let digit = digits[i];
            
            // Double chaque deuxième chiffre
            if (i % 2 === 1) {
                digit *= 2;
                if (digit > 9) {
                    digit -= 9;
                }
            }
            
            sum += digit;
        }
        
        return sum % 10 === 0;
    }
    
    // Vérifie si c'est une carte Visa (commence par 4)
    static isVisa(cardNumber) {
        const cleaned = cardNumber.replace(/\s+/g, '');
        return /^4\d{12}(?:\d{3})?$/.test(cleaned) && this.validate(cleaned);
    }
    
    // Formate le numéro de carte (XXXX XXXX XXXX XXXX)
    static formatCardNumber(input) {
        const value = input.value.replace(/\D/g, '');
        let formatted = '';
        
        for (let i = 0; i < value.length && i < 16; i++) {
            if (i > 0 && i % 4 === 0) {
                formatted += ' ';
            }
            formatted += value[i];
        }
        
        input.value = formatted;
        
        // Validation en temps réel
        if (formatted.replace(/\s/g, '').length >= 16) {
            const cardInput = document.querySelector('.num-carte');
            if (cardInput) {
                const isValid = this.validate(formatted);
                const isVisaCard = /^4/.test(formatted.replace(/\s/g, ''));
                
                if (!isValid) {
                    this.setFieldError(cardInput, 'Numéro de carte invalide (algorithme de Luhn)');
                } else if (!isVisaCard) {
                    this.setFieldError(cardInput, 'Carte non-Visa détectée (les cartes Visa commencent par 4)');
                } else {
                    this.clearFieldError(cardInput);
                }
            }
        }
    }
    
    // Formate la date d'expiration (MM/AA)
    static formatExpirationDate(input) {
        let value = input.value.replace(/\D/g, '');
        
        if (value.length > 2) {
            value = value.substring(0, 2) + '/' + value.substring(2, 4);
        }
        
        input.value = value;
    }
    
    // Formate le CVV (3 chiffres)
    static formatCVV(input) {
        input.value = input.value.replace(/\D/g, '').substring(0, 3);
    }
    
    // Gestion des erreurs de champ
    static setFieldError(input, message) {
        if (!input) return;
        
        input.classList.add('invalid');
        const container = input.parentElement;
        if (!container) return;
        
        let err = container.querySelector('.error-message');
        if (!err) {
            err = document.createElement('small');
            err.className = 'error-message';
            container.appendChild(err);
        }
        err.textContent = message;
        err.style.display = 'block';
    }
    
    static clearFieldError(input) {
        if (!input) return;
        
        input.classList.remove('invalid');
        const container = input.parentElement;
        if (!container) return;
        
        const err = container.querySelector('.error-message');
        if (err) {
            err.textContent = '';
            err.style.display = 'none';
        }
    }
    
    // Valide la date d'expiration
    static validateExpirationDate(dateStr) {
        const [month, year] = dateStr.split('/');
        if (!month || !year) return false;
        
        const mm = parseInt(month, 10);
        let yy = parseInt(year, 10);
        
        // Si année sur 2 chiffres, ajouter 2000
        if (year.length === 2) yy += 2000;
        
        if (mm < 1 || mm > 12 || isNaN(yy)) return false;
        
        const now = new Date();
        const expDate = new Date(yy, mm, 0); // Dernier jour du mois
        
        return expDate > now;
    }
    
    // Valide le CVV
    static validateCVV(cvv) {
        return /^\d{3}$/.test(cvv);
    }
}

// Export pour utilisation globale
window.LuhnValidator = LuhnValidator;
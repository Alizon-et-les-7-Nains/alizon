
import { CartItem } from "./paiement-types";

declare global {
  interface Window {
    // Stockage optionnel des données de paiement / panier injectées depuis le back-end
    __PAYMENT_DATA__?: {
      cart?: CartItem[];
      [key: string]: any;
    };
    // API de paiement externe éventuellement fournie (facultative)
    PaymentAPI?: any;
    // Fonction de chiffrement Vigenère fournie globalement (facultative)
    vignere?: (texte: string, cle: string, sens: number) => string;
    // Clé globale de chiffrement (facultative)
    CLE_CHIFFREMENT?: string;
    // ID d'adresse de facturation stockée globalement (facultatif)
    idAdresseFacturation?: number | null;
  }
}

// Fonction helper pour chiffrer avec Vigenère si disponible.
// sens = 1 pour chiffrement, sens = -1 (ou autre) pour déchiffrement selon implémentation globale.
const chiffrerAvecVignere = (texte: string, sens: number): string => {
  // Clé par défaut si aucune fournie via window
  const cle = window.CLE_CHIFFREMENT || "?zu6j,xX{N12I]0r6C=v57IoASU~?6_y";

  // Utilise la fonction vignere globale si elle existe et que la clé est valide
  if (typeof window.vignere === "function" && cle && cle.length > 0) {
    return window.vignere(texte, cle, sens);
  }

  // Si pas de fonction de chiffrement, log et retourne le texte en clair.
  console.warn(
    "Fonction vignere non disponible ou clé invalide, retour du texte en clair"
  );
  return texte;
};

// Fonction utilitaire pour encoder des données en application/x-www-form-urlencoded
// (non utilisée dans la version finale qui utilise FormData, mais conservée pour référence)
const encodeFormData = (data: any): string => {
  const formData = new URLSearchParams();
  formData.append("action", "createOrder");

  Object.keys(data).forEach((key) => {
    if (key !== "action") {
      // Convertit la valeur en chaîne et l'ajoute aux paramètres
      const value = String(data[key]);
      formData.append(key, value);
    }
  });

  return formData.toString();
};

// Fonction principale exportée : affiche un popup récapitulatif de commande.
// message : texte à afficher (pas utilisé intensément ici, conservé pour extensibilité)
// type : style du popup ("error" | "success" | "info")
export function showPopup(
  message: string,
  type: "error" | "success" | "info" = "info"
) {
  // Création d'un overlay couvrant la page, classé par type pour le style
  const overlay = document.createElement("div");
  overlay.className = `payment-overlay ${type}`;

  // Lecture des inputs présents dans la page (sélecteurs ciblés pour pagePaiement)
  const adresseInput = document.querySelector(
    "body.pagePaiement .adresse-input"
  ) as HTMLInputElement | null;
  const codePostalInput = document.querySelector(
    "body.pagePaiement .code-postal-input"
  ) as HTMLInputElement | null;
  const villeInput = document.querySelector(
    "body.pagePaiement .ville-input"
  ) as HTMLInputElement | null;
  const numCarteInput = document.querySelector(
    "body.pagePaiement .num-carte"
  ) as HTMLInputElement | null;
  const nomCarteInput = document.querySelector(
    "body.pagePaiement .nom-carte"
  ) as HTMLInputElement | null;
  const carteDateInput = document.querySelector(
    "body.pagePaiement .carte-date"
  ) as HTMLInputElement | null;
  const cvvInput = document.querySelector(
    "body.pagePaiement .cvv-input"
  ) as HTMLInputElement | null;

  // Extraction et normalisation des valeurs des champs (trim, suppression d'espaces pour numéro de carte)
  const adresse = adresseInput?.value.trim() || "";
  const codePostal = codePostalInput?.value.trim() || "";
  const ville = villeInput?.value.trim() || "";
  const rawNumCarte = numCarteInput?.value.replace(/\s+/g, "") || "";
  const nomCarte = nomCarteInput?.value.trim() || "";
  const dateCarte = carteDateInput?.value.trim() || "";
  const rawCVV = cvvInput?.value.trim() || "";

  // Vérification simple que tous les champs requis sont renseignés avant d'ouvrir le popup
  if (
    !adresse ||
    !codePostal ||
    !ville ||
    !rawNumCarte ||
    !nomCarte ||
    !dateCarte ||
    !rawCVV
  ) {
    alert("Veuillez remplir tous les champs obligatoires");
    return;
  }

  // CHIFFREMENT DES DONNÉES SENSIBLES via la fonction chiffrerAvecVignere (si disponible)
  const numeroCarteChiffre = chiffrerAvecVignere(rawNumCarte, 1);
  const cvvChiffre = chiffrerAvecVignere(rawCVV, 1);

  // Conserver les 4 derniers chiffres pour l'affichage dans le récapitulatif
  const last4 = rawNumCarte.length >= 4 ? rawNumCarte.slice(-4) : rawNumCarte;

  // Détermination d'une région simple à partir du code postal (ex : Département XX)
  let region = "";
  if (codePostal.length >= 2) {
    const codeDept =
      codePostal.length === 5
        ? codePostal.slice(0, 2)
        : codePostal.padStart(2, "0");
    region = `Département ${codeDept}`;
  }

  // Récupération du panier injecté via window.__PAYMENT_DATA__.cart si présent
  const preCart = Array.isArray(window.__PAYMENT_DATA__?.cart)
    ? (window.__PAYMENT_DATA__!.cart as any[])
    : [];
  let cartItemsHtml = "";

  // Construction du HTML du panier (images / titres / quantités / prix)
  if (Array.isArray(preCart) && preCart.length > 0) {
    cartItemsHtml = preCart
      .map(
        (item: any) => `
      <div class="product">
        <img src="${item.img || "/images/default.png"}" alt="${item.nom}" />
        <p class="title">${item.nom}</p>
        <p><strong>Quantité :</strong> ${item.qty}</p>
        <p><strong>Prix total :</strong> ${(item.prix * item.qty).toFixed(
          2
        )} €</p>
      </div>`
      )
      .join("");
  } else {
    // Message si panier vide
    cartItemsHtml = `<p class="empty">Panier vide</p>`;
  }

  // Injection du contenu HTML du popup dans l'overlay
  overlay.innerHTML = `
    <div class="payment-popup" role="dialog" aria-modal="true" data-type="${type}">
      <button class="close-popup" aria-label="Fermer">✕</button>
      <div class="order-summary">
        <h2>Récapitulatif de commande</h2>
        <div class="info">
          <p><strong>Adresse de livraison :</strong> ${adresse} ${codePostal} ${ville}</p>
          <p><strong>Payé avec :</strong> Carte Visa finissant par ${last4}</p>
        </div>
        <h3>Contenu du panier :</h3>
        <div class="cart">${cartItemsHtml}</div>
        <div class="actions">
          <button class="undo">Annuler</button>
          <button class="confirm">Confirmer ma commande</button>
        </div>
      </div>
    </div>
  `;

  // Ajout de l'overlay au DOM
  document.body.appendChild(overlay);

  // Récupération des boutons du popup pour attacher les événements
  const closeBtn = overlay.querySelector(
    ".close-popup"
  ) as HTMLButtonElement | null;
  const undoBtn = overlay.querySelector(".undo") as HTMLButtonElement | null;
  const confirmBtn = overlay.querySelector(
    ".confirm"
  ) as HTMLButtonElement | null;

  // Fonction utilitaire de suppression de l'overlay du DOM
  let removeOverlay = () => {
    if (document.body.contains(overlay)) {
      document.body.removeChild(overlay);
    }
  };

  // Fermeture simple via bouton fermer ou annuler
  closeBtn?.addEventListener("click", removeOverlay);
  undoBtn?.addEventListener("click", removeOverlay);

  // Si le bouton confirmer n'existe pas, on stoppe
  if (!confirmBtn) return;

  // Handler pour le clic sur Confirmer ma commande
  confirmBtn.addEventListener("click", async () => {
    const popup = overlay.querySelector(".payment-popup") as HTMLElement | null;
    if (!popup) return;

    // Indicateur visuel de traitement : désactive le bouton et change le texte
    const originalText = confirmBtn.textContent;
    confirmBtn.textContent = "Traitement en cours...";
    confirmBtn.disabled = true;

    try {
      // Vérification que la sécurité (vignere) est disponible
      if (!window.vignere) {
        throw new Error("Système de sécurité non disponible");
      }

      // Re-vérification des champs requis (sécurité côté client)
      if (
        !adresse ||
        !codePostal ||
        !ville ||
        !rawNumCarte ||
        !nomCarte ||
        !dateCarte ||
        !rawCVV
      ) {
        throw new Error("Tous les champs sont obligatoires");
      }

      // Récupérer l'ID de l'adresse de facturation si défini globalement
      const idAdresseFact = window.idAdresseFacturation || null;

      // Préparation des données de la commande (inclut les versions chiffrées)
      const orderData: any = {
        adresseLivraison: adresse,
        villeLivraison: ville,
        regionLivraison: region,
        numeroCarte: numeroCarteChiffre,
        cvv: cvvChiffre,
        nomCarte: nomCarte,
        dateExpiration: dateCarte,
        codePostal: codePostal,
      };

      // Inclut l'ID de facturation si disponible
      if (idAdresseFact) {
        orderData.idAdresseFacturation = idAdresseFact;
        console.log(
          "Utilisation de l'adresse de facturation ID:",
          idAdresseFact
        );
      }

      // Résultat de l'appel vers le serveur ou l'API de paiement
      let result;

      // Si une API de paiement globale est fournie, on l'utilise en priorité
      if (
        window.PaymentAPI &&
        typeof window.PaymentAPI.createOrder === "function"
      ) {
        console.log("Utilisation de PaymentAPI");
        result = await window.PaymentAPI.createOrder(orderData);
      } else {
        // Sinon, fallback vers un fetch POST direct en utilisant FormData pour l'encodage
        console.log("Utilisation de fetch direct");

        // FormData gère correctement l'encodage des champs pour un POST multipart/form-data
        const formData = new FormData();
        formData.append("action", "createOrder");

        // Ajout des champs de orderData à la FormData
        Object.keys(orderData).forEach((key) => {
          formData.append(key, orderData[key]);
        });

        // Appel fetch vers l'URL courante (chaîne vide => la même page) :
        const response = await fetch("", {
          method: "POST",
          body: formData, // FormData gère automatiquement l'encodage
        });

        // Vérification du statut HTTP
        if (!response.ok) {
          throw new Error(`Erreur HTTP: ${response.status}`);
        }

        // Tentative de parsing JSON de la réponse
        result = await response.json();
      }

      // Gestion de la réponse : si succès, afficher un message de remerciement
      if (result.success) {
        popup.innerHTML = `
        <div class="thank-you">
          <h2>Merci de votre commande !</h2>
          <p>Votre commande a bien été enregistrée.</p>
          <p><strong>Numéro de commande :</strong> ${result.idCommande}</p>
          <button class="close-popup">Retour à l'accueil</button>
        </div>
      `;

        // Bouton interne pour rediriger vers l'accueil (ici un chemin relatif)
        const innerClose = popup.querySelector(
          ".close-popup"
        ) as HTMLButtonElement | null;
        innerClose?.addEventListener("click", () => {
          // Redirection vers la page d'accueil connectée
          window.location.href = "../../views/frontoffice/accueilConnecte.php";
        });
      } else {
        // Si result.success falsy, lever une erreur avec le message renvoyé
        throw new Error(
          result.error || "Erreur inconnue lors de la création de la commande"
        );
      }
    } catch (error) {
      // Log détaillé pour debug
      console.error("Erreur complète:", error);

      // Construire un message d'erreur utilisateur plus lisible
      let errorMessage = "Erreur lors de la création de la commande";
      if (error instanceof Error) {
        if (error.message.includes("SyntaxError")) {
          errorMessage = "Erreur de format des données. Veuillez réessayer.";
        } else if (error.message.includes("HTTP")) {
          errorMessage = "Erreur de communication avec le serveur.";
        } else {
          errorMessage = error.message;
        }
      }

      // Afficher le message d'erreur au client
      alert(errorMessage);

      // Réactiver le bouton confirmer et restaurer le texte original
      confirmBtn.textContent = originalText;
      confirmBtn.disabled = false;
    }
  });

  // Fermeture du popup en cliquant sur l'overlay (en dehors du popup)
  overlay.addEventListener("click", (e) => {
    if (e.target === overlay) {
      removeOverlay();
    }
  });

  // Gestion de la touche Escape pour fermer le popup
  const handleEscape = (e: KeyboardEvent) => {
    if (e.key === "Escape") {
      removeOverlay();
      document.removeEventListener("keydown", handleEscape);
    }
  };
  document.addEventListener("keydown", handleEscape);

  // Nettoyage : s'assurer que l'écouteur sur keydown est supprimé lorsque l'overlay est retiré
  const originalRemove = removeOverlay;
  removeOverlay = () => {
    document.removeEventListener("keydown", handleEscape);
    originalRemove();
  };
}

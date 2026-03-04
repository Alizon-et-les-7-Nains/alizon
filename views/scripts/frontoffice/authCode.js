// Gestion de l'authentification à deux facteurs
const form = document.querySelector("#formA2F");
const inputs = form ? form.querySelectorAll('input[type="text"]') : [];

// Automatiser le passage entre les champs
if (inputs.length > 0) {
  inputs.forEach((input, index) => {
    // Focus sur le premier champ au chargement
    if (index === 0) {
      input.focus();
    }

    // Passer au champ suivant après saisie
    input.addEventListener("input", (e) => {
      const value = e.target.value;

      // Ne garder que les chiffres
      if (!/^[0-9]$/.test(value)) {
        e.target.value = "";
        return;
      }

      // Passer au champ suivant si rempli
      if (value && index < inputs.length - 1) {
        inputs[index + 1].focus();
      }

      // Soumettre automatiquement quand le dernier champ est rempli
      if (value && index === inputs.length - 1) {
        form.submit();
      }
    });

    // Gérer la touche Backspace pour revenir en arrière
    input.addEventListener("keydown", (e) => {
      if (e.key === "Backspace" && !e.target.value && index > 0) {
        inputs[index - 1].focus();
      }
    });

    // Gérer le collage d'un code complet
    input.addEventListener("paste", (e) => {
      e.preventDefault();
      const pastedData = e.clipboardData.getData("text").replace(/\D/g, "");

      if (pastedData.length === 6) {
        inputs.forEach((inp, i) => {
          inp.value = pastedData[i] || "";
        });
        inputs[5].focus();
      }
    });
  });

  // Gérer la soumission du formulaire
  form.addEventListener("submit", async (e) => {
    e.preventDefault();

    // Récupérer le code complet
    const code = Array.from(inputs)
      .map((inp) => inp.value)
      .join("");

    // Vérifier que tous les champs sont remplis
    if (code.length !== 6) {
      const erreur = document.querySelector("#erreurCodeA2F");
      erreur.textContent = "Veuillez entrer les 6 chiffres";
      erreur.style.display = "block";
      return;
    }

    try {
      const response = await fetch(window.location.href, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({ otp: code }),
      });

      const data = await response.json();

      if (data.success) {
        // Code correct : rediriger vers l'accueil
        window.location.href = "../../views/frontoffice/accueilConnecte.php";
      } else {
        // Code incorrect : afficher l'erreur
        const erreur = document.querySelector("#erreurCodeA2F");
        erreur.textContent = "Code incorrect";
        erreur.style.display = "block";

        // Réinitialiser les champs
        inputs.forEach((inp) => (inp.value = ""));
        inputs[0].focus();
      }
    } catch (err) {
      console.error("Erreur fetch:", err);
      const erreur = document.querySelector("#erreurCodeA2F");
      erreur.textContent = "Erreur de connexion";
      erreur.style.display = "block";
    }
  });
}

// Fonction pour fermer la popup A2F
function fermerPopupA2F() {
  // Vider les champs et afficher un message
  inputs.forEach((inp) => (inp.value = ""));
  // Rediriger vers la page de connexion
  window.location.href = "connexionClient.php";
}

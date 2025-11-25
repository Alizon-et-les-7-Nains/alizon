<?php 
    $currentPage = basename(__FILE__);
    require_once "../../controllers/pdo.php"; 

    // Gestion de la session
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $message = $_SESSION['message'] ?? ''; 
    unset($_SESSION['message']);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../public/style.css">
    <title>Connexion à un compte vendeur</title>
</head>

<body>
    <?php require_once "./partials/header.php"; ?>

    <main class="connexionVendeur">
        <img class="triskiel" src="../../public/images/triskiel gris.svg" alt="">

        <div class="pdp_title">
            <div class="cercle-pdp">
                <img src="../../public/images/pdp_user.svg" alt="Avatar" onerror="this.style.display='none'; this.nextElementSibling.style.display='block'">
            </div>
            <h1>Connexion à votre compte vendeur Alizon</h1>
        </div>

        <div class="information_connexion">
            <form method="post" class="form-vendeur" id="monForm" action="../../controllers/connexionCompteVendeur.php">
                
                <?php if (!empty($message)) : ?>
                    <div class="alert alert-danger text-center w-100 mb-3" role="alert" style="color: red; text-align: center;">
                        <?= htmlspecialchars($message) ?>
                    </div>
                <?php endif; ?>

                <div class="inputs-container">
                    <div class="mb-4">
                        <input type="text" id="pseudo" name="pseudo" placeholder="Nom d'utilisateur" required class="form-control custom-input">
                    </div>

                    <div class="mb-2">
                        <input type="password" id="mdp" placeholder="Mot de passe" required class="form-control custom-input">
                        
                        <input type="hidden" name="password_chiffre" id="password_chiffre">
                    </div>
                </div>

                <div class="liens-utiles">
                    <a href="CreerCompteVendeur.php">Pas encore vendeur ? Inscrivez vous ici</a>
                    <a href="#">Mot de passe oublié ? Cliquez ici</a>
                </div>

                <div class="actions">
                    <button type="submit" id="btnConnexion" class="btn_connexion" disabled>Se connecter</button>
                </div>
            </form>
        </div>

        <p class="text-footer">
            Alizon, en tant que responsable de traitement, traite les données recueillies à des fins de gestion de la relation client, 
            gestion des commandes et des livraisons, personnalisation des services, prévention de la fraude, marketing et publicité ciblée. 
            Pour en savoir plus, reportez-vous à la Politique de protection de vos données personnelles.
        </p>
    </main>
    
    <?php require_once "./partials/footer.php"; ?>

    <script src="../../controllers/Chiffrement.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Éléments
            const form = document.getElementById('monForm');
            const pseudoInput = document.getElementById('pseudo');
            const mdpInput = document.getElementById('mdp');
            const hiddenMdpInput = document.getElementById('password_chiffre');
            const btnConnexion = document.getElementById('btnConnexion');

            // Active ou désactive le btn se connnecter
            function verifierChamps() {
                // Si pseudo et mdp sont remplis
                if (pseudoInput.value.trim() !== "" && mdpInput.value.trim() !== "") {
                    btnConnexion.disabled = false; // On active le bouton
                    btnConnexion.style.cursor = "pointer"; 
                } else {
                    btnConnexion.disabled = true;  // On désactive le bouton
                    btnConnexion.style.cursor = "not-allowed";
                }
            }

            // Vérification
            pseudoInput.addEventListener('input', verifierChamps);
            mdpInput.addEventListener('input', verifierChamps);


            // Chiffrement du formulaire envoyé
            form.addEventListener('submit', function(e) {
                e.preventDefault(); // On bloque l'envoi immédiat

                const passwordClair = mdpInput.value;

                // On vérifie si la fonction de chiffrement est bien chargée
                if (typeof vignere !== 'undefined') {

                    try {
                        const passwordChiffre = vignere(passwordClair, cle, 1);
                        hiddenMdpInput.value = passwordChiffre; // On met le code chiffré dans l'input caché
                        
                        console.log("Mot de passe chiffré prêt à l'envoi");
                        
                        // On envoie le formulaire
                        this.submit();
                    } catch (error) {
                        console.error("Erreur de chiffrement : ", error);
                        alert("Erreur technique lors du chiffrement.");
                    }
                } else {
                    alert("Erreur : Le script de sécurité n'est pas chargé.");
                }
            });
        });
    </script>
</body>
</html>
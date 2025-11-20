<?php 
session_start();
$currentPage=basename(FILE);
require_once "../../controllers/pdo.php";

    $nom = $_SESSION['form_data']['nom'] ?? '';
    $prenom = $_SESSION['form_data']['prenom'] ?? '';
    $email = $_SESSION['form_data']['email'] ?? '';
    $noTelephone = $_SESSION['form_data']['noTelephone'] ?? '';
    $pseudo = $_SESSION['form_data']['pseudo'] ?? '';
    $dateNaissance = $_SESSION['form_data']['dateNaissance'] ?? '';
    $noSiren = $_SESSION['form_data']['noSiren'] ?? '';
    $idAdresse = $_SESSION['form_data']['idAdresse'] ?? '';
    $raisonSocial = $_SESSION['form_data']['raisonSocial'] ?? '';
    $message = $_SESSION['form_data']['message'] ?? ''; 
    unset($_SESSION['form_data']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../public/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <title>Ajouter un produit au catalogue</title>
</head>
<body class="backoffice">
    <header>
        <?php require_once "./partials/header.php"?>
    </header>
    <?php require_once "./partials/aside.php"?>
        
    <main class="AjouterProduit"> 
        <form action="../../controllers/ajouterProduit.php" method="POST" enctype="multipart/form-data" class="product-content" id="formAjout">
            
            <div class="left-section">
                <div class="ajouterPhoto" id="zoneUpload">
                    <input type="file" id="photoUpload" name="photo" accept="image/*" hidden>
                    
                    <div class="etat-vide" id="etatVide">
                        <div class="icone-wrapper">
                            <img src="../../public/images/ajouterPhoto.svg" alt="Icône ajout">
                        </div>
                        <p>Cliquer pour ajouter une photo</p>
                    </div>

                    <div class="etat-preview" id="etatPreview" style="display: none;">
                        <img src="" alt="Prévisualisation" id="imagePreview">
                        <div class="overlay-modifier">
                            <span>Cliquer pour modifier la photo</span>
                        </div>
                    </div>
                </div>

                <div class="form-details">
                    <input type="text" name="nom" class="product-name-input" placeholder="Intitulé du produit" required>
                
                    <div class="price-weight-kg">
                        <input type="number" step="0.01" name="prix" id="inputPrix" placeholder="Prix (€)" required>
                        <input type="number" step="0.01" name="poids" id="inputPoids" placeholder="Poids (kg)" required>
                        <span class="prix-kg-label" id="labelPrixKg">Prix au Kg: -- €</span>
                    </div>

                    <input type="text" name="mots_cles" class="keywords-input" placeholder="Mots clés (séparés par des virgules)">
                </div>
            </div>

            <div class="right-section">
                <div class="product-desc-box">
                    <label for="product-description">Description du produit</label>
                    <textarea id="product-description" name="description" placeholder="Description de votre produit" maxlength="1000" required></textarea>
                    <div class="char-count" id="charCount">0/1000</div> 
                </div>

                <div class="form-actions">
                    <button type="button" class="btn-previsualiser">Prévisualiser</button>
                    <button type="button" class="btn-annuler" id="btnAnnuler">Annuler</button>
                    <button type="submit" class="btn-ajouter">Ajouter le produit</button>
                </div>
            </div>
        </form>
    </main>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const zoneUpload = document.getElementById('zoneUpload');
        const photoInput = document.getElementById('photoUpload');
        const etatVide = document.getElementById('etatVide');
        const etatPreview = document.getElementById('etatPreview');
        const imagePreview = document.getElementById('imagePreview');
        
        const textArea = document.getElementById('product-description');
        const charCountDisplay = document.getElementById('charCount');
        const btnAnnuler = document.getElementById('btnAnnuler');
        const form = document.getElementById('formAjout');

        // calcul du prix
        const inputPrix = document.getElementById('inputPrix');
        const inputPoids = document.getElementById('inputPoids');
        const labelPrixKg = document.getElementById('labelPrixKg');

        // pour la photo
        zoneUpload.addEventListener('click', () => photoInput.click());

        photoInput.addEventListener('change', function() {
            const file = this.files[0];
            if (file && file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    imagePreview.src = e.target.result;
                    etatVide.style.display = 'none';
                    etatPreview.style.display = 'block';
                };
                reader.readAsDataURL(file);
            }
        });

        // comptage carac pour la description
        textArea.addEventListener('input', function() {
            const currentLength = this.value.length;
            charCountDisplay.textContent = `${currentLength}/1000`;
            charCountDisplay.style.color = currentLength >= 1000 ? '#e74c3c' : 'gray';
        });

        // calcul du prix au kg 
        function calculerPrixKg() {
            const prix = parseFloat(inputPrix.value);
            const poids = parseFloat(inputPoids.value);

            if (!isNaN(prix) && !isNaN(poids) && poids > 0) {
                const prixKg = (prix / poids).toFixed(2);
                labelPrixKg.textContent = `Prix au Kg: ${prixKg} €`;
                labelPrixKg.style.color = '#1D3B54';
            } else {
                labelPrixKg.textContent = "Prix au Kg: -- €";
                labelPrixKg.style.color = 'gray';
            }
        }

        inputPrix.addEventListener('input', calculerPrixKg);
        inputPoids.addEventListener('input', calculerPrixKg);

        // Btn annuler
        btnAnnuler.addEventListener('click', function() {
            form.reset();
            // Reset image
            imagePreview.src = "";
            etatPreview.style.display = 'none';
            etatVide.style.display = 'flex';
            // Reset compteur
            charCountDisplay.textContent = "0/1000";
            charCountDisplay.style.color = 'gray';
            // Reset Prix Kg
            labelPrixKg.textContent = "Prix au Kg: -- €";
        });
    });
    </script>

    <?php require_once "./partials/footer.php"?>
</body>
</html>
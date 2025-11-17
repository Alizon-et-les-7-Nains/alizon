<?php // require_once "../../controllers/pdo.php" ?>
<!DOCTYPE html>
<html lang="en">
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
        <div class="product-content">
            <div class="left-section">
                <div class="ajouterPhoto">
                    <input type="file" id="photoUpload" name="photo" accept="image/*" style="display: none;">
                    <div class="placeholder-photo">
                        <img src="../../../public/images/ajouterPhoto.svg" alt="Ajouter une photo" id="imagePreview">
                        <p id="placeholderText">Cliquer pour ajouter une photo</p>
                        <div class="overlay-text" id="overlayText">Cliquer pour modifier</div>
                    </div>
                </div>

                <div class="form-details">
                    <input type="text" class="product-name-input" placeholder="Intitulé du produit" required>
                
                    <div class="price-weight-kg">
                        <input type="text" placeholder="Prix" required>
                        <input type="text" placeholder="Poids" required>
                        <span class="prix-kg-label">Prix au Kg:</span>
                    </div>

                    <!-- <input type="text" class="keywords-input" placeholder="Mots clés (séparés par des virgules)"> -->
                </div>
            </div>

            <div class="right-section">
                <div class="ajouterResume resume-box">
                    <label for="resume">Résumé du produit</label>
                    <textarea name="resume" id="resume" placeholder>Décrivez votre produit en quelques mots</textarea>
                </div>
            <h2>Plus d'informations</h2>

            <div id="sections-container"></div>

                <div class="ajouterSection">
                    <p>Etoffez la description de votre produit en ajoutant une section</p>
                    <select id="section-type">
                        <option value="both">Titre + Description</option>
                        <option value="title">Titre seulement</option>
                        <option value="desc">Description seulement</option>
                    </select>
                    <button id="add-section-btn" type="button">Ajouter une section</button>
                </div>

            <div class="form-actions">
                <a href="#"><button type="button" class="btn-previsualiser">Prévisualiser</button></a>
                <a href="#"><button type="button" class="btn-annuler">Annuler</button></a>
                <a href="#"><button type="submit" class="btn-ajouter">Ajouter le produit</button></a>
            </div>
        </div>
    </main>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const photoUploadInput = document.getElementById('photoUpload');
        const ajouterPhotoDiv = document.querySelector('.ajouterPhoto'); 
        const imagePreview = document.getElementById('imagePreview');
        const placeholderText = document.getElementById('placeholderText');
        const overlayText = document.getElementById('overlayText');
        const addSectionBtn = document.getElementById('add-section-btn');
        const sectionsContainer = document.getElementById('sections-container');
        const sectionTypeSelect = document.getElementById('section-type');
        const resumeTextarea = document.getElementById('resume');

        const originalImageSrc = imagePreview.src;

        // Gestion du clic pour upload d'image
        ajouterPhotoDiv.addEventListener('click', function() {
            photoUploadInput.click();
        });

        photoUploadInput.addEventListener('change', function() {
            const files = this.files;
            if (files && files.length > 0) {
                const file = files[0];
                if (file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        imagePreview.src = e.target.result;
                        placeholderText.style.display = 'none';
                        overlayText.style.opacity = '1';
                    };
                    reader.readAsDataURL(file);
                } else {
                    imagePreview.src = originalImageSrc;
                    placeholderText.style.display = 'block';
                    overlayText.style.opacity = '0';
                    alert("Votre fichier n'est pas une image, merci de réessayer.");
                }
            } else {
                imagePreview.src = originalImageSrc;
                placeholderText.style.display = 'block';
                overlayText.style.opacity = '0';
            }
        });

        // Fonction pour vérifier si le bouton doit disparaître
        function checkSections() {
            const allSections = sectionsContainer.querySelectorAll('.new-section-box');
            let hasTitle = resumeTextarea.value.trim() !== ''; // prend en compte le résumé
            let hasDesc = resumeTextarea.value.trim() !== ''; // si tu veux le résumé aussi comme description

            allSections.forEach(section => {
                const titleInput = section.querySelector('input[type="text"]');
                const descTextarea = section.querySelector('textarea');

                if (titleInput && titleInput.value.trim() !== '') hasTitle = true;
                if (descTextarea && descTextarea.value.trim() !== '') hasDesc = true;
            });

            // Si on a à la fois un titre et une description quelque part, on cache le bouton
            if (hasTitle && hasDesc) {
                addSectionBtn.style.display = 'none';
            } else {
                addSectionBtn.style.display = 'inline-block';
            }
        }

        // Créer une nouvelle section
        function createNewSection(){
            const type = sectionTypeSelect.value;
            const newSection = document.createElement('div');
            newSection.classList.add('new-section-box');

            let sectionHTML = '';

            if(type === "both" || type === "title"){
                sectionHTML += `
                    <div class="input-group">
                        <label>Titre de la section</label>
                        <input type="text" placeholder="Ex: Ingrédients">
                    </div>
                `;
            }

            if(type === "both" || type === "desc"){
                sectionHTML += `
                    <div class="input-group">
                        <label>Description</label>
                        <textarea placeholder="Détaillez le contenu de cette section."></textarea>
                    </div>
                `;
            }

            sectionHTML += `
                <button type="button" class="btn-delete-section" title="Supprimer la section">
                    <i class="bi bi-x-circle-fill"></i>
                </button>
            `;

            newSection.innerHTML = sectionHTML;

            // Supprimer une section
            newSection.querySelector('.btn-delete-section').addEventListener('click', function(){
                newSection.remove();
                checkSections();
            });

            sectionsContainer.appendChild(newSection);
            checkSections(); // Vérifie après ajout

            // Ajouter un margin-bottom pour séparer les boutons du bas du footer
            const formActions = document.querySelector('.form-actions');
            formActions.style.marginBottom = '50px';
        }

        // Ajout de sections au clic
        addSectionBtn.addEventListener('click', createNewSection);

        // Vérification au changement du résumé ou des sections
        resumeTextarea.addEventListener('input', checkSections);
        sectionsContainer.addEventListener('input', checkSections);
    });


    </script>
    <?php require_once "./partials/footer.php"?>
</body>
</html>

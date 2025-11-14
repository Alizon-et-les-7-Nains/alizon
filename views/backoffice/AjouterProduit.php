<?php require_once "../../controllers/pdo.php" ?>
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

                    <input type="text" class="keywords-input" placeholder="Mots clés (séparés par des virgules)">
                </div>
            </div>

            <div class="right-section">
                <div class="right-section">
                <div class="ajouterResume resume-box">
                    <label for="resume">Résumé du produit (Affiché en haut de page)</label>
                    <textarea name="resume" id="resume" placeholder>Décrivez votre produit en quelques mots</textarea>
                </div>
                <h2>Plus d'informations</h2>
                <div class="ajouterSection">
                    <p>Etoffez la description de votre produit en ajoutant une première section</p>
                    <button id="add-section-btn" type="button">Ajouter une section</button>
                </div>
                <div class="form-actions">
                    <a href="#"><button type="button" class="btn-previsualiser">Prévisualiser</button></a>
                    <a href="#"><button type="button" class="btn-annuler">Annuler</button></a>
                    <a href="#"><button type="submit" class="btn-ajouter">Ajouter le produit</button></a>
                </div>
            </div>
            </div>
        </div>
    </main>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        //Récupère les éléments
        const photoUploadInput = document.getElementById('photoUpload');
        const ajouterPhotoDiv = document.querySelector('.ajouterPhoto'); 
        const imagePreview = document.getElementById('imagePreview'); //image
        const placeholderText = document.getElementById('placeholderText'); //paragraphe
        const overlayText = document.getElementById('overlayText'); // texte “cliquer pour modifier”
        
        // Sauvegarde de l'URL par défaut
        const originalImageSrc = imagePreview.src;

        //Déclenche le clic sur l'input de fichier
        ajouterPhotoDiv.addEventListener('click', function() {
            photoUploadInput.click();
        });

        //Gére la sélection du fichier et la prévisualisation
        photoUploadInput.addEventListener('change', function() {
            const files = this.files;
            
            if (files && files.length > 0) {
                const file = files[0];
                
                if (file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    
                    reader.onload = function(e) {
                        imagePreview.src = e.target.result;
                        placeholderText.style.display = 'none';
                        overlayText.style.display = 'block';
                    };

                    reader.readAsDataURL(file);

                } else {
                    imagePreview.src = originalImageSrc;
                    placeholderText.style.display = 'block';
                    overlayText.style.display = 'none';
                    alert("Votre fichier n'est pas une image, merci de réessayer.");
                }
            } else {
                imagePreview.src = originalImageSrc;
                placeholderText.style.display = 'block';
                overlayText.style.display = 'none';
            }
        });
        // Gestion des sections
        const addSectionBtn = document.getElementById('add-section-btn');
        const sectionsContainer = document.getElementById('sections-container');
        let sectionCount = 0;
    
        // Fonction pour créer une nouvelle section
        function createNewSection(){
            sectionCount ++;
            const newSection = document.createElement('div');
            newSection.classList.add('new-section-box');
            newSection.dataset.sectionId = sectionCount;
    
            newSection.innerHTML = `
                <div class="section-header">
                    <h3 class="section-title">Section n°${sectionCount}</h3>
                    <button type="button" class="btn-delete-section" title="Supprimer la section">
                        <i class="bi bi-x-circle-fill"></i>
                    </button>
                </div>
    
                <div class="input-group">
                    <label for="section-title-${sectionCount}">Titre de la section (H3)</label>
                    <input type="text" id="section-title-${sectionCount}" name="section_title_${sectionCount}" placeholder="Ex: Ingrédients">
                </div>
    
                <div class="input-group">
                    <label for="section-desc-${sectionCount}">Description (P)</label>
                    <textarea id="section-desc-${sectionCount}" name="section_desc_${sectionCount}" placeholder="Détaillez le contenu de cette section."></textarea>
                </div>
            `;
    
            newSection.querySelector('.btn-delete-section').addEventListener('click', function(){
                newSection.remove();
            });
    
            sectionsContainer.appendChild(newSection);
        }
        createNewSection();
        // Ajout de sections supplémentaires au clic
        addSectionBtn.addEventListener('click', createNewSection);
    });


    </script>
    <?php require_once "./partials/footer.php"?>
</body>
</html>

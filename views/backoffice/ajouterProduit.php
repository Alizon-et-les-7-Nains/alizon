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
       
    <main class="ajouterProduit"> 
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
        const addSectionBtn = document.getElementById('add-section-btn');
        const sectionsContainer = document.getElementById('sections-container');

        function createNewSection() {
            const newSection = document.createElement('div');
            newSection.classList.add('new-section-box');

            newSection.innerHTML = `
                <div class="input-group">
                    <label>Titre de la section <span style="color:red">*</span></label>
                    <input type="text" placeholder="Ex: Ingrédients" required>
                </div>

                <div class="input-group">
                    <label>Description (facultatif)</label>
                    <textarea placeholder="Détaillez le contenu de cette section."></textarea>
                </div>

                <button type="button" class="btn-delete-section" title="Supprimer la section">
                    <i class="bi bi-x-circle-fill"></i>
                </button>
            `;

            // Supprime la section
            newSection.querySelector('.btn-delete-section')
                    .addEventListener('click', () => newSection.remove());

            sectionsContainer.appendChild(newSection);

            // Ajout de marge pour pas que ça colle au footer
            const formActions = document.querySelector('.form-actions');
            formActions.style.marginBottom = '50px';
        }

        addSectionBtn.addEventListener('click', createNewSection);
    });

    </script>
    <?php require_once "./partials/footer.php"?>
</body>
</html>

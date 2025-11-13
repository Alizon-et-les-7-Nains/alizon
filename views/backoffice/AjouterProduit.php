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
        <?php require_once "./partials/headerMain.php"?>
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
                <div class="ajouterResume">
                    <textarea name="resume" id="resume" placeholder>Décrivez votreproduit
                    en quelques mots (cette partie s'affichera tout en haut de la page)</textarea>
                </div>
                <div class="ajouterSection">
                </div>

                <div class="form-actions">
                    <a href="#"><button type="button" class="btn-previsualiser">Prévisualiser</button></a>
                    <a href="#"><button type="button" class="btn-annuler">Annuler</button></a>
                    <a href="#"><button type="submit" class="btn-ajouter">Ajouter le produit</button></a>
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
    });
    </script>
    <?php require_once "./partials/footer.php"?>
</body>
</html>

<?php
    require_once "../../controllers/pdo.php";
?>

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
        <form class="product-content" id="monForm" action="../../controllers/updateProduit.php?id=<?php echo($productId)?>" method="post" enctype="multipart/form-data">
            <div class="left-section">
                <div class="ajouterPhoto">
                    <input type="file" id="photoUpload" name="photo" accept="image/*" style="display: none;">
                    <img src="../../../public/images/ajouterPhoto.svg" alt="Ajouter une photo" id="imagePreview">
                    <div class="placeholder-photo">
                        <p id="placeholderText">Cliquer pour ajouter une photo</p>
                        <div class="overlay-text" id="overlayText">Cliquer pour modifier</div>
                    </div>
                </div>

                <div class="form-details">
                    <input type="text" class="product-name-input" placeholder="Intitulé du produit" name="nom" required>
                
                    <div class="price-weight-kg">
                        <input type="text" placeholder="Prix" name="prix" required>
                        <input type="text" placeholder="Poids" name="poids" required>
                        <span class="prix-kg-label">Prix au Kg:</span>
                    </div>
                    <input type="text" class="keywords-input" placeholder="Mots clés (séparés par des virgules)" name="mots_cles" required>
                </div>
            </div>

            <div class="right-section">
                <div class="product-desc-box">
                    <label for="description">Description du produit</label><br>   
                    <textarea name="description" id="description" placeholder="Décrivez votre produit en quelques mots"></textarea>
                    <div class="char-count">0/1000</div>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn-previsualiser" onclick="location.href='#'">Prévisualiser</button>
                    <button type="button" class="btn-annuler" onclick="location.href='#'">Annuler</button>
                    <button type="submit" class="btn-ajouter" onclick="location.href='#'">Ajouter le produit</button>
                </div>
            </div>
        </form>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
    const photoUploadInput = document.getElementById('photoUpload');
    const ajouterPhotoDiv = document.querySelector('.ajouterPhoto'); 
    const imagePreview = document.getElementById('imagePreview');
    const placeholderText = document.getElementById('placeholderText');
    const overlayText = document.getElementById('overlayText');
    const descriptionTextarea = document.getElementById('description');
    const charCount = document.querySelector('.char-count');
    const maxLength = 1000;

    // Si une image est chargée ou pas 
    let imageLoaded = false; 
    overlayText.style.opacity = '0';
    
    // Mettre à jour l'état de l'affichage
    function updatePhotoDisplay(isImageProductLoaded) {
        imageLoaded = isImageProductLoaded;
        if (imageLoaded) {
            placeholderText.style.display = 'none';
            imagePreview.style.opacity = '1';
        } else {
            // Revenir à l'état initial
            imagePreview.src = imagePreview.getAttribute('data-original-src') || "../../../public/images/ajouterPhoto.svg";
            placeholderText.style.display = 'block';
            overlayText.style.opacity = '0';
        }
    }

    // Clic photo
    ajouterPhotoDiv.addEventListener('click', function() {
        photoUploadInput.click();
    });

    // Survol pour afficher le texte pour modifier
    ajouterPhotoDiv.addEventListener('mouseenter', function() {
        if (imageLoaded) {
            overlayText.style.opacity = '1';
        }
    });

    ajouterPhotoDiv.addEventListener('mouseleave', function() {
        overlayText.style.opacity = '0';
    });

    //Sauvegarde de la source de l'icône par défaut
    imagePreview.setAttribute('data-original-src', imagePreview.src);
    // Vérification si il y a déjà une image de produit
    if (!imagePreview.src.includes('ajouterPhoto.svg')) {
         updatePhotoDisplay(true);
    }
    
    // Compteur de caractères
    descriptionTextarea.addEventListener('input', function() {
        const currentLength = this.value.length;
        charCount.textContent = `${currentLength}/${maxLength}`;
    });

    // Gestion du changement de fichier
    photoUploadInput.addEventListener('change', function() {
        const files = this.files;
        if (files && files.length > 0) {
            const file = files[0];
            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    imagePreview.src = e.target.result;
                    updatePhotoDisplay(true); // Image chargée on masque le placeholder
                };
                reader.readAsDataURL(file);
            } else {
                alert("Votre fichier n'est pas une image, merci de réessayer.");
                updatePhotoDisplay(false); // Reviens à l'état initial
            }
        } else {
            if (!imageLoaded) { 
               updatePhotoDisplay(false);
            }
        }
    });
});

    </script>
    <?php require_once "./partials/footer.php"?>
</body>
</html>

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
                <div class="product-desc-box">
                    <label for="product-description">Description du produit</label>
                    <textarea id="product-description" placeholder="Description de votre produit" maxlength="1000"></textarea> 
                    <div class="char-count">0/1000</div> 
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
            // --- Logique d'ajout et prévisualisation de photo ---

            // Récupère les éléments
            const photoUploadInput = document.getElementById('photoUpload');
            const ajouterPhotoDiv = document.querySelector('.ajouterPhoto'); 
            const imagePreview = document.getElementById('imagePreview'); // image
            const placeholderText = document.getElementById('placeholderText'); // paragraphe
            
            // Sauvegarde de l'URL par défaut
            const originalImageSrc = imagePreview.src;

            // Déclenche le clic sur l'input de fichier
            ajouterPhotoDiv.addEventListener('click', function() {
                photoUploadInput.click();
            });

            // Gère la sélection du fichier et la prévisualisation
            photoUploadInput.addEventListener('change', function() {
                const files = this.files;
                
                if (files && files.length > 0) {
                    const file = files[0];
                    
                    if (file.type.startsWith('image/')) {
                        // Création du lecteur de fichier
                        const reader = new FileReader();
                        
                        reader.onload = function(e) {
                            // Met à jour la source de l'image
                            imagePreview.src = e.target.result;
                            // Masque le texte
                            placeholderText.style.display = 'none';
                        };

                        // Lit le fichier
                        reader.readAsDataURL(file);

                    } else {
                        // Si le fichier n'est pas une image
                        imagePreview.src = originalImageSrc;
                        placeholderText.style.display = 'block';
                        alert("Votre fichier n'est pas une image, merci de réessayer.");
                    }
                } else {
                    // Si la sélection est annulée
                    imagePreview.src = originalImageSrc;
                    placeholderText.style.display = 'block';
                }
            });

            // --- Logique de comptage de caractères (Char Count) ---

            const productDescription = document.getElementById('product-description');
            const charCountDisplay = document.querySelector('.char-count');
            const MAX_CHARS = 1000;

            // Fonction de mise à jour du compteur
            function updateCharCount() {
                let currentLength = productDescription.value.length;
                
                // Si on dépasse la limite, on tronque le texte et met à jour la longueur
                if (currentLength > MAX_CHARS) {
                    productDescription.value = productDescription.value.substring(0, MAX_CHARS);
                    currentLength = MAX_CHARS; // S'assure que le compteur affiche la limite
                }

                // Met à jour l'affichage
                charCountDisplay.textContent = `${currentLength}/${MAX_CHARS}`;
                
                // Optionnel : change la couleur si la limite est atteinte
                if (currentLength === MAX_CHARS) {
                    charCountDisplay.style.color = 'red';
                } else {
                    charCountDisplay.style.color = 'gray'; // Couleur normale
                }
            }

            // Écoute l'événement 'input' (chaque frappe, collage, etc.)
            productDescription.addEventListener('input', updateCharCount);

            // Initialise le compteur au chargement de la page (utile si la textarea contient déjà du texte)
            updateCharCount(); 
        });
    </script> 
    
    <?php require_once "./partials/footer.php"?>
</body>
</html>
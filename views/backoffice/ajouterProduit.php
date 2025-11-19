<?php require_once "../../controllers/pdo.php" ?>
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
        <form action="../../controllers/ajouterProduit.php" method="POST" enctype="multipart/form-data" class="product-content" id="formAjoutProduit">
            
            <div class="left-section">
                <div class="ajouterPhoto" id="zoneUpload">
                    <input type="file" id="photoUpload" name="photo" accept="image/*" hidden>
                    
                    <div class="etat-vide" id="etatVide">
                        <div class="icone-wrapper">
                            <img src="../../../public/images/ajouterPhoto.svg" alt="Icône ajout">
                        </div>
                        <p>Cliquer pour ajouter une photo</p>
                    </div>

                    <div class="etat-preview" id="etatPreview" style="display: none;">
                        <img src="" alt="Prévisualisation du produit" id="imagePreview">
                        <div class="overlay-modifier">
                            <span>Cliquer pour modifier la photo</span>
                        </div>
                    </div>
                </div>

                <div class="form-details">
                    <input type="text" name="nom_produit" class="product-name-input" placeholder="Intitulé du produit" required>
                
                    <div class="price-weight-kg">
                        <input type="number" step="0.01" name="prix" placeholder="Prix" required>
                        <input type="number" step="0.01" name="poids" placeholder="Poids (kg)" required>
                        <span class="prix-kg-label">Prix au Kg: -- €</span>
                    </div>

                    <input type="text" name="tags" class="keywords-input" placeholder="Mots clés (séparés par des virgules)">
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
            // --- VARIABLES ---
            const zoneUpload = document.getElementById('zoneUpload');
            const photoInput = document.getElementById('photoUpload');
            const etatVide = document.getElementById('etatVide');
            const etatPreview = document.getElementById('etatPreview');
            const imagePreview = document.getElementById('imagePreview');
            
            const textArea = document.getElementById('product-description');
            const charCountDisplay = document.getElementById('charCount');
            const btnAnnuler = document.getElementById('btnAnnuler');
            const form = document.getElementById('formAjoutProduit');

            // --- 1. GESTION DE L'IMAGE ---
            
            // Déclenche l'input file au clic sur la zone
            zoneUpload.addEventListener('click', function() {
                photoInput.click();
            });

            // Au changement de fichier
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

            // --- 2. COMPTEUR DE CARACTÈRES ---
            textArea.addEventListener('input', function() {
                const currentLength = this.value.length;
                const maxLength = this.getAttribute('maxlength');
                
                charCountDisplay.textContent = `${currentLength}/${maxLength}`;
                
                if (currentLength >= maxLength) {
                    charCountDisplay.style.color = 'red';
                } else {
                    charCountDisplay.style.color = 'gray';
                }
            });

            // --- 3. BOUTON ANNULER ---
            btnAnnuler.addEventListener('click', function() {
                // Reset du formulaire
                form.reset();
                
                // Reset manuel de la prévisualisation
                imagePreview.src = "";
                etatPreview.style.display = 'none';
                etatVide.style.display = 'flex'; // Remettre en flex pour centrer
                
                // Reset compteur
                charCountDisplay.textContent = "0/1000";
                charCountDisplay.style.color = 'gray';
            });
        });
    </script>

    <?php require_once "./partials/footer.php"?>
</body>
</html>
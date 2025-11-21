<?php
    require_once '../../controllers/pdo.php';

    require_once '../../controllers/prix.php';
    require_once '../../controllers/date.php';

    require_once '../../controllers/auth.php';

if (!isset($_GET['id'])) {
    die("Aucun produit sélectionné");
}

$productId = (int)$_GET['id']; 

// Préparer la requête
$stmt = $pdo->prepare("SELECT * FROM _produit WHERE idProduit = :id");
$stmt->execute(['id' => $productId]);
$produit = $stmt->fetch(PDO::FETCH_ASSOC);

$stmt2 = $pdo->prepare("SELECT * FROM _imageDeProduit WHERE idProduit = :id");
$stmt2->execute(['id' => $productId]);
$image = $stmt2->fetch(PDO::FETCH_ASSOC);



$hasImage = ($image && !empty($image['URL']));
$imageUrl = $hasImage 
    ? '../../public' . $image['URL'] 
    : '../../public/images/ajouterPhoto.svg';

if (!$produit) {
    die("Produit introuvable.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../public/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <title>Modifer un produit du catalogue</title>
</head>
<body class="backoffice">
    <?php require_once './partials/header.php' ?>

    <?php
        $currentPage = basename(__FILE__);
        require_once './partials/aside.php';
    ?>
       
    <main class="modifierProduit"> 
        <form class="product-content" id="monForm" action="../../controllers/updateProduit.php?id=<?php echo($productId)?>" method="post" enctype="multipart/form-data">
            <div class="left-section">
                <div class="ajouterPhoto">
                    <input type="file" id="photoUpload" name="url" accept="image/*" style="display: none;">
                    <div class="placeholder-photo">
                    <img src="<?= htmlspecialchars($imageUrl) ?>" id="imagePreview">

                    <p id="placeholderText" style="<?= $hasImage ? 'display:none;' : '' ?>">
                        Cliquer pour ajouter une image
                    </p>

                    <div class="overlay-text" id="overlayText" style="<?= $hasImage ? '' : 'display:none;' ?>">
                        Cliquer pour modifier
                    </div>
                    </div>
                </div>

                <div class="form-details">
                    <input type="text" class="product-name-input" placeholder="Intitulé du produit" name="nom" required
                    value="<?= htmlspecialchars($produit['nom'] ?? '') ?>">

                    <div class="price-weight-kg">
                        <input type="text" placeholder="Prix" name="prix" required
                        value="<?= htmlspecialchars($produit['prix'] ?? '') ?>">
                        
                        <input type="text" placeholder="Poids" name="poids" required 
                        value="<?= htmlspecialchars($produit['poids'] ?? '') ?>">
                    </div>
                    <input type="text" class="motclé" placeholder="Mots clés (séparés par des virgules)" name="mots_cles" required
                    value="<?= htmlspecialchars($produit['mots_cles'] ?? '') ?>">

                </div>
            </div>

            <div class="right-section">
                <div class="ajouterResume resume-box">
                    <label for="resume">Résumé du produit</label><br>   
                    <textarea name="description" id="resume" placeholder="Décrivez votre produit en quelques mots"><?= htmlspecialchars($produit['description'] ?? '') ?></textarea>
                </div>

            <div class="form-actions">
                <button type="button" class="btn-previsualiser">Prévisualiser</button>
                <button type="submit" class="btn-ajouter">Modifier le produit</button>
            </form>
                <form class="supprimerProduit" id="monForm" action="../../controllers/deleteProduit.php?id=<?php echo($productId)?>" method="post" enctype="multipart/form-data">            
                        <button type="submit" class="btn-supprimer">Supprimer</button>
                         <dialog>
                            <h1>Êtes-vous sûr de vouloir vous déconnecter ?</h1>
                            <nav>
                                <button>Annuler</button>
                                <button autofocus>Oui</button>
                            </nav>
                        </dialog>
                    </a>
                   
                </form>
            </div>
        
    </main>

    <script src="/public/script.js">
        
    document.addEventListener('DOMContentLoaded', function() {
        const photoUploadInput = document.getElementById('photoUpload');
        const ajouterPhotoDiv = document.querySelector('.ajouterPhoto'); 
        const imagePreview = document.getElementById('imagePreview');
        const placeholderText = document.getElementById('placeholderText');
        const overlayText = document.getElementById('overlayText');
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
        // Ajouter un margin-bottom pour séparer les boutons du bas du footer
        const formActions = document.querySelector('.form-actions');
        formActions.style.marginBottom = '50px';

        // Vérification au changement du résumé ou des sections
        resumeTextarea.addEventListener('input', checkSections);
    });


    </script>
    <?php require_once "./partials/footer.php"?>
</body>
</html>

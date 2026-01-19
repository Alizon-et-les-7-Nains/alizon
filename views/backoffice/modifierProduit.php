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
    ? $image['URL'] 
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
    <title>Alizon - Modifier produit</title>
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
                    <label> Intitulé du produit</label>
                    <input type="text" class="product-name-input" placeholder="Intitulé du produit" name="nom" required
                    value="<?= htmlspecialchars($produit['nom'] ?? '') ?>">

                    <div class="price-weight-kg">
                        <label>Prix</label>
                        <input type="text" placeholder="Prix" name="prix" required
                        value="<?= htmlspecialchars($produit['prix'] ?? '') ?>">
                        <label>Poids</label>
                        <input type="text" placeholder="Poids" name="poids" required 
                        value="<?= htmlspecialchars($produit['poids'] ?? '') ?>">
                    </div>
                    <label>Mot clés (séparés par des virgules)</label>
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
                <button type="submit" class="btn-ajouter">Modifier le produit</button>
            </form>
                <form class="supprimerProduit" id="formSupprimer" action="../../controllers/deleteProduit.php?id=<?php echo($productId)?>" method="post" enctype="multipart/form-data">            
                        <button type="submit" class="btn-supprimer">Supprimer</button>
                </form>
            </div>
        
            <?php require_once './partials/retourEnHaut.php' ?>
    </main>

    <script src="../../public/script.js"> </script>
    <script>
document.addEventListener('DOMContentLoaded', () => {

    const fileInput = document.getElementById('photoUpload');
    const imagePreview = document.getElementById('imagePreview');
    const placeholderText = document.getElementById('placeholderText');
    const overlayText = document.getElementById('overlayText');

    fileInput.addEventListener('change', () => {
        const file = fileInput.files[0];
        if (!file) return;

        // Sécurité : uniquement images
        if (!file.type.startsWith('image/')) {
            alert("Veuillez sélectionner une image.");
            fileInput.value = "";
            return;
        }

        const reader = new FileReader();
        reader.onload = e => {
            imagePreview.src = e.target.result;
            placeholderText.style.display = 'none';
            overlayText.style.display = 'block';
        };

        reader.readAsDataURL(file);
    });

});
</script>

    <?php require_once "./partials/footer.php"?>
</body>
</html>

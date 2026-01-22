<?php
require_once "../../controllers/pdo.php";
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: connexionClient.php");
    exit();
}

$idClient = $_SESSION['user_id'];

if (!isset($_GET['id'])) {
    die("Erreur : idProduit manquant.");
}


$idProduit = intval($_GET['id']);

// Récupérer l'avis pour le formulaire
$stmt = $pdo->prepare("SELECT * FROM _avis WHERE idClient = ? AND idProduit = ?");
$stmt->execute([$idClient, $idProduit]);
$avis = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$avis) {
    die("Avis introuvable.");
}

function afficherEtoiles($note) {
    // Cette fonction permet d'afficher les étoiles pleines et vides 
    // En fonction de la note de l'avis que l'on a écrit
    $html = "";
    for ($i = 1; $i <= 5; $i++) {
        if ($i <= $note) $html .= "<img class='star' src='../../public/images/etoile.svg'>";
        else $html .= "<img class='star' src='../../public/images/etoileVide.svg'>";
    }
    return $html;

}

// Image par défaut
<<<<<<< HEAD
$imageDefaut = "../../public/images/add_image.jpg";
=======
$imageDefaut = "../../public/images/addImage.svg";
>>>>>>> bc626ffe7cd36919a38ee16a2a62c16f09fe9f1f

// Récupération de l'image liée à l'avis
$stmtImg = $pdo->prepare("
    SELECT url 
    FROM _imageAvis 
    WHERE idClient = ? AND idProduit = ?");
$stmtImg->execute([$idClient, $idProduit]);
$imageAvis = $stmtImg->fetch(PDO::FETCH_ASSOC);
// Déterminer si une image existe
$hasImage = ($imageAvis && !empty($imageAvis['url']));
// URL finale à afficher
$imageUrl = $hasImage ? $imageAvis['url'] : $imageDefaut;

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <link rel="icon" href="../../public/images/logoBackoffice.svg">
    <link rel="stylesheet" href="../../public/style.css">
        <title>Modifier un avis</title>
    </head>
    <body class="modifierAvis">
        <?php include './partials/headerConnecte.php'; ?>
        <main>

    <h2>Modifier mon avis</h2>
    
    <form action="../../controllers/modifierAvis_action.php" method="POST" enctype="multipart/form-data">
    <input type="hidden" name="idProduit" value="<?= $idProduit ?>">
    
        <input type="file" id="photoUpload" name="url" style="display:none">


        <label for="photoUpload" class="placeholder-photo">

            <img id="imagePreview"
                src="<?= htmlspecialchars($imageUrl) ?>"
                alt="Image de l'avis">

            <p id="placeholderText" style="<?= $hasImage ? 'display:none;' : '' ?>">
                Cliquer pour ajouter une image
            </p>

            <div class="overlay-text" id="overlayText" style="<?= $hasImage ? '' : 'display:none;' ?>">
                Cliquer pour modifier
            </div>

        </label>


        <label>Titre :</label><br>
        <input type="text" name="titreAvis" value="<?php echo htmlspecialchars($avis['titreAvis']); ?>"><br><br>

        <article class="etoiles">
            <?= afficherEtoiles(round($avis['note'])); ?>
        </article>
        <input type="hidden" name="note" id="note" value="<?= htmlspecialchars($avis['note'] ?? '') ?>">
        
        <label>Contenu :</label><br>
        <textarea name="contenuAvis" required><?php echo htmlspecialchars($avis['contenuAvis']); ?></textarea><br><br>

        <button type="submit" id=publishButton>Modifier</button>
    </form>
</main>
<?php include './partials/footerDeconnecte.php'; ?>
</body>
</html>

<script>
document.addEventListener('DOMContentLoaded', () => {

    /* ==========================
       GESTION DES ÉTOILES
    ========================== */
    const noteInput = document.getElementById('note');
    const stars = document.querySelectorAll('.etoiles .star');
    const emptyStar = "/public/images/etoileVide.svg";
    const fullStar  = "/public/images/etoile.svg";

    if (stars.length > 0 && noteInput) {
        stars.forEach((star, index) => {
            star.addEventListener('click', () => {
                const rating = index + 1;

                stars.forEach((s, i) => {
                    s.src = i < rating ? fullStar : emptyStar;
                });

                noteInput.value = rating;
            });
        });
    }

    /* ==========================
       PREVIEW IMAGE
    ========================== */
    const fileInput = document.getElementById('photoUpload');
    const imagePreview = document.getElementById('imagePreview');
    const placeholderText = document.getElementById('placeholderText');
    const overlayText = document.getElementById('overlayText');

    if (!fileInput || !imagePreview) {
        console.error("Éléments image manquants");
        return;
    }

    fileInput.addEventListener('change', function () {
        if (!this.files || this.files.length === 0) return;

        const file = this.files[0];

        if (!file.type.startsWith('image/')) {
            alert("Veuillez sélectionner une image valide.");
            this.value = "";
            return;
        }

        const reader = new FileReader();
        reader.onload = e => {
            imagePreview.src = e.target.result;
            imagePreview.style.display = "block";
            if (placeholderText) placeholderText.style.display = 'none';
            if (overlayText) overlayText.style.display = 'flex';
        };
        reader.readAsDataURL(file);
    });

});
</script>

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
    
    <!-- Formulaire de modification de l'avis -->
    <form action="../../controllers/modifierAvis_action.php" method="POST">
        
        <input type="hidden" name="idProduit" value="<?php echo $idProduit; ?>">

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
    const noteInput = document.getElementById('note');
    const stars = document.querySelectorAll('.etoiles .star');
    const emptyStar = "/public/images/etoileVide.svg";
    const fullStar = "/public/images/etoile.svg";

    // Pour toutes les étoiles, si on clique desus alors on change celle-ci 
    // Ainsi que les précédentes en étoiles pleines. Cela permet aussi de stocker
    // L'information du nombre d'étoiles que l'on souhaite mettre.
    stars.forEach((star, index) => {
        star.addEventListener('click', () => {
            const rating = index + 1;

            stars.forEach((s, i) => s.src = i < rating ? fullStar : emptyStar);

            noteInput.value = rating;
        });
    });
});
</script>

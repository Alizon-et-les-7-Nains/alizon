<?php
require_once "../../controllers/pdo.php";
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../views/frontoffice/connexionClient.php');
    exit;
}

/* Get max price once for slider */
$stmt = $pdo->query("SELECT MAX(prix) AS maxPrice FROM _produit");
$maxPrice = (int) $stmt->fetch()['maxPrice'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Catalogue</title>
    <link rel="stylesheet" href="../../public/style.css">
</head>
<body>

<?php include '../../views/frontoffice/partials/headerConnecte.php'; ?>

<main class="pageCatalogue">

    <!-- FILTERS -->
    <aside class="filter-sort">
        <h3>Filtres</h3>

        <label>Prix</label>
        <div class="slider-container">
            <div class="values">
                <span id="minValue">0€</span>
                <span id="maxValue"><?= $maxPrice ?>€</span>
            </div>

            <div class="slider-wrapper">
                <div class="slider-track"></div>
                <div class="slider-range" id="range"></div>
                <input type="range" id="sliderMin" min="0" max="<?= $maxPrice ?>" value="0">
                <input type="range" id="sliderMax" min="0" max="<?= $maxPrice ?>" value="<?= $maxPrice ?>">
            </div>
        </div>
    </aside>

    <!-- PRODUCTS -->
    <div class="products-section">
        <p id="resultat"></p>
        <section class="listeArticle"></section>
    </div>

</main>

<section class="confirmationAjout">
    <h4>Produit ajouté au panier !</h4>
</section>

<script>
const sliderMin = document.getElementById("sliderMin");
const sliderMax = document.getElementById("sliderMax");
const minValue = document.getElementById("minValue");
const maxValue = document.getElementById("maxValue");
const range = document.getElementById("range");
const list = document.querySelector(".listeArticle");
const result = document.getElementById("resultat");

let debounce;

function updateSliderUI(min, max) {
    minValue.textContent = min + "€";
    maxValue.textContent = max + "€";

    const p1 = (min / sliderMin.max) * 100;
    const p2 = (max / sliderMax.max) * 100;
    range.style.left = p1 + "%";
    range.style.width = (p2 - p1) + "%";
}

function fetchProducts(min, max) {
    fetch(`../../controllers/catalogue_products.php?minPrice=${min}&maxPrice=${max}`)
        .then(r => r.text())
        .then(html => {
            list.innerHTML = html;
            result.textContent =
                list.children.length + " résultat" +
                (list.children.length > 1 ? "s" : "");
        });
}

function onSliderChange() {
    let min = parseInt(sliderMin.value);
    let max = parseInt(sliderMax.value);

    if (min > max) [min, max] = [max, min];

    updateSliderUI(min, max);

    clearTimeout(debounce);
    debounce = setTimeout(() => {
        fetchProducts(min, max);
    }, 150);
}

sliderMin.addEventListener("input", onSliderChange);
sliderMax.addEventListener("input", onSliderChange);

/* Initial load */
onSliderChange();
</script>

<script src="../scripts/frontoffice/paiement-ajax.js"></script>
</body>
</html>

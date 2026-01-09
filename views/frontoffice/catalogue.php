<?php
require_once "../../controllers/pdo.php";
require_once "../../controllers/prix.php";
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../views/frontoffice/connexionClient.php');
    exit;
}

$idClient = $_SESSION['user_id'];

$sortBy = $_GET['sort'] ?? '';
$minNote = $_GET['minNote'] ?? '';
$category = $_GET['category'] ?? '';
$zone = $_GET['zone'] ?? '';
$vendeur = $_GET['vendeur'] ?? '';
$searchQuery = $_GET['search'] ?? '';

$sql = "SELECT p.*, r.tauxRemise, r.debutRemise, r.finRemise 
        FROM _produit p 
        LEFT JOIN _remise r ON p.idProduit = r.idProduit 
        AND CURDATE() BETWEEN r.debutRemise AND r.finRemise
        WHERE 1=1";

$params = [];

if (!empty($searchQuery)) {
    $sql .= " AND p.nom LIKE :search";
    $params[':search'] = '%' . $searchQuery . '%';
}

if (!empty($minNote)) {
    $sql .= " AND p.note >= :minNote";
    $params[':minNote'] = $minNote;
}

if (!empty($category)) {
    $sql .= " AND p.typeProd = :category";
    $params[':category'] = $category;
}

// Tri
if ($sortBy === 'prix_asc') {
    $sql .= " ORDER BY p.prix ASC";
} elseif ($sortBy === 'prix_desc') {
    $sql .= " ORDER BY p.prix DESC";
} elseif ($sortBy === 'note') {
    $sql .= " ORDER BY p.note DESC";
} else {
    $sql .= " ORDER BY p.idProduit DESC";
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

$nbResultats = count($products);
$maxPrice = !empty($products) ? max(array_column($products, 'prix')) : 0;
echo "console.log('Max price: " . $maxPrice . "');";
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catalogue</title>
    <link rel="icon" href="../../public/images/logoBackoffice.svg">
    <link rel="stylesheet" href="../../public/style.css">
    <style>
    </style>
</head>
<body>
<?php include '../../views/frontoffice/partials/headerConnecte.php' ?>
<main class="pageCatalogue">
    <aside class="filter-sort">
        <h3>Filtres</h3>
        <form method="GET" action="">
            <label for="tri">Trier par :</label>
            <label for="prix">Filtrer par prix :</label>
            <div class="slider-container">
                <div class="values">
                    <span class="value" id="minValue">0</span>
                    <span class="value" id="maxValue">100</span>
                </div>
                <div class="slider-wrapper">
                    <div class="slider-track"></div>
                    <div class="slider-range" id="range"></div>
                    <input type="range" id="sliderMin" min="0" max="100" value="0">
                    <input type="range" id="sliderMax" min="0" max="100" value="<?php echo $maxPrice; ?>">
                </div>
            </div>

            <label for="minNote" id="minNoteLabel">Note minimale :</label>
            <label for="categorie">Catégorie :</label>
            <label for="zone">Zone géographique :</label>
            <label for="vendeur">Vendeur :</label>
        </form>
    </aside>
    
    <div class="products-section">
        <p id="resultat"><?= $nbResultats ?> résultat<?= $nbResultats > 1 ? 's' : '' ?><?= !empty($searchQuery) ? ' pour "' . htmlspecialchars($searchQuery) . '"' : '' ?></p>
        <section class="listeArticle">
            <?php 
            if (count($products) > 0) {
                foreach ($products as $value) {
                    $idProduit = $value['idProduit'];
                    $stockProduit = $value['stock'];
                    $prixOriginal = $value['prix'];
                    $tauxRemise = $value['tauxRemise'] ?? 0;
                    $enRemise = !empty($value['tauxRemise']) && $value['tauxRemise'] > 0;
                    $prixRemise = $enRemise ? $prixOriginal * (1 - $tauxRemise/100) : $prixOriginal;
                    
                    $stmtImg = $pdo->prepare("SELECT URL FROM _imageDeProduit WHERE idProduit = :idProduit");
                    $stmtImg->execute([':idProduit' => $idProduit]);
                    $imageResult = $stmtImg->fetch(PDO::FETCH_ASSOC);
                    $image = !empty($imageResult) ? $imageResult['URL'] : '../../public/images/defaultImageProduit.png';
                    ?>
            <article>
                <img src="<?php echo htmlspecialchars($image); ?>" class="imgProduit"
                    onclick="window.location.href='produit.php?id=<?php echo $idProduit; ?>'"
                    alt="Image du produit">
                <h2 class="nomProduit"
                    onclick="window.location.href='produit.php?id=<?php echo $idProduit; ?>'">
                    <?php echo htmlspecialchars($value['nom']); ?></h2>
                <div class="notation">
                    <?php if(number_format($value['note'], 1) == 0) { ?>
                        <span>Pas de note</span>
                    <?php } else { ?>
                        <span><?php echo number_format($value['note'], 1); ?></span>
                        <?php for ($i=0; $i < number_format($value['note'], 0); $i++) { ?>
                            <img src="../../public/images/etoile.svg" alt="Note" class="etoile">
                        <?php } ?>
                    <?php } ?>
                </div>
                <div class="infoProd">
                    <div class="prix">
                        <?php if ($enRemise): ?>
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <h2><?php echo formatPrice($prixRemise); ?></h2>
                                <h3 style="text-decoration: line-through; color: #999;">
                                    <?php echo formatPrice($prixOriginal); ?>
                                </h3>
                            </div>
                        <?php else: ?>
                            <h2><?php echo formatPrice($prixOriginal); ?></h2>
                        <?php endif; ?>
                        <?php 
                            $prixAffichage = $enRemise ? $prixRemise : $prixOriginal;
                            $poids = $value['poids'];
                            $prixAuKg = $poids > 0 ? $prixAffichage/$poids : 0;
                            $prixAuKg = round($prixAuKg,2);
                        ?>
                        <?php if ($poids > 0): ?>
                            <h4><?php echo htmlspecialchars($prixAuKg); ?>€ / kg</h4>
                        <?php endif; ?>
                    </div>
                    <div>
                        <?php if(number_format($value['stock'], 1) == 0) { ?>
                            <b style="color: red; margin-right: 5px;">Aucun stock</b>
                        <?php } else { ?>
                            <button class="plus" data-id="<?= htmlspecialchars($value['idProduit'] ?? '') ?>">
                                <img src="../../public/images/btnAjoutPanier.svg" alt="Bouton ajout panier">
                            </button>
                        <?php } ?>
                    </div>
                </div>
            </article>
            <?php } 
            } else { ?>
                <h1>Aucun produit disponible</h1>
            <?php } ?>
        </section>
    </div>
</main>

<section class="confirmationAjout">
    <h4>Produit ajouté au panier !</h4>
</section>

<script>
    const popupConfirmation = document.querySelector(".confirmationAjout");
    const boutonsAjout = document.querySelectorAll(".plus");

    boutonsAjout.forEach(btn => {
        btn.addEventListener("click", function(e) {
            e.stopPropagation();
            popupConfirmation.style.display = "block";
            setTimeout(() => {
                popupConfirmation.style.display = "none";
            }, 3000);
        });
    });

    const sliderMin = document.getElementById('sliderMin');
    const sliderMax = document.getElementById('sliderMax');
    const minValue = document.getElementById('minValue');
    const maxValue = document.getElementById('maxValue');
    const range = document.getElementById('range');

    function updateSlider() {
        let min = parseInt(sliderMin.value);
        let max = parseInt(sliderMax.value);

        if (min > max) {
            [min, max] = [max, min];
            sliderMin.value = min;
            sliderMax.value = max;
        }

        minValue.textContent = min+'€';
        maxValue.textContent = max+'€';

        const percent1 = (min / sliderMin.max) * 100;
        const percent2 = (max / sliderMax.max) * 100;

        range.style.left = percent1 + '%';
        range.style.width = (percent2 - percent1) + '%';
    }

    sliderMin.addEventListener('input', updateSlider);
    sliderMax.addEventListener('input', updateSlider);

    updateSlider();
</script>

<script src="../scripts/frontoffice/paiement-ajax.js"></script>
</body>
</html>
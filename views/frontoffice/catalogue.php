<?php
require_once "../../controllers/pdo.php";
require_once "../../controllers/prix.php";
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../views/frontoffice/connexionClient.php');
    exit;
}

$produitsParPage = 15;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $produitsParPage;

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

$countSql = "SELECT COUNT(*) FROM _produit p 
             LEFT JOIN _remise r ON p.idProduit = r.idProduit 
             AND CURDATE() BETWEEN r.debutRemise AND r.finRemise
             WHERE 1=1";

$countStmt = $pdo->query($countSql);
$totalProduits = $countStmt->fetchColumn();

$nbPages = ceil($totalProduits / $produitsParPage);

$sql .= " LIMIT :limit OFFSET :offset";
$stmt = $pdo->prepare($sql);

$stmt->bindValue(':limit', (int)$produitsParPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

$maxPrice = !empty($products) ? max(array_column($products, 'prix')) : 0;
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
                    <span class="value" id="maxValue"><?php echo $maxPrice; ?></span>
                </div>
                <div class="slider-wrapper">
                    <div class="slider-track"></div>
                    <div class="slider-range" id="range"></div>
                    <input type="range" id="sliderMin" min="0" max="<?php echo $maxPrice; ?>" value="0">
                    <input type="range" id="sliderMax" min="0" max="<?php echo $maxPrice; ?>" value="<?php echo $maxPrice; ?>">
                </div>
            </div>

            <label for="minNote" id="minNoteLabel">Note minimale :</label>
            <label for="categorie">Catégorie :</label>
            <label for="zone">Zone géographique :</label>
            <label for="vendeur">Vendeur :</label>
        </form>
    </aside>
    
    <div class="products-section">
        <p id="resultat"><?= $totalProduits ?> résultat<?= $totalProduits > 1 ? 's' : '' ?><?= !empty($searchQuery) ? ' pour "' . htmlspecialchars($searchQuery) . '"' : ' dans le catalogue' ?></p>
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
                    $prixAffichage = $enRemise ? $prixRemise : $prixOriginal;
                    ?>
            <article data-price="<?= $prixAffichage ?>">
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
            <div class="pagination">
                <?php if ($nbPages > 1): ?>
                    <?php if ($page > 1): ?>
                        <a href="?page=<?= $page-1 ?>">« Précédent</a>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $nbPages; $i++): ?>
                        <a href="?page=<?= $i ?>" class="<?= $i == $page ? 'active' : '' ?>"><?= $i ?></a>
                    <?php endfor; ?>

                    <?php if ($page < $nbPages): ?>
                        <a href="?page=<?= $page+1 ?>">Suivant »</a>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
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
<script>
    const articles = document.querySelectorAll(".listeArticle article");
    const resultat = document.getElementById("resultat");

    function filtrerProduitsParPrix() {
        const min = parseInt(sliderMin.value);
        const max = parseInt(sliderMax.value);
        let visibles = 0;

        articles.forEach(article => {
            const prix = parseFloat(article.dataset.price);

            if (prix >= min && prix <= max) {
                article.style.display = "";
                visibles++;
            } else {
                article.style.display = "none";
            }
        });

        resultat.textContent = visibles + " résultat" + (visibles > 1 ? "s" : "");
    }

    sliderMin.addEventListener("input", () => {
        updateSlider();
        filtrerProduitsParPrix();
    });

    sliderMax.addEventListener("input", () => {
        updateSlider();
        filtrerProduitsParPrix();
    });
</script>

</body>
</html>
<?php
include "../../controllers/pdo.php";
include "../../controllers/prix.php";
session_start();

$produitsParPage = 15;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;

// Reglage du decalage pour la pagination
$offset = ($page - 1) * $produitsParPage;

$idClient = $_SESSION['user_id'];

$searchQuery = isset($_GET['search']) ? trim($_GET['search']) : "";

// Récupérer les produits avec pagination
$sql = "SELECT p.*, r.tauxRemise, r.debutRemise, r.finRemise 
        FROM _produit p 
        LEFT JOIN _remise r ON p.idProduit = r.idProduit 
        AND CURDATE() BETWEEN r.debutRemise AND r.finRemise";

// Compter tous les produits
$countSql = "SELECT COUNT(*) FROM _produit p 
             LEFT JOIN _remise r ON p.idProduit = r.idProduit 
             AND CURDATE() BETWEEN r.debutRemise AND r.finRemise";

// Récuperer la totalité des catégories

$catSql = "SELECT DISTINCT typeProd FROM _produit p;";
$stmt = $pdo->prepare($catSql);
$stmt->execute();
$listeCategories = $stmt->fetchAll(PDO::FETCH_ASSOC);

$countStmt = $pdo->query($countSql);
$totalProduits = $countStmt->fetchColumn(); // fetchColumn récupère la première colonne du premier résultat

$nbPages = ceil($totalProduits / $produitsParPage);


$sql .= " LIMIT :limit OFFSET :offset"; // LIMIT : nommbre de produits par page (15), OFFSET : decalage sur l'ensemble des résultats
$stmt = $pdo->prepare($sql);

// Liaison des paramètres pour la pagination
$stmt->bindValue(':limit', (int)$produitsParPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupérer le prix maximum pour le slider
$maxPriceStmt = $pdo->query("SELECT MAX(prix) as maxPrix FROM _produit");
$maxPriceRow = $maxPriceStmt->fetch(PDO::FETCH_ASSOC);
$maxPrice = $maxPriceRow['maxPrix'] ?? 100;

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
<?php if (isset($_SESSION['user_id'])) {
    include '../../views/frontoffice/partials/headerConnecte.php';
} else { 
    include '../../views/frontoffice/partials/headerDeconnecte.php';
} ?>
<main class="pageCatalogue">
    <aside class="filter-sort">
        <h3>Filtres</h3>
        <form method="GET" action="">
            <label for="tri">Trier par :</label>
            <div class="triNote">
                <input type="radio" id="triNoteCroissant" name="tri" value="noteAsc">
                <label for="triNoteCroissant">Note croissante</label>
                <input type="radio" id="triNoteDecroissant" name="tri" value="noteDesc">
                <label for="triNoteDecroissant">Note décroissante</label>
            </div>
            <div class="triPrix">
                <input type="radio" id="triPrixCroissant" name="tri" value="prixAsc">
                <label for="triPrixCroissant">Prix croissant</label>
                <input type="radio" id="triPrixDecroissant" name="tri" value="prixDesc">
                <label for="triPrixDecroissant">Prix décroissant</label>
            </div>
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

            <label for="minNote" id="minNoteLabel">Trier par note :</label>
            <div>
                <img src="../../public/images/etoileVide.svg" data-index="1" class="star" alt="1 étoile">
                <img src="../../public/images/etoileVide.svg" data-index="2" class="star" alt="2 étoiles">
                <img src="../../public/images/etoileVide.svg" data-index="3" class="star" alt="3 étoiles">
                <img src="../../public/images/etoileVide.svg" data-index="4" class="star" alt="4 étoiles">
                <img src="../../public/images/etoileVide.svg" data-index="5" class="star" alt="5 étoiles">
                <input type="hidden" name="note" id="note" value="0"> 
            </div>

<?php echo var_dump($listeCategories) ?>

            <label for="categorie">Catégorie :</label>
            <select name="categorie" id="categorieSelect" class="filter-select">
                <option value="" class="opt-highlight">Toutes les catégories</option>
                <?php foreach ($listeCategories as $categorie) { ?>
                    <option value="<?= $categorie['typeProd'] ?>" class="choix"><?= $categorie['typeProd'] ?></option>
                <?php } ?>
            </select>

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
                    if ($prixAffichage = $enRemise) {
                        $prixAffichage = $prixRemise;
                    } else {
                        $prixAffichage = $prixOriginal;
                    }
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
    </div>
</main>

<section class="confirmationAjout">
    <h4>Produit ajouté au panier !</h4>
</section>

<script>
// Filtres prix
const sliderMin = document.getElementById('sliderMin');
const sliderMax = document.getElementById('sliderMax');
const minValue = document.getElementById('minValue');
const maxValue = document.getElementById('maxValue');
const range = document.getElementById('range');

// Tri notes
const triNoteCroissant = document.getElementById('triNoteCroissant');
const triNoteDecroissant = document.getElementById('triNoteDecroissant');
let sortOrder = '';

// Variables globales
const searchQuery = <?php $searchQuery ?>;
const listeArticle = document.querySelector('.listeArticle');
const resultat = document.getElementById('resultat');
const paginationDiv = document.querySelector('.pagination');
const popupConfirmation = document.querySelector(".confirmationAjout");
const noteInput = document.getElementById('note');
let currentPage = <?= $page ?>;
let isFiltering = false;

document.addEventListener('DOMContentLoaded', function() {
    const stars = document.querySelectorAll('.star');
    const emptyStar = "../../public/images/etoileVide.svg";
    const fullStar = "../../public/images/etoile.svg";

    // Gestion des étoiles
    stars.forEach((star, index) => {
        star.addEventListener('click', () => {
            const rating = index + 1;
            stars.forEach((s, i) => {
                s.src = i < rating ? fullStar : emptyStar;
            });
            noteInput.value = rating;
            loadProduits(1);
        });
    });
});

const categorieSelect = document.getElementById('categorieSelect');
categorieSelect.addEventListener('change', () => {
    loadProduits(1);
});

function updateSlider() {
    let min = parseInt(sliderMin.value);
    let max = parseInt(sliderMax.value);
    if (min > max) [min, max] = [max, min];
    sliderMin.value = min;
    sliderMax.value = max;
    minValue.textContent = min+'€';
    maxValue.textContent = max+'€';
    const percent1 = (min / sliderMin.max) * 100;
    const percent2 = (max / sliderMax.max) * 100;
    range.style.left = percent1 + '%';
    range.style.width = (percent2 - percent1) + '%';
}

function reattacherAjouterPanier() {
    document.querySelectorAll('.plus').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            popupConfirmation.style.display = "block";
            setTimeout(() => {
                popupConfirmation.style.display = "none";
            }, 3000);
        });
    });
}

function pagination() {
    document.querySelectorAll('.pageLink').forEach(link => {
        link.addEventListener('click', e => {
            e.preventDefault();
            const newPage = parseInt(link.dataset.page);
            loadProduits(newPage);
        });
    });
}

function loadProduits(page = 1) {
    const min = parseInt(sliderMin.value);
    const max = parseInt(sliderMax.value);
    const notemin = parseInt(noteInput.value);
    const catValue = categorieSelect.value; 

    fetch(`../../controllers/filtrerProduits.php?minPrice=${min}&maxPrice=${max}&page=${page}&sortOrder=${sortOrder}&minNote=${notemin}&categorie=${catValue}`)
        .then(res => {
            // Vérifie si la réponse HTTP est correcte (status 200-299)
            if (!res.ok) {
                throw new Error(`Erreur HTTP: ${res.status}`);
            }
            return res.json(); // Conversion en JSON
        })
        .then(data => {
            listeArticle.innerHTML = data.html; // Recuperation des nouvelles données et mise à jour des produits
            currentPage = page; // Mise à jour de la page cournante
            resultat.textContent = `${data.totalProduits} produit${data.totalProduits > 1 ? 's' : ''}`; // Mise à jour du nombre de résultats
            
            // Mise à jour de la pagination
            let pagHTML = '';
            if (data.nbPages > 1) {
                if (page > 1) pagHTML += `<a href="#" class="pageLink" data-page="${page-1}">« Précédent</a>`;
                for (let i=1; i<=data.nbPages; i++){
                    pagHTML += `<a href="#" class="pageLink ${i===page?'active':''}" data-page="${i}">${i}</a>`;
                }
                if (page < data.nbPages) pagHTML += `<a href="#" class="pageLink" data-page="${page+1}">Suivant »</a>`;
            }
            paginationDiv.innerHTML = pagHTML;  // Recuperation des nouvelles données et mise à jour des produits

            pagination();
            reattacherAjouterPanier();
            
            isFiltering = true;
        })
        .catch(error => {
            console.error('Erreur lors du chargement des produits:', error);
            listeArticle.innerHTML = '<h1>Erreur lors du chargement des produits</h1>';
        });
}

// Events listeners sur les sliders
sliderMin.addEventListener('input', () => { 
    updateSlider(); 
    loadProduits(1); 
    console.log(searchbar.value)
});

sliderMax.addEventListener('input', () => { 
    updateSlider(); 
    loadProduits(1); 
});

triNoteCroissant.addEventListener('change', () => {
    if (triNoteCroissant.checked) {
        sortOrder = 'noteAsc';
        loadProduits(1);
    }
});

triNoteDecroissant.addEventListener('change', () => {
    if (triNoteDecroissant.checked) {
        sortOrder = 'noteDesc';
        loadProduits(1);
    }
});

triPrixCroissant.addEventListener('change', () => {
    if (triPrixCroissant.checked) {
        sortOrder = 'prixAsc';
        loadProduits(1);
    }
});

triPrixDecroissant.addEventListener('change', () => {
    if (triPrixDecroissant.checked) {
        sortOrder = 'prixDesc';
        loadProduits(1);
    }
});

if(searchQuery = ""){
    searchbar.placeholder = 'Recherche';
}
else{
    searchbar.value = searchQuery;
}


updateSlider();

reattacherAjouterPanier();

document.querySelector('form').addEventListener('submit', e => e.preventDefault());

</script>

</body>
</html>
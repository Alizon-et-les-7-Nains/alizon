<?php
require_once "../../controllers/pdo.php";
require_once "../../controllers/prix.php";
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../views/frontoffice/connexionClient.php');
    exit;
}

$idClient = $_SESSION['user_id'];

// Récupération des filtres
$sortBy = $_GET['sort'] ?? '';
$minNote = $_GET['minNote'] ?? '';
$category = $_GET['category'] ?? '';
$zone = $_GET['zone'] ?? '';
$vendeur = $_GET['vendeur'] ?? '';
$searchQuery = $_GET['search'] ?? '';

// Construction de la requête SQL
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
        .pageCatalogue {
            display: flex;
            gap: 20px;
            padding: 20px;
        }

        .filter-sort {
            width: 22%;
            background-color: #f5f5f5;
            padding: 20px;
            border-radius: 10px;
            height: fit-content;
            position: sticky;
            top: 20px;
        }

        .filter-sort h3 {
            color: #273469;
            font-family: "Lora", serif;
            font-size: 24px;
            margin-bottom: 20px;
        }

        .filter-sort form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .filter-sort label {
            color: #273469;
            font-family: "Open-sans", serif;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .filter-sort select,
        .filter-sort input {
            padding: 8px;
            border: 2px solid #273469;
            border-radius: 5px;
            font-family: "Open-sans", serif;
        }

        .filter-sort button {
            background-color: #273469;
            color: white;
            padding: 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-family: "Open-sans", serif;
            font-weight: 600;
            margin-top: 10px;
        }

        .filter-sort button:hover {
            background-color: #1a2347;
        }

        .products-section {
            width: 78%;
        }

        #resultat {
            color: #273469;
            font-family: "Lora", serif;
            font-size: 28px;
            margin-bottom: 20px;
            font-weight: 600;
        }

        .listeArticle {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(276px, 1fr));
            gap: 20px;
            width: 100%;
        }

        .listeArticle article {
            border: 2px solid #273469;
            border-radius: 20px;
            padding: 10px;
            min-width: 276px;
            max-width: 276px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            margin: 0;
        }

        .listeArticle article:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(39, 52, 105, 0.3);
            cursor: pointer;
        }

        .listeArticle article h2 {
            color: #273469;
            font-size: 20px;
            font-family: "Open-sans", serif;
            font-weight: 500;
            margin-top: 10px;
            margin-bottom: 0;
        }

        .listeArticle article .imgProduit {
            max-width: 267px;
            max-height: 168px;
            object-fit: contain;
            border-radius: 20px;
        }

        .notation {
            display: flex;
            align-items: center;
            gap: 5px;
            margin: 5px 0;
        }

        .notation .etoile {
            width: 20px;
            height: 20px;
        }

        .notation span {
            color: #273469;
            font-family: "Lora", serif;
            font-size: 18px;
            font-weight: bold;
        }

        .infoProd {
            display: flex;
            justify-content: space-between;
            flex-wrap: nowrap;
            flex-direction: row;
            align-items: center;
        }

        .prix h2 {
            color: #273469;
            font-size: 32px;
            font-family: "Lora", serif;
            font-weight: bold;
            margin: 0;
        }

        .prix h3 {
            color: #000000;
            font-size: 12px;
            font-family: "Lora", serif;
            font-weight: bold;
            font-style: italic;
            margin: 0;
        }

        .prix h4 {
            color: #273469;
            font-size: 14px;
            font-family: "Open-sans", serif;
            margin: 0;
        }

        .infoProd button {
            background: none;
            border: none;
            padding: 0;
            cursor: pointer;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        @media (max-width: 1200px) {
            .pageCatalogue {
                flex-direction: column;
            }

            .filter-sort,
            .products-section {
                width: 100%;
            }

            .filter-sort {
                position: static;
            }
        }
    </style>
</head>
<body>
<?php // include '../../views/frontoffice/partials/headerConnecte.php' ?>
<main class="pageCatalogue">
    <aside class="filter-sort">
        <h3>Filtres</h3>
        <form method="GET" action="">
            <label for="sort">Trier par :</label>
            <select name="sort" id="sort">
                <option value="">Par défaut</option>
                <option value="prix_asc" <?= $sortBy === 'prix_asc' ? 'selected' : '' ?>>Prix croissant</option>
                <option value="prix_desc" <?= $sortBy === 'prix_desc' ? 'selected' : '' ?>>Prix décroissant</option>
                <option value="note" <?= $sortBy === 'note' ? 'selected' : '' ?>>Meilleure note</option>
            </select>

            <label for="minNote">Note minimale :</label>
            <select name="minNote" id="minNote">
                <option value="">Toutes les notes</option>
                <option value="4" <?= $minNote === '4' ? 'selected' : '' ?>>4+ étoiles</option>
                <option value="3" <?= $minNote === '3' ? 'selected' : '' ?>>3+ étoiles</option>
                <option value="2" <?= $minNote === '2' ? 'selected' : '' ?>>2+ étoiles</option>
            </select>

            <label for="category">Catégorie :</label>
            <select name="category" id="category">
                <option value="">Toutes les catégories</option>
                <option value="charcuterie" <?= $category === 'charcuterie' ? 'selected' : '' ?>>Charcuterie</option>
                <option value="alcools" <?= $category === 'alcools' ? 'selected' : '' ?>>Alcools</option>
                <option value="fruits" <?= $category === 'fruits' ? 'selected' : '' ?>>Fruits & Légumes</option>
            </select>

            <button type="submit">Appliquer les filtres</button>
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
</script>

<script src="../scripts/frontoffice/paiement-ajax.js"></script>
</body>
</html>
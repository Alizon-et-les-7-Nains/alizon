<?php
require_once "../controllers/pdo.php";
require_once "../controllers/prix.php";

$minPrice = (int) ($_GET['minPrice'] ?? 0);
$maxPrice = (int) ($_GET['maxPrice'] ?? PHP_INT_MAX);

$sql = "
    SELECT p.*, r.tauxRemise
    FROM _produit p
    LEFT JOIN _remise r ON p.idProduit = r.idProduit
    AND CURDATE() BETWEEN r.debutRemise AND r.finRemise
    WHERE p.prix BETWEEN :min AND :max
    ORDER BY p.idProduit DESC
";

$stmt = $pdo->prepare($sql);
$stmt->execute([
    ':min' => $minPrice,
    ':max' => $maxPrice
]);

$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$products) {
    echo "<h1>Aucun produit disponible</h1>";
    exit;
}

foreach ($products as $p) {

    $prix = $p['prix'];
    $remise = $p['tauxRemise'] ?? 0;
    $final = $remise ? $prix * (1 - $remise / 100) : $prix;

    $imgStmt = $pdo->prepare(
        "SELECT URL FROM _imageDeProduit WHERE idProduit = :id LIMIT 1"
    );
    $imgStmt->execute([':id' => $p['idProduit']]);
    $img = $imgStmt->fetchColumn() ?: '../public/images/defaultImageProduit.png';
    ?>
    <article>
        <img src="<?= htmlspecialchars($img) ?>"
             onclick="location.href='produit.php?id=<?= $p['idProduit'] ?>'">

        <h2><?= htmlspecialchars($p['nom']) ?></h2>

        <div class="prix">
            <h2><?= formatPrice($final) ?></h2>
        </div>

        <?php if ($p['stock'] > 0): ?>
            <button class="plus" data-id="<?= $p['idProduit'] ?>">
                <img src="../public/images/btnAjoutPanier.svg">
            </button>
        <?php else: ?>
            <b style="color:red">Aucun stock</b>
        <?php endif; ?>
    </article>
<?php } ?>

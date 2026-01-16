<?php
require_once "pdo.php";
require_once "prix.php";
session_start();

$produitsParPage = 15;
$page = max(1, (int)($_GET['page'] ?? 1));
$offset = ($page - 1) * $produitsParPage;

$minPrice = (float)($_GET['minPrice'] ?? 0);
$maxPrice = (float)($_GET['maxPrice'] ?? 999999);
$noteMin = (float)($_GET['minNote'] ?? 0);
$categorie = $_GET['categorie'] ?? '';

$params = [
    ':noteMin'  => $noteMin,
    ':minPrice'=> $minPrice,
    ':maxPrice'=> $maxPrice
];

$sqlWhere = "
WHERE p.note >= :noteMin
AND (p.prix * (1 - COALESCE(r.tauxRemise,0)/100)) 
BETWEEN :minPrice AND :maxPrice
";

if ($categorie !== '') {
    $sqlWhere .= " AND p.idCategorie = :categorie";
    $params[':categorie'] = $categorie;
}

$baseSql = "
FROM _produit p
JOIN _categorie c ON p.idCategorie = c.idCategorie
LEFT JOIN _remise r 
    ON p.idProduit = r.idProduit
    AND CURDATE() BETWEEN r.debutRemise AND r.finRemise
" . $sqlWhere;

// COUNT
$countStmt = $pdo->prepare("SELECT COUNT(*) " . $baseSql);
$countStmt->execute($params);
$totalProduits = $countStmt->fetchColumn();
$nbPages = ceil($totalProduits / $produitsParPage);

// PRODUITS
$sql = "SELECT p.* " . $baseSql . " LIMIT :limit OFFSET :offset";
$stmt = $pdo->prepare($sql);

foreach ($params as $k => $v) {
    $stmt->bindValue($k, $v);
}
$stmt->bindValue(':limit', $produitsParPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

$stmt->execute();
$produits = $stmt->fetchAll(PDO::FETCH_ASSOC);

// HTML
$html = "";

foreach ($produits as $p) {

    $stmtImg = $pdo->prepare(
        "SELECT URL FROM _imageDeProduit WHERE idProduit = ? LIMIT 1"
    );
    $stmtImg->execute([$p['idProduit']]);
    $img = $stmtImg->fetchColumn();

    if (!$img) {
        $img = "public/images/defaultImageProduit.png";
    }

    $html .= "
    <article>
        <img src='/$img' class='imgProduit'>
        <h2>{$p['nom']}</h2>
        <h3>" . formatPrice($p['prix']) . "</h3>
    </article>";
}

header('Content-Type: application/json');
echo json_encode([
    'html' => $html,
    'nbPages' => $nbPages,
    'totalProduits' => $totalProduits
]);

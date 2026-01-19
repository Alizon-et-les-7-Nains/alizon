<?php
require_once "pdo.php";
require_once "prix.php";
session_start();

$produitsParPage = 15;
$page = max(1, (int)($_GET['page'] ?? 1));
$offset = ($page - 1) * $produitsParPage;

$minPrice = isset($_GET['minPrice']) ? (float)$_GET['minPrice'] : 0;
$maxPrice = isset($_GET['maxPrice']) ? (float)$_GET['maxPrice'] : 1000000;
$sortOrder = isset($_GET['sortOrder']) ? $_GET['sortOrder'] : '';
$noteMin = isset($_GET['minNote']) ? (float)$_GET['minNote'] : 1;
$categorie = isset($_GET['categorie']) ? $_GET['categorie'] : "";
$noteMin = isset($_GET['minNote']) ? (float)$_GET['minNote'] : 1;
$pertinenceCroissant = isset($_GET['pertinenceCroissant']) ? $_GET['pertinenceCroissant'] : false;
$pertinenceDecroissant = isset($_GET['pertinenceDecroissant']) ? $_GET['pertinenceDecroissant'] : false;
$vendeur = $_GET['vendeur'] ?? null;
$sqlVendeur="";
$recherche = isset($_GET['search']) ? trim($_GET['search']) : '';
$zone = $_GET['zone'] ?? '';

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

if ($zone !== '') {
    $sqlWhere .= " AND v.cp LIKE :zone"; 
    $params[':zone'] = $zone . '%';
}

$baseSql = "
FROM _produit p
JOIN _categorie c ON p.idCategorie = c.idCategorie
JOIN _vendeur v ON p.idVendeur = v.codeVendeur
LEFT JOIN _remise r 
    ON p.idProduit = r.idProduit
    AND CURDATE() BETWEEN r.debutRemise AND r.finRemise
" . $sqlWhere;

//Compter les produits
$countSql = "SELECT COUNT(*) FROM _produit p
             LEFT JOIN _remise r ON p.idProduit = r.idProduit LEFT JOIN _vendeur v ON v.codeVendeur = p.idVendeur  AND CURDATE() BETWEEN r.debutRemise AND r.finRemise
             WHERE p.note >= :noteMin ". $sqlVendeur ."
             AND (p.prix * (1 - COALESCE(r.tauxRemise,0)/100)) BETWEEN :minPrice AND :maxPrice" . $catCondition;

if ($recherche !== '') {
    $countSql .= " AND (p.nom LIKE :search OR p.description LIKE :search)";
}

$countStmt = $pdo->prepare($countSql);
if (!empty($recherche)) $countStmt->bindValue(':search', '%' . $recherche . '%');
$countStmt->bindValue(':minPrice', $minPrice);
$countStmt->bindValue(':maxPrice', $maxPrice);
$countStmt->bindValue(':noteMin', $noteMin);
if (!empty($categorie)) $countStmt->bindValue(':categorie', $categorie);
if(!empty($vendeur)){
    $countStmt->bindValue(':idVendeur',$vendeur);
}
$countStmt->execute();
$totalProduits = $countStmt->fetchColumn();
$nbPages = ceil($totalProduits / $produitsParPage);

if ($recherche !== '') {
    $sql .= " AND (p.nom LIKE :search OR p.description LIKE :search)";
}

$sql = "SELECT p.*, r.tauxRemise, r.debutRemise, r.finRemise
        FROM _produit p
        LEFT JOIN _remise r ON p.idProduit = r.idProduit LEFT JOIN _vendeur v ON v.codeVendeur = p.idVendeur AND CURDATE() BETWEEN r.debutRemise AND r.finRemise
        WHERE p.note >= :noteMin ". $sqlVendeur ." AND (p.prix * (1 - COALESCE(r.tauxRemise,0)/100)) BETWEEN :minPrice AND :maxPrice" . $catCondition;

if ($sortOrder === 'noteAsc') {
    $sql .= " ORDER BY p.note ASC";
} elseif ($sortOrder === 'noteDesc') {
    $sql .= " ORDER BY p.note DESC";
} elseif ($sortOrder === 'prixAsc') {
    $sql .= " ORDER BY (p.prix * (1 - COALESCE(r.tauxRemise,0)/100)) ASC";
} elseif ($sortOrder === 'prixDesc') {
    $sql .= " ORDER BY (p.prix * (1 - COALESCE(r.tauxRemise,0)/100)) DESC";
}
// Rien
$sql .= " LIMIT :limit OFFSET :offset";

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

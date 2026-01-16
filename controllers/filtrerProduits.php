<?php
include "pdo.php";
include "prix.php";
session_start();

$produitsParPage = 15;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $produitsParPage;

$minPrice = isset($_GET['minPrice']) ? (float)$_GET['minPrice'] : 0;
$maxPrice = isset($_GET['maxPrice']) ? (float)$_GET['maxPrice'] : 1000000;
$sortOrder = isset($_GET['sortOrder']) ? $_GET['sortOrder'] : '';

$noteMin = isset($_GET['minNote']) && $_GET['minNote'] !== "" ? (float)$_GET['minNote'] : 0;

$categorie = isset($_GET['categorie']) ? $_GET['categorie'] : "";
$vendeur = $_GET['vendeur'] ?? null;
$recherche = isset($_GET['search']) ? trim($_GET['search']) : '';

$params = [
    ':noteMin' => $noteMin,
    ':minPrice' => $minPrice,
    ':maxPrice' => $maxPrice
];

$sqlVendeur = "";
if(!empty($vendeur)){
    $sqlVendeur = " AND p.idVendeur = :idVendeur";
    $params[':idVendeur'] = $vendeur;
}

$catCondition = "";
if (!empty($categorie)) {
    $catCondition = " AND p.typeProd = :categorie";
    $params[':categorie'] = $categorie;
}

$searchCondition = "";
if ($recherche !== '') {
    $searchCondition = " AND (p.nom LIKE :search OR p.description LIKE :search)";
    $params[':search'] = '%' . $recherche . '%';
}

$baseSql = " FROM _produit p
             LEFT JOIN _remise r ON p.idProduit = r.idProduit 
             AND CURDATE() BETWEEN r.debutRemise AND r.finRemise
             WHERE p.note >= :noteMin" . $sqlVendeur . $catCondition . $searchCondition . "
             AND (p.prix * (1 - COALESCE(r.tauxRemise,0)/100)) BETWEEN :minPrice AND :maxPrice";

$countSql = "SELECT COUNT(*) " . $baseSql;
$countStmt = $pdo->prepare($countSql);
$countStmt->execute($params);
$totalProduits = $countStmt->fetchColumn();
$nbPages = ceil($totalProduits / $produitsParPage);

$sql = "SELECT p.*, r.tauxRemise, r.debutRemise, r.finRemise " . $baseSql;

if ($sortOrder === 'noteAsc') $sql .= " ORDER BY p.note ASC";
elseif ($sortOrder === 'noteDesc') $sql .= " ORDER BY p.note DESC";
elseif ($sortOrder === 'prixAsc') $sql .= " ORDER BY (p.prix * (1 - COALESCE(r.tauxRemise,0)/100)) ASC";
elseif ($sortOrder === 'prixDesc') $sql .= " ORDER BY (p.prix * (1 - COALESCE(r.tauxRemise,0)/100)) DESC";

$sql .= " LIMIT :limit OFFSET :offset";

$stmt = $pdo->prepare($sql);
foreach($params as $key => $val) $stmt->bindValue($key, $val);
$stmt->bindValue(':limit', (int)$produitsParPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: application/json');
echo json_encode(['html' => $html, 'nbPages' => $nbPages, 'totalProduits' => $totalProduits]);
<?php
require_once "pdo.php";
require_once "prix.php";
session_start();

$produitsParPage = 15;
$page = max(1, (int)($_GET['page'] ?? 1));
$offset = ($page - 1) * $produitsParPage;

// Récupération des paramètres
$minPrice = (float)($_GET['minPrice'] ?? 0);
$maxPrice = (float)($_GET['maxPrice'] ?? 1000000);
$noteMin  = (float)($_GET['minNote'] ?? 0);
$categorie = $_GET['categorie'] ?? '';
$recherche = trim($_GET['search'] ?? '');
$vendeur   = $_GET['vendeur'] ?? '';
$zone      = $_GET['zone'] ?? '';
$sortOrder = $_GET['sortOrder'] ?? '';

// Initialisation des paramètres pour PDO
$params = [
    ':noteMin'  => $noteMin,
    ':minPrice' => $minPrice,
    ':maxPrice' => $maxPrice
];

// 1. Construction dynamique de la clause WHERE
// Note : COALESCE(p.note, 0) permet d'inclure les produits sans note quand on filtre sur 0
$sqlWhere = "
WHERE COALESCE(p.note, 0) >= :noteMin
AND (p.prix * (1 - COALESCE(r.tauxRemise,0)/100)) BETWEEN :minPrice AND :maxPrice
";

if ($categorie !== '') {
    $sqlWhere .= " AND p.typeProd = :categorie"; // On utilise le nom de la catégorie envoyé par le catalogue
    $params[':categorie'] = $categorie;
}

if ($recherche !== '') {
    $sqlWhere .= " AND (p.nom LIKE :search OR p.description LIKE :search)";
    $params[':search'] = '%' . $recherche . '%';
}

if ($vendeur !== '') {
    $sqlWhere .= " AND p.idVendeur = :vendeur";
    $params[':vendeur'] = $vendeur;
}

if ($zone !== '') {
    $sqlWhere .= " AND a.codePostal LIKE :zone";
    $params[':zone'] = $zone . '%';
}

// 2. Base de la requête (Jointures)
// On utilise LEFT JOIN pour l'adresse et la remise pour ne pas cacher les produits qui n'en ont pas
$baseSqlFrom = "
FROM _produit p
LEFT JOIN _vendeur v ON p.idVendeur = v.codeVendeur
LEFT JOIN _adresseVendeur a ON v.idAdresse = a.idAdresse
LEFT JOIN _remise r ON p.idProduit = r.idProduit 
    AND CURDATE() BETWEEN r.debutRemise AND r.finRemise
" . $sqlWhere;

// 3. Compter le total (pour la pagination)
$countStmt = $pdo->prepare("SELECT COUNT(*) " . $baseSqlFrom);
foreach ($params as $k => $v) { $countStmt->bindValue($k, $v); }
$countStmt->execute();
$totalProduits = $countStmt->fetchColumn();
$nbPages = ceil($totalProduits / $produitsParPage);

// 4. Gestion du Tri
$orderClause = " ORDER BY p.idProduit DESC";
if ($sortOrder === 'noteAsc')  $orderClause = " ORDER BY p.note ASC";
if ($sortOrder === 'noteDesc') $orderClause = " ORDER BY p.note DESC";
if ($sortOrder === 'prixAsc')  $orderClause = " ORDER BY (p.prix * (1 - COALESCE(r.tauxRemise,0)/100)) ASC";
if ($sortOrder === 'prixDesc') $orderClause = " ORDER BY (p.prix * (1 - COALESCE(r.tauxRemise,0)/100)) DESC";

// 5. Requête finale pour les produits
$sql = "SELECT p.*, r.tauxRemise, r.debutRemise, r.finRemise " . $baseSqlFrom . $orderClause . " LIMIT :limit OFFSET :offset";
$stmt = $pdo->prepare($sql);

foreach ($params as $k => $v) { $stmt->bindValue($k, $v); }
$stmt->bindValue(':limit', $produitsParPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$produits = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 6. Génération du HTML (Doit être complet pour garder le design)
$html = "";
if (count($produits) > 0) {
    foreach ($produits as $p) {
        $idProduit = $p['idProduit'];
        $prixOriginal = $p['prix'];
        $tauxRemise = $p['tauxRemise'] ?? 0;
        $prixFinal = ($tauxRemise > 0) ? $prixOriginal * (1 - $tauxRemise/100) : $prixOriginal;

        // Récupération de l'image
        $stmtImg = $pdo->prepare("SELECT URL FROM _imageDeProduit WHERE idProduit = ? LIMIT 1");
        $stmtImg->execute([$idProduit]);
        $img = $stmtImg->fetchColumn() ?: 'public/images/defaultImageProduit.png';

        $html .= "<article>";
        if ($tauxRemise > 0) {
            $html .= "<div class='bannierePromo'><h1>-".round($tauxRemise)."%</h1><img class='imgBanniere' src='../../public/images/laBanniere.png'></div>";
        }
        $html .= "<img src='../../$img' class='imgProduit' onclick=\"window.location.href='produit.php?id=$idProduit'\">";
        $html .= "<h2>" . htmlspecialchars($p['nom']) . "</h2>";
        $html .= "<div class='prix'><h2>" . formatPrice($prixFinal) . "</h2>";
        if ($tauxRemise > 0) {
            $html .= "<h3 style='text-decoration:line-through; color:#999;'>" . formatPrice($prixOriginal) . "</h3>";
        }
        $html .= "</div>";
        if ($p['stock'] > 0) {
            $html .= "<button class='plus' data-id='$idProduit'><img src='../../public/images/btnAjoutPanier.svg'></button>";
        } else {
            $html .= "<b style='color:red'>Aucun stock</b>";
        }
        $html .= "</article>";
    }
} else {
    $html = "<h1>Aucun produit disponible</h1>";
}

header('Content-Type: application/json');
echo json_encode([
    'html' => $html,
    'nbPages' => $nbPages,
    'totalProduits' => $totalProduits
]);
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
$noteMin = isset($_GET['minNote']) ? (float)$_GET['minNote'] : 1;
$categorie = isset($_GET['categorie']) ? $_GET['categorie'] : "";
$noteMin = isset($_GET['minNote']) ? (float)$_GET['minNote'] : 1;
$vendeur = $_GET['vendeur'] ?? null;
$sqlVendeur="";
$recherche = isset($_GET['search']) ? trim($_GET['search']) : '';

if(!empty($vendeur)){
    $sqlVendeur = "AND p.idVendeur = :idVendeur";
}

// Construction de la condition de catégorie dynamique
$catCondition = "";
if (!empty($categorie)) {
    $catCondition = " AND p.typeProd = :categorie";
}

// 1. Compter les produits
$countSql = "SELECT COUNT(*) FROM _produit p
             LEFT JOIN _remise r ON p.idProduit = r.idProduit LEFT JOIN _vendeur v ON v.codeVendeur = p.idVendeur  AND CURDATE() BETWEEN r.debutRemise AND r.finRemise
             WHERE p.note >= :noteMin ". $sqlVendeur ."
             AND (p.prix * (1 - COALESCE(r.tauxRemise,0)/100)) BETWEEN :minPrice AND :maxPrice" . $catCondition;

if ($recherche !== '') {
    $countSql .= " AND (p.nom LIKE :search OR p.description LIKE :search)";
}

$countStmt = $pdo->prepare($countSql);
$countStmt->bindValue(':search', '%' . $recherche . '%');
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

$sql = "SELECT p.*, r.tauxRemise, r.debutRemise, r.finRemise
        FROM _produit p
        LEFT JOIN _remise r ON p.idProduit = r.idProduit LEFT JOIN _vendeur v ON v.codeVendeur = p.idVendeur AND CURDATE() BETWEEN r.debutRemise AND r.finRemise
        WHERE p.note >= :noteMin ". $sqlVendeur ." AND (p.prix * (1 - COALESCE(r.tauxRemise,0)/100)) BETWEEN :minPrice AND :maxPrice" . $catCondition;

if ($recherche !== '') {
    $sql .= " AND (p.nom LIKE :search OR p.description LIKE :search)";
}

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
$stmt->bindValue(':search', '%' . $recherche . '%');
$stmt->bindValue(':minPrice', $minPrice);
$stmt->bindValue(':maxPrice', $maxPrice);
$stmt->bindValue(':noteMin', $noteMin);
if (!empty($categorie)) $stmt->bindValue(':categorie', $categorie); // Correction ici : liaison au $stmt
$stmt->bindValue(':limit', (int)$produitsParPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
if(!empty($vendeur)){
    $stmt->bindValue(':idVendeur',$vendeur);
}
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Objet JSON encodé JSON afin de manipuler les données en AJAX
$data = ['html' => '', 'nbPages' => $nbPages, 'totalProduits' => $totalProduits];

// Restructruration du code HTML selon la page et les filtres
if (count($products) > 0) {
    foreach ($products as $value) {
        $idProduit = $value['idProduit'];
        $prixOriginal = $value['prix'];
        $tauxRemise = $value['tauxRemise'] ?? 0;
        $enRemise = !empty($value['tauxRemise']) && $value['tauxRemise'] > 0;
        $prixRemise = $enRemise ? $prixOriginal * (1 - $tauxRemise/100) : $prixOriginal;
        $prixAffichage = $enRemise ? $prixRemise : $prixOriginal;
        $poids = $value['poids'];
        $prixAuKg = $poids > 0 ? $prixAffichage/$poids : 0;
        $prixAuKg = round($prixAuKg,2);

        $stmtImg = $pdo->prepare("SELECT URL FROM _imageDeProduit WHERE idProduit = :idProduit");
        $stmtImg->execute([':idProduit' => $idProduit]);
        $imageResult = $stmtImg->fetch(PDO::FETCH_ASSOC);
        $image = !empty($imageResult) ? $imageResult['URL'] : '../../public/images/defaultImageProduit.png';

        $data['html'] .= 
        '<article data-price="'.$prixAffichage.'">'.
            '<img src="'.htmlspecialchars($image).'" class="imgProduit" onclick="window.location.href=\'produit.php?id='.$idProduit.'\'" alt="Image du produit">'.
            '<h2 class="nomProduit" onclick="window.location.href=\'produit.php?id='.$idProduit.'\'">'.htmlspecialchars($value['nom']).'</h2>'.
            '<div class="notation">'.(number_format($value['note'],1)==0?'<span>Pas de note</span>':'<span>'.number_format($value['note'],1).'</span>');
        for ($i = 0; $i < number_format($value['note'],0); $i++){
            $data['html'] .= '<img src="../../public/images/etoile.svg" alt="Note" class="etoile">';
        }
        $data['html'] .=
        '</div>'.
        '<div class="infoProd"><div class="prix">';
        if($enRemise){
            $data['html'] .=
            '<div style="display:flex;align-items:center;gap:8px;">'.
                '<h2>'.formatPrice($prixRemise).'</h2>'.
                '<h3 style="text-decoration: line-through; color:#999;">'.formatPrice($prixOriginal).'</h3>'.
            '</div>';
        } else {
            $data['html'] .= '<h2>'.formatPrice($prixOriginal).'</h2>';
        }
        if ($poids > 0) {
            $data['html'] .= '<h4>'.htmlspecialchars($prixAuKg).'€/kg</h4>';
        }
        $data['html'] .= '</div>';
        if (number_format($value['stock'], 1) == 0){
            $data['html'] .= '<b style="color: red; margin-right: 5px;">Aucun stock</b>';
        }
        else{
            $data['html'] .= '<button class="plus" data-id="'.htmlspecialchars($value["idProduit"]).'"><img src="../../public/images/btnAjoutPanier.svg" alt="Bouton ajout panier"></button>';
        }
        $data['html'] .= '</div></article>';
    }
} else {
    $data['html'] = '<h1>Aucun produit disponible</h1>';
}

header('Content-Type: application/json');
echo json_encode($data);

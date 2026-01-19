<?php
include "pdo.php";
include "prix.php";
session_start();

$produitsParPage = 15;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $produitsParPage;

// 1. Récupération et sécurisation des paramètres
$minPrice = (float)($_GET['minPrice'] ?? 0);
$maxPrice = (float)($_GET['maxPrice'] ?? 1000000);
$sortOrder = $_GET['sortOrder'] ?? '';
$noteMin  = (float)($_GET['minNote'] ?? 0);
$categorie = $_GET['categorie'] ?? '';
$vendeur   = $_GET['vendeur'] ?? '';
$recherche = trim($_GET['search'] ?? '');
$zone      = $_GET['zone'] ?? '';

// 2. Initialisation des paramètres PDO (évite les erreurs de variables indéfinies)
$params = [
    ':noteMin'  => $noteMin,
    ':minPrice' => $minPrice,
    ':maxPrice' => $maxPrice
];

// 3. Construction de la clause WHERE dynamique
$sqlWhere = " WHERE COALESCE(p.note, 0) >= :noteMin 
              AND (p.prix * (1 - COALESCE(r.tauxRemise,0)/100)) BETWEEN :minPrice AND :maxPrice";

if ($recherche !== '') {
    $sqlWhere .= " AND (p.nom LIKE :search OR p.description LIKE :search)";
    $params[':search'] = '%' . $recherche . '%';
}

if ($categorie !== '') {
    $sqlWhere .= " AND p.typeProd = :categorie";
    $params[':categorie'] = $categorie;
}

if (!empty($vendeur)) {
    $sqlWhere .= " AND p.idVendeur = :idVendeur";
    $params[':idVendeur'] = $vendeur;
}

if ($zone !== '') {
    $sqlWhere .= " AND a.codePostal LIKE :zone";
    $params[':zone'] = $zone . '%';
}

// 4. Définition de la structure SQL avec les JOINTURES (indispensable pour la zone et les remises)
$baseSqlFrom = " FROM _produit p
                 LEFT JOIN _vendeur v ON p.idVendeur = v.codeVendeur
                 LEFT JOIN _adresseVendeur a ON v.idAdresse = a.idAdresse
                 LEFT JOIN _remise r ON p.idProduit = r.idProduit 
                 AND CURDATE() BETWEEN r.debutRemise AND r.finRemise " . $sqlWhere;

// 5. Calcul du total pour la pagination
$countStmt = $pdo->prepare("SELECT COUNT(*) " . $baseSqlFrom);
foreach ($params as $k => $v) { 
    $countStmt->bindValue($k, $v); 
}
$countStmt->execute();
$totalProduits = $countStmt->fetchColumn();
$nbPages = ceil($totalProduits / $produitsParPage);

// 6. Gestion du Tri
$orderClause = " ORDER BY p.idProduit DESC";
if ($sortOrder === 'noteAsc')  $orderClause = " ORDER BY p.note ASC";
if ($sortOrder === 'noteDesc') $orderClause = " ORDER BY p.note DESC";
if ($sortOrder === 'prixAsc')  $orderClause = " ORDER BY (p.prix * (1 - COALESCE(r.tauxRemise,0)/100)) ASC";
if ($sortOrder === 'prixDesc') $orderClause = " ORDER BY (p.prix * (1 - COALESCE(r.tauxRemise,0)/100)) DESC";

// 7. Requête finale pour récupérer les produits
$sql = "SELECT p.*, r.tauxRemise, r.debutRemise, r.finRemise " . $baseSqlFrom . $orderClause . " LIMIT :limit OFFSET :offset";
$stmt = $pdo->prepare($sql);

foreach ($params as $k => $v) { 
    $stmt->bindValue($k, $v); 
}
$stmt->bindValue(':limit', (int)$produitsParPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 8. Construction de la réponse JSON
$data = ['html' => '', 'nbPages' => $nbPages, 'totalProduits' => $totalProduits];

if (count($products) > 0) {
    foreach ($products as $value) {
        $idProduit = $value['idProduit'];
        $prixOriginal = $value['prix'];
        $tauxRemise = $value['tauxRemise'] ?? 0;
        $enRemise = ($tauxRemise > 0);
        $prixAffichage = $enRemise ? $prixOriginal * (1 - $tauxRemise/100) : $prixOriginal;
        
        $poids = $value['poids'] ?? 0;
        $prixAuKg = $poids > 0 ? round($prixAffichage / $poids, 2) : 0;

        $stmtImg = $pdo->prepare("SELECT URL FROM _imageDeProduit WHERE idProduit = :idProduit");
        $stmtImg->execute([':idProduit' => $idProduit]);
        $image = $stmtImg->fetchColumn() ?: '../../public/images/defaultImageProduit.png';

        $data['html'] .= 
        '<article data-price="'.$prixAffichage.'">';
        
        if ($enRemise) {
            $data['html'] .= '<div class="bannierePromo"><h1>-'.round($tauxRemise).'%</h1><img class="imgBanniere" src="../../public/images/laBanniere.png"></div>';
        }

        $data['html'] .= 
            '<img src="'.htmlspecialchars($image).'" class="imgProduit" onclick="window.location.href=\'produit.php?id='.$idProduit.'\'" alt="Image du produit">'.
            '<div class="nomEtPromo"><h2 class="nomProduit" onclick="window.location.href=\'produit.php?id='.$idProduit.'\'">'.
            ($enRemise ? '<span id="promoTexte">Promo</span> ' : '').htmlspecialchars($value['nom']).'</h2></div>'.
            '<div class="notation">'.(number_format($value['note'],1) == 0 ? '<span>Pas de note</span>' : '<span>'.number_format($value['note'],1).'</span>');
        
        for ($i = 0; $i < round($value['note']); $i++){
            $data['html'] .= '<img src="../../public/images/etoile.svg" alt="Note" class="etoile">';
        }
        
        $data['html'] .= '</div><div class="infoProd"><div class="prix">';
        
        if($enRemise){
            $data['html'] .= 
            '<div style="display:flex;align-items:center;gap:8px;">'.
                '<h2>'.formatPrice($prixAffichage).'</h2>'.
                '<h3 style="text-decoration: line-through; color:#999;">'.formatPrice($prixOriginal).'</h3>'.
            '</div>';
        } else {
            $data['html'] .= '<h2>'.formatPrice($prixOriginal).'</h2>';
        }
        
        if ($poids > 0) {
            $data['html'] .= '<h4>'.htmlspecialchars($prixAuKg).'€/kg</h4>';
        }
        
        $data['html'] .= '</div>';
        
        if ($value['stock'] <= 0){
            $data['html'] .= '<b style="color: red; margin-right: 5px;">Aucun stock</b>';
        } else {
            $data['html'] .= '<button class="plus" data-id="'.$idProduit.'"><img src="../../public/images/btnAjoutPanier.svg" alt="Bouton ajout panier"></button>';
        }
        
        $data['html'] .= '</div></article>';
    }
} else {
    // Message d'erreur personnalisé quand aucun produit n'est trouvé
    $data['html'] = '<div style="text-align:center; width:100%; padding:40px;">'.
                    '<h1>Désolé, aucun produit ne correspond à vos critères de recherche ou à cette zone géographique.</h1>'.
                    '<p>Essayez de modifier vos filtres ou de choisir un autre département.</p>'.
                    '</div>';
}

header('Content-Type: application/json');
echo json_encode($data);
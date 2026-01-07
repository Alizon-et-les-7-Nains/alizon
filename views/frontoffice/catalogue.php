<?php
$products = [
    [
        'name' => 'Tomates bio',
        'note' => '4.5/5',
        'nbAvis' => '127 avis',
        'price' => '3.50',
        'prixAuKg' => '3.50 €/kg',
        'category' => 'Fruits & Légumes',
        'seller' => 'Ferme du Soleil',
        'zone' => 'Bretagne',
        'image' => '../../public/images/caramel_beurre_sale.jpg'
    ],
    [
        'name' => 'T-shirt coton bio',
        'note' => '4.8/5',
        'nbAvis' => '89 avis',
        'price' => '25.00',
        'prixAuKg' => '',
        'category' => 'Vêtements',
        'seller' => 'Atelier Textile Local',
        'zone' => 'Normandie',
        'image' => '../../public/images/caramel_beurre_sale.jpg'
    ],
    [
        'name' => 'Miel de lavande',
        'note' => '4.9/5',
        'nbAvis' => '203 avis',
        'price' => '8.50',
        'prixAuKg' => '17.00 €/kg',
        'category' => 'Épicerie',
        'seller' => 'Rucher Provençal',
        'zone' => 'Provence',
        'image' => '../../public/images/caramel_beurre_sale.jpg'
    ],
    [
        'name' => 'Chaussures en cuir',
        'note' => '4.2/5',
        'nbAvis' => '45 avis',
        'price' => '89.00',
        'prixAuKg' => '',
        'category' => 'Vêtements',
        'seller' => 'Cordonnerie Artisanale',
        'zone' => 'Auvergne',
        'image' => '../../public/images/caramel_beurre_sale.jpg'
    ],
    [
        'name' => 'Fromage de chèvre',
        'note' => '4.7/5',
        'nbAvis' => '156 avis',
        'price' => '6.20',
        'prixAuKg' => '24.80 €/kg',
        'category' => 'Produits laitiers',
        'seller' => 'Chèvrerie des Collines',
        'zone' => 'Bourgogne',
        'image' => '../../public/images/caramel_beurre_sale.jpg'
    ],
    [
        'name' => 'Savon artisanal',
        'note' => '4.6/5',
        'nbAvis' => '78 avis',
        'price' => '5.50',
        'prixAuKg' => '',
        'category' => 'Cosmétiques',
        'seller' => 'Savonnerie Nature',
        'zone' => 'Alsace',
        'image' => '../../public/images/caramel_beurre_sale.jpg'
    ],
    [
        'name' => 'Pommes Golden',
        'note' => '4.3/5',
        'nbAvis' => '92 avis',
        'price' => '2.80',
        'prixAuKg' => '2.80 €/kg',
        'category' => 'Fruits & Légumes',
        'seller' => 'Verger du Pays',
        'zone' => 'Normandie',
        'image' => '../../public/images/caramel_beurre_sale.jpg'
    ],
    [
        'name' => 'Pull en laine',
        'note' => '4.9/5',
        'nbAvis' => '34 avis',
        'price' => '65.00',
        'prixAuKg' => '',
        'category' => 'Vêtements',
        'seller' => 'Tricot Local',
        'zone' => 'Bretagne',
        'image' => '../../public/images/caramel_beurre_sale.jpg'
    ],
    [
        'name' => 'Confiture de fraises',
        'note' => '4.8/5',
        'nbAvis' => '167 avis',
        'price' => '4.90',
        'prixAuKg' => '14.70 €/kg',
        'category' => 'Épicerie',
        'seller' => 'Confitures Maison',
        'zone' => 'Aquitaine',
        'image' => '../../public/images/caramel_beurre_sale.jpg'
    ],
    [
        'name' => 'Écharpe en soie',
        'note' => '4.5/5',
        'nbAvis' => '56 avis',
        'price' => '38.00',
        'prixAuKg' => '',
        'category' => 'Accessoires',
        'seller' => 'Atelier Soie',
        'zone' => 'Lyon',
        'image' => '../../public/images/caramel_beurre_sale.jpg'
    ]
];




?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catalogue</title>
    <link rel="icon" href="/public/images/logoBackoffice.svg">
    <link rel="stylesheet" href="../../public/style.css">
</head>
<body>
<?php include '../../views/frontoffice/partials/headerConnecte.php' ?>
<main class="pageCatalogue">
    <aside class="filter-sort">
        <form method="GET" action="">
            <label for="sort">Trier par :</label>
            <label for="filter">Prix</label>
            <label for="filter">note minimale</label>
            <label for="filter">Catégorie</label>
            <label for="filter">Zone géographique</label>
            <label for="filter">Vendeur</label>
        </form>
    </aside>
    <div class="products-section">
        <p id="resultat">*** résultats pour "********"</p>
        <section class="listeArticle">
            <?php 
            $stmt = $pdo->prepare("SELECT p.*, r.tauxRemise, r.debutRemise, r.finRemise 
                                    FROM _produit p 
                                    LEFT JOIN _remise r ON p.idProduit = r.idProduit 
                                    AND CURDATE() BETWEEN r.debutRemise AND r.finRemise
                                    ORDER BY p.idProduit DESC LIMIT 10;");
            $stmt->execute();
            $produitNouveaute = $stmt->fetchAll(PDO::FETCH_ASSOC);

            
            if (count($produitNouveaute) > 0) {
                foreach ($produitNouveaute as $value) {
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
            <article style="margin-top: 5px;">
                <img src="<?php echo htmlspecialchars($image); ?>" class="imgProduit"
                    onclick="window.location.href='?addRecent=<?php echo $idProduit; ?>&id=<?php echo $idProduit; ?>'"
                    alt="Image du produit">
                <h2 class="nomProduit"
                    onclick="window.location.href='?addRecent=<?php echo $idProduit; ?>&id=<?php echo $idProduit; ?>'">
                    <?php echo htmlspecialchars($value['nom']); ?></h2>
                        <div class="notation">
                            <?php if(number_format($value['note'], 1) == 0) { ?>
                                <span><?php echo "Pas de note" ?></span>
                            <?php } else { ?>
                            <span><?php echo number_format($value['note'], 1); ?></span>
                            <?php for ($i=0; $i < number_format($value['note'], 0); $i++) { ?>
                                <img src="../../public/images/etoile.svg" alt="Note" class="etoile">
                            <?php }} ?>
                        </div>
                <div class="infoProd">
                    <div class="prix">
                        <?php if ($enRemise): ?>
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <h2><?php echo formatPrice($prixRemise); ?></h2>
                                <h3 style="text-decoration: line-through; color: #999; margin: 0; font-size: 0.9em;">
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
                            $prixAuKg = round($prixAuKg,2) ?>
                        <h4 style="margin: 0;"><?php echo htmlspecialchars($prixAuKg); ?>€ / kg</h4>
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
</body>
</html>
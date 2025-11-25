<?php
include '../../controllers/pdo.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'ajouter_panier') {
    $idProduit = intval($_POST['idProduit']);
    $quantite = intval($_POST['quantite']);
    
    if (isset($_SESSION['user_id'])) {
        $idClient = $_SESSION['user_id'];
                $success = updateQuantityInDatabase($pdo, $idClient, $idProduit, $quantite);
    }
    if ($success) {
        $_SESSION['message_panier'] = "Produit ajouté au panier avec succès!";
    } else {
        $_SESSION['message_panier'] = "Erreur lors de l'ajout au panier.";
    }
}

$productId = intval($_GET['id']) ?? 0;

if($productId == 0) {
    die("Produit non spécifié");
}

$sqlProduit = "SELECT 
                p.idProduit,
                p.nom AS nom_produit,
                p.description, 
                p.prix,
                p.note,
                p.stock,
                v.prenom AS prenom_vendeur,
                v.nom AS nom_vendeur,
                v.raisonSocial,
                promo.idPromotion,
                promo.debutPromotion,
                promo.finPromotion,
                remise.idRemise,
                remise.tauxRemise,
                remise.debutRemise,
                remise.finRemise
               FROM _produit p 
               JOIN _vendeur v ON p.idVendeur = v.codeVendeur
               LEFT JOIN _promotion promo ON p.idProduit = promo.idProduit 
                    AND CURDATE() BETWEEN promo.debutPromotion AND promo.finPromotion
               LEFT JOIN _remise remise ON p.idProduit = remise.idProduit 
                    AND CURDATE() BETWEEN remise.debutRemise AND remise.finRemise
               WHERE p.idProduit = $productId";

$result = $pdo->query($sqlProduit);
$produit = $result->fetch(PDO::FETCH_ASSOC);

$idClient = $_SESSION['user_id'] ?? 0;
$sqlAdresse = "SELECT * 
               FROM _adresseClient 
               WHERE idAdresse = (SELECT idAdresse FROM _client WHERE idClient = ?)";
$stmtAdresse = $pdo->prepare($sqlAdresse);
$stmtAdresse->execute([$idClient]);
$adresse = $stmtAdresse->fetch(PDO::FETCH_ASSOC);

if (!$produit) {
    echo "<p>Produit introuvable.</p>";
    exit;
}
$sqlImages = "SELECT * 
              FROM _imageDeProduit 
              WHERE idProduit = $productId";

$resultImages = $pdo->query($sqlImages);
$images = $resultImages->fetchAll(PDO::FETCH_ASSOC);

function updateQuantityInDatabase($pdo, $idClient, $idProduit, $delta) {
    $idProduit = intval($idProduit);
    $idClient = intval($idClient);
    $delta = intval($delta);
    
    if ($idClient <= 0 || $idProduit <= 0 || $delta <= 0) {
        return false;
    }
    
    try {        
        $stmtPanier = $pdo->prepare("SELECT idPanier FROM _panier WHERE idClient = ? ORDER BY idPanier DESC LIMIT 1");
        $stmtPanier->execute([$idClient]);
        $panier = $stmtPanier->fetch(PDO::FETCH_ASSOC);
        
        if (!$panier) {            $stmtCreate = $pdo->prepare("INSERT INTO _panier (idClient) VALUES (?)");
            $stmtCreate->execute([$idClient]);
            $idPanier = $pdo->lastInsertId();
        } else {
            $idPanier = $panier['idPanier'];
        }
        
        $sql = "SELECT quantiteProduit FROM _produitAuPanier 
                WHERE idProduit = ? AND idPanier = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$idProduit, $idPanier]);
        $current = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($current) {
            $newQty = intval($current['quantiteProduit']) + $delta;
            
            $sql = "UPDATE _produitAuPanier SET quantiteProduit = ? 
                    WHERE idProduit = ? AND idPanier = ?";
            $stmt = $pdo->prepare($sql);
            return $stmt->execute([$newQty, $idProduit, $idPanier]);
        } else {
            $sql = "INSERT INTO _produitAuPanier (idProduit, idPanier, quantiteProduit) VALUES (?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            return $stmt->execute([$idProduit, $idPanier, $delta]);
        }
    } catch (PDOException $e) {
        error_log("Erreur panier: " . $e->getMessage());
        return false;
    }
}

$sqlAvis = "SELECT a.*
            FROM _avis a
            WHERE a.idProduit = $productId";

$resultAvis = $pdo->query($sqlAvis);
$lesAvis = $resultAvis->fetchAll(PDO::FETCH_ASSOC);

$sqlNoteMoyenne = "SELECT AVG(note) as moyenne_note FROM _avis WHERE idProduit = ?";
$stmt = $pdo->prepare($sqlNoteMoyenne);
$stmt->execute([$productId]);
$resultNote = $stmt->fetch(PDO::FETCH_ASSOC);
$note = $resultNote['moyenne_note'] ?? 0;

$sqlNbAvis = "SELECT COUNT(note) as nb_avis FROM _avis WHERE idProduit = ?";
$stmt = $pdo->prepare($sqlNbAvis);
$stmt->execute([$productId]);
$resultNbAvis = $stmt->fetch(PDO::FETCH_ASSOC);
$nombreAvis = $resultNbAvis['nb_avis'] ?? 0;



// $images = [
//     [
//         'URL' => 'cidre.png',
//         'title' => 'Premium Cidre'
//     ],
//     [
//         'URL' => 'rillettes.png', 
//         'title' => 'Artisanal Cidre'
//     ],
//     [
//         'URL' => 'defaultImageProduit.png',
//         'title' => 'Traditional Cidre'
//     ]
// ];

// // Your existing product data (mock)
// $produit = [
//     'nom_produit' => 'Cidre Artisanal Breton de merde',
//     'description' => 'Un cidre artisanal produit selon les méthodes traditionnelles bretonnes...',
//     'prix' => 12.50,
//     'prenom_vendeur' => 'Jean',
//     'nom_vendeur' => 'Dupont',
//     'stock' => 20 ];
// 
function calculerPromotion($produit) {
    $promotion = [
        'est_en_promotion' => false,
        'est_en_remise' => false,
        'prix_original' => $produit['prix'],
        'prix_promotion' => $produit['prix'],
        'taux_remise' => 0,
        'economie' => 0,
        'date_fin_promotion' => null
    ];
    if (!empty($produit['idRemise']) && 
        $produit['debutRemise'] <= date('Y-m-d') && 
        $produit['finRemise'] >= date('Y-m-d')) {
        
        $promotion['est_en_remise'] = true;
        $promotion['taux_remise'] = $produit['tauxRemise'];
        $promotion['prix_promotion'] = round($produit['prix'] * (1 - $produit['tauxRemise']/100), 2);
        $promotion['economie'] = $produit['prix'] - $promotion['prix_promotion'];
        $promotion['date_fin_promotion'] = $produit['finRemise'];
    }
    
    elseif (!empty($produit['idPromotion']) && 
            $produit['debutPromotion'] <= date('Y-m-d') && 
            $produit['finPromotion'] >= date('Y-m-d')) {
        
        $promotion['est_en_promotion'] = true;
        $promotion['date_fin_promotion'] = $produit['finPromotion'];
    }
    return $promotion;
}

$promotion = calculerPromotion($produit);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <!-- sass --watch views/styles/main.scss:public/style.css -->
    <!-- ssh sae@10.253.5.104
    su -
    grognasseEtCompagnie
    cd /docker/data/web/html
    git pull -->

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($produit['nom_produit'])?></title>
    <link rel="icon" href="/public/images/logoBackoffice.svg">
    <link rel="stylesheet" href="../../public/style.css">
</head>
<body class="pageProduit">
<header>
<?php if (isset($_SESSION['user_id'])) {
    include '../../views/frontoffice/partials/headerConnecte.php';
} else { 
    include '../../views/frontoffice/partials/headerDeconnecte.php';
} ?>
</header>
<main>
<?php
if (isset($_SESSION['message_panier'])) {
    echo '<div class="message-panier" style="background-color: #d4edda; color: #155724; padding: 10px; margin: 10px; border-radius: 5px; border: 1px solid #c3e6cb;">';
    echo htmlspecialchars($_SESSION['message_panier']);
    echo '</div>';
    unset($_SESSION['message_panier']);
}
?>
<section class="infoHautProduit">
<article class="rectangleProduit">
    <div class="banniere">
        <h1>-<?php echo htmlspecialchars($promotion['taux_remise']); ?>%</h1>
        <img class="poly1" src="../../public/images/poly1.svg" alt="">
        <img class="imgBanniere" src="../../public/images/laBanniere.png" alt="">
        <img class="poly2" src="../../public/images/poly2.svg" alt="">
    </div>
    <img src="../../public/images/flecheGauche.svg" alt="Previous" class="carousel-arrow prev-arrow">
    <div class="carousel-container">
        <div class="carousel-slide">
            <?php if (!empty($images)): ?>
                <?php foreach ($images as $index => $image): ?>
                    <img src="<?php echo htmlspecialchars($image['URL']); ?>" 
                         alt="Image produit <?php echo $index + 1; ?>"
                         class="carousel-image <?php echo $index === 0 ? 'active' : ''; ?>">
                <?php endforeach; ?>
            <?php else: ?>
                <img src="../../public/images/placeholder.jpg" alt="Pas d'image trouvée" class="carousel-image active">
            <?php endif; ?>
        </div>
        <div id="lesCercles" class="carousel-indicators">
            <?php if (count($images) > 1): ?> 
                <?php foreach ($images as $index => $image): ?>
                    <div class="cercleNoir indicator <?php echo $index === 0 ? 'active' : ''; ?>" 
                         data-index="<?php echo $index; ?>"></div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
    <img src="../../public/images/flecheDroite.svg" alt="Next" class="carousel-arrow next-arrow">
    </article>
    <article class="infoPreviewProduit">
        <div class="attributsproduit">
            <h1 class="nomProduit"><?php echo htmlspecialchars($produit['nom_produit']); ?></h1>
            <?php if ($promotion['est_en_promotion']): ?>
            <h3>Promotion</h3> 
            <?php endif; ?>
        </div>
        <div class="product-rating">
            <div>
                <div class="star-rating">
                    <div class="stars" style="--rating: <?php echo $note; ?>"></div>
                </div>
                <span class="rating-number"><?php echo number_format($note, 1); ?>/5</span>
            </div>
            <span class="review-count" id="reviewCountHautProduit"><?php echo $nombreAvis; ?> évaluations</span>
        </div>
        <div id="prix">
            <?php if ($promotion['est_en_remise']): ?>
            <h1><?php echo number_format($promotion['prix_promotion'], 2, ',', ' '); ?>€</h1>
            <h3><del><?php echo number_format($produit['prix'], 2, ',', ' '); ?>€</del></h3> 
            <?php else: ?>
            <h1><?php echo number_format($produit['prix'], 2, ',', ' '); ?>€</h1>
            <?php endif; ?>
        </div>
        <h2>Description de l'article :</h2>
        <p><?php echo htmlspecialchars($produit['description']);?></p>
        <a href="#conteneurTexte">Voir plus sur le produit</a>
        <div class="version">
            <h3>Version :</h3>
            <!-- <p>50cl</p>
            <p>1L</p>
            <p>1.5L</p> -->
        </div>
        <h3>Choisir un type de produit :</h3>
        <!-- <div>
            <img src="../../public/images/Image_bouteille.svg" alt="">
            <img src="../../public/images/Image_bouteille.svg" alt="">
            <img src="../../public/images/Image_bouteille.svg" alt="">
            <img src="../../public/images/Image_bouteille.svg" alt="">
        </div> -->
    </article>
    <article class="actionsProduit">
        <h2>Vendu par <?php echo htmlspecialchars($produit['prenom_vendeur'] . ' ' . $produit['nom_vendeur']); ?></h2>
        <p class="underline" id="plusDarticles"><a href="">Plus d'article de ce vendeur</a></p>
        <br>
        <hr>
        <div class="ligneActions">
            <img src="../../public/images/camion.png" alt="">
            <p>Livraison <b>GRATUITE</b> - Expédié par <b>mondial relais</b>. Arrivée entre le <b>mar. 21 septembre - ven. 24 septembre</b></p>
        </div>
        <div class="ligneActions">
            <img src="../../public/images/emplacement.png" alt="">
            <p>Livré a <a href=""><b>
                <?php echo htmlspecialchars($adresse['ville']); ?>
                <?php echo htmlspecialchars($adresse['codePostal']);?></b>, 
                <?php echo htmlspecialchars($adresse['adresse']); ?></a></p>   
        </div>
        <div class="ligneActions">
            <img src="../../public/images/tec.png" alt="">
        <p>
            Consulter les <b><?php if (isset($_SESSION['user_id'])) {
            echo '<a href="legalesConnecte.php">conditions générales de vente</a>';
        } else { 
            echo '<a href="legalesNonConnecte.php">conditions générales de vente</a>';
        } ?>
        </b></p>
    </div>
    <hr>
    <br>
    <div id="quantite">
        <form action="" method="POST">
            <div id="quantiteContainer">
                <p>Quantité</p>
                <div>
                    <button type="button" id="moins"><img src="../../public/images/moins.svg" alt=""></button>
                    <input type="text" id="quantiteInput" name="quantite" value="1" readonly>
                    <button type="button" id="plus"><img src="../../public/images/plus.svg" alt=""></button>
                </div>
            </div>
            <input type="hidden" name="idProduit" value="<?php echo $productId; ?>">
            <input type="hidden" name="action" value="ajouter_panier">
            <button class="bouton boutonRose" type="submit" name="ajouter_panier">Ajouter au panier</button>
        </form>
        <?php if (isset($_SESSION['user_id'])) {
            echo '  <form action="pagePaiement.php" method="POST">
                        <input type="hidden" name="idProduit" value="<?php echo $productId; ?>">
                        <button class="bouton boutonBleu" >Acheter maintenant</button>
                    </form>';
        } else { 
            echo '  <form action="connexionClient.php" method="POST">
                        <input type="hidden" name="idProduit" value="<?php echo $productId; ?>">
                        <button class="bouton boutonBleu" >Acheter maintenant</button>
                    </form>';
        } ?>
    </div>
</article>
</section>
<hr>
<section class="informations">
    <input type="checkbox" id="activeVoirPlus">
    <div class="conteneurTexte" id="conteneurTexte">
        <h2>Plus d'informations sur l'article</h2>
        <p>
        <?php 
            echo htmlspecialchars($produit['description']);
        ?>
        </p>
    </div> 
    <label for="activeVoirPlus" class="voirPlus"> </label> 
</section>
<hr>
<section class="sectionAvis">
    <h2>Ce qu'en disent nos clients</h2>
    <div class="product-rating">
        <div class="horizontal">
            <div class="star-rating">
                <div class="stars" style="--rating: <?php echo $note; ?>"></div>
            </div>
            <span class="rating-number"><?php echo number_format($note, 1); ?>/5</span>
        </div>
        <span class="review-count"><?php echo $nombreAvis; ?> évaluations</span>
    </div>
    <?php if (isset($_SESSION['user_id'])) {
    echo 
    '<a href="ecrireCommentaire.php?id=' . $productId . '" class="boutonCommentaire">
        Écrire un commentaire
    </a>';
    } else {
    echo     
    '<a href="connexionClient.php" class="boutonCommentaire">
        Écrire un commentaire
    </a>';
    }
    ?>

    <?php if (!empty($lesAvis)): ?>
        <?php foreach ($lesAvis as $avis): ?>
            <article>
                <img src="../../public/images/pp.png" id="pp">
                <div>
                    <div class="vertical">
                        <div class="horizontal">
                            <div class="star-rating">
                                <div class="stars" style="--rating: <?php echo htmlspecialchars($avis['note']); ?>"></div>
                            </div>
                            <h3><?php echo htmlspecialchars($avis['titreAvis']); ?></h3>
                        </div>
                        <h6>Avis déposé le <?php echo htmlspecialchars($avis['dateAvis']); ?> par <?php echo htmlspecialchars($avis['idClient']); ?></h6>
                    </div>
                    <p><?php echo htmlspecialchars($avis['contenuAvis']); ?></p>
                    <div class="baselineSpaceBetween">
                        <div class="sectionImagesAvis">
                        </div>   
                        <div class="actionsAvis">
                            <img src="../../public/images/pouceHaut.png" alt="Like" onclick="changerPouce(this, 'haut')" class="pouce">
                            <img src="../../public/images/pouceBas.png" alt="Dislike" onclick="changerPouce(this, 'bas')" class="pouce">
                            <shape></shape>
                            <a href="#">Signaler</a>
                        </div>
                    </div>
                </div>
            </article>
        <?php endforeach; ?>
    <?php else: ?>
        <p>Aucun avis pour ce produit.</p>
    <?php endif; ?>

</section>
<section class="stickyTelephone">
    <img src="../../public/images/<?php echo !empty($images[0]['URL']) ? htmlspecialchars($images[0]['URL']) : 'placeholder.jpg'; ?>" alt="<?php echo htmlspecialchars($produit['nom_produit']); ?>">
    <article>
        <aside>
            <h3><?php echo htmlspecialchars($produit['nom_produit']);?></h3>
            <h2 id="prixTelephone"><?php echo number_format($produit['prix'], 2, ',', ' '); ?>€</h2>
        </aside>
        <aside>
            <form action="" method="POST">
                <input type="hidden" name="idProduit" value="<?php echo $productId; ?>">
                <input type="hidden" name="quantite" value="1">
                <input type="hidden" name="action" value="ajouter_panier">
                <button class="bouton boutonRose" type="submit" name="ajouter_panier">Ajouter au panier</button>
            </form>
            <form action="pagePaiement.php" method="POST">
                <input type="hidden" name="idProduit" value="<?php echo $productId; ?>">
                <button class="bouton boutonBleu">Acheter maintenant</button>
            </form>
        </aside>
    </article>
</section>
</main>
<footer>
<?php if (isset($_SESSION['user_id'])) {
    include '../../views/frontoffice/partials/footerConnecte.php';
} else { 
    include '../../views/frontoffice/partials/footerDeconnecte.php';
} ?>
</footer> 
</body>
<script>
class ProductCarousel {
    constructor() {
        this.currentImageIndex = 0;
        this.images = document.querySelectorAll('.carousel-image');
        this.init();
    }

    init() {
        this.setupEventListeners();
        this.showImage(this.currentImageIndex);
    }

    setupEventListeners() {
        const prevArrow = document.querySelector('.prev-arrow');
        const nextArrow = document.querySelector('.next-arrow');

        if (prevArrow) {
            prevArrow.addEventListener('click', () => this.prevImage());
        }
        
        if (nextArrow) {
            nextArrow.addEventListener('click', () => this.nextImage());
        }
        
        const indicators = document.querySelectorAll('.indicator');
        indicators.forEach(indicator => {
            indicator.addEventListener('click', () => {
                const index = parseInt(indicator.getAttribute('data-index'));
                this.goToImage(index);
            });
        });
        
        document.addEventListener('keydown', (event) => {
            if (event.key === 'ArrowLeft') {
                this.prevImage();
            } else if (event.key === 'ArrowRight') {
                this.nextImage();
            }
        });
    }
    
    showImage(index) {
        this.images.forEach(img => {
            img.classList.remove('active');
        });
        
        if (this.images[index]) {
            this.images[index].classList.add('active');
        }
        
        const indicators = document.querySelectorAll('.indicator');
        indicators.forEach((indicator, i) => {
            indicator.classList.toggle('active', i === index);
        });
        
        this.currentImageIndex = index;
    }
    
    nextImage() {
        let nextIndex = this.currentImageIndex + 1;
        if (nextIndex >= this.images.length) {
            nextIndex = 0;
        }
        this.showImage(nextIndex);
    }
    
    prevImage() {
        let prevIndex = this.currentImageIndex - 1;
        if (prevIndex < 0) {
            prevIndex = this.images.length - 1;
        }
        this.showImage(prevIndex);
    }
    
    goToImage(index) {
        if (index >= 0 && index < this.images.length) {
            this.showImage(index);
        }
    }
}

document.addEventListener('DOMContentLoaded', function() {
    new ProductCarousel();
    
    let quantite = 1;
    const quantiteInput = document.getElementById('quantiteInput');
    
    const plusBtn = document.getElementById('plus');
    const moinsBtn = document.getElementById('moins');

    const stock = <?php echo $produit['stock']; ?>;
    
    if (plusBtn && quantiteInput) {
        plusBtn.addEventListener('click', () => {
            if (quantite < stock){
                quantite++;
                quantiteInput.value = quantite;
                quantiteInput.style.color = '#273469';
            }
            else {
                quantiteInput.style.color = 'red';
                
                setTimeout(() => {
                    quantiteInput.style.color = '#273469';
                }, 300);
            }
        });
    }
    
    if (moinsBtn && quantiteInput) {
        moinsBtn.addEventListener('click', () => {
            if (quantite > 1){
                quantite--;
                quantiteInput.value = quantite;
            }
        });
    }
});

function changerPouce(element, type) {
    const article = element.closest('article');
    const pouceHaut = article.querySelector('img[alt="Like"]');
    const pouceBas = article.querySelector('img[alt="Dislike"]');
    
    const pouceHautInactif = "../../public/images/pouceHaut.png";
    const pouceHautActif = "../../public/images/pouceHautActive.png";
    const pouceBasInactif = "../../public/images/pouceBas.png";
    const pouceBasActif = "../../public/images/pouceBasActive.png";
    
    if (element.src.includes('Active')) {
        if (type === 'haut') {
            element.src = pouceHautInactif;
        } else {
            element.src = pouceBasInactif;
        }
    } else {
        if (pouceHaut) pouceHaut.src = pouceHautInactif;
        if (pouceBas) pouceBas.src = pouceBasInactif;
        
        if (type === 'haut') {
            element.src = pouceHautActif;
        } else {
            element.src = pouceBasActif;
        }
    }
}



</script>

</script>
</html>
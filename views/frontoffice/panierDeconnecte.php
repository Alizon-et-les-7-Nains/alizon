<?php
require_once "../../controllers/pdo.php";
require_once "../../controllers/prix.php";

    const PRODUIT_CONSULTE_MAX_SIZE = 4;

    const PRODUIT_DANS_PANIER_MAX_SIZE = 10;

    // Récupération du cookie existant ou création d'un tableau vide
    if (((isset($_COOKIE['produitConsulte'])) && (isset($_COOKIE['produitPanier']))) && (!empty($_COOKIE['produitConsulte']) && !empty($_COOKIE['produitPanier']))) {
        $tabIDProduitConsulte = unserialize($_COOKIE['produitConsulte']);
        $tabIDProduitPanier = unserialize($_COOKIE['produitPanier']);
        if (!is_array($tabIDProduitConsulte)) {
            $tabIDProduitConsulte = [];
            $tabIDProduitPanier = [];
        }
    } else {
        $tabIDProduitConsulte = [];
        $tabIDProduitPanier = [];
    }

    // Fonction pour ajouter un produit consulte
    function ajouterProduitPanier(&$tabIDProduitPanier, $idProduit, $quantite = 1) {
        if (isset($tabIDProduitPanier[$idProduit])) {
            $tabIDProduitPanier[$idProduit] += $quantite;
        } else {
            if (count($tabIDProduitPanier) >= PRODUIT_DANS_PANIER_MAX_SIZE) {
                $message = "Impossible d'ajouter plus de ".PRODUIT_DANS_PANIER_MAX_SIZE." produits différents. Connectez-vous pour en ajouter plus.";
                echo "<script>alert(".json_encode($message).");</script>";
                return false;
            }
            $tabIDProduitPanier[$idProduit] = $quantite;
        }
        
        setcookie("produitPanier", serialize($tabIDProduitPanier), time() + (60*60*24*90), "/");
        return true;
    }

    function modifierQuantitePanier(&$tabIDProduitPanier, $idProduit, $quantite) {
        if (isset($tabIDProduitPanier[$idProduit])) {
            if ($quantite == 0) {
                unset($tabIDProduitPanier[$idProduit]);
            } else {
                $tabIDProduitPanier[$idProduit] += $quantite;
            }
        }
        
        setcookie("produitPanier", serialize($tabIDProduitPanier), time() + (60*60*24*90), "/");
        return true;
    }

    if (isset($_GET['addPanier']) && !empty($_GET['addPanier'])) {
        $idProduitAjoute = intval($_GET['addPanier']);
        $quantite = isset($_GET['qty']) ? intval($_GET['qty']) : 1;
        ajouterProduitPanier($tabIDProduitPanier, $idProduitAjoute, $quantite);
        
        if (isset($_GET['id'])) {
            header("Location: produit.php?id=" . intval($_GET['id']));
            exit;
        }
    }

    $tabIDProduitPanier = unserialize($_COOKIE['produitPanier']);

    // Récupération des informations des produits dans le panier
    $nbProduit = count($tabIDProduitPanier);
    foreach ($tabIDProduitPanier as $key => $value) {
        $totalProduit += $value;
    }

    // ============================================================================
    // AFFICHAGE DE LA PAGE
    // ============================================================================
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../public/style.css">
    <title>Alizon - Votre panier</title>
</head>
<body class="panier">
    <?php include "../../views/frontoffice/partials/headerConnecte.php"; ?>

    <main>
        <section class="listeProduit">
            <?php foreach ($tabIDProduitPanier as $item) { ?>
                <article>
                    <div class="imgProduit">
                        <?php 
                            $idProduit = $item['idProduit'] ?? 0;
                            $stmtImg = $pdo->prepare("SELECT URL FROM _imageDeProduit WHERE idProduit = :idProduit");
                            $stmtImg->execute([':idProduit' => $idProduit]);
                            $imageResult = $stmtImg->fetch(PDO::FETCH_ASSOC);
                            $image = !empty($imageResult) ? $imageResult['URL'] : '../../public/images/defaultImageProduit.png';    
                        ?>
                        <img src="<?= htmlspecialchars($image) ?>" alt="<?= htmlspecialchars($item['nom'] ?? '') ?>">
                    </div>
                    <div class="infoProduit">
                        <div>
                            <h2><?= htmlspecialchars($item['nom'] ?? 'N/A') ?></h2>
                            <h4>En stock</h4>
                        </div>
                        <div class="quantiteProduit">
                            <button class="minus" data-id="<?= htmlspecialchars($item['idProduit'] ?? '') ?>" onclick="window.location.href='?addPanier=<?php echo $idProduit; ?>&qty=<?php echo -1; ?>'">
                            <img src="../../public/images/minusDarkBlue.svg" alt="Symbole moins">
                        </button>                            
                        <p class="quantite"><?= htmlspecialchars($item['qty'] ?? 'N/A') ?></p> 
                        <button class="plus" data-id="<?= htmlspecialchars($item['idProduit'] ?? '') ?>" onclick="window.location.href='?addPanier=<?php echo $idProduit; ?>&qty=<?php echo 1; ?>'">
                            <img src="../../public/images/plusDarkBlue.svg" alt="Symbole plus">
                        </button> 
                        </div>
                    </div>
                    <div class="prixOpt">
                        <?= htmlspecialchars($item['prix'] ?? 'N/A') ?>          
                        <button class="delete" data-id="<?= htmlspecialchars($item['idProduit'] ?? '') ?>" onclick="window.location.href='?addPanier=<?php echo $idProduit; ?>&qty=<?php echo 0; ?>'">
                        <img src="../../public/images/binDarkBlue.svg" alt="Enlever produit">
                        </button>
                    </div>
                </article> 
            <?php } if ($nbProduit==0) { ?>
                <h1 class="aucunProduit">Aucun produit</h1>
            <?php } else { ?>
        </section>
        <section class="recapPanier">
            <h1>Votre panier</h1>
            <div class="cardRecap">
                <article>
                    <h2><b>Récapitulatif de votre panier</b></h2>
                    <div class="infoCommande">
                        <section>
                            <h2>Nombres d'articles</h2>
                            <h2 class="val"><?= $nbProduit ?? 0 ?></h2>
                        </section>
                        <section>
                            <h2>Prix HT</h2>
                            
                            <?php
                            $prixTotal = 0;

                            foreach ($tabIDProduitPanier as $idP) {
                                $prix = $pdo->query("SELECT prix FROM _produit WHERE idProduit = " . intval($idP));
                                $panier = $prix ? $prix->fetch(PDO::FETCH_ASSOC) : false;

                                $prixTotal += $panier['prix'] ?? 0;
                            }
                            ?>

                            <h2 class="val"><?= number_format($prixTotal, 2) ?>€</h2>
                        </section>
                        <section>
                            <h2>TVA</h2>
                            <h2 class="val"><?= number_format($prixTotal * 0.2, 2) ?>€</h2>
                        </section>
                        <section>
                            <h2>Total</h2>
                            <h2 class="val"><?= number_format($prixTotal * 1.2, 2) ?>€</h2>
                        </section>
                    </div>
                </article>
                <a href="../../views/frontoffice/connexionClient.php"><p>Passer la commande</p></a>
            </div>
            <a href="" class="viderPanier">Vider le panier</a>
        </section>
        <?php } ?>
    </main>

    <?php include "../../views/frontoffice/partials/footerConnecte.php"; ?>

    <script src="../scripts/frontoffice/paiement-ajax.js"></script>
    <script src="../../public/amd-shim.js"></script>
    <script src="../../public/script.js"></script>
</body>
</html>
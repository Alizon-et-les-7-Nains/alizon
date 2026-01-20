<?php
// Initialisation de la connexion avec le serveur / BDD
require_once "../../controllers/pdo.php";
require_once "../../controllers/prix.php";

    // Constante définissant le nombre maximum de produits différents dans le panier pour les utilisateurs déconnectés
    const PRODUIT_DANS_PANIER_MAX_SIZE = 10;

    // Récupération du cookie du panier ou création d'un tableau vide
    // Pour les utilisateurs déconnectés, le panier est stocké uniquement dans les cookies
    if (!isset($_COOKIE["produitPanier"]) || empty($_COOKIE["produitPanier"])) {
        $tabIDProduitPanier = [];
    } else {
        // Désérialisation du cookie pour récupérer le tableau des produits
        $tabIDProduitPanier = @unserialize($_COOKIE["produitPanier"]);
        
        // Sécurisation : si la désérialisation échoue, on remet un tableau vide
        if (!is_array($tabIDProduitPanier)) {
            $tabIDProduitPanier = [];
        }
    }

    // Calcul du nombre total de produits dans le panier (somme des quantités)
    $nbProduit = 0;
    foreach ($tabIDProduitPanier as $key => $value) {
        $nbProduit = $nbProduit + $value;
    }

    // Fonction pour ajouter un produit au panier via cookie (utilisateurs déconnectés)
    function ajouterProduitPanier(&$tabIDProduitPanier, $idProduit, $quantite = 1) {
        // Si le produit existe déjà dans le panier, on augmente sa quantité
        if (isset($tabIDProduitPanier[$idProduit])) {
            $tabIDProduitPanier[$idProduit] += $quantite;
        } else {
            // Vérification de la limite de produits différents dans le panier
            if (count($tabIDProduitPanier) >= PRODUIT_DANS_PANIER_MAX_SIZE) {
                $message = "Impossible d'ajouter plus de ".PRODUIT_DANS_PANIER_MAX_SIZE." produits différents. Connectez-vous pour en ajouter plus.";
                echo "<script>alert(".json_encode($message).");</script>";
                return false;
            }
            $tabIDProduitPanier[$idProduit] = $quantite;
        }
        
        // Mise à jour du cookie avec le nouveau contenu du panier (durée : 90 jours)
        setcookie("produitPanier", serialize($tabIDProduitPanier), time() + (60*60*24*90), "/");
        return true;
    }

    // Fonction pour modifier la quantité d'un produit dans le panier (augmenter, diminuer ou supprimer)
    function modifierQuantitePanier(&$tabIDProduitPanier, $idProduit, $quantite) {
        if (isset($tabIDProduitPanier[$idProduit])) {
            // Si la quantité est 0 ou devient négative, on supprime le produit du panier
            if ($quantite == 0 || ($tabIDProduitPanier[$idProduit] + $quantite) <= 0) {
                unset($tabIDProduitPanier[$idProduit]);
            } else {
                $tabIDProduitPanier[$idProduit] += $quantite;
            }
        }
        
        setcookie("produitPanier", serialize($tabIDProduitPanier), time() + (60*60*24*90), "/");
        
        // Redirection vers la page du panier pour rafraîchir l'affichage
        header("Location: panierDeconnecte.php");
        return true;
    }

    // Gestion de l'ajout ou de la modification de quantité via paramètres GET
    if (isset($_GET['addPanier']) && !empty($_GET['addPanier'])) {
        $idProduitAjoute = intval($_GET['addPanier']);
        $quantite = isset($_GET['qty']) ? intval($_GET['qty']) : 1;
        modifierQuantitePanier($tabIDProduitPanier, $idProduitAjoute, $quantite);
        
        if (isset($_GET['id'])) {
            header("Location: produit.php?id=" . intval($_GET['id']));
            exit;
        }
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
    <link rel="icon" href="/public/images/logoBackoffice.svg">
    <title>Alizon - Votre panier</title>
</head>
<body class="panier">
    <?php include "../../views/frontoffice/partials/headerDeconnecte.php"; ?>

    <main>
        <section class="listeProduit">
            <?php // Affichage de chaque produit présent dans le panier
            foreach ($tabIDProduitPanier as $idProduit => $quantite) { 
                // Récupération des informations du produit depuis la base de données
                $stmt = $pdo->prepare("SELECT * FROM _produit WHERE idProduit = ?");
                $stmt->execute([intval($idProduit)]);
                $panier = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : false;

                ?>
                <article>
                    <div class="imgProduit">
                        <?php 
                            // Récupération de l'image du produit ou utilisation de l'image par défaut
                            $stmtImg = $pdo->prepare("SELECT URL FROM _imageDeProduit WHERE idProduit = :idProduit");
                            $stmtImg->execute([':idProduit' => $idProduit]);
                            $imageResult = $stmtImg->fetch(PDO::FETCH_ASSOC);
                            $image = !empty($imageResult) ? $imageResult['URL'] : '../../public/images/defaultImageProduit.png';    
                        ?>
                        <img src="<?= htmlspecialchars($image) ?>" alt="<?= htmlspecialchars($panier['nom'] ?? 'N/A') ?>">
                    </div>
                    <div class="infoProduit">
                        <div>
                            <h2><?= htmlspecialchars($panier['nom'] ?? 'N/A') ?></h2>
                            <h4>En stock</h4>
                        </div>
                        <div class="quantiteProduit">
                            <!-- Bouton pour diminuer la quantité -->
                            <button class="minus" data-id="<?= htmlspecialchars($panier['idProduit'] ?? 'N/A') ?>" onclick="window.location.href='?addPanier=<?php echo $idProduit; ?>&qty=<?php echo -1; ?>'">
                            <img src="../../public/images/minusDarkBlue.svg" alt="Symbole moins">
                            </button>                            
                            <p class="quantite"><?= htmlspecialchars($quantite ?? 'N/A') ?></p>
                            <!-- Bouton pour augmenter la quantité -->
                            <button class="plus" data-id="<?= htmlspecialchars($panier['idProduit'] ?? 'N/A') ?>" onclick="window.location.href='?addPanier=<?php echo $idProduit; ?>&qty=<?php echo 1; ?>'">
                                <img src="../../public/images/plusDarkBlue.svg" alt="Symbole plus">
                            </button> 
                        </div>
                    </div>
                    <div class="prixOpt">
                        <p><?= number_format($panier['prix'] ?? 0, 2) ?> €</p>
                        <!-- Bouton pour supprimer complètement le produit du panier -->
                        <button class="delete" data-id="<?= htmlspecialchars($panier['idProduit'] ?? 'N/A') ?>" onclick="window.location.href='?addPanier=<?php echo $idProduit; ?>&qty=<?php echo 0; ?>'">
                        <img src="../../public/images/binDarkBlue.svg" alt="Enlever produit" class="delBtnImg">
                        </button>
                    </div>
                </article> 
            <?php // Affichage d'un message si le panier est vide
            } if ($nbProduit<=0) { ?>
                <h1 class="aucunProduit">Aucun produit</h1>
            <?php } else { ?>
        </section>
        <section class="recapPanier">
            <h1>Votre panier</h1>
            <div class="cardRecap">
                <article>
                    <h2><b>Récapitulatif de votre panier</b></h2>
                    <div class="infoCommande">

                    <?php
                    
                        // Calcul du prix total du panier (somme des prix × quantités)
                        $prixTotal = 0;
                        
                        // Parcours de tous les produits pour calculer le total
                        foreach($tabIDProduitPanier as $idProduit => $quantite) {
                            $stmt = $pdo->prepare("SELECT * FROM _produit WHERE idProduit = ?");
                            $stmt->execute([intval($idProduit)]);
                            $panier = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : false;
                            $prixTotal += $panier['prix'] * $quantite;
                        }

                    ?>

                        <section>
                            <h2>Nombres d'articles</h2>
                            <h2 class="val"><?= $nbProduit ?? 0 ?></h2>
                        </section>
                        <section>
                            <h2>Prix HT</h2>
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
                <!-- Redirection vers la page de connexion pour finaliser la commande -->
                <a href="../../views/frontoffice/connexionClient.php"><p>Passer la commande</p></a>
            </div>
            <!-- Formulaire pour vider complètement le panier -->
            <form method="GET" action="../../controllers/viderPanier.php">
                <button class="viderPanierCookie viderPanier" name="idUtilisateur">Vider le panier</button>
            </form>
        </section>
        <?php } ?>
        <?php require_once '../backoffice/partials/retourEnHaut.php' ?>
    </main>

    <?php include "../../views/frontoffice/partials/footerConnecte.php"; ?>

    <script src="../../public/amd-shim.js"></script>
    <script src="../../public/script.js"></script>
</body>
</html>
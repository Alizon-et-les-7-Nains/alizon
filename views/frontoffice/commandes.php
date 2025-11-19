<?php require_once "../../controllers/prix.php" ?>
<?php require_once "../../controllers/pdo.php" ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../public/style.css">
    <title>Alizon - Mes Commandes</title>
</head>
<body class="pageCommandes">
    <?php include '../../views/frontoffice/partials/headerConnecte.php'; ?>

    <main>
        <section class="topRecherche">
            <h1>Vos commandes</h1>
            <input class="supprElem" type="search" name="rechercheCommande" id="rechercheCommande" placeholder="Rechercher une commande">
        </section>
        
        <?php
        $commandesEnCours = [];
        
        $commandesLivrees2025 = [];
        
        $commandesLivrees2024 = [];
        
        // Récupérer le filtre sélectionné (par défaut "cours")
        $filtre = isset($_GET['filtre']) ? $_GET['filtre'] : 'cours';
        
        // Déterminer quelles commandes afficher
        if ($filtre === 'cours') {
            $commandesAffichees = $commandesEnCours;
            $messageVide = "Aucune commande actuellement en cours";
        } elseif ($filtre === '2025') {
            $commandesAffichees = $commandesLivrees2025;
            $messageVide = "Aucune commande effectuée en 2025";
        } else { // 2024
            $commandesAffichees = $commandesLivrees2024;
            $messageVide = "Aucune commande effectuée en 2024";
        }
        
        $nombreCommandes = count($commandesAffichees);
        ?>
        
        <section class="filtreRecherche">
            <p><?php echo $nombreCommandes; ?></p>
            <p>commande<?php echo $nombreCommandes > 1 ? 's' : ''; ?> en</p>
            <select name="typeFiltrage" id="typeFiltrage" onchange="window.location.href='?filtre=' + this.value">
                <option value="cours" <?php echo $filtre === 'cours' ? 'selected' : ''; ?>>cours</option>
                <option value="2025" <?php echo $filtre === '2025' ? 'selected' : ''; ?>>2025</option>
                <option value="2024" <?php echo $filtre === '2024' ? 'selected' : ''; ?>>2024</option>
            </select>
        </section>

        <?php if ($nombreCommandes === 0): ?>
            <section class="messageVide" style="text-align: center; padding: 60px 20px; font-size: 20px; color: #1e3a8a;">
                <p><?php echo $messageVide; ?></p>
            </section>
        <?php else: ?>
            <?php foreach ($commandesAffichees as $commande): ?>
                <section class="commande">
                    <?php 
                    $nombreProduits = count($commande['produits']);
                    foreach ($commande['produits'] as $index => $produit): 
                    ?>
                        <section class="produit <?php echo ($index === $nombreProduits - 1) ? 'dernierProduit' : ''; ?>">
                            <div class="containerImg">
                                <img src="<?php echo $produit['image']; ?>" alt="<?php echo $produit['nom']; ?>">
                                <div class="infoProduit">
                                    <h2><?php echo $produit['nom']; ?></h2>
                                    <ul>
                                        <li>Pommes sélectionnées issues de vergers traditionnels.</li>
                                        <li>Fermentation naturelle</li>
                                        <li>Pas d'arômes artificiels, ni de colorants....</li>
                                    </ul>
                                    <div class="statutCommande <?php echo $commande['statut'] === 'Livré' ? 'livre' : 'enCours'; ?>">
                                        <?php if ($commande['statut'] === 'Livré'): ?>
                                            <p>Livré le <?php echo $commande['dateLivraison']; ?></p>
                                            <a>Voir le suivi</a>
                                        <?php else: ?>
                                            <p><?php echo $commande['statut']; ?></p>
                                            <a>Suivre la commande <img src="../../../public/images/truckWhite.svg" alt="Icône de suivi de livraison"></a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <div class="listeBtn">
                                <a href="">Écrire un commentaire <img src="../../../public/images/penDarkBlue.svg" alt="Icône stylo pour écrire"></a>
                                <a href="">Acheter à nouveau <img src="../../../public/images/redoWhite.svg" alt="Icône renouveler l'achat"></a>
                                <?php if ($commande['statut'] === 'Livré'): ?>
                                    <a href="">Demander un retour<img src="../../../public/images/redoDarkBlue.svg" alt="Icône retour"></a>
                                <?php else: ?>
                                    <a href="">Annuler la commande <img src="../../../public/images/redoDarkBlue.svg" alt="Icône annuler"></a>
                                <?php endif; ?>
                            </div>
                        </section>
                    <?php endforeach; ?>
                    
                    <section class="footerCommande">
                        <div class="infoCommande">
                            <p class="supprElem">Commande effectuée le</p>
                            <p class="supprElem"><?php echo $commande['date']; ?></p>
                        </div>
                        <div class="infoCommande">
                            <p>Total</p>
                            <p>€<?php echo $commande['total']; ?></p>
                        </div>
                        <div class="infoCommande">
                            <p>N° de commande :</p>
                            <p><?php echo $commande['id']; ?></p>
                        </div>
                        <div class="liensCommande">
                            <a class="supprElem">Afficher des détails de commande</a>
                            <span class="supprElem">|</span>
                            <a href="">Facture</a>
                        </div>
                    </section>
                </section>
            <?php endforeach; ?>
        <?php endif; ?>
    </main>

</body>
</html>
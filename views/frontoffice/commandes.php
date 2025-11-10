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
        
        <section class="filtreRecherche">
            <p>2</p>
            <p>commandes en</p>
            <select name="typeFiltrage">
                <option value="cours">cours</option>
                <option value="2025">2025</option>
                <option value="2026">2026</option>
            </select>
        </section>

        <?php 
        // Première commande - En cours de préparation
        ?>
        <section class="commande">
            <?php for ($j = 0; $j < 2; $j++) { ?>
                <section class="produit <?php echo ($j === 1) ? 'dernierProduit' : ''; ?>">
                    <div class="containerImg">
                        <img src="../../../public/images/imageRillettes.png" alt="Bouteille de Cidre Coco d'Issé">
                        <div class="infoProduit">
                            <h2>Cidre Coco d'Issé</h2>
                            <ul>
                                <li>Pommes sélectionnée issues de vergers traditionnels.</li>
                                <li>Fermentation naturelle</li>
                                <li>Pas d'arômes artificiels, ni de colorants....</li>
                            </ul>
                            <div class="statutCommande enCours">
                                <p>En cours de préparation</p>
                                <a> Suivre la commande <img src="../../../public/images/truckWhite.svg" alt="Icône de suivi de livraison"></a>
                        </div>
                    </div>
                    </div>
                    <div class="listeBtn">
                        <a href="">Écrire un commentaire <img src="../../../public/images/penDarkBlue.svg" alt="Icône stylo pour écrire"></a>
                        <a href="">Acheter à nouveau <img src="../../../public/images/redoWhite.svg" alt="Icône renouveler l'achat"></a>
                        <a href="">Annuler la commande <img src="../../../public/images/redoDarkBlue.svg" alt="Icône annuler"></a>
                    </div>
                </section>
            <?php } ?>
            
            <section class="footerCommande">
                <div class="infoCommande">
                    <p class="supprElem">Commande effectuée le </p>
                    <p class="supprElem">12 décembre 2027</p>
                </div>
                <div class="infoCommande">
                    <p>Total </p>
                    <p>€0,00</p>
                </div>
                <div class="infoCommande">
                    <p>N° de commande : </p>
                    <p>D01-8711879-1493445</p>
                </div>
                <div class="liensCommande">
                    <a class="supprElem">Afficher des détails de commande</a>
                    <span class="supprElem">|</span>
                    <a href="">Facture</a>
                </div>
            </section>
        </section>

        <?php 
        // Deuxième commande - Livrée
        ?>
        <section class="commande">
            <?php for ($j = 0; $j < 2; $j++) { ?>
                <section class="produit <?php echo ($j === 1) ? 'dernierProduit' : ''; ?>">
                    <div class="containerImg">
                        <img src="../../../public/images/imageRillettes.png" alt="Bouteille de Cidre Coco d'Issé">
                        <div class="infoProduit">
                            <h2>Cidre Coco d'Issé</h2>
                            <ul>
                                <li>Pommes sélectionnée issues de vergers traditionnels.</li>
                                <li>Fermentation naturelle</li>
                                <li>Pas d'arômes artificiels, ni de colorants....</li>
                            </ul>
                            <div class="statutCommande livre">
                                <p>Livré le 01/01/2025</p>
                                <a>Voir le suivi</a>
                        </div>
                    </div>
                    </div>
                    <div class="listeBtn">
                        <a href="">Écrire un commentaire <img src="../../../public/images/penDarkBlue.svg" alt="Icône stylo pour écrire"></a>
                        <a href="">Acheter à nouveau <img src="../../../public/images/redoWhite.svg" alt="Icône renouveler l'achat"></a>
                        <a href="">Demander un retour<img src="../../../public/images/redoDarkBlue.svg" alt="Icône retour"></a>
                    </div>
                </section>
            <?php } ?>
            
            <section class="footerCommande">
                <div class="infoCommande">
                    <p class="supprElem">Commande effectuée le </p>
                    <p class="supprElem">25 décembre 2024</p>
                </div>
                <div class="infoCommande">
                    <p>Total </p>
                    <p>€0,00</p>
                </div>
                <div class="infoCommande">
                    <p>N° de commande : </p>
                    <p>D01-8711879-1493446</p>
                </div>
                <div class="liensCommande">
                    <a class="supprElem">Afficher des détails de commande</a>
                    <span class="supprElem">|</span>
                    <a href="">Facture</a>
                </div>
            </section>
        </section>
    </main>

</body>
</html>

<?php 

    require_once '../../controllers/pdo.php';
    require_once '../../controllers/auth.php';

    if(isset($_GET['error']) && isset($_GET['idProduit'])) {
        $idProduit = $_GET['idProduit'];
        $codeErreur = $_GET['error'];
        echo "<script>window.addEventListener('load', () => popUpErreur('$idProduit', $codeErreur));</script>";
    }

    //On récupère l'id du vendeur
    $idVendeur = $_SESSION['id'];

    //On récupère toutes les informations des produits en vente
    $stmt = $pdo->query("SELECT prod.idproduit, nom, note, prix, url, poids FROM _produit as prod JOIN _imageDeProduit as img on prod.idproduit = img.idproduit WHERE envente = true AND idVendeur = '$idVendeur';");
    $produitEnVente = $stmt->fetchAll(PDO::FETCH_ASSOC); 
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Alizon</title>

        <link rel="stylesheet" href="../../public/style.css">
        <link rel="icon" href="/public/images/logoBackoffice.svg">
    </head>

    <body class="backoffice">
        
        <?php require_once './partials/header.php' ?>

        <?php $currentPage = basename(__FILE__); require_once './partials/aside.php' ?>

        <main class="produitBackOffice">

            <h1>Produits en Vente</h1>
            <div class = "ligneProduit">

            <?php 
            //on parcourt les produits en vente
            for ($i = 0; $i < count($produitEnVente); $i++) { 
            $idProduit = $produitEnVente[$i]['idproduit'];
            
            $stmt = $pdo->query("SELECT count(prod.idproduit) as evaluation FROM saedb._produit as prod join saedb._avis on prod.idproduit = _avis.idproduit WHERE prod.idproduit = '$idProduit' and envente = true;");
            $evaluations = $stmt->fetchAll(PDO::FETCH_ASSOC); 

            //Pour verifier si le produit à une promotion
            $stmt = $pdo->query("SELECT * FROM saedb._promotion WHERE idproduit = '$idProduit';");
            $promo = $stmt->fetchAll(PDO::FETCH_ASSOC); 

            //Pour verifier si le produit à une remise
            $stmt = $pdo->query("SELECT * FROM saedb._remise WHERE idproduit = '$idProduit';");
            $remise = $stmt->fetchAll(PDO::FETCH_ASSOC); 
            
            // Récupérer les remises actives
            $remiseActiveSTMT = $pdo->prepare("SELECT tauxRemise FROM _remise WHERE idProduit = ? AND CURDATE() BETWEEN debutRemise AND finRemise");
            $remiseActiveSTMT->execute([$idProduit]);
            $remiseActive = $remiseActiveSTMT->fetch(PDO::FETCH_ASSOC);
            
            $prixOriginal = $produitEnVente[$i]['prix'];
            $tauxRemise = $remiseActive['tauxRemise'] ?? 0;
            $enRemise = !empty($remiseActive) && $tauxRemise > 0;
            $prixRemise = $enRemise ? $prixOriginal * (1 - $tauxRemise/100) : $prixOriginal;
            ?>
                
            <section>
                <article>
                    <img class="produit" src="<?php echo $produitEnVente[$i]['url'];?>" alt="">

                    <div class="nomEtEvaluation">
                        <p><?php echo htmlspecialchars($produitEnVente[$i]['nom']); ?></p>

                        <div class="evaluation">
                            <div class="etoiles">
                                <img src="/public/images/etoile.svg" alt="">
                                <p><?php echo htmlspecialchars($produitEnVente[$i]['note']); ?></p>
                            </div>

                            <p><?php 
                                echo htmlspecialchars($evaluations[0]['evaluation']) . " évaluations";
                            ?></p>                                
                            </div>
                        </div>

                        <div class="prixEtPrixAuKg">
                            <?php if ($enRemise): ?>
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <p class="prix"><?php echo number_format($prixRemise,2,','); ?>€</p>
                                    <p class="prix" style="text-decoration: line-through; color: #999; font-size: 0.9em;"><?php echo number_format($prixOriginal,2,','); ?>€</p>
                                </div>
                            <?php else: ?>
                                <p class="prix"><?php echo number_format($prixOriginal,2,','); ?>€</p>
                            <?php endif; ?>
                            <?php 
                                $prixAffichage = $enRemise ? $prixRemise : $prixOriginal;
                                $poids = $produitEnVente[$i]['poids'];
                                $prixAuKg = $poids > 0 ? $prixAffichage/$poids : 0;
                                $prixAuKg = round($prixAuKg,2) ?>
                            <p class = "prixAuKg"><?php echo htmlspecialchars($prixAuKg); ?>€ / kg</p>
                        </div>

                        <div class="bouton">
                            <img src="/public/images/iconeFiltre.svg" alt="">
                            <button>Options</button>

                            <div class="hoverBouton">

                                <div class="iconeTexteLigne">
                                    <div class="iconeTexte">
                                        <img src="/public/images/iconePromouvoir.svg" alt="">
                                        <?php $idProd = $produitEnVente[$i]['idproduit'] ?>
                                        <?php $nom = $produitEnVente[$i]['nom'] ?>
                                        <?php $nbEval = $evaluations[0]['evaluation'] ?>
                                        <?php if(count($promo) == 1) { 
                                                $dateRaw = new DateTime($promo[0]['finPromotion']);
                                                $dateFinPromo = $dateRaw->format('d/m/Y'); 
                     
                                                $cheminSysteme = "/var/www/html/images/baniere/" . $idProd . ".jpg";
                                                if (file_exists($cheminSysteme)) {
                                                    $image = "/images/baniere/" . $idProd . ".jpg";
                                                } else {
                                                    $stmtImg = $pdo->prepare("SELECT URL FROM _imageDeProduit WHERE idProduit = :idProduit");
                                                    $stmtImg->execute([':idProduit' => $idProd]);
                                                    $imageResult = $stmtImg->fetch(PDO::FETCH_ASSOC);
                                                    $image = !empty($imageResult) ? $imageResult['URL'] : '../../public/images/defaultImageProduit.png';
                                                }

                                        ?>
                                            <!-- ca ouvre la popup de modification de promotion -->
                                            <button onclick="popUpModifierPromotion(
                                                <?php echo $idProd; ?>, 
                                                '<?php echo htmlspecialchars(addslashes($nom), ENT_QUOTES); ?>', 
                                                '<?php echo $produitEnVente[$i]['url']; ?>', 
                                                <?php echo htmlspecialchars(addslashes(number_format($prixRemise)), ENT_QUOTES); ?>, 
                                                <?php echo htmlspecialchars($nbEval) ?>, 
                                                <?php echo htmlspecialchars($produitEnVente[$i]['note']) ?>, 
                                                <?php echo $prixAuKg?>, 
                                                '<?php echo $dateFinPromo?>',
                                                '<?php echo $image ?>'
                                            )">
                                                Modifier
                                            </button>
                                        <?php } else { ?>
                                            <!-- ca ouvre la popup de promotion -->
                                            <button onclick="popUpPromouvoir(<?php echo $idProd; ?>, '<?php echo htmlspecialchars(addslashes($nom), ENT_QUOTES); ?>', '<?php echo $produitEnVente[$i]['url']; ?>', <?php echo htmlspecialchars(addslashes($prixOriginal), ENT_QUOTES); ?>, <?php echo htmlspecialchars($nbEval) ?>, <?php echo htmlspecialchars($produitEnVente[$i]['note']) ?>, <?php echo $prixAuKg?>)">
                                                Promouvoir
                                            </button>
                                        <?php } ?>
                                    </div>
                                    <div class="ligne"></div>
                                </div>

                                <div class="iconeTexteLigne">
                                    <div class="iconeTexte">
                                        <img src="/public/images/iconeRemise.svg" alt="">
                                        <?php 
                                            //Si il y a une remise alors on ouvre la popup de Modification d'une remise sinon on ouvre la pop up de création d'une remise
                                            if(count($remise) == 1) { ?>
                                            <button onclick="popUpModifierRemise(<?php echo $idProd; ?>, '<?php echo htmlspecialchars(addslashes($nom), ENT_QUOTES); ?>', '<?php echo $produitEnVente[$i]['url']; ?>', <?php echo htmlspecialchars(addslashes($prixRemise), ENT_QUOTES); ?>, <?php echo htmlspecialchars($nbEval) ?>, <?php echo htmlspecialchars($produitEnVente[$i]['note']) ?>, <?php echo $prixAuKg?>, true)">
                                                Modifier remise
                                            </button>
                                        <?php } else { ?>
                                            <button onclick="popUpRemise(<?php echo $idProd; ?>, '<?php echo htmlspecialchars(addslashes($nom), ENT_QUOTES); ?>', '<?php echo $produitEnVente[$i]['url']; ?>', <?php echo htmlspecialchars(addslashes($prixOriginal), ENT_QUOTES); ?>, <?php echo htmlspecialchars($nbEval) ?>, <?php echo htmlspecialchars($produitEnVente[$i]['note']) ?>, <?php echo $prixAuKg?>, false)">
                                                Remise
                                            </button>
                                        <?php } ?>                                    
                                    </div>
                                    <div class="ligne"></div>
                                </div>

                                <div class="iconeTexteLigne">
                                    <div class="iconeTexte">
                                        <img src="/public/images/iconeModifier.svg" alt="">
                                        <a href= <?php echo "modifierProduit.php?id=".$idProd?>><button>Modifier</button></a>
                                    </div>
                                    <div class="ligne"></div>
                                </div>

                                <div class="iconeTexteLigne">
                                    <div class="iconeTexte">
                                        <img src="/public/images/iconePrevisualiser.svg" alt="">
                                        <a href=<?php echo "previsualiser.php?id=". $idProduit?>><button>Prévisualiser</button></a> 
                                    </div>
                                    <div class="ligne"></div>
                                </div>
                                
                                <form method="POST" action="../../controllers/RetirerDeLaVente.php">
                                    <div>
                                        <input type="hidden" name="idproduit" value="<?php echo $produitEnVente[$i]['idproduit']; ?>">
                                        <img src="/public/images/iconeRetirerVente.svg" alt="">
                                        <button>Retirer de la vente</button>
                                    </div>  
                                </form>


                            </div>
                        </div>

                    </article>
                </section>
            <?php } ?>
            </div>
            <?php 
                require_once '../../controllers/pdo.php';
                //On récupère les infos des produits hors vente
                $stmt = $pdo->query("SELECT prod.idproduit, nom, note, prix, url, poids FROM _produit as prod JOIN _imageDeProduit as img on prod.idproduit = img.idproduit WHERE envente = false AND idVendeur =  '$idVendeur';");
                $produitHorsVente = $stmt->fetchAll(PDO::FETCH_ASSOC); 
            ?>

            <h1>Produits hors Vente</h1>
            
            <div class = "ligneProduit">
            <?php for ($i = 0; $i < count($produitHorsVente); $i++) { 
                $idProduit = $produitHorsVente[$i]['idproduit'];
                
                $stmt = $pdo->query("SELECT count(prod.idproduit) as evaluation FROM saedb._produit as prod join saedb._avis on prod.idproduit = _avis.idproduit WHERE prod.idproduit = '$idProduit' and envente = false;");
                $evaluations = $stmt->fetchAll(PDO::FETCH_ASSOC); 
                
                // Récupérer les remises actives pour les produits hors vente aussi
                $remiseActiveSTMT = $pdo->prepare("SELECT tauxRemise FROM _remise WHERE idProduit = ? AND CURDATE() BETWEEN debutRemise AND finRemise");
                $remiseActiveSTMT->execute([$idProduit]);
                $remiseActive = $remiseActiveSTMT->fetch(PDO::FETCH_ASSOC);
                
                $prixOriginal = $produitHorsVente[$i]['prix'];
                $tauxRemise = $remiseActive['tauxRemise'] ?? 0;
                $enRemise = !empty($remiseActive) && $tauxRemise > 0;
                $prixRemise = $enRemise ? $prixOriginal * (1 - $tauxRemise/100) : $prixOriginal;
            ?>
                
            <section>
                <article>
                    <img class="produit" src="<?php echo $produitHorsVente[$i]['url'];?>" alt="">

                    <div class="nomEtEvaluation">
                        <p><?php echo htmlspecialchars($produitHorsVente[$i]['nom']); ?></p>

                        <div class="evaluation">
                            <div class="etoiles">
                                <img src="/public/images/etoile.svg" alt="">
                                <p><?php echo htmlspecialchars($produitHorsVente[$i]['note']); ?></p>
                            </div>

                            <p><?php 
                                    echo htmlspecialchars($evaluations[0]['evaluation']) . " évaluations";
                            ?></p>
                            </div>
                        </div>

                        <div class="prixEtPrixAuKg">
                            <?php if ($enRemise): ?>
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <p class="prix"><?php echo htmlspecialchars($prixRemise); ?>€</p>
                                    <p class="prix" style="text-decoration: line-through; color: #999; font-size: 0.9em;"><?php echo number_format($prixOriginal,2,','); ?>€</p>
                                </div>
                            <?php else: ?>
                                <p class="prix"><?php echo number_format($prixOriginal,2,','); ?>€</p>
                            <?php endif; ?>
                            <?php 
                                $prixAffichage = $enRemise ? $prixRemise : $prixOriginal;
                                $poids = $produitHorsVente[$i]['poids'];
                                $prixAuKg = $poids > 0 ? $prixAffichage/$poids : 0;
                                $prixAuKg = round($prixAuKg,2) ?>
                            <p class = "prixAuKg"><?php echo htmlspecialchars($prixAuKg); ?>€ / kg</p>
                        </div>

                        <div class="bouton">
                            <img src="/public/images/iconeFiltre.svg" alt="">
                            <button>Options</button>

                            <div class="hoverBouton">
                                <div class="iconeTexteLigne">
                                    <div class="iconeTexte">
                                        <img src="/public/images/iconeModifier.svg" alt="">
                                        <a href= <?php echo "modifierProduit.php?id=".$idProduit?>><button>Modifier</button></a>
                                    </div>
                                    <div class="ligne"></div>
                                </div>

                                <div class="iconeTexteLigne">
                                    <div class="iconeTexte">
                                        <img src="/public/images/iconePrevisualiser.svg" alt="">
                                        <a href=<?php echo "previsualiser.php?id=". $idProduit?>><button>Prévisualiser</button></a> 
                                    </div>
                                    <div class="ligne"></div>
                                </div>

                                <form method="POST" action="../../controllers/mettreEnVente.php">
                                    <div>
                                        <input type="hidden" name="idproduit" value="<?php echo $produitHorsVente[$i]['idproduit']; ?>">
                                        <img src="/public/images/iconeAjouterVente.svg" alt="">
                                        <button type="submit">Ajouter à la vente</button>
                                    </div>
                                </form>


                            </div>
                        </div>

                    </article>
                </section>
            <?php } ?>
            </div>

            <?php require_once './partials/retourEnHaut.php' ?>
        </main>

        <?php require_once './partials/footer.php' ?>

        <script src="../scripts/backoffice/scriptProduit.js"></script>
        <script src="/public/script.js"></script>
    </body>
</html>
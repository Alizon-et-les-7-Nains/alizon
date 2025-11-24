<?php 
    require_once '../../controllers/pdo.php';

    if(isset($_GET['error']) && isset($_GET['id'])) {
        $idProduit = $_GET['id'];
        $codeErreur = $_GET['error'];
        echo "<script>window.addEventListener('load', () => popUpErreur('$idProduit', $codeErreur));</script>";
    }

    $stmt = $pdo->query("SELECT prod.idproduit, nom, note, prix, url FROM _produit as prod JOIN _imageDeProduit as img on prod.idproduit = img.idproduit WHERE envente = true;");
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

            <?php for ($i = 0; $i < count($produitEnVente); $i++) { 
            $idProduit = $produitEnVente[$i]['idproduit'];
            
            $stmt = $pdo->query("SELECT count(prod.idproduit) as evaluation FROM saedb._produit as prod join saedb._avis on prod.idproduit = _avis.idproduit WHERE prod.idproduit = '$idProduit' and envente = true;");
            $evaluations = $stmt->fetchAll(PDO::FETCH_ASSOC); 
            ?>
                
            <section>
                <article>
                    <img class="produit" src="/public/<?php echo $produitEnVente[$i]['url'];?>" alt="">

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
                            <p class="prix"><?php echo htmlspecialchars($produitEnVente[$i]['prix']); ?>€</p>
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
                                        <button onclick="popUpPromouvoir(<?php echo $idProd; ?>, '<?php echo htmlspecialchars(addslashes($nom), ENT_QUOTES); ?>', '/public/<?php echo $produitEnVente[$i]['url']; ?>', <?php echo htmlspecialchars(addslashes($produitEnVente[$i]['prix']), ENT_QUOTES); ?>, <?php echo htmlspecialchars($nbEval) ?>, <?php echo htmlspecialchars($produitEnVente[$i]['note']) ?>)">
                                            Promouvoir
                                        </button>
                                    </div>
                                    <div class="ligne"></div>
                                </div>

                                <div class="iconeTexteLigne">
                                    <div class="iconeTexte">
                                        <img src="/public/images/iconeRemise.svg" alt="">
                                        <button onclick="popUpRemise(<?php echo $idProd; ?>, '<?php echo htmlspecialchars(addslashes($nom), ENT_QUOTES); ?>', '/public/<?php echo $produitEnVente[$i]['url']; ?>', <?php echo htmlspecialchars(addslashes($produitEnVente[$i]['prix']), ENT_QUOTES); ?>, <?php echo htmlspecialchars($nbEval) ?>, <?php echo htmlspecialchars($produitEnVente[$i]['note']) ?>)">Remise</button>
                                    </div>
                                    <div class="ligne"></div>
                                </div>

                                <div class="iconeTexteLigne">
                                    <div class="iconeTexte">
                                        <img src="/public/images/iconeModifier.svg" alt="">
                                        <button>Modifier</button>
                                    </div>
                                    <div class="ligne"></div>
                                </div>

                                <div class="iconeTexteLigne">
                                    <div class="iconeTexte">
                                        <img src="/public/images/iconePrevisualiser.svg" alt="">
                                        <button>Prévisualiser</button>
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
                $stmt = $pdo->query("SELECT prod.idproduit, nom, note, prix, url FROM _produit as prod JOIN _imageDeProduit as img on prod.idproduit = img.idproduit WHERE envente = false;");
                $produitHorsVente = $stmt->fetchAll(PDO::FETCH_ASSOC); 
            ?>

            <h1>Produits hors Vente</h1>
            
            <div class = "ligneProduit">
            <?php for ($i = 0; $i < count($produitHorsVente); $i++) { 
                $idProduit = $produitHorsVente[$i]['idproduit'];
                
                $stmt = $pdo->query("SELECT count(prod.idproduit) as evaluation FROM saedb._produit as prod join saedb._avis on prod.idproduit = _avis.idproduit WHERE prod.idproduit = '$idProduit' and envente = false;");
                $evaluations = $stmt->fetchAll(PDO::FETCH_ASSOC); 
            ?>
                
            <section>
                <article>
                    <img class="produit" src="/public/<?php echo $produitHorsVente[$i]['url'];?>" alt="">

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
                            <p class="prix"><?php echo htmlspecialchars($produitHorsVente[$i]['prix']); ?>€</p>
                        </div>

                        <div class="bouton">
                            <img src="/public/images/iconeFiltre.svg" alt="">
                            <button>Options</button>

                            <div class="hoverBouton">
                                <div class="iconeTexteLigne">
                                    <div class="iconeTexte">
                                        <img src="/public/images/iconeModifier.svg" alt="">
                                        <button>Modifier</button>
                                    </div>
                                    <div class="ligne"></div>
                                </div>

                                <div class="iconeTexteLigne">
                                    <div class="iconeTexte">
                                        <img src="/public/images/iconePrevisualiser.svg" alt="">
                                        <button>Prévisualiser</button>
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
        </main>

        <?php require_once './partials/footer.php' ?>

        <script src="../scripts/backoffice/scriptProduit.js"></script>
        <script src="/public/script.js"></script>
    </body>
</html>
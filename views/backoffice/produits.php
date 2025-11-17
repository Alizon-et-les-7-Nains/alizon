<?php //require_once '/var/www/controllers/pdo.php' ;
    // $stmt = $pdo->query("SELECT * FROM _client");
    // $clients = $stmt->fetchAll(PDO::FETCH_ASSOC); 
    // print_r ($clients);
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

        <?php require_once './partials/aside.php' ?>

        <main class="produitBackOffice">
            <h1>Produits en Vente</h1>
            <div class = "ligneProduit">
                <section>
                    <article>
                        <img class = "produit" src="/public/images/rillettes.png" alt="">
                        <div class="nomEtEvaluation">
                            <p>Rillettes</p>
                            <div class="evaluation">
                                <div class="etoiles">
                                    <img src="/public/images/etoile.svg"" alt="">
                                    <p>3</p>
                                </div>
                                <p>200 évaluations</p>
                            </div>
                        </div>
                        <div>
                            <p class="prix"> 29.99 €</p>
                            <p class="prixAuKg"> 99.72€ / kg</p>
                        </div>
                        <div class = "bouton">
                            <img src="/public/images/iconeFiltre.svg" alt="">
                            <button> Options</button>

                            <div class = "hoverBouton">
                                <div class="iconeTexteLigne">
                                    <div class = "iconeTexte">
                                        <img src="/public/images/iconePromouvoir.svg" alt="">
                                        <button onclick="popUpPromouvoir()">Promouvoir</button>
                                    </div>
                                    <div class="ligne"></div>
                                </div>
                                <div class="iconeTexteLigne">
                                    <div class = "iconeTexte">
                                        <img src="/public/images/iconeRemise.svg" alt="">
                                        <button onclick="popUpRemise()" >Remise</button>
                                    </div>
                                    <div class="ligne"></div>
                                </div>
                                <div class="iconeTexteLigne">
                                    <div class = "iconeTexte">
                                        <img src="/public/images/iconeModifier.svg" alt="">
                                        <button>Modifier</button>
                                    </div>
                                    <div class="ligne"></div>
                                </div>
                                <div class="iconeTexteLigne">
                                    <div class = "iconeTexte">
                                    <img src="/public/images/iconePrevisualiser.svg" alt="">
                                        <button>Prévisualiser</button>
                                    </div>
                                    <div class="ligne"></div>
                                </div>
                                <div>
                                    <img src="/public/images/iconeRetirerVente.svg" alt="">
                                    <button>Retirer de la vente</button>
                                </div>
                            </div>
                        </div>

                    </article>
                </section>

                <section>
                    <article>
                        <img class = "produit" src="/public/images/kouign_amann.jpg" alt="">
                        <div class="nomEtEvaluation">
                            <p>Rillettes</p>
                            <div class="evaluation">
                                <div class="etoiles">
                                    <img src="/public/images/etoile.svg"" alt="">
                                    <p>3</p>
                                </div>
                                <p>200 évaluations</p>
                            </div>
                        </div>
                        <div>
                            <p class="prix"> 29.99 €</p>
                            <p class="prixAuKg"> 99.72€ / kg</p>
                        </div>
                        <div class = "bouton">
                            <img src="/public/images/iconeFiltre.svg" alt="">
                            <button> Options</button>

                            <div class = "hoverBouton">
                                <div class="iconeTexteLigne">
                                    <div class = "iconeTexte">
                                        <img src="/public/images/iconePromouvoir.svg" alt="">
                                        <button onclick="popUpPromouvoir()">Promouvoir</button>
                                    </div>
                                    <div class="ligne"></div>
                                </div>
                                <div class="iconeTexteLigne">
                                    <div class = "iconeTexte">
                                        <img src="/public/images/iconeRemise.svg" alt="">
                                        <button onclick="popUpRemise()" >Remise</button>
                                    </div>
                                    <div class="ligne"></div>
                                </div>
                                <div class="iconeTexteLigne">
                                    <div class = "iconeTexte">
                                        <img src="/public/images/iconeModifier.svg" alt="">
                                        <button>Modifier</button>
                                    </div>
                                    <div class="ligne"></div>
                                </div>
                                <div class="iconeTexteLigne">
                                    <div class = "iconeTexte">
                                    <img src="/public/images/iconePrevisualiser.svg" alt="">
                                        <button>Prévisualiser</button>
                                    </div>
                                    <div class="ligne"></div>
                                </div>
                                <div>
                                    <img src="/public/images/iconeRetirerVente.svg" alt="">
                                    <button>Retirer de la vente</button>
                                </div>
                            </div>
                        </div>

                    </article>
                </section>

                <section>
                    <article>
                        <img class = "produit" src="/public/images/saucisson_chouchen.jpg" alt="">
                        <div class="nomEtEvaluation">
                            <p>Rillettes</p>
                            <div class="evaluation">
                                <div class="etoiles">
                                    <img src="/public/images/etoile.svg"" alt="">
                                    <p>3</p>
                                </div>
                                <p>200 évaluations</p>
                            </div>
                        </div>
                        <div>
                            <p class="prix"> 29.99 €</p>
                            <p class="prixAuKg"> 99.72€ / kg</p>
                        </div>
                        <div class = "bouton">
                            <img src="/public/images/iconeFiltre.svg" alt="">
                            <button> Options</button>

                            <div class = "hoverBouton">
                                <div class="iconeTexteLigne">
                                    <div class = "iconeTexte">
                                        <img src="/public/images/iconePromouvoir.svg" alt="">
                                        <button onclick="popUpPromouvoir()">Promouvoir</button>
                                    </div>
                                    <div class="ligne"></div>
                                </div>
                                <div class="iconeTexteLigne">
                                    <div class = "iconeTexte">
                                        <img src="/public/images/iconeRemise.svg" alt="">
                                        <button onclick="popUpRemise()" >Remise</button>
                                    </div>
                                    <div class="ligne"></div>
                                </div>
                                <div class="iconeTexteLigne">
                                    <div class = "iconeTexte">
                                        <img src="/public/images/iconeModifier.svg" alt="">
                                        <button>Modifier</button>
                                    </div>
                                    <div class="ligne"></div>
                                </div>
                                <div class="iconeTexteLigne">
                                    <div class = "iconeTexte">
                                    <img src="/public/images/iconePrevisualiser.svg" alt="">
                                        <button>Prévisualiser</button>
                                    </div>
                                    <div class="ligne"></div>
                                </div>
                                <div>
                                    <img src="/public/images/iconeRetirerVente.svg" alt="">
                                    <button>Retirer de la vente</button>
                                </div>
                            </div>
                        </div>

                    </article>
                </section>

                <section>
                    <article>
                        <img class = "produit" src="/public/images/palets_bretons.jpg" alt="">
                        <div class="nomEtEvaluation">
                            <p>Rillettes</p>
                            <div class="evaluation">
                                <div class="etoiles">
                                    <img src="/public/images/etoile.svg"" alt="">
                                    <p>3</p>
                                </div>
                                <p>200 évaluations</p>
                            </div>
                        </div>
                        <div>
                            <p class="prix"> 29.99 €</p>
                            <p class="prixAuKg"> 99.72€ / kg</p>
                        </div>
                        <div class = "bouton">
                            <img src="/public/images/iconeFiltre.svg" alt="">
                            <button> Options</button>

                            <div class = "hoverBouton">
                                <div class="iconeTexteLigne">
                                    <div class = "iconeTexte">
                                        <img src="/public/images/iconePromouvoir.svg" alt="">
                                        <button onclick="popUpPromouvoir()">Promouvoir</button>
                                    </div>
                                    <div class="ligne"></div>
                                </div>
                                <div class="iconeTexteLigne">
                                    <div class = "iconeTexte">
                                        <img src="/public/images/iconeRemise.svg" alt="">
                                        <button onclick="popUpRemise()" >Remise</button>
                                    </div>
                                    <div class="ligne"></div>
                                </div>
                                <div class="iconeTexteLigne">
                                    <div class = "iconeTexte">
                                        <img src="/public/images/iconeModifier.svg" alt="">
                                        <button>Modifier</button>
                                    </div>
                                    <div class="ligne"></div>
                                </div>
                                <div class="iconeTexteLigne">
                                    <div class = "iconeTexte">
                                    <img src="/public/images/iconePrevisualiser.svg" alt="">
                                        <button>Prévisualiser</button>
                                    </div>
                                    <div class="ligne"></div>
                                </div>
                                <div>
                                    <img src="/public/images/iconeRetirerVente.svg" alt="">
                                    <button>Retirer de la vente</button>
                                </div>
                            </div>
                        </div>

                    </article>
                </section>
            </div>
        </main>

        <?php require_once './partials/footer.php' ?>

        <script src="../scripts/backoffice/popUpPromouvoir.js"></script>
    </body>
</html>
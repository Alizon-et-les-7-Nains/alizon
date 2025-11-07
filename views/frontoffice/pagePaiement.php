<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="../../public/style.css">
    <title>Paiement - Alizon</title>
</head>

<body class="pagePaiement">
    <?php include '../../views/frontoffice/partials/headerConnecte.php'; ?>

    <main class="container">
        <div class="parent">
            <div class="col">
                <section class="delivery">
                    <h3>1 - Informations pour la livraison :</h3>
                    <input type="text" placeholder="Adresse de livraison" aria-label="Adresse de livraison">
                    <div class="ligne">
                        <input class="code-postal-input" type="text" placeholder="Code postal" aria-label="Code postal"
                            required minlenght="5" maxlength="5"> <input type="text" placeholder="Ville"
                            aria-label="Ville">
                    </div>
                    <label><input type="checkbox"> Adresse de facturation différente</label>
                </section>

                <section class="payment">
                    <h3>2 - Informations de paiement :</h3>
                    <input type="text" placeholder="Numéro sur la carte" aria-label="Numéro sur la carte">
                    <input type="text" placeholder="Nom sur la carte" aria-label="Nom sur la carte">
                    <div class="ligne">
                        <input class="carte-date" type="text" placeholder="00/00" aria-label="Date expiration">
                        <input class="cvv-input" type=" text" placeholder="CVV" aria-label="CVV" required minlenght="3"
                            maxlength="3">
                    </div>

                    <div class="logos">
                        <img src="https://upload.wikimedia.org/wikipedia/commons/5/5e/Visa_Inc._logo.svg" alt="Visa">
                    </div>

                    <button class="payer">Payer</button>
                </section>
            </div>


            <div class="col">
                <!-- <section class="promotions">
                    <h3>4 - Appliquer un bon de réduction</h3>
                    <input type="text" placeholder="Code de réduction" aria-label="Code de réduction">
                </section> -->
                <section class="conditions">
                    <h3>5 - Accepter les conditions générales et mentions légales</h3>
                    <label>
                        <input type="checkbox">
                        J’ai lu et j’accepte les
                        <a href="#">Conditions Générales de Vente</a> et les
                        <a href="#">Mentions Légales</a> d’Alizon.
                    </label>
                </section>
            </div>

            <aside class="col recap">
                <div class="produit">
                    <img src="../../public/images/rillettes.png" alt="">
                    <div class="infos">
                        <p class="titre">Lot de rillettes bretonne</p>
                        <p class="prix">29<sup>99</sup>€</p>
                        <div class="gestQte">
                            <div class="qte">
                                <button>-</button>
                                <span>1</span>
                                <button>+</button>
                            </div>
                            <button class="delete">
                                <img src="../../public/images/bin.svg" alt="">
                            </button>
                        </div>
                    </div>
                </div>
            </aside>
        </div>
        <!-- bouton mobile placé après tous les blocs, visible seulement en mobile -->
        <div class="payer-wrapper-mobile">
            <button class="payer payer--mobile">Payer</button>
        </div>
    </main>

    <?php include '../../views/frontoffice/partials/footerConnecte.php'; ?>
    <script src="../../public/script.js"></script>
</body>

</html>
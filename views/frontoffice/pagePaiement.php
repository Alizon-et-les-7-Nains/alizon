<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../public/style.css">
    <title>Paiement de votre panier</title>
</head>

<body class="pagePaiement">
    <?php include '../../views/frontoffice/partials/headerConnecte.php'; ?>

    <main>

        <div class="parent">
            <div class="col col-1">
                <div class="infosLivraison">
                    <h3>1 - Informations pour la livraison :</h3>

                    <form class="form-livraison" autocomplete="on" novalidate>
                        <input id="adresse" name="adresse" type="text" placeholder="Adresse de livraison"
                            class="input input--large">

                        <div class="row">
                            <input id="codepostal" name="codepostal" type="text" placeholder="Code postal"
                                class="input input--half">

                            <input id="ville" name="ville" type="text" placeholder="Ville" class="input input--half">
                        </div>

                        <label class="checkbox">
                            <input type="checkbox" id="facturation" name="facturation">
                            <span>Adresse de facturation différente</span>
                        </label>
                    </form>
                </div>

                <div class="paiement">
                    <h3>2 - Informations de paiement :</h3>

                    <form class="form-paiement" action="">
                        <input id="numcarte" name="numcarte" type="text" placeholder="Numéro de carte bancaire"
                            class="input input--large">

                        <input id="nomcarte" name="nomcarte" type="text" placeholder="Nom sur la carte bancaire"
                            class="input input--large">

                        <div class="row">
                            <input id="dateexp" name="dateexp" type="text" placeholder="Date d'expiration (MM/AA)"
                                class="input input--half">

                            <input id="cvv" name="cvv" type="number" placeholder="CVV" class="input input--half cvv">
                        </div>

                    </form>
                </div>
            </div>

            <!-- Colonne 2 : conditions générales -->
            <div class="col col-2">
                <div class="conditionGen">
                    <h3>5 - Accepter les conditions générales et mentions légales</h3>

                    <label class="checkbox">
                        <input type="checkbox" id="cgv" name="cgv">
                        <span>J’ai lu et j’accepte les Conditions Générales de Vente et les Mentions Légales
                            d’Alizon.</span>
                    </label>
                </div>
            </div>

            <!-- Colonne 3 : récapitulatif du panier -->
            <div class="col col-3">
                <div class="recapPanier">
                    <!-- contenu du récapitulatif -->
                </div>
            </div>
        </div>

    </main>

    <?php include '../../views/frontoffice/partials/footerConnecte.php'; ?>

</body>

</html>
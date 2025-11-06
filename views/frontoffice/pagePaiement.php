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
            <div class="infosLivraison">
                <h3>1 - Informations pour la livraison : </h3>

                <form class="form-livraison" autocomplete="on" novalidate>
                    <label class="sr-only" for="adresse">Adresse de livraison</label>
                    <input id="adresse" name="adresse" type="text" placeholder="Adresse de livraison"
                        class="input input--large">

                    <div class="row">
                        <label class="sr-only" for="codepostal">Code postal</label>
                        <input id="codepostal" name="codepostal" type="text" placeholder="Code postal"
                            class="input input--half">

                        <label class="sr-only" for="ville">Ville</label>
                        <input id="ville" name="ville" type="text" placeholder="Ville" class="input input--half">
                    </div>

                    <label class="checkbox">
                        <input type="checkbox" id="facturation" name="facturation">
                        <span>Adresse de facturation différente</span>
                    </label>
                </form>
            </div>
            <div class="recapPanier">

            </div>
            <div class="paiement">
                <h3>2 - Informations de paiement : </h3>
                <input type="text">
                <input type="text">

                <input type="text">
                <input type="text">
            </div>
            <div class="conditionGen">
                <h3>5 - Accepter les conditions génrales
                    et mentions légales</h3>

                <input type="checkbox" name="" id="">
            </div>
        </div>


    </main>

    <?php include '../../views/frontoffice/partials/footerConnecte.php'; ?>

</body>

</html>
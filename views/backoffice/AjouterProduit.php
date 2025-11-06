<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../public/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <title>Ajouter un produit au catalogue</title>
</head>
<body class="backoffice">
    <?php require_once "./partials/headerMain.php"?>
    <?php require_once "./partials/aside.php"?>
       
    <main class="AjouterProduit"> 
        <div class="ajouterPhoto">
            <img src="../../../public/images/ajouterPhoto.svg" alt="">
            <p>Cliquer pour ajouter une photo</p>
        </div>

        <div class="product-name">
            <input type="text" placeholder="Intitulé du produit" required>
        </div>

        <div class="spec-prod">
            <input type="text" placeholder="Prix" required>
            <input type="text" placeholder="Poids" required>
            <p>Prix au Kg : </p>
        </div>

        <div class="keywords">
            <input type="text" placeholder="Mots clés (séparés par des virgules)">
        </div>

        <div class="product-desc">
            <input type="text" placeholder="Description de votre produit">
        </div>

        <button type="submit" id="previsualiser-btn">Prévisualiser</button>
        <button type="submit" id="annuler-btn">Annuler</button>
        <button type="submit" id="ajt-btn">Ajouter le produit</button>
    </main>
    <?php require_once "./partials/footer.php"?>
</body>
</html>
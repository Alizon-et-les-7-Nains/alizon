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
    <header>
        <?php require_once "./partials/headerMain.php"?>
    </header>
    <?php require_once "./partials/aside.php"?>
       
    <main class="AjouterProduit"> 
        <h1>Backoffice - Nouveau produit</h1>
        <div class="product-content">
            
            <div class="left-section">
                <div class="ajouterPhoto">
                    <div class="placeholder-photo">
                        <img src="../../../public/images/ajouterPhoto.svg" alt="">
                        <p>Cliquer pour ajouter une photo</p>
                    </div>
                </div>

                <div class="form-details">
                    <input type="text" class="product-name-input" placeholder="Intitulé du produit" required>
                
                    <div class="price-weight-kg">
                        <input type="text" placeholder="Prix" required>
                        <input type="text" placeholder="Poids" required>
                        <span class="prix-kg-label">Prix au Kg:</span>
                    </div>

                    <input type="text" class="keywords-input" placeholder="Mots clés (séparés par des virgules)">
                </div>
            </div>

            <div class="right-section">
                <div class="product-desc-box">
                    <label for="product-description">Description du produit</label>
                    <textarea id="product-description" placeholder="Description de votre produit" maxlength="1000"></textarea>
                    <div class="char-count">230/1000</div> 
                </div>

                <div class="form-actions">
                    <button type="button" class="btn-previsualiser">Prévisualiser</button>
                    <button type="button" class="btn-annuler">Annuler</button>
                    <button type="submit" class="btn-ajouter">Ajouter le produit</button>
                </div>
            </div>
        </div>
    </main>
    <?php require_once "./partials/footer.php"?>
</body>
</html>
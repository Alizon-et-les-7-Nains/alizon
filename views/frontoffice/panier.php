<?php 
require_once "../../controllers/prix.php";
require_once "../../controllers/pdo.php";

// ID utilisateur connecté (à remplacer par la gestion de session)
$idClient = 2; 

function getCurrentCart($pdo, $idClient) {
    $stmt = $pdo->query("SELECT idPanier FROM _panier WHERE idClient = " . intval($idClient) . " ORDER BY idPanier DESC LIMIT 1");
    $panier = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : false;

    $cart = [];

    if ($panier) {
        $idPanier = intval($panier['idPanier']); 

        $sql = "SELECT p.idProduit, p.nom, p.prix, pa.quantiteProduit as qty, i.URL as img
                FROM _produitAuPanier pa
                JOIN _produit p ON pa.idProduit = p.idProduit
                LEFT JOIN _imageDeProduit i ON p.idProduit = i.idProduit
                WHERE pa.idPanier = " . intval($idPanier);
        $stmt = $pdo->query($sql);
        $cart = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
    }
    
    return $cart;
}

function updateQuantityInDatabase($pdo, $idClient, $idProduit, $delta) {
    $idProduit = intval($idProduit);
    $idClient = intval($idClient);

    $sql = "SELECT quantiteProduit FROM _produitAuPanier 
            WHERE idProduit = $idProduit AND idPanier IN (
                SELECT idPanier FROM _panier WHERE idClient = $idClient
            )";
    $stmt = $pdo->query($sql);
    $current = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : false;

    if ($current) {
        $newQty = max(0, intval($current['quantiteProduit']) + intval($delta));
        
        if ($newQty > 0) {
            $sql = "UPDATE _produitAuPanier SET quantiteProduit = $newQty 
                    WHERE idProduit = $idProduit AND idPanier IN (
                        SELECT idPanier FROM _panier WHERE idClient = $idClient
                    )";
            $res = $pdo->query($sql);
            $success = $res !== false;
        } else {
            $success = removeFromCartInDatabase($pdo, $idClient, $idProduit);
        }
        
        return $success;
    }
    return false;
}

function removeFromCartInDatabase($pdo, $idClient, $idProduit) {
    $idProduit = intval($idProduit);
    $idClient = intval($idClient);

    $sql = "DELETE FROM _produitAuPanier 
            WHERE idProduit = $idProduit AND idPanier IN (
                SELECT idPanier FROM _panier WHERE idClient = $idClient
            )";
    $res = $pdo->query($sql);
    return $res !== false;
}

// ============================================================================
// GESTION DES ACTIONS AJAX
// ============================================================================

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    try {
        switch ($_POST['action']) {
            case 'updateQty':
                $idProduit = $_POST['idProduit'] ?? '';
                $delta = intval($_POST['delta'] ?? 0);
                if ($idProduit && $delta != 0) {
                    $success = updateQuantityInDatabase($pdo, $idClient, $idProduit, $delta);
                    echo json_encode(['success' => $success]);
                } else {
                    echo json_encode(['success' => false, 'error' => 'Paramètres invalides']);
                }
                break;

            case 'removeItem':
                $idProduit = $_POST['idProduit'] ?? '';
                if ($idProduit) {
                    $success = removeFromCartInDatabase($pdo, $idClient, $idProduit);
                    echo json_encode(['success' => $success]);
                } else {
                    echo json_encode(['success' => false, 'error' => 'ID produit manquant']);
                }
                break;
            case 'getCart':
                $cart = getCurrentCart($pdo, $idClient);
                echo json_encode($cart);
                break;

            default:
                echo json_encode(['success' => false, 'error' => 'Action non reconnue']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

$cart = getCurrentCart($pdo, $idClient);

// ============================================================================
// AFFICHAGE DE LA PAGE
// ============================================================================
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="../../public/style.css">
  <title>Alizon - Panier</title>
</head>
<body class="panier">
    <?php include '../../views/frontoffice/partials/headerConnecte.php'; ?>

    <main>
        <section class="listeProduit">
            <?php foreach ($cart as $item) { 
                $nom = $item['nom'] ?? '';
                $image = $item['img'] ?? '../../public/images/default.png';
                $prix = $item['prix'] ?? 0;
                $qty = $item['qty'] ?? 0;
                ?> 
                <article>
                    <div class="imgProduit">
                        <img src="<?= htmlspecialchars($image) ?>" alt="Image de <?= htmlspecialchars($nom) ?>">
                    </div>
                    <div class="infoProduit">
                        <div>
                            <h2><?= htmlspecialchars($nom) ?></h2>
                            <h4>En stock</h4>
                        </div>
                        <div class="quantiteProduit">
                            <img class="minus" src="../../public/images/minusDarkBlue.svg" alt="Symbole moins" data-id="<?php echo htmlspecialchars($item['idProduit']) ?>" style="cursor: pointer;"> 
                            <p class="quantite"><?php echo intval($qty) ?></p> 
                            <img class="plus" src="../../public/images/plusDarkBlue.svg" alt="Symbole plus" data-id="<?= htmlspecialchars($item['idProduit']) ?>" style="cursor: pointer;"> 
                        </div>
                    </div>
                    <div class="prixOpt">
                        <h2><b><?= number_format($prix * $qty, 2, ',', '') ?>€</b></h2>
                        <img src="../../public/images/binDarkBlue.svg" alt="Enlever produit" class="delete" data-id="<?= htmlspecialchars($item['idProduit']) ?>" style="cursor: pointer;">
                    </div>
                </article>
            <?php } if (count($cart) === 0) { ?>
                <h1 class="aucunProduit">Aucun produit</h1>
            <?php } ?>
    </main>

    <?php include "../../views/frontoffice/partials/footerConnecte.php"; ?>

    <script>
        const plus = document.querySelectorAll('.plus');
        const minus = document.querySelectorAll('.minus');
        const btnPoubelle = document.querySelectorAll('.delete');

        plus.forEach(btn => {
            btn.addEventListener('click', function() {
                const quantiteElement = this.parentElement.querySelector('.quantite');
                let quantite = parseInt(quantiteElement.textContent);
                quantite++;
                quantiteElement.textContent = quantite;
            });
        });

        minus.forEach(btn => {
            btn.addEventListener('click', function() {
                const quantiteElement = this.parentElement.querySelector('.quantite');
                let quantite = parseInt(quantiteElement.textContent);
                if (quantite > 0) {
                    quantite--;
                    quantiteElement.textContent = quantite;
                }
            });
        });

        btnPoubelle.forEach(btn => {
            btn.addEventListener('click', function() {
                // Remonter à l'article parent puis chercher la quantité
                const article = this.closest('article');
                const quantiteElement = article.querySelector('.quantite');
                quantiteElement.textContent = '0';
            });
        });
    </script>
    <script src="../scripts/frontoffice/paiement-ajax.js"></script>
</body>
</html>
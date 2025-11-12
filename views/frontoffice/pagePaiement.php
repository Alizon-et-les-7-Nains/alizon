<?php
require_once "../../controllers/pdo.php";

// ID utilisateur connecté (à remplacer par la gestion de session)
$idClient = 1; 

$stmt = $pdo->query("SELECT idPanier FROM _panier WHERE idClient = '$idClient' ORDER BY idPanier DESC LIMIT 1");

$panier = $stmt->fetch(PDO::FETCH_ASSOC);

$cart = [];

if ($panier) {
    $idPanier = $panier['idPanier'];

    $stmt = $pdo->query("
        Select idProduit from _produitAuPanier WHERE idClient = '$idClient' and idPanier = '$idPanier';
    ");
    $produitAuPanier = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $stmt = $pdo->query("
        Select prix, stock from _produit WHERE idProduit = '$produitAuPanier[0]';
    ");
    $infoProd = $stmt->fetch(PDO::FETCH_ASSOC);
    $prix = $infoProd['prix'];
    
    $cart = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>


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

    <?php
    $csvPath = __DIR__ . '/../../public/data/departements.csv';
    $departments = [];
    $citiesByCode = [];
    $postals = [];
    if (file_exists($csvPath) && ($handle = fopen($csvPath, 'r')) !== false) {
    $header = fgetcsv($handle, 0, ';', '"', '\\');
    while (($row = fgetcsv($handle, 0, ';', '"', '\\')) !== false) {
  
            if (count($row) < 4) continue;
            $code = str_pad(trim($row[0]), 2, '0', STR_PAD_LEFT);
            $postal = trim($row[1]);
            $dept = trim($row[2]);
            $city = trim($row[3]);
            $departments[$code] = $dept;
            if (!isset($citiesByCode[$code])) $citiesByCode[$code] = [];
            if ($city !== '' && !in_array($city, $citiesByCode[$code])) $citiesByCode[$code][] = $city;
            if ($postal !== '') {
                if (!isset($postals[$postal])) $postals[$postal] = [];
                if (!in_array($city, $postals[$postal])) $postals[$postal][] = $city;
            }
        }
        fclose($handle);
    } else {
        $departments['22'] = "Côtes-d'Armor";
        $citiesByCode['22'] = ['Saint-Brieuc','Lannion','Dinan'];
    }

    $cart = [
        ['id' => 'rillettes', 'title' => 'Lot de rillettes bretonne', 'price' => 29.99, 'qty' => 1, 'img' => '../../public/images/rillettes.png'],
        ['id' => 'confiture', 'title' => 'Confiture artisanale', 'price' => 6.5, 'qty' => 2, 'img' => '../../public/images/jam.png'],
    ];
    ?>

    <script>
    window.__PAYMENT_DATA__ =
        <?php echo json_encode(['departments' => $departments, 'citiesByCode' => $citiesByCode, 'postals' => $postals, 'cart' => $cart], JSON_UNESCAPED_UNICODE); ?>;
    </script>

    <main class="container">
        <div class="parent">
            <div class="col">
                <section class="delivery">
                    <h3>1 - Informations pour la livraison :</h3>
                    <div class="input-field">
                        <input class="adresse-input" type="text" placeholder="Adresse de livraison"
                            aria-label="Adresse de livraison">
                    </div>
                    <div class="ligne">
                        <div class="input-field fixed-110">
                            <input class="code-postal-input" type="text" placeholder="Code département ou postal"
                                aria-label="Code postal">
                        </div>
                        <div class="input-field flex-1">
                            <input class="ville-input" type="text" placeholder="Ville" aria-label="Ville">
                        </div>
                    </div>
                    <label><input type="checkbox"> Adresse de facturation différente</label>
                </section>

                <section class="payment">
                    <h3>2 - Informations de paiement :</h3>
                    <div class="input-field">
                        <input class="num-carte" type="text" placeholder="Numéro sur la carte"
                            aria-label="Numéro sur la carte">
                    </div>
                    <div class="input-field">
                        <input class="nom-carte" type="text" placeholder="Nom sur la carte"
                            aria-label="Nom sur la carte">
                    </div>
                    <div class="ligne">
                        <div class="input-field fixed-100">
                            <input class="carte-date" type="text" placeholder="MM/AA" aria-label="Date expiration">
                        </div>
                        <div class="input-field fixed-80">
                            <input class="cvv-input" type="text" placeholder="CVV" aria-label="CVV" required
                                minlenght="3" maxlength="3">
                        </div>
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

            <aside class="col recap" id="recap">
                <?php foreach ($cart as $item): ?>
                <div class="produit" data-id="<?= htmlspecialchars($item['idProduit']) ?>">
                    <img src="<?= htmlspecialchars($item['img'] ?: '../../public/images/default.png') ?>" alt="">
                    <div class="infos">
                        <p class="titre"><?= htmlspecialchars($item['nom']) ?></p>
                        <p class="prix"><?= number_format($item['prix'], 2, ',', '') ?>€</p>
                        <div class="gestQte">
                            <div class="qte">
                                <button class="minus" data-id="<?= htmlspecialchars($item['idProduit']) ?>">-</button>
                                <span class="qty"
                                    data-id="<?= htmlspecialchars($item['idProduit']) ?>"><?= intval($item['quantiteProduit']) ?></span>
                                <button class="plus" data-id="<?= htmlspecialchars($item['idProduit']) ?>">+</button>
                            </div>
                            <button class="delete" data-id="<?= htmlspecialchars($item['idProduit']) ?>">
                                <img src="../../public/images/bin.svg" alt="">
                            </button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
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
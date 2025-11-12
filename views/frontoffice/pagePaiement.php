<?php
require_once "../../controllers/pdo.php";

// ID utilisateur connecté (à remplacer par la gestion de session)
$idClient = 1; 

$stmt = $pdo->prepare("SELECT idPanier FROM distribill_sae03._panier WHERE idClient = :idClient ORDER BY idPanier DESC LIMIT 1");
$stmt->execute(['idClient' => $idClient]);
$panier = $stmt->fetch(PDO::FETCH_ASSOC);

$cart = [];

if ($panier) {
    $idPanier = $panier['idPanier'];

    $stmt = $pdo->prepare("
        SELECT p.idProduit, p.nom, p.prix, pa.quantiteProduit as qty, i.URL as img
        FROM distribill_sae03._produitAuPanier pa
        JOIN distribill_sae03._produit p ON pa.idProduit = p.idProduit
        LEFT JOIN distribill_sae03._imageDeProduit i ON p.idProduit = i.idProduit
        WHERE pa.idPanier = :idPanier
    ");
    $stmt->execute(['idPanier' => $idPanier]);
    $cart = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// ============================================================================
// FONCTIONS POUR GÉRER LES ACTIONS AJAX
// ============================================================================

function updateQuantityInDatabase($pdo, $idClient, $idProduit, $delta) {
    // Récupérer la quantité actuelle
    $sql = "SELECT quantiteProduit FROM distribill_sae03._produitAuPanier 
            WHERE idProduit = ? AND idPanier IN (
                SELECT idPanier FROM distribill_sae03._panier WHERE idClient = ?
            )";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$idProduit, $idClient]);
    $current = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($current) {
        $newQty = max(0, $current['quantiteProduit'] + $delta);
        
        if ($newQty > 0) {
            // Mettre à jour la quantité
            $sql = "UPDATE distribill_sae03._produitAuPanier SET quantiteProduit = ? 
                    WHERE idProduit = ? AND idPanier IN (
                        SELECT idPanier FROM distribill_sae03._panier WHERE idClient = ?
                    )";
            $stmt = $pdo->prepare($sql);
            $success = $stmt->execute([$newQty, $idProduit, $idClient]);
        } else {
            // Supprimer le produit si quantité = 0
            $success = removeFromCartInDatabase($pdo, $idClient, $idProduit);
        }
        
        return $success;
    }
    return false;
}

function removeFromCartInDatabase($pdo, $idClient, $idProduit) {
    $sql = "DELETE FROM distribill_sae03._produitAuPanier 
            WHERE idProduit = ? AND idPanier IN (
                SELECT idPanier FROM distribill_sae03._panier WHERE idClient = ?
            )";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([$idProduit, $idClient]);
}

function createOrderInDatabase($pdo, $idClient, $adresseLivraison, $villeLivraison, $regionLivraison, $numeroCarte) {
    try {
        $pdo->beginTransaction();

        // Récupérer le panier actuel
        $sql = "SELECT * FROM distribill_sae03._panier WHERE idClient = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$idClient]);
        $panier = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$panier) {
            throw new Exception("Panier non trouvé");
        }

        // Calculer les totaux
        $sqlTotals = "SELECT SUM(p.prix * pap.quantiteProduit) as sousTotal, SUM(pap.quantiteProduit) as nbArticles 
                     FROM distribill_sae03._produitAuPanier pap
                     JOIN distribill_sae03._produit p ON pap.idProduit = p.idProduit
                     WHERE pap.idPanier = ?";
        $stmtTotals = $pdo->prepare($sqlTotals);
        $stmtTotals->execute([$panier['idPanier']]);
        $totals = $stmtTotals->fetch(PDO::FETCH_ASSOC);
        
        $sousTotal = $totals['sousTotal'] ?? 0;
        $nbArticles = $totals['nbArticles'] ?? 0;

        // Créer la commande
        $sql = "
            INSERT INTO distribill_sae03._commande 
            (dateCommande, etatLivraison, montantCommandeTTC, montantCommandeHt, 
             quantiteCommande, adresseLivr, villeLivr, regionLivr, numeroCarte, idPanier)
            VALUES (NOW(), 'En attente', ?, ?, ?, ?, ?, ?, ?, ?)
        ";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $sousTotal * 1.20, // TTC (20% TVA)
            $sousTotal, // HT
            $nbArticles,
            $adresseLivraison,
            $villeLivraison,
            $regionLivraison,
            $numeroCarte,
            $panier['idPanier']
        ]);

        $idCommande = $pdo->lastInsertId();

        // Copier les produits du panier vers la table contient
        $sql = "
            INSERT INTO distribill_sae03._contient (idProduit, idCommande, prixProduitHt, tauxTva, quantite)
            SELECT pap.idProduit, ?, p.prix, COALESCE(t.pourcentageTva, 20.0), pap.quantiteProduit
            FROM distribill_sae03._produitAuPanier pap
            JOIN distribill_sae03._produit p ON pap.idProduit = p.idProduit
            LEFT JOIN distribill_sae03._tva t ON p.typeTva = t.typeTva
            WHERE pap.idPanier = ?
        ";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$idCommande, $panier['idPanier']]);

        // Vider le panier après commande
        $sql = "DELETE FROM distribill_sae03._produitAuPanier WHERE idPanier = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$panier['idPanier']]);

        $pdo->commit();
        return $idCommande;

    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
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

            case 'createOrder':
                $adresseLivraison = $_POST['adresseLivraison'] ?? '';
                $villeLivraison = $_POST['villeLivraison'] ?? '';
                $regionLivraison = $_POST['regionLivraison'] ?? '';
                $numeroCarte = $_POST['numeroCarte'] ?? '';
                
                if ($adresseLivraison && $villeLivraison && $regionLivraison && $numeroCarte) {
                    $idCommande = createOrderInDatabase(
                        $pdo,
                        $idClient,
                        $adresseLivraison,
                        $villeLivraison,
                        $regionLivraison,
                        $numeroCarte
                    );
                    echo json_encode(['success' => true, 'idCommande' => $idCommande]);
                } else {
                    echo json_encode(['success' => false, 'error' => 'Données manquantes']);
                }
                break;

            default:
                echo json_encode(['success' => false, 'error' => 'Action non reconnue']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

// ============================================================================
// RÉCUPÉRATION DES DÉPARTEMENTS ET VILLES
// ============================================================================

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

    <script>
    window.__PAYMENT_DATA__ = {
        departments: <?php echo json_encode($departments, JSON_UNESCAPED_UNICODE); ?>,
        citiesByCode: <?php echo json_encode($citiesByCode, JSON_UNESCAPED_UNICODE); ?>,
        postals: <?php echo json_encode($postals, JSON_UNESCAPED_UNICODE); ?>,
        cart: <?php echo json_encode($cart, JSON_UNESCAPED_UNICODE); ?>,
        idClient: <?php echo $idClient; ?>
    };
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
                <section class="conditions">
                    <h3>3 - Accepter les conditions générales et mentions légales</h3>
                    <label>
                        <input type="checkbox">
                        J'ai lu et j'accepte les
                        <a href="#">Conditions Générales de Vente</a> et les
                        <a href="#">Mentions Légales</a> d'Alizon.
                    </label>
                </section>
            </div>

            <aside class="col recap" id="recap">
                <?php if (empty($cart)): ?>
                <div class="empty-cart">Panier vide</div>
                <?php else: ?>
                <?php foreach ($cart as $item): ?>
                <div class="produit" data-id="<?= htmlspecialchars($item['idProduit']) ?>">
                    <img src="<?= htmlspecialchars($item['img'] ?? '../../public/images/default.png') ?>"
                        alt="<?= htmlspecialchars($item['nom']) ?>">
                    <div class="infos">
                        <p class="titre"><?= htmlspecialchars($item['nom']) ?></p>
                        <p class="prix"><?= number_format($item['prix'] * $item['qty'], 2, ',', '') ?>€</p>
                        <div class="gestQte">
                            <div class="qte">
                                <button class="minus" data-id="<?= htmlspecialchars($item['idProduit']) ?>">-</button>
                                <span class="qty"
                                    data-id="<?= htmlspecialchars($item['idProduit']) ?>"><?= intval($item['qty']) ?></span>
                                <button class="plus" data-id="<?= htmlspecialchars($item['idProduit']) ?>">+</button>
                            </div>
                            <button class="delete" data-id="<?= htmlspecialchars($item['idProduit']) ?>">
                                <img src="../../public/images/bin.svg" alt="Supprimer">
                            </button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </aside>
        </div>

        <div class="payer-wrapper-mobile">
            <button class="payer payer--mobile">Payer</button>
        </div>
    </main>

    <?php include '../../views/frontoffice/partials/footerConnecte.php'; ?>

    <script src="../scripts/frontoffice/paiement-ajax.js"></script>
    <script src="../../public/amd-shim.js"></script>
    <script src="../../public/script.js"></script>
</body>

</html>
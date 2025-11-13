<?php
require_once "../../controllers/pdo.php";

// ID utilisateur connecté (à remplacer par la gestion de session)
$idClient = 1; 

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
    
    $cart = array_map(function($item) {
        return [
            'id' => strval($item['idProduit'] ?? ''),
            'nom' => strval($item['nom'] ?? 'Produit sans nom'),
            'prix' => floatval($item['prix'] ?? 0),
            'qty' => intval($item['qty'] ?? 0),
            'img' => $item['img'] ?? '../../public/images/default.png'
        ];
    }, $cart);
}

// ============================================================================
// FONCTIONS POUR GÉRER LES ACTIONS AJAX
// ============================================================================

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

function createOrderInDatabase($pdo, $idClient, $adresseLivraison, $villeLivraison, $regionLivraison, $numeroCarte) {
    try {
        $pdo->beginTransaction();

        $idClient = intval($idClient);
        $sql = "SELECT * FROM _panier WHERE idClient = $idClient";
        $stmt = $pdo->query($sql);
        $panier = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : false;
        
        if (!$panier) {
            throw new Exception("Panier non trouvé");
        }

        $idPanier = intval($panier['idPanier']);

        // Calculer les totaux
        $sqlTotals = "SELECT SUM(p.prix * pap.quantiteProduit) as sousTotal, SUM(pap.quantiteProduit) as nbArticles 
                     FROM _produitAuPanier pap
                     JOIN _produit p ON pap.idProduit = p.idProduit
                     WHERE pap.idPanier = $idPanier";
        $stmtTotals = $pdo->query($sqlTotals);
        $totals = $stmtTotals ? $stmtTotals->fetch(PDO::FETCH_ASSOC) : [];

        $sousTotal = $totals['sousTotal'] ?? 0;
        $nbArticles = $totals['nbArticles'] ?? 0;

        // Créer la commande (utilisation de quote() pour les champs texte)
        $montantTTC = floatval($sousTotal) * 1.20;
        $montantHT = floatval($sousTotal);
        $adresseQ = $pdo->quote($adresseLivraison);
        $villeQ = $pdo->quote($villeLivraison);
        $regionQ = $pdo->quote($regionLivraison);
        $carteQ = $pdo->quote($numeroCarte);

        $sql = "
            INSERT INTO _commande 
            (dateCommande, etatLivraison, montantCommandeTTC, montantCommandeHt, 
             quantiteCommande, adresseLivr, villeLivr, regionLivr, numeroCarte, idPanier)
            VALUES (NOW(), 'En attente', $montantTTC, $montantHT, $nbArticles, $adresseQ, $villeQ, $regionQ, $carteQ, $idPanier)
        ";
        $res = $pdo->query($sql);
        if ($res === false) {
            throw new Exception("Impossible de créer la commande");
        }

        $idCommande = $pdo->lastInsertId();

        // Copier les produits du panier vers la table contient
        $sql = "
            INSERT INTO _contient (idProduit, idCommande, prixProduitHt, tauxTva, quantite)
            SELECT pap.idProduit, $idCommande, p.prix, COALESCE(t.pourcentageTva, 20.0), pap.quantiteProduit
            FROM _produitAuPanier pap
            JOIN _produit p ON pap.idProduit = p.idProduit
            LEFT JOIN _tva t ON p.typeTva = t.typeTva
            WHERE pap.idPanier = $idPanier
        ";
        $res = $pdo->query($sql);
        if ($res === false) {
            throw new Exception("Impossible de copier les produits dans la commande");
        }

        // Vider le panier après commande
        $sql = "DELETE FROM _produitAuPanier WHERE idPanier = $idPanier";
        $res = $pdo->query($sql);
        if ($res === false) {
            throw new Exception("Impossible de vider le panier");
        }

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
    // Détecter AJAX via header X-Requested-With (si absent, on considère formulaire classique)
    $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

    try {
        switch ($_POST['action']) {
            case 'updateQty':
                $idProduit = $_POST['idProduit'] ?? '';
                $delta = intval($_POST['delta'] ?? 0);

                if ($idProduit && $delta != 0) {
                    $success = updateQuantityInDatabase($pdo, $idClient, $idProduit, $delta);
                    if ($isAjax) {
                        header('Content-Type: application/json');
                        echo json_encode(['success' => $success]);
                        exit;
                    }
                    // Pour les formulaires : PRG
                    header('Location: ' . $_SERVER['REQUEST_URI']);
                    exit;
                } else {
                    if ($isAjax) {
                        header('Content-Type: application/json');
                        echo json_encode(['success' => false, 'error' => 'Paramètres invalides']);
                        exit;
                    }
                    header('Location: ' . $_SERVER['REQUEST_URI']);
                    exit;
                }
                break;

            case 'removeItem':
                $idProduit = $_POST['idProduit'] ?? '';

                if ($idProduit) {
                    $success = removeFromCartInDatabase($pdo, $idClient, $idProduit);
                    if ($isAjax) {
                        header('Content-Type: application/json');
                        echo json_encode(['success' => $success]);
                        exit;
                    }
                    header('Location: ' . $_SERVER['REQUEST_URI']);
                    exit;
                } else {
                    if ($isAjax) {
                        header('Content-Type: application/json');
                        echo json_encode(['success' => false, 'error' => 'ID produit manquant']);
                        exit;
                    }
                    header('Location: ' . $_SERVER['REQUEST_URI']);
                    exit;
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
                    if ($isAjax) {
                        header('Content-Type: application/json');
                        echo json_encode(['success' => true, 'idCommande' => $idCommande]);
                        exit;
                    }
                    // Redirect after successful order
                    header('Location: ' . $_SERVER['REQUEST_URI']);
                    exit;
                } else {
                    if ($isAjax) {
                        header('Content-Type: application/json');
                        echo json_encode(['success' => false, 'error' => 'Données manquantes']);
                        exit;
                    }
                    header('Location: ' . $_SERVER['REQUEST_URI']);
                    exit;
                }
                break;

            default:
                if ($isAjax) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'error' => 'Action non reconnue']);
                    exit;
                }
                header('Location: ' . $_SERVER['REQUEST_URI']);
                exit;
        }
    } catch (Exception $e) {
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            exit;
        }
        // En cas d'erreur serveur pour formulaire : redirection simple (on pourrait afficher un message si besoin)
        header('Location: ' . $_SERVER['REQUEST_URI']);
        exit;
    }
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
                <?php foreach ($cart as $item): 
                        $nom = $item['nom'] ?? '';
                        $imgProd = $item['img'] ?? '../../public/images/default.png';
                        $prix = $item['prix'] ?? 0;
                        $qty = $item['qty'] ?? 0;
                        $id = $item['id'] ?? '';
                    ?>
                <div class="produit" data-id="<?= htmlspecialchars($id) ?>">
                    <img src="<?= htmlspecialchars($imgProd) ?>" alt="<?= htmlspecialchars($nom) ?>">
                    <div class="infos">
                        <p class="titre"><?= htmlspecialchars($nom) ?></p>
                        <p class="prix"><?= number_format($prix * $qty, 2, ',', '') ?>€</p>
                        <div class="gestQte">
                            <div class="qte">
                                <form method="post" style="display:inline">
                                    <input type="hidden" name="action" value="updateQty">
                                    <input type="hidden" name="idProduit" value="<?= htmlspecialchars($id) ?>">
                                    <input type="hidden" name="delta" value="-1">
                                    <button type="submit" class="minus">-</button>
                                </form>

                                <span class="qty" data-id="<?= htmlspecialchars($id) ?>"><?= intval($qty) ?></span>

                                <form method="post" style="display:inline">
                                    <input type="hidden" name="action" value="updateQty">
                                    <input type="hidden" name="idProduit" value="<?= htmlspecialchars($id) ?>">
                                    <input type="hidden" name="delta" value="1">
                                    <button type="submit" class="plus">+</button>
                                </form>
                            </div>

                            <form method="post" onsubmit="return confirm('Supprimer ce produit du panier ?')"
                                style="display:inline">
                                <input type="hidden" name="action" value="removeItem">
                                <input type="hidden" name="idProduit" value="<?= htmlspecialchars($id) ?>">
                                <button type="submit" class="delete">
                                    <img src="../../public/images/bin.svg" alt="Supprimer">
                                </button>
                            </form>
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
<?php
require_once "../../controllers/pdo.php";
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../views/frontoffice/connexionClient.php');
    exit;
}

$idClient = $_SESSION['user_id'];

function getCurrentCart($pdo, $idClient) {
    $stmt = $pdo->prepare("SELECT idPanier FROM _panier WHERE idClient = ? ORDER BY idPanier DESC LIMIT 1");
    $stmt->execute([$idClient]);
    $panier = $stmt->fetch(PDO::FETCH_ASSOC);

    $cart = [];

    if ($panier) {
        $idPanier = intval($panier['idPanier']);
        $sql = "SELECT p.idProduit, p.nom, p.prix, p.stock, pa.quantiteProduit as qty, i.URL as img
                FROM _produitAuPanier pa
                JOIN _produit p ON pa.idProduit = p.idProduit
                LEFT JOIN _imageDeProduit i ON p.idProduit = i.idProduit
                WHERE pa.idPanier = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$idPanier]);
        $cart = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
    }
    
    return $cart;
}

function updateStockAfterOrder($pdo, $idPanier) {
    try {
        $sql = "SELECT pap.idProduit, pap.quantiteProduit 
                FROM _produitAuPanier pap 
                WHERE pap.idPanier = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$idPanier]);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($items as $item) {
            $updateSql = "UPDATE _produit SET stock = stock - ? WHERE idProduit = ?";
            $updateStmt = $pdo->prepare($updateSql);
            $updateStmt->execute([$item['quantiteProduit'], $item['idProduit']]);
        }
        
        return true;
    } catch (Exception $e) {
        error_log("Erreur mise à jour stock: " . $e->getMessage());
        return false;
    }
}

function createOrderInDatabase($pdo, $idClient, $adresseLivraison, $villeLivraison, $numeroCarte, $codePostal = '', $nomCarte = 'Client inconnu', $dateExp = '12/30', $cvv = '000', $idAdresseFacturation = null) {
    try {
        $pdo->beginTransaction();
        $idClient = intval($idClient);

        $stmt = $pdo->prepare("SELECT * FROM _panier WHERE idClient = ? ORDER BY idPanier DESC LIMIT 1");
        $stmt->execute([$idClient]);
        $panier = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$panier) throw new Exception("Aucun panier trouvé pour ce client.");
        $idPanier = intval($panier['idPanier']);

        $sqlTotals = "
            SELECT SUM(p.prix * pap.quantiteProduit) AS sousTotal, SUM(pap.quantiteProduit) AS nbArticles
            FROM _produitAuPanier pap
            JOIN _produit p ON pap.idProduit = p.idProduit
            WHERE pap.idPanier = ?
        ";
        $stmtTotals = $pdo->prepare($sqlTotals);
        $stmtTotals->execute([$idPanier]);
        $totals = $stmtTotals->fetch(PDO::FETCH_ASSOC);
        $sousTotal = floatval($totals['sousTotal'] ?? 0);
        $nbArticles = intval($totals['nbArticles'] ?? 0);

        if ($nbArticles === 0) {
            throw new Exception("Le panier est vide.");
        }

        $checkCarte = $pdo->prepare("SELECT numeroCarte FROM _carteBancaire WHERE numeroCarte = ?");
        $checkCarte->execute([$numeroCarte]);

        if ($checkCarte->rowCount() === 0) {
            $sqlInsertCarte = "
                INSERT INTO _carteBancaire (numeroCarte, nom, dateExpiration, cvv)
                VALUES (?, ?, ?, ?)
            ";
            $stmtCarte = $pdo->prepare($sqlInsertCarte);
            if (!$stmtCarte->execute([$numeroCarte, $nomCarte, $dateExp, $cvv])) {
                throw new Exception("Erreur lors de l'ajout de la carte bancaire.");
            }
        }

        $sqlAdresseLivraison = "
            INSERT INTO _adresseLivraison (idClient, adresse, codePostal, ville)
            VALUES (?, ?, ?, ?)
        ";
        $stmtAdresse = $pdo->prepare($sqlAdresseLivraison);
        
        if (!$stmtAdresse->execute([$idClient, $adresseLivraison, $codePostal, $villeLivraison])) {
            throw new Exception("Erreur lors de l'ajout de l'adresse de livraison.");
        }
        $idAdresseLivraison = $pdo->lastInsertId();

        if ($idAdresseFacturation) {
            $idAdresseFacturation = intval($idAdresseFacturation);
            $checkFact = $pdo->prepare("SELECT idAdresseLivraison FROM _adresseLivraison WHERE idAdresseLivraison = ? AND idClient = ?");
            $checkFact->execute([$idAdresseFacturation, $idClient]);
            if ($checkFact->rowCount() === 0) {
                throw new Exception("Adresse de facturation invalide.");
            }
        } else {
            $idAdresseFacturation = $idAdresseLivraison;
        }

        $montantHT = $sousTotal;
        $montantTTC = $sousTotal * 1.20;

        $sqlCommande = "
            INSERT INTO _commande (
                dateCommande, etatLivraison, montantCommandeTTC, montantCommandeHt,
                quantiteCommande, nomTransporteur, dateExpedition,
                idAdresseLivr, idAdresseFact, numeroCarte, idPanier
            ) VALUES (
                NOW(), 'En préparation', ?, ?,
                ?, 'Colissimo', NULL,
                ?, ?, ?, ?
            )
        ";
        
        $stmtCommande = $pdo->prepare($sqlCommande);
        if (!$stmtCommande->execute([$montantTTC, $montantHT, $nbArticles, $idAdresseLivraison, $idAdresseFacturation, $numeroCarte, $idPanier])) {
            throw new Exception("Erreur lors de la création de la commande.");
        }

        $idCommande = $pdo->lastInsertId();
        
        $sqlContient = "
            INSERT INTO _contient (idProduit, idCommande, prixProduitHt, tauxTva, quantite)
            SELECT pap.idProduit, ?, p.prix, 
                   CASE 
                     WHEN p.typeTva = 'Réduit' THEN 10.0
                     WHEN p.typeTva = 'Super réduit' THEN 5.5
                     WHEN p.typeTva = 'Aucun' THEN 0.0
                     ELSE 20.0 
                   END as tauxTva,
                   pap.quantiteProduit
            FROM _produitAuPanier pap
            JOIN _produit p ON pap.idProduit = p.idProduit
            WHERE pap.idPanier = ?
        ";
        
        $stmtContient = $pdo->prepare($sqlContient);
        if (!$stmtContient->execute([$idCommande, $idPanier])) {
            throw new Exception("Erreur lors de la copie des produits.");
        }

        if (!updateStockAfterOrder($pdo, $idPanier)) {
            throw new Exception("Erreur lors de la mise à jour du stock.");
        }

        $stmtVider = $pdo->prepare("DELETE FROM _produitAuPanier WHERE idPanier = ?");
        if (!$stmtVider->execute([$idPanier])) {
            throw new Exception("Erreur lors du vidage du panier.");
        }

        $pdo->commit();
        return $idCommande;

    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw new Exception("Erreur lors de la création de la commande: " . $e->getMessage());
    }
}

function saveBillingAddress($pdo, $idClient, $adresse, $codePostal, $ville) {
    try {
        $sqlCheck = "SELECT idAdresseLivraison FROM _adresseLivraison 
                    WHERE idClient = ? 
                    AND adresse = ? 
                    AND codePostal = ? 
                    AND ville = ?";
        
        $stmt = $pdo->prepare($sqlCheck);
        $stmt->execute([$idClient, $adresse, $codePostal, $ville]);
        
        if ($stmt && $stmt->rowCount() > 0) {
            $existing = $stmt->fetch(PDO::FETCH_ASSOC);
            return [
                'success' => true, 
                'idAdresseFacturation' => $existing['idAdresseLivraison']
            ];
        }

        $sqlInsert = "
            INSERT INTO _adresseLivraison (idClient, adresse, codePostal, ville)
            VALUES (?, ?, ?, ?)
        ";
        
        $stmtInsert = $pdo->prepare($sqlInsert);
        if (!$stmtInsert->execute([$idClient, $adresse, $codePostal, $ville])) {
            throw new Exception("Erreur lors de l'insertion de l'adresse de facturation.");
        }

        $idAdresseFacturation = $pdo->lastInsertId();
        
        return [
            'success' => true, 
            'idAdresseFacturation' => $idAdresseFacturation
        ];

    } catch (Exception $e) {
        return [
            'success' => false, 
            'error' => $e->getMessage()
        ];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    try {
        switch ($_POST['action']) {
            case 'createOrder':
                $adresseLivraison = $_POST['adresseLivraison'] ?? '';
                $villeLivraison = $_POST['villeLivraison'] ?? '';
                $numeroCarte = $_POST['numeroCarte'] ?? '';
                $cvv = $_POST['cvv'] ?? '';
                $codePostal = $_POST['codePostal'] ?? '';
                $nomCarte = $_POST['nomCarte'] ?? 'Client inconnu';
                $dateExpiration = $_POST['dateExpiration'] ?? '12/30';
                $idAdresseFacturation = $_POST['idAdresseFacturation'] ?? null;

                if (empty($adresseLivraison) || empty($villeLivraison) || empty($numeroCarte) || empty($codePostal)) {
                    echo json_encode(['success' => false, 'error' => 'Tous les champs sont obligatoires']);
                    break;
                }

                $idCommande = createOrderInDatabase($pdo, $idClient, $adresseLivraison, $villeLivraison, $numeroCarte, $codePostal, $nomCarte, $dateExpiration, $cvv, $idAdresseFacturation);
                echo json_encode(['success' => true, 'idCommande' => $idCommande]);
                break;

            case 'saveBillingAddress':
                $adresse = $_POST['adresse'] ?? '';
                $codePostal = $_POST['codePostal'] ?? '';
                $ville = $_POST['ville'] ?? '';
                
                if (empty($adresse) || empty($codePostal) || empty($ville)) {
                    echo json_encode(['success' => false, 'error' => 'Tous les champs d\'adresse sont obligatoires']);
                    break;
                }
                
                $result = saveBillingAddress($pdo, $idClient, $adresse, $codePostal, $ville);
                echo json_encode($result);
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
    <link rel="icon" href="/public/images/logoBackoffice.svg">
    <title>Paiement - Alizon</title>
    <link rel="stylesheet" href="paiement.css">
</head>

<body class="pagePaiement">
    <?php include '../../views/frontoffice/partials/headerConnecte.php'; ?>

    <script>
    window.CLE_CHIFFREMENT = "?zu6j,xX{N12I]0r6C=v57IoASU~?6_y";

    window.__PAYMENT_DATA__ = {
        departments: <?php echo json_encode($departments, JSON_UNESCAPED_UNICODE); ?>,
        citiesByCode: <?php echo json_encode($citiesByCode, JSON_UNESCAPED_UNICODE); ?>,
        postals: <?php echo json_encode($postals, JSON_UNESCAPED_UNICODE); ?>,
        cart: <?php 
            $formattedCart = [];
            foreach ($cart as $item) {
                $formattedCart[] = [
                    'id' => strval($item['idProduit']),
                    'nom' => $item['nom'],
                    'prix' => floatval($item['prix']),
                    'qty' => intval($item['qty']),
                    'stock' => intval($item['stock']),
                    'img' => $item['img'] ?? '../../public/images/default.png'
                ];
            }
            echo json_encode($formattedCart, JSON_UNESCAPED_UNICODE); 
        ?>,
        idClient: <?php echo $idClient; ?>
    };
    </script>

    <main class="container">
        <div class="parent">
            <div class="col">
                <section class="delivery">
                    <h3>1 - Informations pour la livraison :</h3>
                    <div class="input-field">
                        <input class="adresse-input" type="text" placeholder="Adresse de livraison" required>
                        <small class="error-message" data-for="adresse"></small>
                    </div>
                    <div class="ligne">
                        <div class="input-field fixed-110">
                            <input class="code-postal-input" type="text" placeholder="Code postal" required>
                            <small class="error-message" data-for="code-postal"></small>
                        </div>
                        <div class="input-field flex-1">
                            <input class="ville-input" type="text" placeholder="Ville" required>
                            <small class="error-message" data-for="ville"></small>
                        </div>
                    </div>

                    <label>
                        <input id="checkboxFactAddr" type="checkbox">
                        Adresse de facturation différente
                    </label>

                    <div id="billingSection" class="billing-section">
                        <h4>Adresse de facturation :</h4>
                        <div class="input-field">
                            <input class="adresse-fact-input" type="text" placeholder="Adresse de facturation">
                            <small class="error-message" data-for="adresse-fact"></small>
                        </div>
                        <div class="ligne">
                            <div class="input-field fixed-110">
                                <input class="code-postal-fact-input" type="text" placeholder="Code postal">
                                <small class="error-message" data-for="code-postal-fact"></small>
                            </div>
                            <div class="input-field flex-1">
                                <input class="ville-fact-input" type="text" placeholder="Ville">
                                <small class="error-message" data-for="ville-fact"></small>
                            </div>
                        </div>
                        <button id="saveBillingAddress" class="btn-save">Enregistrer cette adresse</button>
                    </div>
                </section>

                <section class="payment">
                    <h3>2 - Informations de paiement :</h3>
                    <div class="input-field">
                        <input class="num-carte" type="text" placeholder="Numéro sur la carte" required>
                        <small class="error-message" data-for="num-carte"></small>
                    </div>
                    <div class="input-field">
                        <input class="nom-carte" type="text" placeholder="Nom sur la carte" required>
                        <small class="error-message" data-for="nom-carte"></small>
                    </div>
                    <div class="ligne">
                        <div class="input-field fixed-100">
                            <input class="carte-date" type="text" placeholder="MM/AA" required>
                            <small class="error-message" data-for="carte-date"></small>
                        </div>
                        <div class="input-field fixed-80">
                            <input class="cvv-input" type="text" placeholder="CVV" required minlength="3" maxlength="3">
                            <small class="error-message" data-for="cvv-input"></small>
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
                        <input type="checkbox" id="cgvCheckbox">
                        J'ai lu et j'accepte les
                        <a href="#">Conditions Générales de Vente</a> et les
                        <a href="#">Mentions Légales</a> d'Alizon.
                    </label>
                    <small class="error-message" data-for="cgv"></small>
                </section>
            </div>
        </div>

        <div class="payer-wrapper-mobile">
            <button class="payer payer--mobile">Payer</button>
        </div>
    </main>

    <div id="confirmationPopup" class="popup-overlay">
        <div class="popup-content">
            <button class="close-popup">&times;</button>
            <div id="popupContent"></div>
        </div>
    </div>

    <?php include '../../views/frontoffice/partials/footerConnecte.php'; ?>

    <script src="../../public/amd-shim.js"></script>
    <script src="../../controllers/Chiffrement.js"></script>
    <script src="../../sricpts/frontoffice/paiement.js"></script>
</body>

</html>
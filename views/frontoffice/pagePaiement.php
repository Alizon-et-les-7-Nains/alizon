<?php
require_once "../../controllers/pdo.php";
session_start();

// ============================================================================
// VÉRIFICATION DE LA CONNEXION
// ============================================================================

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../views/frontoffice/connexionClient.php');
    exit;
}

$idClient = $_SESSION['user_id'];

// ============================================================================
// FONCTIONS DE GESTION DU PANIER ET COMMANDES
// ============================================================================

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

        // Vérifier le stock avant de procéder
        $sqlStockCheck = "
            SELECT p.idProduit, p.nom, p.stock, pap.quantiteProduit as qty_panier
            FROM _produitAuPanier pap
            JOIN _produit p ON pap.idProduit = p.idProduit
            WHERE pap.idPanier = ? AND p.stock < pap.quantiteProduit
        ";
        $stmtStock = $pdo->prepare($sqlStockCheck);
        $stmtStock->execute([$idPanier]);
        $stockIssues = $stmtStock->fetchAll(PDO::FETCH_ASSOC);
        
        if (!empty($stockIssues)) {
            $errorMsg = "Stock insuffisant pour: ";
            foreach ($stockIssues as $issue) {
                $errorMsg .= $issue['nom'] . " (stock: " . $issue['stock'] . ", demandé: " . $issue['qty_panier'] . "), ";
            }
            throw new Exception(rtrim($errorMsg, ', '));
        }

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

        // Vérification existante carte
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

        // Création de l'adresse de livraison
        $sqlAdresseLivraison = "
            INSERT INTO _adresseLivraison (idClient, adresse, codePostal, ville)
            VALUES (?, ?, ?, ?)
        ";
        $stmtAdresse = $pdo->prepare($sqlAdresseLivraison);
        
        if (!$stmtAdresse->execute([$idClient, $adresseLivraison, $codePostal, $villeLivraison])) {
            throw new Exception("Erreur lors de l'ajout de l'adresse de livraison.");
        }
        $idAdresseLivraison = $pdo->lastInsertId();

        // Gestion adresse facturation
        if ($idAdresseFacturation) {
            $idAdresseFacturation = intval($idAdresseFacturation);
        } else {
            $idAdresseFacturation = $idAdresseLivraison;
        }

        // Création de la commande
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

        // Copie des produits vers _contient
        $sqlContient = "
            INSERT INTO _contient (idProduit, idCommande, prixProduitHt, tauxTva, quantite)
            SELECT pap.idProduit, ?, p.prix, COALESCE(t.pourcentageTva, 20.0), pap.quantiteProduit
            FROM _produitAuPanier pap
            JOIN _produit p ON pap.idProduit = p.idProduit
            LEFT JOIN _tva t ON p.typeTva = t.typeTva
            WHERE pap.idPanier = ?
        ";
        $stmtContient = $pdo->prepare($sqlContient);
        if (!$stmtContient->execute([$idCommande, $idPanier])) {
            throw new Exception("Erreur lors de la copie des produits.");
        }

        // Mettre à jour les stocks
        if (!updateStockAfterOrder($pdo, $idPanier)) {
            throw new Exception("Erreur lors de la mise à jour des stocks.");
        }

        // Mettre à jour le nombre de ventes
        $sqlUpdateSales = "
            UPDATE _produit p
            JOIN _produitAuPanier pap ON p.idProduit = pap.idProduit
            SET p.nbVente = p.nbVente + pap.quantiteProduit
            WHERE pap.idPanier = ?
        ";
        $stmtUpdateSales = $pdo->prepare($sqlUpdateSales);
        if (!$stmtUpdateSales->execute([$idPanier])) {
            throw new Exception("Erreur lors de la mise à jour des ventes.");
        }

        // Vider le panier
        $stmtVider = $pdo->prepare("DELETE FROM _produitAuPanier WHERE idPanier = ?");
        if (!$stmtVider->execute([$idPanier])) {
            throw new Exception("Erreur lors du vidage du panier.");
        }

        $pdo->commit();
        return $idCommande;

    } catch (Exception $e) {
        $pdo->rollBack();
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
                'idAdresseFacturation' => $existing['idAdresseLivraison'], 
                'message' => 'Adresse de facturation déjà existante'
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
            'idAdresseFacturation' => $idAdresseFacturation, 
            'message' => 'Adresse de facturation enregistrée avec succès'
        ];

    } catch (Exception $e) {
        error_log("Erreur saveBillingAddress: " . $e->getMessage());
        return [
            'success' => false, 
            'error' => $e->getMessage()
        ];
    }
}

// ============================================================================
// GESTION DES ACTIONS AJAX
// ============================================================================

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

// ============================================================================
// RÉCUPÉRATION DES DONNÉES POUR LA PAGE
// ============================================================================

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

// ============================================================================
// AFFICHAGE DE LA PAGE
// ============================================================================
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="../../public/style.css">
    <link rel="icon" href="/public/images/logoBackoffice.svg">
    <title>Paiement - Alizon</title>
    <style>
    .error-message {
        color: #d32f2f;
        font-size: 0.85rem;
        margin-top: 4px;
        display: block;
        min-height: 20px;
    }

    .billing-section {
        margin-top: 20px;
        padding: 15px;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        background-color: #f9f9f9;
        display: none;
    }

    .billing-section.active {
        display: block;
    }

    .btn-save {
        background: #252b56;
        color: white;
        border: none;
        border-radius: 4px;
        padding: 10px 20px;
        cursor: pointer;
        font-family: 'lora', serif;
        margin-top: 10px;
    }

    .btn-save:hover {
        background: #1a1f3f;
    }

    .popup-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.7);
        display: none;
        justify-content: center;
        align-items: center;
        z-index: 1000;
    }

    .popup-content {
        background: white;
        border-radius: 12px;
        padding: 30px;
        width: 90%;
        max-width: 800px;
        max-height: 80vh;
        overflow-y: auto;
        position: relative;
    }

    .close-popup {
        position: absolute;
        top: 15px;
        right: 15px;
        background: none;
        border: none;
        font-size: 24px;
        cursor: pointer;
        color: #666;
    }

    .cart-summary {
        max-height: 300px;
        overflow-y: auto;
        margin: 20px 0;
        padding: 15px;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
    }

    .cart-item-summary {
        display: flex;
        align-items: center;
        margin-bottom: 15px;
        padding-bottom: 15px;
        border-bottom: 1px solid #eee;
    }

    .cart-item-summary:last-child {
        border-bottom: none;
        margin-bottom: 0;
        padding-bottom: 0;
    }

    .cart-item-image {
        width: 60px;
        height: 60px;
        object-fit: cover;
        border-radius: 4px;
        margin-right: 15px;
    }

    .cart-item-info {
        flex: 1;
    }

    .cart-item-name {
        font-weight: 500;
        margin-bottom: 5px;
        color: #252b56;
    }

    .cart-item-details {
        font-size: 0.9rem;
        color: #666;
    }

    .popup-buttons {
        display: flex;
        justify-content: center;
        gap: 15px;
        margin-top: 20px;
    }

    .btn-confirm {
        background: #4CAF50;
        color: white;
        border: none;
        border-radius: 4px;
        padding: 12px 30px;
        cursor: pointer;
        font-family: 'lora', serif;
        font-size: 16px;
    }

    .btn-cancel {
        background: #f44336;
        color: white;
        border: none;
        border-radius: 4px;
        padding: 12px 30px;
        cursor: pointer;
        font-family: 'lora', serif;
        font-size: 16px;
    }

    .thank-you-popup {
        text-align: center;
        padding: 40px 30px;
    }

    .thank-you-popup h2 {
        color: #252b56;
        margin-bottom: 20px;
    }

    .thank-you-popup p {
        margin-bottom: 15px;
        color: #666;
    }

    .order-number {
        font-size: 24px;
        font-weight: bold;
        color: #4CAF50;
        margin: 20px 0;
    }

    .btn-home {
        background: #252b56;
        color: white;
        border: none;
        border-radius: 4px;
        padding: 12px 30px;
        cursor: pointer;
        font-family: 'lora', serif;
        font-size: 16px;
        margin-top: 20px;
    }
    </style>
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
                        <input class="adresse-input" type="text" placeholder="Adresse de livraison"
                            aria-label="Adresse de livraison" required>
                        <small class="error-message" data-for="adresse"></small>
                    </div>
                    <div class="ligne">
                        <div class="input-field fixed-110">
                            <input class="code-postal-input" type="text" placeholder="Code postal"
                                aria-label="Code postal" required>
                            <small class="error-message" data-for="code-postal"></small>
                        </div>
                        <div class="input-field flex-1">
                            <input class="ville-input" type="text" placeholder="Ville" aria-label="Ville" required>
                            <small class="error-message" data-for="ville"></small>
                        </div>
                    </div>

                    <label>
                        <input id="checkboxFactAddr" type="checkbox">
                        Adresse de facturation différente
                    </label>

                    <div id="billingSection" class="billing-section">
                        <h4 style="margin-top: 0; margin-bottom: 15px;">Adresse de facturation :</h4>
                        <div class="input-field">
                            <input class="adresse-fact-input" type="text" placeholder="Adresse de facturation"
                                aria-label="Adresse de facturation">
                            <small class="error-message" data-for="adresse-fact"></small>
                        </div>
                        <div class="ligne">
                            <div class="input-field fixed-110">
                                <input class="code-postal-fact-input" type="text" placeholder="Code postal"
                                    aria-label="Code postal facturation">
                                <small class="error-message" data-for="code-postal-fact"></small>
                            </div>
                            <div class="input-field flex-1">
                                <input class="ville-fact-input" type="text" placeholder="Ville"
                                    aria-label="Ville facturation">
                                <small class="error-message" data-for="ville-fact"></small>
                            </div>
                        </div>
                        <button id="saveBillingAddress" class="btn-save">Enregistrer cette adresse</button>
                    </div>
                </section>

                <section class="payment">
                    <h3>2 - Informations de paiement :</h3>
                    <div class="input-field">
                        <input class="num-carte" type="text" placeholder="Numéro sur la carte"
                            aria-label="Numéro sur la carte" required>
                        <small class="error-message" data-for="num-carte"></small>
                    </div>
                    <div class="input-field">
                        <input class="nom-carte" type="text" placeholder="Nom sur la carte"
                            aria-label="Nom sur la carte" required>
                        <small class="error-message" data-for="nom-carte"></small>
                    </div>
                    <div class="ligne">
                        <div class="input-field fixed-100">
                            <input class="carte-date" type="text" placeholder="MM/AA" aria-label="Date expiration"
                                required>
                            <small class="error-message" data-for="carte-date"></small>
                        </div>
                        <div class="input-field fixed-80">
                            <input class="cvv-input" type="text" placeholder="CVV" aria-label="CVV" required
                                minlength="3" maxlength="3">
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
                    <small class="error-message" data-for="cgv" style="display: block; margin-top: 10px;"></small>
                </section>
            </div>
        </div>

        <div class="payer-wrapper-mobile">
            <button class="payer payer--mobile">Payer</button>
        </div>
    </main>

    <!-- Popup de confirmation -->
    <div id="confirmationPopup" class="popup-overlay">
        <div class="popup-content">
            <button class="close-popup">&times;</button>
            <div id="popupContent">
                <!-- Contenu dynamique -->
            </div>
        </div>
    </div>

    <?php include '../../views/frontoffice/partials/footerConnecte.php'; ?>

    <script src="../../public/amd-shim.js"></script>
    <script src="../../controllers/Chiffrement.js"></script>
    <script src="../../public/script.js"></script>
    <script>
    // Script principal pour la page de paiement
    document.addEventListener('DOMContentLoaded', function() {
        // Variables globales
        let idAdresseFacturation = null;
        let savedBillingAddress = null;

        // Références aux éléments DOM
        const factAddrCheckbox = document.getElementById('checkboxFactAddr');
        const billingSection = document.getElementById('billingSection');
        const saveBillingBtn = document.getElementById('saveBillingAddress');
        const confirmationPopup = document.getElementById('confirmationPopup');
        const popupContent = document.getElementById('popupContent');
        const closePopupBtn = document.querySelector('.close-popup');
        const payerButtons = document.querySelectorAll('.payer');

        // Gestion de la checkbox adresse de facturation différente
        if (factAddrCheckbox && billingSection) {
            factAddrCheckbox.addEventListener('change', function() {
                if (this.checked) {
                    billingSection.classList.add('active');
                    // Si une adresse est déjà enregistrée, la pré-remplir
                    if (savedBillingAddress) {
                        document.querySelector('.adresse-fact-input').value = savedBillingAddress
                            .adresse;
                        document.querySelector('.code-postal-fact-input').value = savedBillingAddress
                            .codePostal;
                        document.querySelector('.ville-fact-input').value = savedBillingAddress.ville;
                    }
                } else {
                    billingSection.classList.remove('active');
                    idAdresseFacturation = null;
                }
            });
        }

        // Gestion de l'enregistrement de l'adresse de facturation
        if (saveBillingBtn) {
            saveBillingBtn.addEventListener('click', async function() {
                const adresseFactInput = document.querySelector('.adresse-fact-input');
                const codePostalFactInput = document.querySelector('.code-postal-fact-input');
                const villeFactInput = document.querySelector('.ville-fact-input');

                // Validation des champs
                if (!adresseFactInput.value.trim()) {
                    showError('Adresse de facturation requise', 'adresse-fact');
                    return;
                }

                if (!codePostalFactInput.value.trim()) {
                    showError('Code postal requis', 'code-postal-fact');
                    return;
                }

                if (!villeFactInput.value.trim()) {
                    showError('Ville requise', 'ville-fact');
                    return;
                }

                if (!/^\d{5}$/.test(codePostalFactInput.value.trim())) {
                    showError('Le code postal doit contenir 5 chiffres', 'code-postal-fact');
                    return;
                }

                try {
                    // Préparation des données
                    const formData = new URLSearchParams();
                    formData.append('action', 'saveBillingAddress');
                    formData.append('adresse', adresseFactInput.value.trim());
                    formData.append('codePostal', codePostalFactInput.value.trim());
                    formData.append('ville', villeFactInput.value.trim());

                    // Envoi au serveur
                    const response = await fetch('', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: formData
                    });

                    const result = await response.json();

                    if (result.success) {
                        idAdresseFacturation = result.idAdresseFacturation;
                        savedBillingAddress = {
                            adresse: adresseFactInput.value.trim(),
                            codePostal: codePostalFactInput.value.trim(),
                            ville: villeFactInput.value.trim()
                        };
                        alert('Adresse de facturation enregistrée avec succès');
                    } else {
                        showError(result.error || 'Erreur lors de l\'enregistrement');
                    }
                } catch (error) {
                    showError('Erreur réseau');
                    console.error(error);
                }
            });
        }

        // Gestion des boutons payer
        payerButtons.forEach(btn => {
            btn.addEventListener('click', async function(e) {
                e.preventDefault();

                if (!validateForm()) {
                    return;
                }

                // Récupération des données du formulaire
                const formData = getFormData();

                // Vérification du panier
                const cart = window.__PAYMENT_DATA__?.cart || [];
                if (cart.length === 0) {
                    showError('Votre panier est vide');
                    return;
                }

                // Vérification du stock
                const stockIssues = cart.filter(item => item.qty > item.stock);
                if (stockIssues.length > 0) {
                    let errorMsg = 'Stock insuffisant pour:\n';
                    stockIssues.forEach(item => {
                        errorMsg +=
                            `- ${item.nom} (stock: ${item.stock}, demandé: ${item.qty})\n`;
                    });
                    alert(errorMsg);
                    return;
                }

                // Affichage de la popup de confirmation
                showConfirmationPopup(formData, cart);
            });
        });

        // Fermeture de la popup
        if (closePopupBtn) {
            closePopupBtn.addEventListener('click', function() {
                confirmationPopup.style.display = 'none';
            });
        }

        // Fermeture de la popup en cliquant à l'extérieur
        confirmationPopup.addEventListener('click', function(e) {
            if (e.target === confirmationPopup) {
                confirmationPopup.style.display = 'none';
            }
        });

        // Fonction d'affichage d'erreur
        function showError(message, field = null) {
            if (field && document.querySelector(`[data-for="${field}"]`)) {
                const errorEl = document.querySelector(`[data-for="${field}"]`);
                errorEl.textContent = message;
                errorEl.style.display = 'block';
            } else {
                alert(message);
            }
        }

        // Fonction de validation du formulaire
        function validateForm() {
            let isValid = true;

            // Réinitialiser les erreurs
            document.querySelectorAll('.error-message').forEach(el => {
                el.textContent = '';
                el.style.display = 'none';
            });

            // Validation adresse de livraison
            const adresseInput = document.querySelector('.adresse-input');
            const codePostalInput = document.querySelector('.code-postal-input');
            const villeInput = document.querySelector('.ville-input');

            if (!adresseInput.value.trim()) {
                showError('Adresse de livraison requise', 'adresse');
                isValid = false;
            }

            if (!codePostalInput.value.trim()) {
                showError('Code postal requis', 'code-postal');
                isValid = false;
            } else if (!/^\d{5}$/.test(codePostalInput.value.trim())) {
                showError('Le code postal doit contenir 5 chiffres', 'code-postal');
                isValid = false;
            }

            if (!villeInput.value.trim()) {
                showError('Ville requise', 'ville');
                isValid = false;
            }

            // Validation paiement
            const numCarteInput = document.querySelector('.num-carte');
            const nomCarteInput = document.querySelector('.nom-carte');
            const carteDateInput = document.querySelector('.carte-date');
            const cvvInput = document.querySelector('.cvv-input');

            if (!numCarteInput.value.trim()) {
                showError('Numéro de carte requis', 'num-carte');
                isValid = false;
            } else {
                const cardNumber = numCarteInput.value.replace(/\s+/g, '');
                if (!/^\d{16}$/.test(cardNumber)) {
                    showError('Le numéro de carte doit contenir 16 chiffres', 'num-carte');
                    isValid = false;
                }
            }

            if (!nomCarteInput.value.trim()) {
                showError('Nom sur la carte requis', 'nom-carte');
                isValid = false;
            }

            if (!carteDateInput.value.trim()) {
                showError('Date d\'expiration requise', 'carte-date');
                isValid = false;
            } else if (!/^\d{2}\/\d{2}$/.test(carteDateInput.value.trim())) {
                showError('Format de date invalide (MM/AA)', 'carte-date');
                isValid = false;
            }

            if (!cvvInput.value.trim()) {
                showError('CVV requis', 'cvv-input');
                isValid = false;
            } else if (!/^\d{3}$/.test(cvvInput.value.trim())) {
                showError('Le CVV doit contenir 3 chiffres', 'cvv-input');
                isValid = false;
            }

            // Validation CGV
            const cgvCheckbox = document.getElementById('cgvCheckbox');
            if (!cgvCheckbox || !cgvCheckbox.checked) {
                showError('Veuillez accepter les conditions générales', 'cgv');
                isValid = false;
            }

            // Validation adresse de facturation si cochée
            if (factAddrCheckbox && factAddrCheckbox.checked) {
                const adresseFactInput = document.querySelector('.adresse-fact-input');
                const codePostalFactInput = document.querySelector('.code-postal-fact-input');
                const villeFactInput = document.querySelector('.ville-fact-input');

                if (!adresseFactInput.value.trim()) {
                    showError('Adresse de facturation requise', 'adresse-fact');
                    isValid = false;
                }

                if (!codePostalFactInput.value.trim()) {
                    showError('Code postal facturation requis', 'code-postal-fact');
                    isValid = false;
                } else if (!/^\d{5}$/.test(codePostalFactInput.value.trim())) {
                    showError('Le code postal doit contenir 5 chiffres', 'code-postal-fact');
                    isValid = false;
                }

                if (!villeFactInput.value.trim()) {
                    showError('Ville facturation requise', 'ville-fact');
                    isValid = false;
                }
            }

            return isValid;
        }

        // Fonction pour récupérer les données du formulaire
        function getFormData() {
            return {
                adresseLivraison: document.querySelector('.adresse-input').value.trim(),
                villeLivraison: document.querySelector('.ville-input').value.trim(),
                codePostal: document.querySelector('.code-postal-input').value.trim(),
                numCarte: document.querySelector('.num-carte').value.trim(),
                nomCarte: document.querySelector('.nom-carte').value.trim(),
                dateExpiration: document.querySelector('.carte-date').value.trim(),
                cvv: document.querySelector('.cvv-input').value.trim(),
                adresseFacturation: factAddrCheckbox && factAddrCheckbox.checked ?
                    document.querySelector('.adresse-fact-input').value.trim() : null,
                codePostalFacturation: factAddrCheckbox && factAddrCheckbox.checked ?
                    document.querySelector('.code-postal-fact-input').value.trim() : null,
                villeFacturation: factAddrCheckbox && factAddrCheckbox.checked ?
                    document.querySelector('.ville-fact-input').value.trim() : null
            };
        }

        // Fonction pour afficher la popup de confirmation
        function showConfirmationPopup(formData, cart) {
            // Construction du contenu HTML
            let cartHtml = '<div class="cart-summary">';
            let total = 0;

            cart.forEach(item => {
                const itemTotal = item.prix * item.qty;
                total += itemTotal;

                cartHtml += `
                    <div class="cart-item-summary">
                        <img src="${item.img}" alt="${item.nom}" class="cart-item-image">
                        <div class="cart-item-info">
                            <div class="cart-item-name">${item.nom}</div>
                            <div class="cart-item-details">
                                Quantité: ${item.qty} × ${item.prix.toFixed(2)}€ = ${itemTotal.toFixed(2)}€
                            </div>
                        </div>
                    </div>
                `;
            });

            cartHtml += '</div>';

            const popupHtml = `
                <h2 style="color: #252b56; margin-bottom: 20px;">Confirmation de commande</h2>
                <div style="margin-bottom: 20px;">
                    <p><strong>Adresse de livraison :</strong><br>
                    ${formData.adresseLivraison}<br>
                    ${formData.codePostal} ${formData.ville}</p>
                    
                    ${formData.adresseFacturation ? `
                    <p style="margin-top: 10px;"><strong>Adresse de facturation :</strong><br>
                    ${formData.adresseFacturation}<br>
                    ${formData.codePostalFacturation} ${formData.villeFacturation}</p>
                    ` : ''}
                    
                    <p><strong>Paiement :</strong> Carte Visa se terminant par ${formData.numCarte.slice(-4)}</p>
                </div>
                
                <h3 style="color: #252b56; margin-bottom: 10px;">Récapitulatif du panier</h3>
                ${cartHtml}
                
                <div style="text-align: right; margin: 20px 0; padding-top: 15px; border-top: 2px solid #252b56;">
                    <h3 style="margin: 0;">Total : ${total.toFixed(2)}€</h3>
                </div>
                
                <div class="popup-buttons">
                    <button class="btn-cancel">Annuler</button>
                    <button class="btn-confirm">Confirmer la commande</button>
                </div>
            `;

            popupContent.innerHTML = popupHtml;
            confirmationPopup.style.display = 'flex';

            // Gestion des boutons dans la popup
            const confirmBtn = popupContent.querySelector('.btn-confirm');
            const cancelBtn = popupContent.querySelector('.btn-cancel');

            if (cancelBtn) {
                cancelBtn.addEventListener('click', function() {
                    confirmationPopup.style.display = 'none';
                });
            }

            if (confirmBtn) {
                confirmBtn.addEventListener('click', async function() {
                    confirmBtn.disabled = true;
                    confirmBtn.textContent = 'Traitement en cours...';

                    try {
                        // Chiffrement des données sensibles
                        const numeroCarteChiffre = window.vignere ?
                            window.vignere(formData.numCarte.replace(/\s+/g, ''), window
                                .CLE_CHIFFREMENT, 1) : formData.numCarte;
                        const cvvChiffre = window.vignere ?
                            window.vignere(formData.cvv, window.CLE_CHIFFREMENT, 1) : formData.cvv;

                        // Préparation des données pour la commande
                        const orderData = new FormData();
                        orderData.append('action', 'createOrder');
                        orderData.append('adresseLivraison', formData.adresseLivraison);
                        orderData.append('villeLivraison', formData.villeLivraison);
                        orderData.append('numeroCarte', numeroCarteChiffre);
                        orderData.append('cvv', cvvChiffre);
                        orderData.append('nomCarte', formData.nomCarte);
                        orderData.append('dateExpiration', formData.dateExpiration);
                        orderData.append('codePostal', formData.codePostal);

                        if (idAdresseFacturation) {
                            orderData.append('idAdresseFacturation', idAdresseFacturation);
                        }

                        // Envoi de la commande
                        const response = await fetch('', {
                            method: 'POST',
                            body: orderData
                        });

                        const result = await response.json();

                        if (result.success) {
                            // Affichage du message de confirmation
                            const thankYouHtml = `
                                <div class="thank-you-popup">
                                    <h2>Merci pour votre commande !</h2>
                                    <p>Votre commande a été enregistrée avec succès.</p>
                                    <div class="order-number">
                                        Numéro de commande : ${result.idCommande}
                                    </div>
                                    <p>Vous recevrez un email de confirmation sous peu.</p>
                                    <button class="btn-home">Retour à l'accueil</button>
                                </div>
                            `;

                            popupContent.innerHTML = thankYouHtml;

                            // Gestion du bouton retour à l'accueil
                            const homeBtn = popupContent.querySelector('.btn-home');
                            if (homeBtn) {
                                homeBtn.addEventListener('click', function() {
                                    window.location.href =
                                        '../../views/frontoffice/accueilConnecte.php';
                                });
                            }
                        } else {
                            alert('Erreur : ' + (result.error ||
                                'Erreur lors de la création de la commande'));
                            confirmationPopup.style.display = 'none';
                            confirmBtn.disabled = false;
                            confirmBtn.textContent = 'Confirmer la commande';
                        }
                    } catch (error) {
                        console.error('Erreur lors de la commande:', error);
                        alert('Une erreur est survenue. Veuillez réessayer.');
                        confirmBtn.disabled = false;
                        confirmBtn.textContent = 'Confirmer la commande';
                    }
                });
            }
        }
    });
    </script>
</body>

</html>
<?php
require_once "../../controllers/pdo.php";
session_start();
$envPath = __DIR__ . '/../../.env';


if (file_exists($envPath)) {
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        list($name, $value) = explode('=', $line, 2);
        $_ENV[trim($name)] = trim($value);
    }
}

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
    window.__PAYMENT_DATA__ = {
        cart: <?php 
            $formattedCart = [];
            foreach ($cart as $item) {
                $formattedCart[] = [
                    'id' => strval($item['idProduit']),
                    'nom' => htmlspecialchars($item['nom']),
                    'prix' => floatval($item['prix']),
                    'qty' => intval($item['qty']),
                    'stock' => intval($item['stock']),
                    'img' => $item['img'] ?? '../../public/images/default.png'
                ];
            }
            echo json_encode($formattedCart, JSON_UNESCAPED_UNICODE); 
        ?>,
        totals: {
            sousTotal: <?php echo number_format($sousTotal, 2, '.', ''); ?>,
            livraison: <?php echo number_format($livraison, 2, '.', ''); ?>,
            montantTTC: <?php echo number_format($montantTTC, 2, '.', ''); ?>
        }
    };
    </script>

    <main class="container">
        <!-- Progress Steps -->
        <div class="progress-steps">
            <div class="step active">
                <div class="step-circle">1</div>
                <span class="step-text">Livraison</span>
            </div>
            <div class="step-divider"></div>
            <div class="step">
                <div class="step-circle">2</div>
                <span class="step-text">Paiement</span>
            </div>
            <div class="step-divider"></div>
            <div class="step">
                <div class="step-circle">3</div>
                <span class="step-text">Confirmation</span>
            </div>
        </div>

        <div class="payment-layout">
            <div class="forms-container">
                <div class="form-section">
                    <div class="section-header">
                        <div class="section-number">1</div>
                        <h2 class="section-title">Informations de livraison</h2>
                    </div>

                    <div class="form-row">
                        <div class="input-group">
                            <label>Adresse de livraison</label>
                            <input type="text" class="adresse-input" placeholder="123 Rue de la Paix" required>
                            <span class="error-message" data-for="adresse">Adresse requise</span>
                        </div>
                    </div>

                    <div class="form-row two-cols">
                        <div class="input-group">
                            <label>Code postal</label>
                            <input type="text" class="code-postal-input" placeholder="75001" required>
                            <span class="error-message" data-for="code-postal">Code postal invalide</span>
                        </div>
                        <div class="input-group">
                            <label>Ville</label>
                            <input type="text" class="ville-input" placeholder="Paris" required>
                            <span class="error-message" data-for="ville">Ville requise</span>
                        </div>
                    </div>

                    <div class="checkbox-group">
                        <input type="checkbox" id="checkboxFactAddr">
                        <label for="checkboxFactAddr" class="checkbox-label">
                            Utiliser une adresse de facturation différente
                        </label>
                    </div>

                    <div class="billing-section" id="billingSection">
                        <h4 style="margin-bottom: 20px; color: var(--accent); font-size: 18px;">Adresse de facturation
                        </h4>
                        <div class="form-row">
                            <div class="input-group">
                                <label>Adresse</label>
                                <input type="text" class="adresse-fact-input" placeholder="123 Rue de Commerce">
                            </div>
                        </div>
                        <div class="form-row two-cols">
                            <div class="input-group">
                                <label>Code postal</label>
                                <input type="text" class="code-postal-fact-input" placeholder="75001">
                            </div>
                            <div class="input-group">
                                <label>Ville</label>
                                <input type="text" class="ville-fact-input" placeholder="Paris">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <div class="section-header">
                        <div class="section-number">2</div>
                        <h2 class="section-title">Informations de paiement</h2>
                    </div>

                    <div class="form-row">
                        <div class="input-group">
                            <label>Numéro de carte</label>
                            <input type="text" class="num-carte" placeholder="1234 5678 9012 3456" required>
                            <span class="error-message" data-for="num-carte">Numéro de carte invalide</span>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="input-group">
                            <label>Nom sur la carte</label>
                            <input type="text" class="nom-carte" placeholder="Jean Dupont" required>
                            <span class="error-message" data-for="nom-carte">Nom requis</span>
                        </div>
                    </div>

                    <div class="form-row card-details">
                        <div class="input-group">
                            <label>Date d'expiration</label>
                            <input type="text" class="carte-date" placeholder="MM/AA" required>
                            <span class="error-message" data-for="carte-date">Format invalide</span>
                        </div>
                        <div class="input-group">
                            <label>CVV</label>
                            <input type="text" class="cvv-input" placeholder="123" maxlength="3" required>
                            <span class="error-message" data-for="cvv-input">CVV invalide</span>
                        </div>
                    </div>

                    <div class="payment-icons">
                        <img class="payment-icon"
                            src="https://upload.wikimedia.org/wikipedia/commons/5/5e/Visa_Inc._logo.svg" alt="Visa">
                    </div>
                </div>

                <div class="form-section">
                    <div class="section-header">
                        <div class="section-number">3</div>
                        <h2 class="section-title">Conditions générales</h2>
                    </div>

                    <div class="checkbox-group">
                        <input type="checkbox" id="cgvCheckbox">
                        <label for="cgvCheckbox" class="checkbox-label">
                            J'ai lu et j'accepte les <a href="#"
                                style="color: var(--accent); text-decoration: underline;">Conditions Générales de
                                Vente</a> et les <a href="#"
                                style="color: var(--accent); text-decoration: underline;">Mentions Légales</a> d'Alizon.
                        </label>
                    </div>
                    <span class="error-message" data-for="cgv">Vous devez accepter les CGV</span>
                </div>
            </div>

            <aside class="order-summary">
                <h3 class="summary-title">Récapitulatif</h3>

                <div class="cart-items">
                    <?php if (empty($cart)): ?>
                    <p style="text-align: center; color: #666; padding: 20px;">Votre panier est vide</p>
                    <?php else: ?>
                    <?php foreach ($cart as $item): ?>
                    <div class="cart-item">
                        <img class="item-image"
                            src="<?php echo htmlspecialchars($item['img'] ?? '../../public/images/default.png'); ?>"
                            alt="<?php echo htmlspecialchars($item['nom']); ?>">
                        <div class="item-details">
                            <div class="item-name"><?php echo htmlspecialchars($item['nom']); ?></div>
                            <div class="item-meta">Quantité: <?php echo $item['qty']; ?></div>
                        </div>
                        <div class="item-price"><?php echo number_format($item['prix'] * $item['qty'], 2); ?> €</div>
                    </div>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <div class="summary-totals">
                    <div class="total-row">
                        <span>Sous-total</span>
                        <span><?php echo number_format($sousTotal, 2); ?> €</span>
                    </div>
                    <div class="total-row">
                        <span>Livraison</span>
                        <span><?php echo number_format($livraison, 2); ?> €</span>
                    </div>
                    <div class="total-row final">
                        <span>Total</span>
                        <span><?php echo number_format($montantTTC, 2); ?> €</span>
                    </div>
                </div>

                <button class="cta-button">Finaliser le paiement</button>

                <div class="security-badge">
                    <span class="security-icon">🔒</span>
                    <span>Paiement 100% sécurisé</span>
                </div>
            </aside>
        </div>
    </main>

    <div class="popup-overlay" id="confirmationPopup">
        <div class="popup-content" id="popupContent">
        </div>
    </div>

    <?php include '../../views/frontoffice/partials/footerConnecte.php'; ?>

    <script src="../../public/amd-shim.js"></script>
    <script src="../scripts/frontoffice/paiement.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const checkboxFactAddr = document.getElementById('checkboxFactAddr');
        const billingSection = document.getElementById('billingSection');

        if (checkboxFactAddr && billingSection) {
            checkboxFactAddr.addEventListener('change', function() {
                billingSection.classList.toggle('active', this.checked);
            });
        }

        const ctaButton = document.querySelector('.cta-button');
        if (ctaButton) {
            ctaButton.addEventListener('click', function(e) {
                e.preventDefault();

                const formData = {
                    adresseLivraison: document.querySelector('.adresse-input')?.value || '',
                    codePostal: document.querySelector('.code-postal-input')?.value || '',
                    villeLivraison: document.querySelector('.ville-input')?.value || '',
                    numCarte: document.querySelector('.num-carte')?.value.replace(/\s+/g, '') || '',
                    nomCarte: document.querySelector('.nom-carte')?.value || '',
                    dateExpiration: document.querySelector('.carte-date')?.value || '',
                    cvv: document.querySelector('.cvv-input')?.value || ''
                };

                let isValid = true;

                document.querySelectorAll('.error-message').forEach(error => {
                    error.classList.remove('show');
                });

                if (!formData.adresseLivraison) {
                    document.querySelector('[data-for="adresse"]').classList.add('show');
                    isValid = false;
                }
                if (!formData.codePostal) {
                    document.querySelector('[data-for="code-postal"]').classList.add('show');
                    isValid = false;
                }
                if (!formData.villeLivraison) {
                    document.querySelector('[data-for="ville"]').classList.add('show');
                    isValid = false;
                }
                if (!formData.numCarte || formData.numCarte.length < 16) {
                    document.querySelector('[data-for="num-carte"]').classList.add('show');
                    isValid = false;
                }
                if (!formData.nomCarte) {
                    document.querySelector('[data-for="nom-carte"]').classList.add('show');
                    isValid = false;
                }
                if (!formData.dateExpiration) {
                    document.querySelector('[data-for="carte-date"]').classList.add('show');
                    isValid = false;
                }
                if (!formData.cvv || formData.cvv.length !== 3) {
                    document.querySelector('[data-for="cvv-input"]').classList.add('show');
                    isValid = false;
                }

                const cgvCheckbox = document.getElementById('cgvCheckbox');
                if (!cgvCheckbox || !cgvCheckbox.checked) {
                    document.querySelector('[data-for="cgv"]').classList.add('show');
                    isValid = false;
                }

                if (!isValid) {
                    return;
                }
                const popup = document.getElementById('confirmationPopup');
                if (popup) {
                    popup.classList.add('show');
                    console.log('Form data ready for submission:', formData);
                }
            });
        }
    });
    </script>
</body>

</html>
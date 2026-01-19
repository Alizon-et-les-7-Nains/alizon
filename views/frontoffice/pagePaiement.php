<?php
session_start();
require_once "../../controllers/pdo.php";

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../views/frontoffice/connexionClient.php');
    exit;
}

$idClient = $_SESSION['user_id'];

/**
 * Récupère le panier actuel d'un client
 *
 * Cette fonction récupère le panier le plus récent associé à un ID client,
 * puis récupère tous les produits dans ce panier ainsi que leurs détails.
 *
 * Parametres :
 *     $pdo L'instance de connexion à la base de données PDO
 *     $idClient L'ID du client pour lequel récupérer le panier
 *
 * Retourne :
 * 
 *  Un tableau associatif des articles du panier, chacun contenant :
 *        - idProduit : ID du produit
 *        - nom : Nom du produit
 *        - prix : Prix du produit
 *        - stock : Quantité en stock disponible
 *        - qty : Quantité de ce produit dans le panier
 *        - img : URL de l'image du produit (nullable)
 * 
 * Retourne un tableau vide si aucun panier n'existe pour le client
 */
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



/**
 * Met à jour le stock des produits après la création d'une commande
 *
 * Cette fonction récupère tous les produits du panier et diminue leur stock
 * en fonction de la quantité commandée.
 *
 * Parametres :
 *     $pdo L'instance de connexion à la base de données PDO
 *     $idPanier L'ID du panier dont les produits ont été commandés
 *
 * Retourne :
 *     true si la mise à jour s'est déroulée avec succès
 *     false en cas d'erreur
 */
function updateStockAfterOrder($pdo, $idPanier) {
    try {
        // Récupère tous les produits du panier avec leurs quantités
        $sql = "SELECT pap.idProduit, pap.quantiteProduit 
                FROM _produitAuPanier pap 
                WHERE pap.idPanier = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$idPanier]);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Pour chaque produit, diminue le stock de la quantité commandée
        foreach ($items as $item) {
            $updateSql = "UPDATE _produit SET stock = stock - ? WHERE idProduit = ?";
            $updateStmt = $pdo->prepare($updateSql);
            $updateStmt->execute([$item['quantiteProduit'], $item['idProduit']]);
        }
        
        return true;
    } catch (Exception $e) {
        // Enregistre l'erreur dans les logs du serveur
        error_log("Erreur mise à jour stock: " . $e->getMessage());
        return false;
    }
}

/**
 * Enregistre l'adresse de facturation d'un client dans la base de données.
 *
 * Cette fonction vérifie d'abord si l'adresse de livraison existe déjà pour le client donné.
 * Si l'adresse existe, elle retourne l'identifiant de l'adresse existante.
 * Sinon, elle insère la nouvelle adresse de livraison dans la base de données et retourne son identifiant.
 *
 * Paramtres :
 *    $pdo L'objet PDO pour la connexion à la base de données.
 *    $idClient L'identifiant du client.
 *    $adresse L'adresse de livraison.
 *    $codePostal Le code postal de l'adresse.
 *    $ville La ville de l'adresse.
 *
 * Retoune : un tableau contenant le statut de l'opération et l'identifiant de l'adresse de facturation
 * ou un message d'erreur en cas d'échec.
 */
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


/**
 * Fonction permettant de recuperer les informations d'unc client afin de préremplir l'adreesse de livraison
 * 
 * Parametres :
 *    $pdo L'objet PDO pour la connexion à la base de données.
 *    $idClient L'identifiant du client.
 * 
 * Retourne : un tableau associatif contenant l'adresse, le code postal et la ville du client.
 */
function clientInformations($pdo, $idClient) {
    $stmt = $pdo->prepare("SELECT adresse, codePostal, ville FROM _client as c INNER JOIN _adresseClient as ac ON c.idAdresse = ac.idAdresse WHERE c.idAdresse = ? LIMIT 1");
    $stmt->execute([$idClient]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Traitement des requêtes POST pour les actions liées aux commandes et aux adresses de facturation.
 *
 * Vérifie si la méthode de la requête est POST et si l'action est définie dans les données POST.
 * 
 * Les actions prises en charge incluent :
 * - 'createOrder' : Crée une nouvelle commande dans la base de données avec les informations fournies.
 * - 'saveBillingAddress' : Enregistre une nouvelle adresse de facturation pour le client.
 *
 * En cas d'erreur, renvoie un message d'erreur au format JSON.
 *
 * Exception Si une erreur se produit lors du traitement des actions.
 */
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

                $idCommande = createOrderInDatabase($pdo, $idClient, $adresseLivraison, $villeLivraison, $numeroCarte, $codePostal, $nomCarte, $dateExpiration, $cvv, $idAdresseFacturation);
                echo json_encode(['success' => true, 'idCommande' => $idCommande]);
                break;

            case 'saveBillingAddress':
                $adresse = $_POST['adresse'] ?? '';
                $codePostal = $_POST['codePostal'] ?? '';
                $ville = $_POST['ville'] ?? '';
                
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

// Calculer totals
$sousTotal = 0;
$quantiteTotal = 0;
foreach ($cart as $item) {
    $sousTotal += $item['prix'] * $item['qty'];
    $quantiteTotal += $item['qty'];
}
$livraison = 5.99;
$montantTTC = $sousTotal + $livraison;


$clientInfo = clientInformations($pdo, $idClient);
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="../../public/style.css">
    <link rel="icon" href="/public/images/logoBackoffice.svg">
    <title>Paiement - Alizon</title>
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
                            <input type="text" class="adresse-input" placeholder="123 Rue de la Paix" value="<?= htmlspecialchars($clientInfo['adresse']); ?>" required>
                            <span class="error-message" data-for="adresse">Adresse requise</span>
                        </div>
                    </div>

                    <div class="form-row two-cols">
                        <div class="input-group">
                            <label>Code postal</label>
                            <input type="text" class="code-postal-input" placeholder="75001" value="<?= htmlspecialchars($clientInfo['codePostal']) ?>" required>
                            <span class="error-message" data-for="code-postal">Code postal invalide</span>
                        </div>
                        <div class="input-group">
                            <label>Ville</label>
                            <input type="text" class="ville-input" placeholder="Paris" value="<?= htmlspecialchars($clientInfo['ville'])?>" required>
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
                        <h4>Adresse de facturation
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
                        <img class="payment-icon" src="../../public/images/visaLogo.png" alt="Visa">
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
                            J'ai lu et j'accepte les <a href="#">Conditions Générales de
                                Vente</a> et les <a href="#">Mentions Légales</a> d'Alizon.
                        </label>
                    </div>
                    <span class="error-message" data-for="cgv">Vous devez accepter les CGV</span>
                </div>
            </div>

            <aside class="order-summary">
                <h3 class="summary-title">Récapitulatif</h3>

                <div class="cart-items">
                    <?php if (empty($cart)): ?>
                    <p>Votre panier est vide</p>
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
</body>

</html>
<?php
require_once "../../controllers/pdo.php";
session_start();

// ============================================================================
// VÉRIFICATION DE LA CONNEXION
// ============================================================================

if (!isset($_SESSION['user_id'])) {
    // Rediriger vers la page de connexion si non connecté
    header('Location: ../../views/frontoffice/connexionClient.php');
    exit;
}

$idClient = $_SESSION['user_id'];

// ============================================================================
// FONCTIONS DE GESTION DU PANIER
// ============================================================================

function getCurrentCart($pdo, $idClient) {
    // Vérifier d'abord si un panier existe
    $stmt = $pdo->prepare("SELECT idPanier FROM _panier WHERE idClient = ? ORDER BY idPanier DESC LIMIT 1");
    $stmt->execute([$idClient]);
    $panier = $stmt->fetch(PDO::FETCH_ASSOC);

    $cart = [];

    if ($panier) {
        $idPanier = intval($panier['idPanier']); 

        $sql = "SELECT p.idProduit, p.nom, p.prix, pa.quantiteProduit as qty, i.URL as img
                FROM _produitAuPanier pa
                JOIN _produit p ON pa.idProduit = p.idProduit
                LEFT JOIN _imageDeProduit i ON p.idProduit = i.idProduit
                WHERE pa.idPanier = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$idPanier]);
        $cart = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
    } else {
        // Créer un nouveau panier vide si aucun n'existe
        $stmtCreate = $pdo->prepare("INSERT INTO _panier (idClient) VALUES (?)");
        $stmtCreate->execute([$idClient]);
        // Le panier sera vide, mais au moins il existe
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

function createOrderInDatabase($pdo, $idClient, $adresseLivraison, $villeLivraison, $regionLivraison, $numeroCarte, $codePostal = '', $nomCarte = 'Client inconnu', $dateExp = '12/30', $cvv = '000', $idAdresseFacturation = null) {
    try {
        $pdo->beginTransaction();

        $idClient = intval($idClient);

        // Récupération du panier actuel
        $stmt = $pdo->prepare("SELECT * FROM _panier WHERE idClient = ? ORDER BY idPanier DESC LIMIT 1");
        $stmt->execute([$idClient]);
        $panier = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$panier) throw new Exception("Aucun panier trouvé pour ce client.");

        $idPanier = intval($panier['idPanier']);

        // Calcul total
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

        // Vérifier si le panier est vide
        if ($nbArticles === 0) {
            throw new Exception("Le panier est vide.");
        }

        // LES DONNÉES SONT DÉJÀ CHIFFRÉES DEPUIS LE FRONT - on les stocke directement
        $carteQ = $pdo->quote($numeroCarte);
        $cvvQ = $pdo->quote($cvv);

        // Verification existante carte (avec données chiffrées)
        $checkCarte = $pdo->prepare("SELECT numeroCarte FROM _carteBancaire WHERE numeroCarte = ?");
        $checkCarte->execute([$numeroCarte]);

        if ($checkCarte->rowCount() === 0) {
            $sqlInsertCarte = "
                INSERT INTO _carteBancaire (numeroCarte, nom, dateExpiration, cvv)
                VALUES (?, ?, ?, ?)
            ";
            $stmtCarte = $pdo->prepare($sqlInsertCarte);
            if (!$stmtCarte->execute([$numeroCarte, $nomCarte, $dateExp, $cvv])) {
                throw new Exception("Erreur lors de l'ajout de la carte bancaire : " . implode(', ', $stmtCarte->errorInfo()));
            }
        }

        // CRÉATION DE L'ADRESSE DE LIVRAISON (dans _adresse)
        $sqlAdresseLivraison = "
            INSERT INTO _adresse (adresse, region, codePostal, ville, pays, no_appart, lieudit, batiment)
            VALUES (?, ?, ?, ?, 'France', NULL, NULL, NULL)
        ";
        $stmtAdresse = $pdo->prepare($sqlAdresseLivraison);
        if (!$stmtAdresse->execute([$adresseLivraison, $regionLivraison, $codePostal, $villeLivraison])) {
            throw new Exception("Erreur lors de l'ajout de l'adresse de livraison: " . implode(', ', $stmtAdresse->errorInfo()));
        }
        $idAdresseLivraison = $pdo->lastInsertId();

        // UTILISER L'ADRESSE DE FACTURATION SI FOURNIE, SINON UTILISER LA MÊME QUE LA LIVRAISON
        if ($idAdresseFacturation) {
            // Utiliser l'ID de l'adresse de facturation fourni
            $idAdresseFacturation = intval($idAdresseFacturation);
        } else {
            // Si pas d'adresse de facturation spécifique, utiliser la même que la livraison
            // Mais comme c'est une table différente, on doit créer une entrée dans _adresseFacturation
            $sqlInsertFacturation = "
                INSERT INTO _adresseFacturation (idClient, rue, codePostal, ville)
                VALUES (?, ?, ?, ?)
            ";
            $stmtFacturation = $pdo->prepare($sqlInsertFacturation);
            if (!$stmtFacturation->execute([$idClient, $adresseLivraison, $codePostal, $villeLivraison])) {
                throw new Exception("Erreur lors de la création de l'adresse de facturation: " . implode(', ', $stmtFacturation->errorInfo()));
            }
            $idAdresseFacturation = $pdo->lastInsertId();
        }

        // Création de la commande avec les deux adresses
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
            throw new Exception("Erreur lors de la création de la commande : " . implode(', ', $stmtCommande->errorInfo()));
        }

        $idCommande = $pdo->lastInsertId();

        // produits vers _contient
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
            throw new Exception("Erreur lors de la copie des produits : " . implode(', ', $stmtContient->errorInfo()));
        }

        // Vider le panier
        $stmtVider = $pdo->prepare("DELETE FROM _produitAuPanier WHERE idPanier = ?");
        if (!$stmtVider->execute([$idPanier])) {
            throw new Exception("Erreur lors du vidage du panier : " . implode(', ', $stmtVider->errorInfo()));
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
        // CORRECTION : Insérer dans la table _adresseFacturation
        // Vérifier si l'adresse existe déjà dans _adresseFacturation
        $sqlCheck = "SELECT idAdresseFacturation FROM _adresseFacturation 
                    WHERE idClient = ? 
                    AND rue = ? 
                    AND codePostal = ? 
                    AND ville = ?";
        
        $stmt = $pdo->prepare($sqlCheck);
        $stmt->execute([$idClient, $adresse, $codePostal, $ville]);
        
        if ($stmt && $stmt->rowCount() > 0) {
            // Adresse existe déjà
            $existing = $stmt->fetch(PDO::FETCH_ASSOC);
            return [
                'success' => true, 
                'idAdresseFacturation' => $existing['idAdresseFacturation'], 
                'message' => 'Adresse de facturation déjà existante'
            ];
        }

        // Insérer la nouvelle adresse de facturation
        $sqlInsert = "
            INSERT INTO _adresseFacturation (idClient, rue, codePostal, ville)
            VALUES (?, ?, ?, ?)
        ";
        
        $stmtInsert = $pdo->prepare($sqlInsert);
        if (!$stmtInsert->execute([$idClient, $adresse, $codePostal, $ville])) {
            throw new Exception("Erreur lors de l'insertion de l'adresse de facturation: " . implode(', ', $stmtInsert->errorInfo()));
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
                $cvv = $_POST['cvv'] ?? '';
                $codePostal = $_POST['codePostal'] ?? '';
                $nomCarte = $_POST['nomCarte'] ?? 'Client inconnu';
                $dateExpiration = $_POST['dateExpiration'] ?? '12/30';

                // Validation des champs obligatoires
                if (empty($adresseLivraison) || empty($villeLivraison) || empty($regionLivraison) || empty($numeroCarte) || empty($codePostal)) {
                    echo json_encode(['success' => false, 'error' => 'Tous les champs sont obligatoires']);
                    break;
                }

                $idCommande = createOrderInDatabase($pdo, $idClient, $adresseLivraison, $villeLivraison, $regionLivraison, $numeroCarte, $codePostal, $nomCarte, $dateExpiration, $cvv);
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

// ============================================================================
// RÉCUPÉRATION DES DONNÉES POUR LA PAGE
// ============================================================================

// recuperation panier courent
$cart = getCurrentCart($pdo, $idClient);

// Récupération des départements et villes
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
    // Données par défaut si le fichier CSV n'existe pas
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
    <title>Paiement - Alizon</title>
</head>

<body class="pagePaiement">
    <?php include '../../views/frontoffice/partials/headerConnecte.php'; ?>

    <script>
    // DÉFINIR LA CLÉ GLOBALEMENT AVANT TOUT
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
                    <label>
                        <input id="checkboxFactAddr" type="checkbox">
                        Adresse de facturation différente
                    </label>
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
                ?>
                <div class="produit" data-id="<?= htmlspecialchars($item['idProduit']) ?>">
                    <img src="<?= htmlspecialchars($imgProd) ?>" alt="<?= htmlspecialchars($nom) ?>">
                    <div class="infos">
                        <p class="titre"><?= htmlspecialchars($nom) ?></p>
                        <p class="prix"><?= number_format($prix * $qty, 2, ',', '') ?>€</p>
                        <div class="gestQte">
                            <div class="qte">
                                <button class="minus" data-id="<?= htmlspecialchars($item['idProduit']) ?>">-</button>
                                <span class="qty"
                                    data-id="<?= htmlspecialchars($item['idProduit']) ?>"><?= intval($qty) ?></span>
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

    <script src="../../public/amd-shim.js"></script>
    <script src="../../controllers/Chiffrement.js"></script>
    <script src="../../public/script.js"></script>
    <script src="../scripts/frontoffice/paiement-ajax.js"></script>
</body>

</html>
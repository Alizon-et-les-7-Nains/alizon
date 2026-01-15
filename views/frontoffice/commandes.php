<?php
require_once "../../controllers/pdo.php";
require_once "../../controllers/prix.php";
session_start();
ob_start();

$showPopup = false;
$showPopupLivraison = isset($_GET['idCommande']);

if (!empty($_SESSION['commandePayee'])) {
    $showPopup = true;
    unset($_SESSION['commandePayee']);
}

$tabIdDestination = $_SESSION['tabIdDestination'] ?? [];

// ============================================================================
// VÉRIFICATION DE LA CONNEXION
// ============================================================================

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../views/frontoffice/connexionClient.php');
    exit;
}

$idClient = $_SESSION['user_id'];

// ============================================================================
// FONCTION DE RÉCUPÉRATION DES COMMANDES
// ============================================================================

function getCommandes($pdo, $idClient, $filtre){
    $commandes = [];

    $sql = "SELECT c.idCommande, c.dateCommande, c.etatLivraison, c.montantCommandeTTC, 
                   c.montantCommandeHT, c.dateExpedition, c.nomTransporteur, c.idAdresseLivr, c.idAdresseFact, c.numeroCarte
            FROM _commande c
            JOIN _panier p ON c.idPanier = p.idPanier
            WHERE p.idClient = :idClient";

    if ($filtre === 'cours') {
        $sql .= " AND c.etatLivraison NOT IN ('Livrée', 'Annulé')";
    } elseif ($filtre === '2026') {
        $sql .= " AND YEAR(c.dateCommande) = 2026";
    } elseif ($filtre === '2025') {
        $sql .= " AND YEAR(c.dateCommande) = 2025";
    } elseif ($filtre === '2024') {
        $sql .= " AND YEAR(c.dateCommande) = 2024";
    }

    $sql .= " ORDER BY c.dateCommande DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([':idClient' => $idClient]);
    $resultatsCommandes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($resultatsCommandes as $row) {
        $idCommande = $row['idCommande'];

        $sqlProduits = "SELECT v.raisonSocial, p.idProduit, p.nom, co.quantite, i.URL as image
                        FROM _contient co
                        JOIN _produit p ON co.idProduit = p.idProduit
                        LEFT JOIN _imageDeProduit i ON p.idProduit = i.idProduit
                        LEFT JOIN _vendeur v ON v.codeVendeur = p.idVendeur
                        WHERE co.idCommande = :idCommande
                        GROUP BY p.idProduit";

        $stmtProd = $pdo->prepare($sqlProduits);
        $stmtProd->execute([':idCommande' => $idCommande]);
        $produits = $stmtProd->fetchAll(PDO::FETCH_ASSOC);

        $dateCommandeObj = new DateTime($row['dateCommande']);
        $dateCommandeFormatee = $dateCommandeObj->format('d/m/Y');

        $dateLivraisonFormatee = "En attente";
        if (!empty($row['dateExpedition'])) {
            $dateExpObj = new DateTime($row['dateExpedition']);
            $dateLivraisonFormatee = $dateExpObj->format('d/m/Y');
        }

        $commandes[] = [
            'id' => $row['idCommande'],
            'date' => $dateCommandeFormatee,
            'total' => number_format($row['montantCommandeTTC'], 2, ',', ' '),
            'montantHT' => number_format($row['montantCommandeHT'], 2, ',', ' '), // Montant commande TTC
            'statut' => $row['etatLivraison'],
            'dateLivraison' => $dateLivraisonFormatee,
            'transporteur' => $row['nomTransporteur'],
            'produits' => $produits,
            'idAdresseLivr' => $row['idAdresseLivr'],
            'idAdresseFact' => $row['idAdresseFact'],
            'numeroCarte' => $row['numeroCarte'],
        ];
    }

    return $commandes;
}

// ============================================================================
// LOGIQUE D'AFFICHAGE (FILTRES)
// ============================================================================

$filtre = isset($_GET['filtre']) ? $_GET['filtre'] : 'cours';
$commandesAffichees = getCommandes($pdo, $idClient, $filtre);
$nombreCommandes = count($commandesAffichees);

$titreFiltre = "Commandes en cours";
$messageVide = "Aucune commande en cours actuellement.";

if ($filtre === '2026') {
    $titreFiltre = "Commandes 2026";
    $messageVide = "Aucune commande passée en 2026.";
} elseif ($filtre === '2025') {
    $titreFiltre = "Commandes 2025";
    $messageVide = "Aucune commande passée en 2025.";
} elseif ($filtre === '2024') {
    $titreFiltre = "Commandes 2024";
    $messageVide = "Aucune commande passée en 2024.";
}

function getCurrentCart($pdo, $idClient)
{
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

function updateQuantityInDatabase($pdo, $idClient, $idProduit, $delta)
{
    $idProduit = intval($idProduit);
    $idClient = intval($idClient);

    // Récupérer le panier actuel
    $stmtPanier = $pdo->prepare("SELECT idPanier FROM _panier WHERE idClient = ? ORDER BY idPanier DESC LIMIT 1");
    $stmtPanier->execute([$idClient]);
    $panier = $stmtPanier->fetch(PDO::FETCH_ASSOC);

    if (!$panier) {
        // Créer un nouveau panier si nécessaire
        $stmtCreate = $pdo->prepare("INSERT INTO _panier (idClient) VALUES (?)");
        $stmtCreate->execute([$idClient]);
        $idPanier = $pdo->lastInsertId();
    } else {
        $idPanier = $panier['idPanier'];
    }

    // Vérifier si le produit existe déjà dans le panier
    $sql = "SELECT quantiteProduit FROM _produitAuPanier 
            WHERE idProduit = ? AND idPanier = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$idProduit, $idPanier]);
    $current = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($current) {
        // Produit existe : mettre à jour la quantité
        $newQty = max(0, intval($current['quantiteProduit']) + intval($delta));

        if ($newQty > 0) {
            $sql = "UPDATE _produitAuPanier SET quantiteProduit = ? 
                    WHERE idProduit = ? AND idPanier = ?";
            $stmt = $pdo->prepare($sql);
            $success = $stmt->execute([$newQty, $idProduit, $idPanier]);
        } else {
            // Supprimer si quantité = 0
            $sql = "DELETE FROM _produitAuPanier WHERE idProduit = ? AND idPanier = ?";
            $stmt = $pdo->prepare($sql);
            $success = $stmt->execute([$idProduit, $idPanier]);
        }
    } else {
        // Produit n'existe pas : l'ajouter si delta > 0
        if ($delta > 0) {
            $sql = "INSERT INTO _produitAuPanier (idProduit, idPanier, quantiteProduit) VALUES (?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $success = $stmt->execute([$idProduit, $idPanier, $delta]);
        } else {
            $success = false;
        }
    }

    return $success;

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

?>

<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="../../public/style.css">
        <link rel="icon" href="../../public/images/logoBackoffice.svg">
        <title>Alizon - Mes Commandes</title>
    </head>
<body class="pageCommandes">
    <?php if (!$showPopupLivraison): ?>
        <?php include '../../views/frontoffice/partials/headerConnecte.php'; ?>
    <?php endif; ?>

    <main>
        <section class="topRecherche">
            <h1>Vos commandes</h1>
            <input class="supprElem" type="search" name="rechercheCommande" id="rechercheCommande"
                placeholder="Rechercher une commande">
        </section>

        <section class="filtreRecherche">
            <p><?php echo $nombreCommandes; ?></p>
            <p>commande<?php echo $nombreCommandes > 1 ? 's' : ''; ?></p>

            <select name="typeFiltrage" id="typeFiltrage" onchange="window.location.href='?filtre=' + this.value">
                <option value="cours" <?php echo $filtre === 'cours' ? 'selected' : ''; ?>>En cours</option>
                <option value="2026" <?php echo $filtre === '2026' ? 'selected' : ''; ?>>2026</option>
                <option value="2025" <?php echo $filtre === '2025' ? 'selected' : ''; ?>>2025</option>
                <option value="2024" <?php echo $filtre === '2024' ? 'selected' : ''; ?>>2024</option>
            </select>
        </section>

        <?php if ($nombreCommandes === 0): ?>
            <section class="messageVide" style="text-align: center; padding: 60px 20px; font-size: 20px; color: #1e3a8a;">
                <p><?php echo $messageVide; ?></p>
            </section>
        <?php else: ?>
            <?php foreach ($commandesAffichees as $commande): ?>
                <section class="commande">
                    <?php
                    $nombreProduits = count($commande['produits']);
                    echo "<script>console.log(" . json_encode($commande) . ");</script>";
                    echo "<script>console.log(" . json_encode($commandesAffichees) . ");</script>";
                    foreach ($commande['produits'] as $index => $produit):
                        $imgSrc = !empty($produit['image']) ? htmlspecialchars($produit['image']) : '../../public/images/defaultImageProduit.png';
                        ?>
                        <section class="produit <?php echo ($index === $nombreProduits - 1) ? 'dernierProduit' : ''; ?>">
                            <div class="containerImg">
                                <a href="../../views/frontoffice/produit.php?id=<?= $produit['idProduit'] ?>"><img
                                        src="<?php echo $imgSrc; ?>" class="imgProduit"
                                        alt="<?php echo htmlspecialchars($produit['nom']); ?>"></a>
                                <div class="infoProduit">
                                    <h2><?php echo htmlspecialchars($produit['nom']); ?></h2>
                                    <ul>
                                        <li>Quantité : <?php echo $produit['quantite']; ?></li>
                                        <li>Vendu par <?php echo $produit['raisonSocial']; ?></li>
                                    </ul>

                                    <div
                                        class="statutCommande <?php echo $commande['statut'] === 'Livrée' ? 'livre' : 'enCours'; ?>">
                                        <?php if ($commande['statut'] === 'Livrée'): ?>
                                            <p>Livrée le <?php echo $commande['dateLivraison']; ?></p>
                                        <?php else: ?>
                                            <p><?php echo htmlspecialchars($commande['statut']); ?></p>
                                            <a href="commandes.php?idCommande=<?= $commande['id'] ?>">Suivre (<?php echo htmlspecialchars($commande['transporteur']); ?>) <img src="../../public/images/truckWhite.svg" alt="Icône"></a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <div class="listeBtn">
                                <a href="<?php echo "../../views/frontoffice/ecrireCommentaire.php?id=".$produit['idProduit'] ?>">Écrire un commentaire <img src="../../public/images/penDarkBlue.svg" alt="Edit"></a>
                                <button class="plus" data-id="<?= htmlspecialchars($produit['idProduit'] ?? '') ?>">Acheter à nouveau <img src="../../public/images/redoWhite.svg" alt="Image redo"></button>
                                <?php if ($commande['statut'] === 'Livrée'): ?>
                                    <a href="">Retourner<img src="../../public/images/redoDarkBlue.svg" alt="Retour"></a>
                                    <?php else: ?>
                                    <a href="">Annuler<img src="../../public/images/redoDarkBlue.svg" alt="Annuler"></a>
                                <?php endif; ?>
                            </div>
                        </section>
                    <?php endforeach; ?>

                    <section class="footerCommande">
                        <div class="infoCommande">
                            <p class="supprElem">Commande effectuée le</p>
                            <p class="supprElem"><?php echo $commande['date']; ?></p>
                        </div>
                        <div class="infoCommande">
                            <p>Total</p>
                            <p><?php echo $commande['total']; ?> €</p>
                        </div>
                        <div class="infoCommande">
                            <p>N° de commande</p>
                            <p>#<?php echo $commande['id']; ?></p>
                        </div>
                        <div class="liensCommande">

                            <?php 
                                
                                $sql = "SELECT *
                                FROM _adresseClient a
                                WHERE a.idAdresse = :idAdresse";

                                $stmt = $pdo->prepare($sql);
                                $stmt->execute([':idAdresse' => $commande['idAdresseFact']]);
                                $resultatAdresseFacturation = $stmt->fetch(PDO::FETCH_ASSOC);

                                if(!$resultatAdresseFacturation['complementAdresse']) {
                                    $complement = "";
                                } else {
                                    $complement = $resultatAdresseFacturation['complementAdresse'];
                                }
                            
                                $adresseFacturation = $resultatAdresseFacturation['adresse'] . ", " . $resultatAdresseFacturation['codePostal'] . " " . $resultatAdresseFacturation['ville'] . $complement;

                                $sql = "SELECT *
                                        FROM _adresseLivraison a
                                        WHERE a.idAdresseLivraison = :idAdresse";

                                $stmt = $pdo->prepare($sql);
                                $stmt->execute([':idAdresse' => $commande['idAdresseLivr']]);
                                $resultatAdresseLivraison = $stmt->fetch(PDO::FETCH_ASSOC);

                                $adresseLivraison = $resultatAdresseLivraison['adresse'] . ", " . $resultatAdresseLivraison['codePostal'] . " " . $resultatAdresseLivraison['ville'];

                                $sqlCarte = "SELECT nom FROM _carteBancaire WHERE numeroCarte = :numeroCarte";
                                $stmtCarte = $pdo->prepare($sqlCarte);
                                $stmtCarte->execute([':numeroCarte' => $commande['numeroCarte']]);
                                $nomCarte = $stmtCarte->fetch(PDO::FETCH_ASSOC);

                            ?>

                            <a onclick="popUpDetailsCommande('<?= $commande['id'] ?>', '<?= $commande['date'] ?>', '<?= addslashes($adresseFacturation) ?>', '<?= addslashes($adresseLivraison) ?>', '<?= $commande['statut'] ?>', '<?= $commande['transporteur'] ?>', '<?= $commande['montantHT'] ?>', '<?= $commande['total'] ?>', '<?= $nomCarte['nom'] ?>')" href="#">Détails</a>
                            <span class="supprElem">|</span>
                            <a href="../../controllers/facture.php?id= <?php echo ($commande['id']); ?>">Facture</a>
                        </div>
                    </section>
                </section>
            <?php endforeach; ?>
        <?php endif; ?>

        <section class="confirmationAjout">
            <h4>Produit ajouté au panier !</h4>
        </section>

        <?php require_once '../backoffice/partials/retourEnHaut.php' ?>
        <?php include '../../views/frontoffice/partials/footerConnecte.php'; ?>

    </main>

    <script>
        const popupConfirmation = document.querySelector(".confirmationAjout");
        const boutonsAjout = document.querySelectorAll(".plus");

        boutonsAjout.forEach(btn => {
            btn.addEventListener("click", function (e) {

                // Afficher le popup
                popupConfirmation.style.display = "block";

                // Cacher après 1,5 secondes
                setTimeout(() => {
                    popupConfirmation.style.display = "none";
                }, 5000);
            });
        });
    </script>

    <script src="../scripts/frontoffice/paiement-ajax.js"></script>
    <script src="../../public/amd-shim.js"></script>
    <script src="/public/script.js"></script>
    <script src="../scripts/frontoffice/detailsCommande.js"></script>

    

    <?php if ($showPopup): ?>
        <?php
            $sql = "SELECT noBordereau FROM _commande WHERE idCommande = :idCommande";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([":idCommande" => $tabIdDestination[0]["idCommande"]]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
        ?>
        <div class="overlay">
            <div class="popup">
                <p>idCommande</p>
                <p><?php echo htmlspecialchars($tabIdDestination[0]["idCommande"]) ?></p>
                <p>destination</p>
                <p><?php echo htmlspecialchars($tabIdDestination[0]["destination"]) ?></p>
                <p>Numéro de bordereau</p>
                <p><?php echo htmlspecialchars($result['noBordereau']) ?></p>
                <a href="./commandes.php" class="close">Fermer</a>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($showPopupLivraison): ?>
        <?php
            $sql = "SELECT etape FROM _commande WHERE idCommande = :idCommande";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([":idCommande" => $idCommande]);
            $etape = $stmt->fetch(PDO::FETCH_ASSOC);
        ?>
        <div id="popupLivraison" class="overlay">
            <div class="popup">
                <div class="croixFermerLaPage">
                    <div></div>
                    <div></div>
                </div> 
                <h2>Suivi de la livraison</h2>
                <div class="popup-content">

                    <?php
                        $sql = "SELECT nom, description, URL FROM _commande inner join _contient on _commande.idCommande = _contient.idCommande inner join _produit on _produit.idProduit = _contient.idProduit INNER JOIN _imageDeProduit on _produit.idProduit = _imageDeProduit.idProduit WHERE _commande.idCommande = :idCommande";
                        $stmt = $pdo->prepare($sql);
                        $stmt->execute([":idCommande" => $idCommande]);
                        $produits = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    ?>
                    <?php foreach ($produits as $produit): ?>
                        <div class="recapProduit">
                            <img src="<?= htmlspecialchars($produit['URL']) ?>" alt="Image du produit">
                            <div class="nomEtDescription">
                                <h3><?= htmlspecialchars($produit['nom']) ?></h3>
                                <p><?= htmlspecialchars($produit['description']) ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    </div>
                <div class="stepper">
                    <p>En cours de préparation</p>
                    <p>Prise en charge du colis</p>
                    <p>Arrivé à la plateforme Régional</p>
                    <p>Arrivé à la plateforme local</p>
                    <p>Colis livré</p>
                    <div class="rond"></div>
                    <div class="trait">
                        <div class="demiTrait"></div>
                    </div>
                    <div class="rond"></div>
                    <div class="trait">
                        <div class="demiTrait"></div>
                    </div>
                    <div class="rond"></div>
                    <div class="trait">
                        <div class="demiTrait"></div>
                    </div>
                    <div class="rond"></div>
                    <div class="trait">
                        <div class="demiTrait"></div>
                    </div>
                    <div class="rond"></div>
                </div>
            </div>
        </div>
    <?php endif; ?>
    <script>const etape = <?php echo json_encode($etape['etape']); ?>;</script>
    <script src="../scripts/frontoffice/popupSuivieCommande.js"></script>
</body>
</html>
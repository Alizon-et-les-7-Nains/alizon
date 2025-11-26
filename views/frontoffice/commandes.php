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
// FONCTION DE RÉCUPÉRATION DES COMMANDES
// ============================================================================

function getCommandes($pdo, $idClient, $filtre) {
    $commandes = [];
    
    $sql = "SELECT c.idCommande, c.dateCommande, c.etatLivraison, c.montantCommandeTTC, 
                   c.dateExpedition, c.nomTransporteur
            FROM _commande c
            JOIN _panier p ON c.idPanier = p.idPanier
            WHERE p.idClient = :idClient";

    if ($filtre === 'cours') {
        $sql .= " AND c.etatLivraison NOT IN ('Livrée', 'Annulé')";
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
        
        $sqlProduits = "SELECT p.idProduit, p.nom, co.quantite, i.URL as image
                        FROM _contient co
                        JOIN _produit p ON co.idProduit = p.idProduit
                        LEFT JOIN _imageDeProduit i ON p.idProduit = i.idProduit
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
            'statut' => $row['etatLivraison'], 
            'dateLivraison' => $dateLivraisonFormatee,
            'transporteur' => $row['nomTransporteur'],
            'produits' => $produits
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

// Textes dynamiques selon le filtre
$titreFiltre = "Commandes en cours";
$messageVide = "Aucune commande en cours actuellement.";

if ($filtre === '2025') {
    $titreFiltre = "Commandes 2025";
    $messageVide = "Aucune commande passée en 2025.";
} elseif ($filtre === '2024') {
    $titreFiltre = "Commandes 2024";
    $messageVide = "Aucune commande passée en 2024.";
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../public/style.css">
    <link rel="icon" href="/public/images/logoBackoffice.svg">
    <title>Alizon - Mes Commandes</title>
</head>
<body class="pageCommandes">
    <?php include '../../views/frontoffice/partials/headerConnecte.php'; ?>

    <main>
        <section class="topRecherche">
            <h1>Vos commandes</h1>
            <input class="supprElem" type="search" name="rechercheCommande" id="rechercheCommande" placeholder="Rechercher une commande">
        </section>
        
        <section class="filtreRecherche">
            <p><?php echo $nombreCommandes; ?></p>
            <p>commande<?php echo $nombreCommandes > 1 ? 's' : ''; ?></p>
            
            <select name="typeFiltrage" id="typeFiltrage" onchange="window.location.href='?filtre=' + this.value">
                <option value="cours" <?php echo $filtre === 'cours' ? 'selected' : ''; ?>>En cours</option>
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

                    // if ($nombreProduits === 0) {
                    //     echo "<div style='padding:20px;'>Détails des produits indisponibles</div>";
                    // }
                    
                    foreach ($commande['produits'] as $index => $produit): 
                        $imgSrc = !empty($produit['image']) ? htmlspecialchars($produit['image']) : '../../public/images/defaultImageProduit.png';
                    ?>
                        <section class="produit <?php echo ($index === $nombreProduits - 1) ? 'dernierProduit' : ''; ?>">
                            <div class="containerImg">
                                <img src="<?php echo $imgSrc; ?>" alt="<?php echo htmlspecialchars($produit['nom']); ?>">
                                <div class="infoProduit">
                                    <h2><?php echo htmlspecialchars($produit['nom']); ?></h2>
                                    <ul>
                                        <li>Quantité : <?php echo $produit['quantite']; ?></li>
                                        <li>Vendu par Alizon</li>
                                    </ul>
                                    
                                    <div class="statutCommande <?php echo $commande['statut'] === 'Livrée' ? 'livre' : 'enCours'; ?>">
                                        <?php if ($commande['statut'] === 'Livrée'): ?>
                                            <p>Livrée le <?php echo $commande['dateLivraison']; ?></p>
                                        <?php else: ?>
                                            <p><?php echo htmlspecialchars($commande['statut']); ?></p>
                                            <a href="#">Suivre (<?php echo htmlspecialchars($commande['transporteur']); ?>) <img src="../../public/images/truckWhite.svg" alt="Icône"></a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="listeBtn">
                                <a href="">Écrire un commentaire <img src="../../public/images/penDarkBlue.svg" alt="Edit"></a>
                                <a href="../../views/frontoffice/produit.php?id=<?= $produit['idProduit'] ?>">Acheter à nouveau <img src="../../public/images/redoWhite.svg" alt="Redo"></a>
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
                            <a class="supprElem" href="#">Détails</a>
                            <span class="supprElem">|</span>
                            <a href="#">Facture</a>
                        </div>
                    </section>
                </section>
            <?php endforeach; ?>
        <?php endif; ?>

        <?php require_once '../backoffice/partials/retourEnHaut.php' ?>
    </main>
</body>
</html>
<?php
session_start();
require_once __DIR__ . '/../../controllers/pdo.php';

//focntion pour créer un numéro de commande unique
function creerNoCommande($pdo)
{
    do {
        $idCommande = "";
        for ($i = 0; $i < 6; $i++) {
            $idCommande .= rand(0, 9);
        }

        $stmt = $pdo->prepare("SELECT 1 FROM _commande WHERE idCommande = ?");
        $stmt->execute([$idCommande]);
        $exists = $stmt->fetchColumn();

    } while ($exists);

    return $idCommande;
}

//fonction pour mettre à jour le stock après une commande
function updateStockAfterOrder($pdo, $idPanier)
{
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

function chiffrerCodeCarte($code) {
    return password_hash($code, PASSWORD_DEFAULT);
}

//fonction principale pour créer une commande
//qui retourne l'id de la commande créée
//avec toutes les informations nécessaires
function createOrderInDatabase($pdo, $idClient, $adresseLivraison, $villeLivraison, $numeroCarte, $codePostal, $nomCarte, $dateExp, $cvv, $idAdresseFacturation = null)
{
    try {
        // Commencer une transaction
        $pdo->beginTransaction();
        $idClient = intval($idClient);

        // Créer un numéro de commande unique avec la fonction dédiée
        $idCommande = creerNoCommande($pdo);

        // Récupérer le panier actif du client
        $stmt = $pdo->prepare("SELECT * FROM _panier WHERE idClient = ? ORDER BY idPanier DESC LIMIT 1");
        $stmt->execute([$idClient]);
        $panier = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$panier)
            throw new Exception("Aucun panier trouvé pour ce client.");
        $idPanier = intval($panier['idPanier']);

        // Calculer le sous-total et le nombre d'articles
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

        $numeroCarteHash = chiffrerCodeCarte($numeroCarte);

        $checkCarte = $pdo->prepare("SELECT numeroCarte FROM _carteBancaire WHERE numeroCarte = ?");
        $checkCarte->execute([password_verify($numeroCarte, $numeroCarteHash) ? $numeroCarteHash : '']);

        if ($checkCarte->rowCount() === 0) {
            $sqlInsertCarte = "
                    INSERT INTO _carteBancaire (numeroCarte, nom, dateExpiration, cvv)
                    VALUES (?, ?, ?, ?)
                ";
            $stmtCarte = $pdo->prepare($sqlInsertCarte);
            if (!$stmtCarte->execute([$numeroCarteHash, $nomCarte, $dateExp, $cvv])) {
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


        if (empty($idAdresseFacturation)) {
            $stmtAdresseClient = $pdo->prepare("
                    SELECT idAdresse 
                    FROM _client 
                    WHERE idClient = ?
                ");
            $stmtAdresseClient->execute([$idClient]);
            $clientAdresse = $stmtAdresseClient->fetch(PDO::FETCH_ASSOC);

            if (!$clientAdresse || !$clientAdresse['idAdresse']) {
                throw new Exception("Aucune adresse de facturation trouvée pour ce client.");
            }
            $idAdresseFacturation = intval($clientAdresse['idAdresse']);
        } else {
            $idAdresseFacturation = intval($idAdresseFacturation);
            $checkFact = $pdo->prepare("SELECT idAdresse FROM _adresseClient WHERE idAdresse = ?");
            $checkFact->execute([$idAdresseFacturation]);
            if ($checkFact->rowCount() === 0) {
                $stmtAdresseClient = $pdo->prepare("
                        SELECT idAdresse 
                        FROM _client 
                        WHERE idClient = ?
                    ");
                $stmtAdresseClient->execute([$idClient]);
                $clientAdresse = $stmtAdresseClient->fetch(PDO::FETCH_ASSOC);

                if (!$clientAdresse || !$clientAdresse['idAdresse']) {
                    throw new Exception("Aucune adresse de facturation valide trouvée.");
                }
                $idAdresseFacturation = intval($clientAdresse['idAdresse']);
            }
        }

        $montantHT = $sousTotal;
        $montantTTC = $sousTotal;

        //On insère les données dans la table _commande
        $sqlCommande = "
                INSERT INTO _commande (
                    idCommande, dateCommande, etatLivraison, montantCommandeTTC, montantCommandeHt,
                    quantiteCommande, nomTransporteur, dateExpedition,
                    idAdresseLivr, idAdresseFact, numeroCarte, idPanier
                ) VALUES (
                    ?, NOW(), 'En préparation', ?, ?,
                    ?, 'Colissimo', NULL,
                    ?, ?, ?, ?
                )
            ";

        $stmtCommande = $pdo->prepare($sqlCommande);
        if (!$stmtCommande->execute([$idCommande, $montantTTC, $montantHT, $nbArticles, $idAdresseLivraison, $idAdresseFacturation, $numeroCarteHash, $idPanier])) {
            throw new Exception("Erreur lors de la création de la commande.");
        }

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

$_SESSION['commandePayee'] = true;
//On récupère l'id du client
$idClient = $_SESSION['user_id'];


if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    //On récupère toutes les informations de la commande qui vient d'être passé
    $adresseLivraison = $_POST['adresseLivraison'] ?? '';
    $villeLivraison = $_POST['villeLivraison'] ?? '';
    $codePostal = $_POST['codePostal'] ?? '';
    $numeroCarte = $_POST['numeroCarte'] ?? '';
    $nomCarte = $_POST['nomCarte'] ?? 'Client inconnu';
    $dateExp = $_POST['dateExpiration'] ?? '12/30';
    $cvv = $_POST['cvv'] ?? '000';

    $idAdresseFacturation = !empty($_POST['idAdresseFacturation']) ? $_POST['idAdresseFacturation'] : null;

    //Et on créer un id de commande unique qui est lié aux info qu'on vient de recevoir
    $idCommande = createOrderInDatabase(
        $pdo,
        $idClient,
        $adresseLivraison,
        $villeLivraison,
        $numeroCarte,
        $codePostal,
        $nomCarte,
        $dateExp,
        $cvv,
        $idAdresseFacturation
    );

    //On récupère l'id de la commande qu'on vient de passer ainsi que la destination 
    //Pour pouvoir intéragir avec le service
    $stmt = $pdo->prepare('
            SELECT idCommande, CONCAT(adresse, ", " ,codePostal, " ", ville) AS destination
            FROM _commande
            INNER JOIN _adresseLivraison ON idAdresseLivr = idAdresseLivraison
            WHERE idCommande = :idCommande
        ');
    $stmt->execute([':idCommande' => $idCommande]);
    $tab = $stmt->fetchAll(PDO::FETCH_ASSOC);

    //On stock ces informations dans la sesssion
    $_SESSION['tabIdDestination'] = $tab;
}   

//On inclu le fichier qui communique avec le service
include "clientSocketCreation.php";

?>
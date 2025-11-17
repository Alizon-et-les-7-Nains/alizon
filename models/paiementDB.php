<?php
require_once "../controllers/pdo.php";

class PaymentDB {
    private $pdo;
    private $idClient;

    public function __construct($idClient) {
        $this->pdo = $GLOBALS['pdo'];
        $this->idClient = $idClient;
    }

    // Récupérer le panier depuis la base de données
    public function getCart() {
        $sql = "
            SELECT 
                p.idProduit,
                p.nom,
                p.prix,
                pap.quantiteProduit as qty,
                p.stock,
                COALESCE(img.URL, '/images/default.png') as img,
                c.nomCategorie
            FROM _produitAuPanier pap
            JOIN _produit p ON pap.idProduit = p.idProduit
            JOIN _categorie c ON p.idCategorie = c.idCategorie
            LEFT JOIN _imageDeProduit idp ON p.idProduit = idp.idProduit
            LEFT JOIN _image img ON idp.URL = img.URL
            WHERE pap.idPanier IN (
                SELECT idPanier FROM _panier WHERE idClient = ?
            )
        ";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$this->idClient]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Mettre à jour la quantité d'un produit dans le panier
    public function updateQuantity($idProduit, $delta) {
        // Récupérer la quantité actuelle
        $sql = "SELECT quantiteProduit FROM _produitAuPanier 
                WHERE idProduit = ? AND idPanier IN (
                    SELECT idPanier FROM _panier WHERE idClient = ?
                )";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$idProduit, $this->idClient]);
        $current = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($current) {
            $newQty = max(0, $current['quantiteProduit'] + $delta);
            
            if ($newQty > 0) {
                // Mettre à jour la quantité
                $sql = "UPDATE _produitAuPanier SET quantiteProduit = ? 
                        WHERE idProduit = ? AND idPanier IN (
                            SELECT idPanier FROM _panier WHERE idClient = ?
                        )";
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute([$newQty, $idProduit, $this->idClient]);
            } else {
                // Supprimer le produit si quantité = 0
                $this->removeFromCart($idProduit);
            }
            
            // Mettre à jour les totaux du panier
            $this->updateCartTotals();
            return true;
        }
        return false;
    }

    // Supprimer un produit du panier
    public function removeFromCart($idProduit) {
        $sql = "DELETE FROM _produitAuPanier 
                WHERE idProduit = ? AND idPanier IN (
                    SELECT idPanier FROM _panier WHERE idClient = ?
                )";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$idProduit, $this->idClient]);
        
        $this->updateCartTotals();
        return $stmt->rowCount() > 0;
    }

    // Mettre à jour les totaux du panier
    private function updateCartTotals() {
        $sql = "
            UPDATE _panier 
            SET 
                nbArticles = (
                    SELECT COALESCE(SUM(quantiteProduit), 0) 
                    FROM _produitAuPanier 
                    WHERE idPanier = _panier.idPanier
                ),
                sousTotal = (
                    SELECT COALESCE(SUM(p.prix * pap.quantiteProduit), 0) 
                    FROM _produitAuPanier pap
                    JOIN _produit p ON pap.idProduit = p.idProduit
                    WHERE pap.idPanier = _panier.idPanier
                )
            WHERE idClient = ?
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$this->idClient]);
    }

    // Créer une commande
    public function createOrder($adresseLivraison, $villeLivraison, $regionLivraison, $numeroCarte) {
        try {
            $this->pdo->beginTransaction();

            // Récupérer le panier actuel
            $panier = $this->getCurrentPanier();
            if (!$panier) {
                throw new Exception("Panier non trouvé");
            }

            // Créer la commande
            $sql = "
                INSERT INTO _commande 
                (dateCommande, etatLivraison, montantCommandeTTC, montantCommandeHt, 
                 quantiteCommande, adresseLivr, villeLivr, regionLivr, numeroCarte, idPanier)
                VALUES (NOW(), 'En attente', ?, ?, ?, ?, ?, ?, ?, ?)
            ";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                $panier['sousTotal'] * 1.20, // TTC (20% TVA)
                $panier['sousTotal'], // HT
                $panier['nbArticles'],
                $adresseLivraison,
                $villeLivraison,
                $regionLivraison,
                $numeroCarte,
                $panier['idPanier']
            ]);

            $idCommande = $this->pdo->lastInsertId();

            // Copier les produits du panier vers la table contient
            $sql = "
                INSERT INTO _contient (idProduit, idCommande, prixProduitHt, tauxTva, quantite)
                SELECT pap.idProduit, ?, p.prix, t.pourcentageTva, pap.quantiteProduit
                FROM _produitAuPanier pap
                JOIN _produit p ON pap.idProduit = p.idProduit
                JOIN _tva t ON p.typeTva = t.typeTva
                WHERE pap.idPanier = ?
            ";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$idCommande, $panier['idPanier']]);

            // Vider le panier après commande
            $this->clearCart($panier['idPanier']);

            $this->pdo->commit();
            return $idCommande;

        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    private function getCurrentPanier() {
        $sql = "SELECT * FROM _panier WHERE idClient = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$this->idClient]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    private function clearCart($idPanier) {
        $sql = "DELETE FROM _produitAuPanier WHERE idPanier = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$idPanier]);
    }
}
?>
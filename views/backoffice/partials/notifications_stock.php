<?php
// On récupère l'ID du vendeur depuis la session
$idVendeur = $_SESSION['id'] ?? 0;

if ($idVendeur > 0) {
    // 1. Récupérer les produits en alerte (stock <= seuil)
    // On part du principe que la table est '_produit'
    $sqlStock = "SELECT idProduit, nom, stock, seuilAlerte 
                 FROM _produit 
                 WHERE idVendeur = :idVendeur AND stock <= seuilAlerte";

    $stmtStock = $pdo->prepare($sqlStock);
    $stmtStock->execute([':idVendeur' => $idVendeur]);
    $produitsEnAlerte = $stmtStock->fetchAll(PDO::FETCH_ASSOC);

    // 2. Boucler sur chaque produit pour générer la notification
    foreach ($produitsEnAlerte as $p) {
        $idProd = $p['idProduit'];
        $nomProd = $p['nom'];
        $stockActuel = $p['stock'];
        
        // On définit un titre et un contenu unique par produit
        $titreAlerte = "Alerte Stock : " . $nomProd;
        $contenu = "Le produit $nomProd est presque épuisé ($stockActuel restant). [ID:$idProd]";

        // 3. VÉRIFICATION : Existe-t-il déjà une notif pour ce produit et ce vendeur ?
        // On utilise LIKE avec l'ID du produit pour être sûr
        $check = $pdo->prepare("SELECT COUNT(*) FROM _notification 
                                WHERE idClient = :idVendeur 
                                AND est_vendeur = 1 
                                AND contenuNotif LIKE :pattern");
        
        $check->execute([
            ':idVendeur' => $idVendeur,
            ':pattern' => "%[ID:$idProd]%"
        ]);

        // 4. INSERTION : Si aucune notification n'existe pour ce produit
        if ($check->fetchColumn() == 0) {
            $ins = $pdo->prepare("INSERT INTO _notification (idClient, titreNotif, contenuNotif, dateNotif, est_vendeur) 
                                 VALUES (:idVendeur, :titre, :contenu, NOW(), 1)");
            
            $ins->execute([
                ':idVendeur' => $idVendeur,
                ':titre'     => $titreAlerte,
                ':contenu'   => $contenu
            ]);
        }
    }
}

// Suite de votre code (affichage, etc.)
?>
<?php
// =========================================================
// GÉNÉRATION DES NOTIFICATIONS
// =========================================================
$idVendeur = $_SESSION['id'] ?? 0;

if ($idVendeur > 0) {
    // Récupére les produits en stock faible
    $sqlLowStock = file_get_contents(__DIR__ . '/../../../queries/backoffice/stockFaible.sql');
    $stmtLow = $pdo->prepare($sqlLowStock);
    $stmtLow->execute([':idVendeur' => $idVendeur]);
    $produitsAlerte = $stmtLow->fetchAll(PDO::FETCH_ASSOC);

    foreach ($produitsAlerte as $prod) {
        $idProd = $prod['idProduit'];
        $nomProd = $prod['nom'];
        $stockActuel = $prod['stock'];
        
        // Vérifie si une notification existe déjà
        $checkSql = "SELECT COUNT(*) FROM _notification 
                     WHERE idClient = :idVendeur 
                     AND est_vendeur = 1 
                     AND contenuNotif LIKE :pattern";
        $checkStmt = $pdo->prepare($checkSql);
        $checkStmt->execute([
            ':idVendeur' => $idVendeur,
            ':pattern' => "%ID:$idProd%"
        ]);

        // Si elle n'existe pas, on l'insère
        if ($checkStmt->fetchColumn() == 0) {
            $insertSql = "INSERT INTO _notification (idClient, titreNotif, contenuNotif, dateNotif, est_vendeur) 
                          VALUES (:idVendeur, 'Alerte Stock Faible', :contenu, NOW(), 1)";
            $insStmt = $pdo->prepare($insertSql);
            $insStmt->execute([
                ':idVendeur' => $idVendeur,
                ':contenu' => "Le stock de '$nomProd' est faible ($stockActuel restant). Réassort nécessaire ! "
            ]);
        }
    }
}

// ===============================
//  Affichage des notifs
// ===============================
$hideNotif = false;

if (isset($_GET['reassort_id'])) {
    $hideNotif = true;
}

if (!empty($_SESSION['hide_notif'])) {
    unset($_SESSION['hide_notif']);
    $hideNotif = true;
}

if ($hideNotif) {
    return;
}

$idVendeur = $_SESSION['id'] ?? 0;

$sql = "
    SELECT idNotif, titreNotif, contenuNotif 
    FROM _notification 
    WHERE idClient = :idVendeur 
    AND est_vendeur = 1 
    ORDER BY dateNotif DESC
";

$notifSTMT = $pdo->prepare($sql);
$notifSTMT->execute([':idVendeur' => $idVendeur]);
$notifications = $notifSTMT->fetchAll(PDO::FETCH_ASSOC);

if (!empty($notifications)):
?>
<div id="stock-notifications-container">
    <?php foreach ($notifications as $notif): 
        preg_match('/ID:(\d+)/', $notif['contenuNotif'], $matches);
        $idProduit = $matches[1] ?? 0;
    ?>
        <a href="/views/backoffice/stocks.php?reassort_id=<?= (int)$idProduit ?>&idNotif=<?= (int)$notif['idNotif'] ?>"
           class="stock-notif">
            <img src="/public/images/infoDark.svg" alt="Alerte">
            <div>
                <p><strong><?= htmlspecialchars($notif['titreNotif']) ?></strong></p>
                <p><?= htmlspecialchars($notif['contenuNotif']) ?></p>
            </div>
        </a>
    <?php endforeach; ?>
</div>

<style>
#stock-notifications-container {
    position: fixed;
    top: 20px;
    left: 20px;
    z-index: 9999;
    display: flex;
    flex-direction: column;
    gap: 10px;
    max-width: 320px;
    pointer-events: none;
}

.stock-notif {
    pointer-events: auto;
    background: #fff;
    border-left: 5px solid #d9534f;
    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
    padding: 12px;
    text-decoration: none;
    color: #333;
    display: flex;
    align-items: center;
    gap: 12px;
    border-radius: 4px;
    animation: slideIn 0.4s ease-out;
    transition: transform 0.2s, background 0.2s;
}

.stock-notif:hover {
    background: #fcfcfc;
    transform: translateX(5px);
}

.stock-notif img {
    width: 24px;
    flex-shrink: 0;
}

.stock-notif p {
    margin: 0;
    font-size: 0.85em;
    line-height: 1.3;
}

.stock-notif strong {
    color: #d9534f;
}

@keyframes slideIn {
    from { opacity: 0; transform: translateX(-100%); }
    to { opacity: 1; transform: translateX(0); }
}
</style>
<?php endif; ?>

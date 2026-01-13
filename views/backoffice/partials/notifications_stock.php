<?php
$sqlPath = __DIR__ . '/../../../queries/backoffice/stockFaible.sql';
$sqlContent = file_get_contents($sqlPath);

if ($sqlContent !== false) {
    $notifSTMT = $pdo->prepare($sqlContent);
    $idVendeur = $_SESSION['id'] ?? 0;
    $notifSTMT->execute([':idVendeur' => $idVendeur]);
    $produitsAlerte = $notifSTMT->fetchAll(PDO::FETCH_ASSOC);

    if (!empty($produitsAlerte)): ?>
        <div id="stock-notifications-container">
            <?php foreach ($produitsAlerte as $produit): ?>
                <a href="/views/backoffice/stocks.php?reassort_id=<?php echo $produit['idProduit']; ?>" class="stock-notif">
                    <img src="/public/images/infoDark.svg" alt="Alerte">
                    <p>Le produit <strong><?php echo htmlspecialchars($produit['nom']); ?></strong> est à <strong><?php echo $produit['stock']; ?></strong> unités. Réassort nécessaire !</p>
                </a>
            <?php endforeach; ?>
        </div>
        
        <style>
            #stock-notifications-container {
                position: fixed;
                top: 80px;
                left: 20px;
                z-index: 9999;
                display: flex;
                flex-direction: column;
                gap: 10px;
                max-width: 320px;
            }
            .stock-notif {
                background: #fff;
                border-left: 5px solid #d9534f;
                box-shadow: 0 4px 15px rgba(0,0,0,0.2);
                padding: 15px;
                text-decoration: none;
                color: #333;
                display: flex;
                align-items: center;
                gap: 12px;
                border-radius: 4px;
                animation: slideIn 0.4s ease-out;
            }
            .stock-notif:hover { background: #fcfcfc; transform: translateX(5px); }
            .stock-notif img { width: 24px; }
            .stock-notif p { margin: 0; font-size: 0.9em; }
            @keyframes slideIn { from { opacity: 0; transform: translateX(-100%); } to { opacity: 1; transform: translateX(0); } }
        </style>
    <?php endif; 
} ?>
<?php
    $notifSTMT = $pdo->prepare(file_get_contents(__DIR__ . '/../../../queries/backoffice/stockFaible.sql'));
    $notifSTMT-> execute([':idVendeur' => $_SESSION['id']]);
    $produitsAlerte = $notifSTMT->fetchAll(PDO::FETCH_ASSOC);

    if(!empty($produitsAlerte)): ?>
        <div id="stock-notification-container">
            <?php foreach($produitsAlerte as $produit): ?>
                <a href="/views/backoffice/stock.php?reassort_id=<?php echo $produit['idProduit']; ?>" class="stock-notif">
                    <img src="/public/images/infoDark.svg" alt="Alerte">
                    <p>Le produit <strong><?php echo htmlspecialchars($produit['nom']);?></strong> est à <strong><?php echo $produit['stock']; ?></strong> unités.
                        Réassort nécessaire !</p>
                </a>
            <?php endforeach; ?>
        </div>

        <style>
            #stock-notifications-container {
            position: fixed;
            top: 20px;
            left: 20px;
            z-index: 10000;
            display: flex;
            flex-direction: column;
            gap: 10px;
            max-width: 300px;
        }
        .stock-notif {
            background: #fff;
            border-left: 5px solid #d9534f;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            padding: 12px;
            text-decoration: none;
            color: #333;
            display: flex;
            align-items: center;
            gap: 10px;
            border-radius: 4px;
            transition: transform 0.2s;
            animation: slideIn 0.3s ease-out;
        }
        .stock-notif:hover {
            transform: translateX(5px);
            background: #f9f9f9;
        }
        .stock-notif img { width: 20px; height: 20px; }
        .stock-notif p { margin: 0; font-size: 0.85em; line-height: 1.4; }
        
        @keyframes slideIn {
            from { opacity: 0; transform: translateX(-100%); }
            to { opacity: 1; transform: translateX(0); }
        }
        </style>
        
<?php endif; ?>
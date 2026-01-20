<?php 
require_once "../../controllers/pdo.php";
require_once "../../controllers/date.php";
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../frontoffice/connexionClient.php");
    exit();
}
$id_client = $_SESSION['user_id'];

function getNotifications($pdo, $idClient, $est_vendeur) {
    $sql = "SELECT * FROM _notification 
            WHERE (idClient = ? OR idClient = 34) 
            AND est_vendeur = ?
            ORDER BY dateNotif DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$idClient, $est_vendeur]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC); 
}
$notifs = getNotifications($pdo, $id_client, 0);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes notifications</title>
    <link rel="icon" href="/public/images/logoBackoffice.svg">
    <link rel="stylesheet" href="../../public/style.css">
</head>
<body>
    <?php include './partials/headerConnecte.php'; ?>
    <main class="mesNotif">
        <section class="topRecherche" ><h1>Mes notifications</h1></section>

        <?php if(!empty($notifs)): ?>
            <section class="ensembleNotif">
                <div class="sidebarNotif">
                <?php foreach($notifs as $notif): 
                    $contenuApercu = substr($notif['contenuNotif'], 0, 50) . "..."; ?>
                    <div class="apercuNotif" tabindex="0" onclick="afficherContenu(this, '<?= addslashes($notif['titreNotif']) ?>', '<?= $notif['dateNotif'] ?>', '<?= addslashes($notif['contenuNotif']) ?>')">
                        <div>
                            <img id="regular" src="../../public/images/bellRingDark.svg">
                            <img id="focus" src="../../public/images/bellRingLight.svg">
                        </div>
                        <div>
                            <h3><?= htmlspecialchars($notif['titreNotif']) ?></h3>
                            <h4><?= htmlspecialchars($contenuApercu) ?></h4>
                            <h5><?= $notif['dateNotif'] ?></h5>
                        </div>
                    </div>
                    <article class="contenuTel">
                        <div class="titleNotifResponsive">
                            <h2><?= htmlspecialchars($notif['titreNotif']) ?></h2>
                            <small><?= $notif['dateNotif'] ?></small>
                        </div>
                        <div class="corpsNotif"><p><?= nl2br(htmlspecialchars($notif['contenuNotif'])) ?></p></div>
                    </article>
                <?php endforeach; ?>
                </div>
                <article class="ecranNotif">
                    <div class="titleNotif">
                        <h1 id="titre">Cliquez sur une notification</h1>
                        <h3 id="contenu_date"></h3>
                    </div>
                    <div class="contenuNotif" id="notif_body">
                        <p id="date_placeholder">Sélectionnez une notification pour voir le détail.</p>
                    </div>
                </article>
            </section>
        <?php else: ?>
            <h2 class="aucuneNotif">Aucune notification</h2>
        <?php endif; ?>
    </main>
    <?php require_once '../backoffice/partials/retourEnHaut.php' ?>
    <?php include '../../views/frontoffice/partials/footerConnecte.php'; ?>

    <script>
        function afficherContenu(el, t, d, c) {
            document.getElementById("titre").innerText = t;
            document.getElementById("contenu_date").innerText = d;
            
            const match = c.match(/\[ID_PROD:(\d+)\]/);
            let textePropre = c.replace(/\[ID_PROD:\d+\]/, "");
            
            let html = `<p>${textePropre}</p>`;
            if (match) {
                const idProd = match[1];
                html += `<br><a href="mesAvis.php#avis-${idProd}" class="btn-voir-reponse" style="display:inline-block; margin-top:20px; padding:10px 20px; background:#273469; color:white; border-radius:5px; text-decoration:none; font-weight:bold;">Voir la réponse sur mon avis</a>`;
            }
            document.getElementById("notif_body").innerHTML = html;

            if (window.innerWidth <= 840) {
                const mobileContent = el.nextElementSibling;
                document.querySelectorAll('.contenuTel').forEach(item => item.classList.remove('active'));
                if (mobileContent) {
                    mobileContent.classList.add('active');
                    mobileContent.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                }
            }
        }
    </script>
</body>
</html>
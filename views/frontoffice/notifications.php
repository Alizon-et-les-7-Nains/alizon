<?php 
require_once "../../controllers/pdo.php";
require_once "../../controllers/date.php";
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../frontoffice/connexionClient.php");
    exit();
}
$id_client = $_SESSION['user_id'];

// Récupération des notifications pour le client
$stmt = $pdo->prepare("SELECT * FROM _notification WHERE idClient = ? AND est_vendeur = 0 ORDER BY dateNotif DESC");
$stmt->execute([$id_client]);
$notifs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mes notifications</title>
    <link rel="stylesheet" href="../../public/style.css">
</head>
<body>
    <?php include './partials/headerConnecte.php'; ?>
    <main class="mesNotif">
        <section class="topRecherche"><h1>Mes notifications</h1></section>

        <?php if(!empty($notifs)): ?>
            <section class="ensembleNotif">
                <div class="sidebarNotif">
                <?php foreach($notifs as $notif): ?>
                    <div class="apercuNotif" tabindex="0" onclick="afficherContenu(this, '<?= addslashes($notif['titreNotif']) ?>', '<?= $notif['dateNotif'] ?>', '<?= addslashes($notif['contenuNotif']) ?>')">
                        <div><img src="../../public/images/bellRingDark.svg"></div>
                        <div>
                            <h3><?= htmlspecialchars($notif['titreNotif']) ?></h3>
                            <h5><?= $notif['dateNotif'] ?></h5>
                        </div>
                    </div>
                <?php endforeach; ?>
                </div>
                <article class="ecranNotif">
                    <div class="titleNotif">
                        <h1 id="titre">Cliquez sur une notification</h1>
                        <h3 id="contenu_date"></h3>
                    </div>
                    <div class="contenuNotif" id="notif_body">
                        <p>Sélectionnez une notification pour voir le détail.</p>
                    </div>
                </article>
            </section>
        <?php else: ?>
            <h2 class="aucuneNotif">Aucune notification</h2>
        <?php endif; ?>
    </main>

    <script>
        function afficherContenu(el, t, d, c) {
            // Affichage du titre et de la date
            document.getElementById("titre").innerText = t;
            document.getElementById("contenu_date").innerText = d;
            
            // Recherche de l'ID produit caché [ID_PROD:XX] via Regex
            const match = c.match(/\[ID_PROD:(\d+)\]/);
            let texteSansTag = c.replace(/\[ID_PROD:\d+\]/, "");
            
            let htmlContent = `<p>${texteSansTag}</p>`;
            
            // Si un ID produit est trouvé, on génère le bouton de redirection vers l'ancre
            if (match) {
                const idProd = match[1];
                htmlContent += `
                    <br><br>
                    <a href="mesAvis.php#avis-${idProd}" 
                       style="display:inline-block; padding:12px 25px; background:#273469; color:white; border-radius:8px; text-decoration:none; font-weight:bold; font-size:14px;">
                       Voir la réponse sur mon avis
                    </a>`;
            }
            
            document.getElementById("notif_body").innerHTML = htmlContent;

            // Gestion responsive pour mobile
            if (window.innerWidth <= 840) {
                const mobileContent = el.nextElementSibling;
                if (mobileContent && mobileContent.classList.contains('contenuTel')) {
                    document.querySelectorAll('.contenuTel').forEach(item => item.classList.remove('active'));
                    mobileContent.classList.add('active');
                }
            }
        }
    </script>
</body>
</html>
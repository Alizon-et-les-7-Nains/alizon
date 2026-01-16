<?php

    require_once '../../controllers/pdo.php';
    require_once '../../controllers/auth.php';

    if(isset($_GET['error']) && isset($_GET['idProduit'])) {
        $idProduit = $_GET['idProduit'];
        $codeErreur = $_GET['error'];
        echo "<script>window.addEventListener('load', () => popUpErreur('$idProduit', $codeErreur));</script>";
    }

    if (!isset($_SESSION['id'])) {
        header("Location: ../frontoffice/connexionClient.php");
        exit();
    }

    $idVendeur = $_SESSION['id'];

function getNotifications($pdo, $idClient, $est_vendeur) {
    $sql = "SELECT * FROM _notification 
            WHERE idClient = :idClient 
            AND est_vendeur = :est_vendeur 
            ORDER BY dateNotif DESC";
            
    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        'idClient'   => $idClient,
        'est_vendeur' => $est_vendeur
    ]);

    $notif = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    return $notif; 
}

$notifs = getNotifications($pdo, $idVendeur, 1)

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
<body class="backoffice">
        
    <?php require_once './partials/header.php' ?>

    <?php $currentPage = basename(__FILE__); require_once './partials/aside.php' ?>

    <main class="mesNotif">
        <section class="topRecherche" >
            <h1>Mes notifications</h1>
        </section>

        <?php if(!empty($notifs)) { ?>
            <section class="ensembleNotif">
                <div class="sidebarNotif">
                <?php foreach($notifs as $notif) { 
                    $contenuNotif = $notif['contenuNotif'];
                    $contenuNotif = substr($contenuNotif, 0, 50) . "...";?>
                    <div class="apercuNotif" tabindex="0" data-id="<?= htmlspecialchars($notif['idNotif'] ?? '') ?>" onclick="afficherContenu(this, '<?= htmlspecialchars($notif['titreNotif'], ENT_QUOTES) ?>',
                        '<?= htmlspecialchars($notif['dateNotif'], ENT_QUOTES) ?>',
                        '<?= htmlspecialchars($notif['contenuNotif'], ENT_QUOTES) ?>')">
                        <div>
                            <img id="regular" src="../../public/images/bellRingDark.svg" alt="Nouvelle notification">
                            <img id="focus" src="../../public/images/bellRingLight.svg" alt="Nouvelle notification">
                        </div>
                        <div>
                            <h3><?= $notif['titreNotif'] ?></h3>
                            <h4><?= $contenuNotif ?></h4>
                            <h5><?= $notif['dateNotif'] ?></h5>
                        </div>
                    </div>
                    <article class="contenuTel">
                        <div class="titleNotifResponsive">
                            <h2 style="color: #273469;"><?= htmlspecialchars($notif['titreNotif']) ?></h2>
                            <small><?= htmlspecialchars($notif['dateNotif']) ?></small>
                        </div>
                        <div class="corpsNotif" style="margin-top: 15px;">
                            <p><?= nl2br(htmlspecialchars($notif['contenuNotif'])) ?></p>
                        </div>
                    </article>
                <?php } ?>
                </div>
                <article class="ecranNotif">
                    <div class="titleNotif">
                        <h1 id="titre"><?= 'Cliquez sur une notification pour afficher son contenu' ?></h1>
                        <h3 id="contenu"><?= htmlspecialchars($notif['dateNotif'] ?? ' ') ?></h3>
                    </div>
                    <div class="contenuNotif">
                        <p id="date"><?= htmlspecialchars($notif['dateNotif'] ?? ' ') ?></p>
                    </div>
                </article>
            </section>
        <?php } else { ?>
            <h2 class="aucuneNotif">Aucune notification</h2>
        <?php } ?>

        <?php require_once '../backoffice/partials/retourEnHaut.php' ?>
        <?php include '../../views/backoffice/partials/footer.php'; ?>
    </main>

    <script>
        const titreContent = document.getElementById("titre");
        const contenuContent = document.getElementById("contenu");
        const dateContent = document.getElementById("date");

        function afficherContenu(el, t, d, c) {
            if (titreContent) titreContent.innerText = t;
            if (contenuContent) contenuContent.innerText = d;
            if (dateContent) dateContent.innerText = c;

            const mobileContent = el.nextElementSibling;

            if (window.innerWidth <= 840) {
                document.querySelectorAll('.contenuTel').forEach(item => {
                    if (item !== mobileContent) item.classList.remove('active');
                });

                if (mobileContent) {
                    mobileContent.classList.toggle('active'); 
                    if(mobileContent.classList.contains('active')) {
                        setTimeout(() => {
                            mobileContent.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                        }, 100);
                    }
                }
            }
        }
</script>

</body>
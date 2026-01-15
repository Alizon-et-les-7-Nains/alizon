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

$notifs = getNotifications($pdo, $id_client, 0)

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
        <section class="topRecherche">
            <h1>Mes notifications</h1>
        </section>

        <section class="ensembleNotif">
            <div class="sidebarNotif">
            <?php foreach($notifs as $notif) { ?>
                <div class="apercuNotif">
                    <div>
                        <img id="regular" src="../../public/images/bellRingDark.svg" alt="Nouvelle notification">
                        <img id="focus" src="../../public/images/bellLightBlue.svg" alt="Nouvelle notification">
                    </div>
                    <div>
                        <h3><?= $notif['titreNotif'] ?></h3>
                        <h4><?= $notif['contenuNotif'] ?></h4>
                        <h5><?= $notif['dateNotif'] ?></h5>
                    </div>
                </div>
            <?php } ?>
            </div>
            <article class="ecranNotif">
                <div class="titleNotif">
                    <h1>X</h1>
                    <h3>X</h3>
                </div>
                <div class="contenuNotif">
                    X
                </div>
            </article>
        </section>

        <?php require_once '../backoffice/partials/retourEnHaut.php' ?>
        <?php include '../../views/frontoffice/partials/footerConnecte.php'; ?>
    </main>

</body>
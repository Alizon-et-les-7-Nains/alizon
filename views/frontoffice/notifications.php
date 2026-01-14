<?php 
require_once "../../controllers/pdo.php";
require_once "../../controllers/date.php";

session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../frontoffice/connexionClient.php");
    exit();
}

$id_client = $_SESSION['user_id'];

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

    <main class="mesAvis">
        <section class="topRecherche">
            <h1>Mes notifications</h1>
        </section>

        <section class="ensembleNotif">
            <div class="sidebarNotif">
                <div class="apercuNotif">
                    <div>
                        <img src="../../public/images/bellRingDark.svg" alt="Nouvelle notification">
                    </div>
                    <div>
                        <h3>Votre commande à été livrée</h3>
                        <h4>Votre commande "Cidre brut breton" a été livrée au 19 Rue de Cerbar, 56300 Pluvigner le 29/10/2026 à 12h56</h4>
                        <h5>Il y a 59 minutes</h5>
                    </div>
                </div>
                <div class="apercuNotif">
                    <div>
                        <img src="../../public/images/bellRingDark.svg" alt="Nouvelle notification">
                    </div>
                    <div>
                        <h3>Votre commande à été livrée</h3>
                        <h4>Votre commande "Cidre brut breton" a été livrée au 19 Rue de Cerbar, 56300 Pluvigner le 29/10/2026 à 12h56</h4>
                        <h5>Il y a 59 minutes</h5>
                    </div>
                </div>
                        <div class="apercuNotif">
                    <div>
                        <img src="../../public/images/bellRingDark.svg" alt="Nouvelle notification">
                    </div>
                    <div>
                        <h3>Votre commande à été livrée</h3>
                        <h4>Votre commande "Cidre brut breton" a été livrée au 19 Rue de Cerbar, 56300 Pluvigner le 29/10/2026 à 12h56</h4>
                        <h5>Il y a 59 minutes</h5>
                    </div>
                </div>
                <div class="apercuNotif">
                    <div>
                        <img src="../../public/images/bellRingDark.svg" alt="Nouvelle notification">
                    </div>
                    <div>
                        <h3>Votre commande à été livrée</h3>
                        <h4>Votre commande "Cidre brut breton" a été livrée au 19 Rue de Cerbar, 56300 Pluvigner le 29/10/2026 à 12h56</h4>
                        <h5>Il y a 59 minutes</h5>
                    </div>
                </div>
                        <div class="apercuNotif">
                    <div>
                        <img src="../../public/images/bellRingDark.svg" alt="Nouvelle notification">
                    </div>
                    <div>
                        <h3>Votre commande à été livrée</h3>
                        <h4>Votre commande "Cidre brut breton" a été livrée au 19 Rue de Cerbar, 56300 Pluvigner le 29/10/2026 à 12h56</h4>
                        <h5>Il y a 59 minutes</h5>
                    </div>
                </div>
                <div class="apercuNotif">
                    <div>
                        <img src="../../public/images/bellRingDark.svg" alt="Nouvelle notification">
                    </div>
                    <div>
                        <h3>Votre commande à été livrée</h3>
                        <h4>Votre commande "Cidre brut breton" a été livrée au 19 Rue de Cerbar, 56300 Pluvigner le 29/10/2026 à 12h56</h4>
                        <h5>Il y a 59 minutes</h5>
                    </div>
                </div>
                <div class="apercuNotif">
                    <div>
                        <img src="../../public/images/bellRingDark.svg" alt="Nouvelle notification">
                    </div>
                    <div>
                        <h3>Votre commande à été livrée</h3>
                        <h4>Votre commande "Cidre brut breton" a été livrée au 19 Rue de Cerbar, 56300 Pluvigner le 29/10/2026 à 12h56</h4>
                        <h5>Il y a 59 minutes</h5>
                    </div>
                </div>
                <div class="apercuNotif">
                    <div>
                        <img src="../../public/images/bellRingDark.svg" alt="Nouvelle notification">
                    </div>
                    <div>
                        <h3>Votre commande à été livrée</h3>
                        <h4>Votre commande "Cidre brut breton" a été livrée au 19 Rue de Cerbar, 56300 Pluvigner le 29/10/2026 à 12h56</h4>
                        <h5>Il y a 59 minutes</h5>
                    </div>
                </div>
            </div>
            <article class="ecranNotif">
                <div class="titleNotif">
                    <h1>Votre commande à été livrée</h1>
                    <h3>Il y a 16 minutes</h3>
                </div>
                <div class="contenuNotif">
                    Votre commande “Rillettes de thon - 300g” a été livrée au 19 Rue de Cerbar, 56300 Pluvigner ce lundi 29 janvier. Notre service de livraison vous informe que le colis a bien été déposé à l’adresse indiquée et qu’il est désormais disponible à votre domicile. Le livreur a pris soin de déposer le paquet dans un endroit sécurisé afin de garantir la fraîcheur et la qualité du produit.
                    Vous pouvez déguster vos rillettes de thon dès à présent. Préparées avec soin, elles sont idéales pour accompagner un apéritif entre amis, garnir des toasts croustillants ou encore relever une salade composée. Conservez le produit au réfrigérateur et consommez-le rapidement après ouverture afin d’apprécier pleinement toutes ses saveurs.
                    Si vous n’avez pas reçu la commande ou si vous constatez un problème avec le produit, n’hésitez pas à contacter notre service client via votre espace personnel. Nous serons ravis de vous assister et, si nécessaire, de procéder à un remplacement ou à un remboursement.
                    Merci de votre confiance et bonne dégustation !
                </div>
            </article>
        </section>

        <?php require_once '../backoffice/partials/retourEnHaut.php' ?>
        <?php include '../../views/frontoffice/partials/footerConnecte.php'; ?>
    </main>

</body>
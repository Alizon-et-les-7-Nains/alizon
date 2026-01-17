<?php 
require_once "../../controllers/pdo.php";
require_once "../../controllers/date.php";

session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../frontoffice/connexionClient.php");
    exit();
}

$id_client = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT * FROM _avis WHERE idClient = ?");
$stmt->execute([$id_client]);
$mesAvis = $stmt->fetchAll(PDO::FETCH_ASSOC);

function afficherEtoiles($note) {
    // Fonction permettant d'afficher le nombre
    // d'étoiles d'un commentaire écrit en fonction de 
    // la note ayant été attribuée
    $html = "";
    for ($i = 1; $i <= 5; $i++) {
        if ($i <= $note) $html .= "<img class='etoile' src='/public/images/etoile.svg'>";
        else $html .= "<img class='vide' src='/public/images/etoileVide.svg'>";
    }
    return $html;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Avis</title>

    <link rel="icon" href="/public/images/logoBackoffice.svg">
    <link rel="stylesheet" href="../../public/style.css">
</head>
<body>
    <header>
        <?php include './partials/headerConnecte.php'; ?>
    </header>
    <main class="mesAvis">
        <h1> Mes Commentaires</h1>

        <?php
            if (!$mesAvis){
                ?> <h2> Aucun Avis </h2> <?php
            }
            else{
                ?>
                <section> <?php
                // Pour tous les avis, on récupère les produits associés ainsi que leurs images
                foreach ($mesAvis as $avis) {
                $p = $avis['idProduit'];
                $stmt2 = $pdo->prepare("SELECT * FROM _produit WHERE idProduit = ?");
                $stmt2->execute([$p]);
                $monProduit = $stmt2->fetch(PDO::FETCH_ASSOC);
                $stmt3 = $pdo->prepare("SELECT * FROM _imageDeProduit WHERE idProduit = ?");
                $stmt3->execute([$p]);
                $imageProduit = $stmt3->fetch(PDO::FETCH_ASSOC); 
                ?> 
                <article>
                <!-- Carte du produit-->
                    <div class="produit">
                        <img src=<?php echo($imageProduit['URL']) ?>>
                        <div class="infos-produit">
                            <h3><?php echo($monProduit['nom']); ?></h3>
                            <p><?php echo($monProduit['prix'] . "€"); ?></p>
                        </div>
                    </div>
                    <!-- Contenu de l'avis-->
                    <div class="contenu">
                        <div class="header-contenu">
                            <h2><?php echo($avis['titreAvis']); ?></h2>
                            <span class="date"><?php echo("Publié le " . formatDate($avis['dateAvis'])); ?></span>
                        </div>
                        <div class="note">
                            <?= afficherEtoiles(round($avis['note'])); ?>
                        </div>
                        <div class="texte">
                            <p><?php echo($avis['contenuAvis']); ?></p>
                        </div>
                        <div class="actions">
                            <a href="./modifierAvis.php?id=<?php echo $avis['idProduit']; ?>">Modifier</a>
                            <a href="../../controllers/supprimerAvis.php?id=<?php echo($p);?>" class="supprimerAvis">Supprimer</a>
                        </div>
                    </div>
            </article>
                <?php               
            }  
        }      
?>
            </section>

            <?php require_once '../backoffice/partials/retourEnHaut.php' ?>
    </main>
    <?php include './partials/footerConnecte.php'; ?>
</body>
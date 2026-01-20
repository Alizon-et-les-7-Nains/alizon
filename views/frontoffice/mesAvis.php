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
    <title>Mes Avis</title>
    <link rel="stylesheet" href="../../public/style.css">
</head>
<body>
    <?php include './partials/headerConnecte.php'; ?>
    <main class="mesAvis">
        <h1> Mes Commentaires</h1>
        <?php if (!$mesAvis): ?>
            <h2> Aucun Avis </h2>
        <?php else: ?>
            <section>
            <?php foreach ($mesAvis as $avis): 
                $p = $avis['idProduit'];
                $stmt2 = $pdo->prepare("SELECT * FROM _produit WHERE idProduit = ?");
                $stmt2->execute([$p]);
                $monProduit = $stmt2->fetch(PDO::FETCH_ASSOC);

                $stmt3 = $pdo->prepare("SELECT * FROM _imageDeProduit WHERE idProduit = ? LIMIT 1");
                $stmt3->execute([$p]);
                $imageProduit = $stmt3->fetch(PDO::FETCH_ASSOC); 
            ?> 
                <article id="avis-<?= $p ?>"> 
                    <div class="produit">
                        <img src="<?= htmlspecialchars($imageProduit['URL'] ?? '../../public/images/defaultImageProduit.png') ?>">
                        <div class="infos-produit">
                            <h3><?= htmlspecialchars($monProduit['nom']); ?></h3>
                            <p><?= htmlspecialchars($monProduit['prix']); ?>€</p>
                        </div>
                    </div>
                    <div class="contenu">
                        <div class="header-contenu">
                            <h2><?= htmlspecialchars($avis['titreAvis']); ?></h2>
                            <span class="date"><?= "Publié le " . formatDate($avis['dateAvis']); ?></span>
                        </div>
                        <div class="note"><?= afficherEtoiles(round($avis['note'])); ?></div>
                        <div class="texte"><p><?php echo nl2br(htmlspecialchars($avis['contenuAvis'])); ?></p></div>
                        <div class="actions">
                            <a href="./modifierAvis.php?id=<?= $p; ?>">Modifier</a>
                            <a href="../../controllers/supprimerAvis.php?id=<?= $p; ?>" class="supprimerAvis">Supprimer</a>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
            </section>
        <?php endif; ?>
    </main>
    <?php include './partials/footerConnecte.php'; ?>
</body>
</html>
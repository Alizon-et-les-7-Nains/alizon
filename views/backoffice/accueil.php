<?php
    require_once '../../controllers/pdo.php';

    require_once '../../controllers/prix.php';
    require_once '../../controllers/date.php';

    require_once '../../controllers/auth.php';
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alizon</title>

    <link rel="stylesheet" href="../../public/style.css">
    <link rel="icon" href="/public/images/logoBackoffice.svg">
</head>

<body class="backoffice">
    <?php require_once './partials/header.php' ?>
    <?php require_once './partials/notifications_stock.php' ?>

    <?php
        $idVendeur = $_SESSION['id'];

        $sqlCheckStock = file_get_contents('../../queries/backoffice/stockFaible.sql');
        $stmtStock = $pdo->prepare($sqlCheckStock);
        $stmtStock->execute([':idVendeur' => $idVendeur]);
        $produitsEnAlerte = $stmtStock->fetchAll(PDO::FETCH_ASSOC);

        foreach ($produitsEnAlerte as $p) {
            $checkNotif = $pdo->prepare("SELECT COUNT(*) FROM _notification WHERE idClient = ? AND est_vendeur = 1 AND contenuNotif LIKE ?");
            $checkNotif->execute([$idVendeur, "%" . $p['nom'] . "%"]);
            
            if ($checkNotif->fetchColumn() == 0) {
                $ins = $pdo->prepare("INSERT INTO _notification (idClient, titreNotif, contenuNotif, dateNotif, est_vendeur) VALUES (?, ?, ?, NOW(), 1)");
                $titre = "Alerte Stock : " . $p['nom'];
                $contenu = "Le produit " . $p['nom'] . " est à " . $p['stock'] . " unités. (ID:" . $p['idProduit'] . ")";
                $ins->execute([$idVendeur, $titre, $contenu]);
            }
        }
    ?>

    <?php
        $currentPage = basename(__FILE__);
        require_once './partials/aside.php';
    ?>

    <main class="acceuilBackoffice">
        <section class="stock">
            <h1>Stocks Faibles</h1>
            <article>
                <?php
$stockSTMT = $pdo->prepare(file_get_contents('../../queries/backoffice/stockFaibleAccueil.sql'));
$stockSTMT->execute([':idVendeur' => $_SESSION['id']]);
$stock = $stockSTMT->fetchAll(PDO::FETCH_ASSOC);
    if (count($stock) == 0) echo "<h2>Aucun stock affaibli</h2>";
    foreach ($stock as $produit => $atr) {
        $idProduit = $atr['idProduit'];
        $image = ($pdo->query(str_replace('$idProduit', $idProduit, file_get_contents('../../queries/imagesProduit.sql'))))->fetchAll(PDO::FETCH_ASSOC);
        $image = $image = !empty($image) ? $image[0]['URL'] : '';
        
        // Récupérer les remises
        $remiseSTMT = $pdo->prepare("SELECT tauxRemise FROM _remise WHERE idProduit = ? AND CURDATE() BETWEEN debutRemise AND finRemise");
        $remiseSTMT->execute([$idProduit]);
        $remise = $remiseSTMT->fetch(PDO::FETCH_ASSOC);
        
        $prixOriginal = $atr['prix'];
        $tauxRemise = $remise['tauxRemise'] ?? 0;
        $enRemise = !empty($remise) && $tauxRemise > 0;
        $prixRemise = $enRemise ? $prixOriginal * (1 - $tauxRemise/100) : $prixOriginal;
        
        $html = "
        <table>
            <tr>
                <td><img src='$image'></td>
            </tr>
            <tr>
                <td>" . $atr['nom'] . "</td>
            </tr>
            <tr>";
                if ($enRemise) {
                    $html .= "<td><div style='display: flex; align-items: center; gap: 8px;'>
                        <span>" . formatPrice($prixRemise) . "</span>
                        <span style='text-decoration: line-through; color: #999; font-size: 0.9em;'>" . formatPrice($prixOriginal) . "</span>
                    </div></td>";
                } else {
                    $html .= "<td>" . formatPrice($prixOriginal) . "</td>";
                }
                $stock = $atr['stock'];
                $seuil = "";
                if ($stock == 0) {
                    $seuil = "epuise";
                } else if ($stock <= $atr['seuilAlerte']) {
                    $seuil = "faible";
                }
                $html .= "<td class=\"$seuil\">$stock</td>
            </tr>
        </table>
        ";
        echo $html;
    }
?>
            </article>
            <a href="./stocks.php" title="Voir plus"><img src="/public/images/infoDark.svg"></a>
        </section>

        <section class="commandes">
            <h1>Dernières Commandes</h1>
            <article>
                <?php
$commandesSTMT = $pdo->prepare(file_get_contents('../../queries/backoffice/dernieresCommandes.sql'));
$commandesSTMT->execute([':idVendeur' => $_SESSION['id']]);
$commandes = $commandesSTMT->fetchAll(PDO::FETCH_ASSOC);
    if (count($commandes) == 0) echo "<h2>Aucune commande</h2>";
    foreach ($commandes as $commande) {
        $idProduit = $commande['idProduit'];
        $image = ($pdo->query(str_replace('$idProduit', $idProduit, file_get_contents('../../queries/imagesProduit.sql'))))->fetchAll(PDO::FETCH_ASSOC);
        $image = $image = !empty($image) ? $image[0]['URL'] : '';
        
        // Récupérer les remises
        $remiseSTMT = $pdo->prepare("SELECT tauxRemise FROM _remise WHERE idProduit = ? AND CURDATE() BETWEEN debutRemise AND finRemise");
        $remiseSTMT->execute([$idProduit]);
        $remise = $remiseSTMT->fetch(PDO::FETCH_ASSOC);
        
        $prixOriginal = $commande['prix'];
        $tauxRemise = $remise['tauxRemise'] ?? 0;
        $enRemise = !empty($remise) && $tauxRemise > 0;
        $prixRemise = $enRemise ? $prixOriginal * (1 - $tauxRemise/100) : $prixOriginal;
        $prixAffichage = $enRemise ? $prixRemise : $prixOriginal;
        
        $html = "
        <table>
            <tr>
                <td rowspan=2><img src='$image'></td>
                <th>" . $commande['nom'] . "</th>
            </tr>
            <tr>
                <td>
                    Prix Unitaire : <strong>" . ($enRemise ? 
                        "<div style='display: flex; align-items: center; gap: 4px;'>
                            <span>" . formatPrice($prixRemise) . "</span>
                            <span style='text-decoration: line-through; color: #999; font-size: 0.9em;'>" . formatPrice($prixOriginal) . "</span>
                        </div>" 
                        : formatPrice($prixOriginal)) . "</strong><br>
                    Prix Total : <strong>" . formatPrice($prixAffichage * $commande['quantiteProduit']) . "</strong><br>
                    Statut : <strong>" . $commande['etatLivraison'] . "</strong>
                </td>
            </tr>
            <tr>
                <td>" . formatDate($commande['dateCommande']) . "</td>
                <th>" . $commande['quantiteProduit'] . "</th>
            </tr>
        </table>
        ";
        echo $html;
    }
?>
            </article>
            <a href="./commandes.php" title="Voir plus"><img src="/public/images/infoDark.svg"></a>
        </section>

        <section class="avis">
            <h1>Derniers Avis</h1>
            <article>
                <?php
$avisSTMT = $pdo->prepare(file_get_contents('../../queries/backoffice/derniersAvis.sql'));
$avisSTMT->execute([':idVendeur' => $_SESSION['id']]);
$avis = $avisSTMT->fetchAll(PDO::FETCH_ASSOC);
    if (count($avis) == 0) echo "<h2>Aucun avis</h2>";
    foreach ($avis as $avi) {
        $imagesAvis = ($pdo->query(str_replace('$idClient', $avi['idClient'], str_replace('$idProduit', $avi['idProduit'], file_get_contents('../../queries/imagesAvis.sql')))))->fetchAll(PDO::FETCH_ASSOC);
        $imageClient = "/images/photoProfilClient/photo_profil" . $avi['idClient'] . ".svg";
        $html = "
        <table>
            <tr>
                <th rowspan=2>
                    <figure>
                        <img src='$imageClient' onerror=" . '"this.style.display=' . "'none'" . '"' . ">
                        <figcaption>" . $avi['pseudo'] . "</figcaption>
                    </figure>
                    <figure>
                        <figcaption>" . str_replace('.', ',', $avi['note']) . "</figcaption>
                        <img src='/public/images/etoile.svg'>
                    </figure>
                </th>
                <th>" . $avi['nomProduit'] . " - " . $avi['titreAvis'] . "</th>
                <td>Le " . formatDate($avi['dateAvis']) . "</td>
            </tr>
            <tr>
                <td colspan='2'>" . $avi['contenuAvis'] . "</td>
            </tr>
            <tr>
                <td></td>
                <td colspan='2'>";   
                    foreach ($imagesAvis as $imageAvi) {
                        $html .= "<img src='" . $imageAvi['URL'] . "' class='imageAvis'>";
                    }
                $html .= "</td>
            </tr>
        </table>
        ";
        echo $html;
    }
?>
            </article>
            <a href="./avis.php" title="Voir plus"><img src="/public/images/infoDark.svg"></a>
        </section>

        <section class="produits">
            <h1>Produits en Vente</h1>
            <article>
                <?php
$produitsSTMT = $pdo->prepare(file_get_contents('../../queries/backoffice/produitsVente.sql'));
$produitsSTMT->execute([':idVendeur' => $_SESSION['id']]);
$produits = $produitsSTMT->fetchAll(PDO::FETCH_ASSOC);
if (count($produits) == 0) echo "<h2>Aucun produit en vente</h2>";
    foreach ($produits as $produit => $atr) {
        $idProduit = $atr['idProduit'];
        $image = ($pdo->query(str_replace('$idProduit', $idProduit, file_get_contents('../../queries/imagesProduit.sql'))))->fetchAll(PDO::FETCH_ASSOC);
        $image = $image = !empty($image) ? $image[0]['URL'] : '';
        
        // Récupérer les remises
        $remiseSTMT = $pdo->prepare("SELECT tauxRemise FROM _remise WHERE idProduit = ? AND CURDATE() BETWEEN debutRemise AND finRemise");
        $remiseSTMT->execute([$idProduit]);
        $remise = $remiseSTMT->fetch(PDO::FETCH_ASSOC);
        
        $prixOriginal = $atr['prix'];
        $tauxRemise = $remise['tauxRemise'] ?? 0;
        $enRemise = !empty($remise) && $tauxRemise > 0;
        $prixRemise = $enRemise ? $prixOriginal * (1 - $tauxRemise/100) : $prixOriginal;
        
        $html = "
        <table>
            <tr>
                <td><img src='$image'></td>
            </tr>
            <tr>";
                $html .= "<td>" . $atr['nom'] . "</td>";
                if ($enRemise) {
                    $html .= "<td><div style='display: flex; align-items: center; gap: 8px;'>
                        <span>" . formatPrice($prixRemise) . "</span>
                        <span style='text-decoration: line-through; color: #999; font-size: 0.9em;'>" . formatPrice($prixOriginal) . "</span>
                    </div></td>";
                } else {
                    $html .= "<td>" . formatPrice($prixOriginal) . "</td>";
                }
            $html .= "</tr>
        </table>
        ";
        echo $html;
    }
?>
            </article>
            <a href="./produits.php" title="Voir plus"><img src="/public/images/infoDark.svg"></a>
        </section>

        <?php require_once './partials/retourEnHaut.php' ?>
    </main>

    <?php require_once './partials/footer.php' ?>

    <script src="../../public/amd-shim.js"></script>
    <script src="../../public/script.js"></script>
</body>

</html>
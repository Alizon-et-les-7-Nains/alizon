<?php
require_once '../../controllers/pdo.php';
require_once '../../controllers/prix.php';
require_once '../../controllers/date.php';
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Alizon</title>

        <link rel="stylesheet" href="../../public/style.css">
        <link rel="icon" href="/public/images/logoBackoffice.svg">
    </head>

    <body class="backoffice">
        <?php require_once './partials/header.php' ?>

        <?php require_once './partials/aside.php' ?>

        <main class="acceuilBackoffice">
            <section class="stock">
                <h1>Stocks Faibles</h1>
                <article>
<?php
    $stock = ($pdo->query(file_get_contents('../../queries/backoffice/stockFaible.sql')))->fetchAll(PDO::FETCH_ASSOC);
    if (count($stock) == 0) echo "<h2>Aucun stock affaibli</h2>";
    foreach ($stock as $produit => $atr) {
        $idProduit = $atr['idProduit'];
        $image = ($pdo->query(str_replace('$idProduit', $idProduit, file_get_contents('../../queries/imagesProduit.sql'))))->fetchAll(PDO::FETCH_ASSOC);
        $image = $image = !empty($image) ? $image[0]['URL'] : '';
        $html = "
        <table>
            <tr>
                <td><img src='$image'></td>
            </tr>
            <tr>
                <td>" . $atr['nom'] . "</td>
            </tr>
            <tr>";
                $prix = formatPrice($atr['prix']);
                $html .= "<td>" . $prix . "</td>";
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
                <a href="./stock.php" title="Voir plus"><img src="/public/images/infoDark.svg"></a>
            </section>

            <section class="commandes">
                <h1>Dernières Commandes</h1>
                <article>
<?php
    $commandes = ($pdo->query(file_get_contents('../../queries/backoffice/dernieresCommandes.sql')))->fetchAll(PDO::FETCH_ASSOC);
    if (count($commandes) == 0) echo "<h2>Aucune commande</h2>";
    foreach ($commandes as $commande) {
        $idProduit = $commande['idProduit'];
        $image = ($pdo->query(str_replace('$idProduit', $idProduit, file_get_contents('../../queries/imagesProduit.sql'))))->fetchAll(PDO::FETCH_ASSOC);
        $image = $image = !empty($image) ? $image[0]['URL'] : '';
        $html = "
        <table>
            <tr>
                <td rowspan=2><img src='$image'></td>
                <th>" . $commande['nom'] . "</th>
            </tr>
            <tr>
                <td>
                    Prix Unitaire : <strong>" . formatPrice($commande['prix']) . "</strong><br>
                    Prix Total : <strong>" . formatPrice($commande['prix'] * $commande['quantiteProduit']) . "</strong><br>
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
    $avis = ($pdo->query(file_get_contents('../../queries/backoffice/derniersAvis.sql')))->fetchAll(PDO::FETCH_ASSOC);
    if (count($avis) == 0) echo "<h2>Aucun avis</h>";
    foreach ($avis as $avi) {
        $imagesAvis = ($pdo->query(str_replace('$idClient', $avi['idClient'], str_replace('$idProduit', $avi['idProduit'], file_get_contents('../../queries/imagesAvis.sql')))))->fetchAll(PDO::FETCH_ASSOC);
        $imageClient = ($pdo->query("select URL from _imageClient where idClient = " . $avi['idClient']))->fetchAll(PDO::FETCH_ASSOC);
        $imageClient = $imageClient = !empty($imageClient) ? $imageClient[0]['URL'] : '';
        $html = "
        <table>
            <tr>
                <th rowspan=2>
                    <figure>
                        <img src='$imageClient'>
                        <figcaption>" . $avi['nomClient'] . "</figcaption>
                    </figure>
                    <figure>
                        <figcaption>" . str_replace('.', ',', $avi['note']) . "</figcaption>
                        <img src='/public/images/etoile.svg'>
                    </figure>
                </th>
                <th>" . $avi['nomProduit'] . " - " . $avi['titreAvis'] . "</th>
                <td>Le" . formatDate($avi['dateAvis']) . "</td>
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
    $produits = ($pdo->query(file_get_contents('../../queries/backoffice/produitsVente.sql')))->fetchAll(PDO::FETCH_ASSOC);
    foreach ($produits as $produit => $atr) {
        $idProduit = $atr['idProduit'];
        $image = ($pdo->query(str_replace('$idProduit', $idProduit, file_get_contents('../../queries/imagesProduit.sql'))))->fetchAll(PDO::FETCH_ASSOC);
        $image = $image = !empty($image) ? $image[0]['URL'] : '';
        $html = "
        <table>
            <tr>
                <td><img src='$image'></td>
            </tr>
            <tr>";
                $prix = formatPrice($atr['prix']);
                $html .= "<td>" . $atr['nom'] . "</td>
                <td>" . $prix . "</td>
            </tr>
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

        <script src="../../public/script.js"></script>
    </body>
</html>
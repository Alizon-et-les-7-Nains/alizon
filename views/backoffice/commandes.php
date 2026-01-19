<?php
    require_once '../../controllers/auth.php';

    require_once '../../controllers/date.php';
    require_once '../../controllers/prix.php';
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alizon - Commandes</title>

    <link rel="stylesheet" href="../../public/style.css">
    <link rel="icon" href="/public/images/logoBackoffice.svg">
</head>

<body class="backoffice">
    <?php require_once './partials/header.php' ?>

    <?php
        $currentPage = basename(__FILE__);
        require_once './partials/aside.php';
    ?>

    <main class="commandesBackoffice">
        <section>
            <h1>En Cours</h1>
<?php
    $cmdsSTMT = $pdo->prepare(file_get_contents('../../queries/backoffice/commandes/encours.sql'));
    $cmdsSTMT->execute([$_SESSION['id']]);
    $encours = $cmdsSTMT->fetchAll(PDO::FETCH_ASSOC);

    if (count($encours) == 0) echo "<h2>Aucune commande en cours</h2>";

    foreach ($encours as $encour) {
        $prodsSTMT = $pdo->prepare(file_get_contents('../../queries/backoffice/commandes/prodsDeCommande.sql'));
        $prodsSTMT->execute([$encour['idCommande'], $_SESSION['id']]);
        $prods = $prodsSTMT->fetchAll(PDO::FETCH_ASSOC);

        $html = "<article id='c-" . $encour['idCommande'] . "'>
            <ul>
                <li><h2>" . $encour['etatLivraison'] . "...</h2></li>
                <li>" . formatDate($encour['dateCommande']) . "</li>
            </ul>
            <ul>";
                foreach ($prods as $prod) {
                    $imageSTMT = $pdo->prepare('select URL from _imageDeProduit where idProduit = ?');
                    $imageSTMT->execute([$prod['idProduit']]);
                    $image = $imageSTMT->fetchColumn();
                    
                    $html .= "<li>
                        <table>
                            <tr>
                                <td colspan=2>
                                    <figure>
                                        <img src='$image'>
                                    </figure>
                                </td>
                            </tr>
                            <tr>
                                <td>" . $prod['nom'] . "</td>
                                <td>x" . $prod['quantite'] . "</td>
                            </tr>
                        </table>
                    </li>";
                }
            $html .= "</ul>
        </article>";
        echo $html;
        
        $dateExp = $encour['dateExpedition'] == '' ? 'Non expédiée' : formatDate($encour['dateExpedition']);
        $dateLiv = $encour['dateLivraison'] == '' ? 'Non livrée' : formatDate($encour['dateLivraison']);

        echo "<dialog id='c-" . $encour['idCommande'] . "' class='popup-commande'>
            <h2>Commande du " . formatDate($encour['dateCommande']) . "</h2>
            <ul>";
                $total = 0;
                foreach ($prods as $prod) {
                    $imageSTMT = $pdo->prepare('select URL from _imageDeProduit where idProduit = ?');
                    $imageSTMT->execute([$prod['idProduit']]);
                    $image = $imageSTMT->fetchColumn();

                    $total += intval($prod['prix']) * intval($prod['quantite']);
                    $total = formatPrice($total);

                    echo "<li>
                        <table>
                            <tr>
                                <td colspan=2><img src='$image'></td>
                            </tr>
                            <tr>
                                <td>" . $prod['nom'] . "</td>
                                <td>x" . $prod['quantite'] . "</td>
                            </tr>
                            <tr>
                                <td>" . formatPrice($prod['prix']) . "</td>
                                <td>" . formatPrice($prod['prix'] * $prod['quantite']) . "</td>
                            </tr>
                        </table>
                    </li>";
                }
            echo "</ul>
            <table>
                <tr>
                    <td>Expédition : $dateExp</td>
                    <td>Client : " . $encour['pseudo'] . "</td>
                </tr>
                <tr>
                    <td>Livraison : $dateLiv</td>
                    <td>N° Commande : " . $encour['idCommande'] . "</td>
                </tr>
                <tr>
                    <td colspan=2>Total : $total</td>
                </tr>
            </table>
        </dialog>";
    }
?>
        </section>

        <section class="livre">
            <h1>Finalisées</h1>
<?php
    $cmdsSTMT = $pdo->prepare(file_get_contents('../../queries/backoffice/commandes/livre.sql'));
    $cmdsSTMT->execute([$_SESSION['id']]);
    $livres = $cmdsSTMT->fetchAll(PDO::FETCH_ASSOC);

    if (count($livres) == 0) echo "<h2>Aucune commande finalisée</h2>";

    foreach ($livres as $livre) {
        $prodsSTMT = $pdo->prepare(file_get_contents('../../queries/backoffice/commandes/prodsDeCommande.sql'));
        $prodsSTMT->execute([$livre['idCommande'], $_SESSION['id']]);
        $prods = $prodsSTMT->fetchAll(PDO::FETCH_ASSOC);

        $html = "<article id='c-" . $livre['idCommande'] . "'>
            <ul>
                <li><h2>" . $livre['etatLivraison'] . "</h2></li>
                <li>" . formatDate($livre['dateCommande']) . "</li>
            </ul>
            <ul>";
                foreach ($prods as $prod) {
                    $html .= "<li>
                        <table>
                            <tr>
                                <td colspan=2>
                                    <figure>
                                        <img src='../../public/images/caramels.png'>
                                    </figure>
                                </td>
                            </tr>
                            <tr>
                                <td>" . $prod['nom'] . "</td>
                                <td>x" . $prod['quantite'] . "</td>
                            </tr>
                        </table>
                    </li>";
                }
            $html .= "</ul>
        </article>";
        echo $html;

        $dateExp = formatDate($livre['dateExpedition']); $dateExp = ($dateExp === '01/01/1970') ? 'Non expédiée' : $dateExp;
        $dateLiv = formatDate($livre['dateLivraison']); $dateLiv = ($dateLiv === '01/01/1970') ? 'Non livrée' : $dateLiv;

        echo "<dialog id='c-" . $livre['idCommande'] . "' class='popup-commande'>
            <h2>Commande du " . formatDate($livre['dateCommande']) . "</h2>
            <ul>";
                $total = 0;
                foreach ($prods as $prod) {
                    $imageSTMT = $pdo->prepare('select URL from _imageDeProduit where idProduit = ?');
                    $imageSTMT->execute([$prod['idProduit']]);
                    $image = $imageSTMT->fetchColumn();

                    $total += $prod['prix'] * $prod['quantite'];
                    $total = formatPrice($total);

                    echo "<li>
                        <table>
                            <tr>
                                <td colspan=2><img src='../../public/images/caramels.png'></td>
                            </tr>
                            <tr>
                                <td>" . $prod['nom'] . "</td>
                                <td>x" . $prod['quantite'] . "</td>
                            </tr>
                            <tr>
                                <td>" . formatPrice($prod['prix']) . "</td>
                                <td>" . formatPrice($prod['prix'] * $prod['quantite']) . "</td>
                            </tr>
                        </table>
                    </li>";
                }
            echo "</ul>
            <table>
                <tr>
                    <td>Expédition : $dateExp</td>
                    <td>Client : " . $livre['pseudo'] . "</td>
                </tr>
                <tr>
                    <td>Livraison : $dateLiv</td>
                    <td>N° Commande : " . $livre['idCommande'] . "</td>
                </tr>
                <tr>
                    <td colspan=2>Total : $total</td>
                </tr>
            </table>
        </dialog>";
    }
?>
        </section>

        <?php require_once './partials/retourEnHaut.php' ?>
    </main>

    <?php require_once './partials/footer.php' ?>

    <script src="../../public/amd-shim.js"></script>
    <script src="../../public/script.js"></script>
</body>

</html>
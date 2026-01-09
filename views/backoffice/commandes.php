<?php
    require_once '../../controllers/auth.php';

    require_once '../../controllers/date.php';
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

        $html = "<article>
            <ul>
                <li><h2>" . $encour['etatLivraison'] . "...</h2></li>
                <li>" . formatDate($encour['dateCommande']) . "</li>
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
                                <td>x" . $prod['quantiteProduit'] . "</td>
                            </tr>
                        </table>
                    </li>";
                }
            $html .= "</ul>
        </article>";
        echo $html;
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

        $html = "<article>
            <ul>
                <li><h2>" . $livre['etatLivraison'] . "...</h2></li>
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
                                <td>x" . $prod['quantiteProduit'] . "</td>
                            </tr>
                        </table>
                    </li>";
                }
            $html .= "</ul>
        </article>";
        echo $html;
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
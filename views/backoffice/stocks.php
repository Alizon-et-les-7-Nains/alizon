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
    <title>Alizon - Stocks</title>

    <link rel="icon" href="/public/images/logoBackoffice.svg">
    <link rel="stylesheet" href="/public/style.css">
</head>

<body class="backoffice">
    <?php require_once './partials/header.php' ?>

    <?php
        $currentPage = basename(__FILE__);
        require_once './partials/aside.php';
    ?>

    <main class="backoffice-stocks">
        <section>
            <h1>Produits Épuisés</h1>
            <article>
<?php
$epuisesSTMT = $pdo->prepare(file_get_contents('../../queries/backoffice/produitsEpuises.sql'));
$epuisesSTMT->execute([':idVendeur' => $_SESSION['id']]);
$epuises = $epuisesSTMT->fetchAll(PDO::FETCH_ASSOC);
if (count($epuises) == 0) echo "<h2>Aucun produit épuisé</h2>";
    foreach ($epuises as $epuise) {
        $reassort = $epuise['dateReassort'] != NULL ? "Réassort prévu le " . formatDate($epuise['dateReassort']) : 'Aucun réassort prévu';
        $image = ($pdo->query('select * from _imageDeProduit where idProduit = ' . $epuise['idProduit']))->fetchAll(PDO::FETCH_ASSOC);
        $image = $image = !empty($image) ? $image[0]['URL'] : '';
        $commandes = $pdo->prepare(file_get_contents('../../queries/backoffice/dernieresCommandesProduit.sql'));
        $commandes->execute(['idProduit' => $epuise['idProduit'], 'idVendeur' => $_SESSION['id']]);
        $commandes = $commandes->fetchAll(PDO::FETCH_ASSOC);
        $html = "<div>
                    <button class='settings' id='" . $epuise['idProduit'] . "'>
                        <div><div></div></div>
                        <div><div class='right'></div></div>
                        <div><div></div></div>
                    </button>

                    <table>
                        <tr>
                            <td rowspan=2>
                                <table>
                                    <tr>
                                        <td rowspan=4><img src='$image'></td>
                                        <th>" . $epuise['nom'] . "</th>
                                    </tr>
                                    <tr>
                                        <td class='type'>" . $epuise['typeProd'] . "</td>
                                    </tr>
                                    <tr>
                                        <th>" . formatPrice($epuise['prix']) . "</th>
                                    </tr>
                                    <tr>
                                        <th>
                                            <figure>
                                                <figcaption>" . str_replace('.', ',', $epuise['note']) . "</figcaption>
                                                <img src='/public/images/etoile.svg'>
                                            </figure>
                                        </th>
                                    </tr>
                                </table>
                            </td>
                            <th colspan=2>Dernières commandes</th>
                        </tr>
                        <tr>
                            <td>
                                <ul>";
                                    if (count($commandes) == 0) $html .= "<li><h3>Aucune commande</h3></li>";
                                    foreach ($commandes as $commande) {
                                        $html .= "<ul>
                                            <li>" . $commande['quantiteCommande'] . "</li>
                                            <li>" . formatDate($commande['dateCommande']) . "</li>
                                        </ul>";
                                    }
                                $html .= "</ul>
                            </td>
                        </tr>
                    </table>
                    <ul>
                        <li>
                            <figure>
                                <img src='/public/images/infoDark.svg'>
                                <figcaption>" . $reassort . "</figcaption>
                            </figure>
                        </li>
                        <li><h2>Épuisé le " . formatDate($epuise['dateStockEpuise']) . "</h2></li>
                    </ul>
                </div>";
        echo $html;
echo "
    <dialog class='reassort' id='d-" . $epuise['idProduit'] ."'>
        <h1>Paramètres de réassort</h1>
        <form action='../../controllers/reassort.php' method='post'> 
            <input type='hidden' name='idProduit' value='" . $epuise['idProduit'] . "'>   
            <input type='number' placeholder='Seuil d&#39;alerte' value='" . $epuise['seuilAlerte'] . "' name='seuil' id='seuil'>
            <label for='seuil' id='errorFieldSeuil'>Doit être un entier</label>
            <input type='date' placeholder='Date du réassort' value='" . $epuise['dateReassort'] . "' name='date' id='dateReassort'>
            <label for='date' id='errorFieldDate'>Ne doit pas être passée</label>
            <input type='number' placeholder='Réassortir' name='reassort' id='reassort'>
            <label for='reassort' id='errorFieldReassort'>Doit être un entier</label>
            <ul>
                <li><input type='button' value='Annuler' class='annuler'></li>
                <li><input type='submit' value='Valider' id='buttonConfirm'></li>
            </ul>
        </form>
    </dialog>
";
    }
?>
            </article>
        </section>

        <section>
            <h1>Produits en Alerte</h1>
            <article>
<?php
$faiblesSTMT = $pdo->prepare(file_get_contents('../../queries/backoffice/stockFaible.sql'));
$faiblesSTMT->execute([':idVendeur' => $_SESSION['id']]);
$faibles = $faiblesSTMT->fetchAll(PDO::FETCH_ASSOC);
if (count($faibles) == 0) echo "<h2>Aucun produit en alerte</h2>";
    foreach ($faibles as $faible) {
        $reassort = $faible['dateReassort'] != NULL ? "Réassort prévu le " . formatDate($faible['dateReassort']) : 'Aucun réassort prévu';
        $image = ($pdo->query('select * from _imageDeProduit where idProduit = ' . $faible['idProduit']))->fetchAll(PDO::FETCH_ASSOC);
        $image = $image = !empty($image) ? $image[0]['URL'] : '';
        $commandes = $pdo->prepare(file_get_contents('../../queries/backoffice/dernieresCommandesProduit.sql'));
        $commandes->execute(['idProduit' => $faible['idProduit'], 'idVendeur' => $_SESSION['id']]);
        $commandes = $commandes->fetchAll(PDO::FETCH_ASSOC);
        $html = "<div>
                    <button class='settings' id='" . $faible['idProduit'] . "'>
                        <div><div></div></div>
                        <div><div class='right'></div></div>
                        <div><div></div></div>
                    </button>

                    <table>
                        <tr>
                            <td rowspan=2>
                                <table>
                                    <tr>
                                        <td rowspan=4><img src='$image'></td>
                                        <th>" . $faible['nom'] . "</th>
                                    </tr>
                                    <tr>
                                        <td class='type'>" . $faible['typeProd'] . "</td>
                                    </tr>
                                    <tr>
                                        <th>" . formatPrice($faible['prix']) . "</th>
                                    </tr>
                                    <tr>
                                        <th>
                                            <figure>
                                                <figcaption>" . str_replace('.', ',', $faible['note']) . "</figcaption>
                                                <img src='/public/images/etoile.svg'>
                                            </figure>
                                        </th>
                                    </tr>
                                </table>
                            </td>
                            <th colspan=2>Dernières commandes</th>
                        </tr>
                        <tr>
                            <td>
                                <ul>";
                                    if (count($commandes) == 0) $html .= "<li><h3>Aucune commande</h3></li>";
                                    foreach ($commandes as $commande) {
                                        $html .= "<ul>
                                            <li>" . $commande['quantiteCommande'] . "</li>
                                        <li>" . formatDate($commande['dateCommande']) . "</li>
                                    </ul>";
                                }
                                $html .= "</ul>
                            </td>
                        </tr>
                    </table>
                    <ul>
                        <li>
                            <figure>
                                <img src='/public/images/infoDark.svg'>
                                <figcaption>" . $reassort . "</figcaption>
                            </figure>
                        </li>
                        <li><h2>" . $faible['stock'] . " restants</h2></li>
                    </ul>
                </div>";
        echo $html;
echo "
    <dialog class='reassort' id='d-" . $faible['idProduit'] ."'>
        <h1>Paramètres de réassort</h1>
        <form action='../../controllers/reassort.php' method='post'> 
            <input type='hidden' name='idProduit' value='" . $faible['idProduit'] . "'>   
            <input type='number' placeholder='Seuil d&#39;alerte' value='" . $faible['seuilAlerte'] . "' name='seuil' id='seuil'>
            <label for='seuil' id='errorFieldSeuil'>Doit être un entier</label>
            <input type='date' placeholder='Date du réassort' value='" . $faible['dateReassort'] . "' name='date' id='dateReassort'>
            <label for='date' id='errorFieldDate'>Ne doit pas être passée</label>
            <input type='number' placeholder='Réassortir' name='reassort' id='reassort'>
            <label for='reassort' id='errorFieldReassort'>Doit être un entier</label>
            <ul>
                <li><input type='button' value='Annuler' class='annuler'></li>
                <li><input type='submit' value='Valider' id='buttonConfirm'></li>
            </ul>
        </form>
    </dialog>
";
    }
?>
            </article>
        </section>

        <section>
            <h1>Produits en Stock</h1>
            <article>
<?php
$stocksSTMT = $pdo->prepare(file_get_contents('../../queries/backoffice/produitsStock.sql'));
$stocksSTMT->execute([':idVendeur' => $_SESSION['id']]);
$stocks = $stocksSTMT->fetchAll(PDO::FETCH_ASSOC);
    if (count($stocks) == 0) echo "<h3>Aucun produit en stock</h3>";
    foreach ($stocks as $stock) {
        $reassort = $stock['dateReassort'] != NULL ? "Réassort prévu le " . formatDate($stock['dateReassort']) : 'Aucun réassort prévu';
        $image = ($pdo->query('select * from _imageDeProduit where idProduit = ' . $stock['idProduit']))->fetchAll(PDO::FETCH_ASSOC);
        $image = $image = !empty($image) ? $image[0]['URL'] : '';
        $commandes = $pdo->prepare(file_get_contents('../../queries/backoffice/dernieresCommandesProduit.sql'));
        $commandes->execute(['idProduit' => $stock['idProduit'], 'idVendeur' => $_SESSION['id']]);
        $commandes = $commandes->fetchAll(PDO::FETCH_ASSOC);
        $html = "<div>
                    <button class='settings' id='" . $stock['idProduit'] . "'>
                        <div><div></div></div>
                        <div><div class='right'></div></div>
                        <div><div></div></div>
                    </button>

                    <table>
                        <tr>
                            <td rowspan=2>
                                <table>
                                    <tr>
                                        <td rowspan=4><img src='$image'></td>
                                        <th>" . $stock['nom'] . "</th>
                                    </tr>
                                    <tr>
                                        <td class='type'>" . $stock['typeProd'] . "</td>
                                    </tr>
                                    <tr>
                                        <th>" . formatPrice($stock['prix']) . "</th>
                                    </tr>
                                    <tr>
                                        <th>
                                            <figure>
                                                <figcaption>" . str_replace('.', ',', $stock['note']) . "</figcaption>
                                                <img src='/public/images/etoile.svg'>
                                            </figure>
                                        </th>
                                    </tr>
                                </table>
                            </td>
                            <th colspan=2>Dernières commandes</th>
                        </tr>
                        <tr>
                            <td>
                                <ul>";
                                    if (count($commandes) == 0) $html .= "<li><h3>Aucune commande</h3></li>";
                                    foreach ($commandes as $commande) {
                                        $html .= "<ul>
                                            <li>" . $commande['quantiteCommande'] . "</li>
                                            <li>" . formatDate($commande['dateCommande']) . "</li>
                                        </ul>";
                                    }
                                $html .= "</ul>
                            </td>
                        </tr>
                    </table>
                    <ul>
                        <li>
                            <figure>
                                <img src='/public/images/infoDark.svg'>
                                <figcaption>" . $reassort . "</figcaption>
                            </figure>
                        </li>
                        <li><h2>" . $stock['stock'] . " restants</h2></li>
                    </ul>
                </div>";
        echo $html;
echo "
    <dialog class='reassort' id='d-" . $stock['idProduit'] ."'>
        <h1>Paramètres de réassort</h1>
        <form action='../../controllers/reassort.php' method='post'> 
            <input type='hidden' name='idProduit' value='" . $stock['idProduit'] . "'>   
            <input type='number' placeholder='Seuil d&#39;alerte' value='" . $stock['seuilAlerte'] . "' name='seuil' id='seuil'>
            <label for='seuil' id='errorFieldSeuil'>Doit être un entier</label>
            <input type='date' placeholder='Date du réassort' value='" . $stock['dateReassort'] . "' name='date' id='dateReassort'>
            <label for='date' id='errorFieldDate'>Ne doit pas être passée</label>
            <input type='number' placeholder='Réassortir' name='reassort' id='reassort'>
            <label for='reassort' id='errorFieldReassort'>Doit être un entier</label>
            <ul>
                <li><input type='button' value='Annuler' class='annuler'></li>
                <li><input type='submit' value='Valider' id='buttonConfirm'></li>
            </ul>
        </form>
    </dialog>
";
    }
?>
            </article>
        </section>

        <?php require_once './partials/retourEnHaut.php' ?>
    </main>

    <?php require_once './partials/footer.php' ?>

    <script src="../../public/amd-shim.js"></script>
    <script src="../../public/script.js"></script>
</body>

</html>
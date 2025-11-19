<?php
    require_once '../../controllers/pdo.php';
    require_once '../../controllers/prix.php';
    require_once '../../controllers/date.php';
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

    <?php require_once './partials/aside.php' ?>

    <main class="backoffice-stocks">
        <section>
            <h1>Produits Épuisés</h1>
            <article>
<?php
    $epuises = ($pdo->query('select * from _produit where stock = 0'))->fetchAll(PDO::FETCH_ASSOC);
    if (count($epuises) == 0) echo "<h2>Aucun produit épuisé</h2>";
    foreach ($epuises as $epuise) {
        $image = ($pdo->query('select * from _imageDeProduit where idProduit = ' . $epuise['idProduit']))->fetchAll(PDO::FETCH_ASSOC);
        $image = $image = !empty($image) ? $image[0]['URL'] : '';
        $commandes = $pdo->prepare(file_get_contents('../../queries/backoffice/dernieresCommandesProduit.sql'));
        $commandes->execute(['idProduit' => $epuise['idProduit']]);
        $commandes = $commandes->fetchAll(PDO::FETCH_ASSOC);
        $html = "<div>
                    <button class='settings'>
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
                                        <td>" . $epuise['typeProd'] . "</td>
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
                                <figcaption>Aucun réassort prévu</figcaption>
                            </figure>
                        </li>
                        <li>Épuisé le 29 août</li>
                    </ul>
                </div>";
        echo $html;
    }
?>
            </article>
        </section>

        <section>
            <h1>Produits en Alerte</h1>
            <article>
            </article>
        </section>

        <section>
            <h1>Produits en Stock</h1>
            <article>
                <div>
                    <button class="settings">
                        <div><div></div></div>
                        <div><div class="right"></div></div>
                        <div><div></div></div>
                    </button>

                    <table>
                        <tr>
                            <td rowspan=2>
                                <table>
                                    <tr>
                                        <td rowspan=4><img src='/public/images/rillettes.png'></td>
                                        <th colspan>Rillettes de thon caca pipi</th>
                                    </tr>
                                    <tr>
                                        <td>Charcuterie</td>
                                    </tr>
                                    <tr>
                                        <th>29,99€</th>
                                    </tr>
                                    <tr>
                                        <th>
                                            <figure>
                                                <figcaption>3,5</figcaption>
                                                <img src="/public/images/etoile.svg">
                                            </figure>
                                        </th>
                                    </tr>
                                </table>
                            </td>

                            <th colspan=2>Dernières commandes</th>
                        </tr>

                        <tr>
                            <td>
                                <ul>
                                    <li>
                                        <ul>
                                            <li>2</li>
                                            <li>09/07/2025</li>
                                        </ul>
                                    </li>
                                    <li>
                                        <ul>
                                            <li>2</li>
                                            <li>09/07/2025</li>
                                        </ul>
                                    </li>
                                </ul>
                            </td>
                        </tr>
                    </table>

                    <ul>
                        <li>
                            <figure>
                                <img src="/public/images/infoDark.svg">
                                <figcaption>Aucun réassort prévu</figcaption>
                            </figure>
                        </li>
                        <li>45 restants</li>
                    </ul>
                </div>
            </article>
        </section>

        <?php require_once './partials/retourEnHaut.php' ?>
    </main>

    <?php require_once './partials/footer.php' ?>

    <script src="../../public/amd-shim.js"></script>
    <script src="../../public/script.js"></script>
</body>

</html>
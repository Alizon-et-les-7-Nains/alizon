<?php
    require_once '../../controllers/pdo.php';
    require_once '../../controllers/auth.php';
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alizon - Statistiques</title>

    <link rel="icon" href="/public/images/logoBackoffice.svg">
    <link rel="stylesheet" href="/public/style.css">
</head>

<body class="backoffice">
    <?php require_once './partials/header.php' ?>

    <?php
        $currentPage = basename(__FILE__);
        require_once './partials/aside.php';
    ?>

    <main class="backoffice-stats">
        <section>
            <h1>Ventes</h1>

            <table>
                <tr>
                    <td><button class="selected">Journalier</button></td>
                    <td><button>Hebdomadaire</button></td>
                    <td><button>Mensuel</button></td>
                    <td><button>Annuel</button></td>
                </tr>
                <tr>
                    <th colspan=2>Filtrer par Catégorie</th>
                    <th colspan=2>Filtrer par Produit</th>
                </tr>
                <tr>
                    <td colspan=2>
                        <select name="category" id="category">
                            <option value="" default>Aucun filtre de catégorie</option>
                        </select>
                    </td>
                    <td colspan=2>
                        <select name="product" id="product">
                            <option value="" default hidden>Aucun filtre de produit</option>
                        </select>
                    </td>
                </tr>
            </table>

            <ul class="stats">
                <li><button id="prev"><img src="../../public/images/flecheDroite.svg" alt="Précédent"></button></li>
                <li>
                    <article>
                        <canvas id="stats"></canvas>
                    </article>
                </li>
                <li><button id="next" disabled><img src="../../public/images/flecheDroite.svg" alt="Suivant"></button></li>
            </ul>
            
            <table>
                <tr>
                    <th>Ventes</th>
                    <th>Chiffre d'affaires</th>
                </tr>
                <tr>
                    <td>
                        <figure>
                            <figcaption id="ventes">378</figcaption>
                        </figure>
                    </td>
                    <td>
                        <figure>
                            <figcaption id="argents">1437,50€</figcaption>
                        </figure>
                    </td>
                </tr>
            </table>
        </section>

        <?php require_once './partials/retourEnHaut.php' ?>
    </main>

    <?php require_once './partials/footer.php' ?>

    <script src="../../public/script.js"></script>
    <script type="module" src="../scripts/backoffice/charts.js"></script>
    <script type="module" src="../scripts/backoffice/stats.js"></script>
</body>

</html>
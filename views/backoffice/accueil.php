<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Alizon</title>

        <link rel="stylesheet" href="../../public/style.css">
    </head>

    <body class="backoffice">
        <?php require_once './partials/headerMain.php' ?>

        <?php require_once './partials/aside.php' ?>

        <main class="acceuilBackoffice">
            <section>
                <h1>Derniers Bilans</h1>
                <article>
                    <table>
                        <thead>
                            <tr>
                                <td><button class="here">Journalier</button></td>
                                <td><button>Hebdomadaire</button></td>
                                <td><button>Mensuel</button></td>
                            </tr>
                        </thead>

                        <tbody>
                            <tr>
                                <td>Nombre de ventes</td>
                                <td colspan=2>Chiffre d'affaires</td>
                            </tr>
                            
                            <tr>
                                <td>
                                    <figure>
                                        <img src="/public/images/arrowDestonks.svg">
                                        <figcaption class="neg">46</figcaption>
                                    </figure>
                                </td>
                                <td>
                                    <figure colspan=2>
                                        <img src="/public/images/arrowStonks.svg">
                                        <figcaption class="pos">1.634,50€</figcaption>
                                    </figure>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </article>
                <a href="" title="Voir plus"><img src="/public/images/infoDark.svg"></a>
            </section>

            <section>
                <h1>Stocks Faible</h1>
                <a href="" title="Voir plus"><img src="/public/images/infoDark.svg"></a>
            </section>

            <section>
                <h1>Dernières Commandes</h1>
                <a href="" title="Voir plus"><img src="/public/images/infoDark.svg"></a>
            </section>

            <section>
                <h1>Derniers Avis</h1>
                <a href="" title="Voir plus"><img src="/public/images/infoDark.svg"></a>
            </section>

            <section>
                <h1>Produits en Vente</h1>
                <a href="" title="Voir plus"><img src="/public/images/infoDark.svg"></a>
            </section>
        </main>

        <?php require_once './partials/footer.php' ?>
    </body>
</html>
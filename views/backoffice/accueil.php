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
        <?php require_once './partials/headerMain.php' ?>

        <?php require_once './partials/aside.php' ?>

        <main class="acceuilBackoffice">
            <!--
            <section>
                <h1>Derniers Bilans</h1>
                <article>
                    <table>
                        <thead>
                            <tr>
                                <td><button class="bilan here">Journalier</button></td>
                                <td><button class="bilan">Hebdomadaire</button></td>
                                <td><button class="bilan">Mensuel</button></td>
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
            -->

            <section class="stock">
                <h1>Stocks Faible</h1>
                <article>
                    <table>
                        <tr>
                            <td colspan=2><img src="/public/images/rilletes.svg"></td>
                        </tr>
                        <tr>
                            <td colspan=2>Rillettes</td>
                        </tr>
                        <tr>
                            <td>29,99€</td>
                            <td class="epuise">0</td>
                        </tr>
                    </table>
                    <table>
                        <tr>
                            <td colspan=2><img src="/public/images/rilletes.svg"></td>
                        </tr>
                        <tr>
                            <td colspan=2>Rillettes</td>
                        </tr>
                        <tr>
                            <td>29,99€</td>
                            <td class="epuise">0</td>
                        </tr>
                    </table>
                    <table>
                        <tr>
                            <td colspan=2><img src="/public/images/rilletes.svg"></td>
                        </tr>
                        <tr>
                            <td colspan=2>Rillettes</td>
                        </tr>
                        <tr>
                            <td>29,99€</td>
                            <td class="epuise">0</td>
                        </tr>
                    </table>
                    <table>
                        <tr>
                            <td colspan=2><img src="/public/images/rilletes.svg"></td>
                        </tr>
                        <tr>
                            <td colspan=2>Rillettes</td>
                        </tr>
                        <tr>
                            <td>29,99€</td>
                            <td class="faible">5</td>
                        </tr>
                    </table>
                    <table>
                        <tr>
                            <td colspan=2><img src="/public/images/rilletes.svg"></td>
                        </tr>
                        <tr>
                            <td colspan=2>Rillettes</td>
                        </tr>
                        <tr>
                            <td>29,99€</td>
                            <td class="faible">6</td>
                        </tr>
                    </table>
                    <table>
                        <tr>
                            <td colspan=2><img src="/public/images/rilletes.svg"></td>
                        </tr>
                        <tr>
                            <td colspan=2>Rillettes</td>
                        </tr>
                        <tr>
                            <td>29,99€</td>
                            <td class="faible">12</td>
                        </tr>
                    </table>
                </article>
                <a href="" title="Voir plus"><img src="/public/images/infoDark.svg"></a>
            </section>

            <section>
                <h1>Dernières Commandes</h1>
                <a href="" title="Voir plus"><img src="/public/images/infoDark.svg"></a>
            </section>

            <section class="avis">
                <h1>Derniers Avis</h1>
                <article>
                    <ul>
                        <li>
                            <table>
                                <tr>
                                    <td rowspan=3></td>
                                    <td>Pneu</td>
                                    <td>
                                        <figure>
                                            <figcaption>3,5</figcaption>
                                            <img src="/public/images/etoile.svg">
                                        </figure>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Douceur gourmande</td>
                                    <td>Un cidre délicat, à la robe claire et lumineuse, aux arômes de pomme fraîchement cueillie. La bouche est souple et veloutée, dominée par une belle rondeur sucrée qui en fait une boisson conviviale et facile à apprécier. À déguster bien frais, seul ou en accompagnement de desserts fruités.</td>
                                    <td>
                                        <ul>
                                            <li><input type="text" placeholder="Écrivez la réponse ici..." name="" id=""></li>
                                            <li><button>Répondre</button></li>
                                            <li>
                                                <ul>
                                                    <li><img src=""></li>
                                                    <li><img src=""></li>
                                                    <li>|</li>
                                                    <li>Signaler</li>
                                                </ul>
                                            </li>
                                        </ul>
                                    </td>
                                </tr>
                            </table>
                        </li>
                    </ul>
                </article>
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
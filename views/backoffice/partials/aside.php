<aside class="backoffice">
    <ul>
        <?php $class = __FILE__ == 'acceuil.php' ? 'here' : ''; ?>
        <li class="aside-btn here">
            <figure>
                <img src="/public/images/homeDarkBlue.svg">
                <figcaption>Accueil</figcaption>
            </figure>
        </li>
        <?php $class = __FILE__ == 'produits.php' ? 'here' : ''; ?>
        <li class="aside-btn">
            <figure>
                <img src="/public/images/cartDarkBlue.svg">
                <figcaption>Produits</figcaption>
            </figure>
        </li>
        <?php $class = __FILE__ == 'stock.php' ? 'here' : ''; ?>
        <li class="aside-btn">
            <figure>
                <img src="/public/images/boiteDark.svg">
                <figcaption>Stock</figcaption>
            </figure>
        </li>
        <?php $class = __FILE__ == 'avis.php' ? 'here' : ''; ?>
        <li class="aside-btn">
            <figure>
                <img src="/public/images/chatDark.svg">
                <figcaption>Avis</figcaption>
            </figure>
        </li>
        <?php $class = __FILE__ == 'commandes.php' ? 'here' : ''; ?>
        <li class="aside-btn">
            <figure>
                <img src="/public/images/cartCheckDark.svg">
                <figcaption>Commandes</figcaption>
            </figure>
        </li>
    </ul>
</aside>
<aside class="backoffice">
    <ul>
        <?php echo $currentPage; ?>
        <?php $class = $currentPage == 'acceuil.php' ? 'here' : ''; ?>
        <li class="aside-btn <?php echo $class; ?>">
            <figure>
                <img src="/public/images/homeDarkBlue.svg">
                <figcaption>Accueil</figcaption>
            </figure>
        </li>
        <?php $class = $currentPage == 'produits.php' ? 'here' : ''; ?>
        <li class="aside-btn <?php echo $class; ?>">
            <figure>
                <img src="/public/images/cartDarkBlue.svg">
                <figcaption>Produits</figcaption>
            </figure>
        </li>
        <?php $class = $currentPage == 'stock.php' ? 'here' : ''; ?>
        <li class="aside-btn <?php echo $class; ?>">
            <figure>
                <img src="/public/images/boiteDark.svg">
                <figcaption>Stock</figcaption>
            </figure>
        </li>
        <?php $class = $currentPage == 'avis.php' ? 'here' : ''; ?>
        <li class="aside-btn <?php echo $class; ?>">
            <figure>
                <img src="/public/images/chatDark.svg">
                <figcaption>Avis</figcaption>
            </figure>
        </li>
        <?php $class = $currentPage == 'commandes.php' ? 'here' : ''; ?>
        <li class="aside-btn <?php echo $class; ?>">
            <figure>
                <img src="/public/images/cartCheckDark.svg">
                <figcaption>Commandes</figcaption>
            </figure>
        </li>
    </ul>
</aside>
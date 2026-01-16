<aside class="backoffice">
    <ul>
        <?php $class = $currentPage == 'accueil.php' ? 'here' : ''; ?>
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
        <?php $class = $currentPage == 'stocks.php' ? 'here' : ''; ?>
        <li class="aside-btn <?php echo $class; ?>">
            <figure>
                <img src="/public/images/boiteDark.svg">
                <figcaption>Stocks</figcaption>
            </figure>
        </li>
        <?php $class = $currentPage == 'commandes.php' ? 'here' : ''; ?>
        <li class="aside-btn <?php echo $class; ?>">
            <figure>
                <img src="/public/images/cartCheckDark.svg">
                <figcaption>Commandes</figcaption>
            </figure>
        </li>
        <?php $class = $currentPage == 'avis.php' ? 'here' : ''; ?>
        <li class="aside-btn <?php echo $class; ?>">
            <figure>
                <img src="/public/images/chatDark.svg">
                <figcaption>Avis</figcaption>
            </figure>
        </li>
        <?php $class = $currentPage == 'notification.php' ? 'here' : ''; ?>
        <li class="aside-btn <?php echo $class; ?>">
            <figure>
                <img id="focus" src="../../public/images/bellRingLight.svg" alt="Nouvelle notification">
                <figcaption>Notifications</figcaption>
            </figure>
        </li>
    </ul>
</aside>
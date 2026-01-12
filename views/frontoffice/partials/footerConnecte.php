<footer class="footerFront">
        <div class="footerPC">
            <div>
                <a href="../frontoffice/legalesConnecte.php">Conditions générales de vente</a>
                <a href="../frontoffice/legalesConnecte.php">Mentions légales</a>
                <p>© 2025 Alizon Tous droits réservés.</p>
            </div>
        </div>

        <div class="footerTel">
            <a href="../frontoffice/accueilConnecte.php"><img src="../../../public/images/homeLightBlue.svg" alt="" class="homeLightBlue"></a>
            <a href="../frontoffice/panier.php"><img src="../../../public/images/cartLightBlue.svg" alt="" class="cartLightBlue"></a>
            <a href="javascript:void(0);" onclick="menuBurgerTel();" style="margin-right: 0px;"><img src="../../../public/images/burgerLightBlue.svg" alt=""class="burgerLightBlue"></a>
        </div> 

        <section id="burgerIconTel">
            <a href="../frontoffice/compteClient.php">Mon compte</a>
            <a href="../frontoffice/commandes.php">Mes commandes</a>
            <a href="../frontoffice/mesAvis.php">Mes commentaires</a>
            <a href="../frontoffice/legalesConnecte.php" class="separation">Mentions légales</a>
            <a href="../frontoffice/connexionClient.php">Déconnexion</a>
        </section>

</footer>

<script>
function menuBurgerTel() {
    var burgerIcon = document.getElementById("burgerIconTel");
    burgerIcon.style.display = (burgerIcon.style.display === "flex") ? "none" : "flex";
}
</script>
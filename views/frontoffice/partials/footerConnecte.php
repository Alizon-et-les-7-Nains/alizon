<!-- Footer pour les utilisateurs connectés - Affiche les liens légaux et le menu mobile -->
<footer class="footerFront">
        <!-- Pied de page version PC avec liens légaux -->
        <div class="footerPC">
            <div>
                <a href="../frontoffice/legalesConnecte.php">Conditions générales de vente</a>
                <a href="../frontoffice/legalesConnecte.php">Mentions légales</a>
                <p>© 2025 Alizon Tous droits réservés.</p>
            </div>
        </div>

        <!-- Barre de navigation mobile avec icônes (accueil, panier, menu burger) -->
        <div class="footerTel">
            <a href="../frontoffice/accueilConnecte.php"><img src="../../../public/images/homeLightBlue.svg" alt="" class="homeLightBlue"></a>
            <a href="../frontoffice/panier.php"><img src="../../../public/images/cartLightBlue.svg" alt="" class="cartLightBlue"></a>
            <a href="javascript:void(0);" onclick="menuBurgerTel();" style="margin-right: 0px;"><img src="../../../public/images/burgerLightBlue.svg" alt=""class="burgerLightBlue"></a>
        </div> 

        <!-- Menu burger mobile avec liens de navigation pour utilisateur connecté -->
        <section id="burgerIconTel">
            <a href="../frontoffice/compteClient.php">Mon compte</a>
            <a href="../frontoffice/commandes.php">Mes commandes</a>
            <a href="../frontoffice/notifications.php">Mes notifications</a>
            <a href="../frontoffice/mesAvis.php">Mes commentaires</a>
            <a href="../frontoffice/legalesConnecte.php" class="separation">Mentions légales</a>
            <a href="../frontoffice/connexionClient.php">Déconnexion</a>
        </section>

</footer>

<!-- Script pour gérer l'affichage/masquage du menu burger mobile -->
<script>
// Fonction pour afficher/masquer le menu burger sur mobile
function menuBurgerTel() {
    var burgerIcon = document.getElementById("burgerIconTel");
    burgerIcon.style.display = (burgerIcon.style.display === "flex") ? "none" : "flex";
}
</script>
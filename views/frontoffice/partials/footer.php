<?php
if (session_status() === PHP_SESSION_NONE) {
    if (isset($_COOKIE[session_name()])) {
        session_start(['read_and_close' => true]);
    }
}

$isConnected = isset($_SESSION[session_id()]); 

if ($isConnected){
?>

    <!--------------------------------------------------->
    <!-------------    Footer Connecté ------------------>
    <!--------------------------------------------------->

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../../public/style.css">
    <title>footer</title>
</head>
<body>
  <footer class="footerFront">
    <div class="footerPC">
      <div>
        <a href="">Conditions générales de vente</a>
        <a href="">Mentions légales</a>
        <p>© 2025 Alizon Tous droits réservés.</p>
      </div>
      <div>
        <img src="../../../public/images/whiteLetter.svg" alt="Bouton contact">
      </div>
    </div>
    <div class="footerTel">
      <a href="../acceuilConnecte.php"><img src="../../../public/images/homeLightBlue.svg" alt="" class="homeLightBlue"></a>
      <a href=""><img src="../../../public/images/searchLightBlue.svg" alt="" class="searchLightBlue"></a>
      <a href=""><img src="../../../public/images/cartLightBlue.svg" alt="" class="cartLightBlue"></a>
      <a href="" style="margin-right: 0px;"><img src="../../../public/images/burgerLightBlue.svg" alt="" class="burgerLightBlue"></a>
    </div>
  </footer>
</body>
</html>


<?php } else{ ?>

    <!---------------------------------------------------->
    <!-------------    Footer Déconnecté ----------------->
    <!---------------------------------------------------->

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../../public/style.css">
    <title>footer</title>
</head>
<body>
  <footer class="footerFront">
    <div class="footerPC">
      <div>
        <a href="">Conditions générales de vente</a>
        <a href="">Mentions légales</a>
        <p>© 2025 Alizon Tous droits réservés.</p>
      </div>
      <div>
        <img src="../../../public/images/whiteLetter.svg" alt="Bouton contact">
      </div>
    </div>
    <div class="footerTel">
      <a href="../acceuilDeconnecte.php"><img src="../../../public/images/homeLightBlue.svg" alt="" class="homeLightBlue"></a>
      <a href=""><img src="../../../public/images/searchLightBlue.svg" alt="" class="searchLightBlue"></a>
      <a href=""><img src="../../../public/images/cartLightBlue.svg" alt="" class="cartLightBlue"></a>
      <a href="../inscription.php" style="margin-right: 0px;"><img src="../../../public/images/utilLightBlue.svg" alt=""></a>
    </div>
  </footer>
</body>
</html>
<?php }?>

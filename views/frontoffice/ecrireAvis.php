<?php
    if (isset($_COOKIE[session_name()])) {
        session_start(['read_and_close' => true]);
    }
?>
<?php //require_once "../../controllers/pdo.php" ?> 
<?php //require_once "../../controllers/prix.php" ?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">  
  <link rel="stylesheet" href="../../public/style.css">
  
  <title>Alizon - Ecrire un avis</title>
</head>

  
    <body class="ecrireAvis">
        <?php include './partials/headerConnecte.php'; ?>
        <main>
            <h2>Cet article vous a-t-il plu ?</h2>

            <p>Laissez une note : </p>
            <img src="../../public/images/etoileVide.svg" alt="étoile de séléction">
            <img src="../../public/images/etoileVide.svg" alt="étoile de séléction">
            <img src="../../public/images/etoileVide.svg" alt="étoile de séléction">
            <img src="../../public/images/etoileVide.svg" alt="étoile de séléction">
            <img src="../../public/images/etoileVide.svg" alt="étoile de séléction">

            <p>Ajouter des photos : </p>
            <img src="../../public/images/addImage.svg" alt="Ajouter une image">

            <form id="monForm" action="/inscription.php" method="post" enctype="multipart/form-data">
                <label>Ecrire un commentaire : </label><br>
                <input type="text" placeholder="Sujet" id="sujet" name="sujet" required />
                <div>
                    <textarea placeholder="Message" rows="5"></textarea>
                    <input id="publishButton" type="submit" value="Publier"/>
                </div>
            </form>
        </main>
    </body>
    <?php include './partials/footerConnecte.php'; ?>
</html>
<?php
require_once '../../controllers/pdo.php' ;
    
session_start();

$id_client = 1; //$_SESSION['id_client'];
$idAdresse = 1; //$_SESSION['id_adresse'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    //update la BDD avec les nouvelles infos du user
    $pseudo = $_POST['pseudo'];
    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];
    $email = $_POST['email'];
    $dateNaissance = $_POST['dateNaissance'];
    $telephone = $_POST['telephone'];
    $codePostal = $_POST['codePostal'];
    $adresse1 = $_POST['adresse1'];
    $pays = $_POST['pays'];
    $ville = $_POST['ville'];
    $region = $_POST['region'] ?? '';

    $stmt = $pdo->query(
    "UPDATE _client 
    SET pseudo = '$pseudo', 
    nom = '$nom', 
    prenom = '$prenom', 
    email =  '$email', 
    dateNaissance = '$dateNaissance',
    noTelephone = '$telephone'
    WHERE idClient = '$id_client';");

    $stmt = $pdo->query(
    "UPDATE _adresse 
    SET adresse = '$adresse1',
    pays = '$pays',
    ville = '$ville', 
    codePostal = '$codePostal',
    region = '$region'
    WHERE idAdresse = '$idAdresse';");

}   

    //verification et upload de la nouvelle photo de profil
    $photoPath = '../../public/images/photoDeProfil/photo_profil'.$id_client.'.png';
    if (file_exists($photoPath)) {
        unlink($photoPath); // supprime l’ancien fichier
    }

    if (isset($_FILES['photoProfil']) && $_FILES['photoProfil']['tmp_name'] != '') {
        move_uploaded_file($_FILES['photoProfil']['tmp_name'], '../../public/images/photoDeProfil/photo_profil'.$id_client.'.png');
    }

    //on recupère les infos du user pour les afficher
    $stmt = $pdo->query("SELECT * FROM _client WHERE idClient = '$id_client'");
    $client = $stmt->fetch(PDO::FETCH_ASSOC);

    $pseudo = $client['pseudo'];
    $prenom = $client['prenom'];
    $nom = $client['nom'];
    $dateNaissance = $client['dateNaissance'];
    $email = $client['email'];
    $noTelephone = $client['noTelephone'];

    //on recupère les infos d'adresse du user pour les afficher
    $stmt = $pdo->query("SELECT * FROM _adresse WHERE idAdresse = '$idAdresse'");
    $adresse = $stmt->fetch(PDO::FETCH_ASSOC);

    $pays = $adresse['pays'];
    $ville = $adresse['ville'];
    $codePostal = $adresse['codePostal'];
    $adresse1 = $adresse['adresse'];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Compte</title>
    <link rel="stylesheet" href="../../public/style.css">
</head>
<body>
    <?php include 'partials/headerConnecte.php'; ?>

    <main class="mainCompteClient">
        <form method="POST" enctype="multipart/form-data" action="">
            <div id="titreCompte">
                <div class="photo-container">
                    <?php 
                        if (file_exists($photoPath)) {
                            echo "<img src=".$photoPath." alt=photoProfil id=imageProfile>";
                        } else {
                            echo '<img src="../../public/images/profil.png" alt="photoProfil" id="imageProfile">';
                        }
                    ?>
                </div>
                <h1>Mon Compte</h1>
            </div>

            <section>
                <article>
                    <div><p><?php echo htmlspecialchars($pseudo ?? ''); ?></p></div>
                   <div><p><?php echo htmlspecialchars($prenom ?? ''); ?></p></div>
                   <div><p><?php echo htmlspecialchars($nom ?? ''); ?></p></div>
                   <div><p><?php echo htmlspecialchars($dateNaissance ?? ''); ?></p></div>
                </article>

                <article>
                    <div><p><?php echo htmlspecialchars($adresse1 ?? ''); ?></p></div>
                    <div><p><?php echo htmlspecialchars(" "); ?></p></div>
                    <div class="double-champ">
                        <div><p><?php echo htmlspecialchars($codePostal ?? ''); ?></p></div>
                        <div><p><?php echo htmlspecialchars($ville ?? ''); ?></p></div>
                    </div>
                    <div><p><?php echo htmlspecialchars($pays ?? ''); ?></p></div>
                </article>

                <article>
                    <div><p><?php echo htmlspecialchars($noTelephone ?? ''); ?></p></div>
                    <div><p><?php echo htmlspecialchars($email ?? ''); ?></p></div>
                </article> 
            </section>

            <div id="buttonsCompte">
                <button type="button" onclick="popUpModifierMdp()" class="boutonModifierMdp">Modifier le mot de passe</button>
                <button class="boutonAnnuler" type="button" onclick="boutonAnnuler()">Annuler</button>
                <button type="button" class="boutonModiferProfil">Modifier</button>
            </div>
        </form>

    </main>
    
    <?php include 'partials/footerConnecte.php'; ?>

    <?php 
        $stmt = $pdo->query("SELECT mdp FROM _client WHERE idClient = '$id_client'");
        $tabMdp = $stmt->fetch(PDO::FETCH_ASSOC);
        $mdp = $tabMdp['mdp'];
    ?>
    <script src="../scripts/frontoffice/Chiffrement.js"></script>
    <script>
        const mdp = "<?php echo str_replace('\\', '\\\\', $mdp); ?>";
    </script>
    <script src="../scripts/frontoffice/compteClient.js"></script>
</body>
</html>
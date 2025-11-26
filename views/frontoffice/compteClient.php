<?php
session_start();
require_once '../../controllers/pdo.php' ;
    

if (!isset($_SESSION['user_id'])) {
    header("Location: ../frontoffice/connexionClient.php");
    exit();
}

$id_client = $_SESSION['user_id'];

$stmt = $pdo->query("SELECT idAdresse FROM saedb._client WHERE idClient = '$id_client'");
$client = $stmt->fetch(PDO::FETCH_ASSOC);
$idAdresse = $client['idAdresse'] ?? null;


if (!$idAdresse) {
    $pdo->query("INSERT INTO saedb._adresseClient (`adresse`, region, codePostal, ville, pays, complementAdresse) 
                 VALUES (NULL, NULL, NULL, NULL, NULL, NULL)");
    $idAdresse = $pdo->lastInsertId();
    $pdo->query("UPDATE saedb._client SET idAdresse = $idAdresse WHERE idClient = $id_client");
}

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
    $adresse2 = $_POST['adresse2'];
    $adresse2 = trim($adresse2);
    $pays = $_POST['pays'];
    $ville = $_POST['ville'];

    $stmt = $pdo->query( 
    "UPDATE saedb._client 
    SET pseudo = '$pseudo', 
        nom = '$nom', 
        prenom = '$prenom', 
        email = '$email', 
        dateNaissance = '$dateNaissance',
        noTelephone = '$telephone',
        idAdresse = '$idAdresse'
        WHERE idClient = '$id_client';
    ");


    $stmt = $pdo->query(
    "UPDATE saedb._adresseClient 
    SET `adresse` = '$adresse1',
        pays = '$pays',
        ville = '$ville', 
        codePostal = '$codePostal',
        complementAdresse = '$adresse2'
    WHERE idAdresse = '$idAdresse';");
}
    //verification et upload de la nouvelle photo de profil
    $photoPathBase = '/var/www/html/images/photoProfilClient/photo_profil'.$id_client;
    $photoPath = null;

    $extensionsPossibles = ['png', 'jpg', 'jpeg', 'webp', 'svg'];
    $extension = '';

    foreach ($extensionsPossibles as $ext) {
        if (file_exists($photoPathBase . '.' . $ext)) {
            $extension = '.' . $ext;
            $photoPath = $photoPathBase . $extension;
            break;
        }
    }

    if (file_exists($photoPath)) {
        unlink($photoPath);
    }

    if (isset($_FILES['photoProfil']) && $_FILES['photoProfil']['tmp_name'] != '') {

        $newExt = strtolower(pathinfo($_FILES['photoProfil']['name'], PATHINFO_EXTENSION));
        $photoPath = $photoPathBase . '.' . $newExt;
        move_uploaded_file($_FILES['photoProfil']['tmp_name'], $photoPath);
        $extension = '.' . $newExt;
    }

    //on recupère les infos du user pour les afficher
    $stmt = $pdo->query("SELECT * FROM saedb._client WHERE idClient = '$id_client'");
    $client = $stmt->fetch(PDO::FETCH_ASSOC);

    $pseudo = $client['pseudo'] ?? '';
    $prenom = $client['prenom'] ?? '';
    $nom = $client['nom'] ?? '';
    $dateNaissance = $client['dateNaissance'] ?? '';
    $email = $client['email'] ?? '';
    $noTelephone = $client['noTelephone'] ?? '';

    $stmt = $pdo->query("SELECT * FROM saedb._adresseClient WHERE idAdresse = '$idAdresse'");
    $adresse = $stmt->fetch(PDO::FETCH_ASSOC);

    $adresse1 = $adresse['adresse'] ?? '';
    $adresse2 = $adresse['complementAdresse'] ?? '';
    $codePostal = $adresse['codePostal'] ?? '';
    $ville = $adresse['ville'] ?? '';
    $pays = $adresse['pays'] ?? '';


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="/public/images/logoBackoffice.svg">
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
                            echo '<img src="/images/photoProfilClient/photo_profil' . $id_client . $extension . '" alt="photoProfil" id="imageProfile">';
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
                    <div><p><?php echo htmlspecialchars(!empty($adresse2) ? $adresse2 : 'Complément d\'adresse'); ?></p></div>
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
    <div id="overlay-mdp" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:9999; justify-content:center; align-items:center;">
    
        <div style="background:white; padding:30px; border-radius:10px; width:450px; max-width:90%; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
            <h2 style="text-align:center; margin-bottom:20px;">Changer le mot de passe</h2>

            <form action="../../controllers/traitementMdp.php" method="POST">
                
                <div style="margin-bottom:15px;">
                    <label for="mdp" style="display:block; margin-bottom:5px;">Nouveau mot de passe</label>
                    <input type="password" name="nouveauMdp" id="mdp" style="width:100%; padding:8px; border:1px solid #ccc; border-radius:4px;" required>
                </div>

                <div id="password-requirements-container" class="hidden" style="margin-bottom:15px; background:#f9f9f9; padding:10px; border-radius:5px; font-size:0.85rem;">
                    <p style="margin:0 0 5px 0; font-weight:bold;">Le mot de passe doit contenir :</p>
                    <ul style="list-style:none; padding-left:0; margin:0;">
                        <li id="req-length" class="status-red"><i class="bi bi-x-circle-fill"></i> Au moins 12 caractères</li>
                        <li id="req-lowercase" class="status-red"><i class="bi bi-x-circle-fill"></i> Une minuscule</li>
                        <li id="req-uppercase" class="status-red"><i class="bi bi-x-circle-fill"></i> Une majuscule</li>
                        <li id="req-number" class="status-red"><i class="bi bi-x-circle-fill"></i> Un chiffre (0-9)</li>
                        <li id="req-special" class="status-red"><i class="bi bi-x-circle-fill"></i> Un caractère spécial</li>
                    </ul>
                </div>

                <div style="margin-bottom:20px;">
                    <label for="confimer_mdp" style="display:block; margin-bottom:5px;">Confirmer le mot de passe</label>
                    <input type="password" name="confirmMdp" id="confimer_mdp" style="width:100%; padding:8px; border:1px solid #ccc; border-radius:4px;" required>
                    <div id="req-match" class="status-red" style="margin-top:5px; font-size:0.85rem;"></div>
                </div>

                <div style="display:flex; justify-content:space-between; gap:10px;">
                    <button type="button" onclick="fermerPopUpMdp()" style="background:#ccc; border:none; padding:10px 20px; border-radius:5px; cursor:pointer;">Annuler</button>
                    <button type="submit" id="btn_inscription" disabled style="background:#007bff; color:white; border:none; padding:10px 20px; border-radius:5px; cursor:pointer; opacity: 0.5;">Valider</button>
                </div>
            </form>
        </div>

    </div>
    
    <?php include 'partials/footerConnecte.php'; ?>

    
    <?php 
        //On récupère le mot de passe de la BDD
        $stmt = $pdo->query("SELECT mdp FROM saedb._client WHERE idClient = '$id_client'");
        $tabMdp = $stmt->fetch(PDO::FETCH_ASSOC);
        $mdp = $tabMdp['mdp'] ?? '';
    ?>
    <script src="../../controllers/Chiffrement.js"></script>
    <script>
        //On récupère le mot de passe de la BDD et on utilise json_encode pour que les caratères comme \ soient considérés
        const mdp = <?php echo json_encode($mdp); ?>;
        // Fonctions pour ouvrir et fermer la pop-up
        function popUpModifierMdp() {
            const overlay = document.getElementById('overlay-mdp');
            overlay.style.display = 'flex'; // Utilise flex pour centrer
        }

        function fermerPopUpMdp() {
            document.getElementById('overlay-mdp').style.display = 'none';
        }

        // Mise à jour du style du bouton valider (visuel) quand il est disabled/enabled
        const btnSubmit = document.getElementById('btn_inscription');
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.attributeName === "disabled") {
                    btnSubmit.style.opacity = btnSubmit.disabled ? "0.5" : "1";
                    btnSubmit.style.cursor = btnSubmit.disabled ? "not-allowed" : "pointer";
                }
            });
        });
        if(btnSubmit) {
            observer.observe(btnSubmit, { attributes: true });
        }
    </script>
    <script src="../scripts/frontoffice/compteClient.js"></script>
</body>
</html>
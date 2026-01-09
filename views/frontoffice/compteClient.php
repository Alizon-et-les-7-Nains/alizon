<?php
session_start();
require_once '../../controllers/pdo.php' ;
    

if (!isset($_SESSION['user_id'])) {
    header("Location: ../frontoffice/connexionClient.php");
    exit();
}

$id_client = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT idAdresse FROM saedb._client WHERE idClient = :idClient");
$stmt->execute([":idClient" => $id_client]);
$client = $stmt->fetch(PDO::FETCH_ASSOC);
$idAdresse = $client['idAdresse'] ?? null;


if (!$idAdresse) {
    $pdo->prepare("
        INSERT INTO saedb._adresseClient 
        (adresse, region, codePostal, ville, pays, complementAdresse)
        VALUES (NULL, NULL, NULL, NULL, NULL, NULL)
    ")->execute();

    $idAdresse = $pdo->lastInsertId();

    $pdo->prepare("
        UPDATE saedb._client 
        SET idAdresse = :idAdresse 
        WHERE idClient = :idClient
    ")->execute([
        ":idAdresse" => $idAdresse,
        ":idClient"  => $id_client
    ]);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $pseudo        = $_POST['pseudo'];
    $nom           = $_POST['nom'];
    $prenom        = $_POST['prenom'];
    $email         = $_POST['email'];
    $dateNaissance = $_POST['dateNaissance'];
    $telephone     = $_POST['telephone'];
    $codePostal    = $_POST['codePostal'];
    $adresse1      = $_POST['adresse1'];
    $adresse2      = $_POST['adresse2'];
    $pays          = $_POST['pays'];
    $ville         = $_POST['ville'];

    $stmt = $pdo->prepare("
        UPDATE saedb._client 
        SET pseudo = :pseudo,
            nom = :nom,
            prenom = :prenom,
            email = :email,
            dateNaissance = :dateNaissance,
            noTelephone = :telephone,
            idAdresse = :idAdresse
        WHERE idClient = :idClient
    ");

    $stmt->execute([
        ":pseudo"        => $pseudo,
        ":nom"           => $nom,
        ":prenom"        => $prenom,
        ":email"         => $email,
        ":dateNaissance" => $dateNaissance,
        ":telephone"     => $telephone,
        ":idAdresse"     => $idAdresse,
        ":idClient"      => $id_client
    ]);

    $stmt = $pdo->prepare("
        UPDATE saedb._adresseClient 
        SET adresse = :adresse1,
            pays = :pays,
            ville = :ville,
            codePostal = :codePostal,
            complementAdresse = :adresse2
        WHERE idAdresse = :idAdresse
    ");

    $stmt->execute([
        ":adresse1"   => $adresse1,
        ":pays"       => $pays,
        ":ville"      => $ville,
        ":codePostal" => $codePostal,
        ":adresse2"   => $adresse2,
        ":idAdresse"  => $idAdresse
    ]);
}

    //verification et upload de la nouvelle photo de profil
    $photoPath = '/var/www/html/images/photoProfilClient/photo_profil'.$id_client;

    $extensionsPossibles = ['png', 'jpg', 'jpeg', 'webp', 'svg'];
    $extension = '';

    foreach ($extensionsPossibles as $ext) {
        if (file_exists($photoPath . '.' . $ext)) {
            $extension = '.' . $ext;
            break;
        }
    }

    if (file_exists($photoPath)) {
        unlink($photoPath); // supprime l'ancien fichier
    }

    if (isset($_FILES['photoProfil']) && $_FILES['photoProfil']['tmp_name'] != '') {
        $extension = pathinfo($_FILES['photoProfil']['name'], PATHINFO_EXTENSION);
        $extension = '.'.$extension;
        move_uploaded_file($_FILES['photoProfil']['tmp_name'], $photoPath.$extension);
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
                        if (file_exists($photoPath.$extension)) {
                            echo '<img src="/images/photoProfilClient/photo_profil'.$id_client.$extension.'" alt="photoProfil" id="imageProfile">';
                        } else {
                            echo '<img src="../../public/images/profil.png" alt="photoProfil" id="imageProfile">';
                        }
                    ?>
                </div>
                <h1>Mon Compte</h1>
            </div>

            <section>
                <article>
                    <div>
                        <label>Pseudo</label>
                        <p><?php echo htmlspecialchars($pseudo ?? ''); ?></p>
                    </div>
                   <div>
                        <label>Prénom</label>
                            <p><?php echo htmlspecialchars($prenom ?? ''); ?></p>
                    </div>
                   <div>
                        <label>Nom</label>
                        <p><?php echo htmlspecialchars($nom ?? ''); ?></p>
                    </div>
                   <div>
                        <label>Date de naissance</label>
                        <p><?php echo htmlspecialchars($dateNaissance ?? ''); ?></p>
                    </div>
                </article>

                <article>
                    <div>
                        <label>Adresse</label>
                        <p><?php echo htmlspecialchars($adresse1 ?? ''); ?></p>
                </div>
                    <div>
                        <label>Complément d'adresse</label>
                        <p><?php echo htmlspecialchars($adresse2 ?? ''); ?></p>
                </div>
                    <div class="double-champ">
                        <div>
                            <label>Code Postal</label>
                            <p><?php echo htmlspecialchars($codePostal ?? ''); ?></p>
                        </div>
                        <div><label>Ville</label>
                        <p><?php echo htmlspecialchars($ville ?? ''); ?></p>
                        </div>
                    </div>
                    <div>
                        <label>Pays</label>
                        <p><?php echo htmlspecialchars($pays ?? ''); ?></p>
                    </div>
                </article>

                <article>
                    <div>
                        <label>Numéro de téléphone</label>
                        <p><?php echo htmlspecialchars($noTelephone ?? ''); ?></p>
                    </div>
                    <div>
                        <label>Email</label>
                        <p><?php echo htmlspecialchars($email ?? ''); ?></p>
                    </div>
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
        //On récupère le mot de passe de la BDD
        $stmt = $pdo->query("SELECT mdp FROM saedb._client WHERE idClient = '$id_client'");
        $tabMdp = $stmt->fetch(PDO::FETCH_ASSOC);
        $mdp = $tabMdp['mdp'] ?? '';
    ?>
    <script src="../../controllers/Chiffrement.js"></script>
    <script>
        //On récupère le mot de passe de la BDD et on utilise json_encode pour que les caratères comme \ soient considérés
        const mdp = <?php echo json_encode($mdp); ?>;
    </script>
    <script src="../scripts/frontoffice/compteClient.js"></script>
</body>
</html>
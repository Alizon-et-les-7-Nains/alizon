<?php
require_once '../../controllers/pdo.php';
    
session_start();

$code_vendeur = 1; //$_SESSION['code_vendeur'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupérer les données du formulaire
    $raisonSociale = $_POST['raisonSociale'];
    $noSiren = $_POST['noSiren'];
    $prenom = $_POST['prenom'];
    $nom = $_POST['nom'];
    $email = $_POST['email'];
    $telephone = $_POST['telephone'];
    $codePostal = $_POST['codePostal'];
    $adresse1 = $_POST['adresse1'];
    $pays = $_POST['pays'];
    $ville = $_POST['ville'];
    $region = $_POST['region'] ?? '';
    $pseudo = $_POST['pseudo'] ?? '';

    // Mettre à jour le vendeur (avec les colonnes existantes)
    $stmt = $pdo->prepare(
        "UPDATE _vendeur 
        SET raisonSocial = :raisonSociale, 
        noSiren = :noSiren,
        prenom = :prenom, 
        nom = :nom, 
        email = :email, 
        noTelephone = :telephone,
        adresse = :adresse,
        ville = :ville,
        region = :region,
        pseudo = :pseudo
        WHERE codeVendeur = :code_vendeur"
    );
    
    $stmt->execute([
        ':raisonSociale' => $raisonSociale,
        ':noSiren' => $noSiren,
        ':prenom' => $prenom,
        ':nom' => $nom,
        ':email' => $email,
        ':telephone' => $telephone,
        ':adresse' => $adresse1,
        ':ville' => $ville,
        ':region' => $region,
        ':pseudo' => $pseudo,
        ':code_vendeur' => $code_vendeur
    ]);

    // Mettre à jour l'adresse dans la table _adresse (si elle existe)
    if (!empty($_POST['ancienne_adresse']) && !empty($_POST['ancienne_ville']) && !empty($_POST['ancienne_region'])) {
        $stmt = $pdo->prepare(
            "UPDATE _adresse 
            SET adresse = :adresse,
            pays = :pays,
            ville = :ville, 
            codePostal = :codePostal,
            region = :region
            WHERE adresse = :ancienne_adresse 
            AND ville = :ancienne_ville 
            AND region = :ancienne_region"
        );
        
        $stmt->execute([
            ':adresse' => $adresse1,
            ':pays' => $pays,
            ':ville' => $ville,
            ':codePostal' => $codePostal,
            ':region' => $region,
            ':ancienne_adresse' => $_POST['ancienne_adresse'],
            ':ancienne_ville' => $_POST['ancienne_ville'],
            ':ancienne_region' => $_POST['ancienne_region']
        ]);
    }

    // Redirection pour éviter la resoumission du formulaire
    header("Location: compteVendeur.php");
    exit();
}   

// On récupère les infos du vendeur pour les afficher
$stmt = $pdo->query("SELECT * FROM _vendeur WHERE codeVendeur = '$code_vendeur'");
$vendeur = $stmt->fetch(PDO::FETCH_ASSOC);

$raisonSociale = $vendeur['raisonSocial'] ?? '';
$noSiren = $vendeur['noSiren'] ?? '';
$prenom = $vendeur['prenom'] ?? '';
$nom = $vendeur['nom'] ?? '';
$email = $vendeur['email'] ?? '';
$noTelephone = $vendeur['noTelephone'] ?? '';
$adresseVendeur = $vendeur['adresse'] ?? '';
$villeVendeur = $vendeur['ville'] ?? '';
$regionVendeur = $vendeur['region'] ?? '';
$pseudo = $vendeur['pseudo'] ?? '';
$dateNaissance = $vendeur['dateNaissance'] ?? ''; // Nouveau champ

// Variables pour l'adresse
$pays = '';
$codePostal = '';

// On récupère les infos d'adresse complètes depuis la table _adresse
if ($adresseVendeur && $villeVendeur && $regionVendeur) {
    $stmt = $pdo->prepare("SELECT * FROM _adresse WHERE adresse = :adresse AND ville = :ville AND region = :region");
    $stmt->execute([
        ':adresse' => $adresseVendeur,
        ':ville' => $villeVendeur,
        ':region' => $regionVendeur
    ]);
    $adresse = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($adresse) {
        $pays = $adresse['pays'] ?? '';
        $codePostal = $adresse['codePostal'] ?? '';
        // S'assurer que les valeurs correspondent
        $adresse1 = $adresse['adresse'] ?? $adresseVendeur;
        $ville = $adresse['ville'] ?? $villeVendeur;
        $region = $adresse['region'] ?? $regionVendeur;
    } else {
        // Si l'adresse n'existe pas dans _adresse, utiliser les valeurs du vendeur
        $adresse1 = $adresseVendeur;
        $ville = $villeVendeur;
        $region = $regionVendeur;
    }
} else {
    $adresse1 = $adresseVendeur;
    $ville = $villeVendeur;
    $region = $regionVendeur;
}

// Vérification et upload de la nouvelle photo de profil
$photoPath = '../../public/images/photoDeProfil/photo_profil_vendeur'.$code_vendeur.'.png';

if (isset($_FILES['photoProfil']) && $_FILES['photoProfil']['tmp_name'] != '') {
    if (file_exists($photoPath)) {
        unlink($photoPath); // supprime l'ancien fichier
    }
    move_uploaded_file($_FILES['photoProfil']['tmp_name'], $photoPath);
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon compte - Vendeur</title>
    <link rel="stylesheet" href="../../public/style.css">
</head>

<body class="monCompte backoffice">
    <?php include 'partials/header.php'; ?>

    <main class="mainCompteVendeur">
        <form method="POST" enctype="multipart/form-data" action="">
            <!-- Champs cachés pour stocker les anciennes valeurs d'adresse -->
            <input type="hidden" name="ancienne_adresse" value="<?php echo htmlspecialchars($adresse1); ?>">
            <input type="hidden" name="ancienne_ville" value="<?php echo htmlspecialchars($ville); ?>">
            <input type="hidden" name="ancienne_region" value="<?php echo htmlspecialchars($region); ?>">

            <div id="titreCompte">
                <div class="photo-container">
                    <label for="photoProfilInput" style="cursor: pointer;">
                        <?php 
                            if (file_exists($photoPath)) {
                                echo "<img src='".$photoPath."' alt='photoProfil' id='imageProfile'>";
                            } else {
                                echo '<img src="../../public/images/profil.png" alt="photoProfil" id="imageProfile">';
                            }
                        ?>
                    </label>
                    <input type="file" id="photoProfilInput" name="photoProfil" accept="image/*" style="display: none;"
                        onchange="this.form.submit()">
                </div>
                <h1>Mon Compte</h1>
            </div>

            <section>
                <!-- Informations Personnelles -->
                <article class="infos-personnelles">
                    <h2>Informations Personnelles</h2>
                    <div class="champ">
                        <label for="nom">Nom</label>
                        <input type="text" id="nom" name="nom" value="<?php echo htmlspecialchars($nom); ?>" readonly>
                    </div>
                    <div class="champ">
                        <label for="prenom">Prénom</label>
                        <input type="text" id="prenom" name="prenom" value="<?php echo htmlspecialchars($prenom); ?>"
                            readonly>
                    </div>
                    <div class="champ">
                        <label for="dateNaissance">Date de naissance</label>
                        <input type="date" id="dateNaissance" name="dateNaissance"
                            value="<?php echo htmlspecialchars($dateNaissance); ?>" readonly>
                    </div>
                </article>

                <!-- Adresse -->
                <article class="infos-adresse">
                    <h2>Adresse</h2>
                    <div class="champ">
                        <label for="adresse1">Adresse</label>
                        <input type="text" id="adresse1" name="adresse1"
                            value="<?php echo htmlspecialchars($adresse1); ?>" readonly>
                    </div>
                    <div class="double-champ">
                        <div class="champ">
                            <label for="codePostal">Code postal</label>
                            <input type="text" id="codePostal" name="codePostal"
                                value="<?php echo htmlspecialchars($codePostal); ?>" readonly>
                        </div>
                        <div class="champ">
                            <label for="ville">Ville</label>
                            <input type="text" id="ville" name="ville" value="<?php echo htmlspecialchars($ville); ?>"
                                readonly>
                        </div>
                    </div>
                </article>

                <!-- Contact -->
                <article class="infos-contact">
                    <h2>Contact</h2>
                    <div class="champ">
                        <label for="telephone">Numéro de téléphone</label>
                        <input type="tel" id="telephone" name="telephone"
                            value="<?php echo htmlspecialchars($noTelephone); ?>" readonly>
                    </div>
                    <div class="champ">
                        <label for="email">Adresse E-Mail</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>"
                            readonly>
                    </div>
                </article>

                <!-- Informations Entreprise -->
                <article class="infos-entreprise">
                    <h2>Informations Entreprise</h2>
                    <div class="champ">
                        <label for="raisonSociale">Raison sociale</label>
                        <input type="text" id="raisonSociale" name="raisonSociale"
                            value="<?php echo htmlspecialchars($raisonSociale); ?>" readonly>
                    </div>
                    <div class="champ">
                        <label for="noSiren">Numéro SIREN</label>
                        <input type="text" id="noSiren" name="noSiren" value="<?php echo htmlspecialchars($noSiren); ?>"
                            readonly>
                    </div>
                </article>

                <!-- Informations de compte -->
                <article class="infos-compte">
                    <h2>Informations de compte</h2>
                    <div class="champ">
                        <label for="pseudo">Nom d'utilisateur</label>
                        <input type="text" id="pseudo" name="pseudo" value="<?php echo htmlspecialchars($pseudo); ?>"
                            readonly>
                    </div>
                    <div class="champ-mot-de-passe">
                        <label for="motDePasseActuel">Mot de passe actuel</label>
                        <input type="password" id="motDePasseActuel" name="motDePasseActuel" placeholder="••••••••"
                            readonly>
                    </div>
                    <div class="champ-mot-de-passe">
                        <label for="nouveauMotDePasse">Nouveau mot de passe</label>
                        <input type="password" id="nouveauMotDePasse" name="nouveauMotDePasse" placeholder="••••••••"
                            readonly>
                    </div>
                    <div class="exigences-mot-de-passe">
                        <h4>Exigences du mot de passe :</h4>
                        <ul>
                            <li>Longueur minimale de 12 caractères</li>
                            <li>Au moins une minuscule / majuscule</li>
                            <li>Au moins un chiffre</li>
                            <li>Au moins un caractère spécial</li>
                        </ul>
                    </div>
                </article>

                <!-- Code vendeur -->
                <article class="code-vendeur">
                    <div class="champ">
                        <label>Code vendeur</label>
                        <span
                            class="code-vendeur-value">VD<?php echo str_pad($code_vendeur, 3, '0', STR_PAD_LEFT); ?></span>
                    </div>
                </article>
            </section>

            <div id="buttonsCompte">
                <button type="button" onclick="popUpModifierMdp()" class="boutonModifierMdp">Modifier le mot de
                    passe</button>
                <button class="boutonAnnuler" type="button" onclick="boutonAnnuler()"
                    style="display:none;">Annuler</button>
                <button type="button" class="boutonModifierProfil">Modifier</button>
                <button type="submit" class="boutonSauvegarder" style="display:none;">Sauvegarder</button>
                <button type="button" class="boutonSupprimerCompte" onclick="supprimerCompte()">Supprimer le
                    compte</button>
            </div>
        </form>
    </main>

    <?php include 'partials/footer.php'; ?>

    <?php 
        // On récupère le mot de passe de la BDD
        $stmt = $pdo->query("SELECT mdp FROM _vendeur WHERE codeVendeur = '$code_vendeur'");
        $tabMdp = $stmt->fetch(PDO::FETCH_ASSOC);
        $mdp = $tabMdp['mdp'] ?? '';
    ?>
    <script src="../../controllers/Chiffrement.js"></script>
    <script>
    // On récupère le mot de passe de la BDD
    const mdp = <?php echo json_encode($mdp); ?>;
    </script>
    <script src="../scripts/backoffice/compteVendeur.js"></script>
</body>

</html>
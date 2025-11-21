<?php
session_start();
require_once '../../controllers/pdo.php';

$code_vendeur = 2;

// Récupération des informations du vendeur avec jointure sur l'adresse
$stmt = $pdo->prepare("
    SELECT v.*, a.codePostal, a.ville, a.region, a.pays, a.adresse as adresse_complete
    FROM _vendeur v 
    LEFT JOIN _adresseVendeur a ON v.idAdresse = a.idAdresse 
    WHERE v.codeVendeur = :id
");

$stmt->execute([':id' => $code_vendeur]);
$vendeur = $stmt->fetch(PDO::FETCH_ASSOC);

$raisonSociale = $vendeur['raisonSocial'] ?? '';
$noSiren       = $vendeur['noSiren'] ?? '';
$prenom        = $vendeur['prenom'] ?? '';
$nom           = $vendeur['nom'] ?? '';
$email         = $vendeur['email'] ?? '';
$telephone     = $vendeur['noTelephone'] ?? '';
$adresse       = $vendeur['adresse_complete'] ?? '';
$ville         = $vendeur['ville'] ?? '';
$codePostal    = $vendeur['codePostal'] ?? '';
$pseudo        = $vendeur['pseudo'] ?? '';
$noSiren       = $vendeur['noSiren'] ?? '';
$prenom        = $vendeur['prenom'] ?? '';
$nom           = $vendeur['nom'] ?? '';
$email         = $vendeur['email'] ?? '';
$telephone     = $vendeur['noTelephone'] ?? '';
$adresse       = $vendeur['adresse_complete'] ?? '';
$ville         = $vendeur['ville'] ?? '';
$codePostal    = $vendeur['codePostal'] ?? '';
$pseudo        = $vendeur['pseudo'] ?? '';
$dateNaissance = $vendeur['dateNaissance'] ?? '';
$region        = $vendeur['region'] ?? '';
$pays          = $vendeur['pays'] ?? '';
$idAdresse     = $vendeur['idAdresse'] ?? '';

  //verification et upload de la nouvelle photo de profil
    $photoPath = '/var/www/html/images/photoProfilVendeur/photo_profil'.$code_vendeur;

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

?>

$region = $vendeur['region'] ?? '';
$pays = $vendeur['pays'] ?? '';
$idAdresse = $vendeur['idAdresse'] ?? '';

// Chemin de la photo de profil
$photoDir = '../../images/photoProfilVendeur/';
$photoFilename = 'photo_profil' . $code_vendeur . '.png';
$photoPath = $photoDir . $photoFilename;
$photoFullPath = '/var/www/html/images/photoProfilVendeur/' . $photoFilename;
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="../../public/style.css">
    <title>Mon compte</title>
    <title>Mon compte</title>
</head>

<body class="monCompte backoffice">


    <?php include 'partials/header.php'; ?>

    <main class="page-compte">

        <div class="header-compte">
            <div class="photo-profil">
                <?php 
                        if (file_exists($photoPath.$extension)) {
                            echo '<img src="/images/photoProfilVendeur/photo_profil'.$code_vendeur.$extension.'" alt="photoProfil" id="imageProfile">';
                        } else {
                            echo '<img src="../../public/images/profil.png" alt="photoProfil" id="imageProfile">';
                        }
                    ?>
            </div>
            <input type="file" id="uploadPhoto" name="photoProfil" accept="image/*" hidden>
            <h1>Mon compte</h1>
        </div>

        <form class="form-compte" method="POST" action="../../controllers/majVendeur.php" enctype="multipart/form-data">
            <input type="hidden" name="code_vendeur" value="<?= $code_vendeur ?>">
            <input type="hidden" name="id_adresse" value="<?= $idAdresse ?>">

            <!-- Colonne gauche -->
            <article class="col">

                <div class="champ">
                    <input type="text" id="nom" name="nom" value="<?= htmlspecialchars($nom) ?>" readonly>
                    <div class="field-error">
                        <p>Le nom est obligatoire</p>
                    </div>
                </div>
                <main class="page-compte">

                    <div class="header-compte">
                        <div class="photo-profil">
                            <?php 
                    if (file_exists($photoFullPath)) {
                        echo '<img src="' . $photoPath . '" alt="photoProfil" id="imageProfile">';
                    } else {
                        echo '<img src="../../public/images/profil.png" alt="photoProfil" id="imageProfile">';
                    }
                ?>
                        </div>
                        <input type="file" id="uploadPhoto" name="photoProfil" accept="image/*" hidden>
                        <h1>Mon compte</h1>
                    </div>

                    <form class="form-compte" method="POST" action="../../controllers/majVendeur.php"
                        enctype="multipart/form-data">
                        <input type="hidden" name="code_vendeur" value="<?= $code_vendeur ?>">
                        <input type="hidden" name="id_adresse" value="<?= $idAdresse ?>">

                        <!-- Colonne gauche -->
                        <article class="col">

                            <div class="champ">
                                <input type="text" id="nom" name="nom" value="<?= htmlspecialchars($nom) ?>" readonly>
                                <div class="field-error">
                                    <p>Le nom est obligatoire</p>
                                </div>
                            </div>

                            <div class="champ">
                                <input type="text" id="prenom" name="prenom" value="<?= htmlspecialchars($prenom) ?>"
                                    readonly>
                                <div class="field-error">
                                    <p>Le prénom est obligatoire</p>
                                    <div class="champ">
                                        <input type="text" id="prenom" name="prenom"
                                            value="<?= htmlspecialchars($prenom) ?>" readonly>
                                        <div class="field-error">
                                            <p>Le prénom est obligatoire</p>
                                        </div>
                                    </div>

                                    <div class="champ">
                                        <div class="champ-date">
                                            <input type="date" id="dateNaissance" name="dateNaissance"
                                                value="<?= $dateNaissance ?>" </div>

                                            <div class="champ">
                                                <div class="champ-date">
                                                    <input type="date" id="dateNaissance" name="dateNaissance"
                                                        value="<?= $dateNaissance ?>" readonly>
                                                </div>
                                                <div class="field-error">
                                                    <p>Vous devez avoir 18 ans</p>
                                                    <div class="field-error">
                                                        <p>Vous devez avoir 18 ans</p>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="champ">
                                                <input type="text" id="adresse" name="adresse"
                                                    value="<?= htmlspecialchars($adresse) ?>" readonly>
                                                <div class="field-error">
                                                    <p>L'adresse est obligatoire</p>
                                                    <div class="champ">
                                                        <input type="text" id="adresse" name="adresse"
                                                            value="<?= htmlspecialchars($adresse) ?>" readonly>
                                                        <div class="field-error">
                                                            <p>L'adresse est obligatoire</p>
                                                        </div>
                                                    </div>

                                                    <div class="champ-double">
                                                        <div class="champ">
                                                            <input type="text" id="codePostal" name="codePostal"
                                                                value="<?= htmlspecialchars($codePostal) ?>" readonly>
                                                            <div class="field-error">
                                                                <p>Le code postal doit contenir 5 chiffres</p>
                                                            </div>
                                                        </div>
                                                        <div class="champ">
                                                            <input type="text" id="ville" name="ville"
                                                                value="<?= htmlspecialchars($ville) ?>" readonly>
                                                            <div class="field-error">
                                                                <p>La ville est obligatoire</p>
                                                            </div>

                                                            <div class="champ-double">
                                                                <div class="champ">
                                                                    <input type="text" id="codePostal" name="codePostal"
                                                                        value="<?= htmlspecialchars($codePostal) ?>"
                                                                        readonly>
                                                                    <div class="field-error">
                                                                        <p>Le code postal doit contenir 5 chiffres</p>
                                                                    </div>
                                                                </div>
                                                                <div class="champ">
                                                                    <input type="text" id="ville" name="ville"
                                                                        value="<?= htmlspecialchars($ville) ?>"
                                                                        readonly>
                                                                    <div class="field-error">
                                                                        <p>La ville est obligatoire</p>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="champ">
                                                            <input type="text" id="region" name="region"
                                                                value="<?= htmlspecialchars($region) ?>" readonly>
                                                            <div class="field-error">
                                                                <p>La région est obligatoire</p>
                                                                <div class="champ">
                                                                    <input type="text" id="region" name="region"
                                                                        value="<?= htmlspecialchars($region) ?>"
                                                                        readonly>
                                                                    <div class="field-error">
                                                                        <p>La région est obligatoire</p>
                                                                    </div>
                                                                </div>

                                                                <div class="champ">
                                                                    <input type="tel" id="telephone" name="telephone"
                                                                        value="<?= htmlspecialchars($telephone) ?>"
                                                                        readonly>
                                                                    <div class="field-error">
                                                                        <p>Le téléphone doit contenir 10 chiffres</p>
                                                                    </div>
                                                                </div>

                                                                <div class="champ">
                                                                    <input type="email" id="email" name="email"
                                                                        value="<?= htmlspecialchars($email) ?>"
                                                                        readonly>
                                                                    <div class="field-error">
                                                                        <p>L'email n'est pas valide</p>
                                                                    </div>

                                                                    <div class="champ">
                                                                        <input type="tel" id="telephone"
                                                                            name="telephone"
                                                                            value="<?= htmlspecialchars($telephone) ?>"
                                                                            readonly>
                                                                        <div class="field-error">
                                                                            <p>Le téléphone doit contenir 10 chiffres
                                                                            </p>
                                                                        </div>
                                                                    </div>

                                                                    <div class="champ">
                                                                        <input type="email" id="email" name="email"
                                                                            value="<?= htmlspecialchars($email) ?>"
                                                                            readonly>
                                                                        <div class="field-error">
                                                                            <p>L'email n'est pas valide</p>
                                                                        </div>
                                                                    </div>

                        </article>
                        </div>

            </article>

            <!-- Colonne droite -->
            <article class="col">

                <div class="champ">
                    <input type="text" id="raisonSociale" name="raisonSociale"
                        value="<?= htmlspecialchars($raisonSociale) ?>" readonly>
                    <div class="field-error">
                        <p>La raison sociale est obligatoire</p>
                    </div>
                </div>

                <div class="champ">
                    <input type="text" id="noSiren" name="noSiren" value="<?= htmlspecialchars($noSiren) ?>" readonly>
                    <div class="field-error">
                        <p>Le SIREN doit contenir 9 chiffres</p>
                        <!-- Colonne droite -->
                        <article class="col">

                            <div class="champ">
                                <input type="text" id="raisonSociale" name="raisonSociale"
                                    value="<?= htmlspecialchars($raisonSociale) ?>" readonly>
                                <div class="field-error">
                                    <p>La raison sociale est obligatoire</p>
                                </div>
                            </div>

                            <div class="champ">
                                <input type="text" id="noSiren" name="noSiren" value="<?= htmlspecialchars($noSiren) ?>"
                                    readonly>
                                <div class="field-error">
                                    <p>Le SIREN doit contenir 9 chiffres</p>
                                </div>
                            </div>

                            <div class="champ">
                                <input type="text" id="pseudo" name="pseudo" value="<?= htmlspecialchars($pseudo) ?>"
                                    readonly>
                                <div class="field-error">
                                    <p>Le pseudo est obligatoire</p>
                                </div>

                                <div class="champ">
                                    <input type="text" id="pseudo" name="pseudo"
                                        value="<?= htmlspecialchars($pseudo) ?>" readonly>
                                    <div class="field-error">
                                        <p>Le pseudo est obligatoire</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Section modification mot de passe -->
                            <div class="champ">
                                <input type="password" id="ancienMdp" name="ancienMdp" placeholder="Ancien mot de passe"
                                    readonly>
                                <div class="field-error">
                                    <p>L'ancien mot de passe est obligatoire</p>
                                    <!-- Section modification mot de passe -->
                                    <div class="champ">
                                        <input type="password" id="ancienMdp" name="ancienMdp"
                                            placeholder="Ancien mot de passe" readonly>
                                        <div class="field-error">
                                            <p>L'ancien mot de passe est obligatoire</p>
                                        </div>
                                    </div>

                                    <div class="champ">
                                        <input type="password" id="nouveauMdp" name="nouveauMdp"
                                            placeholder="Nouveau mot de passe" readonly>
                                        <div class="field-error">
                                            <p>Le mot de passe ne respecte pas les critères</p>
                                        </div>

                                        <div class="champ">
                                            <input type="password" id="nouveauMdp" name="nouveauMdp"
                                                placeholder="Nouveau mot de passe" readonly>
                                            <div class="field-error">
                                                <p>Le mot de passe ne respecte pas les critères</p>
                                            </div>
                                        </div>

                                        <div class="champ">
                                            <input type="password" id="confirmationMdp" name="confirmationMdp"
                                                placeholder="Confirmer le nouveau mot de passe" readonly>
                                            <div class="field-error">
                                                <p>La confirmation ne correspond pas</p>
                                            </div>

                                            <div class="champ">
                                                <input type="password" id="confirmationMdp" name="confirmationMdp"
                                                    placeholder="Confirmer le nouveau mot de passe" readonly>
                                                <div class="field-error">
                                                    <p>La confirmation ne correspond pas</p>
                                                </div>
                                            </div>

                                            <ul class="mpd-rules">
                                                <li>Longueur minimale de 12 caractères</li>
                                                <li>Au moins une minuscule / majuscule</li>
                                                <li>Au moins un chiffre</li>
                                                <li>Au moins un caractère spécial</li>
                                            </ul>
                                        </div>

                                        <ul class="mpd-rules">
                                            <li>Longueur minimale de 12 caractères</li>
                                            <li>Au moins une minuscule / majuscule</li>
                                            <li>Au moins un chiffre</li>
                                            <li>Au moins un caractère spécial</li>
                                        </ul>

                                        <div class="champ">
                                            <span class="field-label">Code vendeur :</span>
                                            <span
                                                class="code-vendeur">VD<?= str_pad($code_vendeur, 3, '0', STR_PAD_LEFT) ?></span>
                                        </div>

                        </article>

        </form>
        <div class="actions">
            <button type="button" class="modifier boutonModifierProfil">Modifier</button>
            <button type="button" class="annuler boutonAnnuler" style="display: none;">Annuler</button>
            <button type="submit" class="sauvegarder boutonSauvegarder" style="display: none;">Sauvegarder</button>
            <button type="button" class="modifier-mdp boutonModifierMdp">Modifier le mot de passe</button>
        </div>

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
    // Variables globales pour le JavaScript
    const codeVendeur = <?= $code_vendeur ?>;
    const mdpCrypte = <?php echo json_encode($mdp); ?>;
    // Variables globales pour le JavaScript
    const codeVendeur = <?= $code_vendeur ?>;
    const mdpCrypte = <?php echo json_encode($mdp); ?>;
    </script>
    <script src="../scripts/backoffice/compteVendeur.js"></script>
</body>

</html>
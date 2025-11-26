<?php
require_once '../../controllers/auth.php';
require_once '../../controllers/pdo.php';

if (!isset($_SESSION['id'])) {
    header("Location: ../backoffice/connexion.php");
    exit();
}

$code_vendeur = $_SESSION['id'];

// Récupération de l'idAdresse du vendeur
$stmt = $pdo->prepare("SELECT idAdresse FROM _vendeur WHERE codeVendeur = :id");
$stmt->execute([':id' => $code_vendeur]);
$vendeur = $stmt->fetch(PDO::FETCH_ASSOC);
$idAdresse = $vendeur['idAdresse'] ?? null;

// Si pas d'adresse, en créer une vide
if (!$idAdresse) {
    $stmt = $pdo->prepare("INSERT INTO _adresseVendeur (adresse, region, codePostal, ville, pays, complementAdresse) 
                           VALUES (NULL, NULL, NULL, NULL, NULL, NULL)");
    $stmt->execute();
    $idAdresse = $pdo->lastInsertId();
    
    $stmt = $pdo->prepare("UPDATE _vendeur SET idAdresse = :idAdresse WHERE codeVendeur = :code_vendeur");
    $stmt->execute([
        ':idAdresse' => $idAdresse,
        ':code_vendeur' => $code_vendeur
    ]);
}

// Traitement du formulaire

    // Gestion de la photo de profil
    //verification et upload de la nouvelle photo de profil
    $photoPath = '/var/www/html/images/photoProfilClient/photo_profil'.$code_vendeur;

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

// Récupération des informations du vendeur pour affichage
$stmt = $pdo->prepare("
    SELECT v.*, a.codePostal, a.ville, a.region, a.pays, a.adresse as adresse_complete
    FROM _vendeur v 
    LEFT JOIN _adresseVendeur a ON v.idAdresse = a.idAdresse
    WHERE v.codeVendeur = :id
");
$stmt->execute([':id' => $code_vendeur]);
$vendeur = $stmt->fetch(PDO::FETCH_ASSOC);

// Extraction des données pour affichage
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
$dateNaissance = $vendeur['dateNaissance'] ?? '';
$region        = $vendeur['region'] ?? '';
$pays          = $vendeur['pays'] ?? '';
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="../../public/style.css">
    <title>Mon compte</title>
</head>

<body class="monCompte backoffice">
    <?php include 'partials/header.php'; ?>

    <?php
        $currentPage = basename(__FILE__);
        require_once './partials/aside.php';
    ?>

    <main class="page-compte">
        <form class="form-compte" method="POST" action="../../controllers/compteVendeur.php"
            enctype="multipart/form-data">
            <div class="header-compte">
                <div class="photo-profil-container">
                    <div class="photo-profil">
                        <?php 
                        if (file_exists($photoPath . $extension)) {
                            echo '<img src="/images/photoProfilVendeur/photo_profil' . $code_vendeur . $extension . '" alt="photoProfil" id="imageProfile">';
                        } else {
                            echo '<img src="../../public/images/profil.png" alt="photoProfil" id="imageProfile">';
                        }
                        ?>
                    </div>
                </div>
                <h1>Mon compte</h1>
            </div>

            <input type="hidden" name="code_vendeur" value="<?= $code_vendeur ?>">
            <input type="hidden" name="id_adresse" value="<?= $idAdresse ?>">

            <!-- CONTENEUR DES COLONNES -->
            <div class="colonnes-container">
                <!-- Colonne gauche -->
                <article class="col">
                    <div class="champ">
                        <input type="text" id="nom" name="nom" value="<?= htmlspecialchars($nom) ?>" readonly>
                        <div class="field-error">
                            <p>Le nom est obligatoire</p>
                        </div>
                    </div>

                    <div class="champ">
                        <input type="text" id="prenom" name="prenom" value="<?= htmlspecialchars($prenom) ?>" readonly>
                        <div class="field-error">
                            <p>Le prénom est obligatoire</p>
                        </div>
                    </div>

                    <div class="champ">
                        <div class="champ-date">
                            <input type="date" id="dateNaissance" name="dateNaissance" value="<?= $dateNaissance ?>"
                                readonly>
                        </div>
                        <div class="field-error">
                            <p>Vous devez avoir 18 ans</p>
                        </div>
                    </div>

                    <div class="champ">
                        <input type="text" id="adresse" name="adresse" value="<?= htmlspecialchars($adresse) ?>"
                            readonly>
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
                            <input type="text" id="ville" name="ville" value="<?= htmlspecialchars($ville) ?>" readonly>
                            <div class="field-error">
                                <p>La ville est obligatoire</p>
                            </div>
                        </div>
                    </div>

                    <div class="champ">
                        <input type="text" id="region" name="region" value="<?= htmlspecialchars($region) ?>" readonly>
                        <div class="field-error">
                            <p>La région est obligatoire</p>
                        </div>
                    </div>

                    <div class="champ">
                        <input type="text" id="pays" name="pays" value="<?= htmlspecialchars($pays) ?>" readonly>
                        <div class="field-error">
                            <p>Le pays est obligatoire</p>
                        </div>
                    </div>

                    <div class="champ">
                        <input type="tel" id="telephone" name="telephone" value="<?= htmlspecialchars($telephone) ?>"
                            readonly>
                        <div class="field-error">
                            <p>Le téléphone doit contenir 10 chiffres</p>
                        </div>
                    </div>

                    <div class="champ">
                        <input type="email" id="email" name="email" value="<?= htmlspecialchars($email) ?>" readonly>
                        <div class="field-error">
                            <p>L'email n'est pas valide</p>
                        </div>
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
                        <input type="text" id="noSiren" name="noSiren" value="<?= htmlspecialchars($noSiren) ?>"
                            readonly>
                        <div class="field-error">
                            <p>Le SIREN doit contenir 9 chiffres</p>
                        </div>
                    </div>

                    <div class="champ">
                        <input type="text" id="pseudo" name="pseudo" value="<?= htmlspecialchars($pseudo) ?>" readonly>
                        <div class="field-error">
                            <p>Le pseudo est obligatoire</p>
                        </div>
                    </div>

                    <!-- Section modification mot de passe -->
                    <div class="champ">
                        <input type="password" id="ancienMdp" name="ancienMdp" placeholder="Ancien mot de passe"
                            readonly>
                        <div class="field-error">
                            <p>L'ancien mot de passe est obligatoire</p>
                        </div>
                    </div>

                    <div class="champ">
                        <input type="password" id="nouveauMdp" name="nouveauMdp" placeholder="Nouveau mot de passe"
                            readonly>
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
                    </div>

                    <ul class="mpd-rules">
                        <li>Longueur minimale de 12 caractères</li>
                        <li>Au moins une minuscule / majuscule</li>
                        <li>Au moins un chiffre</li>
                        <li>Au moins un caractère spécial</li>
                    </ul>

                    <div class="champ">
                        <span class="field-label">Code vendeur :</span>
                        <span class="code-vendeur">VD<?= str_pad($code_vendeur, 3, '0', STR_PAD_LEFT) ?></span>
                    </div>
                </article>
            </div> <!-- Fin du conteneur des colonnes -->

            <div class="actions">
                <button type="button" class="modifier boutonModifierProfil">Modifier</button>
                <button type="button" class="annuler boutonAnnuler" style="display: none;">Annuler</button>
                <button type="submit" class="sauvegarder boutonSauvegarder" style="display: none;">Sauvegarder</button>
                <button type="button" class="modifier-mdp boutonModifierMdp">Modifier le mot de passe</button>
            </div>
        </form>

        <?php require_once './partials/retourEnHaut.php' ?>
    </main>
    <?php include 'partials/footer.php'; ?>

    <?php 
    // Récupération du mot de passe pour le JavaScript
    $stmt = $pdo->prepare("SELECT mdp FROM _vendeur WHERE codeVendeur = :code_vendeur");
    $stmt->execute([':code_vendeur' => $code_vendeur]);
    $tabMdp = $stmt->fetch(PDO::FETCH_ASSOC);
    $mdp = $tabMdp['mdp'] ?? '';
    ?>

    <script src="../../controllers/Chiffrement.js"></script>
    <script>
    // Variables globales pour le JavaScript
    const codeVendeur = <?= $code_vendeur ?>;
    const mdpCrypte = <?php echo json_encode($mdp); ?>;
    </script>
    <script src="../scripts/backoffice/compteVendeur.js"></script>
    <script src="../../public/amd-shim.js"></script>
    <script src="../../public/script.js"></script>
</body>

</html>
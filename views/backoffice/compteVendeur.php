<?php
// Activer l'affichage des erreurs
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../../controllers/pdo.php';
require_once '../../controllers/auth.php';

if (!isset($_SESSION['id'])) {
    header("Location: ../backoffice/connexion.php");
    exit();
}

$code_vendeur = $_SESSION['id'];

// DÉBOGAGE : Vérifier si le POST arrive
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<pre style='background: yellow; padding: 20px; border: 2px solid red;'>";
    echo "=== POST REÇU ===\n";
    echo "Données POST:\n";
    print_r($_POST);
    echo "\nDonnées FILES:\n";
    print_r($_FILES);
    echo "</pre>";
    
    // Ne pas continuer pour l'instant
    // exit(); // Décommentez ceci pour arrêter l'exécution et voir les données
}

// Récupération des informations du vendeur avec jointure sur l'adresse
$stmt = $pdo->prepare("
    SELECT v.*, a.codePostal, a.ville, a.region, a.pays, a.adresse as adresse_complete
    FROM _vendeur v 
    LEFT JOIN _adresseVendeur a ON v.idAdresse = a.idAdresse
    WHERE v.codeVendeur = :id;
");

$stmt->execute([':id' => $code_vendeur]);
$vendeur = $stmt->fetch(PDO::FETCH_ASSOC);

// Extraction des données
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
$idAdresse     = $vendeur['idAdresse'] ?? '';

// Gestion de la photo de profil
$photoPath = '/var/www/html/images/photoProfilVendeur/photo_profil' . $code_vendeur;
$extension = '';

$extensionsPossibles = ['png', 'jpg', 'jpeg', 'webp', 'svg'];
foreach ($extensionsPossibles as $ext) {
    if (file_exists($photoPath . '.' . $ext)) {
        $extension = '.' . $ext;
        break;
    }
}

// Traitement du formulaire - MÊME STRUCTURE QUE COMPTE CLIENT
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    try {
        // Récupération des données du formulaire
        $pseudo = $_POST['pseudo'] ?? '';
        $nom = $_POST['nom'] ?? '';
        $prenom = $_POST['prenom'] ?? '';
        $email = $_POST['email'] ?? '';
        $dateNaissance = $_POST['dateNaissance'] ?? '';
        $telephone = $_POST['telephone'] ?? '';
        $codePostal = $_POST['codePostal'] ?? '';
        $adresse = $_POST['adresse'] ?? '';
        $pays = $_POST['pays'] ?? 'France'; // Valeur par défaut
        $ville = $_POST['ville'] ?? '';
        $region = $_POST['region'] ?? '';
        $raisonSociale = $_POST['raisonSociale'] ?? '';
        $noSiren = $_POST['noSiren'] ?? '';

        // Gestion des valeurs NULL pour dateNaissance
        $dateNaissance = ($dateNaissance === '') ? null : $dateNaissance;

        // ===== GESTION DE L'ADRESSE =====
        // Si pas d'idAdresse, on en crée une
        if (empty($idAdresse)) {
            echo "<p style='color: blue;'>Création d'une nouvelle adresse...</p>";
            
            $stmt = $pdo->prepare("
                INSERT INTO _adresseVendeur (adresse, pays, ville, codePostal, region, complementAdresse)
                VALUES (:adresse, :pays, :ville, :codePostal, :region, NULL)
            ");
            
            $stmt->execute([
                ':adresse' => $adresse,
                ':pays' => $pays,
                ':ville' => $ville,
                ':codePostal' => $codePostal,
                ':region' => $region
            ]);
            
            $idAdresse = $pdo->lastInsertId();
            echo "<p style='color: green;'>Adresse créée avec l'ID: {$idAdresse}</p>";
        } else {
            // Sinon on met à jour l'adresse existante
            echo "<p style='color: blue;'>Mise à jour de l'adresse {$idAdresse}...</p>";
            
            $stmt = $pdo->prepare("
                UPDATE _adresseVendeur 
                SET adresse = :adresse,
                    pays = :pays,
                    ville = :ville, 
                    codePostal = :codePostal,
                    region = :region
                WHERE idAdresse = :idAdresse
            ");

            $stmt->execute([
                ':adresse' => $adresse,
                ':pays' => $pays,
                ':ville' => $ville,
                ':codePostal' => $codePostal,
                ':region' => $region,
                ':idAdresse' => $idAdresse
            ]);
            
            echo "<p style='color: green;'>Adresse mise à jour !</p>";
        }

        // ===== MISE À JOUR DU VENDEUR =====
        echo "<p style='color: blue;'>Mise à jour du vendeur...</p>";
        
        $stmt = $pdo->prepare("
            UPDATE _vendeur 
            SET pseudo = :pseudo, 
                nom = :nom, 
                prenom = :prenom, 
                email = :email, 
                dateNaissance = :dateNaissance,
                noTelephone = :telephone,
                raisonSocial = :raisonSocial,
                noSiren = :noSiren,
                idAdresse = :idAdresse
            WHERE codeVendeur = :code_vendeur
        ");

        $result = $stmt->execute([
            ':pseudo' => $pseudo,
            ':nom' => $nom,
            ':prenom' => $prenom,
            ':email' => $email,
            ':dateNaissance' => $dateNaissance,
            ':telephone' => $telephone,
            ':raisonSocial' => $raisonSociale,
            ':noSiren' => $noSiren,
            ':idAdresse' => $idAdresse,
            ':code_vendeur' => $code_vendeur
        ]);
        
        if ($result) {
            echo "<p style='color: green; font-weight: bold;'>✅ Vendeur mis à jour avec succès !</p>";
        } else {
            echo "<p style='color: red;'>❌ Erreur lors de la mise à jour du vendeur</p>";
        }

        // ===== TRAITEMENT DE LA PHOTO =====
        if (isset($_FILES['photoProfil']) && $_FILES['photoProfil']['tmp_name'] != '') {
            echo "<p style='color: blue;'>Upload de la photo...</p>";
            
            // Supprimer les anciennes photos
            foreach ($extensionsPossibles as $ext) {
                $oldFile = $photoPath . '.' . $ext;
                if (file_exists($oldFile)) {
                    unlink($oldFile);
                }
            }
            
            // Uploader la nouvelle photo
            $extension = '.' . pathinfo($_FILES['photoProfil']['name'], PATHINFO_EXTENSION);
            move_uploaded_file($_FILES['photoProfil']['tmp_name'], $photoPath . $extension);
            
            echo "<p style='color: green;'>Photo uploadée !</p>";
        }

        // Recharger les données après mise à jour
        $stmt = $pdo->prepare("
            SELECT v.*, a.codePostal, a.ville, a.region, a.pays, a.adresse as adresse_complete
            FROM _vendeur v 
            LEFT JOIN _adresseVendeur a ON v.idAdresse = a.idAdresse
            WHERE v.codeVendeur = :id
        ");
        $stmt->execute([':id' => $code_vendeur]);
        $vendeur = $stmt->fetch(PDO::FETCH_ASSOC);

        // Mettre à jour les variables
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
        $idAdresse     = $vendeur['idAdresse'] ?? '';
        
        echo "<p style='color: green; font-weight: bold; font-size: 18px;'>🎉 TOUTES LES MODIFICATIONS ONT ÉTÉ ENREGISTRÉES !</p>";
        
    } catch (PDOException $e) {
        echo "<p style='color: red; font-weight: bold;'>❌ ERREUR PDO: " . $e->getMessage() . "</p>";
        error_log("Erreur PDO: " . $e->getMessage());
    }
}
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
        <form class="form-compte" method="POST" action="" enctype="multipart/form-data">
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
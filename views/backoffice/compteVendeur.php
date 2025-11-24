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

// Extraction des données une seule fois
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

// Gestion de la photo de profil - version simplifiée comme client
$photoPath = '/var/www/html/images/photoProfilVendeur/photo_profil' . $code_vendeur;
$extension = '';

$extensionsPossibles = ['png', 'jpg', 'jpeg', 'webp', 'svg'];
foreach ($extensionsPossibles as $ext) {
    if (file_exists($photoPath . '.' . $ext)) {
        $extension = '.' . $ext;
        break;
    }
}

// Traitement de l'upload de photo si formulaire soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['photoProfil']) && $_FILES['photoProfil']['tmp_name'] != '') {
    // Supprimer l'ancienne photo
    foreach ($extensionsPossibles as $ext) {
        $oldFile = $photoPath . '.' . $ext;
        if (file_exists($oldFile)) {
            unlink($oldFile);
        }
    }
    
    // Uploader la nouvelle photo
    $extension = '.' . pathinfo($_FILES['photoProfil']['name'], PATHINFO_EXTENSION);
    move_uploaded_file($_FILES['photoProfil']['tmp_name'], $photoPath . $extension);
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

    <main class="page-compte">
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
            <input type="file" id="photoProfil" name="photoProfil" accept="image/*" style="display: none;">
            <h1>Mon compte</h1>
        </div>

        <form class="form-compte" method="POST" action="" enctype="multipart/form-data">
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
                    <input type="text" id="prenom" name="prenom" value="<?= htmlspecialchars($prenom) ?>" readonly>
                    <div class="field-error">
                        <p>Le prénom est obligatoire</p>
                    </div>
                </div>

                <!-- ... autres champs ... -->
            </article>

            <!-- Colonne droite -->
            <article class="col">
                <!-- ... autres champs ... -->

                <div class="champ">
                    <span class="field-label">Code vendeur :</span>
                    <span class="code-vendeur">VD<?= str_pad($code_vendeur, 3, '0', STR_PAD_LEFT) ?></span>
                </div>
            </article>

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
</body>

</html>
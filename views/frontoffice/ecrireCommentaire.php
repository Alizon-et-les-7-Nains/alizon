<?php 
session_start();
require_once "../../controllers/pdo.php";

$productId = intval($_GET['id'] ?? 0);

if ($productId === 0) {
    die("Produit non spécifié.");
}

$sqlProduit = "SELECT p.nom, p.idVendeur AS nom_produit , id_vendeur FROM _produit p WHERE p.idProduit = ?";
$stmtProduit = $pdo->prepare($sqlProduit);
$stmtProduit->execute([$productId]);
$produit = $stmtProduit->fetch(PDO::FETCH_ASSOC);

if (!$produit) {
    die("Produit introuvable.");
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $clientId = intval($_SESSION['user_id'] ?? 0);
        $note = intval($_POST['note'] ?? 0);
        $sujet = trim($_POST['sujet'] ?? '');
        $message = trim($_POST['message'] ?? '');

        // Validations
        if ($clientId === 0) $errors[] = "Vous devez être connecté pour laisser un avis.";
        if ($note < 1 || $note > 5) $errors[] = "Veuillez sélectionner une note.";
        if (empty($sujet)) $errors[] = "Le sujet est obligatoire.";
        if (strlen($message) < 10) $errors[] = "Le message doit contenir au moins 10 caractères.";

        if (empty($errors)) {
            $fileName = null;
            
            // Traitement de l'image
            if (!empty($_FILES['photo']['name'])) {
                // Utilisation d'un chemin relatif au dossier public pour plus de fiabilité
                $targetDir = __DIR__ . "/../../public/images/imagesAvis/";
                
                if (!is_dir($targetDir)) {
                    mkdir($targetDir, 0755, true);
                }
                
                $fileExtension = strtolower(pathinfo($_FILES["photo"]["name"], PATHINFO_EXTENSION));
                $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                
                if (in_array($fileExtension, $allowedExtensions)) {
                    $dbFileName = uniqid('avis_') . '.' . $fileExtension;
                    $targetFile = $targetDir . $dbFileName;

                    if (move_uploaded_file($_FILES["photo"]["tmp_name"], $targetFile)) {
                        // Chemin stocké en base de données pour l'affichage web
                        $fileName = $dbFileName;
                    } else {
                        $errors[] = "Erreur lors de l'upload de l'image.";
                    }
                } else {
                    $errors[] = "Format d'image non autorisé.";
                }
            }

            if (empty($errors)) {
                // 1. Insertion ou mise à jour de l'avis
                $sqlAvis = "INSERT INTO _avis (idProduit, idClient, titreAvis, contenuAvis, note, dateAvis) 
                            VALUES (:idProduit, :idClient, :titre, :contenu, :note, CURDATE())
                            ON DUPLICATE KEY UPDATE titreAvis = VALUES(titreAvis), contenuAvis = VALUES(contenuAvis), note = VALUES(note), dateAvis = CURDATE()";
                $stmt = $pdo->prepare($sqlAvis);
                $stmt->execute([
                    ':idProduit' => $productId,
                    ':idClient' => $clientId,
                    ':titre' => $sujet,
                    ':contenu' => $message,
                    ':note' => $note
                ]);

                $stmt = $pdo->prepare("
                    INSERT INTO _notification (idClient, contenuNotif, titreNotif, dateNotif, est_vendeur) 
                    VALUES (?, ?, ?, ?, 1)
                ");
                
                $nomProduit = htmlspecialchars($produit['nom_produit']);

                $stmt->execute([
                    $produit['id_vendeur'],
                    "Vous avez un nouvel avis pour {$nomProduit} : \"{$sujet}\" avec une note de {$note}/5.",
                    "✉️ Nouvel avis sur {$nomProduit} !",
                    date('Y-m-d H:i:s'),
                ]);

                // 2. Insertion de l'image si présente
                if ($fileName) {
                    $sqlImageAvis = "INSERT INTO _imageAvis (idProduit, idClient, URL) 
                                    VALUES (:idProduit, :idClient, :urlImage)
                                    ON DUPLICATE KEY UPDATE URL = VALUES(URL)";
                    $stmtImageAvis = $pdo->prepare($sqlImageAvis);
                    $stmtImageAvis->execute([
                        ':idProduit' => $productId,
                        ':idClient' => $clientId,
                        ':urlImage' => $fileName 
                    ]);
                }

                header("Location: produit.php?id=" . $productId);
                exit;
            }
        }
    } catch(PDOException $e) {
        $errors[] = "Erreur base de données : " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Écrire un avis - <?php echo htmlspecialchars($produit['nom_produit']); ?></title>
    <link rel="icon" href="/public/images/logoBackoffice.svg">
    <link rel="stylesheet" href="../../public/style.css">
</head>
<body class="pageEcrireCommentaire">
<header>
    <?php include '../../views/frontoffice/partials/headerConnecte.php'; ?>
</header>
<main>
    <?php if (!empty($errors)): ?>
        <div class="error-messages" style="background-color: #f8d7da; color: #721c24; padding: 15px; margin: 20px; border-radius: 5px; border: 1px solid #f5c6cb;">
            <h3>Erreurs :</h3>
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    <div id="popupConfirmation" class="modal-popup" style="display: none;">
        <div class="modal-content">
            <span class="close-modal">&times;</span>
            <h2>Confirmer la publication</h2>
            <div id="recapAvis">
                <p><strong>Note :</strong> <span id="recapNote"></span>/5⭐</p>
                <p><strong>Sujet :</strong> <span id="recapSujet"></span></p>
            </div>
            <p>Votre avis sera visible par tous les utilisateurs.</p>
            <div id="boutonsPopupAvis">
                <button id="btnConfirmerAvis" class="bouton boutonBleu">Valider la publication</button>
                <button id="btnAnnulerPopup" class="bouton boutonRose">Annuler</button>
            </div>
        </div>
    </div>
    <section class="reviewArticle">
        <form action="" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="idProduit" value="<?php echo $productId; ?>">
            
            <h1>Évaluer : <b><?php echo htmlspecialchars($produit['nom_produit']); ?></b></h1>
            
            <h2>Laisser une note <span style="color: red;">*</span> :</h2>
            <article class="etoiles">
                <img src="../../public/images/etoileVide.svg" data-index="1" class="star" alt="1 étoile">
                <img src="../../public/images/etoileVide.svg" data-index="2" class="star" alt="2 étoiles">
                <img src="../../public/images/etoileVide.svg" data-index="3" class="star" alt="3 étoiles">
                <img src="../../public/images/etoileVide.svg" data-index="4" class="star" alt="4 étoiles">
                <img src="../../public/images/etoileVide.svg" data-index="5" class="star" alt="5 étoiles">
            </article>
            <input type="hidden" name="note" id="note" value="0">            
            <h2>Ajouter une photo (optionnel) :</h2>
            <ul>
                <img src="../../public/images/ajouterPhoto.svg" alt="Ajouter une photo" id="ajouterPhoto" style="cursor: pointer; width: 80px; height: 80px;">
                <input type="file" id="inputPhoto" accept="image/*" style="display:none" name="photo">
                <div id="preview"></div>
            </ul>
            
            <h2>Sujet <span style="color: red;">*</span> :</h2>
            <textarea name="sujet" id="sujet" placeholder="Ex: Excellent produit artisanal" rows="2" required><?php echo isset($_POST['sujet']) ? htmlspecialchars($_POST['sujet']) : ''; ?></textarea>
            
            <h2>Message <span style="color: red;">*</span> :</h2>
            <textarea name="message" id="message" placeholder="Partagez votre expérience avec ce produit..." rows="6" required><?php echo isset($_POST['message']) ? htmlspecialchars($_POST['message']) : ''; ?></textarea>            
            <button type="submit" class="bouton boutonBleu">Publier mon avis</button>
            </form>
            <a href="produit.php?id=<?php echo $productId; ?>" class="bouton boutonRose" style="display: inline-block; text-align: center; text-decoration: none; margin-left: 10px;">Annuler</a>
        
    </section>
    <?php require_once '../backoffice/partials/retourEnHaut.php' ?>
</main>
<footer>
    <?php include '../../views/frontoffice/partials/footerConnecte.php'; ?>
</footer>
</body>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const noteInput = document.getElementById('note');
    const stars = document.querySelectorAll('.star');
    const emptyStar = "../../public/images/etoileVide.svg";
    const fullStar = "../../public/images/etoile.svg";

    // Pour toutes les étoiles, si on clique desus alors on change celle-ci 
    // Ainsi que les précédentes en étoiles pleines. Cela permet aussi de stocker
    // L'information du nombre d'étoiles que l'on souhaite mettre.
    stars.forEach((star, index) => {
        star.addEventListener('click', () => {
            const rating = index + 1;
            stars.forEach((s, i) => {
                s.src = i < rating ? fullStar : emptyStar;
            });
            noteInput.value = rating;
        });
    });

    // Gestion de l'upload photo
    const ajouterPhoto = document.getElementById('ajouterPhoto');
    const inputPhoto = document.getElementById('inputPhoto');
    
    ajouterPhoto.addEventListener('click', () => {
        inputPhoto.click();
    });

    inputPhoto.addEventListener('change', function () {
        const file = this.files[0];
        if (!file) return;

        if (file.size > 5 * 1024 * 1024) {
            alert('La photo ne doit pas dépasser 5 MB');
            this.value = '';
            return;
        }

        const reader = new FileReader();
        reader.onload = function (e) {
            document.getElementById('preview').innerHTML =
                `<div style="position: relative; display: inline-block; margin-top: 10px;">
                    <img src="${e.target.result}" style="max-width:200px; border-radius: 8px; border: 2px solid #273469;">
                    <button type="button" onclick="removePhoto()" style="position: absolute; top: -10px; right: -10px; background: red; color: white; border: none; border-radius: 50%; width: 30px; height: 30px; cursor: pointer; font-size: 18px;">×</button>
                </div>`;
        };
        reader.readAsDataURL(file);
    });

    // Gestion de la popup de confirmation
    const formulaire = document.querySelector('form');
    const popupConfirmation = document.getElementById('popupConfirmation');
    const btnConfirmer = document.getElementById('btnConfirmerAvis');
    const btnAnnuler = document.getElementById('btnAnnulerPopup');
    const closeBtns = document.querySelectorAll('.close-modal');

    formulaire.addEventListener('submit', function(e) {
        e.preventDefault();

        const note = parseInt(noteInput.value);
        const sujet = document.getElementById('sujet').value.trim();
        const message = document.getElementById('message').value.trim();

        if (note === 0 || note < 1 || note > 5) {
            alert('Veuillez sélectionner une note entre 1 et 5 étoiles');
            return false;
        }

        if (sujet === '') {
            alert('Veuillez remplir le sujet');
            return false;
        }

        if (message === '' || message.length < 10) {
            alert('Le message doit contenir au moins 10 caractères');
            return false;
        }

        document.getElementById('recapNote').textContent = note;
        document.getElementById('recapSujet').textContent = sujet;
        // document.getElementById('recapMessage').textContent = message;

        popupConfirmation.style.display = 'flex';
    });

    btnConfirmer.addEventListener('click', function() {
        popupConfirmation.style.display = 'none';
        formulaire.submit();
    });

    btnAnnuler.addEventListener('click', function() {
        popupConfirmation.style.display = 'none';
    });

    closeBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            this.closest('.modal-popup').style.display = 'none';
        });
    });

    window.addEventListener('click', function(e) {
        if (e.target === popupConfirmation) {
            popupConfirmation.style.display = 'none';
        }
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            popupConfirmation.style.display = 'none';
        }
    });
});

function removePhoto() {
    document.getElementById('preview').innerHTML = '';
    document.getElementById('inputPhoto').value = '';
}
</script>
</html>
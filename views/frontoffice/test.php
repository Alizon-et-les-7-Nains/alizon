<?php 
session_start();

// Configuration de la connexion à la base de données
$host = 'localhost';
$dbname = 'saedb';
$username = 'votre_username';
$password = 'votre_password';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Récupération des données du formulaire
    $note = floatval($_POST['note'] ?? 0);
    $sujet = trim($_POST['sujet'] ?? '');
    $message = trim($_POST['message'] ?? '');
    
    // À récupérer depuis la session ou l'URL
    $idProduit = 1; // TODO: Récupérer l'ID réel du produit
    $idClient = $_SESSION['idClient'] ?? null; // TODO: Vérifier que l'utilisateur est connecté
    
    // Validation des données
    if ($idClient === null) {
        die("Vous devez être connecté pour laisser un avis");
    }
    
    if ($note < 1 || $note > 5) {
        die("La note doit être entre 1 et 5");
    }
    
    if (empty($sujet) || empty($message)) {
        die("Le sujet et le message sont obligatoires");
    }
    
    try {
        $sql = "INSERT INTO saedb._avis (idProduit, idClient, titreAvis, contenuAvis, note, dateAvis) 
                VALUES (:idProduit, :idClient, :titre, :contenu, :note, CURDATE())";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':idProduit' => $idProduit,
            ':idClient' => $idClient,
            ':titre' => $sujet,
            ':contenu' => $message,
            ':note' => $note
        ]);
        
        // Traitement de l'image si elle existe
        if (!empty($_FILES['photo']['name']) && $_FILES['photo']['error'] === 0) {
            
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $maxSize = 5 * 1024 * 1024; // 5MB
            
            if (!in_array($_FILES['photo']['type'], $allowedTypes)) {
                throw new Exception("Type de fichier non autorisé");
            }
            
            if ($_FILES['photo']['size'] > $maxSize) {
                throw new Exception("Fichier trop volumineux (max 5MB)");
            }
            
            $targetDir = "../../public/images/";
            $extension = pathinfo($_FILES["photo"]["name"], PATHINFO_EXTENSION);
            $fileName = "avis_" . $idProduit . "_" . $idClient . "_" . time() . "." . $extension;
            $targetFile = $targetDir . $fileName;
            
            if (move_uploaded_file($_FILES["photo"]["tmp_name"], $targetFile)) {
                
                // Insertion de l'image dans la table _image (si elle n'existe pas déjà)
                $sqlImage = "INSERT IGNORE INTO saedb._image (URL, alt, titre) 
                            VALUES (:url, :alt, :titre)";
                $stmtImage = $pdo->prepare($sqlImage);
                $stmtImage->execute([
                    ':url' => $fileName,
                    ':alt' => 'Photo avis ' . $sujet,
                    ':titre' => $sujet
                ]);
                
                // Lien entre l'image et l'avis
                $sqlImageAvis = "INSERT INTO saedb._imageAvis (idProduit, idClient, URL) 
                                VALUES (:idProduit, :idClient, :url)";
                $stmtImageAvis = $pdo->prepare($sqlImageAvis);
                $stmtImageAvis->execute([
                    ':idProduit' => $idProduit,
                    ':idClient' => $idClient,
                    ':url' => $fileName
                ]);
            }
        }
        
        // Redirection après succès
        $_SESSION['message_success'] = "Votre avis a été publié avec succès !";
        header("Location: page_produit.php?id=" . $idProduit);
        exit;
        
    } catch(PDOException $e) {
        if ($e->getCode() == 23000) { // Duplicate entry
            die("Vous avez déjà laissé un avis pour ce produit");
        }
        die("Erreur lors de l'insertion : " . $e->getMessage());
    } catch(Exception $e) {
        die("Erreur : " . $e->getMessage());
    }
}

// Données pour l'affichage (à récupérer depuis la BDD normalement)
$idProduit = intval($_GET['id'] ?? 1);

// Récupération des images du produit
$sqlImages = "SELECT i.URL, i.titre 
              FROM saedb._imageDeProduit ip 
              JOIN saedb._image i ON ip.URL = i.URL 
              WHERE ip.idProduit = :idProduit";
$stmtImages = $pdo->prepare($sqlImages);
$stmtImages->execute([':idProduit' => $idProduit]);
$images = $stmtImages->fetchAll(PDO::FETCH_ASSOC);

if (empty($images)) {
    $images = [['URL' => 'defaultImageProduit.png', 'titre' => 'Produit']];
}

// Récupération des infos du produit
$sqlProduit = "SELECT p.nom, p.description, p.prix, p.stock, v.prenom as prenom_vendeur, v.nom as nom_vendeur
               FROM saedb._produit p
               JOIN saedb._vendeur v ON p.idVendeur = v.codeVendeur
               WHERE p.idProduit = :idProduit";
$stmtProduit = $pdo->prepare($sqlProduit);
$stmtProduit->execute([':idProduit' => $idProduit]);
$produit = $stmtProduit->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laisser un avis</title>
    <link rel="stylesheet" href="../../public/style.css">
</head>
<body class="pageEcrireCommentaire">
    <section class="produit">
        <img src="../../public/images/<?php echo htmlspecialchars($images[0]['URL']); ?>" alt="">
        <h2><?php echo htmlspecialchars($images[0]['titre']); ?></h2>
    </section>
    <hr>
    <section class="reviewArticle">
        <form action="" method="POST" enctype="multipart/form-data">
            <h1>Cet article vous a-t-il plu ?</h1>
            <h2>Laisser une note : </h2>
            <article class="etoiles">
                <img src="../../public/images/etoileVide.svg" data-index="1" class="star" alt="">
                <img src="../../public/images/etoileVide.svg" data-index="2" class="star" alt="">
                <img src="../../public/images/etoileVide.svg" data-index="3" class="star" alt="">
                <img src="../../public/images/etoileVide.svg" data-index="4" class="star" alt="">
                <img src="../../public/images/etoileVide.svg" data-index="5" class="star" alt="">
            </article>
            <input type="hidden" name="note" id="note" required>
            <h2>Ajouter une photo : </h2>
            <ul>
                <img src="../../public/images/ajouterPhoto.svg" alt="" id="ajouterPhoto">
                <input type="file" name="photo" id="inputPhoto" accept="image/*" style="display:none">
                <div id="preview"></div>
            </ul>
            <h2>Écrire un commentaire : </h2>
            <textarea name="sujet" id="sujet" placeholder="Sujet" required></textarea>
            <textarea name="message" id="message" placeholder="Message" required></textarea>
            <button type="submit" class="bouton boutonBleu">Publier</button>
        </form>
    </section>
</body>
<script>
    const noteInput = document.getElementById('note');
    const stars = document.querySelectorAll('.star');
    const emptyStar = "../../public/images/etoileVide.svg";
    const fullStar = "../../public/images/etoile.svg";

    stars.forEach((star, index) => {
        star.addEventListener('click', () => {
            const rating = index + 1;
            stars.forEach((s, i) => {
                s.src = i < rating ? fullStar : emptyStar;
            });
            noteInput.value = rating;
        });
    });

    const ajouterPhoto = document.getElementById('ajouterPhoto');
    const inputPhoto = document.getElementById('inputPhoto');
    
    ajouterPhoto.addEventListener('click', () => {
        inputPhoto.click();
    });

    inputPhoto.addEventListener('change', function () {
        const file = this.files[0];
        if (!file) return;

        const reader = new FileReader();
        reader.onload = function (e) {
            document.getElementById('preview').innerHTML =
                `<img src="${e.target.result}" style="max-width:150px">`;
        };
        reader.readAsDataURL(file);
    });
</script>
</html>
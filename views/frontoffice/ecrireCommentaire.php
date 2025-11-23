<?php 
session_start();
require_once "../../controllers/pdo.php";

// Insert review (only once!)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $productId = intval($_GET['id'] ?? 0);
        $clientId = intval($_SESSION['idClient'] ?? 0);
        $note = intval($_POST['note'] ?? 0);
        $sujet = trim($_POST['sujet'] ?? '');
        $message = trim($_POST['message'] ?? '');

        // Validation
        if ($clientId === 0) {
            die("Vous devez être connecté pour laisser un avis.");
        }
        if ($productId === 0) {
            die("Produit invalide.");
        }
        if (empty($sujet) || empty($message) || $note === 0) {
            die("Veuillez remplir tous les champs obligatoires.");
        }

        // Handle file upload
        $fileName = null;
        if (!empty($_FILES['photo']['name'])) {
            $targetDir = "../../public/images/";
            $fileName = basename($_FILES["photo"]["name"]);
            $targetFile = $targetDir . $fileName;

            if (move_uploaded_file($_FILES["photo"]["tmp_name"], $targetFile)) {
                // File uploaded successfully
            } else {
                $fileName = null;
            }
        }

        // Insert review
        $sqlAvis = "INSERT INTO saedb._avis (idProduit, idClient, titreAvis, contenuAvis, note, dateAvis) 
        VALUES (:idProduit, :idClient, :titre, :contenu, :note, CURDATE())";
        $stmt = $pdo->prepare($sqlAvis);
        $stmt->execute([
            ':idProduit' => $productId,
            ':idClient' => $clientId,
            ':titre' => $sujet,
            ':contenu' => $message,
            ':note' => $note
        ]);

        // Only insert image if one was uploaded
        if ($fileName) {
            $sqlImageAvis = "INSERT INTO saedb._imageAvis (idProduit, idClient, URL) 
                            VALUES (:idProduit, :idClient, :urlImage)";
            $stmtImageAvis = $pdo->prepare($sqlImageAvis);
            $stmtImageAvis->execute([
                ':idProduit' => $productId,
                ':idClient' => $clientId,
                ':urlImage' => '/images/' . $fileName  // Added path prefix
            ]);
        }

        // Redirect after successful submission
        header("Location: product.php?id=" . $productId);
        exit;

    } catch(PDOException $e) {
        die("Erreur lors de l'insertion de l'avis : " . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="../../public/style.css">
</head>
<?php // include "../../views/frontoffice/partials/headerConnecte.php" ?>
<body class="pageEcrireCommentaire">
    <!-- <section class="produit">
        <img src="../../public/images/<?php echo $images[0]['URL']?>" alt="">
        <h2><?php echo $images[0]['title']?></h2>
    </section>
    <hr> -->
    <section class="reviewArticle">
        <form action="" method="POST" enctype="multipart/form-data">
            <h1>Cet article vous a-t'il plu ?</h1>
            <h2>Laisser une note : </h2>
            <article class="etoiles">
                <img src="../../public/images/etoileVide.svg" data-index="1" class="star" alt="">
                <img src="../../public/images/etoileVide.svg" data-index="2" class="star" alt="">
                <img src="../../public/images/etoileVide.svg" data-index="3" class="star" alt="">
                <img src="../../public/images/etoileVide.svg" data-index="4" class="star" alt="">
                <img src="../../public/images/etoileVide.svg" data-index="5" class="star" alt="">
            </article>
            <input type="hidden" name="note" id="note">
            <h2>Ajouter des photos : </h2>
            <ul>
                <img src="../../public/images/ajouterPhoto.svg" alt="" id="ajouterPhoto">
                    <input type="file" id="inputPhoto" accept="image/*" style="display:none" name="photo">
                    <button type="submit" id="submitBtn" style="display:none"></button>
                <div id="preview"></div>
            </ul>
            <h2>Ecrire un commentaire : </h2>
            <textarea name="sujet" id="sujet" placeholder="Sujet"></textarea>
            <input type="text" name="titre" id="titre" style="display:none">
            <textarea name="message" id="message" placeholder="Message"></textarea>
            <input type="text" name="titre" id="titre" style="display:none">
            <button type="submit" class="bouton boutonBleu">Publier</button>
        </form>
    </section>
</body>
<script>
    const noteInput = document.getElementById('note')
    const stars = document.querySelectorAll('.star');
    const emptyStar = "../../public/images/etoileVide.svg";
    const fullStar = "../../public/images/etoile.svg";

    stars.forEach((star, index) => {
        star.addEventListener('click', () => {
            const rating = index + 1;

            stars.forEach((s, i) => {
                if (i < rating) {
                    s.src = fullStar;
                } else {
                    s.src = emptyStar
                }
            });
            noteInput.value = rating;
        });
    });

    const ajouterPhoto = document.getElementById('ajouterPhoto');
    const inputPhoto = document.getElementById('inputPhoto');
    ajouterPhoto.addEventListener('click', () => {
        inputPhoto.click();
    })

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
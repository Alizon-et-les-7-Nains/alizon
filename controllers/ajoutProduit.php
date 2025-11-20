<?php
session_start();
require_once 'pdo.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nom = $_POST['nom']; 
    $prix = $_POST['prix'];
    $poids = $_POST['poids'];
    $description = $_POST['description'];
    $mots_cles = $_POST['mots_cles'];

    try {

        $pdo->beginTransaction();

        // Insertion dans _produit
        $sql = "INSERT INTO _produit 
            (nom, prix, poids, description, mots_cles) 
            VALUES (:nom, :prix, :poids, :description, :mots_cles)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':nom' => $nom,
            ':prix' => $prix,
            ':poids' => $poids,
            ':description' => $description,
            ':mots_cles' => $mots_cles
        ]);

        // On récupère l'ID généré
        $idNewProduit = $pdo->lastInsertId();

        // Gestion des images
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === 0) {
            $extension = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
            $nouveauNomImage = 'produit_' . $idNewProduit . '_' . time() . '.' . $extension;
            $dossierDestination = "../public/images/" . $nouveauNomImage;
            // Déplacement du fichier
            if (!move_uploaded_file($_FILES['photo']['tmp_name'], $dossierDestination)) {
                throw new Exception("Impossible de déplacer l'image.");
            }

            // Insertion dans _image car sinon l'insertion de l'image dans _imageDeProduit ne pourra pas etre faite
            $sql = "INSERT INTO _image (URL) VALUES (:URL)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':URL' => $nouveauNomImage]);

            // Insertion dans _imageDeProduit
            $sql = "INSERT INTO _imageDeProduit (idProduit, URL) VALUES (:idProduit, :URL)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':idProduit' => $idNewProduit,
                ':URL' => $nouveauNomImage
            ]);
        }

        $pdo->commit();

        header('Location: ../views/backoffice/produits.php?success=1');
        exit();

    } catch (Exception $e) {
        $pdo->rollBack();
        die("Erreur lors de l'ajout : " . $e->getMessage());
    }
}
?>

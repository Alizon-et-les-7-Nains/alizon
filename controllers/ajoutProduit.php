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

        // insertion de produit
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

        $idNewProduit = $pdo->lastInsertId();

        // Pour les images, insertion dans _imageDeProduit
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === 0) {
            
            $extension = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
            $nouveauNomImage = 'produit_' . $idNewProduit . '_' . time() . '.' . $extension;

            $dossierDestination = "../public/images/" . $nouveauNomImage;

            if (move_uploaded_file($_FILES['photo']['tmp_name'], $dossierDestination)) {

                $sql = "INSERT INTO _imageDeProduit (idProduit, URL) 
                        VALUES (:idProduit, :URL)";
                
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':idProduit' => $idNewProduit,
                    ':URL' => $nouveauNomImage
                ]);
            }
        }

        $pdo->commit();

        header('Location: ../views/backoffice/produits.php?success=1');
        exit();

    } catch (PDOException $e) {
        $pdo->rollBack();
        echo "<h3>Erreur SQL :</h3><pre>".$e->getMessage()."</pre>";
        exit();
    }
}
?>

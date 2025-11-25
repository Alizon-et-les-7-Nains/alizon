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
            (nom, prix, poids, description, mots_cles, idVendeur, stock, versionProd, note) 
            VALUES (:nom, :prix, :poids, :description, :mots_cles, :idVendeur, :stock, :versionProd, :note)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':nom' => $nom,
            ':prix' => $prix,
            ':poids' => $poids,
            ':description' => $description,
            ':mots_cles' => $mots_cles,
            ':idVendeur' => $_SESSION['id'],
            ':stock' => 1,
            ':versionProd' => 1.0,
            ':note' => 0.0
        ]);

        // On récupère l'ID généré
        $idNewProduit = $pdo->lastInsertId();

        // Gestion des images
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === 0) {
            $extension = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
            $nouveauNomImage = 'produit_' . $idNewProduit . '_' . time() . '.' . $extension;
            $dossierDestination = $_SERVER['DOCUMENT_ROOT'] . '/images/' . $nouveauNomImage;
            // Déplacement du fichier
            error_log(print_r($_FILES['photo'], true));
            if (!move_uploaded_file($_FILES['photo']['tmp_name'], $dossierDestination)) {
                throw new Exception("Impossible de déplacer l'image.");
            }

            // Insertion dans _imageDeProduit
            $sql = "INSERT INTO _imageDeProduit (idProduit, URL) VALUES (:idProduit, :URL)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':idProduit' => $idNewProduit,
                ':URL' => "/images/$nouveauNomImage"
            ]);
        }

        $pdo->commit();

        $id_session = session_id();
        $_SESSION['id_session'] = $id_session;
        header('Location: ../views/backoffice/accueil.php');
        exit();

    } catch (Exception $e) {
        $pdo->rollBack();
        die("Erreur lors de l'ajout : " . $e->getMessage());
    }
}
?>

<?php
session_start();
require_once 'pdo.php';

$idVendeur = $_SESSION['idVendeur'] ?? 1; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // on récupère les données
    $nom = $_POST['nom']; 
    $prix = $_POST['prix'];
    $poids = $_POST['poids'];
    $description = $_POST['description'];
    $mots_cles = $_POST['mots_cles'];

    try {
        // commencement
        $pdo->beginTransaction();

        $sql = "INSERT INTO _produit 
            (nom, prix, poids, description, mots_cles, idVendeur) 
                       VALUES (:nom, :prix, :poids, :description, :mots_cles, :idVendeur) 
                       RETURNING idProduit";
        
        $stmt = $pdo->prepare($sql);

        $stmt->execute([$nom, $prix, $poids, $description, $mots_cles, $idVendeur]);
        
        // Récupération de l'ID généré
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $idNewProduit = $result['idProduit'];

        // Gestion de l'image du produit
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === 0) {
            
            $extension = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
            $nouveauNomImage = 'produit_' . $idNewProduit . '_' . time() . '.' . $extension;
            
            $dossierDestination = "../public/images/" . $nouveauNomImage;

            if (move_uploaded_file($_FILES['photo']['tmp_name'], $dossierDestination)) {
                
                // Insertion dans la table _imageDeProduit
                $sql = "INSERT INTO imageDeProduit (idProduit, URL) VALUES (:idProduit, :URL)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$idNewProduit, $nouveauNomImage]);
            }
        }

        $pdo->commit();

        // Redirection
        header('Location: ../views/backoffice/produits.php?success=1');
        exit();

    } catch (Exception $e) {
        $pdo->rollBack();
        echo "Erreur lors de l'ajout : " . $e->getMessage();
    }
}
?>
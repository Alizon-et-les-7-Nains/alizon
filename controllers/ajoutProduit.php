<?php
    session_start();
    require_once 'pdo.php';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // on récupère les données
        $nom = $_POST['nom']; 
        $prix = $_POST['prix'];
        $poids = $_POST['poids'];
        $description = $_POST['description'];
        $mots_cles = $_POST['mots_cles'];

        $sql = "INSERT INTO _produit 
            (nom, prix, poids, description, mots_cles) 
                VALUES (:nom, :prix, :poids, :description, :mots_cles) 
                    RETURNING idProduit";
            
        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            ':nom' => $nom, 
            ':prix' => $prix, 
            ':poids' => $poids, 
            'description' => $description, 
            'mots_cles' => $mots_cles
        ]);
            
        /* Récupération de l'ID généré
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $idNewProduit = $result['idProduit'];

        // Gestion de l'image du produit
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === 0) {
                
            $extension = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
            $nouveauNomImage = 'produit_' . $idNewProduit . '_' . time() . '.' . $extension;
                
            $dossierDestination = "../public/images/" . $nouveauNomImage;

            if (move_uploaded_file($_FILES['photo']['tmp_name'], $dossierDestination)) {
                    
                // Insertion dans la table _imageDeProduit
                $sql = "INSERT INTO _imageDeProduit (idProduit, URL) VALUES (:idProduit, :URL)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$idNewProduit, $nouveauNomImage]);
            }
        }*/

        $pdo->commit();

        $id_session = session_id();
        $_SESSION['id_session'] = $id_session;
        header('Location: ../views/backoffice/produits.php?success=1');
        exit();
    }
?>
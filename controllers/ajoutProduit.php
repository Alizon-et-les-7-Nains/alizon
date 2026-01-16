<?php
ob_start();
require_once 'pdo.php';
require_once 'treatment.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = $_POST['nom']; 
    $prix = $_POST['prix'];
    $poids = $_POST['poids'];
    $description = $_POST['description'];
    $mots_cles = $_POST['mots_cles'];
    $typeProd = $_POST['typeProd'];

    $dateActuelle = date('Y-m-d H:i:s');
    $versionInitiale = "1.0";

    try {
        $pdo->beginTransaction();

        $sql = "INSERT INTO _produit 
                (nom, prix, poids, description, mots_cles, idVendeur, stock, versionProd, note, seuilAlerte, enVente, typeProd, dateAjout, dateDerniereModif) 
                VALUES 
                (:nom, :prix, :poids, :description, :mots_cles, :idVendeur, :stock, :versionProd, :note, :seuilAlerte, :enVente, :typeProd, :dateAjout, :dateModif)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':nom'          => $nom,
            ':prix'         => $prix,
            ':poids'        => $poids,
            ':description'  => $description,
            ':mots_cles'    => $mots_cles,
            ':idVendeur'    => $_SESSION['id'],
            ':stock'        => 0,
            ':versionProd'  => $versionInitiale,
            ':note'         => 0.0,
            ':seuilAlerte'  => 0,
            ':enVente'      => 0,
            ':typeProd'     => $typeProd,
            ':dateAjout'    => $dateActuelle,
            ':dateModif'    => $dateActuelle
        ]);

        $idNewProduit = $pdo->lastInsertId();

        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === 0) {
            $extension = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
            $nouveauNomImage = 'produit_' . $idNewProduit . '_' . time() . '.' . $extension;
            
            $dossierDestination = $_SERVER['DOCUMENT_ROOT'] . '/public/images/upload/' . $nouveauNomImage;

            try {
                treat($_FILES['photo']['tmp_name'], $dossierDestination);
            } catch (Exception $e) {
                move_uploaded_file($_FILES['photo']['tmp_name'], $dossierDestination);
            }

            $sqlImg = "INSERT INTO _imageDeProduit (idProduit, URL) VALUES (:idProduit, :URL)";
            $stmtImg = $pdo->prepare($sqlImg);
            $stmtImg->execute([
                ':idProduit' => $idNewProduit,
                ':URL' => "/public/images/upload/$nouveauNomImage"
            ]);
        }

        $pdo->commit();
        header('Location: ../views/backoffice/accueil.php');
        exit();

    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        die("Erreur lors de l'ajout du produit : " . $e->getMessage());
    }
}
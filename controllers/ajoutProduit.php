<?php
    session_start();
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
    require_once 'pdo.php';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        try {
            $pdo->beginTransaction();

            $sql = "INSERT INTO _produit 
                (nom, prix, poids, description, mots_cles) 
                VALUES (:nom, :prix, :poids, :description, :mots_cles)
                RETURNING idProduit";

            $stmt = $pdo->prepare($sql);

            $stmt->execute([
                ':nom' => $_POST['nom'], 
                ':prix' => $_POST['prix'], 
                ':poids' => $_POST['poids'], 
                ':description' => $_POST['description'], 
                ':mots_cles' => $_POST['mots_cles']
            ]);

            $pdo->commit();
            header('Location: ../views/backoffice/produits.php?success=1');
            exit();

        } catch (PDOException $e) {
            $pdo->rollBack();
            die("Erreur lors de l'ajout du produit : " . $e->getMessage());
        }
    }
?>
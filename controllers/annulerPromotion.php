<?php

require_once 'pdo.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') { 

    if(isset($_POST['id']) && isset($_POST['nom']) && isset($_POST['annulationProduit'])) {
        $idProd = intval($_POST['id']); 

        try {
            $stmt = $pdo->prepare("DELETE FROM _promotion WHERE idProduit =:idProd");
            $stmt->execute([':idProd' => $idProd]);
        } catch (Exception $e) {
            header('Location: ../views/backoffice/produits.php?error=3&idProduit='.$idProd);
            exit;
        }

    }

    header('Location: ../views/backoffice/produits.php');
    exit;
}

?>
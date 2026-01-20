<?php

require_once 'pdo.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') { 

    if(isset($_POST['annulationProduit'])) {
        // On récupère l'id du produit
        $idProd = intval($_POST['annulationProduit']); 

        try {
            // On supprime la remise de la table _remise
            $stmt = $pdo->prepare("DELETE FROM _remise WHERE idProduit =:idProd");
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
<?php
require_once 'pdo.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // On recupere l'id du produit
    $idproduit = $_POST['idproduit'];
    // On modifie l'attribut envente dans la table _produit
    $stmt = $pdo->prepare("UPDATE saedb._produit SET envente = true WHERE idproduit = :idproduit");
    $stmt->execute([
        ':idproduit' => $idproduit
    ]);
}

header("Location: ../views/backoffice/produits.php"); 
exit()
?>

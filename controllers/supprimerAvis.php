<?php 
require_once 'pdo.php';
session_start();
$idProd = $_GET['id'];  
$idClient = $_SESSION['user_id'];  
function updateNoteProduit(PDO $pdo, int $idProduit) {
    // Change la note du produit après la suppression d'un commentaire
    $stmt = $pdo->prepare("
        SELECT AVG(note) AS moyenne
        FROM _avis
        WHERE idProduit = ?
    ");
    $stmt->execute([$idProduit]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    // Recalcule de la moyenne du produit
    $moyenne = $result['moyenne'] !== null ? $result['moyenne'] : 0;

    // Update de la note du produit
    $stmt2 = $pdo->prepare("
        UPDATE _produit
        SET note = ?
        WHERE idProduit = ?
    ");
    $stmt2->execute([$moyenne, $idProduit]);
}
// Suppression du commentaire
$stmt = $pdo->prepare("DELETE FROM _avis WHERE idClient = :idClient AND idProduit = :idProduit");

try{
    $stmt->execute([
        ':idClient' => $idClient,
        ':idProduit' => $idProd
    ]);
}
catch(PDOException $e){
    echo "Erreur SQL : " . $e->getMessage();
}
updateNoteProduit($pdo, $idProd);

header("Location: ../views/frontoffice/mesAvis.php"); 
exit();

?>
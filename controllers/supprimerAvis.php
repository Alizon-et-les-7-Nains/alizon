<?php 
require_once 'pdo.php';
session_start();
$idProd = $_GET['id'];  
$idClient = $_SESSION['user_id'];  
function updateNoteProduit(PDO $pdo, int $idProduit) {
    $stmt = $pdo->prepare("
        SELECT AVG(note) AS moyenne
        FROM _avis
        WHERE idProduit = ?
    ");
    $stmt->execute([$idProduit]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    $moyenne = $result['moyenne'] !== null ? $result['moyenne'] : 0;

    $stmt2 = $pdo->prepare("
        UPDATE _produit
        SET note = ?
        WHERE idProduit = ?
    ");
    $stmt2->execute([$moyenne, $idProduit]);
}

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
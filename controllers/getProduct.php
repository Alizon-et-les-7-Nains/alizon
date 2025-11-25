<?php

require_once '../../controllers/pdo.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$stmt = $pdo->prepare('SELECT * FROM _produit WHERE idProduit = :idProduit');
$stmt->execute(['idProduit' => $data['idProduit']]);
echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));
exit;

?>
<?php

session_start();
require_once '../../controllers/pdo.php';

$data = json_decode(file_get_contents('php://input'), true);
$stmt = $pdo->prepare('select * from _produit where idProduit = :idProduit');
$stmt->execute(['idProduit' => $data['idProduit']]);
echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));

?>
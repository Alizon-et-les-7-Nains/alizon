<?php require_once 'pdo.php';
session_start();

$sql = "DELETE FROM _promotion WHERE finPromotion < NOW()";
$stmt = $pdo->prepare($sql);
$stmt->execute();

$sql = "DELETE FROM _remise WHERE finRemise < NOW()";
$stmt = $pdo->prepare($sql);
$stmt->execute();

$sql = "UPDATE _produit SET dateReassort = NULL WHERE dateReassort < NOW()";
$stmt = $pdo->prepare($sql);
$stmt->execute();
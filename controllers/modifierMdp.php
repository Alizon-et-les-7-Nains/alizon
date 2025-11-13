<?php
require_once 'pdo.php';
session_start();

$idClient = 1; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ancienMdp = $_POST['ancienMdp'];
    $nouveauMdp = $_POST['nouveauMdp'];
    $confirmationMdp = $_POST['confirmationMdp'];

    $stmt = $pdo->query(
    "UPDATE _client 
    SET mdp = '$nouveauMdp'
    WHERE idClient = '$idClient';");
}
?>

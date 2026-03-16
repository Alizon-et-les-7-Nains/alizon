<?php
    session_start();
    require_once "pdo.php";

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: ../views/backoffice/produits.php');
        exit;
    }

    $produits = $_POST['produits'];
    print_r($produits);

?>
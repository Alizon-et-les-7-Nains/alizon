<?php

require_once 'pdo.php';

if (isset($_POST['idProduit'])) {
    $pdo->beginTransaction();
    $prodSTMT = $pdo->prepare(file_get_contents('../queries/backoffice/produit.sql'));
    $prodSTMT->execute([':idProduit' => $_POST['idProduit']]);
    $prod = $prodSTMT->fetch(PDO::FETCH_ASSOC);

    if (isset($_POST['seuil']) && $_POST['seuil'] != $prod['seuilAlerte']) {
        $editSuilSTMT = $pdo->prepare(file_get_contents('../queries/backoffice/editSeuil.sql'));
        $editSuilSTMT->execute(['idProduit' => $_POST['idProduit'], 'seuil' => $_POST['seuil']]);
    }

    if (isset($_POST['date']) && $_POST['date'] != $prod['dateReassort']) {
        $editSuilSTMT = $pdo->prepare(file_get_contents('../queries/backoffice/editDate.sql'));
        $editSuilSTMT->execute(['idProduit' => $_POST['idProduit'], 'date' => $_POST['date']]);
    }

    if (isset($_POST['reassort']) && $_POST['reassort'] != $prod['stock']) {
        $editSuilSTMT = $pdo->prepare(file_get_contents('../queries/backoffice/editStock.sql'));
        $editSuilSTMT->execute(['idProduit' => $_POST['idProduit'], 'stock' => $_POST['reassort']]);
    }

    $pdo->commit();

    header('Location: ../views/backoffice/stocks.php');
    die();
}

?>
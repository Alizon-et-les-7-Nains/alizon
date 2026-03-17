<?php
    session_start();
    require_once "pdo.php";

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: ../views/backoffice/produits.php');
        exit;
    }

    if (!isset($_POST['produits'])) {
        echo "Aucun produit sélectionné";
        exit;
    }

    $produits = $_POST['produits'];

    //On fait ens sorte de faire (?, ?, ?) en fonction du nombre de produits sélectionnés ici 3 donc 3 points d'interrogation
    $placeholders = implode(',', array_fill(0, count($produits), '?'));

    $stmt = $pdo->prepare("
        SELECT nom, prix, URL, note FROM _produit INNER JOIN _imageDeProduit ON  _produit.idProduit = _imageDeProduit.idProduit 
        WHERE idProduit IN ($placeholders)
    ");

    $stmt->execute($produits);
    $produitsCatalogue = $stmt->fetchAll(PDO::FETCH_ASSOC);

    print_r($produitsCatalogue);

?>
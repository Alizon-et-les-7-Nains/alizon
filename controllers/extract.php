<?php

require_once 'pdo.php';
require_once 'date.php';

$stocks = [];

// Fonctions de récupération des données de stock
function fetchEpuises(PDO $pdo) {
    global $stocks;
    $epuiseSTMT = $pdo->prepare(file_get_contents('../queries/backoffice/produitsEpuises.sql'));
    $epuiseSTMT->execute(['idVendeur' => $_POST['id']]);
    $stocks['epuise'] = $epuiseSTMT->fetchAll(PDO::FETCH_ASSOC);
}
function fetchFaibles(PDO $pdo) {
    global $stocks;
    $faiblesSTMT = $pdo->prepare(file_get_contents('../queries/backoffice/stockFaible.sql'));
    $faiblesSTMT->execute(['idVendeur' => $_POST['id']]);
    $stocks['faible'] = $faiblesSTMT->fetchAll(PDO::FETCH_ASSOC);
}
function fetchstocks(PDO $pdo) {
    global $stocks;
    $stocksSTMT = $pdo->prepare(file_get_contents('../queries/backoffice/produitsStock.sql'));
    $stocksSTMT->execute(['idVendeur' => $_POST['id']]);
    $stocks['stock'] = $stocksSTMT->fetchAll(PDO::FETCH_ASSOC);
}

// Insertion des données nécessaires dans une variable
if (isset($_POST['tout']) && $_POST['tout'] == 'on') {
    fetchEpuises($pdo);
    fetchFaibles($pdo);
    fetchstocks($pdo);
} else {
    if (isset($_POST['epuise']) && $_POST['epuise'] == 'on') {
        fetchEpuises($pdo);
    }
    if (isset($_POST['faible']) && $_POST['faible'] == 'on') {
        fetchFaibles($pdo);
    }
    if (isset($_POST['stock']) && $_POST['stock'] == 'on') {
        fetchstocks($pdo);
    }
}

$date = new DateTime();

// Nommage et création du fichier en écriture
$fileName = "stocks_{$date->format('d-m-Y')}.csv";
$file = fopen($fileName, 'w+');

// Entêtes des colonnes du tableau
$columns = ['Produit', 'Prix (€)', 'Note (/5)', 'En Vente', 'Date de réassort', 'Seuil d\'alerte', 'Stock'];

$data = '';

foreach ($stocks as $cat => $products) {
    // Catégorie
    switch ($cat) {
        case 'epuise':
            $cat = 'Épuisé';
            break;
        case 'faible':
            $cat = 'En alerte';
            break;
        case 'stock':
            $cat = 'En stock';
            break;
    }
    $data .= $cat . "\n";
    
    // Entêtes
    if (!empty($products)) {
        // Entêtes
        for ($i = 0; $i < count($columns); $i++) {
            $data .= $columns[$i];
            if ($i != count($columns) - 1) $data .= ',';
        }
        $data .= "\n";

        // Données
        foreach ($products as $product) {
            $enVente = $product['enVente'] ? 'Oui' : 'Non';
            $dateReassort = $product['dateReassort'] == '' ? 'Aucune' : formatDate($product['dateReassort']); // Détecter s'il n'y a pas de date de réassort définie
            $data .= str_replace(',', ' ', $product['nom']) . ',' . $product['prix'] . ',' . round($product['note'], 1) . ',' . $enVente . ',' . $dateReassort . ',' . $product['seuilAlerte'] . ',' . $product['stock'] ."\n";
        }
    } else {
        $data .= "Aucun produit\n";
    }

    $data .= "\n";
}

fwrite($file, $data);
fclose($file);

// Requête de téléchargement du fichier
header('Content-Type: text/csv');
header("Content-Disposition: attachment; filename=\"$fileName\"");
readfile($fileName);

unlink($fileName);

?>
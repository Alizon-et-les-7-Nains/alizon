<?php

header('Content-Type: application/json');

define('ROOT', $_SERVER['DOCUMENT_ROOT']);

require_once ROOT . '/controllers/pdo.php';

session_start();
if (!isset($_SESSION['id'])) {
    http_response_code(401);
    echo json_encode(['401 error' => 'Utilisateur non authentifié']);
    exit();
}

$data = [];

if (isset($_GET['category'])) {
    if (empty($_GET['category'])) {
        $productsSTMT = $pdo->prepare(file_get_contents(ROOT . '/queries/backoffice/stats/products.sql'));
        $productsSTMT->execute([$_SESSION['id']]);
        $data = $productsSTMT->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $productsSTMT = $pdo->prepare(file_get_contents(ROOT . '/queries/backoffice/stats/productsByCategory.sql'));
        $productsSTMT->execute([$_SESSION['id'], urldecode($_GET['category'])]);
        $data = $productsSTMT->fetchAll(PDO::FETCH_ASSOC);
    }
} else {
    http_response_code(400);
    echo json_encode(['400 error' => 'Requête incomplète']);
    exit();
}

if ($data === false) {
    http_response_code(500);
    echo json_encode(['500 error' => 'Impossible de résoudre les données']);
    exit();
}

echo json_encode($data);

?>
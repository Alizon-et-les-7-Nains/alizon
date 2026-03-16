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

$stats = [];

if (isset($_GET['category'])) {
    $statsSTMT = $pdo->prepare(file_get_contents(ROOT . '/queries/backoffice/stats/statsByCategory.sql'));
    $statsSTMT->execute([urldecode($_GET['category']), $_SESSION['id']]);
    $stats = $statsSTMT->fetchAll(PDO::FETCH_ASSOC);
} else if (isset($_GET['product'])) {
    $statsSTMT = $pdo->prepare(file_get_contents(ROOT . '/queries/backoffice/stats/statsByProduct.sql'));
    $statsSTMT->execute([urldecode($_GET['product']), $_SESSION['id']]);
    $stats = $statsSTMT->fetchAll(PDO::FETCH_ASSOC);
} else {
    $statsSTMT = $pdo->prepare(file_get_contents(ROOT . '/queries/backoffice/stats/stats.sql'));
    $statsSTMT->execute([$_SESSION['id']]);
    $stats = $statsSTMT->fetchAll(PDO::FETCH_ASSOC);
}

if ($stats === false) {
    http_response_code(500);
    echo json_encode(['500 error' => 'Impossible de résoudre les données']);
    exit();
}

echo json_encode($stats);

?>
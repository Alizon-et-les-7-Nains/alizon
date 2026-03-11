<?php

header('Content-Type: application/json');

define('ROOT', $_SERVER['DOCUMENT_ROOT']);

require_once ROOT . '/controllers/pdo.php';

$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

session_start();
if (!isset($_SESSION['id'])) {
    http_response_code(401);
    echo json_encode(['401 error' => 'Utilisateur non authentifié']);
    exit();
}

$stats = [];

if (isset($_GET['category'])) {

} else if (isset($_GET['product'])) {

} else {
    $statsSTMT = $pdo->prepare(file_get_contents(ROOT . '/queries/backoffice/stats/stats.sql'));
    $statsSTMT->execute([$_SESSION['id']]);
    $stats = $statsSTMT->fetchAll(PDO::FETCH_ASSOC);
}

if (!$stats) {
    http_response_code(500);
    echo json_encode(['500 error' => 'Impossible de résoudre les données']);
    exit();
}

echo json_encode($stats);

?>
<?php

require_once 'pdo.php';

header('Content-Type: application/json');

$data;

$action = $_GET['action'] ?? null;

function authentify(): void {
    session_start();
    if (!isset($_SESSION['id'])) {
        http_response_code(401);
        echo json_encode(['401 error' => 'Utilisateur non authentifié']);
        exit();
    }
}

switch ($action) {
    case 'stats':
        authentify();
        $statsSTMT = $pdo->prepare(file_get_contents('../queries/backoffice/stats/stats.sql'));
        $statsSTMT->execute([$_SESSION['id']]);
        $data = $statsSTMT->fetchAll(PDO::FETCH_ASSOC);
        break;

    default:
        http_response_code(400);
        $data = ['400 error' => 'Action API non reconnue'];
        break;
}

echo json_encode($data);

?>
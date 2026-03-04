<?php
session_start();
require_once "../../controllers/pdo.php";
require_once "../../vendor/autoload.php";

use OTPHP\TOTP;

header('Content-Type: application/json');

// Récupérer les données JSON envoyées
$data = json_decode(file_get_contents("php://input"), true);

$otp = $data['otp'] ?? '';



$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
    echo json_encode(["success" => false]);
    exit();
}

// Récupérer la clé secrète en base
$stmt = $pdo->prepare("SELECT otp_secret FROM _client WHERE idClient = ?");
$stmt->execute([$user_id]);
$secret = $stmt->fetchColumn();

$totp = TOTP::create($secret);

if ($totp->verify($otp)) {

    $_SESSION['tmp_usr'] = $user_id;

    echo json_encode(["success" => true]);

} else {
    echo json_encode(["success" => false]);
}
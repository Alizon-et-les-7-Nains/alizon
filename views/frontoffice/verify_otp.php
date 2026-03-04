<?php
session_start();
require_once "../../controllers/pdo.php";
require_once "../../vendor/autoload.php";

use OTPHP\TOTP;

header('Content-Type: application/json');

function chiffrement($data) {
    $key = 'la_super_cle_secrete';
    $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
    $encrypted = openssl_encrypt($data, 'aes-256-cbc', $key, 0, $iv);
    return base64_encode($iv . $encrypted);
}

function dechiffrement($data) {
    $key = 'la_super_cle_secrete';
    $data = base64_decode($data);
    $iv_length = openssl_cipher_iv_length('aes-256-cbc');
    $iv = substr($data, 0, $iv_length);
    $encrypted = substr($data, $iv_length);
    return openssl_decrypt($encrypted, 'aes-256-cbc', $key, 0, $iv);
}

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
$secret = dechiffrement($secret);

$totp = TOTP::create($secret);

$getsecret = $totp->getSecret();
var_dump($getsecret);
var_dump($otp);
var_dump($totp->now());
var_dump($totp->verify($otp));
var_dump($secret)

if ($totp->verify($otp)) {

    $_SESSION['tmp_usr'] = $user_id;

    echo json_encode(["success" => true]);

} else {
    echo json_encode(["success" => false]);
}
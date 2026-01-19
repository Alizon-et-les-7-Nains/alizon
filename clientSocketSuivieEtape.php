<?php
session_start();
require_once __DIR__ . "/controllers/pdo.php";

$idCommande = intval($_GET['idCommande']);

$host = 'web';
$port = 8080;
$socket = @fsockopen($host, $port, $errno, $errstr, 5);
if (!$socket) exit("Erreur socket: $errstr");

fwrite($socket, "AUTH admin e10adc3949ba59abbe56e057f20f883e\n");
fgets($socket, 1024); // ignorer la réponse AUTH

$stmt = $pdo->prepare("SELECT noBordereau FROM _commande WHERE idCommande = :idCommande");
$stmt->execute([":idCommande" => $idCommande]);
$bordereau = $stmt->fetchColumn();

fwrite($socket, "STATUS $bordereau\n");
$status = fgets($socket, 4096);
$status_response = explode("|", trim($status));
$photo_size = intval($status_response[7]);

$photo = '';
if ($photo_size > 0) {
    $read = 0;
    while ($read < $photo_size) {
        $chunk = fread($socket, min(8192, $photo_size - $read));
        if ($chunk === false || $chunk === '') break;
        $photo .= $chunk;
        $read += strlen($chunk);
    }
}

if ($photo_size > 0) {
    header("Content-Type: image/jpeg");
    header("Content-Length: " . strlen($photo));
    echo $photo;
}

fclose($socket);
exit;
?>
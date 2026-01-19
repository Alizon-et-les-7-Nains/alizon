<?php
session_start();
require_once __DIR__ . "/controllers/pdo.php";

$server_host = 'web';
$server_port = 8080;
$timeout = 10;

$idCommande = intval($_GET['idCommande']);
$image_path = null;


$sql = "SELECT noBordereau FROM _commande WHERE idCommande = :idCommande";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([":idCommande" => $idCommande]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
$bordereau = $result['noBordereau'];

$socket = fsockopen($server_host, $server_port, $errno, $errstr, $timeout);
stream_set_timeout($socket, $timeout);

$username = 'alizon';
$password_md5 = 'e10adc3949ba59abbe56e057f20f883e';

fwrite($socket, "AUTH $username $password_md5\n");
fgets($socket, 1024);

fwrite($socket, "STATUS $bordereau\n");
fgets($socket, 2048);

$remaining = stream_get_contents($socket);
fclose($socket);

// Nettoyer et sauvegarder l'image
$remaining = trim(preg_replace('/\A\r?\n/', '', $remaining));
if ($remaining && $remaining !== 'null') {
    $image_path = tempnam(sys_get_tempdir(), 'delivraptor') . '.jpg';
    file_put_contents($image_path, rtrim($remaining, "\n"));
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title></title>
</head>
<body>
    <img src="data:image/jpeg;base64,<?= base64_encode(file_get_contents($image_path)) ?>" alt="Photo de livraison">
</body>
</html>

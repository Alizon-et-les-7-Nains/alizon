<?php
session_start();
require_once __DIR__ . "/controllers/pdo.php";

$idCommande = intval($_GET['idCommande']);

$host = 'web';
$port = 8080;

$socket = @fsockopen($host, $port, $errno, $errstr, 5);

if (!$socket) {
    echo "ERREUR: Impossible de se connecter à $host:$port\n";
    exit(1);
}

// Authentification
fwrite($socket, "AUTH admin e10adc3949ba59abbe56e057f20f883e\n");
$auth_response = fgets($socket, 1024);

// Récupérer le bordereau
$sql = "SELECT noBordereau FROM _commande WHERE idCommande = :idCommande";
$stmt = $pdo->prepare($sql);
$stmt->execute([":idCommande" => $idCommande]);
$result = $stmt->fetch(PDO::FETCH_ASSOC);
$bordereau = $result['noBordereau'];

// Envoyer STATUS
fwrite($socket, "STATUS $bordereau\n");

// 1. LIRE TOUTE LA LIGNE TEXTE JUSQU'AU 7E PIPE
$text_line = '';
$pipe_count = 0;

while (!feof($socket)) {
    $char = fgetc($socket);
    if ($char === false) break;
    
    $text_line .= $char;
    
    if ($char === '|') {
        $pipe_count++;
    }
    
    if ($pipe_count === 7) {
        break;
    }
}

//faire un tableau avec les parties
$status_parts = explode("|", rtrim($text_line, '|'));

$bordereau_recu = $status_parts[0];
$commande = $status_parts[1];
$destination = $status_parts[2];
$localisation = $status_parts[3];
$etape = $status_parts[4];
$date_etape = $status_parts[5];

$image_data = '';
if ($etape == '9' && $typeLivraison === 'ABSENT') {
    $typeLivraison = $status_parts[6];
    
    // Lire jusqu'au \n final
    while (!feof($socket)) {
        $chunk = fread($socket, 8192);
        if ($chunk === false) break;
        
        $image_data .= $chunk;
        
        // Vérifier si on a atteint le \n final
        if (substr($image_data, -1) === "\n") {
            $image_data = substr($image_data, 0, -1); // Retirer le \n
            break;
        }
    }
    
    // Vérifier que ce n'est pas juste "null"
    if ($image_data !== 'null' && strlen($image_data) > 10) {
        $_SESSION['photo'] = base64_encode($image_data);
    } else {
        unset($_SESSION['photo']);
    }
} else {
    // Lire "null\n"
    $null_response = fgets($socket, 10);
    unset($_SESSION['photo']);
}

// Mettre à jour la base
$sql = "UPDATE _commande SET etape = :etape WHERE idCommande = :idCommande";
$stmt = $pdo->prepare($sql);
$stmt->execute([":etape" => $etape, ":idCommande" => $idCommande]);

$_SESSION['typeLivraison'] = $typeLivraison;

fclose($socket);

header('Location: views/frontoffice/commandes.php?idCommande=' . $idCommande);
exit;
?>
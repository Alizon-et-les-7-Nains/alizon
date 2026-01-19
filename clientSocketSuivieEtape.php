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

// 1. LIRE LA PARTIE TEXTE (jusqu'au dernier pipe |)
$text_data = '';
while (!feof($socket)) {
    $char = fgetc($socket);
    if ($char === false) break;
    
    $text_data .= $char;
    
    // On s'arrête au dernier pipe
    if ($char === '|') {
        break;
    }
}

// Parser les données texte
$status_parts = explode("|", rtrim($text_data, '|'));

$etape = $status_parts[4];
$typeLivraison = $status_parts[6];

var_dump("Étape: $etape, Type: $typeLivraison");

// 2. LIRE LA PARTIE IMAGE/NULL
$image_data = '';

if ($etape == 9 && $typeLivraison === 'ABSENT') {
    // Lire jusqu'au \n final (l'image peut être grosse)
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
    
    // Sauvegarder l'image
    if (strlen($image_data) > 4) { // Plus que "null"
        $_SESSION['photo'] = base64_encode($image_data);
        var_dump("Image reçue: " . strlen($image_data) . " octets");
    }
} else {
    // Lire "null\n"
    $null_response = fgets($socket, 10);
    var_dump("Pas d'image: $null_response");
}

// Mettre à jour la base
$sql = "UPDATE _commande SET etape = :etape WHERE idCommande = :idCommande";
$stmt = $pdo->prepare($sql);
$stmt->execute([":etape" => $etape, ":idCommande" => $idCommande]);

$_SESSION['typeLivraison'] = $typeLivraison;

fclose($socket);

//header('Location: views/frontoffice/commandes.php?idCommande=' . $idCommande);
exit;
?>
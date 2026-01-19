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

// ===== NOUVELLE MÉTHODE DE LECTURE =====

// 1. LIRE TOUTE LA LIGNE TEXTE (qui se termine par le dernier |)
// Format: bordereau|commande|dest|loc|etape|date|type|
$text_line = '';
$pipe_count = 0;

while (!feof($socket)) {
    $char = fgetc($socket);
    if ($char === false) break;
    
    $text_line .= $char;
    
    // Compter les pipes
    if ($char === '|') {
        $pipe_count++;
    }
    
    // On attend 7 pipes (bordereau|cmd|dest|loc|etape|date|type|)
    if ($pipe_count === 7) {
        break;
    }
}

var_dump("Ligne texte reçue: $text_line");

// Parser les données texte
$status_parts = explode("|", rtrim($text_line, '|'));

// Vérifier qu'on a bien toutes les parties
if (count($status_parts) < 7) {
    echo "ERREUR: Réponse incomplète du serveur\n";
    var_dump($status_parts);
    fclose($socket);
    exit(1);
}

$bordereau_recu = $status_parts[0];
$commande = $status_parts[1];
$destination = $status_parts[2];
$localisation = $status_parts[3];
$etape = $status_parts[4];
$date_etape = $status_parts[5];
$typeLivraison = $status_parts[6];

var_dump("Étape: $etape, Type: $typeLivraison");

// 2. LIRE LA PARTIE IMAGE/NULL
$image_data = '';

if ($etape == '9' && $typeLivraison === 'ABSENT') {
    var_dump("Attente de l'image...");
    
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
        var_dump("Image reçue: " . strlen($image_data) . " octets");
    } else {
        unset($_SESSION['photo']);
        var_dump("Pas d'image (reçu: $image_data)");
    }
} else {
    // Lire "null\n"
    $null_response = fgets($socket, 10);
    unset($_SESSION['photo']);
    var_dump("Pas d'image attendue: $null_response");
}

// Mettre à jour la base
$sql = "UPDATE _commande SET etape = :etape WHERE idCommande = :idCommande";
$stmt = $pdo->prepare($sql);
$stmt->execute([":etape" => $etape, ":idCommande" => $idCommande]);

$_SESSION['typeLivraison'] = $typeLivraison;

var_dump("Mise à jour réussie");

fclose($socket);

header('Location: views/frontoffice/commandes.php?idCommande=' . $idCommande);
exit;
?>
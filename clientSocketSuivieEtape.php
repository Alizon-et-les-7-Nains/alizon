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
$bordereau = trim($result['noBordereau']);

// Envoyer STATUS
fwrite($socket, "STATUS $bordereau\n");
// LIRE LA LIGNE DE STATUT COMPLÈTE (jusqu'au \n)
$text_line = fread($socket, 4096);

if ($text_line === false) {
    fclose($socket);
    echo "ERREUR: Pas de réponse du serveur\n";
    exit(1);
}

// Parser les données (enlever le \n final)
$status_parts = explode("|", rtrim($text_line));

if (count($status_parts) < 7) {
    fclose($socket);
    echo "ERREUR: Format de réponse invalide\n";
    exit(1);
}

$bordereau_recu = $status_parts[0];
$commande = $status_parts[1];
$destination = $status_parts[2];
$localisation = $status_parts[3];
$etape = $status_parts[4];
$date_etape = $status_parts[5];
$typeLivraison = $status_parts[6] ?? '';

// LIRE LA PARTIE IMAGE/NULL
if ($etape == '9' && $typeLivraison === 'ABSENT') {
    $image_data = '';
    
    // Lire jusqu'au \n final
    while (!feof($socket)) {
        $chunk = fread($socket, 32768);
        
        if ($chunk === false || $chunk === '') {
            break;
        }
        
        $image_data .= $chunk;
        
        // Chercher le \n final
        if (strpos($image_data, "\n") !== false) {
            // Retirer le \n
            $image_data = substr($image_data, 0, strpos($image_data, "\n"));
            break;
        }
    }
    
    // Vérifier validité
    if ($image_data !== '' && $image_data !== 'null' && strlen($image_data) > 10) {
        $_SESSION['photo'] = base64_encode($image_data);
    } else {
        unset($_SESSION['photo']);
    }
} else {
    // Lire "null\n"
    fgets($socket, 10);
    unset($_SESSION['photo']);
}

// Mettre à jour la base
$sql = "UPDATE _commande SET etape = :etape WHERE idCommande = :idCommande";
$stmt = $pdo->prepare($sql);
$stmt->execute([":etape" => $etape, ":idCommande" => $idCommande]);

$_SESSION['typeLivraison'] = $typeLivraison;

// Fermer proprement
fwrite($socket, "QUIT\n");
fclose($socket);

header('Location: views/frontoffice/commandes.php?idCommande=' . $idCommande);
exit;
?>
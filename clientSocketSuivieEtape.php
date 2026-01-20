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

// Passer en mode binaire (important pour Windows, sans effet sur Linux)
stream_set_blocking($socket, true);

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
$text_line = fgets($socket, 4096);

if ($text_line === false) {
    fclose($socket);
    echo "ERREUR: Pas de réponse du serveur\n";
    exit(1);
}

// Parser les données
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
    $buffer = '';
    
    // Stratégie: lire par blocs jusqu'à trouver le \n final
    // L'image binaire peut contenir des \n, donc on ne peut pas utiliser fgets
    
    // D'abord, lire jusqu'à trouver soit "null\n" soit des données binaires + \n final
    $first_bytes = fread($socket, 4);
    
    if ($first_bytes === 'null') {
        // Consommer le \n
        fread($socket, 1);
        unset($_SESSION['photo']);
    } else {
        // C'est le début de l'image binaire
        $image_data = $first_bytes;
        
        // Lire le reste de l'image
        // Stratégie: lire jusqu'à timeout ou fermeture
        stream_set_timeout($socket, 0, 100000); // 100ms timeout
        
        while (!feof($socket)) {
            $chunk = fread($socket, 8192);
            
            if ($chunk === false || $chunk === '') {
                break;
            }
            
            $image_data .= $chunk;
            
            // Vérifier si on a le \n final
            // L'image se termine par \n envoyé par le serveur
            if (substr($image_data, -1) === "\n") {
                // Retirer le \n final
                $image_data = substr($image_data, 0, -1);
                break;
            }
            
            // Sécurité: si trop gros, arrêter
            if (strlen($image_data) > 10000000) { // 10 MB max
                break;
            }
        }
        
        // Vérifier validité
        if (strlen($image_data) > 10) {
            $_SESSION['photo'] = base64_encode($image_data);
            error_log("Image reçue: " . strlen($image_data) . " octets");
        } else {
            unset($_SESSION['photo']);
        }
    }
} else {
    // Lire "null\n"
    fread($socket, 5); // "null\n"
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
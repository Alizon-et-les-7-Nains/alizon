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

// OPTIMISATION 1: Définir un timeout de lecture plus court
stream_set_timeout($socket, 5);

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
fflush($socket);

// OPTIMISATION 2: Lire la ligne complète d'un coup (limite à 4096 car contient 7 champs)
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

// OPTIMISATION 3: Gestion optimisée de l'image
$image_data = '';

if ($etape == '9' && $typeLivraison === 'ABSENT') {
    // MÉTHODE OPTIMISÉE: Lire par gros chunks jusqu'au \n final
    $buffer = '';
    $chunk_size = 65536; // 64 KB chunks pour performance maximale
    
    while (!feof($socket)) {
        $chunk = fread($socket, $chunk_size);
        
        if ($chunk === false) {
            break;
        }
        
        $buffer .= $chunk;
        
        // Vérifier si on a le \n final
        $newline_pos = strpos($buffer, "\n");
        if ($newline_pos !== false) {
            // On a trouvé le \n, extraire seulement la partie image
            $image_data = substr($buffer, 0, $newline_pos);
            break;
        }
    }
    
    // Vérifier que ce n'est pas "null"
    if ($image_data !== 'null' && strlen($image_data) > 10) {
        $_SESSION['photo'] = base64_encode($image_data);
    } else {
        unset($_SESSION['photo']);
    }
} else {
    // Lire la réponse "null\n"
    $null_response = fgets($socket, 10);
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
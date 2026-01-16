<?php
require_once __DIR__ . "/controllers/pdo.php";

$idCommande = intval($_GET['idCommande']);

function send_command($socket, $command)
{
    // Envoi de la commande
    fwrite($socket, $command . "\n");

    // Lecture de la réponse
    $response = '';
    while (!feof($socket)) {
        $response .= fgets($socket, 1024);
        if (strpos($response, "\n") !== false) {
            break;
        }
    }

    return trim($response);
}

// Utilisation
$host = 'web';
$port = 8080;

// Connexion persistante
$socket = @fsockopen($host, $port, $errno, $errstr, 5);

if (!$socket) {
    echo "ERREUR: Impossible de se connecter à $host:$port\n";
    echo "Code: $errno - Message: $errstr\n";
    exit(1);
}

// Test 1: Authentification
//echo "Test AUTH:\n";
$auth_response = send_command($socket, "AUTH admin e10adc3949ba59abbe56e057f20f883e");
//echo "Réponse: $auth_response\n\n";

$sql = "SELECT noBordereau FROM _commande WHERE idCommande = :idCommande";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([":idCommande" => $idCommande]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

$bordereau = $result['noBordereau'];

// Extraire le numéro de bordereau
// Test 3: Consultation
//echo "Test STATUS:\n";
$status_response = send_command($socket, "STATUS $bordereau");
$sql = "UPDATE _commande SET etape = :etape WHERE idCommande = :idCommande";
$stmt = $pdo->prepare($sql);
$stmt->execute([":etape" => $status_response, ":idCommande" => $idCommande]);
var_dump($status_response);
//echo "Réponse: $status_response\n\n";


// Test 4: HELP
//echo "Test HELP:\n";
$help_response = send_command($socket, "HELP");
//echo $help_response;

// Fermeture de la connexion
fclose($socket);

header('Location: views/frontoffice/commandes.php');
exit;
?>
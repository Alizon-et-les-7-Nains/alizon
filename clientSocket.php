<?php
require_once __DIR__ . "/controllers/pdo.php";

$tabIdDestination = $_SESSION['tabIdDestination'];
// auto_test.php
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

// Test 2: Création
//echo "Test CREATE:\n";
$create_response = send_command($socket, "CREATE " . $tabIdDestination[0]["idCommande"] . " " . $tabIdDestination[0]["destination"] . " " . $tabIdDestination[0]["destination"]);
$sql = "UPDATE _commande SET bordereau = :noBordereau WHERE idCommande = :idCommande";
$stmt = $pdo->prepare($sql);
$stmt->execute([":noBordereau" => $create_response, ":idCommande" => $tabIdDestination[0]["idCommande"]]);
//echo "Réponse: $create_response\n\n";

// Extraire le numéro de bordereau
if (preg_match('/BORDEREAU (\d+)/', $create_response, $matches)) {
    $bordereau = $matches[1];

    // Test 3: Consultation
    //echo "Test STATUS:\n";
    $status_response = send_command($socket, "STATUS $bordereau");
    //echo "Réponse: $status_response\n\n";
}

// Test 4: HELP
//echo "Test HELP:\n";
$help_response = send_command($socket, "HELP");
//echo $help_response;

// Fermeture de la connexion
fclose($socket);

header('Location: views/frontoffice/commandes.php');
exit;
?>
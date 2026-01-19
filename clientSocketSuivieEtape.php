<?php
session_start();
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
$status_response = explode("|", $status_response);
$sql = "UPDATE _commande SET etape = :etape WHERE idCommande = :idCommande";
$stmt = $pdo->prepare($sql);
$stmt->execute([":etape" => $status_response[4], ":idCommande" => $idCommande]);

$photo = $status_response[7];
$typeLivraison = $status_response[6];
$etape = $status_response[4];
$_SESSION['typeLivraison'] = $typeLivraison;


if ($etape == 9 && $typeLivraison === 'ABSENT') {

    if ($photo != null) {
        $imageData = '';
        while (!feof($socket)) {
            $chunk = fread($socket, 8192);
            if ($chunk === false || $chunk === '') break;
            $imageData .= $chunk;
        }
        $_SESSION['photo'] = $imageData;
        file_put_contents('test_image.jpg', $_SESSION['photo']);
    }
} else {
    // Supprimer la session photo si autre chose que ABSENT
    unset($_SESSION['photo']);
}

//echo "Réponse: $status_response\n\n";


// Test 4: HELP
//echo "Test HELP:\n";
$help_response = send_command($socket, "HELP");
//echo $help_response;

// Fermeture de la connexion
fclose($socket);

header('Location: views/frontoffice/commandes.php?idCommande=' . $idCommande);
exit;
?>
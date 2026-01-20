<?php
require_once __DIR__ . "/controllers/pdo.php";

$tabIdDestination = $_SESSION['tabIdDestination'];
// auto_test.php

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
fwrite($socket, "AUTH admin e10adc3949ba59abbe56e057f20f883e");
$auth_response = fgets($socket, 1024);
//echo "Réponse: $auth_response\n\n";

// Test 2: Création
//echo "Test CREATE:\n";

fwrite($socket, "CREATE " . $tabIdDestination[0]["idCommande"] . " " . $tabIdDestination[0]["destination"]);
$create_response = fgets($socket, 1024);
$sql = "UPDATE _commande SET noBordereau = :noBordereau WHERE idCommande = :idCommande";
$stmt = $pdo->prepare($sql);
$stmt->execute([":noBordereau" => $create_response, ":idCommande" => $tabIdDestination[0]["idCommande"]]);
//echo "Réponse: $create_response\n\n";

fwrite($socket, "QUIT");
// Fermeture de la connexion
fclose($socket);

header('Location: views/frontoffice/commandes.php');
exit;

?>
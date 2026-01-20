<?php
require_once __DIR__ . '/../../controllers/pdo.php';

//On récupère le tableau stocker dans la session avec l'id et la destination de notre commande
$tabIdDestination = $_SESSION['tabIdDestination'];

$host = 'web';
$port = 8080;

// Connexion au socket
$socket = @fsockopen($host, $port, $errno, $errstr, 5);

if (!$socket) {
    echo "ERREUR: Impossible de se connecter à $host:$port\n";
    echo "Code: $errno - Message: $errstr\n";
    exit(1);
}

//Authentification avec le mdp hashé d'alizon
fwrite($socket, "AUTH admin e10adc3949ba59abbe56e057f20f883e");
$auth_response = fgets($socket, 1024);

//Création d'un numéro de bordereau avec notre numéro de commande et notre destination
fwrite($socket, "CREATE " . $tabIdDestination[0]["idCommande"] . " " . $tabIdDestination[0]["destination"]);
$create_response = fgets($socket, 1024);
//On reçoit une réponse du service qui nous renvoie un numéro de bordereau unique associé à notre commande 
//On ajoute ce dernier dans notre table _commande
$sql = "UPDATE _commande SET noBordereau = :noBordereau WHERE idCommande = :idCommande";
$stmt = $pdo->prepare($sql);
$stmt->execute([":noBordereau" => $create_response, ":idCommande" => $tabIdDestination[0]["idCommande"]]);

// Fermeture de la connexion
fwrite($socket, "QUIT");
fclose($socket);

header('Location: ../../views/frontoffice/commandes.php');
exit;

?>
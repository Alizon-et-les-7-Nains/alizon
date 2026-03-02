<?php 
require_once 'pdo.php';
session_start();  

//Récupération de l'id de l'utilisateur
$id_client = $_POST['id_client'];

// Suppression de la réponse à un avis
$stmt = $pdo->prepare("DELETE FROM _client WHERE idClient = :idClient");

try{
    $stmt->execute([
        ':idClient' => $idClient
    ]);
}
catch(PDOException $e){
    echo "Erreur SQL : " . $e->getMessage();
}

session_unset();
session_destroy();
setcookie(session_name(), '', time() - 3600, '/');   
header('Location: ../views/frontoffice/accueilDeconnecte.php');
exit();

?>
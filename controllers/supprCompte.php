<?php 
require_once 'pdo.php';
session_start();  

//Récupération de l'id de l'utilisateur
$id_client = $_POST['id_client'];

try{
    // Anonymiser les données
    $stmt = $pdo->prepare("UPDATE _client SET email = NULL, prenom = NULL, dateNaissance = NULL, nom = NULL, mdp = NULL, noTelephone = NULL, pseudo = 'Anonyme' WHERE idClient = :idClient");
    $stmt->execute([
        ':idClient' => $id_client
    ]);

    // Supprimer les données qui ne serviront plus (ex: notification, panier acutel...)
    $stmt = $pdo->prepare("DELETE FROM _notification WHERE idClient = :idClient");
    $stmt->execute([
        ':idClient' => $id_client
    ]);

}
catch(PDOException $e){
    error_log("Erreur SQL : " . $e->getMessage());
}

session_unset();
session_destroy();
setcookie(session_name(), '', time() - 3600, '/');   
header('Location: ../views/frontoffice/accueilDeconnecte.php');
exit();

?>
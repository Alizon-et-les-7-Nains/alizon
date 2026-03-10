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

    // Supprimer / anonymiser les données qui ne serviront plus (ex: notification, photo de profil, adresses...)
    $stmt = $pdo->prepare("DELETE FROM _notification WHERE idClient = :idClient");
    $stmt->execute([
        ':idClient' => $id_client
    ]);

    $stmt = $pdo->prepare("DELETE FROM _adresseLivraison WHERE idClient = :idClient");
    $stmt->execute([
        ':idClient' => $id_client
    ]);

    $stmt = $pdo->prepare("UPDATE _adresseClient SET adresse = NULL, region = NULL, codePostal = NULL, ville = NULL, pays = NULL, complementAdresse = NULL WHERE idClient = :idClient");
    $stmt->execute([
        ':idClient' => $id_client
    ]);

    // Gestion de l'affichage de la photo de profil
    $photoProfilPath = "/images/photoProfilClient/photo_profil" . $id_client;
    $extensionsPossibles = ['png', 'jpg', 'jpeg', 'webp', 'svg'];
    $photoProfilUrl = "../../public/images/profil.png";

    foreach ($extensionsPossibles as $ext) {
        $cheminComplet = "/var/www/html" . $photoProfilPath . "." . $ext;
        if (file_exists($cheminComplet)) {
            unlink($cheminComplet);
            break;
        }
    }

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
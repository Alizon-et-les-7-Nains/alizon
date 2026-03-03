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

    // Supprimer les données qui ne serviront plus (ex: notification, photo de profil...)
    $stmt = $pdo->prepare("DELETE FROM _notification WHERE idClient = :idClient");
    $stmt->execute([
        ':idClient' => $id_client
    ]);

    // Gestion de l'affichage de la photo de profil
    $photoProfilPath = "/images/photoProfilClient/photo_profil" . $avis['idClient'];
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
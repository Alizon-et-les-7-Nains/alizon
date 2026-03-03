<?php 
require_once 'pdo.php';
session_start();  

//Récupération de l'id de l'utilisateur
$id_client = $_POST['id_client'];

try{
    // On commence par les commandes car elles bloquent souvent le reste
    // Supprimer les commandes du compte client
    $stmt = $pdo->prepare("DELETE FROM _commande WHERE idPanier IN (SELECT idPanier FROM _panier WHERE idClient = :idClient)");
    $stmt->execute([':idClient' => $id_client]);

    // Supprimer le panier du compte client
    $stmt = $pdo->prepare("DELETE FROM _panier WHERE idClient = :idClient");
    $stmt->execute([
        ':idClient' => $id_client
    ]);

    // Supprimer les notifications attachés au compte client
    $stmt = $pdo->prepare("DELETE FROM _notification WHERE idClient = :idClient");
    $stmt->execute([
        ':idClient' => $id_client
    ]);

    // Supprimer les signalements attachés au compte client
    $stmt = $pdo->prepare("DELETE FROM _signalement WHERE idClientSignale = :idClient");
    $stmt->execute([
        ':idClient' => $id_client
    ]);

    // Supprimer l'adresse de livraison et facturation du client
    $stmt = $pdo->prepare("DELETE FROM _adresseLivraison WHERE idClient = :idClient");
    $stmt->execute([
        ':idClient' => $id_client
    ]);

    // Remplacer les avis par le compte anonyme
    $stmt = $pdo->prepare("UPDATE _avis SET idClient = 11111 WHERE idClient = :idClient");
    $stmt->execute([
        ':idClient' => $id_client
    ]);

    // Remplacer les réponses à un avis lié au compte client
    $stmt = $pdo->prepare("UPDATE _reponseAvis SET idClient = 11111 WHERE idClient = :idClient");
    $stmt->execute([
        ':idClient' => $id_client
    ]);
    
    // Remplacer les images attachés aux avis par le compte anonyme
    $stmt = $pdo->prepare("UPDATE _imageAvis SET idClient = 11111 WHERE idClient = :idClient");
    $stmt->execute([
        ':idClient' => $id_client
    ]);

    // En dernier, on supprime le client maintenant que plus rien ne dépend de lui
    // Supprimer le compte client
    $stmt = $pdo->prepare("DELETE FROM _client WHERE idClient = :idClient");
    $stmt->execute([
        ':idClient' => $id_client
    ]);
}
catch(PDOException $e){
    echo "Erreur SQL : " . $e->getMessage();
    exit(); // Ajouté pour éviter l'erreur de headers déjà envoyés en cas de crash SQL
}

session_unset();
session_destroy();
setcookie(session_name(), '', time() - 3600, '/');   
header('Location: ../views/frontoffice/accueilDeconnecte.php');
exit();

?>
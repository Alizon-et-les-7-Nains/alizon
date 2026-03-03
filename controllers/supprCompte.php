<?php 
require_once 'pdo.php';
session_start();  

//Récupération de l'id de l'utilisateur
$id_client = $_POST['id_client'];

try{
    try {
        // Supprimer l'adresse de livraison et facturation du client
        $stmt = $pdo->prepare("DELETE FROM _adresseLivraison WHERE idClient = :idClient");
        $stmt->execute([
            ':idClient' => $id_client
        ]);
    } catch (PDOException $e) {
        echo $e + "\n\n\nerreur suprresion adresse livraison / facturation client";
    }

    try {
        // Remplacer les avis par le compte anonyme
        $stmt = $pdo->prepare("UPDATE _avis SET idClient = 11111 WHERE idClient = :idClient");
        $stmt->execute([
            ':idClient' => $id_client
        ]);
    } catch (PDOException $e) {
        echo $e + "\n\n\nerreur suprresion avis";
    }

    try {
        // Remplacer les réponses à un avis lié au compte client
        $stmt = $pdo->prepare("UPDATE _reponseAvis SET idClient = 11111 WHERE idClient = :idClient");
        $stmt->execute([
            ':idClient' => $id_client
        ]);
    } catch (PDOException $e) {
        echo $e + "\n\n\nerreur suprresion reponses";
    }
    
    try {
        // Remplacer les images attachés aux avis par le compte anonyme
        $stmt = $pdo->prepare("UPDATE _imageAvis SET idClient = 11111 WHERE idClient = :idClient");
        $stmt->execute([
            ':idClient' => $id_client
        ]);
    } catch (PDOException $e) {
        echo $e + "\n\n\nerreur suprresion reponses";
    }

    try {
        // Supprimer les notifications attachés au compte client
        $stmt = $pdo->prepare("DELETE FROM _notification WHERE idClient = :idClient");
        $stmt->execute([
            ':idClient' => $id_client
        ]);
    } catch (PDOException $e) {
        echo $e + "\n\n\nerreur suprresion notifications";
    }

    try {
        // Supprimer les signalements attachés au compte client
        $stmt = $pdo->prepare("UPDATE _signalement SET idClientSignale = 11111 WHERE idClientSignale = :idClient");
        $stmt->execute([
            ':idClient' => $id_client
        ]);
    } catch (PDOException $e) {
        echo $e + "\n\n\nerreur suprresion signalement";
    }

    try {
        // Supprimer les commandes du compte client
        $stmt = $pdo->prepare("SELECT idPanier FROM _panier WHERE idClient = :idClient");
        $stmt->execute([
            ':idClient' => $id_client
        ]);
        $paniers = $stmt->fetchAll(PDO::FETCH_COLUMN);
        foreach ($paniers as $idPanier) {
            $stmt = $pdo->prepare("DELETE FROM _facture WHERE idCommande IN (SELECT idCommande FROM _commande WHERE idPanier = :idPanier)");
            $stmt->execute([':idPanier' => $idPanier]);
            $stmt = $pdo->prepare("DELETE FROM _contient WHERE idCommande IN (SELECT idCommande FROM _commande WHERE idPanier = :idPanier)");
            $stmt->execute([':idPanier' => $idPanier]);
            $stmt = $pdo->prepare("DELETE FROM _commande WHERE idPanier = :idPanier");
            $stmt->execute([':idPanier' => $idPanier]);
        }
    } catch (PDOException $e) {
        echo $e + "\n\n\nerreur suprresion commandes";
    }

    try {
        // Supprimer le panier du compte client
        $stmt = $pdo->prepare("DELETE FROM _produitAuPanier WHERE idPanier IN (SELECT idPanier FROM _panier WHERE idClient = :idClient)");
        $stmt->execute([':idClient' => $id_client]);
        $stmt = $pdo->prepare("DELETE FROM _panier WHERE idClient = :idClient");
        $stmt->execute([
            ':idClient' => $id_client
        ]);
    } catch (PDOException $e) {
        echo $e + "\n\n\nerreur suprresion panier";
    }

    try {
        // Supprimer le compte client
        $stmt = $pdo->prepare("DELETE FROM _client WHERE idClient = :idClient");
        $stmt->execute([
            ':idClient' => $id_client
        ]);
    } catch (PDOException $e) {
        echo $e + "\n\n\nerreur suprresion compte";
    }

    session_unset();
    session_destroy();
    setcookie(session_name(), '', time() - 3600, '/');   
    header('Location: ../views/frontoffice/accueilDeconnecte.php');
    exit();
}
catch(PDOException $e){
    echo "Erreur SQL : " . $e->getMessage();
}

?>
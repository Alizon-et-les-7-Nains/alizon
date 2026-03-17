<?php
// Ce fichier est appelé lorsqu'on veut connaître le suivi de notre commande
session_start();
require_once __DIR__ . '/../../controllers/pdo.php';

// Dans l'url de la pop up on récupère l'id de la commande
if (!isset($_GET['idCommande'])) {
    error_log("clientSocketSuivieEtape: idCommande manquant");
    header('Location: ../../views/frontoffice/commandes.php');
    exit;
}

$idCommande = intval($_GET['idCommande']);

// Vérifier que la commande existe
$checkCommande = $pdo->prepare("SELECT idCommande, idClient FROM _commande WHERE idCommande = ?");
$checkCommande->execute([$idCommande]);
$commandeData = $checkCommande->fetch(PDO::FETCH_ASSOC);

if (!$commandeData) {
    error_log("clientSocketSuivieEtape: Commande $idCommande non trouvée");
    header('Location: ../../views/frontoffice/commandes.php');
    exit;
}

$host = 'web';
$port = 8080;

// Connexion au socket
$socket = @fsockopen($host, $port, $errno, $errstr, 5);

if (!$socket) {
    error_log("ERREUR socket: $errno - $errstr");
    $_SESSION['error_message'] = "Impossible de se connecter au service de suivi";
    header('Location: ../../views/frontoffice/commandes.php?idCommande=' . $idCommande);
    exit;
}

// Mode bloquant pour attendre la réponse du serveur avant de continuer
stream_set_blocking($socket, true);

// Authentification
fwrite($socket, "AUTH admin e10adc3949ba59abbe56e057f20f883e\n");
$auth_response = fgets($socket, 1024);

// On récupère le numéro de bordereau dans la table _commande 
$sql = "SELECT noBordereau FROM _commande WHERE idCommande = :idCommande";
$stmt = $pdo->prepare($sql);
$stmt->execute([":idCommande" => $idCommande]);
$result = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$result || empty($result['noBordereau'])) {
    error_log("clientSocketSuivieEtape: Pas de bordereau pour la commande $idCommande");
    fclose($socket);
    $_SESSION['error_message'] = "Aucun bordereau trouvé pour cette commande";
    header('Location: ../../views/frontoffice/commandes.php?idCommande=' . $idCommande);
    exit;
}

$bordereau = trim($result['noBordereau']);

// On demande le Status de notre commande en envoyant notre numéro de bordereau
fwrite($socket, "STATUS $bordereau\n");

// On récupère la réponse du serveur
$server_response = fgets($socket, 4096);

if ($server_response === false) {
    error_log("clientSocketSuivieEtape: Pas de réponse du serveur pour le bordereau $bordereau");
    fclose($socket);
    $_SESSION['error_message'] = "Pas de réponse du serveur de suivi";
    header('Location: ../../views/frontoffice/commandes.php?idCommande=' . $idCommande);
    exit;
}

// On fait un tableau avec toutes les infos du serveur 
// Les données étaient sous la forme 
// noBordereau | idCommande | destination | localisation | etape | date_etape | typeLivraison | l'img binaire
$status_parts = explode("|", rtrim($server_response));

if (count($status_parts) < 7) {
    error_log("clientSocketSuivieEtape: Format de réponse invalide: " . $server_response);
    fclose($socket);
    $_SESSION['error_message'] = "Format de réponse invalide du serveur";
    header('Location: ../../views/frontoffice/commandes.php?idCommande=' . $idCommande);
    exit;
}

$bordereau_recu = $status_parts[0];
$commande = $status_parts[1];
$destination = $status_parts[2];
$localisation = $status_parts[3];
$etape = $status_parts[4];
$date_etape = $status_parts[5];
$typeLivraison = $status_parts[6] ?? '';

// Récupérer l'ancienne étape pour comparer
$sqlOld = "SELECT etape FROM _commande WHERE idCommande = :idCommande";
$stmtOld = $pdo->prepare($sqlOld);
$stmtOld->execute([":idCommande" => $idCommande]);
$oldResult = $stmtOld->fetch(PDO::FETCH_ASSOC);
$oldEtape = $oldResult ? $oldResult['etape'] : null;

// Si la commande est à l'étape 9 et que le type de livraison est ABSENT 
// Alors il va falloir lire l'image de la boîte aux lettres qu'on a reçue en binaire
if ($etape == '9' && $typeLivraison === 'ABSENT') {

    $image_data = ''; // Variables pour stocker les données de l'image
    
    // Lire les 4 premiers octets envoyés par le serveur
    $first_bytes = fread($socket, 4);
    
    // Si le serveur renvoie 'null', cela signifie qu'aucune image n'est disponible
    if ($first_bytes === 'null') {
        // Lire un octet supplémentaire
        fread($socket, 1);
        // Supprimer l'image précédente de la session si elle existe
        unset($_SESSION['photo']);
        error_log("Pas d'image disponible pour la commande $idCommande");
    } else {
        // Sinon, on commence à stocker les données reçues
        $image_data = $first_bytes;
    
        // Définir un timeout très court pour la lecture du socket afin de ne pas bloquer
        stream_set_timeout($socket, 0, 100000); 
        
        // Lire progressivement les données envoyées par le serveur jusqu'à la fin
        while (!feof($socket)) {

            // Lire un "chunk" de 8192 octets
            $chunk = fread($socket, 8192);
                    
            // Si aucune donnée n'est reçue, on sort de la boucle
            if ($chunk === false || $chunk === '') {
                break;
            }

            // Ajouter le chunk aux données accumulées
            $image_data .= $chunk;
            
            // Vérifie si le dernier caractère est un retour à la ligne "\n" (fin de transmission)
            if (substr($image_data, -1) === "\n") {
                $image_data = substr($image_data, 0, -1);
                break;
            }
            
            // Si l'image dépasse 10 Mo, on arrête la lecture pour éviter de surcharger la mémoire
            if (strlen($image_data) > 10000000) { 
                error_log("Image trop grande pour la commande $idCommande");
                break;
            }
        }
        
        // Si l'image reçue est suffisamment grande, on la stocke dans la session en base64
        if (strlen($image_data) > 10) {
            $_SESSION['photo'] = base64_encode($image_data);
            error_log("Image reçue: " . strlen($image_data) . " octets pour la commande $idCommande");
        } else {
            unset($_SESSION['photo']);
            error_log("Image trop petite pour la commande $idCommande");
        }
    }
} else {
    // Si la commande n'est pas à l'étape 9 ou que le type de livraison n'est pas ABSENT
    // On lit 5 octets pour consommer le flux et on supprime la photo précédente
    fread($socket, 5); 
    unset($_SESSION['photo']);
}

// Mettre à jour l'étape dans la table _commande
$sql = "UPDATE _commande SET etape = :etape WHERE idCommande = :idCommande";
$stmt = $pdo->prepare($sql);
$stmt->execute([":etape" => $etape, ":idCommande" => $idCommande]);

// Définir les variables pour la notification
$titre = "";
$contenu = "";
$etatLivraison = "";

if ($etape == 1 || $etape == 2) {
    $etatLivraison = 'En cours de préparation';
    $titre = "📦 Colis en préparation";
    $contenu = "Votre colis pour la commande n°$idCommande est en cours de préparation.";
} else if ($etape == 3 || $etape == 4) {
    $etatLivraison = 'Prise en charge du colis';
    $titre = "⏳ Colis pris en charge";
    $contenu = "Votre colis pour la commande n°$idCommande a été pris en charge par le transporteur.";
} else if ($etape == 5 || $etape == 6) {
    $etatLivraison = 'Arrivé à la plateforme régionale';
    $titre = "📍 Colis arrivé à la plateforme régionale";
    $contenu = "Votre colis pour la commande n°$idCommande est arrivé à la plateforme régionale.";
} else if ($etape == 7 || $etape == 8) {
    $etatLivraison = 'Arrivé à la plateforme locale';
    $titre = "🏡 Colis arrivé à la plateforme locale";
    $contenu = "Votre colis pour la commande n°$idCommande est arrivé à la plateforme locale.";
} else if ($etape == 9) {
    // Adapter le message selon le type de livraison
    if ($typeLivraison === 'ABSENT') {
        $etatLivraison = 'Colis non distribué - Absent';
        $titre = "📫 Colis non distribué";
        $contenu = "Votre colis pour la commande n°$idCommande n'a pas pu être distribué (destinataire absent). Une photo a été prise.";
    } else if ($typeLivraison === 'REFUSE') {
        $etatLivraison = 'Colis refusé';
        $titre = "📫 Colis refusé";
        $contenu = "Votre colis pour la commande n°$idCommande a été refusé.";
    } else {
        $etatLivraison = 'Colis livré';
        $titre = "📫 Colis livré";
        $contenu = "Votre colis pour la commande n°$idCommande a été livré avec succès.";
    }
}

// Mettre à jour l'état de livraison
if (!empty($etatLivraison)) {
    $sql = "UPDATE _commande SET etatLivraison = :etatLivraison WHERE idCommande = :idCommande";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([":etatLivraison" => $etatLivraison, ":idCommande" => $idCommande]);
}

// Créer une notification UNIQUEMENT si l'étape a changé
if (!empty($titre) && $oldEtape != $etape) {
    try {
        $idClient = $commandeData['idClient'];
        
        if (!empty($idClient)) {
            // Insertion dans _notification avec l'ordre correct des colonnes
            // D'après votre structure : idNotif (auto), idClient, contenuNotif, titreNotif, dateNotif, est_vendeur
            $sqlNotif = "INSERT INTO _notification (idClient, contenuNotif, titreNotif, dateNotif, est_vendeur) 
                        VALUES (:idClient, :contenu, :titre, NOW(), 0)";
            
            $stmtNotif = $pdo->prepare($sqlNotif);
            $success = $stmtNotif->execute([
                ':idClient' => $idClient,
                ':contenu' => $contenu,
                ':titre' => $titre
            ]);
            
            if ($success) {
                error_log("✅ Notification créée pour la commande $idCommande (client $idClient) : $titre");
            } else {
                $errorInfo = $stmtNotif->errorInfo();
                error_log("❌ Erreur lors de la création de la notification: " . print_r($errorInfo, true));
            }
        } else {
            error_log("⚠️ Impossible de créer la notification : idClient non trouvé pour la commande $idCommande");
        }
    } catch (PDOException $e) {
        error_log("❌ Exception PDO lors de la création de la notification : " . $e->getMessage());
        error_log("Code: " . $e->getCode());
    } catch (Exception $e) {
        error_log("❌ Exception générale lors de la création de la notification : " . $e->getMessage());
    }
} else if ($oldEtape == $etape) {
    error_log("ℹ️ Pas de nouvelle notification pour la commande $idCommande (étape inchangée: $etape)");
}

$_SESSION['typeLivraison'] = $typeLivraison;

// Fermer proprement
fwrite($socket, "QUIT\n");
fclose($socket);

// Message de succès pour l'utilisateur
$_SESSION['success_message'] = "Suivi de commande mis à jour avec succès";

header('Location: ../../views/frontoffice/commandes.php?idCommande=' . $idCommande);
exit;
?>
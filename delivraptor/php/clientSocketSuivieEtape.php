<?php
//Ce fichier est appellé lorsqu'on veut connaitre le suivie de notre commande
session_start();
require_once __DIR__ . '/../../controllers/pdo.php';

//Dans l'url de la pop up on recupère l'id de la commande
$idCommande = intval($_GET['idCommande']);

$host = 'web';
$port = 8080;

// Connexion au socket
$socket = @fsockopen($host, $port, $errno, $errstr, 5);

if (!$socket) {
    echo "ERREUR: Impossible de se connecter à $host:$port\n";
    exit(1);
}

// Mode bloquant pour attendre la réponse du serveur avant de continuer
stream_set_blocking($socket, true);

// Authentification
fwrite($socket, "AUTH admin e10adc3949ba59abbe56e057f20f883e\n");
$auth_response = fgets($socket, 1024);

// On récupère le numero de bordereau dans la table _commande 
$sql = "SELECT noBordereau FROM _commande WHERE idCommande = :idCommande";
$stmt = $pdo->prepare($sql);
$stmt->execute([":idCommande" => $idCommande]);
$result = $stmt->fetch(PDO::FETCH_ASSOC);
$bordereau = trim($result['noBordereau']);

// On demande le Status de notre commande en envoyant notre numero de bordereau
fwrite($socket, "STATUS $bordereau\n");

// On recupère la reponse du serveur
$server_response = fgets($socket, 4096);

if ($server_response === false) {
    fclose($socket);
    echo "ERREUR: Pas de réponse du serveur\n";
    exit(1);
}

// On fait un tableau avec toutes les infos du serv 
// Les données étaient sous la forme 
// noBordereau | idCOmmande | destination | Arrivé chez transporteur | etape | date_etape | typeLivraison | l'img binaire
$status_parts = explode("|", rtrim($server_response));

if (count($status_parts) < 7) {
    fclose($socket);
    echo "ERREUR: Format de réponse invalide\n";
    exit(1);
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

// Si on la commande est à l'étape 0 et que le type de livraison est à l'etat ABSENT 
// Alors il va falloir lire l'img de la boite aux lettres qu'on a recu en binaire
if ($etape == '9' && $typeLivraison === 'ABSENT') {

    $image_data = ''; // Variables pour stocker les données de l'image
    $buffer = '';
    
    // Lire les 4 premiers octets envoyés par le serveur
    $first_bytes = fread($socket, 4);
    
    // Si le serveur renvoie 'null', cela signifie qu'aucune image n'est disponible
    if ($first_bytes === 'null') {
        // Lire un octet supplémentaire
        fread($socket, 1);
        // Supprimer l'image précédente de la session si elle existe
        unset($_SESSION['photo']);
    } else {
        // Sinon, on commence à stocker les données reçues
        $image_data = $first_bytes;
    
        // Définir un timeout très court pour la lecture du socket afin de ne pas bloquer
        stream_set_timeout($socket, 0, 100000); 
        
        // Lire progressivement les données envoyées par le serveur jusqu'à la fin ou jusqu'au marqueur de fin
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
                break;
            }
        }
        
        // Si l'image reçue est suffisamment grande, on la stocke dans la session en base64
        if (strlen($image_data) > 10) {
            $_SESSION['photo'] = base64_encode($image_data);
            error_log("Image reçue: " . strlen($image_data) . " octets");
             // Sinon, on supprime la photo existante
        } else {
            unset($_SESSION['photo']);
        }
    }
} else {
    // Si la commande n'est pas à l'étape 9 ou que le type de livraison n'est pas ABSENT
    // On lit 5 octets pour consommer le flux et on supprime la photo précédente
    fread($socket, 5); 
    unset($_SESSION['photo']);
}

// Mettre à jour l'étape et l'état de livraison dans la table _commande
$sql = "UPDATE _commande SET etape = :etape WHERE idCommande = :idCommande";
$stmt = $pdo->prepare($sql);
$stmt->execute([":etape" => $etape, ":idCommande" => $idCommande]);

// Mettre à jour l'état de livraison en fonction de l'étape
if ($etape == 1 || $etape == 2) {
    $sql = "UPDATE _commande SET etatLivraison = 'En cours de préparation' WHERE idCommande = :idCommande";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([":idCommande" => $idCommande]);
    
    // Ajout notification
    $titre = "📦 Colis en préparation";
    $contenu = "Votre colis est en cours de préparation.";
} else if ($etape == 3 || $etape == 4) {
    $sql = "UPDATE _commande SET etatLivraison = 'Prise en charge du colis' WHERE idCommande = :idCommande";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([":idCommande" => $idCommande]);
    
    // Ajout notification
    $titre = "⏳ Colis pris en charge";
    $contenu = "Votre colis a été pris en charge par le transporteur.";
} else if ($etape == 5 || $etape == 6) {
    $sql = "UPDATE _commande SET etatLivraison = 'Arriver à la plateforme Régional' WHERE idCommande = :idCommande";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([":idCommande" => $idCommande]);
    
    // Ajout notification
    $titre = "📍 Colis arrivé à la plateforme régionale";
    $contenu = "Votre colis est arrivé à la plateforme régionale.";
} else if ($etape == 7 || $etape == 8) {
    $sql = "UPDATE _commande SET etatLivraison = 'Arriver à la plateforme local' WHERE idCommande = :idCommande";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([":idCommande" => $idCommande]);
    
    // Ajout notification
    $titre = "🏡 Colis arrivé à la plateforme locale";
    $contenu = "Votre colis est arrivé à la plateforme locale.";
} else if ($etape == 9) {
    $sql = "UPDATE _commande SET etatLivraison = 'Colis livré' WHERE idCommande = :idCommande";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([":idCommande" => $idCommande]);
    
    // Ajout notification
    $titre = "📫 Colis livré";
    $contenu = "Votre colis a été livré.";
}

// Récupérer idClient pour les notifications (uniquement si l'étape a changé)
if (isset($titre) && $oldEtape != $etape) {
    $stmtClient = $pdo->prepare("SELECT idClient FROM _commande WHERE idCommande = ?");
    $stmtClient->execute([$idCommande]);
    $rowClient = $stmtClient->fetch(PDO::FETCH_ASSOC);
    
    if ($rowClient && !empty($rowClient['idClient'])) {
        $idClientNotif = $rowClient['idClient'];
        
        // Structure correcte de la table _notification : idNotif (auto), idClient, contenuNotif, titreNotif, dateNotif, est_vendeur
        $sqlNotif = "INSERT INTO _notification (idClient, contenuNotif, titreNotif, dateNotif, est_vendeur) 
                     VALUES (?, ?, ?, NOW(), ?)";
        $stmtNotif = $pdo->prepare($sqlNotif);
        $stmtNotif->execute([$idClientNotif, $contenu, $titre, 0]);
        
        error_log("Notification créée pour la commande $idCommande : $titre");
    } else {
        error_log("Impossible de créer la notification : idClient non trouvé pour la commande $idCommande");
    }
}

$_SESSION['typeLivraison'] = $typeLivraison;

// Fermer proprement
fwrite($socket, "QUIT\n");
fclose($socket);

header('Location: ../../views/frontoffice/commandes.php?idCommande=' . $idCommande);
exit;
?>
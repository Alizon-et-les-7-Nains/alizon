<?php
require_once '../../controllers/pdo.php' ;
    
// Fonctions de déchiffrement
function convert($char, $cle, $sens) {
    $codeMin = 32;
    $codeMax = 126;
    $nbChars = $codeMax - $codeMin + 1;
    
    $valAsciiChar = ord($char);
    $valAsciiCle = ord($cle);
    
    if ($valAsciiChar < $codeMin || $valAsciiChar > $codeMax) {
        return $char;
    }
    
    $decal = $valAsciiCle - $codeMin;
    
    if ($sens == 1) {
        $newCode = (($valAsciiChar - $codeMin + $decal) % $nbChars) + $codeMin;
    } else {
        $newCode = (($valAsciiChar - $codeMin - $decal + $nbChars) % $nbChars) + $codeMin;
    }
    
    return chr($newCode);
}

function vignere($texte, $cle, $sens) {
    $result = "";
    $indexCLe = 0;
    $cleLength = strlen($cle);
    
    for ($i = 0; $i < strlen($texte); $i++) {
        $cleChar = $cle[$indexCLe % $cleLength];
        $result .= convert($texte[$i], $cleChar, $sens);
        $indexCLe++;
    }
    
    return $result;
}

// Clé de déchiffrement (à mettre dans vos variables d'environnement)
$cle = "?zu6j,xX{N12I]0r6C=v57IoASU~?6_y";

// Fonction pour déchiffrer un mot de passe
function dechiffrerMotDePasse($mdpChiffre, $cle) {
    return vignere($mdpChiffre, $cle, -1);
}

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // 1. Récupérer les mots de passe à traiter
    $sqlSelect = "
        SELECT idClient, email, mdp 
        FROM _client 
        WHERE mdp IS NOT NULL 
          AND mdp != ''
          AND mdp NOT LIKE '$2y$%'
        ORDER BY idClient
    ";
    
    $stmt = $pdo->query($sqlSelect);
    $clients = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Nombre de clients à traiter : " . count($clients) . "<br><br>";
    
    // 2. Pour chaque client, déchiffrer et hacher le mot de passe
    foreach ($clients as $client) {
        $idClient = $client['idClient'];
        $email = $client['email'];
        $mdpChiffre = $client['mdp'];
        
        // Déchiffrer le mot de passe
        $mdpClair = dechiffrerMotDePasse($mdpChiffre, $cle);
        
        // Hacher avec password_hash (utilisation de BCRYPT par défaut)
        $mdpHache = password_hash($mdpClair, PASSWORD_DEFAULT);
        
        // 3. Mettre à jour le mot de passe dans la base de données
        $sqlUpdate = "UPDATE _client SET mdp = :mdp WHERE idClient = :id";
        $updateStmt = $pdo->prepare($sqlUpdate);
        $updateStmt->execute([
            ':mdp' => $mdpHache,
            ':id' => $idClient
        ]);
        
        // Afficher les résultats
        echo "Client ID: $idClient<br>";
        echo "Email: $email<br>";
        echo "Mot de passe chiffré: " . htmlspecialchars($mdpChiffre) . "<br>";
        echo "Mot de passe déchiffré: $mdpClair<br>";
        echo "Mot de passe haché: " . substr($mdpHache, 0, 20) . "...<br>";
        echo "Mise à jour: " . ($updateStmt->rowCount() > 0 ? "✓" : "✗") . "<br>";
        echo "----------------------------------------<br>";
    }
    
    echo "<br>Traitement terminé avec succès !";
    
} catch (PDOException $e) {
    echo "Erreur : " . $e->getMessage();
}
?>
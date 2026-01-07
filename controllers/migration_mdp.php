<?php
require_once 'pdo.php'; // Utilisation de votre connexion PDO existante

// La clé doit être exactement la même que dans Chiffrement.js
$cle = "?zu6j,xX{N12I]0r6C=v57IoASU~?6_y";

/**
 * Version PHP de votre fonction convert
 */
function convert($char, $cleChar, $sens) {
    $codeMin = 32;
    $codeMax = 126;
    $nbChars = $codeMax - $codeMin + 1;

    $valAsciiChar = ord($char);
    $valAsciiCle = ord($cleChar);

    if ($valAsciiChar < $codeMin || $valAsciiChar > $codeMax) {
        return $char;
    }

    $decal = $valAsciiCle - $codeMin;

    if ($sens === 1) {
        $newCode = (($valAsciiChar - $codeMin + $decal) % $nbChars) + $codeMin;
    } else {
        // Logique pour le sens -1 (identique au JS)
        $newCode = (($valAsciiChar - $codeMin - $decal + $nbChars) % $nbChars) + $codeMin;
    }

    return chr($newCode);
}

/**
 * Version PHP de votre fonction vignere
 */
function vignere($texte, $cle, $sens) {
    $result = "";
    $indexCle = 0;
    $longueurTexte = strlen($texte);
    $longueurCle = strlen($cle);

    for ($i = 0; $i < $longueurTexte; $i++) {
        $cleChar = $cle[$indexCle % $longueurCle];
        $result .= convert($texte[$i], $cleChar, $sens);
        $indexCle++;
    }
    return $result;
}

try {
    // 1. Récupération de tous les vendeurs
    $stmt = $pdo->query("SELECT codeVendeur, mdp FROM _vendeur");
    $vendeurs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $count = 0;
    foreach ($vendeurs as $v) {
        // On chiffre le mot de passe en clair (sens 1)
        $mdpChiffre = vignere($v['mdp'], $cle, 1);
        
        // 2. Mise à jour en base de données
        $update = $pdo->prepare("UPDATE _vendeur SET mdp = :mdp WHERE codeVendeur = :id");
        $update->execute([
            ':mdp' => $mdpChiffre, 
            ':id' => $v['codeVendeur']
        ]);
        $count++;
    }

    echo "Migration terminée ! $count vendeurs ont été mis à jour.";

} catch (Exception $e) {
    die("Erreur lors de la migration : " . $e->getMessage());
}
?>
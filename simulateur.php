<?php
// simulateur.php
// Script autonome pour faire avancer d'une étape tous les colis en transit

// 1. Connexion BDD
$host = 'localhost';
$dbname = 'saedb';
$user = 'sae';
$pass = 'grognasseEtCompagnie';

try {
    $db = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur BDD : " . $e->getMessage());
}

// 2. Récupérer tous les colis non terminés (étape 1 à 8)
$stmt = $db->prepare("
    SELECT numBordereau, etape, localisation 
    FROM _delivraptor_colis 
    WHERE etape < 9
    ORDER BY date_etape ASC
");
$stmt->execute();
$colis = $stmt->fetchAll(PDO::FETCH_ASSOC);

$compteur = 0;

foreach ($colis as $col) {
    $nouvelle_etape = $col['etape'] + 1;
    $numBordereau = $col['numBordereau'];
    $ancienne_localisation = $col['localisation'];

    // 3. Définir la nouvelle localisation selon l'étape
    $localisation = '';
    switch ($nouvelle_etape) {
        case 2:
            $localisation = 'En transit vers plateforme transporteur';
            break;
        case 3:
            $localisation = 'Arrivé chez transporteur';
            break;
        case 4:
            $localisation = 'Départ vers plateforme régionale';
            break;
        case 5:
            $localisation = 'Arrivé plateforme régionale';
            break;
        case 6:
            $localisation = 'Départ vers centre local';
            break;
        case 7:
            $localisation = 'Arrivé centre local';
            break;
        case 8:
            $localisation = 'Départ pour livraison finale';
            break;
        case 9:
            $localisation = 'Chez destinataire';
            break;
        default:
            $localisation = $ancienne_localisation;
    }

    // 4. Gestion spéciale de l'étape 9
    if ($nouvelle_etape == 9) {
        $type_livraison = '';
        $refus_raison = null;
        $photo_path = null;

        $choix = rand(1, 3); // 1 = mains propres, 2 = absent, 3 = refusé

        switch ($choix) {
            case 1:
                $type_livraison = 'MAINS_PROPRES';
                break;
            case 2:
                $type_livraison = 'ABSENT';
                $photo_path = ''; // Mettre image boite aux lettres
                break;
            case 3:
                $type_livraison = 'REFUSE';
                $raisons = [
                    'Destinataire absent',
                    'Adresse incorrecte',
                    'Colis endommagé',
                    'Refusé par le destinataire'
                ];
                $refus_raison = $raisons[array_rand($raisons)];
                break;
        }

        // Mise à jour avec infos de livraison
        $stmt = $db->prepare("
            UPDATE _delivraptor_colis 
            SET etape = 9, 
                localisation = :localisation, 
                date_etape = NOW(),
                livraison_type = :type,
                refus_raison = :raison,
                photo_path = :photo
            WHERE numBordereau = :bordereau
        ");
        $stmt->execute([
            ':localisation' => $localisation,
            ':type' => $type_livraison,
            ':raison' => $refus_raison,
            ':photo' => $photo_path,
            ':bordereau' => $numBordereau
        ]);

    } else {
        // 5. Mise à jour normale (étape 2 à 8)
        $stmt = $db->prepare("
            UPDATE _delivraptor_colis 
            SET etape = :etape, 
                localisation = :localisation, 
                date_etape = NOW()
            WHERE numBordereau = :bordereau
        ");
        $stmt->execute([
            ':etape' => $nouvelle_etape,
            ':localisation' => $localisation,
            ':bordereau' => $numBordereau
        ]);
    }

    // 6. Historisation
    $stmt = $db->prepare("
        INSERT INTO _delivraptor_colis_historique 
        (numBordereau, etape, date_etape, localisation)
        VALUES (:bordereau, :etape, NOW(), :localisation)
    ");
    $stmt->execute([
        ':bordereau' => $numBordereau,
        ':etape' => $nouvelle_etape,
        ':localisation' => $localisation
    ]);

    // 7. Si passage de l'étape 4 à 5, libérer la file
    if ($col['etape'] == 4 && $nouvelle_etape == 5) {
        $stmt = $db->prepare("
            DELETE FROM _delivraptor_file_prise_en_charge 
            WHERE numBordereau = :bordereau
        ");
        $stmt->execute([':bordereau' => $numBordereau]);
        echo "[Libéré file] Bordereau $numBordereau quitte la file.\n";
    }

    $compteur++;
    echo "Bordereau $numBordereau : Étape {$col['etape']} → $nouvelle_etape ($localisation)\n";
}

echo "\n=== Simulation terminée ===\n";
echo "$compteur colis avancés d'une étape.\n";
?>
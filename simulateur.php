<?php
// simulateur.php

// 1. Connexion BDD
$host = 'maraidb';
$dbname = 'saedb';
$user = 'sae';
$pass = 'grognasseEtCompagnie';

try {
    $db = new PDO("mysql:host=$host;port=3306;dbname=$dbname;charset=utf8", $user, $pass);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur BDD : " . $e->getMessage());
}

// Nettoyer la file des colis qui ne devraient pas y être (étape >= 5)
function nettoyerFileInvalide(PDO $db): void {
    $db->prepare("
        DELETE f FROM _delivraptor_file_prise_en_charge f
        JOIN _delivraptor_colis c ON c.numBordereau = f.numBordereau
        WHERE c.etape >= 5
    ")->execute();
    
    echo "[Nettoyage] Colis aux étapes >= 5 retirés de la file de prise en charge\n";
}

function ajouterColisEnAttente(PDO $db): void {
    $next = $db->query("
        SELECT q.noCommande, c.numBordereau
        FROM _delivraptor_queue q
        JOIN _delivraptor_colis c ON c.noCommande = q.noCommande
        WHERE q.traite = FALSE
          AND NOT EXISTS (
              SELECT 1 FROM _delivraptor_file_prise_en_charge f
              WHERE f.numBordereau = c.numBordereau
          )
        ORDER BY q.date_creation ASC
        LIMIT 1
    ")->fetch(PDO::FETCH_ASSOC);

    if (!$next) {
        return; // rien en attente
    }

    $db->prepare("
        INSERT INTO _delivraptor_file_prise_en_charge (numBordereau, date_entree)
        VALUES (:bordereau, NOW())
    ")->execute([':bordereau' => $next['numBordereau']]);

    $db->prepare("
        UPDATE _delivraptor_queue SET traite = TRUE 
        WHERE noCommande = :noCommande
    ")->execute([':noCommande' => $next['noCommande']]);

    echo "[Ajout file] Bordereau {$next['numBordereau']} entre en file de prise en charge.\n";
}

// Nettoyage initial
nettoyerFileInvalide($db);

// 2. Récupérer tous les colis en file (avec leur étape/localisation)
$stmt = $db->prepare("
    SELECT f.numBordereau, c.etape, c.localisation
    FROM _delivraptor_file_prise_en_charge f
    JOIN _delivraptor_colis c USING (numBordereau)
    WHERE c.etape < 5  -- Seulement les colis aux étapes 1-4
    ORDER BY f.date_entree ASC
");
$stmt->execute();
$colis = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 3. Récupérer aussi les colis qui ne sont PAS dans la file mais qui sont aux étapes 5-8
$stmt_hors_file = $db->prepare("
    SELECT c.numBordereau, c.etape, c.localisation
    FROM _delivraptor_colis c
    WHERE c.etape >= 5 AND c.etape < 9
      AND NOT EXISTS (
          SELECT 1 FROM _delivraptor_file_prise_en_charge f
          WHERE f.numBordereau = c.numBordereau
      )
    ORDER BY c.date_etape ASC
");
$stmt_hors_file->execute();
$colis_hors_file = $stmt_hors_file->fetchAll(PDO::FETCH_ASSOC);

// Combiner tous les colis qui doivent progresser
$tous_colis = array_merge($colis, $colis_hors_file);

$compteur = 0;

foreach ($tous_colis as $col) {
    $nouvelle_etape = $col['etape'] + 1;
    $numBordereau = $col['numBordereau'];
    $ancienne_localisation = $col['localisation'];

    // Mettre à jour la date d'entrée dans la file si le colis y est encore
    if ($col['etape'] < 5) {
        $db->prepare("
            UPDATE _delivraptor_file_prise_en_charge
            SET date_entree = NOW()
            WHERE numBordereau = :bordereau
        ")->execute([':bordereau' => $numBordereau]);
    }

    // 4. Définir la nouvelle localisation selon l'étape
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

    // 5. Gestion spéciale de l'étape 9
    if ($nouvelle_etape == 9) {
        $type_livraison = '';
        $refus_raison = null;
        $photo_path = null;

        $choix = random_int(1, 3); // 1 = mains propres, 2 = absent, 3 = refusé

        switch ($choix) {
            case 1:
                $type_livraison = 'MAINS_PROPRES';
                break;
            case 2:
                $type_livraison = 'ABSENT';
                $photo_path = './images/imgBoiteAuxLettres.jpg';
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

        // Si le colis était dans la file, le retirer
        if ($col['etape'] < 5) {
            $db->prepare("
                DELETE FROM _delivraptor_file_prise_en_charge 
                WHERE numBordereau = :bordereau
            ")->execute([':bordereau' => $numBordereau]);
            echo "[Livré] Bordereau $numBordereau livré et retiré de la file.\n";
        }

    } else {
        // 6. Mise à jour normale (étape 2 à 8)
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

        // 7. Si passage de l'étape 4 à 5, libérer la file
        if ($col['etape'] == 4 && $nouvelle_etape == 5) {
            $db->prepare("
                DELETE FROM _delivraptor_file_prise_en_charge 
                WHERE numBordereau = :bordereau
            ")->execute([':bordereau' => $numBordereau]);
            echo "[Libéré file] Bordereau $numBordereau quitte la file (étape 4→5).\n";

            // On fait entrer un colis en attente dès qu'une place se libère
            ajouterColisEnAttente($db);
        }
        
        // 8. Si colis passe à l'étape 5, s'assurer qu'il n'est pas dans la file
        elseif ($nouvelle_etape >= 5 && $col['etape'] < 5) {
            $db->prepare("
                DELETE FROM _delivraptor_file_prise_en_charge 
                WHERE numBordereau = :bordereau
            ")->execute([':bordereau' => $numBordereau]);
        }
    }

    // 9. Historisation
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

    $compteur++;
    echo "Bordereau $numBordereau : Étape {$col['etape']} → $nouvelle_etape ($localisation)\n";
}

echo "\n=== Simulation terminée ===\n";
echo "$compteur colis avancés d'une étape.\n";

?>
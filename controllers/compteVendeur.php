 <?php 
// require_once 'pdo.php';
// require_once 'auth.php';

// $code_vendeur = $_SESSION['id'];

// if ($_SERVER['REQUEST_METHOD'] === 'POST') {

//     // Récupération des données du formulaire
//     $pseudo        = $_POST['pseudo'] ?? '';
//     $nom           = $_POST['nom'] ?? '';
//     $prenom        = $_POST['prenom'] ?? '';
//     $email         = $_POST['email'] ?? '';
//     $dateNaissance = $_POST['dateNaissance'] ?? '';
//     $telephone     = $_POST['telephone'] ?? '';
//     $codePostal    = $_POST['codePostal'] ?? '';
//     $adresse       = $_POST['adresse'] ?? '';
//     $pays          = $_POST['pays'] ?? '';
//     $ville         = $_POST['ville'] ?? '';
//     $region        = $_POST['region'] ?? '';
//     $raisonSociale = $_POST['raisonSociale'] ?? '';
//     $noSiren       = $_POST['noSiren'] ?? '';
//     $idAdresse     = $_POST['id_adresse'] ?? '';
//     $ancienMdp     = $_POST['ancienMdp'] ?? '';
//     $nouveauMdp    = $_POST['nouveauMdp'] ?? '';

//     // Gestion des valeurs NULL pour dateNaissance
//     $dateNaissance = ($dateNaissance === '') ? null : $dateNaissance;

//     try {
//         // Vérifier si une transaction est déjà active
//         if (!$pdo->inTransaction()) {
//             $pdo->beginTransaction();
//         }

//         // Mise à jour du mot de passe si fourni
//         if (!empty($nouveauMdp) && !empty($ancienMdp)) {
//             $stmt = $pdo->prepare("SELECT mdp FROM _vendeur WHERE codeVendeur = :code_vendeur");
//             $stmt->execute([':code_vendeur' => $code_vendeur]);
//             $vendeur = $stmt->fetch(PDO::FETCH_ASSOC);

//             if ($vendeur && $ancienMdp === $vendeur['mdp']) {
//                 $stmt = $pdo->prepare("UPDATE _vendeur SET mdp = :mdp WHERE codeVendeur = :code_vendeur");
//                 $stmt->execute([
//                     ':mdp' => $nouveauMdp,
//                     ':code_vendeur' => $code_vendeur
//                 ]);
//             }
//         }

//         // Mise à jour des informations du vendeur
//         $stmt = $pdo->prepare("
//             UPDATE _vendeur 
//             SET pseudo = :pseudo, 
//                 nom = :nom, 
//                 prenom = :prenom, 
//                 email = :email, 
//                 dateNaissance = :dateNaissance,
//                 noTelephone = :telephone,
//                 raisonSocial = :raisonSocial,
//                 noSiren = :noSiren
//             WHERE codeVendeur = :code_vendeur
//         ");

//         $stmt->execute([
//             ':pseudo'        => $pseudo,
//             ':nom'           => $nom,
//             ':prenom'        => $prenom,
//             ':email'         => $email,
//             ':dateNaissance' => $dateNaissance,
//             ':telephone'     => $telephone,
//             ':raisonSocial'  => $raisonSociale,
//             ':noSiren'       => $noSiren,
//             ':code_vendeur'  => $code_vendeur
//         ]);

//         // Mise à jour de l'adresse
//         $stmt = $pdo->prepare("
//             UPDATE _adresseVendeur 
//             SET adresse = :adresse,
//                 pays = :pays,
//                 ville = :ville, 
//                 codePostal = :codePostal,
//                 region = :region
//             WHERE idAdresse = :idAdresse
//         ");

//         $stmt->execute([
//             ':adresse'    => $adresse,
//             ':pays'       => $pays,
//             ':ville'      => $ville,
//             ':codePostal' => $codePostal,
//             ':region'     => $region,
//             ':idAdresse'  => $idAdresse
//         ]);

//         // Commit seulement si la transaction est active
//         if ($pdo->inTransaction()) {
//             $pdo->commit();
//         }

//         // Redirection pour éviter le rechargement du formulaire
//         header("Location: ../views/backoffice/compteVendeur.php?success=1");
//         exit();

//     } catch (Exception $e) {
//         // Rollback seulement si la transaction est active
//         if ($pdo->inTransaction()) {
//             $pdo->rollBack();
//         }
//         echo "Erreur SQL : " . $e->getMessage();
//         exit();
//     }
// }

require_once 'auth.php';
require_once 'pdo.php';

$code_vendeur = $_SESSION['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Récupération des données du formulaire
    $pseudo        = $_POST['pseudo'] ?? '';
    $nom           = $_POST['nom'] ?? '';
    $prenom        = $_POST['prenom'] ?? '';
    $email         = $_POST['email'] ?? '';
    $dateNaissance = $_POST['dateNaissance'] ?? '';
    $telephone     = $_POST['telephone'] ?? '';
    $codePostal    = $_POST['codePostal'] ?? '';
    $adresse       = $_POST['adresse'] ?? '';
    $pays          = $_POST['pays'] ?? '';
    $ville         = $_POST['ville'] ?? '';
    $region        = $_POST['region'] ?? '';
    $raisonSociale = $_POST['raisonSociale'] ?? '';
    $noSiren       = $_POST['noSiren'] ?? '';
    $idAdresse     = $_POST['id_adresse'] ?? '';
    $ancienMdp     = $_POST['ancienMdp'] ?? '';
    $nouveauMdp    = $_POST['nouveauMdp'] ?? '';
    $confirmationMdp = $_POST['confirmationMdp'] ?? '';

    // Gestion des valeurs NULL pour dateNaissance
    $dateNaissance = ($dateNaissance === '') ? null : $dateNaissance;

    try {
        // Vérifier si une transaction est déjà active
        if (!$pdo->inTransaction()) {
            $pdo->beginTransaction();
        }

        // VÉRIFICATION ET MISE À JOUR DU MOT DE PASSE
        $motDePasseModifie = false;
        if (!empty($ancienMdp) && !empty($nouveauMdp) && !empty($confirmationMdp)) {
            // Vérifier que les nouveaux mots de passe correspondent
            if ($nouveauMdp !== $confirmationMdp) {
                throw new Exception("La confirmation du mot de passe ne correspond pas.");
            }
            
            // Vérifier l'ancien mot de passe
            $stmt = $pdo->prepare("SELECT mdp FROM _vendeur WHERE codeVendeur = :code_vendeur");
            $stmt->execute([':code_vendeur' => $code_vendeur]);
            $vendeur = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$vendeur) {
                throw new Exception("Utilisateur non trouvé.");
            }

            // Comparaison directe (si vous n'utilisez pas de hachage)
            // NOTE: Il est fortement recommandé d'utiliser password_hash() et password_verify()
            if ($ancienMdp !== $vendeur['mdp']) {
                throw new Exception("L'ancien mot de passe est incorrect.");
            }

            // Validation des critères du nouveau mot de passe
            if (strlen($nouveauMdp) < 12) {
                throw new Exception("Le mot de passe doit contenir au moins 12 caractères.");
            }
            if (!preg_match('/[a-z]/', $nouveauMdp) || !preg_match('/[A-Z]/', $nouveauMdp)) {
                throw new Exception("Le mot de passe doit contenir au moins une minuscule et une majuscule.");
            }
            if (!preg_match('/[0-9]/', $nouveauMdp)) {
                throw new Exception("Le mot de passe doit contenir au moins un chiffre.");
            }
            if (!preg_match('/[^a-zA-Z0-9]/', $nouveauMdp)) {
                throw new Exception("Le mot de passe doit contenir au moins un caractère spécial.");
            }

            // Mettre à jour le mot de passe
            $stmt = $pdo->prepare("UPDATE _vendeur SET mdp = :mdp WHERE codeVendeur = :code_vendeur");
            $stmt->execute([
                ':mdp' => $nouveauMdp,
                ':code_vendeur' => $code_vendeur
            ]);
            
            $motDePasseModifie = true;
        }

        // Mise à jour des informations du vendeur
        $stmt = $pdo->prepare("
            UPDATE _vendeur 
            SET pseudo = :pseudo, 
                nom = :nom, 
                prenom = :prenom, 
                email = :email, 
                dateNaissance = :dateNaissance,
                noTelephone = :telephone,
                raisonSocial = :raisonSocial,
                noSiren = :noSiren
            WHERE codeVendeur = :code_vendeur
        ");

        $stmt->execute([
            ':pseudo'        => $pseudo,
            ':nom'           => $nom,
            ':prenom'        => $prenom,
            ':email'         => $email,
            ':dateNaissance' => $dateNaissance,
            ':telephone'     => $telephone,
            ':raisonSocial'  => $raisonSociale,
            ':noSiren'       => $noSiren,
            ':code_vendeur'  => $code_vendeur
        ]);

        // Mise à jour de l'adresse
        $stmt = $pdo->prepare("
            UPDATE _adresseVendeur 
            SET adresse = :adresse,
                pays = :pays,
                ville = :ville, 
                codePostal = :codePostal,
                region = :region
            WHERE idAdresse = :idAdresse
        ");

        $stmt->execute([
            ':adresse'    => $adresse,
            ':pays'       => $pays,
            ':ville'      => $ville,
            ':codePostal' => $codePostal,
            ':region'     => $region,
            ':idAdresse'  => $idAdresse
        ]);

        // Commit seulement si la transaction est active
        if ($pdo->inTransaction()) {
            $pdo->commit();
        }

        // Message de succès
        $message = "Profil mis à jour avec succès";
        if ($motDePasseModifie) {
            $message .= " et mot de passe modifié";
        }

        // Redirection pour éviter le rechargement du formulaire
        header("Location: ../views/backoffice/compteVendeur.php?success=1&message=" . urlencode($message));
        exit();

    } catch (Exception $e) {
        // Rollback seulement si la transaction est active
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        // Redirection avec message d'erreur
        header("Location: ../views/backoffice/compteVendeur.php?error=1&message=" . urlencode($e->getMessage()));
        exit();
    }
}
 ?>
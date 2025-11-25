<?php
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
        if (!$pdo->inTransaction()) {
            $pdo->beginTransaction();
        }

        $motDePasseModifie = false;
        if (!empty($ancienMdp) && !empty($nouveauMdp) && !empty($confirmationMdp)) {
            if ($nouveauMdp !== $confirmationMdp) {
                throw new Exception("La confirmation du mot de passe ne correspond pas.");
            }
            
            $stmt = $pdo->prepare("SELECT mdp FROM _vendeur WHERE codeVendeur = :code_vendeur");
            $stmt->execute([':code_vendeur' => $code_vendeur]);
            $vendeur = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$vendeur) {
                throw new Exception("Utilisateur non trouvé.");
            }

            if ($ancienMdp !== $vendeur['mdp']) {
                throw new Exception("L'ancien mot de passe est incorrect.");
            }

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

            $stmt = $pdo->prepare("UPDATE _vendeur SET mdp = :mdp WHERE codeVendeur = :code_vendeur");
            $stmt->execute([
                ':mdp' => $nouveauMdp,
                ':code_vendeur' => $code_vendeur
            ]);
            
            $motDePasseModifie = true;
        }

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

        if ($pdo->inTransaction()) {
            $pdo->commit();
        }

        $message = "Profil mis à jour avec succès";
        if ($motDePasseModifie) {
            $message .= " et mot de passe modifié";
        }

        header("Location: ../views/backoffice/compteVendeur.php?success=1&message=" . urlencode($message));
        exit();

    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        header("Location: ../views/backoffice/compteVendeur.php?error=1&message=" . urlencode($e->getMessage()));
        exit();
    }
}
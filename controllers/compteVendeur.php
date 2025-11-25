<?php 
require_once 'pdo.php';
require_once 'auth.php';

$code_vendeur = $_SESSION['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Récupération des données du formulaire
    $pseudo = $_POST['pseudo'] ?? '';
    $nom = $_POST['nom'] ?? '';
    $prenom = $_POST['prenom'] ?? '';
    $email = $_POST['email'] ?? '';
    $dateNaissance = $_POST['dateNaissance'] ?? '';
    $telephone = $_POST['telephone'] ?? '';
    $codePostal = $_POST['codePostal'] ?? '';
    $adresse = $_POST['adresse'] ?? '';
    $pays = $_POST['pays'] ?? '';
    $ville = $_POST['ville'] ?? '';
    $region = $_POST['region'] ?? '';
    $raisonSociale = $_POST['raisonSociale'] ?? '';
    $noSiren = $_POST['noSiren'] ?? '';
    $idAdresse = $_POST['id_adresse'] ?? '';
    $ancienMdp = $_POST['ancienMdp'] ?? '';
    $nouveauMdp = $_POST['nouveauMdp'] ?? '';

    // Gestion des valeurs NULL pour dateNaissance
    $dateNaissance = ($dateNaissance === '') ? null : $dateNaissance;

    try {
        $pdo->beginTransaction();

        // Mise à jour du mot de passe si fourni
        if (!empty($nouveauMdp) && !empty($ancienMdp)) {
            // Vérifier l'ancien mot de passe (sans hachage)
            $stmt = $pdo->prepare("SELECT mdp FROM _vendeur WHERE codeVendeur = :code_vendeur");
            $stmt->execute([':code_vendeur' => $code_vendeur]);
            $vendeur = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($vendeur && $ancienMdp === $vendeur['mdp']) {
                // Mettre à jour le mot de passe
                $stmt = $pdo->prepare("UPDATE _vendeur SET mdp = :mdp WHERE codeVendeur = :code_vendeur");
                $stmt->execute([
                    ':mdp' => $nouveauMdp,
                    ':code_vendeur' => $code_vendeur
                ]);
            }
        }

        // Mise à jour du vendeur
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
            ':pseudo' => $pseudo,
            ':nom' => $nom,
            ':prenom' => $prenom,
            ':email' => $email,
            ':dateNaissance' => $dateNaissance,
            ':telephone' => $telephone,
            ':raisonSocial' => $raisonSociale,
            ':noSiren' => $noSiren,
            ':code_vendeur' => $code_vendeur
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
            ':adresse' => $adresse,
            ':pays' => $pays,
            ':ville' => $ville,
            ':codePostal' => $codePostal,
            ':region' => $region,
            ':idAdresse' => $idAdresse
        ]);

        $pdo->commit();
        
        // Redirection pour éviter le rechargement du formulaire
        header("Location: ../views/backoffice/compteVendeur.php?success=1");
        exit();

    } catch (Exception $e) {
        $pdo->rollBack();
        header("Location: ../views/backoffice/compteVendeur.php?error=1");
        exit();
    }
}
?>
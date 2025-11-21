<?php
session_start();
require_once 'pdo.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code_vendeur = $_POST['code_vendeur'] ?? 1;
    $id_adresse = $_POST['id_adresse'] ?? null;
    
    try {
        // Récupération des données du formulaire
        $nom = $_POST['nom'] ?? '';
        $prenom = $_POST['prenom'] ?? '';
        $dateNaissance = $_POST['dateNaissance'] ?? '';
        $adresse = $_POST['adresse'] ?? '';
        $codePostal = $_POST['codePostal'] ?? '';
        $ville = $_POST['ville'] ?? '';
        $region = $_POST['region'] ?? '';
        $telephone = $_POST['telephone'] ?? '';
        $email = $_POST['email'] ?? '';
        $raisonSociale = $_POST['raisonSociale'] ?? '';
        $noSiren = $_POST['noSiren'] ?? '';
        $pseudo = $_POST['pseudo'] ?? '';
        $ancienMdp = $_POST['ancienMdp'] ?? '';
        $nouveauMdp = $_POST['nouveauMdp'] ?? '';
        
        // Vérification du mot de passe si fourni
        if (!empty($ancienMdp) && !empty($nouveauMdp)) {
            // Récupérer le mot de passe actuel
            $stmt = $pdo->prepare("SELECT mdp FROM _vendeur WHERE codeVendeur = :id");
            $stmt->execute([':id' => $code_vendeur]);
            $vendeur = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($vendeur) {
                // Inclure le fichier de chiffrement
                require_once 'Chiffrement.php';
                
                // Déchiffrer le mot de passe stocké
                $mdpDecrypte = vignere($vendeur['mdp'], $cle, -1);
                
                if ($ancienMdp !== $mdpDecrypte) {
                    $_SESSION['error'] = "L'ancien mot de passe est incorrect.";
                    header('Location: ../backoffice/compteVendeur.php');
                    exit();
                }
                
                // Valider le nouveau mot de passe
                if (!validerMotDePasse($nouveauMdp)) {
                    $_SESSION['error'] = "Le nouveau mot de passe ne respecte pas les critères de sécurité.";
                    header('Location: ../backoffice/compteVendeur.php');
                    exit();
                }
                
                // Chiffrer le nouveau mot de passe
                $nouveauMdpCrypte = vignere($nouveauMdp, $cle, 1);
                
                // Mettre à jour le mot de passe
                $stmt = $pdo->prepare("UPDATE _vendeur SET mdp = :mdp WHERE codeVendeur = :id");
                $stmt->execute([
                    ':mdp' => $nouveauMdpCrypte,
                    ':id' => $code_vendeur
                ]);
                
                $_SESSION['success'] = "Mot de passe modifié avec succès.";
            }
        }
        
        // Mettre à jour les informations du vendeur
        $stmt = $pdo->prepare("
            UPDATE _vendeur 
            SET nom = :nom, prenom = :prenom, dateNaissance = :dateNaissance,
                noTelephone = :telephone, email = :email, raisonSocial = :raisonSociale,
                noSiren = :noSiren, pseudo = :pseudo
            WHERE codeVendeur = :id
        ");
        
        $stmt->execute([
            ':nom' => $nom,
            ':prenom' => $prenom,
            ':dateNaissance' => $dateNaissance,
            ':telephone' => $telephone,
            ':email' => $email,
            ':raisonSociale' => $raisonSociale,
            ':noSiren' => $noSiren,
            ':pseudo' => $pseudo,
            ':id' => $code_vendeur
        ]);
        
        // Mettre à jour l'adresse
        if ($id_adresse) {
            // Mettre à jour l'adresse existante
            $stmtAdr = $pdo->prepare("
                UPDATE _adresseVendeur 
                SET adresse = :adresse, codePostal = :codePostal, ville = :ville, region = :region
                WHERE idAdresse = :id
            ");
            
            $stmtAdr->execute([
                ':adresse' => $adresse,
                ':codePostal' => $codePostal,
                ':ville' => $ville,
                ':region' => $region,
                ':id' => $id_adresse
            ]);
        } else {
            // Créer une nouvelle adresse
            $stmtAdr = $pdo->prepare("
                INSERT INTO _adresseVendeur (adresse, codePostal, ville, region, pays)
                VALUES (:adresse, :codePostal, :ville, :region, 'France')
            ");
            
            $stmtAdr->execute([
                ':adresse' => $adresse,
                ':codePostal' => $codePostal,
                ':ville' => $ville,
                ':region' => $region
            ]);
            
            // Récupérer l'ID de la nouvelle adresse
            $newAdresseId = $pdo->lastInsertId();
            
            // Lier l'adresse au vendeur
            $stmtLink = $pdo->prepare("UPDATE _vendeur SET idAdresse = :idAdresse WHERE codeVendeur = :id");
            $stmtLink->execute([
                ':idAdresse' => $newAdresseId,
                ':id' => $code_vendeur
            ]);
        }
        
        $_SESSION['success'] = isset($_SESSION['success']) ? $_SESSION['success'] : "Profil mis à jour avec succès.";
        
    } catch (PDOException $e) {
        $_SESSION['error'] = "Erreur lors de la mise à jour : " . $e->getMessage();
    }
    
    header('Location: ../views/backoffice/compteVendeur.php');
    exit();
}

function validerMotDePasse($mdp) {
    // Longueur minimale de 12 caractères
    if (strlen($mdp) < 12) {
        return false;
    }
    
    // Au moins une minuscule et une majuscule
    if (!preg_match('/[a-z]/', $mdp) || !preg_match('/[A-Z]/', $mdp)) {
        return false;
    }
    
    // Au moins un chiffre
    if (!preg_match('/[0-9]/', $mdp)) {
        return false;
    }
    
    // Au moins un caractère spécial
    if (!preg_match('/[^a-zA-Z0-9]/', $mdp)) {
        return false;
    }
    
    return true;
}
?>
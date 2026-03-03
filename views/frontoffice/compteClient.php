<?php
session_start();
require_once '../../controllers/pdo.php';
require_once '/var/www/html/vendor/autoload.php';

use OTPHP\TOTP;

if (!isset($_SESSION['user_id'])) {
    header("Location: ../frontoffice/connexionClient.php");
    exit();
}

// On récupère l'id du client
$id_client = $_SESSION['user_id'];

// Fonction de chiffrement
function chiffrement($data) {
    $key = 'la_super_cle_secrete';
    $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
    $encrypted = openssl_encrypt($data, 'aes-256-cbc', $key, 0, $iv);
    return base64_encode($iv . $encrypted);
}

function dechiffrement($data) {
    $key = 'la_super_cle_secrete';
    $data = base64_decode($data);
    $iv_length = openssl_cipher_iv_length('aes-256-cbc');
    $iv = substr($data, 0, $iv_length);
    $encrypted = substr($data, $iv_length);
    return openssl_decrypt($encrypted, 'aes-256-cbc', $key, 0, $iv);
}

// Traitement de l'activation/désactivation de l'A2F
$data = json_decode(file_get_contents("php://input"), true);

if (isset($data['activate'])) {
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'Non authentifié']);
        exit;
    }

    $activate = $data['activate'] ? 1 : 0;
    
    if ($activate == 1) {
        // Générer un nouveau secret pour l'A2F
        $totp = TOTP::create();
        $totp->setLabel($_SESSION['user_id']);
        $totp->setIssuer('MonSite');
        
        $secret = $totp->getSecret();
        $secret_chiffre = chiffrement($secret);
        
        // URL pour le QR code
        $otpauthUrl = $totp->getProvisioningUri();
        
        // Mettre à jour la BDD
        $sql = "UPDATE _client SET otp_enabled = 1, otp_secret = ? WHERE idClient = ?";
        $stmt = $pdo->prepare($sql);
        $success = $stmt->execute([$secret_chiffre, $_SESSION['user_id']]);
        
        echo json_encode([
            'success' => $success,
            'otpauthUrl' => $otpauthUrl,
            'secret' => $secret
        ]);
    } else {
        // Désactiver l'A2F
        $sql = "UPDATE _client SET otp_enabled = 0, otp_secret = NULL WHERE idClient = ?";
        $stmt = $pdo->prepare($sql);
        $success = $stmt->execute([$_SESSION['user_id']]);
        
        echo json_encode(['success' => $success]);
    }
    exit;
}

// On récupère l'id de son adresse
$stmt = $pdo->prepare("SELECT idAdresse, otp_enabled FROM saedb._client WHERE idClient = :idClient");
$stmt->execute([":idClient" => $id_client]);
$client = $stmt->fetch(PDO::FETCH_ASSOC);
$idAdresse = $client['idAdresse'] ?? null;
$otp_enabled = $client['otp_enabled'] ?? 0;

// Si il n'a pas d'adresse on lui en créer une à null 
if (!$idAdresse) {
    $pdo->prepare("
        INSERT INTO saedb._adresseClient 
        (adresse, region, codePostal, ville, pays, complementAdresse)
        VALUES (NULL, NULL, NULL, NULL, NULL, NULL)
    ")->execute();

    $idAdresse = $pdo->lastInsertId();

    $pdo->prepare("
        UPDATE saedb._client 
        SET idAdresse = :idAdresse 
        WHERE idClient = :idClient
    ")->execute([
        ":idAdresse" => $idAdresse,
        ":idClient"  => $id_client
    ]);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // on récupère toutes les infos du formulaire
    $pseudo        = $_POST['pseudo'];
    $nom           = $_POST['nom'];
    $prenom        = $_POST['prenom'];
    $email         = $_POST['email'];
    $dateNaissance = $_POST['dateNaissance'];
    $telephone     = $_POST['telephone'];
    $codePostal    = $_POST['codePostal'];
    $adresse1      = $_POST['adresse1'];
    $adresse2      = $_POST['adresse2'];
    $pays          = $_POST['pays'];
    $ville         = $_POST['ville'];

    // Pour modifier les anciennes infos par les nouvelles
    $stmt = $pdo->prepare("
        UPDATE saedb._client 
        SET pseudo = :pseudo,
            nom = :nom,
            prenom = :prenom,
            email = :email,
            dateNaissance = :dateNaissance,
            noTelephone = :telephone,
            idAdresse = :idAdresse
        WHERE idClient = :idClient
    ");

    $stmt->execute([
        ":pseudo"        => $pseudo,
        ":nom"           => $nom,
        ":prenom"        => $prenom,
        ":email"         => $email,
        ":dateNaissance" => $dateNaissance,
        ":telephone"     => $telephone,
        ":idAdresse"     => $idAdresse,
        ":idClient"      => $id_client
    ]);

    $stmt = $pdo->prepare("
        UPDATE saedb._adresseClient 
        SET adresse = :adresse1,
            pays = :pays,
            ville = :ville,
            codePostal = :codePostal,
            complementAdresse = :adresse2
        WHERE idAdresse = :idAdresse
    ");

    $stmt->execute([
        ":adresse1"   => $adresse1,
        ":pays"       => $pays,
        ":ville"      => $ville,
        ":codePostal" => $codePostal,
        ":adresse2"   => $adresse2,
        ":idAdresse"  => $idAdresse
    ]);
}

// verification et upload de la nouvelle photo de profil
$photoPath = '/var/www/html/images/photoProfilClient/photo_profil'.$id_client;

$extensionsPossibles = ['png', 'jpg', 'jpeg', 'webp', 'svg'];
$AncienneExtension = '';

// On parcourt toutes les extensions possibles
foreach ($extensionsPossibles as $ext) {
    if (file_exists($photoPath . '.' . $ext)) {
        $AncienneExtension = '.' . $ext;
        break;
    }
}

// Vérifie si un fichier a été envoyé via le formulaire et qu'il n'est pas vide
if (isset($_FILES['photoProfil']) && $_FILES['photoProfil']['tmp_name'] != '') {
        
    // Si une ancienne photo existe, on la supprime pour la remplacer
    if (file_exists($photoPath . $AncienneExtension)) {
        unlink($photoPath . $AncienneExtension); 
    }
    // Récupère l'extension du nouveau fichier
    $extension = pathinfo($_FILES['photoProfil']['name'], PATHINFO_EXTENSION);
    $extension = '.'.$extension;

    // Déplace le fichier temporaire téléchargé vers le dossier des photos de profil
    move_uploaded_file($_FILES['photoProfil']['tmp_name'], $photoPath.$extension);
} else {
    // Si aucun nouveau fichier n'est envoyé, on garde l'extension de l'ancienne photo
    $extension = $AncienneExtension;
}   

// on recupère les infos du user pour les afficher
$stmt = $pdo->prepare("SELECT * FROM saedb._client WHERE idClient = ?");
$stmt->execute([$id_client]);
$client = $stmt->fetch(PDO::FETCH_ASSOC);

$pseudo = $client['pseudo'] ?? '';
$prenom = $client['prenom'] ?? '';
$nom = $client['nom'] ?? '';
$dateNaissance = $client['dateNaissance'] ?? '';
$email = $client['email'] ?? '';
$noTelephone = $client['noTelephone'] ?? '';

$stmt = $pdo->prepare("SELECT * FROM saedb._adresseClient WHERE idAdresse = ?");
$stmt->execute([$idAdresse]);
$adresse = $stmt->fetch(PDO::FETCH_ASSOC);

$adresse1 = $adresse['adresse'] ?? '';
$adresse2 = $adresse['complementAdresse'] ?? '';
$codePostal = $adresse['codePostal'] ?? '';
$ville = $adresse['ville'] ?? '';
$pays = $adresse['pays'] ?? '';

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="/public/images/logoBackoffice.svg">
    <title>Mon Compte</title>
    <link rel="stylesheet" href="../../public/style.css">
    <style>
        .qr-code-popup {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }
        
        .qr-code-content {
            background: white;
            padding: 30px;
            border-radius: 10px;
            text-align: center;
            max-width: 400px;
            width: 90%;
        }
        
        .qr-code-content h2 {
            margin-bottom: 20px;
            color: #333;
        }
        
        #qrcode-container {
            display: flex;
            justify-content: center;
            margin: 20px 0;
        }
        
        #qrcode-container img {
            max-width: 250px;
            height: auto;
        }
        
        #closePopup {
            background: #4CAF50;
            color: white;
            border: none;
            padding: 10px 30px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            margin-top: 20px;
        }
        
        #closePopup:hover {
            background: #45a049;
        }
        
        .secret-text {
            background: #f5f5f5;
            padding: 10px;
            border-radius: 5px;
            margin: 10px 0;
            word-break: break-all;
            font-family: monospace;
        }
    </style>
</head>
<body>
    <?php include 'partials/headerConnecte.php'; ?>

    <?php if (isset($_GET['error'])): ?>
        <?php $error = $_GET['error']; ?>
        <?php if ($error == 1): ?>
            <p class='erreur'>Ancien mot de passe incorrect</p>
        <?php elseif ($error == 2): ?>
            <p class='erreur'>Les mots de passe ne correspondent pas</p>
        <?php endif; ?>
    <?php endif; ?>

    <main class="mainCompteClient">
        <form method="POST" enctype="multipart/form-data" action="">
            <div id="titreCompte">
                <div class="photo-container">
                    <?php 
                        if (file_exists($photoPath . $extension)) {
                            echo '<img src="/images/photoProfilClient/photo_profil'.$id_client.$extension.'?v='.time().'" alt="photoProfil" id="imageProfile">';
                        } else {
                            echo '<img src="../../public/images/profil.png?v='.time().'" alt="photoProfil" id="imageProfile">';
                        }
                    ?>
                </div>
                <h1>Mon Compte</h1>
            </div>

            <section>
                <article>
                    <div>
                        <label>Pseudo</label>
                        <p><?php echo htmlspecialchars($pseudo ?? ''); ?></p>
                    </div>
                    <div>
                        <label>Prénom</label>
                        <p><?php echo htmlspecialchars($prenom ?? ''); ?></p>
                    </div>
                    <div>
                        <label>Nom</label>
                        <p><?php echo htmlspecialchars($nom ?? ''); ?></p>
                    </div>
                    <div>
                        <label>Date de naissance</label>
                        <p><?php echo htmlspecialchars($dateNaissance ?? ''); ?></p>
                    </div>
                </article>

                <article>
                    <div>
                        <label>Adresse</label>
                        <p><?php echo htmlspecialchars($adresse1 ?? ''); ?></p>
                    </div>
                    <div>
                        <label>Complément d'adresse</label>
                        <p><?php echo htmlspecialchars($adresse2 ?? ''); ?></p>
                    </div>
                    <div class="double-champ">
                        <div>
                            <label>Code Postal</label>
                            <p><?php echo htmlspecialchars($codePostal ?? ''); ?></p>
                        </div>
                        <div>
                            <label>Ville</label>
                            <p><?php echo htmlspecialchars($ville ?? ''); ?></p>
                        </div>
                    </div>
                    <div>
                        <label>Pays</label>
                        <p><?php echo htmlspecialchars($pays ?? ''); ?></p>
                    </div>
                </article>

                <article>
                    <div>
                        <label>Numéro de téléphone</label>
                        <p><?php echo htmlspecialchars($noTelephone ?? ''); ?></p>
                    </div>
                    <div>
                        <label>Email</label>
                        <p><?php echo htmlspecialchars($email ?? ''); ?></p>
                    </div>
                </article> 
            </section>

            <div id="buttonsCompte">
                <button type="button" onclick="popUpSupprimerMdp(<?php echo $id_client ?>)" class="boutonSupprimerMdp">Supprimer mon compte</button>
                <button type="button" onclick="popUpModifierMdp()" class="boutonModifierMdp">Modifier le mot de passe</button>
                <button class="boutonAnnuler" type="button" onclick="boutonAnnuler()">Annuler</button>
                <button type="button" class="boutonModiferProfil">Modifier</button>
                <div class="authenTwofacts">
                    <label for="remember_me">Activer l'authentification à deux facteurs</label>
                    <input type="checkbox" id="remember_me" name="remember_me" <?php echo $otp_enabled ? 'checked' : ''; ?>>
                </div>
            </div>
        </form>
    </main>
    
    <?php include 'partials/footerConnecte.php'; ?>

    <?php 
        $stmt = $pdo->prepare("SELECT mdp FROM saedb._client WHERE idClient = ?");
        $stmt->execute([$id_client]);
        $tabMdp = $stmt->fetch(PDO::FETCH_ASSOC);
        $mdp = $tabMdp['mdp'] ?? '';
    ?>
    
    <script>
        const mdp = <?php echo json_encode($mdp); ?>;
    </script>
    <script src="../scripts/frontoffice/compteClient.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/qrcode/build/qrcode.min.js"></script>
    <!-- <script src="../scripts/frontoffice/connexionClient.js"></script> --> 
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const a2f = document.getElementById('remember_me');
            
            if (!a2f) {
                console.error('Checkbox A2F non trouvée');
                return;
            }

            a2f.addEventListener("change", function() {
                if (this.checked) {
                    console.log("Activation de l'authentification à deux facteurs");
                    this.disabled = true;

                    fetch(window.location.href, {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                        },
                        body: JSON.stringify({ activate: true }),
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Erreur réseau');
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            // Créer et afficher la popup
                            const qrCodePopup = document.createElement("div");
                            qrCodePopup.classList.add("qr-code-popup");
                            qrCodePopup.innerHTML = `
                                <div class="qr-code-content">
                                    <h2>Scannez ce QR code avec votre application d'authentification</h2>
                                    <div id="qrcode-container"></div>
                                    <p>Ou saisissez manuellement cette clé secrète :</p>
                                    <div class="secret-text">${data.secret}</div>
                                    <button id="closePopup">Fermer</button>
                                </div>
                            `;
                            
                            document.body.appendChild(qrCodePopup);
                            
                            // Générer le QR code
                            const qrcodeContainer = qrCodePopup.querySelector('#qrcode-container');
                            
                            setTimeout(() => {
                                if (typeof QRCode !== 'undefined') {
                                    QRCode.toCanvas(qrcodeContainer, data.otpauthUrl, {
                                        width: 250,
                                        height: 250
                                    }, function (error) {
                                        if (error) {
                                            console.error(error);
                                            qrcodeContainer.innerHTML = '<p style="color: red;">Erreur de chargement du QR code</p>';
                                        }
                                    });
                                } else {
                                    console.error("Bibliothèque QRCode non chargée");
                                    qrcodeContainer.innerHTML = '<p style="color: red;">Erreur de chargement du QR code</p>';
                                }
                            }, 0);

                            // Fermeture de la popup
                            const closeButton = qrCodePopup.querySelector("#closePopup");
                            closeButton.addEventListener("click", function() {
                                qrCodePopup.remove();
                            });
                        } else {
                            alert("Une erreur est survenue lors de l'activation.");
                            this.checked = false;
                        }
                    })
                    .catch(error => {
                        console.error("Erreur:", error);
                        alert("Une erreur est survenue lors de l'activation.");
                        this.checked = false;
                    })
                    .finally(() => {
                        this.disabled = false;
                    });
                } else {
                    console.log("Désactivation de l'authentification à deux facteurs");
                    this.disabled = true;

                    if (confirm('Êtes-vous sûr de vouloir désactiver l\'authentification à deux facteurs ?')) {
                        fetch(window.location.href, {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/json",
                            },
                            body: JSON.stringify({ activate: false }),
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                alert("L'authentification à deux facteurs a été désactivée.");
                            } else {
                                alert("Une erreur est survenue lors de la désactivation.");
                                this.checked = true;
                            }
                        })
                        .catch(error => {
                            console.error("Erreur:", error);
                            alert("Une erreur est survenue lors de la désactivation.");
                            this.checked = true;
                        })
                        .finally(() => {
                            this.disabled = false;
                        });
                    } else {
                        this.checked = true;
                        this.disabled = false;
                    }
                }
            });
        });
    </script>
</body>
</html>
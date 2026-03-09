<?php
    session_start();
    require_once "../../controllers/pdo.php";
    require_once "../../controllers/a2f_helpers.php";
    require_once '/var/www/html/vendor/autoload.php';

    use OTPHP\TOTP;

    $data = json_decode(file_get_contents('php://input'), true);

    if (isset($data['checkBlock']) && isset($_SESSION['bo_pending_vendeur_id'])) {
        $blockedUntil = $_SESSION['bo_otp_blocked_until'] ?? 0;
        $currentTime = time();

        if ($blockedUntil > $currentTime) {
            echo json_encode([
                'blocked' => true,
                'remainingTime' => $blockedUntil - $currentTime
            ]);
        } else {
            echo json_encode(['blocked' => false]);
        }
        exit;
    }

    if (isset($data['otp']) && isset($_SESSION['bo_pending_vendeur_id'])) {
        $code = $data['otp'];

        if (!isset($_SESSION['bo_otp_failed_attempts'])) {
            $_SESSION['bo_otp_failed_attempts'] = 0;
        }
        if (!isset($_SESSION['bo_otp_blocked_until'])) {
            $_SESSION['bo_otp_blocked_until'] = 0;
        }

        $blockDuration = 30;
        $currentTime = time();

        if ($_SESSION['bo_otp_blocked_until'] > $currentTime) {
            echo json_encode([
                'success' => false,
                'blocked' => true,
                'remainingTime' => $_SESSION['bo_otp_blocked_until'] - $currentTime,
                'message' => 'Trop de tentatives échouées. Veuillez patienter.'
            ]);
            exit;
        }

        $sql = "SELECT otp_secret, mdp FROM _vendeur WHERE codeVendeur = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$_SESSION['bo_pending_vendeur_id']]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result && !empty($result['otp_secret'])) {
            $secret = a2f_decrypt($result['otp_secret']);
            $totp = TOTP::create($secret);
            $isValid = $totp->verify($code);

            if ($isValid) {
                session_regenerate_id(true);
                $_SESSION['session_id'] = session_id();
                $_SESSION['id'] = $_SESSION['bo_pending_vendeur_id'];
                $_SESSION['pass'] = $result['mdp'];

                unset($_SESSION['bo_a2f_required'], $_SESSION['bo_pending_vendeur_id']);
                $_SESSION['bo_otp_failed_attempts'] = 0;
                $_SESSION['bo_otp_blocked_until'] = 0;

                echo json_encode(['success' => true]);
                exit;
            }
        }

        $_SESSION['bo_otp_failed_attempts']++;

        if ($_SESSION['bo_otp_failed_attempts'] >= 3) {
            $_SESSION['bo_otp_blocked_until'] = $currentTime + $blockDuration;
            $_SESSION['bo_otp_failed_attempts'] = 0;

            echo json_encode([
                'success' => false,
                'blocked' => true,
                'remainingTime' => $blockDuration,
                'message' => 'Trop de tentatives échouées. Veuillez patienter ' . $blockDuration . ' secondes.'
            ]);
            exit;
        }

        echo json_encode([
            'success' => false,
            'blocked' => false,
            'attemptsLeft' => 3 - $_SESSION['bo_otp_failed_attempts'],
            'message' => 'Code incorrect'
        ]);
        exit;
    }

    $isA2FPending = !empty($_SESSION['bo_a2f_required']) && !empty($_SESSION['bo_pending_vendeur_id']);
    $flashMessage = $_SESSION['message'] ?? '';

    if (!$isA2FPending) {
        session_unset();
        session_destroy();
        session_start();
        if ($flashMessage !== '') {
            $_SESSION['message'] = $flashMessage;
        }
        setcookie(session_name(), '', time() - 3600, '/');
    }

    $currentPage = basename(__FILE__);
    $message = $_SESSION['message'] ?? '';
    unset($_SESSION['message']);

    $popupA2f = $isA2FPending;
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../public/style.css">
    <title>Alizon</title>
</head>

<body class="backoffice nonConnecte">
    <?php require_once "./partials/header.php"; ?>

    <main class="connexionVendeur">
        <img class="triskiel" src="../../public/images/triskiel gris.svg" alt="">

        <div class="pdp_title">
            <div class="cercle-pdp">
                <img src="../../public/images/pdp_user.svg" alt="Avatar"
                    onerror="this.style.display='none'; this.nextElementSibling.style.display='block'">
            </div>
            <h1>Connexion à votre compte vendeur Alizon</h1>
        </div>

        <div class="information_connexion">
            <form method="post" class="form-vendeur" id="monForm" action="../../controllers/connexionCompteVendeur.php">

                <?php if (!empty($message)) : ?>
                <div class="alert alert-danger text-center w-100 mb-3" role="alert">
                    <?= $message ?>
                </div>
                <?php endif; ?>

                <div class="inputs-container">
                    <div class="mb-4">
                        <input type="text" id="pseudo" name="pseudo" placeholder="Nom d'utilisateur" required
                            class="form-control custom-input">
                    </div>

                    <div class="mb-2">
                        <input type="password" id="mdp" name="mdp" placeholder="Mot de passe" required
                            class="form-control custom-input">
                    </div>
                </div>

                <?php
                    if (isset($_GET['error'])) {
                        $message = match ($_GET['error']) {
                            "1" => "Identifiants invalides",
                            "3" => "Session expirée",
                            default => "Erreur inattendue"
                        };
                        echo "<p id='error'>$message</p>";
                    }
                ?>

                <div class="liens-utiles">
                    <a href="CreerCompteVendeur.php">Pas encore vendeur ? Inscrivez vous ici</a>
                </div>

                <div class="actions">
                    <button type="submit" id="btnConnexion" class="btn_connexion" disabled>Se connecter</button>
                </div>
            </form>

            <?php if ($popupA2f): ?>
                <div class="bodyPopupA2f">
                    <div class="popupA2f">
                        <div class="croixFermerLaPage" onclick="fermerPopupA2F()">
                            <div></div><div></div>
                        </div>
                        <h1>Authentification à double facteur</h1>
                        <p style="margin-bottom: 20px; color: #666;">Entrez le code à 6 chiffres de votre application d'authentification</p>
                        <form id="formA2F" data-success-redirect="../../views/backoffice/accueil.php" data-close-redirect="connexion.php">
                            <div>
                                <input type="text" name="num1" id="num1" maxlength="1" pattern="[0-9]" autocomplete="off">
                                <input type="text" name="num2" id="num2" maxlength="1" pattern="[0-9]" autocomplete="off">
                                <input type="text" name="num3" id="num3" maxlength="1" pattern="[0-9]" autocomplete="off">
                                <input type="text" name="num4" id="num4" maxlength="1" pattern="[0-9]" autocomplete="off">
                                <input type="text" name="num5" id="num5" maxlength="1" pattern="[0-9]" autocomplete="off">
                                <input type="text" name="num6" id="num6" maxlength="1" pattern="[0-9]" autocomplete="off">
                            </div>
                            <p class="erreur" id="erreurCodeA2F" style="display: none; color: red; margin-top: 15px;">Code incorrect</p>
                            <button type="submit">Vérifier</button>
                        </form>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <p class="text-footer">
            Alizon, en tant que responsable de traitement, traite les données recueillies à des fins de gestion de la
            relation client,
            gestion des commandes et des livraisons, personnalisation des services, prévention de la fraude, marketing
            et publicité ciblée.
            Pour en savoir plus, reportez-vous à la Politique de protection de vos données personnelles.
        </p>
    </main>

    <?php require_once "./partials/footer.php"; ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // éléments
    const pseudoInput = document.getElementById('pseudo');
    const mdpInput = document.getElementById('mdp');
    const btnConnexion = document.getElementById('btnConnexion');

    // vérification
    function verifierChamps() {
        // Si pseudo et mdp sont remplis
        if (pseudoInput.value.trim() !== "" && mdpInput.value.trim() !== "") {
            btnConnexion.disabled = false; // On active le bouton
        } else {
            btnConnexion.disabled = true; // On désactive le bouton
        }
    }

    // tapage utilisateur
    pseudoInput.addEventListener('input', verifierChamps);
    mdpInput.addEventListener('input', verifierChamps);
});
</script>
<script src="https://cdn.jsdelivr.net/npm/qrcode/build/qrcode.min.js"></script>
<script src="../scripts/backoffice/auth-code.js"></script>
</body>

</html>
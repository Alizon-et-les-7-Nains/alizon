<?php
// Initialisation de la connexion avec le serveur / BDD
require_once "../../controllers/pdo.php";
require_once "../../controllers/prix.php";
session_start();

// ============================================================================
// CONFIGURATION INITIALE
// ============================================================================

// Vérification de l'authentification de l'utilisateur
// Si l'utilisateur n'est pas connecté, redirection vers la page de connexion
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../views/frontoffice/connexionClient.php');
    exit;
}

$idClient = $_SESSION['user_id'];

// ============================================================================
// FONCTIONS DE GESTION DE LA LISTE DE SOUHAIT
// ============================================================================

// Fonction pour récupérer le contenu de la liste de souhait d'un client
function getWishlist($pdo, $idClient) {
    // Récupération du dernier panier de l'utilisateur
    $stmt = $pdo->prepare("SELECT * FROM _listeDeSouhait WHERE idClient = ? ORDER BY dateAjout DESC LIMIT 1");
    $stmt->execute([intval($idClient)]);
    $wishlist = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC): false;
    
    return $wishlist;
}

// Fonction pour rechercher un produit dans la liste de souhait d'un client
function getWishlistProduct($pdo, $idClient, $idProduit) {
    // Récupération du dernier panier de l'utilisateur
    $stmt = $pdo->prepare("SELECT * FROM _listeDeSouhait WHERE idClient = ? AND idProduit = ?");
    $stmt->execute([intval($idClient), intval($idProduit)]);
    $wishlist = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC): false;
    
    return ($wishlist == null);
}

// Fonction pour modifier la liste de souhait
function updateWishlist($pdo, $idClient, $idProduit) {

    $res = getWishlistProduct($pdo, $idClient,$idProduit);

    if ($res) {
        try {
            $stmt = $pdo->prepare("DELETE FROM _listeDeSouhait WHERE idClient = ? AND idProduit = ?");
            $stmt->execute([intval($idClient), intval($idProduit)]);
        } catch(Exception $e) {
            error_log($e);
        }
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO _listeDeSouhait(idClient, idProduit, dateAjout) VALUES (?, ?, ?)");
            $stmt->execute([intval($idClient), intval($idProduit), date('Y-m-d H:i:s')]);
        } catch(Exception $e) {
            error_log($e);
        }
    }

    return $idProduit;
}

// Fonction pour rechercher récupérer les informations d'un produit dans la liste de souhait d'un client
function getProductDetails($pdo, $idProduit) {
    // Récupération du dernier panier de l'utilisateur
    $stmt = $pdo->prepare("SELECT * FROM _produit WHERE idProduit = ?");
    $stmt->execute([intval($idProduit)]);
    $product = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC): false;
    
    return $product;
}


// ============================================================================
// RÉCUPÉRATION DES DONNÉES POUR LA PAGE
// ============================================================================

// recuperation panier courent
$wishlist = getWishlist($pdo, $idClient);

// ============================================================================
// AFFICHAGE DE LA PAGE
// ============================================================================
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../public/style.css">
    <link rel="icon" href="/public/images/logoBackoffice.svg">
    <title>Alizon - Votre liste de souhaits</title>
</head>

<body class="listeDeSouhait">
    <?php include "../../views/frontoffice/partials/headerConnecte.php"; ?>

    <main>
        <h1>Liste de souhaits</h1>

        <section class="ensembleProduits">
            <?php if ($wishlist) : ?>
                <?php foreach ($wishlist as $item) : ?>
                    <?php $productDetails = getProductDetails($pdo, $item['idProduit']); ?>
                    <div class="produit">
                        <h1><?= $productDetails['nom'] ?></h1>
                        <button class="plus" data-stock="<?= $productDetails['stock'] ?>" data-id="<?= $item['idProduit'] ?>">Ajouter au panier</button>
                    </div>
                <?php endforeach; ?>
            <?php else : ?>
                <p>Votre liste de souhaits est vide.</p>
            <?php endif; ?>
        </section>

        <?php require_once '../backoffice/partials/retourEnHaut.php' ?>
    </main>

    <?php include "../../views/frontoffice/partials/footerConnecte.php"; ?>

    <script src="../scripts/frontoffice/paiement-ajax.js"></script>
    <script src="../../public/amd-shim.js"></script>
    <script src="../../public/script.js"></script>
    
</body>


</html>
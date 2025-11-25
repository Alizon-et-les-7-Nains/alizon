<?php 
require_once 'pdo.php';
session_start();

$idProd = $_GET['id']; 
var_dump($_FILES);

// Si il y a eu un formulare de remplie, on fait 2 requêtes 
// La première requête permet de mettre à jour les informations du produit sur lequel le formulaire à été rempli
// Ensuite l'image est envoyée sur le serveur puis
// La deuxième requête permet de mettre à jour l'image d'un produit

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare("UPDATE _produit SET nom = :nom, description = :description, prix = :prix, poids = :poids, mots_cles = :mot_cles WHERE idProduit = :idProduit");
    $imgDeProd = $pdo->prepare("UPDATE _imageDeProduit SET URL = :url WHERE idProduit = :idProduit");
    $stmt->execute([
        ':nom' => $_POST['nom'],
        ':description' => $_POST['description'],
        ':prix' => $_POST['prix'],
        ':poids' => $_POST['poids'],
        ':mot_cles' => $_POST['mots_cles'],
        ':idProduit' => $idProd
    ]);


    $photoPath = '/var/www/html/images/'.$_FILES['url']['name'];


if (isset($_FILES['url']) && $_FILES['url']['tmp_name'] !== '') {

        if (file_exists($photoPath)) {
            unlink($photoPath); // supprime l'ancien fichier
        }   
        move_uploaded_file($_FILES['url']['tmp_name'], $photoPath);
        $fileName = $_FILES['url']['name'];
}
else{
    $sqlUrl = $pdo->prepare("SELECT * FROM _imageDeProduit WHERE idProduit = $idProd");
    $result =  $pdo->query($sqlUrl);
    $fileName = $result->fetch(PDO::FETCH_ASSOC);
}

$url = "/images/$fileName";

try{
    $imgDeProd->execute([
        ':url' => $url,
        ':idProduit' => $idProd
    ]);
}
catch(PDOException $e){
    echo "Erreur SQL : " . $e->getMessage();
    }
}




header("Location: ../views/backoffice/accueil.php"); 
exit();
?>
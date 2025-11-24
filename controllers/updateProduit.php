<?php 
require_once 'pdo.php';
session_start();

$idProd = $_GET['id']; 
// Si il y a eu un formulare de remplie, on fait 2 requêtes 
// La première requête permet de mettre à jour les informations du produit sur lequel le formulaire à été rempli
// La deuxième permet de mettre à jour l'image d'un produit

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

// Construit le chemin de la nouvelle image 
if($_FILES['url']['name']){
    $fileName = $_FILES['url']['name'];
    move_uploaded_file($_FILES['url']['name'], "/var/www/html/images/" . $_FILES['url']['name']);

}
else{
    $sqlUrl = $pdo->prepare("SELECT * FROM _imageDeProduit WHERE idProduit = :idProduit");
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
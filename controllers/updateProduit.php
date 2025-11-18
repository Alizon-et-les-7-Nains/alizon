<?php $stmt = $pdo->prepare("UPDATE produits SET nom = ?, description = ?, prix = ?, poids = ?, mots_cles = ?,  WHERE id = ?");
$stmt->execute([
    $_POST['nom'],
    $_POST['description'],
    $_POST['prix'],
    $_POST['poids'],
    $_POST['mots_cles'],
    $productId
]);?>
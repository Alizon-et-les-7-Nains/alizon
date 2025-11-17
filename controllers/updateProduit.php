<?php $stmt = $pdo->prepare("UPDATE produits SET nom = ?, prix = ?, poids = ?, mots_cles = ?, resume = ? WHERE id = ?");
$stmt->execute([
    $_POST['nom'],
    $_POST['prix'],
    $_POST['poids'],
    $_POST['mots_cles'],
    $_POST['resume'],
    $productId
]);?>
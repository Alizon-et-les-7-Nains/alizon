<?php
session_start();

if (!empty($_SESSION['photo'])) {
    header('Content-Type: image/jpeg');
    header('Content-Length: ' . strlen($_SESSION['photo']));
    echo $_SESSION['photo'];
    exit;
}

// Image par défaut si aucune photo
header('Content-Type: image/png');
readfile('../../public/images/defaultImageProduit.png');
exit;
?>
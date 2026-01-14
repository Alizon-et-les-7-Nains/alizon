<?php
require_once 'pdo.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (empty($_POST['mail'])) {
        die("Mail non reçu");
    }

    $mail = $_POST['mail'];
    $codeVerif = (string) random_int(1000, 9999);

    $title = "Récupération de mot de passe Alizon";
    $message = "Code de vérification : $codeVerif";

    mail($mail, $title, $message);

    header("Location: ../views/frontoffice/mdpOublie.php?mail=" . urlencode($mail));
    exit;
}

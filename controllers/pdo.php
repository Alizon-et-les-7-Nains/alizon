<?php
include('../../../config/config.php');
try {
    $pdo = new PDO("$driver:host=$server;dbname=$dbname", $user, $pass);
} catch (PDOException $e) {
    die();
}
?>

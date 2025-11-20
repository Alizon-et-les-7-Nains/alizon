<?php
include('/var/www/config/config.php');
try {
    $pdo = new PDO("$driver:host=$server;dbname=$dbname", 
            $user, $pass);
} catch (PDOException $e) {
    print "Erreur !: " . $e->getMessage() . "<br/>";
    die();
}
?>

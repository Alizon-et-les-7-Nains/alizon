<?php
include('../../config/config.php');
try {
    $dbh = new PDO("$driver:host=$server;dbname=$dbname", 
            $user, $pass);
    foreach($dbh->query('SELECT * from saedb._client', 
                        PDO::FETCH_ASSOC) 
                as $row) {
        echo "<pre>";
        print_r($row);
        echo "</pre>";
    }
    $dbh = null;
} catch (PDOException $e) {
    print "Erreur !: " . $e->getMessage() . "<br/>";
    die();
}
?>

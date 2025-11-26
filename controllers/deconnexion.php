<?php
    session_start();  
    session_unset();
    session_destroy();
    setcookie(session_name(), '', time() - 3600, '/');
    
    header('Location: ../views/frontoffice/accueilDeconnecte.php');
    exit();
?>
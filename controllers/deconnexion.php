<?php
    // Suppression et destruction de la session ainsi que des cookies
    session_start();  
    session_unset();
    session_destroy();
    setcookie(session_name(), '', time() - 3600, '/');
    
    header('Location: ../views/frontoffice/accueilDeconnecte.php');
    exit();
?>
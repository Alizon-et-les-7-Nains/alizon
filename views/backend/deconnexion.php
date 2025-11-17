<?php
    //Permet de détruire la session actuelle puis de retourner à l'accueil

    session_start();  
    session_unset();
    session_destroy();
    setcookie(session_name(), '', time() - 3600, '/');
    
    header('Location: ../frontoffice/accueilDeconnecte.php');
    exit();
?>
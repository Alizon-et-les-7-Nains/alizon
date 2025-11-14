<?php
    session_start();  
    $id_session = session_id();
    $_SESSION['id_session'] = $id_session;
    header('Location: ../frontoffice/accueilConnecte.php');
    exit();
?>
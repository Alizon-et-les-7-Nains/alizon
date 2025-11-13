<?php
    session_start();
    $id_session = session_id();
    if($id_session!==''){
        $_SESSION[$id_session]=$id_session;
    }
    else{
        $_SESSIONS[$id_session]=30;
    }

    header('Location: ../../frontoffice/page404.php');
    exit();
?>
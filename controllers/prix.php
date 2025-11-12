<?php

function prix($prix) {
    $prix = str_replace('.', ',', (String)$prix); 
    if (explode(',', $prix)[1]) {
        if (strlen(explode(',', $prix)[1]) == 1) {
            $prix .= "0";
        }
    }
    return $prix;
}

?>
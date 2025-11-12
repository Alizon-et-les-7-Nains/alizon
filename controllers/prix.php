<?php

function prix($prix) {
    $prix = str_replace('.', ',', (String)$prix); 
    $parts = explode(',', $prix);
    
    if (isset($parts[1])) {
        if (strlen($parts[1]) == 1) {
            $prix .= "0";
        }
    } else {
        $prix .= ",00";
    }
    
    return $prix . "€";
}

?>
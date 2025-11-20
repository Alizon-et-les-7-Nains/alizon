<?php

function formatPrice($prix) {
    return number_format((float)$prix, 2, ',', ' ') . "€";
}

?>
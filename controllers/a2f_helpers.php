<?php

if (!function_exists('a2f_encrypt')) {
    function a2f_encrypt($data)
    {
        $key = 'la_super_cle_secrete';
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
        $encrypted = openssl_encrypt($data, 'aes-256-cbc', $key, 0, $iv);
        return base64_encode($iv . $encrypted);
    }
}

if (!function_exists('a2f_decrypt')) {
    function a2f_decrypt($data)
    {
        $key = 'la_super_cle_secrete';
        $decoded = base64_decode($data);
        $ivLength = openssl_cipher_iv_length('aes-256-cbc');
        $iv = substr($decoded, 0, $ivLength);
        $encrypted = substr($decoded, $ivLength);
        return openssl_decrypt($encrypted, 'aes-256-cbc', $key, 0, $iv);
    }
}

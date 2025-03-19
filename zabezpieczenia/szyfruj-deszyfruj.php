<?php

function szyfruj($dane) {
    $klucz = 'oEZnGglFPATfs1LI8LB8+EubkHOMileJfNl7Rt8i0Zw=';
    $klucz_szyfrujacy = base64_decode($klucz);
    $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
    $dane_zaszyfrowane = openssl_encrypt($dane, 'aes-256-cbc', $klucz_szyfrujacy, 0, $iv);
    return base64_encode($dane_zaszyfrowane . '::' . $iv);
} 
function deszyfruj($dane) {
    $klucz = 'oEZnGglFPATfs1LI8LB8+EubkHOMileJfNl7Rt8i0Zw=';
    $klucz_deszyfrujacy = base64_decode($klucz);
    list($dane_zaszyfrowane, $iv) = explode('::', base64_decode($dane), 2);
    return openssl_decrypt($dane_zaszyfrowane, 'aes-256-cbc', $klucz_deszyfrujacy, 0, $iv);
}
?>

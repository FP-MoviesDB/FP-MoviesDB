<?php 

// if (!defined('ABSPATH')) exit;

// function fp_encrypt_url($url) {
//     if (empty($url)) return '';
//     $encryption_key = FP_MOVIES_ENCRYPTION_KEY;
//     $encryption_method = FP_MOVIES_ENCRYPTION_METHOD;
//     $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($encryption_method));
//     $encrypted = openssl_encrypt($url, $encryption_method, $encryption_key, 0, $iv);
//     return urlencode(base64_encode($encrypted . '::' . $iv));
// }

// function fp_decrypt_url($encrypted_url) {
//     $encryption_key = FP_MOVIES_ENCRYPTION_KEY;
//     $encryption_method = FP_MOVIES_ENCRYPTION_METHOD;
//     $decoded_url = base64_decode(urldecode($encrypted_url));
//     $parts = explode('::', $decoded_url);

//     if (count($parts) !== 2) {
//         return false;
//     }

//     list($encrypted_data, $iv) = $parts;

//     if (empty($encrypted_data) || empty($iv)) {
//         return false;
//     }

//     $decrypted_url = openssl_decrypt($encrypted_data, $encryption_method, $encryption_key, 0, $iv);
//     return $decrypted_url !== false ? $decrypted_url : false;
// }

if (!defined('ABSPATH')) exit;

function base62_encode($data) {
    $chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $base = strlen($chars);
    $hex = bin2hex($data);
    $dec = '0';

    for ($i = 0; $i < strlen($hex); $i++) {
        $dec = bcmul($dec, '16', 0);
        $dec = bcadd($dec, hexdec($hex[$i]), 0);
    }

    // Convert decimal to base62
    $result = '';
    while (bccomp($dec, '0', 0) > 0) {
        $remainder = bcmod($dec, $base);
        $result = $chars[$remainder] . $result;
        $dec = bcdiv($dec, $base, 0);
    }

    return $result;
}


function base62_decode($data) {
    $chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $base = strlen($chars);
    $dec = '0';

    // Convert base62 to decimal using bcmath
    for ($i = 0; $i < strlen($data); $i++) {
        $dec = bcmul($dec, $base, 0);
        $dec = bcadd($dec, strpos($chars, $data[$i]), 0);
    }

    // Convert decimal to hex
    $hex = '';
    while (bccomp($dec, '0', 0) > 0) {
        $remainder = bcmod($dec, '256');
        $hex = str_pad(dechex($remainder), 2, '0', STR_PAD_LEFT) . $hex;
        $dec = bcdiv($dec, '256', 0);
    }

    return hex2bin($hex);
}


function fp_encrypt_url($url) {
    if (empty($url)) return '';

    $encryption_key = FP_MOVIES_ENCRYPTION_KEY;
    $encryption_method = FP_MOVIES_ENCRYPTION_METHOD;
    $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($encryption_method));
    $encrypted = openssl_encrypt($url, $encryption_method, $encryption_key, 0, $iv);
    $data = $encrypted . '::' . base64_encode($iv);
    $encoded = base62_encode($data);
    $safe_url = rawurlencode($encoded);
    return $safe_url;
}

function fp_decrypt_url($encrypted_url) {
    $encryption_key = FP_MOVIES_ENCRYPTION_KEY;
    $encryption_method = FP_MOVIES_ENCRYPTION_METHOD;

    $decoded_url = rawurldecode($encrypted_url);

    $decoded_data = base62_decode($decoded_url);
    $parts = explode('::', $decoded_data);

    if (count($parts) !== 2) {
        return false; // Invalid format
    }

    list($encrypted_data, $iv_base64) = $parts;

    $iv = base64_decode($iv_base64); // Decode Base64 IV

    if (empty($encrypted_data) || strlen($iv) !== openssl_cipher_iv_length($encryption_method)) {
        return false; // Invalid data or IV length
    }

    $decrypted_url = openssl_decrypt($encrypted_data, $encryption_method, $encryption_key, 0, $iv);
    return $decrypted_url !== false ? $decrypted_url : false;
}

?>

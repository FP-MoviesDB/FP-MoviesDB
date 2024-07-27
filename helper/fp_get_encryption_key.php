<?php

if (!defined('ABSPATH')) exit;

function generate_encryption_key() {
    // AES-256 key length is 32 bytes (256 bits)
    $key = openssl_random_pseudo_bytes(32);
    return base64_encode($key);
}
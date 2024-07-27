<?php

if (!defined('ABSPATH')) exit;

function process_sora_encryption($url)
{
    if (function_exists('sora_client_url')) {
        return sora_client_url($url);
    } else {
        return false;
    }
}
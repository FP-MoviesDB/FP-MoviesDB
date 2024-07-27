<?php

if (!defined('ABSPATH')) exit;

function process_gyani_encryption($url, $settings)
{
    $api_token = $settings['mtg_gyanilink_api_token'] ?? '';
    if (!empty($api_token)) {
        $long_url = urlencode($url);
        $api_url = "https://gyanilinks.com/api?api=$api_token&url=$long_url";
        $result = @json_decode(file_get_contents($api_url), TRUE);
        if ($result && isset($result['status']) && $result['status'] === 'error') {
            return false;
        } else {
            return $result['shortenedUrl'] ?? false;
        }
    }
    return false;
}
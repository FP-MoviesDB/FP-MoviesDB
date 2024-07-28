<?php

if (!defined('ABSPATH')) exit;

// function process_gyani_encryption($url, $settings)
// {
//     $api_token = $settings['mtg_gyanilink_api_token'] ?? '';
//     if (!empty($api_token)) {
//         $long_url = urlencode($url);
//         $api_url = "https://gyanilinks.com/api?api=$api_token&url=$long_url";
//         $result = @json_decode(file_get_contents($api_url), TRUE);
//         if ($result && isset($result['status']) && $result['status'] === 'error') {
//             return false;
//         } else {
//             return $result['shortenedUrl'] ?? false;
//         }
//     }
//     return false;
// }

function process_gyani_encryption($url, $settings) {
    $api_token = $settings['mtg_gyanilink_api_token'] ?? '';
    if (!empty($api_token)) {
        $long_url = urlencode($url);
        $api_url = "https://gyanilinks.com/api?api=$api_token&url=$long_url";

        // Use wp_remote_get to fetch data from the API
        $response = wp_remote_get($api_url);

        // Check if the request was successful
        if (is_wp_error($response)) {
            // error_log('Error contacting Gyani Links API: ' . $response->get_error_message());
            return false; // Return false if there was an error
        }

        // Decode the response body
        $body = wp_remote_retrieve_body($response);
        $result = json_decode($body, true);

        // Check the result and handle accordingly
        if ($result && isset($result['status']) && $result['status'] === 'error') {
            return false;
        } else {
            return $result['shortenedUrl'] ?? false;
        }
    }
    return false;
}

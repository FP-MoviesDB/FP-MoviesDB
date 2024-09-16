<?php

if (!defined('ABSPATH')) exit;

// Function to validate FilePress API key
function validate_fp_api_key($input)
{
    // Access other form options
    $key_type = (isset($_POST['mtg_fp_key_type']) && !empty($_POST['mtg_fp_key_type'])) ? sanitize_text_field($_POST['mtg_fp_key_type']) : get_option('mtg_fp_key_type');
    $global_access = (isset($_POST['mtg_global_access']) && $_POST['mtg_global_access'] === 'on') ? 'true' : 'false';

    if (empty($input)) {
        add_settings_error(
            'mtg_fp_api_key',
            'mtg_fp_api_key_error',
            'FP API Key is required.',
            'error'
        );
        return $input;
    }

    $resURL = FP_MOVIES_FP_BASE_URL . '/movie/1?api_key=' . urlencode($input)
        . '&request_type=' . urlencode($key_type);

    if ($key_type === 'personal') {
        $resURL .= '&show_global_org_files=' . $global_access;
    }

    // error_log($resURL);

    if (function_exists('fp_log_error')) {
        fp_log_error('VR_URL: ' . $resURL);
    }

    $response = wp_remote_get($resURL, array('timeout' => 10));
    // error_log(print_r($response, true));
    if (is_wp_error($response) || wp_remote_retrieve_response_code($response) != 200) {
        add_settings_error(
            'mtg_fp_api_key',
            'mtg_fp_api_key_error',
            'Invalid FP API Key. Please recheck API Key & key type.',
            'error'
        );
        return '';
        // return get_option('mtg_fp_api_key');
    }

    return $input; // Return the sanitized and validated input
}


// Function to validate TMDB API key
function validate_tmdb_api_key($input)
{
    if (empty($input)) {
        add_settings_error(
            'mtg_tmdb_api_key',
            'mtg_tmdb_api_key_error',
            'TMDB API Key is required.',
            'error'
        );
        return $input;
    }

    // USE CONSTANT = FP_MOVIES_TMDB_API_BASE_URL
    $res_url = FP_MOVIES_TMDB_API_BASE_URL . '/authentication?api_key=' . $input;
    $response = wp_remote_get($res_url, array('timeout' => 10));
    if (is_wp_error($response) || wp_remote_retrieve_response_code($response) != 200) {
        add_settings_error(
            'mtg_tmdb_api_key',
            'mtg_tmdb_api_key_error',
            'Invalid TMDB API Key. Please check and try again.',
            'error'
        );
        return '';
        // return get_option('mtg_tmdb_api_key'); // Return the existing option if the key is invalid
    }

    return $input; // Return the sanitized and validated input
}

function mtg_sanitize_hex_color($color)
{
    return sanitize_hex_color($color);
}

function mtg_sanitize_template_settings($input)
{
    $sanitized_input = array();
    foreach ($input as $key => $value) {
        if ($key === 'sTitle_Separator') {
            // Use wp_unslash to preserve spaces
            $sanitized_input[$key] = wp_unslash($value);
        } elseif (is_bool($value)) {
            $sanitized_input[$key] = (bool) $value;
        } else {
            $sanitized_input[$key] = sanitize_text_field($value);
        }
    }
    return $sanitized_input;
}

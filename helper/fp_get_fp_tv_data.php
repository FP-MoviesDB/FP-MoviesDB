<?php

if (!defined('ABSPATH')) exit;

function fetch_fpTVData($tmdb_id)
{
    $request_url = FP_MOVIES_FP_BASE_URL . "/tv/{$tmdb_id}";
    $request_url .= '?api_key=' . FP_MOVIES_FP_API_KEY;
    $fp_apiType = get_option_with_fallback('mtg_fp_key_type', 'personal');
    if ($fp_apiType === 'personal') {
        $mtg_global_access = get_option_with_fallback('mtg_global_access', 'false') === 'on' ? 'true' : 'false';
        $request_url .= '&show_global_org_files=' . $mtg_global_access;
    }
    $request_url .= '&video_meta_data=true&unique=true';
    $request_url .= '&request_type=' . $fp_apiType;

    // if (function_exists('curl_version')) {
    //     $curl = curl_init($request_url);
    //     curl_setopt_array($curl, array(
    //         CURLOPT_RETURNTRANSFER => true,
    //         CURLOPT_ENCODING => '',
    //         CURLOPT_MAXREDIRS => 10,
    //         CURLOPT_TIMEOUT => 30,
    //         CURLOPT_FOLLOWLOCATION => true,
    //         CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    //         CURLOPT_CUSTOMREQUEST => 'GET',
    //     ));
    //     $response = curl_exec($curl);
    //     $data = json_decode($response, TRUE);
    // } else {
    //     $response = file_get_contents($request_url);
    //     $data = json_decode($response, TRUE);
    // }

    // return $data;
    $response = wp_remote_get($request_url, array(
        'timeout'     => 30,
        'redirection' => 10
    ));

    // Check if the request was successful
    if (is_wp_error($response)) {
        // Handle error appropriately
        error_log('Failed to fetch data from API: ' . $response->get_error_message());
        return null; // Or handle the error in another appropriate way
    }

    // Decode the response body
    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);

    return $data;
}

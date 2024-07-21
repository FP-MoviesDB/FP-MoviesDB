<?php

if (!defined('ABSPATH')) exit;

function fp_getAll_IDs()
{
    if (!current_user_can('manage_options') || !isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'fp_get_all_ids_nonce')) {
        wp_send_json_error(array('message' => 'Unauthorized'), 400);
        return;
    }

    $api = FP_MOVIES_FP_BASE_URL . '/latest';
    $per_page = 500;
    $paged = isset($_POST['paged']) ? intval($_POST['paged']) : 1;
    $post_type = $_POST['post_type'];
    $fpkey = get_option('mtg_fp_api_key', FP_MOVIES_FP_API_KEY);
    $fp_key_type = get_option('mtg_fp_key_type');

    $args = [];

    if ($fp_key_type == 'personal') {
        $isGlobalAccess = get_option('mtg_global_access');
        $args['request_type'] = $fp_key_type;
        $args['show_global_org_files'] = ($isGlobalAccess == 'on' ? 'true' : 'false');
    } else {
        $args['request_type'] = $fp_key_type;
    }

    $args['api_key'] = $fpkey;
    $args['genre'] = $post_type;
    $args['page'] = $paged;
    $args['per_page'] = $per_page;

    $max_retries = 3;
    $attempt = 0;
    $success = false;

    $request_url = $api . '?' . http_build_query($args);
    $api_resp = wp_remote_get($request_url);
    $response = wp_remote_retrieve_response_code($api_resp);
    $json = wp_remote_retrieve_body($api_resp);
    $data = json_decode($json, true);

    while ($attempt < $max_retries && !$success) {
        $attempt++;
        if ($response == 200 && !is_wp_error($api_resp)) {
            if ($data['status'] === true) {
                $success = true;
            } else {
                if ($attempt >= $max_retries) {
                    wp_send_json_error(array('message' => 'API Key is invalid or not working after ' . $max_retries . ' attempts'), 400);
                    return;
                }
            }
        } else {
            if ($attempt >= $max_retries) {
                wp_send_json_error(array('message' => 'API Key is invalid or not working after ' . $max_retries . ' attempts'), 400);
                return;
            }
        }
        sleep(1);
    }

    // if ($response != 200 || is_wp_error($api_resp)) {
    //     wp_send_json_error(array('message' => 'API Key is invalid or not working'), 400);
    //     return;
    // }



    $tmdb_ids = [];
    $total_pages = 0;

    if (isset($data['data'])) {
        if (isset($data['data']['fileList'])) {
            foreach ($data['data']['fileList'] as $file) {
                if (isset($file['file']['tmdb_id'])) {
                    $tmdb_ids[] = $file['file']['tmdb_id'];
                }
            }
        }

        if (isset($data['data']['totalResult'])) {
            $total_result = $data['data']['totalResult'];
            $total_pages = ceil($total_result / $per_page);
        }
    }

    wp_send_json_success(array('tmdb_ids' => $tmdb_ids, 'total_pages' => $total_pages, 'current_page' => $paged), 200);

    wp_die();
}


function fp_getRecent_IDs()
{
    if (!current_user_can('manage_options') || !isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'fp_get_all_ids_nonce')) {
        wp_send_json_error(array('message' => 'Unauthorized'), 400);
        return;
    }

    $api = FP_MOVIES_FP_BASE_URL . '/latest';
    $per_page = isset($_POST['number']) ? min(intval($_POST['number']), 500) : 500;  // Maximum 500
    $paged = 1;
    $post_type = $_POST['post_type'];
    $fpkey = get_option('mtg_fp_api_key', FP_MOVIES_FP_API_KEY);
    $fp_key_type = get_option('mtg_fp_key_type');

    $args = [];

    if ($fp_key_type == 'personal') {
        $isGlobalAccess = get_option('mtg_global_access');
        $args['request_type'] = $fp_key_type;
        $args['show_global_org_files'] = ($isGlobalAccess == 'on' ? 'true' : 'false');
    } else {
        $args['request_type'] = $fp_key_type;
    }

    $args['api_key'] = $fpkey;
    $args['genre'] = $post_type;
    $args['page'] = $paged;
    $args['per_page'] = $per_page;

    $max_retries = 3;
    $attempt = 0;
    $success = false;

    $request_url = $api . '?' . http_build_query($args);
    $api_resp = wp_remote_get($request_url);
    $response = wp_remote_retrieve_response_code($api_resp);

    // if ($response != 200 || is_wp_error($api_resp)) {
    //     wp_send_json_error(array('message' => 'API Key is invalid or not working'), 400);
    //     return;
    // }

    $json = wp_remote_retrieve_body($api_resp);
    $data = json_decode($json, true);

    while ($attempt < $max_retries && !$success) {
        $attempt++;
        if ($response == 200 && !is_wp_error($api_resp)) {
            if ($data['status'] === true) {
                $success = true;
            } else {
                if ($attempt >= $max_retries) {
                    wp_send_json_error(array('message' => 'API Key is invalid or not working after ' . $max_retries . ' attempts'), 400);
                    return;
                }
            }
        } else {
            if ($attempt >= $max_retries) {
                wp_send_json_error(array('message' => 'API Key is invalid or not working after ' . $max_retries . ' attempts'), 400);
                return;
            }
        }
        sleep(1);
    }

    $tmdb_ids = [];

    if (isset($data['data'])) {
        if (isset($data['data']['fileList'])) {
            foreach ($data['data']['fileList'] as $file) {
                if (isset($file['file']['tmdb_id'])) {
                    $tmdb_ids[] = $file['file']['tmdb_id'];
                }
            }
        }
    }

    wp_send_json_success(array('tmdb_ids' => $tmdb_ids), 200);

    wp_die();
}

add_action('wp_ajax_fp_getAllIDs', 'fp_getAll_IDs');
add_action('wp_ajax_fp_getRecentIDs', 'fp_getRecent_IDs');

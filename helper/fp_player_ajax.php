<?php

if (!defined('ABSPATH')) exit;

function handle_player_ajax_request()
{
    check_ajax_referer('fp_player_nonce', 'nonce');

    $post_id = isset($_POST['post']) ? $_POST['post'] : null;
    $position = isset($_POST['position']) ? sanitize_text_field($_POST['position']) : null;
    $type = isset($_POST['type']) ? sanitize_text_field($_POST['type']) : null;
    $post_type = isset($_POST['pType']) ? sanitize_text_field($_POST['pType']) : null;

    // Common check for all requests
    if (!$post_id || !$type) {
        wp_send_json_error('Invalid AJAX Request', 400);
        wp_die();
    }

    // Specific checks based on post type
    if ($post_type === 'movie') {
        $position = sanitize_text_field($_POST['position'] ?? null);
        if (!$position) {
            wp_send_json_error('Invalid AJAX Request', 400);
            wp_die();
        }
    } elseif ($post_type === 'tv' && $type !== 'trailer') {
        $season = intval(sanitize_text_field($_POST['season'] ?? null));
        $episode = intval(sanitize_text_field($_POST['episode'] ?? null));
        $position = ($type === 'i') ? sanitize_text_field($_POST['position'] ?? null) : null;

        if (!$season || !$episode || ($type === 'i' && !$position)) {
            wp_send_json_error('Invalid AJAX Request', 400);
            wp_die();
        }
    }

    if (!function_exists('get_all_cached_post_meta')) {
        require_once FP_MOVIES_DIR . 'helper/fp_get_all_meta.php';
    }

    $meta_data = get_movie_tv_post_meta($post_id);
    $player = maybe_unserialize($meta_data['fp_player_data']);
    // error_log('Player Data: ' . print_r($player, true));

    $tmdb_id = $meta_data['fp_tmdb'];

    $is_trailer = $meta_data['fp_trailer'];
    if ($is_trailer) {
        $sub = 2;
    } else {
        $sub = 1;
    }

    if ($type == 'trailer') {
        $url = $meta_data['fp_trailer'];
        $player_type = 'trailer';
    } else if ($type == 'global') {
        $url = FP_MOVIES_GLOBAL_STREAM_URL . '/embed/' . $post_type . '/' . $tmdb_id;
        $player_type = 'global';
    } else if ($type == 'i') {
        if (isset($player[$season][$episode]) && array_key_exists($position - 1, $player[$season][$episode])) {
            $url = $player[$season][$episode][$position - 1]['url'];
        } else {
            $url = false;
        }
        $player_type = 'iframe';
    } else if ($type == 'g') {
        $url = FP_MOVIES_GLOBAL_STREAM_URL . '/embed/' . $post_type . '/' . $tmdb_id . '_' . $season . '_' . $episode;
        $player_type = 'global';
    } else {
        $url = isset($player[$position - $sub]['url']) ? $player[$position - $sub]['url'] : false;
        $player_type = isset($player[$position - $sub]['type']) ? $player[$position - $sub]['type'] : '';
    }

    if (!$url) {
        wp_send_json_error('Invalid URL', 400);
        wp_die();
    }

    $url_iframe = "";

    if ($player_type == 'trailer') {
        $url = trailer_id_iframe_url_embed($url, 0);
    }
    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        wp_send_json_error('Invalid URL', 400);
        wp_die();
    }
    $url_iframe = $url;

    if (!empty($url_iframe)) {
        wp_send_json_success(array('embed_url' => $url_iframe, 'type' => $player_type));
        return;
    }

    wp_send_json_error('Invalid URL', 400);
    wp_die();
}

function trailer_id_iframe_url_embed($id, $autoplay = '0')
{
    if (!empty($id)) {
        if (strpos($id, '[') !== false && strpos($id, ']') !== false) {
            $val = str_replace(array("[", "]"), array('https://www.youtube.com/embed/', '?autoplay=' . $autoplay . '&autohide=1'), $id);
        } else {
            $val = 'https://www.youtube.com/embed/' . $id . '?autoplay=' . $autoplay . '&autohide=1';
        }
        return $val;
    }
}

function handle_fetch_episode_sources()
{

    // check request nonce
    check_ajax_referer('fp_player_nonce', 'nonce');

    $post_id = intval($_POST['post_id']);
    $season = intval($_POST['season']);
    $episode = intval($_POST['episode']);
    $fp_player_data_key = 'fp_cache_playerData_' . $post_id;
    $fp_player_data = fp_get_cache($fp_player_data_key);
    $fp_player_data = maybe_unserialize($fp_player_data);
    // if  ['error' => 'Cache expired'] then reload the page
    // if (isset($fp_player_data['error'])) {
    //     wp_send_json_error(['message' => 'Cache expired'], 400);
    //     wp_die();
    // }
    if (isset($fp_player_data[$season][$episode])) {
        wp_send_json_success(['sources' => $fp_player_data[$season][$episode]]);
    } else {
        // error_log('fp_player_data: ' . print_r($fp_player_data, true));
        // error_log('Current Season Data: ' . print_r($fp_player_data[$season], true));
        wp_send_json_error(['message' => 'No sources found']);
        wp_die();
    }

    wp_die();
}

add_action('wp_ajax_fp_player_ajax', 'handle_player_ajax_request');
add_action('wp_ajax_nopriv_fp_player_ajax', 'handle_player_ajax_request');
add_action('wp_ajax_fetch_episode_sources', 'handle_fetch_episode_sources');
add_action('wp_ajax_nopriv_fetch_episode_sources', 'handle_fetch_episode_sources');

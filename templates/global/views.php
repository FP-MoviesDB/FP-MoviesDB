<?php
if (!defined('ABSPATH')) exit;

function set_post_views($postID) {
    // error_log('Setting post views for post ID: ' . $postID); // Added detailed log
    $count_key = 'mtg_post_views_count';
    $count = get_post_meta($postID, $count_key, true);
    if ($count == '') {
        $count = 0;
        delete_post_meta($postID, $count_key);
        add_post_meta($postID, $count_key, '0');
    } else {
        $count++;
        update_post_meta($postID, $count_key, $count);
    }
}

function track_post_views() {
    // error_log('Tracking post views'); // Added detailed log
    $nonce = isset($_POST['nonce']) ? sanitize_text_field($_POST['nonce']) : '';
    // error_log('Nonce received: ' . $nonce); // Log received nonce

    if (!wp_verify_nonce($nonce, 'fp_views_nonce')) {
        if (function_exists('fp_log_error')) fp_log_error('Nonce verification failed');
        wp_send_json_error('Nonce verification failed', 400);
    }

    if (!isset($_POST['post_id'])) {
        if (function_exists('fp_log_error')) fp_log_error('Invalid post ID');
        wp_send_json_error('Invalid post ID', 400);
    }

    $post_id = intval($_POST['post_id']);
    // error_log('Post ID received: ' . $post_id); // Log received post ID
    set_post_views($post_id);

    wp_send_json_success();
}


add_action('wp_ajax_track_post_views_fp', 'track_post_views');
add_action('wp_ajax_nopriv_track_post_views_fp', 'track_post_views');
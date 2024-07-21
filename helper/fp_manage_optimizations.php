<?php

if (!defined('ABSPATH')) exit;

function fp_disable_optimizations(){
    if (!current_user_can('manage_options') || !isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'disable_opt_nonce')) {
        wp_send_json_error(array('message' => 'Unauthorized'), 400);
        return;
    }
    set_transient('fp_optimizations_disabled', true, 12 * HOUR_IN_SECONDS);
    wp_defer_term_counting(true);
    wp_defer_comment_counting(true);
    wp_suspend_cache_invalidation(true);
    wp_send_json_success(array('message' => 'OD'), 200);
}

function fp_enable_optimizations(){
    if (!current_user_can('manage_options') || !isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'enable_opt_nonce')) {
        wp_send_json_error(array('message' => 'Unauthorized'), 400);
        return;
    }
    wp_defer_term_counting(false);
    wp_defer_comment_counting(false);
    wp_suspend_cache_invalidation(false);
    delete_transient('fp_optimizations_disabled');

    wp_send_json_success(array('message' => 'OE'), 200);
}


add_action('wp_ajax_fp_disableOptimizations', 'fp_disable_optimizations');
add_action('wp_ajax_fp_enableOptimizations', 'fp_enable_optimizations');
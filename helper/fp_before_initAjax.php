<?php

if (!defined('ABSPATH')) exit;

function fp_dismiss_d_notice_handler()
{
    check_ajax_referer('fp_dismiss_notice_nonce', 'nonce');
    $days = isset($_POST['silence_days']) ? intval($_POST['silence_days']) : 7;
    if (current_user_can('manage_options')) {
        set_transient('fp_moviesdb_d_notice', 'waiting', $days * DAY_IN_SECONDS);
        wp_die('Success');
    }
    wp_die('Access Denied');
}


function fp_dismiss_admin_notice_handler()
{
    check_ajax_referer('fp_dismiss_notice_nonce', '_ajax_nonce', true);
    $notice_key = isset($_POST['notice_key']) ? sanitize_text_field($_POST['notice_key']) : '';
    // if (!empty($notice_key)) {
    //     $result = update_user_meta(get_current_user_id(), $notice_key, 'dismissed');
    //     error_log('Notice dismissed: ' . $notice_key . '; Update result: ' . ($result ? 'true' : 'false'));
    // }
    wp_die();
}
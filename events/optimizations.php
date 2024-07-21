<?php

if (!defined('ABSPATH')) exit;

if (!wp_next_scheduled('fp_check_optimizations_status')) {
    wp_schedule_event(time(), 'hourly', 'fp_check_optimizations_status');
}

function fp_check_optimizations_status_handler() {
    if (get_transient('fp_optimizations_disabled')) {
        if (function_exists('fp_log_error')) fp_log_error('Optimizations are disabled');
        wp_defer_term_counting(false);
        wp_defer_comment_counting(false);
        wp_suspend_cache_invalidation(false);
        delete_transient('fp_optimizations_disabled');
    }
}

register_deactivation_hook(__FILE__, 'fp_deactivation');
function fp_deactivation() {
    wp_clear_scheduled_hook('fp_check_optimizations_status');
    if (function_exists('fp_log_error')) fp_log_error('Optimizations check event unscheduled');
    // error_log('Optimizations check event unscheduled');
}
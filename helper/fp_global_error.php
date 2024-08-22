<?php

if (!defined('ABSPATH')) exit;

if (!defined('FP_ERROR_LOG_FILE')) define('FP_ERROR_LOG_FILE', FP_MOVIES_DIR . '/logs/error_logs.txt');

function fp_log_error($message, $context = 'PHP')
{

    if (!FP_MOVIES_LOGS) return;

    if (!file_exists(dirname(FP_ERROR_LOG_FILE))) mkdir(dirname(FP_ERROR_LOG_FILE), 0777, true);
    $logMessage = date('Y-m-d H:i:s') . " - [$context] - $message\n";
    file_put_contents(FP_ERROR_LOG_FILE, $logMessage, FILE_APPEND);

    $lines = file(FP_ERROR_LOG_FILE);
    if (count($lines) > 500) {
        $lines = array_slice($lines, -500);
        file_put_contents(FP_ERROR_LOG_FILE, implode('', $lines));
    }
}

function fp_handle_ajax_log_error()
{
    if (isset($_POST['logMessage']) && isset($_POST['logContext'])) {
        fp_log_error($_POST['logMessage'], $_POST['logContext']);
    }
    wp_die();
}

add_action('wp_ajax_log_javascript_error', 'fp_handle_ajax_log_error');
add_action('wp_ajax_nopriv_log_javascript_error', 'fp_handle_ajax_log_error');

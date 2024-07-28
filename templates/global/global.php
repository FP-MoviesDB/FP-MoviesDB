<?php
if (!defined('ABSPATH')) exit;

function formatBytes($size, $precision = 2)
{
    $base = log($size, 1024);
    $suffixes = array('', 'KB', 'MB', 'GB', 'TB');
    return round(pow(1024, $base - floor($base)), $precision) . ' ' . $suffixes[floor($base)];
}

global $fp_min_m;
wp_enqueue_style('local-poppins-font', esc_url(FP_MOVIES_URL) . 'fonts/poppins' . $fp_min_m . '.css', array(), FP_MOVIES_FILES, 'all');

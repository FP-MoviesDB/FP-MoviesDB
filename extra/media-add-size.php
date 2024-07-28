<?php

if (!defined('ABSPATH')) {
    exit;
}

function fp_sizes_column($cols)
{
    $cols["all_sizes"] = "All Sizes";
    return $cols;
}

// Fill the Sizes column
function fp_sizes_value($column_name, $id)
{
    if ($column_name == "all_sizes") {
        $up_load_dir =  wp_upload_dir();
        $dir = $up_load_dir['url'];
        $meta = wp_get_attachment_metadata($id);
        foreach ($meta['sizes'] as $name => $info) {
            echo esc_html("<strong>" . ucfirst($name) . "</strong>:<br>");
            echo "<small><a href='" . esc_url($dir) . "/" . esc_html($info['file']) . "' target='_blank'>" . esc_html($info['file']) . "</a></small><br><br>";
        }
    }
}

// Hook actions to admin_init
function fp_hook_new_media_columns($isActivated)
{
    // $displayAllSizes = get_option('mtg_displayAllSizes');
    if ($isActivated == 'on') {
        add_filter('manage_media_columns', 'fp_sizes_column');
        add_action('manage_media_custom_column', 'fp_sizes_value', 10, 2);
    } else {
        return;
    }
}

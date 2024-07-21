<?php

if (!defined('ABSPATH')) exit;

function fp_enable_classic_editor($isActivated)
{
    if ($isActivated == 'on') {
        add_filter('use_block_editor_for_post_type', 'fp_classic_editor_for_post_types', 10, 2);
    }
}

function fp_classic_editor_for_post_types($current_status, $post_type)
{
    $disabled_post_types = ['post']; // Specify the post types where Gutenberg should be disabled
    return in_array($post_type, $disabled_post_types) ? false : $current_status;
}

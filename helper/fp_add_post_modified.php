<?php
/*
* -------------------------------------------------------------------------------------
* @author: FP MoviesDB
* @author URI: https://fpmoviesdb.xyz/
* @copyright: (c) | All rights reserved
* -------------------------------------------------------------------------------------
*
* @since 1.0.0
*
*/

if (!defined('ABSPATH')) exit;

function fp_update_post_modified_date($post_id)
{

    // static $updating_post = false;

    // if ($updating_post) {
    //     return;
    // }

    // $updating_post = true;

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if (wp_is_post_autosave($post_id) || wp_is_post_revision($post_id)) {
        return;
    }

    if (!empty($_POST['fp_post_modified_custom']) && $_POST['fp_post_modified_custom'] == '1') {
        $custom_post_modified = $_POST['fp_post_modified'];

        fp_log_error('CUSTOM DATE: ' . $custom_post_modified);

        update_post_meta($post_id, '_fp_post_modified_custom', '1');
    } else {
        $custom_post_modified = current_time('mysql');

        fp_log_error('CURRENT DATE: ' . $custom_post_modified);

        update_post_meta($post_id, '_fp_post_modified_custom', '0');
    }

    $post_data = array(
        // 'ID' => $post_id,
        'post_modified' => date('Y-m-d H:i:s', strtotime($custom_post_modified)),
        'post_modified_gmt' => get_gmt_from_date($custom_post_modified),
    );

    fp_log_error('POST DATA: ' . print_r($post_data, true));

    // wp_update_post($post_data);
    // using wp_update_post() not working as its automatically updated by wordpress with latest values so using $wpdb->update() instead and USE $updating_post to prevent infinite loop

    global $wpdb;
    $wpdb->update(
        $wpdb->posts,
        $post_data,
        array('ID' => $post_id)
    );

    // $updating_post = false;
}

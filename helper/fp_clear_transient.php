<?php

if (!defined('ABSPATH')) die();

function clear_post_specific_transient($post_id, $post, $update)
{
    // error_log('clear_post_specific_transient');
    if (wp_is_post_revision($post_id) || (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)) {
        return;
    }
    $target_post_type = 'post';

    if ($target_post_type === $post->post_type) {
        if (function_exists('fp_log_error')) fp_log_error('Clearing transient for post ID: ' . $post_id);
        $transient_name = 'fp_cache_playerData_' . $post_id;
        $post_player_key = 'fp_cache_postPlayer_' . $post_id;
        $post_links_key = 'fp_cache_postLinks_' . $post_id;

        $all_transients = [
            $transient_name,
            $post_player_key,
            $post_links_key
        ];

        foreach ($all_transients as $transient) {
            // if (get_transient($transient)) {
            //     error_log($transient . ' EXIST ' . $post_id);

            //     error_log($transient . ' CLEARED ' . $post_id);
            // } else {
            //     error_log($transient . ' DOES NOT EXIST ' . $post_id);
            // }
            fp_delete_cache($transient);
        }
        clean_post_cache($post_id);

        // END THE FUNCTION
        return;
    }
}

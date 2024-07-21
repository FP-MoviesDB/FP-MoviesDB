<?php

if (!defined('ABSPATH')) exit;

function video_meta_box_init()
{
    add_meta_box(
        'fp_video_player_meta_box',
        'FP Video Player',
        'fp_video_player_meta_box',
        'post',
        'normal',
        'default'
    );
}

function languages()
{
    return array(
        '-----'                   => null,
        'Chinese'                 => 'cn',
        'Denmark'                 => 'dk',
        'Dutch'                 => 'nl',
        'English'                 => 'en',
        'English British'         => 'gb',
        'Egypt'                 => 'egt',
        'French'                 => 'fr',
        'German'                 => 'de',
        'Indonesian'             => 'id',
        'Hindi'                 => 'in',
        'Italian'                 => 'it',
        'Japanese'                 => 'jp',
        'Korean'                 => 'kr',
        'Philippines'             => 'ph',
        'Portuguese Portugal'     => 'pt',
        'Portuguese Brazil'     => 'br',
        'Polish'                 => 'pl',
        'Romanian'                 => 'td',
        'Scotland'                 => 'sco',
        'Spanish Spain'         => 'es',
        'Spanish Mexico'         => 'mx',
        'Spanish Argentina'     => 'ar',
        'Spanish Peru'             => 'pe',
        'Spanish Chile'         => 'cl',
        'Spanish Colombia'         => 'co',
        'Sweden'                 => 'se',
        'Turkish'                 => 'tr',
        'Russian'                 => 'ru',
        'Vietnam'                 => 'vn'
    );
}

function types_player_options()
{
    return array(
        'iframe'   => 'URL Embed',
        // 'mp4'      => 'URL MP4',
        'dtshcode' => 'Shortcode'
    );
}


function fp_video_player_meta_box($post)
{
    wp_nonce_field('fp_player_editor_nonce', 'fp_player_editor_nonce');
    $video_rows = get_post_meta($post->ID, 'mts_player_fields', true);
    $post_type = get_post_meta($post->ID, 'mtg_post_type', true);
    $post_type = strtolower($post_type);
    if ($post_type == 'movie') {
        if (empty($video_rows) && $post->ID != 0) {
            // $video_rows = array(
            //     array('title' => '', 'url' => '', 'language' => '')
            // );
        }
        require FP_MOVIES_DIR . 'inc/meta/player/fp_editorMovie.php';
    } else if ($post_type == 'series' || $post_type == 'tv') {
        require FP_MOVIES_DIR . 'inc/meta/player/fp_editorTV.php';
    } else {
        echo 'Please initialize the post type';
    }
}

function fp_save_video_meta_box_data($post_id)
{
    if (
        !isset($_POST['fp_player_editor_nonce']) || !wp_verify_nonce($_POST['fp_player_editor_nonce'], 'fp_player_editor_nonce') ||
        !current_user_can('edit_post', $post_id) ||
        (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
    ) {
        return;
    }
    $new_data = [];
    $post_type = get_post_meta($post_id, 'mtg_post_type', true);
    $post_type = strtolower($post_type);

    // fp_set_user_feedback("Missing required field: $field for entry $index", 'error');
    $base_fields = ($post_type == 'movie') ? ['title', 'url'] : ['season', 'episode', 'title', 'url'];

    $validate_entry = function ($index) use ($base_fields) {
        foreach ($base_fields as $field) {
            if (empty($_POST[$field][$index])) {
                return false;
            }
        }
        return true;
    };

    if (isset($_POST['title'])) { // Assuming 'title' is always set if there are entries to process.
        $total_entries = count($_POST['title']); // Assuming all arrays have the same count.
        for ($index = 0; $index < $total_entries; $index++) {
            if ($validate_entry($index)) { // Ensure validation passes.
                $entry = [];
                foreach ($base_fields as $field) {
                    $entry[$field] = $_POST[$field][$index] ?? ''; // Use null coalescence to handle missing fields.
                }
                $entry['type'] = 'iframe'; // Set type if not coming from POST data.
                $entry['language'] = $_POST['language'][$index] ?? ''; // Optional language.

                // Build a nested array based on season and episode if they exist.
                if ($post_type == 'series' || $post_type == 'tv') {
                    $season = $entry['season'] ?? 'general'; // Default season.
                    $episode = $entry['episode'] ?? 0; // Default episode.

                    $new_data[$season][$episode][] = $entry; // Store entry under season and episode.
                } else {
                    $new_data[] = $entry; // Store entry directly.
                }
            }
        }
    }

    if (!empty($new_data)) {
        update_post_meta($post_id, 'mts_player_fields', $new_data);
    } else {
        // error_log('Deleting post meta: ' . $post_id . ' and post_type: ' . $post_type);
        delete_post_meta($post_id, 'mts_player_fields');
    }
}

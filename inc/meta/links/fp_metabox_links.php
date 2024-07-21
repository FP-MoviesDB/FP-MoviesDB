<?php

if (!defined('ABSPATH')) exit;

function links_meta_box_init()
{
    add_meta_box(
        'fp_links_meta_box',
        'FP Additional Links',
        'links_meta_box',
        'post',
        'normal',
        'default'
    );
}

function link_selector(){
    // 144,240,360,480,720,1080,2160
    return [
        '144p' => '144',
        '240p' => '240',
        '360p' => '360',
        '480p' => '480',
        '720p' => '720',
        '1080p' => '1080',
        '2160p' => '2160'
    ];
}

function links_meta_box($post)
{
    wp_nonce_field('fp_links_editor_nonce', 'fp_links_editor_nonce');
    $links_rows = get_post_meta($post->ID, 'mts_links_fields', true);
    $post_type = get_post_meta($post->ID, 'mtg_post_type', true);
    $post_type = strtolower($post_type);
    // error_log('Links rows: ' . print_r($links_rows, true));
    // error_log('Post type: ' . $post_type);
    if ($post_type == 'movie') {
        // error_log('Loading movie links editor');
        require FP_MOVIES_DIR . 'inc/meta/links/fp_editorMovie.php';
    } else if ($post_type == 'series' || $post_type == 'tv') {
        require FP_MOVIES_DIR . 'inc/meta/links/fp_editorTV.php';
    } else {
        echo 'Please initialize the post type';
    }
}


function fp_save_links_meta_box_data($post_id)
{
    if (
        !isset($_POST['fp_links_editor_nonce']) || !wp_verify_nonce($_POST['fp_links_editor_nonce'], 'fp_links_editor_nonce') ||
        !current_user_can('edit_post', $post_id) ||
        (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
    ) {
        return;
    }

    // error_log(print_r($_POST, true));

    $links_data = [];
    $post_type = get_post_meta($post_id, 'mtg_post_type', true);
    $post_type = strtolower($post_type);

    $base_fields = ($post_type == 'movie') ? ['l_title', 'l_url'] : ['l_season', 'l_episode', 'l_title', 'l_url'];

    $validate_entry = function ($index) use ($base_fields) {
        foreach ($base_fields as $field) {
            // error_log('Checking field: ' . $field . ' for entry: ' . $index);
            // error_log('Field value: ' . $_POST[$field][$index]);
            if (empty($_POST[$field][$index])) {
                return false;
            }
        }
        return true;
    };

    if (isset($_POST['l_title'])) {
        // error_log('DATA FOUND');
        $total_entries = count($_POST['l_title']);
        for ($index = 0; $index < $total_entries; $index++) {
            if ($validate_entry($index)) {
                $entry = [];
                foreach ($base_fields as $field) {
                    $entry[$field] = sanitize_text_field($_POST[$field][$index]) ?? '';
                }
                if (isset($_POST['l_quality'][$index]) && !empty($_POST['l_quality'][$index])) {
                    $entry['l_quality'] = sanitize_text_field($_POST['l_quality'][$index]);
                }
                if (isset($_POST['l_audio'][$index]) && !empty($_POST['l_audio'][$index])) {
                    $entry['l_audio'] = sanitize_text_field($_POST['l_audio'][$index]);
                }
                if (isset($_POST['l_size'][$index]) && !empty($_POST['l_size'][$index])) {
                    $entry['l_size'] = sanitize_text_field($_POST['l_size'][$index]);
                }

                if ($post_type == 'series' || $post_type == 'tv') {
                    $season = $entry['l_season'];
                    $episode = $entry['l_episode'];
                    $links_data[$season][$episode] = $entry;
                } else {
                    $links_data[] = $entry;
                }
            }
        }
    }
    else {
        $links_data = [];
    }
    if (!empty($links_data)) {
        update_post_meta($post_id, 'mts_links_fields', $links_data);
    } else {
        delete_post_meta($post_id, 'mts_links_fields');
    }
}

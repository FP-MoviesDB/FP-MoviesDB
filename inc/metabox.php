<?php

if (!defined('ABSPATH')) exit;

function custom_meta_boxes_init()
{
    add_meta_box(
        'fp_post_meta_field_box',
        'MTG Post Data',
        'custom_fields_meta_box_content',
        'post',
        'normal',
        'default'
    );
}

function get_custom_post_meta($post_id)
{

    $default_meta_fields = [
        'mtg_post_type' => [
            'label' => 'Post Type',
            'type' => 'select',
            'options' => [
                'movie' => 'Movie',
                'series' => 'Series'
            ],
            'rows' => 1
        ],
        'mtg_is_featured' => [
            'label' => 'Featured',
            'type' => 'checkbox',
            'rows' => 4
        ],
        'mtg_tmdb_id' => [
            'label' => 'TMDB ID',
            'type' => 'text',
            'rows' => 1
        ],
        'mtg_imdb_id' => [
            'label' => 'IMDB ID',
            'type' => 'text',
            'rows' => 1
        ],
        'mtg_tmdb_title' => [
            'label' => 'TMDB Title',
            'type' => 'text',
            'rows' => 1
        ],
        'mtg_tmdb_tagline' => [
            'label' => 'TMDB Tagline',
            'type' => 'text',
            'rows' => 1
        ],
        'mtg_release_date' => [
            'label' => 'Release Date',
            'type' => 'text',
            'rows' => 1
        ],
        'mtg_yt_trailer' => [
            'label' => 'Youtube Trailer',
            'type' => 'text',
            'rows' => 1
        ],
        'mtg_vote_average' => [
            'label' => 'Vote Average',
            'type' => 'text',
            'rows' => 1
        ],
        'mtg_vote_count' => [
            'label' => 'Vote Count',
            'type' => 'text',
            'rows' => 1
        ],
        'mtg_size_480p' => [
            'label' => 'Avg Size 480p',
            'type' => 'text',
            'rows' => 1
        ],
        'mtg_size_720p' => [
            'label' => 'Avg Size 720p',
            'type' => 'text',
            'rows' => 1
        ],
        'mtg_size_1080p' => [
            'label' => 'Avg Size 1080p',
            'type' => 'text',
            'rows' => 1
        ],
        'mtg_size_2160p' => [
            'label' => 'Avg Size 2160p',
            'type' => 'text',
            'rows' => 1
        ],
        'mtg_poster_path' => [
            'label' => 'Poster Path',
            'type' => 'textarea',
            'rows' => 1
        ],
        'mtg_backdrop_path' => [
            'label' => 'Backdrop Path',
            'type' => 'textarea',
            'rows' => 4
        ],
        'mtg_single_screenshot' => [
            'label' => 'Single Screenshot',
            'type' => 'textarea',
            'rows' => 4
        ],
        'mtg_splash_screenshot' => [
            'label' => 'Splash Screenshot',
            'type' => 'textarea',
            'rows' => 4
        ],
        'mtg_subtitles' => [
            'label' => 'Subtitles',
            'type' => 'textarea',
            'rows' => 4
        ]
    ];

    $all_meta = get_post_meta($post_id);
    $mtg_meta_fields = [];

    // error_log(print_r($all_meta, true));

    // if its new post, return default meta fields
    if (empty($all_meta)) {
        foreach ($default_meta_fields as $key => $label) {
            $mtg_meta_fields[] = [
                'name' => $key,
                'label' => $label['label'],
                'value' => ($key === 'mtg_post_type') ? 'movie' : '',
                'type' => $label['type'],
                'rows' => $label['rows']
            ];
            // add option if it's select field
            if ($label['type'] === 'select') {
                $mtg_meta_fields[count($mtg_meta_fields) - 1]['options'] = $label['options'];
            }
        }
        return $mtg_meta_fields;
    }
    foreach ($default_meta_fields as $key => $field) {
        $default_value = '';
        $label = strtoupper(str_replace('_', ' ', $key));
        $label = str_replace('MTG', '', $label);
        $label = ucwords($label);

        $default_value = isset($all_meta[$key]) ? $all_meta[$key][0] : '';

        if ($key === 'mtg_post_type') {
            if (isset($all_meta[$key]) && is_array($all_meta[$key]) && !empty($all_meta[$key][0])) {
                $default_value = $all_meta[$key][0];
            } else {
                $default_value = 'movie'; // Default to 'movie' if no value exists
            }
        }

        $mtg_meta_fields[] = [
            'name' => $key,
            'label' => $field['label'],
            'value' => $default_value,
            'type' => $field['type'],
            'rows' => $field['rows']
        ];

        if ($field['type'] === 'select') {
            $options = [
                'movie' => 'Movie',
                'series' => 'Series'
            ];
            $mtg_meta_fields[count($mtg_meta_fields) - 1]['options'] = $options;
        }
    }

    usort($mtg_meta_fields, function ($a, $b) {
        if ($a['name'] === 'mtg_is_featured' && $a['type'] === 'checkbox') {
            return -1;  // Move 'mtg_is_featured' to the top
        } elseif ($b['name'] === 'mtg_is_featured' && $b['type'] === 'checkbox') {
            return 1;   // Keep 'mtg_is_featured' at the top
        }

        // Next, prioritize 'mtg_post_type'
        if ($a['name'] === 'mtg_post_type') {
            return -1; // Prioritize 'mtg_post_type' just after 'mtg_is_featured'
        } elseif ($b['name'] === 'mtg_post_type') {
            return 1;  // Keep 'mtg_post_type' high in the list
        }

        // Remaining sorting logic by type
        if ($a['type'] === $b['type']) {
            return 0;  // No change if types are the same
        } elseif ($a['type'] === 'text') {
            return -1; // Text fields go before other types except for 'mtg_post_type' and 'mtg_is_featured'
        } else {
            return 1;  // Other types go after
        }
    });

    return $mtg_meta_fields;
}

function custom_fields_meta_box_content($post)
{
    global $fp_min_m;
    wp_enqueue_script('post-button-admin-script', esc_url(FP_MOVIES_URL) . 'js/fp_post_button' . $fp_min_m . '.js', array('jquery'), FP_MOVIES_FILES, true);
    wp_enqueue_style('post-button-admin-style', esc_url(FP_MOVIES_URL) . '/css/fp_post_button' . $fp_min_m . '.css', array(), FP_MOVIES_FILES);
    wp_localize_script('post-button-admin-script', 'btnData', array(
        'ajax_url' => FP_MOVIES_AJAX,
        'update-nonce' => wp_create_nonce('fp_post_update_nonce'),
        'featured-nonce' => wp_create_nonce('fp-set-featured-nonce')
    ));

    // enqueue mts-admin.css if not already enqueued
    if (!wp_style_is('mts-admin-style', 'enqueued')) {
        wp_enqueue_style('mts-admin-style', esc_url(FP_MOVIES_URL) . '/css/mts-admin' . $fp_min_m . '.css', array(), FP_MOVIES_FILES);
    }

    wp_nonce_field('save_fp_custom_fields_meta_box_data', 'fp_custom_fields_meta_box_nonce');

    $fields = get_custom_post_meta($post->ID);
    $c_post_id = $post->ID;
    echo '<div class="fp_post_meta_fields">';
    if ($c_post_id) {
        $c_post_type = get_post_meta($c_post_id, '_content_type', true);
        $c_tmdb_id = get_post_meta($c_post_id, 'mtg_tmdb_id', true);
        if ($c_post_type || $c_tmdb_id) {
            echo '<div class="refetch-btn-wrapper"><div class="fp-custom-button update-post fp-internal-refetch" data-page="single" data-id="' . esc_html($c_post_id) . '" data-tmdb="' . esc_html($c_tmdb_id) . '" data-type="' . esc_html($c_post_type) . '">ReFetch</div></div>';
        }
    }

    // check if mtg_post_type is set
    $post_type = get_post_meta($post->ID, 'mtg_post_type', true) ? get_post_meta($post->ID, 'mtg_post_type', true) : '';
    foreach ($fields as $field) {
        if (empty($post_type) && $field['type'] === 'checkbox' && $field['name'] === 'mtg_is_featured') {
            $fp_new_post_checkbox = 'grid-column: span 2;';
        } else {
            $fp_new_post_checkbox = '';
        }
        echo '<div class="fp-single-' . esc_attr($field['type']) . '-field" style="' . esc_html($fp_new_post_checkbox) . '">';
        echo '<div class="field-container" style="margin-bottom: 20px;">';
        echo '<label for="' . esc_attr($field['name']) . '">' . esc_html($field['label']) . '</label>';

        if ($field['type'] === 'textarea') {
            echo '<textarea id="' . esc_attr($field['name']) . '" name="' . esc_attr($field['name']) . '" rows="' . esc_attr($field['rows']) . '" class="widefat">' . esc_textarea($field['value']) . '</textarea>';
        } else if ($field['type'] === 'select') {
            echo '<select id="' . esc_attr($field['name']) . '" name="' . esc_attr($field['name']) . '" class="widefat">';
            foreach ($field['options'] as $option_value => $option_label) {
                $selected = (strtolower($field['value']) === $option_value) ? 'selected' : '';
                echo '<option value="' . esc_attr($option_value) . '" ' . esc_html($selected) . '>' . esc_html($option_label) . '</option>';
            }
            echo '</select>';
        } else if ($field['type'] === 'checkbox') {
            // error_log("FIELD: " . print_r($field, true));
            $checked = $field['value'] === '1' ? 'checked' : '';
            $value = $field['value'] === '1' ? '1' : '0';
            echo '<input type="checkbox" id="' . esc_attr($field['name']) . '" name="' . esc_attr($field['name']) . '" value="' . esc_attr($value) . '"' . esc_html($checked) . ' />';
        } else {
            echo '<input type="text" id="' . esc_attr($field['name']) . '" name="' . esc_attr($field['name']) . '" value="' . esc_attr($field['value']) . '" class="widefat" />';
        }

        echo '</div>';
        echo '</div>';
    }
    echo '</div>';
}

function save_custom_fields_meta_box_data($post_id)
{
    if (!isset($_POST['fp_custom_fields_meta_box_nonce']) || !wp_verify_nonce($_POST['fp_custom_fields_meta_box_nonce'], 'save_fp_custom_fields_meta_box_data')) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    $fields = get_custom_post_meta($post_id);

    foreach ($fields as $field) {
        // error_log("FIELD: " . print_r($field, true));
        if ($field['type'] === 'checkbox') {
            $sanitized_value = isset($_POST[$field['name']]) ? '1' : '0';
        } else {
            if (array_key_exists($field['name'], $_POST)) {
                $value = $_POST[$field['name']];
                if ($field['type'] === 'textarea') {
                    $sanitized_value = sanitize_textarea_field($value);
                } else {
                    $sanitized_value = sanitize_text_field($value);
                }
            } else {
                continue;
            }
        }

        update_post_meta(
            $post_id,
            $field['name'],
            $sanitized_value
        );
    }
}

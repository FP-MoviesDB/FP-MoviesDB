<?php

if (!defined('ABSPATH')) exit;

class FP_UpdatePost extends CreatePostHelper
{

    protected $tmdbkey = '';
    protected $fpkey = '';
    protected $apilang = '';

    protected $post_id = '';
    protected $tmdb_id = '';
    protected $post_type = '';

    function __construct()
    {
        add_action('wp_ajax_update_movie_post', array($this, 'handle_update_post'));
        $this->apilang = get_option('mtg_language', 'en-US');
        $this->tmdbkey = get_option('mtg_tmdb_api_key', FP_MOVIES_TMDB_API_KEY);
        $this->fpkey = get_option('mtg_fp_api_key', FP_MOVIES_FP_API_KEY);
    }

    function validation_init($postData)
    {
        if (empty($postData) || !is_array($postData)) {
            wp_send_json_error(array('message' => 'Invalid post data 1'), 400);
            return;
        }

        $this->post_id = $postData['post_id'];
        $this->tmdb_id = $postData['tmdb_id'];
        $this->post_type = $postData['post_type'];

        if (empty($this->post_id) || empty($this->tmdb_id) || empty($this->post_type)) {
            wp_send_json_error(array('message' => 'Invalid post data 2'), 400);
            return;
        }
    }

    function handle_update_post()
    {
        if (!wp_verify_nonce($_POST['nonce'], 'fp_post_update_nonce') || !current_user_can('publish_posts')) {
            wp_send_json_error(array('message' => 'Unauthorized'), 400);
            return;
        }
        require_once FP_MOVIES_DIR . 'helper/fp_get_option_with_fallback.php';
        require_once FP_MOVIES_DIR . 'helper/fp_get_full_lang_name.php';

        // From POST request we need: nonce, and fp_postData that contain {post_id, tmdb_id, post_type}
        $fp_postData = $_POST['fp_postData'] ?? [];
        $this->validation_init($fp_postData);

        // error_log("POST ID: " . print_r($this->post_id, TRUE));
        // error_log("TMDB ID: " . print_r($this->tmdb_id, TRUE));
        // error_log("POST TYPE: " . print_r($this->post_type, TRUE));
        // error_log("TMDB API KEY: " . print_r($this->tmdbkey, TRUE));
        // error_log("FP API KEY: " . print_r($this->fpkey, TRUE));

        $postType_2 = $this->post_type === 'tv' ? 'Series' : ucfirst($this->post_type);
        $fpData = $this->fetchFPdata($this->tmdb_id, $this->post_type, $this->fpkey);
        $this->validate_array($fpData, 'Failed to fetch data from FP API');

        $processedData = $this->processFPData($fpData);
        $single_screenshot = $processedData['single_screenshot'];
        $splash_screenshot = $processedData['splash_screenshot'];
        $resolution = $processedData['resolution'];
        $audios = $processedData['audios'];
        $subtitles = $processedData['subtitles'];
        $qualities = $processedData['qualities'];
        $networks = $processedData['networks'];

        $post_template_Default = get_option('mtg_postDefault_settings', []);
        // error_log("POST TEMPLATE DEFAULT: " . print_r($post_template_Default, TRUE));
        $post_template_default_network = $this->get_arrayValue_with_fallback($post_template_Default, 'default_network', '');
        $post_template_default_network = $this->normalize_to_array($post_template_default_network);

        $postData = $this->fetchTMDBdata($this->tmdb_id, $this->post_type, $this->tmdbkey, $this->apilang);
        $this->validate_array($postData, 'Failed to fetch data from TMDB API');

        $post_template_title = $this->get_arrayValue_with_fallback($post_template_Default, 'title', '{title}');
        $post_template_slug = $this->get_arrayValue_with_fallback($post_template_Default, 'slug', '{title}');
        $post_template_status = $this->get_arrayValue_with_fallback($post_template_Default, 'status', 'draft');
        $post_template_category = $this->get_arrayValue_with_fallback($post_template_Default, 'category', $this->post_type);
        $post_template_tags = $this->get_arrayValue_with_fallback($post_template_Default, 'tags', '');
        // $post_template_quality = $this->get_arrayValue_with_fallback($post_template_Default, 'quality', false);
        if (!empty($qualities)) {
            $quality_values_final = $qualities;
        } else {
            $quality_values_final = $this->get_arrayValue_with_fallback($post_template_Default, 'default_quality', array('HD'));
            $quality_values_final = $this->normalize_to_array($quality_values_final);
        }

        $isAdult = $postData['adult'];

        // ┌───────────────────────────────┐
        // │ ADD FP DATA TO $POSTDATA   │
        // └───────────────────────────────┘
        $postData['quality'] = $quality_values_final;
        $postData['audio'] = implode('-', $audios);
        $audio_count = count($audios);
        $postData['c_audio'] = $audio_count > 0 && $audio_count <= 2 ? implode('-', $audios) : 'Multi Audio';
        $sub_count = count($subtitles);
        $postData['c_subs'] = $sub_count == 1 ? 'ESub' : ($sub_count > 1 ? 'MSubs' : '');
        $postData['p_type_2'] = $postType_2;

        if (isset($postData['networks'])) {
            $postData['networks'] = array_merge($postData['networks'], $networks);
        } else {
            if (!empty($networks) && is_array($networks)) {
                $postData['networks'] = $networks;
            } else {
                $postData['networks'] = $post_template_default_network;
            }
        }

        $overview = sanitize_text_field($postData['overview']);
        $selectors_settings = get_option('mtg_checked_options', []);
        $isGenre = $this->get_arrayValue_with_fallback($selectors_settings, 'genre', false);
        $isAudio = $this->get_arrayValue_with_fallback($selectors_settings, 'audio', false);
        $isYear = $this->get_arrayValue_with_fallback($selectors_settings, 'year', false);
        $isResolution = $this->get_arrayValue_with_fallback($selectors_settings, 'resolution', false);
        $isQuality = $this->get_arrayValue_with_fallback($selectors_settings, 'quality', false);
        $isNetwork = $this->get_arrayValue_with_fallback($selectors_settings, 'network', false);
        $isCast = $this->get_arrayValue_with_fallback($selectors_settings, 'cast', false);
        $isCrew = $this->get_arrayValue_with_fallback($selectors_settings, 'crew', false);
        $isCollection = $this->get_arrayValue_with_fallback($selectors_settings, 'collection', false);

        $category_replace = $this->replace_template_placeholders($post_template_category, $postData);
        $category_names = $this->normalize_to_array($category_replace);
        $category_ids = $this->process_taxonomy_terms('category', $category_names);

        $final_title = $this->format_title($this->replace_template_placeholders($post_template_title, $postData));
        $final_slug = $this->format_slug($this->replace_template_placeholders($post_template_slug, $postData));

        $post_data = array(
            'ID' => $this->post_id,
            'post_title'    => $final_title,
            'post_content'  => $overview,
            'post_status'   => $post_template_status,
            'post_type'     => 'post',
            'post_category' => $category_ids,
            'post_author'  => get_current_user_id(),
            'post_name' => $final_slug,
        );

        if (!empty($tag_ids)) $post_data['tags_input'] = $tag_ids;

        $update_post = wp_update_post($post_data, true);
        if (is_wp_error($update_post)) {
            wp_send_json_error(array('message' => $update_post->get_error_message()), 400);
            return;
        }

        $all_updates_successful = true;

        if (isset($postData['poster_path']) && !empty($postData['poster_path'])) {
            // error_log("POSTER_PATH: " . print_r($postData['poster_path'], TRUE));
            $image_size = $this->get_arrayValue_with_fallback($post_template_Default, 'featured_image_size', 'w780');
            $image_name = $this->get_arrayValue_with_fallback($post_template_Default, 'image_name', '');
            $image_set_success = $this->set_featured_image_from_url($this->post_id, $postData, $image_size, $image_name);
            if (!$image_set_success) {
                $all_updates_successful = false;
            }
        }

        $screenshot_fallback = esc_url(FP_MOVIES_URL) . 'img/no-screenshots.webp';
        $vote_avg_to_one_decimal = number_format($postData['vote_average'], 1, '.', '');
        $mtg_vote_average = !empty($vote_avg_to_one_decimal) && $vote_avg_to_one_decimal != '0'
            ? (float)$vote_avg_to_one_decimal  // Cast to float if non-empty
            : 7.0;

        $meta_data = array(
            '_content_type' => $this->post_type,
            'mtg_post_type' => $postType_2,
            'mtg_tmdb_id' => $postData['tmdb_id'],
            'mtg_imdb_id' => $postData['imdb_id'],
            'mtg_tmdb_title' => $postData['title'],
            'mtg_tmdb_tagline' => $postData['tagline'],
            'mtg_poster_path' => $postData['poster_path'],
            'mtg_release_date' => $postData['release_date'],
            'mtg_backdrop_path' => $postData['backdrop_path'],
            'mtg_yt_trailer' => $postData['trailer'],
            'mtg_vote_average' => $mtg_vote_average,
            'mtg_vote_count' => $postData['vote_count'] ?? 1,
            'mtg_single_screenshot' => !empty($single_screenshot) ? $single_screenshot : $screenshot_fallback,
            'mtg_splash_screenshot' => !empty($splash_screenshot) ? $splash_screenshot : $screenshot_fallback,
            'mtg_subtitles' => !empty($subtitles) ? wp_json_encode($subtitles) : '',
            'mtg_size_480p' => $processedData['size_480p'] ?? '',
            'mtg_size_720p' => $processedData['size_720p'] ?? '',
            'mtg_size_1080p' => $processedData['size_1080p'] ?? '',
            'mtg_size_2160p' => $processedData['size_2160p'] ?? '',
            'mtg_storyline' => $overview
        );

        $current_meta_values = get_post_meta($this->post_id);

        foreach ($meta_data as $key => $value) {
            if (empty($value)) continue;
            $current_value = isset($current_meta_values[$key]) ? $current_meta_values[$key][0] : '';
            if ($current_value !== $value) {
                $result = update_post_meta($this->post_id, $key, $value);
                if (!$result) {
                    $all_updates_successful = false;
                }
            }
        }

        $current_terms = wp_get_object_terms($this->post_id, array('mtg_audio', 'mtg_genre', 'mtg_resolution', 'mtg_quality', 'mtg_network'), array('fields' => 'all_with_object_id'));

        $term_differs = function ($taxonomy, $new_terms) use ($current_terms) {
            $current_term_ids = wp_list_pluck(array_filter($current_terms, function ($term) use ($taxonomy) {
                return $term->taxonomy === $taxonomy;
            }), 'term_id');

            return !empty(array_diff($new_terms, $current_term_ids)) || !empty(array_diff($current_term_ids, $new_terms));
        };

        if ($isAudio === 'on') {
            if (!empty($audios)) {
                $audio_ids = $this->process_taxonomy_terms('mtg_audio', $audios);
                if ($term_differs('mtg_audio', $audio_ids)) {
                    $audio_update_result = wp_set_post_terms($this->post_id, $audio_ids, 'mtg_audio');
                    if (is_wp_error($audio_update_result)) {
                        $all_updates_successful = false;
                    }
                }
            }
        }

        if ($isGenre === 'on') {
            $genres = $postData['genres'];

            if (!empty($genres)) {
                $genre_names = $this->genre_id_to_name($this->post_type, $genres);
                if ($isAdult || $isAdult === 'on') {
                    $genre_names[] = 'Adult';
                }
                $genre_ids = $this->process_taxonomy_terms('mtg_genre', $genre_names);
                if ($term_differs('mtg_genre', $genre_ids)) {
                    $genre_update_result = wp_set_post_terms($this->post_id, $genre_ids, 'mtg_genre');
                    if (is_wp_error($genre_update_result)) {
                        $all_updates_successful = false;
                    }
                }
            }
        }

        if ($isResolution === 'on') {
            if (!empty($resolution)) {
                $resolution_ids = $this->process_taxonomy_terms('mtg_resolution', $resolution);
                if ($term_differs('mtg_resolution', $resolution_ids)) {
                    $resolution_update_result = wp_set_post_terms($this->post_id, $resolution_ids, 'mtg_resolution');
                    if (is_wp_error($resolution_update_result)) {
                        $all_updates_successful = false;
                    }
                }
            }
        }

        if ($isQuality === 'on') {
            $quality_array_names = $postData['quality'];
            if (!is_array($quality_array_names)) {
                $quality_array_names = array($quality_array_names);
            }
            fp_log_error('QUALITY: ' . print_r($quality_array_names, TRUE));
            if (!empty($quality_array_names)) {
                $quality_ids = $this->process_taxonomy_terms('mtg_quality', $quality_array_names);
                if ($term_differs('mtg_quality', $quality_ids)) {
                    $quality_update_result = wp_set_post_terms($this->post_id, $quality_ids, 'mtg_quality');
                    if (is_wp_error($quality_update_result)) {
                        $all_updates_successful = false;
                    }
                }

            }
        }

        if ($isNetwork === 'on') {
            $networkUpdatedValue = $postData['networks'];
            if (!empty($networkUpdatedValue)) {
                $network_ids = $this->process_taxonomy_terms('mtg_network', $networkUpdatedValue);
                if ($term_differs('mtg_network', $network_ids)) {
                    $network_update_result = wp_set_post_terms($this->post_id, $network_ids, 'mtg_network');
                    if (is_wp_error($network_update_result)) {
                        $all_updates_successful = false;
                    }
                }
            }
        }

        if ($isCast === 'on') {
            $cast = $postData['cast'];
            // error_log("CAST: " . print_r($cast, TRUE));
            if (!empty($cast)) {
                $cast_ids = $this->process_taxonomy_terms('mtg_cast', $cast);
                $cast_update_result = wp_set_post_terms($this->post_id, $cast_ids, 'mtg_cast');
                if (is_wp_error($cast_update_result)) {
                    // error_log('Failed to update cast terms for post ID: ' . $this->post_id);
                    // $all_updates_successful = true;
                }
            }
        }

        if ($isCrew === 'on') {
            $crew = $postData['crew'];
            // error_log("CREW: " . print_r($crew, TRUE));
            if (!empty($crew)) {
                $crew_ids = $this->process_taxonomy_terms('mtg_crew', $crew);
                $crew_update_result = wp_set_post_terms($this->post_id, $crew_ids, 'mtg_crew');
                if (is_wp_error($crew_update_result)) {
                    // error_log('Failed to update crew terms for post ID: ' . $this->post_id);
                    // $all_updates_successful = true;
                }
            }
        }

        if ($isCollection === 'on') {
            $collection = $postData['collection'];
            // error_log("COLLECTION: " . print_r($collection, TRUE));
            if (!empty($collection)) {
                // send collection as array
                $collection_ids = $this->process_taxonomy_terms('mtg_collection', array($collection));
                $collection_update_result = wp_set_post_terms($this->post_id, $collection_ids, 'mtg_collection');
                if (is_wp_error($collection_update_result)) {
                    // error_log('Failed to update collection terms for post ID: ' . $this->post_id);
                    // $all_updates_successful = true;
                }
            }
        }

        $return_data = array(
            // 'post_id' => (int)$this->post_id,
            // 'post_type' => $this->post_type,
            // 'post_edit_url' => admin_url("post.php?post={$this->post_id}&action=edit"),
            // 'preview_url' => wp_get_shortlink($this->post_id),
            // 'all_updates_successful' => $all_updates_successful,
            // 'tmdb_id' => $this->tmdb_id,
        );

        wp_send_json_success($return_data, 200);
        return;
    }
}

new FP_UpdatePost();

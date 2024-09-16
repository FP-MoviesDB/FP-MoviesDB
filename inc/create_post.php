<?php

if (!defined('ABSPATH')) exit;

class FP_CreatePost extends CreatePostHelper
{
    protected $tmdbkey = '';
    protected $fpkey = '';
    protected $apilang = '';

    function __construct()
    {
        fp_log_error('FP_CreatePost class loaded');
        add_action('wp_ajax_create_movie_post', array($this, 'handle_create_post'));
        $this->apilang = get_option('mtg_language', 'en-US');
        $this->tmdbkey = get_option('mtg_tmdb_api_key', FP_MOVIES_TMDB_API_KEY);
        $this->fpkey = get_option('mtg_fp_api_key', FP_MOVIES_FP_API_KEY);
    }



    function handle_create_post()
    {
        fp_log_error('Create post request received');
        if (!wp_verify_nonce($_POST['nonce'], 'movie_tv_nonce') || !current_user_can('publish_posts')) {
            wp_send_json_error(array('message' => 'Unauthorized'), 400);
            return;
        }

        require_once FP_MOVIES_DIR . 'helper/fp_get_option_with_fallback.php';
        // require_once FP_MOVIES_DIR . 'helper/fp_process_taxonomy_terms.php';
        require_once FP_MOVIES_DIR . 'helper/fp_get_full_lang_name.php';

        $tmdbData = $_POST['tmdbData'];
        $postType = $tmdbData['p_type'];
        // postType 2 variable that replaces $postType tv to Series if it is tv else same as $postType
        $postType_2 = $postType === 'tv' ? 'Series' : ucfirst($postType);
        $tmdb_id = $tmdbData['tmdb_id'];

        if (empty($postType) || empty($tmdb_id)) {
            wp_send_json_error(array('message' => 'Invalid request'), 400);
            return;
        }

        $fpData = $this->fetchFPdata($tmdb_id, $postType, $this->fpkey);

        if (!$fpData) {
            wp_send_json_error(array('message' => 'FilePress: No Files Found | Add Files First.'), 400);
            return;
        }

        if (is_array($fpData) && !empty($fpData)) {
            $processedData = $this->processFPData($fpData);
            $single_screenshot = $processedData['single_screenshot'];
            $splash_screenshot = $processedData['splash_screenshot'];
            $resolution = $processedData['resolution'];
            $audios = $processedData['audios'];
            $subtitles = $processedData['subtitles'];
            $qualities = $processedData['qualities'];
            $networks = $processedData['networks'];
        }


        // error_log("QUALITIES: " . print_r($qualities, TRUE));
        // error_log("AUDIOS: " . print_r($audios, TRUE));
        // error_log("SUBTITLES: " . print_r($subtitles, TRUE));
        // error_log("SINGLE_SCREENSHOT: " . print_r($single_screenshot, TRUE));
        // error_log("SPLASH_SCREENSHOT: " . print_r($splash_screenshot, TRUE));




        $post_template_Default = get_option('mtg_postDefault_settings', []);

        $post_template_default_network = $this->get_arrayValue_with_fallback($post_template_Default, 'default_network', '');
        $post_template_default_network = $this->normalize_to_array($post_template_default_network);

        $postData = $this->fetchTMDBdata($tmdb_id, $postType, $this->tmdbkey, $this->apilang);

        $post_template_title = $this->get_arrayValue_with_fallback($post_template_Default, 'title', '{title}');
        $post_template_slug = $this->get_arrayValue_with_fallback($post_template_Default, 'slug', '{title}');
        $post_template_status = $this->get_arrayValue_with_fallback($post_template_Default, 'status', 'draft');
        $post_template_category = $this->get_arrayValue_with_fallback($post_template_Default, 'category', $postType);
        $post_template_tags = $this->get_arrayValue_with_fallback($post_template_Default, 'tags', '');
        // $post_template_quality = $this->get_arrayValue_with_fallback($post_template_Default, 'quality', false);

        if (!empty($qualities)) {
            $quality_values_final = $qualities;
        } else {
            $quality_values_final = $this->get_arrayValue_with_fallback($post_template_Default, 'default_quality', array(''));
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

        // $isGenre = get_option_with_fallback('mtg_selectors[mtg_genre]', false);
        // $isAudio = get_option_with_fallback('mtg_selectors[mtg_audio]', false);
        // $isYear = get_option_with_fallback('mtg_selectors[mtg_year]', false);
        // $isResolution = get_option_with_fallback('mtg_selectors[mtg_resolution]', false);
        // $isQuality = get_option_with_fallback('mtg_selectors[mtg_quality]', false);
        // $isNetwork = get_option_with_fallback('mtg_selectors[mtg_network]', false);

        // $networkUpdatedValue = array();
        // if (!empty($postData['networks']) && $postData['networks']) {
        //     $networkUpdatedValue = array_merge($postData['networks'], $post_template_default_network);
        // } else {
        //     $networkUpdatedValue = $post_template_default_network;
        // }
        // // error_log("NETWORK_UPDATED_VALUE: " . print_r($networkUpdatedValue, TRUE));
        // // error_log("NETWORD_TYPE: " . gettype($networkUpdatedValue));
        // $networkUpdatedValue = array_unique($networkUpdatedValue);


        if (!empty($post_template_tags)) {
            $tag_names = $this->normalize_to_array($post_template_tags);
            $tag_names = $this->replace_template_placeholders($tag_names, $postData);
            $tag_ids = $this->process_taxonomy_terms('post_tag', $tag_names);
        }

        $category_replace = $this->replace_template_placeholders($post_template_category, $postData);
        $category_names = $this->normalize_to_array($category_replace);
        $category_ids = $this->process_taxonomy_terms('category', $category_names);

        $final_title = $this->format_title($this->replace_template_placeholders($post_template_title, $postData));
        $final_slug = $this->format_slug($this->replace_template_placeholders($post_template_slug, $postData));

        // error_log("final_title: " . print_r($final_title, TRUE));
        // error_log("final_slug: " . print_r($final_slug, TRUE));
        // error_log("final_category: " . print_r($category_ids, TRUE));
        // error_log("final_tags: " . print_r($tag_ids, TRUE));
        // error_log("final_status: " . print_r($post_template_status, TRUE));
        // error_log("final_overview: " . print_r($overview, TRUE));

        // replace {title} with the title of the movie
        $post_data = array(
            'post_title'    => $final_title,
            'post_content'  => $overview,
            'post_status'   => $post_template_status,
            'post_type'     => 'post',
            'post_category' => $category_ids,
            'post_author'  => get_current_user_id(),
            'post_name' => $final_slug,
        );

        if (!empty($tag_ids)) {
            $post_data['tags_input'] = $tag_ids;
        }

        $post_id = wp_insert_post($post_data);

        if (is_wp_error($post_id)) {
            echo 'Error creating post: ' . esc_html($post_id->get_error_message());
            wp_send_json_error(array('message' => 'Failed to create post'), 400);
        }

        // error_log("POST_ID: " . print_r($post_id, TRUE));
        // error_log("POSTER_URL: " . print_r($tmdbData['poster_url'], TRUE));


        if (!empty($postData['poster_path'])) {
            // error_log("POSTER_PATH: " . print_r($postData['poster_path'], TRUE));
            $image_size = $this->get_arrayValue_with_fallback($post_template_Default, 'featured_image_size', 'w780');
            $image_name = $this->get_arrayValue_with_fallback($post_template_Default, 'image_name', '');
            // fp_log_error('Image Name: ' . $image_name);
            $image_set_success = $this->set_featured_image_from_url($post_id, $postData, $image_size, $image_name);
            if (!$image_set_success) {
                // error_log('Failed to set featured image for post ID: ' . $post_id);
                $all_updates_successful = false;
            }
        }

        // genre, audio and year is a taxonomy term, so we need to create a function to handle this

        // error_log("METADATA: " . print_r($meta_data, TRUE));

        // location pluginURl + img/no-screenshots.webp
        $screenshot_fallback = esc_url(FP_MOVIES_URL) . 'img/no-screenshots.webp';
        $vote_avg_to_one_decimal = number_format($postData['vote_average'], 1, '.', '');
        $mtg_vote_average = !empty($vote_avg_to_one_decimal) && $vote_avg_to_one_decimal != '0'
            ? (float)$vote_avg_to_one_decimal  // Cast to float if non-empty
            : 7.0;

        // error_log("Saving single_screenshot: " . $single_screenshot);

        $meta_data = array(
            '_content_type' => $postType,
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
        $all_updates_successful = true; // Flag to track if all updates are successful

        foreach ($meta_data as $key => $value) {
            if (empty($value)) {
                continue;
            }
            $result = update_post_meta($post_id, $key, $value);
            if ($result === false && get_post_meta($post_id, $key, true) != $value) {
                $all_updates_successful = false;
            }
        }

        // Update the genre, audio and year taxonomy terms
        // error_log("isGenre: " . print_r($isGenre, TRUE));

        if ($isYear === 'on') {
            $all_year = $postData['release_years'];
            // error_log("ALL_YEAR: " . print_r($all_year, TRUE));
            if (!empty($all_year) && is_array($all_year)) {
                $year_ids = $this->process_taxonomy_terms('mtg_year', $all_year);
                $year_update_result = wp_set_post_terms($post_id, $year_ids, 'mtg_year');
                if (is_wp_error($year_update_result)) {
                    // error_log('Failed to update year terms for post ID: ' . $post_id);
                    // $all_updates_successful = true;
                }
            }
        }

        if ($isAudio === 'on') {
            if (!empty($audios)) {
                $audio_ids = $this->process_taxonomy_terms('mtg_audio', $audios);
                $audio_update_result = wp_set_post_terms($post_id, $audio_ids, 'mtg_audio');
                if (is_wp_error($audio_update_result)) {
                    // error_log('Failed to update audio terms for post ID: ' . $post_id);
                    // $all_updates_successful = true;
                }
            }
        }


        if ($isGenre === 'on') {
            $genres = $postData['genres'];
            // error_log("GENRES: " . print_r($genres, TRUE));
            if (!empty($genres)) {
                $genre_names = $this->genre_id_to_name($postType, $genres);
                // error_log("GENRE_NAMES: " . print_r($genre_names, TRUE));

                // OR network contains 'VMAX'
                if ($isAdult || $isAdult === 'on') {
                    $genre_names[] = 'Adult';
                }

                $genre_ids = $this->process_taxonomy_terms('mtg_genre', $genre_names);

                $genre_update_result = wp_set_post_terms($post_id, $genre_ids, 'mtg_genre');

                // error_log("GENRE_UPDATE_RESULT: " . print_r($genre_update_result, TRUE));

                if (is_wp_error($genre_update_result)) {
                    // error_log('Failed to update genre terms for post ID: ' . $post_id);
                    // $all_updates_successful = true;
                }
            }
        }

        if ($isResolution === 'on') {
            if (!empty($resolution)) {
                // error_log("RESOLUTION: " . print_r($resolution, TRUE));
                $resolution_ids = $this->process_taxonomy_terms('mtg_resolution', $resolution);
                // error_log("RESOLUTION_IDS: " . print_r($resolution_ids, TRUE));
                $resolution_update_result = wp_set_post_terms($post_id, $resolution_ids, 'mtg_resolution');
                if (is_wp_error($resolution_update_result)) {
                    // error_log('Failed to update resolution terms for post ID: ' . $post_id);
                    // $all_updates_successful = true;
                }
            }
        }

        if ($isQuality === 'on') {
            $quality_array_names = $postData['quality'];
            if (!is_array($quality_array_names)) {
                $quality_array_names = array($quality_array_names);
            }
            if (!empty($quality_array_names)) {
                $quality_ids = $this->process_taxonomy_terms('mtg_quality', $quality_array_names);
                wp_set_post_terms($post_id, $quality_ids, 'mtg_quality');
            }
        }

        if ($isNetwork === 'on') {
            $networkUpdatedValue = $postData['networks'];
            if (!empty($networkUpdatedValue)) {
                $network_ids = $this->process_taxonomy_terms('mtg_network', $networkUpdatedValue);
                fp_log_error('NETWORK_IDS: ' . print_r($network_ids, TRUE));
                wp_set_post_terms($post_id, $network_ids, 'mtg_network');
            }
        }

        if ($isCast === 'on') {
            $cast = $postData['cast'];
            // error_log("CAST: " . print_r($cast, TRUE));
            if (!empty($cast)) {
                $cast_ids = $this->process_taxonomy_terms('mtg_cast', $cast);
                $cast_update_result = wp_set_post_terms($post_id, $cast_ids, 'mtg_cast');
                if (is_wp_error($cast_update_result)) {
                    // error_log('Failed to update cast terms for post ID: ' . $post_id);
                    // $all_updates_successful = true;
                }
            }
        }

        if ($isCrew === 'on') {
            $crew = $postData['crew'];
            // error_log("CREW: " . print_r($crew, TRUE));
            if (!empty($crew)) {
                $crew_ids = $this->process_taxonomy_terms('mtg_crew', $crew);
                $crew_update_result = wp_set_post_terms($post_id, $crew_ids, 'mtg_crew');
                if (is_wp_error($crew_update_result)) {
                    // error_log('Failed to update crew terms for post ID: ' . $post_id);
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
                $collection_update_result = wp_set_post_terms($post_id, $collection_ids, 'mtg_collection');
                if (is_wp_error($collection_update_result)) {
                    // error_log('Failed to update collection terms for post ID: ' . $post_id);
                    // $all_updates_successful = true;
                }
            }
        }

        // error_log("ALL_UPDATES_SUCCESSFUL: " . print_r($all_updates_successful, TRUE));
        // $post_edit_url = get_edit_post_link((int) $post_id);
        $return_data = array(
            'post_id' => (int) $post_id,
            'post_type' => $postType,
            'post_edit_url' => admin_url("post.php?post={$post_id}&action=edit"),
            'preview_url' => wp_get_shortlink($post_id),
            'all_updates_successful' => $all_updates_successful,
            'tmdb_id' => $tmdb_id,
        );
        // error_log('Return data: ' . print_r($return_data, TRUE));
        // error_log("POST DETAILS: ->");
        // error_log("POST_ID: " . print_r($post_id, TRUE));
        // error_log("POST_TYPE: " . print_r($postType, TRUE));
        // error_log("POST_TITLE: " . print_r($final_title, TRUE));
        // error_log("POST IMDB ID: " . print_r($postData['imdb_id'], TRUE));
        wp_send_json_success($return_data, 200);
        return;
    }
}
new FP_CreatePost();

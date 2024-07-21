<?php

if (!defined('ABSPATH')) exit;

function get_all_cached_post_meta($post_id)
{
    $cache_key = 'all_post_meta_' . $post_id;
    $all_meta = wp_cache_get($cache_key);
    if (false === $all_meta) {
        $all_meta = get_post_meta($post_id);
        wp_cache_set($cache_key, $all_meta);
    }
    return $all_meta;
}

function get_cached_post_meta($post_id, $key)
{
    $cache_key = 'post_meta_' . $post_id . '_' . $key;
    $value = wp_cache_get($cache_key);
    if (false === $value) {
        $value = get_post_meta($post_id, $key, true);
        wp_cache_set($cache_key, $value);
    }
    return $value;
}

function get_cached_terms($post_id, $taxonomy)
{
    $cache_key = 'terms_' . $post_id . '_' . $taxonomy;
    $terms = wp_cache_get($cache_key);
    if (false === $terms) {
        $terms = get_the_terms($post_id, $taxonomy);
        wp_cache_set($cache_key, $terms);
    }
    return $terms;
}

function get_term_names($post_id, $taxonomy, $separator = ', ')
{
    $terms = get_the_terms($post_id, $taxonomy);
    $term_names = [];

    if (!empty($terms) && !is_wp_error($terms)) {
        foreach ($terms as $term) {
            $term_names[] = $term->name;
        }
    }
    return implode($separator, $term_names);
}


function get_movie_tv_post_meta($post_id)
{
    // $start_time = microtime(true);
    // Ensure that the function only runs within the loop
    if (!$post_id) {
        return null;
    }

    $all_meta = get_all_cached_post_meta($post_id);
    $template_data = get_option('mtg_template_settings');
    // if (!function_exists('get_option_with_fallback')) {
    //     require_once FP_MOVIES_DIR . 'helper/fp_get_option_with_fallback.php';
    // }
    // $separator = get_option_with_fallback('mtg_template_post_title_separator', '-');
    if (isset($template_data['sTitle_Separator']) && !empty($template_data['sTitle_Separator'])) {
        $separator = $template_data['sTitle_Separator'];
    } else {
        $separator = '-';
    }
    // if (empty($separator)) {
    //     $separator = '-';
    // }
    // $post_type = get_cached_post_meta($post_id, 'mtg_post_type');
    // $avg_rating = get_cached_post_meta($post_id, 'mtg_vote_average');
    // // $avg_rating = number_format($avg_rating_1_decimal, 1, '.', '') || 0;

    // $fp_subtitles = get_cached_post_meta($post_id, 'mtg_subtitles');
    // $fp_subtitles_array = json_decode($fp_subtitles, true);
    $post_type = $all_meta['mtg_post_type'][0] ?? '';
    $avg_rating = $all_meta['mtg_vote_average'][0] ?? '';
    $fp_subtitles = $all_meta['mtg_subtitles'][0] ?? '';
    $fp_subtitles_array = json_decode($fp_subtitles, true) ?? [];
    $fp_subtitles_array = array_filter($fp_subtitles_array, function ($value) {
        return !empty($value);
    });

    if (!empty($fp_subtitles_array)) {
        $fp_subtitles_array = array_filter($fp_subtitles_array, function ($value) {
            return !empty($value); // Keeps only non-empty values.
        });
    }

    $audio = get_cached_terms($post_id, 'mtg_audio');
    $audio_count = !empty($audio) ? count($audio) : 0;
    $subtitle_count = !empty($fp_subtitles_array) ? count($fp_subtitles_array) : 0;
    $fp_audio = get_term_names($post_id, 'mtg_audio', $separator);

    // get all the year, and put into array
    $all_years = get_cached_terms($post_id, 'mtg_year');
    $years = [];
    if (!empty($all_years) && !is_wp_error($all_years)) {
        foreach ($all_years as $year) {
            $years[] = $year->name;
        }
    }
    // save latest and oldest year in variable
    $latest_year = '';
    $oldest_year = '';
    $audio_contain = '';
    $audio_contain_2 = '';
    $c_audio2 = '';
    $c_subs = '';
    $c_subs_2 = "";

    if (!empty($years)) {
        $latest_year = max($years);
        $oldest_year = min($years);
    }

    if ($subtitle_count == 1) {
        $c_subs = 'ESubs';
    } elseif ($subtitle_count > 1) {
        $c_subs = 'MultiSubs';
    } else {
        $c_subs = '';
    }

    if ($audio_count == 2) {
        $audio_contain = 'Dual Audio';
    } elseif ($audio_count > 2) {
        $audio_contain = 'Multi Audio';
    } else {
        $audio_contain = '';
    }

    if ($audio_count <= 2) {
        $audio_contain2 = $c_audio2;
    } else {
        $audio_contain2 = 'MULTi';
    }

    if ($subtitle_count  >= 2) {
        $c_subs_2 = 'Multi Subtitles';
    } elseif ($subtitle_count == 1) {
        $c_subs_2 = 'Single Subtitle';
    } else {
        $c_subs_2 = 'Unknown';
    }

    $default_language = wp_trim_words($fp_audio, 4, '...etc ');
    $language_extend = '';
    if (!empty($audio_contain2)) {
        $language_extend = '[' . $audio_contain . ' ' . ucwords($post_type) . ']';
    }

    $single_sc = $all_meta['mtg_single_screenshot'][0] ?? '';
    $splash_sc = $all_meta['mtg_splash_screenshot'][0] ?? '';
    // $single_sc = get_cached_post_meta($post_id, 'mtg_single_screenshot');
    // $splash_sc = get_cached_post_meta($post_id, 'mtg_splash_screenshot');

    $avg_sizes = [
        '480' => get_cached_post_meta($post_id, 'mtg_size_480p'),
        '720' => get_cached_post_meta($post_id, 'mtg_size_720p'),
        '1080' => get_cached_post_meta($post_id, 'mtg_size_1080p'),
        '2160' => get_cached_post_meta($post_id, 'mtg_size_2160p'),
    ];


    $single_sc = explode("\n", $single_sc);
    $splash_sc = explode("\n", $splash_sc);





    $meta_data = array(
        '_postType' => get_cached_post_meta($post_id, '_content_type'),
        'fp_title'  => get_the_title($post_id),
        'fp_post_type' => $post_type,
        'fp_post_type_upper' => $post_type ? ucwords($post_type) : '',
        'fp_imdb'    => get_cached_post_meta($post_id, 'mtg_imdb_id'),
        'fp_tmdb'    => get_cached_post_meta($post_id, 'mtg_tmdb_id'),
        'fp_tmdb_title' => get_cached_post_meta($post_id, 'mtg_tmdb_title'),
        'fp_tmdb_tagline' => get_cached_post_meta($post_id, 'mtg_tmdb_tagline'),
        'fp_poster'  => get_cached_post_meta($post_id, 'mtg_poster_path'),
        'fp_overview' => get_the_content(null, false, $post_id),
        'fp_backdrop' => get_cached_post_meta($post_id, 'mtg_backdrop_path'),
        'fp_release_date' => get_cached_post_meta($post_id, 'mtg_release_date'),
        'fp_single_sc' => $single_sc,
        'fp_splash_sc' => $splash_sc,
        'fp_subtitles' => $fp_subtitles,
        'fp_subtitles_array' => $fp_subtitles_array,
        'fp_subtitles_count' => $subtitle_count,
        'fp_subtitles_count_customValue' => $c_subs,
        'fp_subtitles_count_customValue_2' => $c_subs_2,
        'fp_audio_count' => $audio_count,
        'fp_audio_count_customValue' => $audio_contain,
        'fp_audio_count_customValue_2' => $audio_contain_2,
        'fp_vote_average' => $avg_rating,
        'fp_vote_count' => get_cached_post_meta($post_id, 'mtg_vote_count'),
        'fp_latest_year' => $latest_year,
        'fp_oldest_year' => $oldest_year,
        'fp_separator' => $separator,
        'fp_default_language' => $default_language,
        'fp_language_extend' => $language_extend,
        'fp_trailer' => get_cached_post_meta($post_id, 'mtg_yt_trailer'),
        'fp_player_data' => get_cached_post_meta($post_id, 'mts_player_fields'),
        'fp_links_data' => get_cached_post_meta($post_id, 'mts_links_fields'),
        'fp_sizes' => $avg_sizes,

        // Taxonomy metadata

        'fp_genres_ORG' => get_cached_terms($post_id, 'mtg_genre'),
        'fp_years_ORG' => get_cached_terms($post_id, 'mtg_year'),
        'fp_audio_ORG' => get_cached_terms($post_id, 'mtg_audio'),
        'fp_resolution_ORG' => get_cached_terms($post_id, 'mtg_resolution'),
        'fp_quality_ORG' => get_cached_terms($post_id, 'mtg_quality'),
        'fp_network_ORG' => get_cached_terms($post_id, 'mtg_network'),

        'fp_genres' => get_term_names($post_id, 'mtg_genre', $separator),
        'fp_years' => get_term_names($post_id, 'mtg_year', $separator),
        'fp_audio' => $fp_audio,
        'fp_resolution' => get_term_names($post_id, 'mtg_resolution', $separator),
        'fp_quality' => strtoupper(get_term_names($post_id, 'mtg_quality', $separator)),
        'fp_network' => get_term_names($post_id, 'mtg_network', $separator),
    );

    // $end_time = microtime(true); // End timing
    // $execution_time = $end_time - $start_time; // Calculate duration

    // error_log('Execution time for get_movie_tv_post_meta: ' . $execution_time);

    return $meta_data;
}

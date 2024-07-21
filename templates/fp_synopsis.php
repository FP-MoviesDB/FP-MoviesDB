<?php

if (!defined('ABSPATH')) exit;

class FP_PostSynopsis extends CreatePostHelper
{

    public function __construct()
    {
        $this->fp_post_synopsis(); // Call your function immediately
    }

    function fp_post_synopsis()
    {
        $post_id = get_the_ID();
        if (!$post_id) {
            return '';
        }

        $meta_data = FP_Movies_Metadata_Cache::get_meta_data($post_id);
        $template_settings = FP_Movies_Shortcodes::get_template_settings();

        $postData = array();
        if (empty($meta_data)) {
            $meta_data = get_movie_tv_post_meta($post_id);
            // error_log('Falling back to direct metadata fetching for post ID: ' . $post_id);
            $postData = array(
                'title' => $meta_data['fp_title'],
                't_title' => $meta_data['fp_tmdb_title'],
                'genre' => $meta_data['fp_genres'],
                'release_year' => $meta_data['fp_oldest_year'],
                'latest_year' => $meta_data['fp_latest_year'],
                'quality' => $meta_data['fp_quality'],
                'audio' => $meta_data['fp_audio'],
                'c_audio' => $meta_data['fp_audio_count_customValue'],
                'c_subs' => $meta_data['fp_subtitles_count_customValue'],
                'p_type' => $meta_data['fp_post_type_upper'],
                'network' => $meta_data['fp_network'],
                'separator' => $meta_data['fp_separator'],
                'post_type' => $meta_data['fp_post_type'],
            );
        } else {
            try {
                $postData = FP_Movies_Metadata_Cache::get_processed_data();
            } catch (Exception $e) {
                if (function_exists('fp_log_error')) fp_log_error('Processed data not found. Fetching directly. ' . $e);
                // error_log('Processed data not found. Fetching directly. ' . $e);
                return '';
            }
        }

        // $synopsis_title = get_option_with_fallback('mtg_template_post_info_title_shortcodes', "SYNOPSIS/PLOT:");
        $synopsis_title = $this->get_arrayValue_with_fallback($template_settings, 'sSynopsis_Title', "SYNOPSIS/PLOT:");
        $synopsis_title = $this->format_title($this->replace_template_placeholders($synopsis_title, $postData));

        ?>

        <div class="fp_post_synopsis_wrapper">
            <h2 class="fp_post_synopsis_heading"><?php echo $synopsis_title; ?></h2>
            <p class="fp_post_synopsis_content"><?php echo $meta_data['fp_overview']; ?></p>
        </div>

        <?php


    }
}
new FP_PostSynopsis();
?>
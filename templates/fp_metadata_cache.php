<?php
// Prevent direct access
if (!defined('ABSPATH')) exit;

class FP_Movies_Metadata_Cache {
    private static $meta_data = null;
    private static $processed_data = null;

    public static function get_meta_data($post_id) {
        if (self::$meta_data === null) {
            self::$meta_data = get_movie_tv_post_meta($post_id);
            self::process_meta_data();
        }
        return self::$meta_data;
    }

    private static function process_meta_data() {
        $data = self::$meta_data;
        // $separator = get_option_with_fallback('mtg_template_post_title_separator', '-');

        // Process and structure the metadata
        self::$processed_data = [
            'title' => $data['fp_title'],
            't_title' => $data['fp_tmdb_title'],
            'release_year' => $data['fp_oldest_year'],
            'latest_year' => $data['fp_latest_year'],
            'quality' => $data['fp_quality'],
            'audio' => $data['fp_audio'],
            'c_audio' => $data['fp_audio_count_customValue'],
            'c_subs' => $data['fp_subtitles_count_customValue'],
            'p_type' => $data['fp_post_type_upper'],
            'network' => $data['fp_network'],
            'separator' => $data['fp_separator'],
            'post_type' => $data['fp_post_type'],
            'resolutions' => $data['fp_resolution'],
        ];
    }

    public static function get_processed_data() {
        if (self::$processed_data === null) {
            throw new Exception("Metadata must be fetched before accessing processed data.");
        }
        return self::$processed_data;
    }
}


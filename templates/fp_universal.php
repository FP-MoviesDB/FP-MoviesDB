<?php
if (!defined('ABSPATH')) exit;

if (!class_exists('FP_Universal_Shortcode')) {
    class FP_Universal_Shortcode extends CreatePostHelper {
        function __construct() {
            // Constructor logic if needed
        }

        function fp_display_universal($atts) {

            fp_log_error('Universal shortcode attributes: ' . json_encode($atts));
            $post_id = get_the_ID();
            if (!$post_id) return 'noPostID';

            $default_atts = [
                'content' => 'No content provided',
                'type' => 'text'
            ];

            $atts = shortcode_atts($default_atts, $atts, 'fp_universal_view');
            $final_content = '';

            $content_type = $atts['type'];

            if ($content_type === 'text') {
                $final_content = esc_html($atts['content']);
            } else if ($content_type === 'html') {
                $final_content = htmlspecialchars_decode($atts['content']);
            }

            fp_log_error('Universal content (func): ' . $final_content);
            fp_log_error('Universal contentType: ' . $content_type);
            
            if (!$final_content) return '';

            // Load post metadata
            $meta_data = FP_Movies_Metadata_Cache::get_meta_data($post_id);
            if (!$meta_data) {
                $meta_data = get_movie_tv_post_meta($post_id);
            }

            $postData = $this->prepare_post_data($meta_data);

            fp_log_error('Universal shortcode post data: ' . json_encode($postData));


            $final_content = $this->format_title($this->replace_template_placeholders_2($final_content, $postData));

            fp_log_error('Universal final_content: ' . $final_content);

            return $final_content ? "<div class='post-universal-wrapper'>$final_content</div>" : '';
        }

        private function prepare_post_data($meta_data) {
            return [
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
            ];
        }
    }
}

// Initialize the shortcode class only when needed


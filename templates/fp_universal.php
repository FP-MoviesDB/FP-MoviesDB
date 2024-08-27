<?php
if (!defined('ABSPATH')) exit;

if (!class_exists('FP_Universal_Shortcode')) {
    class FP_Universal_Shortcode extends CreatePostHelper
    {
        function __construct()
        {
            // Constructor logic if needed
        }

        function fp_display_universal($atts)
        {

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

            $d_content = base64_decode($atts['content']);

            fp_log_error('Universal DECODED (base64): ' . $d_content);

            if ($content_type === 'text') {
                // fp_log_error('Universal content (text): ' . esc_html($d_content));
                $final_content = esc_html($d_content);
            } else if ($content_type === 'html') {
                $final_content = htmlspecialchars_decode($d_content);
            } else if ($content_type === 'shortcode') {

                if (preg_match('/^\[.*\]$/', trim($d_content))) {
                    // Extract the shortcode tag to check its validity
                    preg_match('/\[([a-zA-Z0-9-_]+)[\s\S]*\]/', trim($d_content), $matches);
                    $shortcode_tag = $matches[1] ?? '';

                    if ($shortcode_tag && shortcode_exists($shortcode_tag)) {
                        // Process it as a shortcode if it exists
                        $final_content = do_shortcode($d_content);
                    } else {
                        // fp_log_error('Universal shortcode: Invalid or non-existent shortcode.');
                        $final_content = esc_html($d_content); // Treat as text if invalid
                    }
                } else {
                    // Otherwise, treat it as regular text
                    $final_content = esc_html($d_content);
                }
            } else if ($content_type === 'php') {

                $decoded_php = htmlspecialchars_decode($d_content);

                if (preg_match('/^<\?php/', trim($decoded_php))) {
                    // If it starts with <?php, execute it as PHP
                    ob_start();
                    try {
                        eval('?>' . $decoded_php);
                        $final_content = ob_get_clean();
                    } catch (ParseError $e) {
                        ob_end_clean();
                        fp_log_error('Universal shortcode: PHP execution error: ' . $e->getMessage());
                        $final_content = '';
                    }
                } else {
                    // Otherwise, treat it as regular text
                    $final_content = esc_html($d_content);
                }
            }

            if (!$final_content) return '';

            // Load post metadata
            $meta_data = FP_Movies_Metadata_Cache::get_meta_data($post_id);
            if (!$meta_data) {
                $meta_data = get_movie_tv_post_meta($post_id);
            }

            $postData = $this->prepare_post_data($meta_data);

            // fp_log_error('Universal shortcode post data: ' . json_encode($postData));


            $final_content = $this->format_title($this->replace_template_placeholders_2($final_content, $postData));

            fp_log_error('Universal final_content: ' . $final_content);

            return $final_content ? "<div class='post-universal-wrapper'>$final_content</div>" : '';
        }

        private function prepare_post_data($meta_data)
        {
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

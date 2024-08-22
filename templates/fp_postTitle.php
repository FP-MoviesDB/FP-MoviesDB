<?php

if (!defined('ABSPATH')) exit;

if (!class_exists('FP_PostTitle')) {
    class FP_PostTitle extends CreatePostHelper
    {

        public function __construct()
        {
            $this->fp_postTitle(); // Call your function immediately
        }

        function fp_postTitle()
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
                    if (function_exists('fp_log_error')) fp_log_error('Processed data not found. Fetching directly.');
                    return '';
                }
            }


            // $user_title = get_option_with_fallback('mtg_template_post_title', "{title} ({l_year}) {p_type}");
            $user_title = $this->get_arrayValue_with_fallback($template_settings, 'sPost_Title', "{title} ({l_year}) {p_type}");
            $post_title = $this->format_title($this->replace_template_placeholders($user_title, $postData));

?>
            <div class="fp-post-title-wrapper">
                <h1 class="fp-post-title-head"><?php echo esc_html($post_title); ?></h1>
            </div>
<?php

        }
    }
}
new FP_PostTitle();
?>
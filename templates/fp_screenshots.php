<?php

if (!defined('ABSPATH')) exit;

class FP_PostScreenshots extends CreatePostHelper
{
    public function __construct()
    {
        // get data for option 'mtg_template_settings' and store it in $this->template_data
        $template_settings = FP_Movies_Shortcodes::get_template_settings();
        $this->fp_screenshotsView($template_settings);
    }

    function fp_screenshotsView($tData = array())
    {
        $post_id = get_the_ID();

        if (!$post_id) {
            return '';
        }

        $meta_data = FP_Movies_Metadata_Cache::get_meta_data($post_id);
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
                if (function_exists('fp_log_error')) fp_log_error('Processed data not found. Fetching directly.');
                // error_log('Processed data not found. Fetching directly.');
                return '';
            }
        }


        // $screenshot_title = get_option_with_fallback('mtg_template_post_screenshot_title_shortcodes', "{title} {p_type} Screenshots");
        $screenshot_title = $this->get_arrayValue_with_fallback($tData, 'sScreenshot_Title', "{title} {p_type} Screenshots");
        $screenshot_title = $this->format_title($this->replace_template_placeholders($screenshot_title, $postData));

        // $single_sc_limit = get_option_with_fallback('mtg_template_single_screenshot_limit', 7);
        $single_sc_limit = $this->get_arrayValue_with_fallback($tData, 'sSingle_Screenshot_Limit', 7);
        $splash_sc_limit = $this->get_arrayValue_with_fallback($tData, 'sSplash_Screenshot_Limit', 3);
        // $single_sc_limit = get_arrayValue_with_fallback($tData, 'mtg_template_single_screenshot_limit', 7);

        // $splash_sc_limit = get_option_with_fallback('mtg_template_splash_screenshot_limit', 3);
        $single_sc_limit = intval($single_sc_limit);
        $splash_sc_limit = intval($splash_sc_limit);
        if (!is_int($single_sc_limit) || $single_sc_limit > 10) {
            $single_sc_limit = 7;
        }
        if (!is_int($splash_sc_limit) || $splash_sc_limit > 5) {
            $splash_sc_limit = 3;
        }
        $single_sc = $meta_data['fp_single_sc'];
        $splash_sc = $meta_data['fp_splash_sc'];
        shuffle($single_sc);
        shuffle($splash_sc);
        $single_sc = array_slice($single_sc, 0, $single_sc_limit);
        $splash_sc = array_slice($splash_sc, 0, $splash_sc_limit);
?>
        <div class="image-gallery">
            <span class="image-gallery-heading" style="margin: 20px 0px; width: 100%; text-align: center;">
                <h2><?php echo esc_html($screenshot_title); ?></h2>
            </span>
            <div class="single-image-gallery">
                <?php foreach ($single_sc as $index => $image_url) : ?>
                    <?php if ($image_url) : ?>
                        <div class="fp-img-wrapper">
                            <div class="loading-animation">
                                <div class="loading-spinner"></div>
                            </div>
                            <img id="single-image-<?php echo esc_attr($index); ?>" data-src="<?php echo esc_url(trim($image_url)); ?>" src="<?php echo esc_url(trim($image_url)); ?>" alt="Download <?php echo esc_attr($meta_data['fp_title']) ?> (<?php echo esc_attr($meta_data['fp_latest_year']) ?>) <?php echo esc_attr($meta_data['fp_post_type_upper']) ?>" title="Download <?php echo esc_attr($meta_data['fp_title']) ?> (<?php echo esc_attr($meta_data['fp_latest_year']) ?>) <?php echo esc_attr($meta_data['fp_post_type_upper']) ?>" class="fp-screenshot-responsive-image" loading="lazy" width="100%" height="auto" />
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
                <?php foreach ($splash_sc as $index => $image_url) : ?>
                    <?php if ($image_url) : ?>
                        <div class="fp-img-wrapper">
                            <div class="loading-animation">
                                <div class="loading-spinner"></div>
                            </div>
                            <img id="splash-image-<?php echo esc_attr($index); ?>" data-src="<?php echo esc_url(trim($image_url)); ?>" src="<?php echo esc_url(trim($image_url)); ?>" alt="Download <?php echo esc_attr($meta_data['fp_title']) ?> (<?php echo esc_attr($meta_data['fp_latest_year']) ?>) <?php echo esc_attr($meta_data['fp_post_type_upper']) ?> Screenshots" title="Download <?php echo esc_attr($meta_data['fp_title']) ?> (<?php echo esc_attr($meta_data['fp_latest_year']) ?>) <?php echo esc_attr($meta_data['fp_post_type_upper']) ?> Screenshots" class="fp-screenshot-responsive-image" loading="lazy" width="100%" height="auto">

                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>

<?php
    }
}
new FP_PostScreenshots();
?>
<?php

if (!defined('ABSPATH')) exit;

class FP_PostLinks extends CreatePostHelper
{
    public function __construct()
    {
        $this->fp_postLinks();
    }

    function fp_postLinks()
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
                if (function_exists('fp_log_error')) fp_log_error('Processed data not found. Fetching directly.');
                // error_log('Processed data not found. Fetching directly.');
                return '';
            }
        }

        $_postType = $meta_data['_postType'];
        $tmdb_id = $meta_data['fp_tmdb'];
        $user_links_data = $meta_data['fp_links_data'];
        require_once FP_MOVIES_DIR . 'helper/fp_links_encryption.php';

        global $fp_min_m;
        $allowed_html = array(
            'div' => array('class' => array()),
            'a' => array('href' => array(), 'target' => array()),
            'span' => array('class' => array()),
            'br' => array()
        );

        $allowed_html_tv = array(
            'div' => array(
                'class' => array(),
                'id' => array(),
                'style' => array(),
                'onclick' => array()
            ),
            'a' => array(
                'href' => array(),
                'class' => array(),
                'target' => array()
            ),
            'span' => array(
                'class' => array()
            )
        );
        if ($_postType == 'movie') {
            wp_enqueue_style('fp-post-movie-links-css', esc_url(FP_MOVIES_URL) . '/templates/css/fp_movieLinks' . $fp_min_m . '.css', array(), FP_MOVIES_FILES, 'all');
            require_once FP_MOVIES_DIR . 'templates/links/fp_movieLinks.php';
            $movieLinks = new FPMovieLinks();
            $movie_data = $movieLinks->fp_movies_links($tmdb_id, $meta_data, $user_links_data);
            // $user_title = get_option_with_fallback('mtg_template_movies_links_title', "{title} {p_type} Links");
            $user_title = $this->get_arrayValue_with_fallback($template_settings, 'sLinks_Movies_Title', "{title} {p_type} Links");
        } else if ($_postType == 'tv') {
            wp_enqueue_script('fp-post-link-js', esc_url(FP_MOVIES_URL) . '/templates/js/fp_postLinks' . $fp_min_m . '.js', array(), FP_MOVIES_FILES, true);
            wp_enqueue_script('fp-post-tv-links-js', esc_url(FP_MOVIES_URL) . '/templates/js/fp_seriesLinks' . $fp_min_m . '.js', array(), FP_MOVIES_FILES, true);
            wp_enqueue_style('fp-post-tv-links-css', esc_url(FP_MOVIES_URL) . '/templates/css/fp_seriesLinks' . $fp_min_m . '.css', array(), FP_MOVIES_FILES, 'all');
            require_once FP_MOVIES_DIR . 'templates/links/fp_tvLinks.php';
            $tv_links = new FPSeriesLinks();
            $tv_data = $tv_links->fp_series_links($tmdb_id, $meta_data, $user_links_data);
            $seasons_output = $tv_data['seasons_output'];
            $packs_output = $tv_data['packs_output'];
            // $user_title = get_option_with_fallback('mtg_template_series_links_title', "{title} {p_type} Links");
            $user_title = $this->get_arrayValue_with_fallback($template_settings, 'sLinks_Series_Title', "{title} {p_type} Links");
        }
        $post_download_title = $this->format_title($this->replace_template_placeholders($user_title, $postData));

?>


        <div class='post-links-wrapper'>
            <div class="post-links-title">
                <h2><?php echo esc_html($post_download_title); ?></h2>
            </div>
            <!-- removed  id="moviesDetails" from below div -->
            <div class='post-download-links-wrapper'>
                <?php
                if ($_postType == 'movie') {
                    if (!empty($movie_data)) {
                        echo '<div class="links-preview">' . wp_kses($movie_data, $allowed_html) . '</div>';
                    }
                } else {
                    if (!empty($seasons_output)) {
                        echo wp_kses($seasons_output, $allowed_html);
                    }

                    // Check if there are any packs data before displaying the pack section
                    if (!empty($packs_output)) {
                        echo '<div class="down-btn" onclick="togglePacks()">Season PACKs</div>';
                        echo '<div id="season-pack-content" style="display: none;">';
                        echo wp_kses('<div class="mdownlinks mdownlinks-zip">' . $packs_output . '</div>', $allowed_html);
                        echo '</div>';
                    }
                ?>
                <?php
                }
                ?>
            </div>
        </div>
<?php
    }
}
new FP_PostLinks();
?>
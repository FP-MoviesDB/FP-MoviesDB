<?php
// Prevent direct access
if (!defined('ABSPATH')) exit;

class FP_Movies_Shortcodes
{

    private static $css_enqueued = false;
    private static $js_enqueued = false;
    private static $php_enqueued = false;
    private static $get_post_meta_enqueued = false;
    private static $fallback_func_enqueued = false;
    private static $meta_data_enqueued = false;
    private static $font_enqueued = false;
    private static $view_enqueued = false;

    private static $isCacheEnabled = null;
    private static $current_post_id = null;
    private static $current_post_type = null;

    private static $color_settings = null;
    private static $template_settings = null;

    public static function init()
    {
        require_once FP_MOVIES_DIR . 'templates/home/fp_homepage_ajax.php';
        require_once FP_MOVIES_DIR . 'templates/global/views.php';
        self::$template_settings = get_option('mtg_template_settings', []);
        self::$color_settings = get_option('mtg_color_settings', []);
        add_shortcode('fp-post-title', array(__CLASS__, 'shortcode_post_title'));
        add_shortcode('fp-post-player', array(__CLASS__, 'shortcode_post_player'));
        add_shortcode('fp-post-info', array(__CLASS__, 'shortcode_post_info'));
        add_shortcode('fp-imdb-box-view', array(__CLASS__, 'shortcode_imdb_view'));
        add_shortcode('fp-screenshot-view', array(__CLASS__, 'shortcode_screenshots'));
        add_shortcode('fp-synopsis-view', array(__CLASS__, 'shortcode_synopsis'));
        add_shortcode('fp-post-links', array(__CLASS__, 'shortcode_links'));
        add_shortcode('fp-homepage-view', array(__CLASS__, 'fp_homepage_view'));
    }


    public static function enqueue_local_poppins_font()
    {
        global $fp_min_m;
        wp_enqueue_style('local-poppins-font', FP_MOVIES_URL . 'fonts/poppins' . $fp_min_m . '.css', array(), FP_MOVIES_FILES, 'all');
    }

    private static function enqueue_global_files()
    {
        // error_log('Attempting to hook Font Awesome');

        // add_action('wp_enqueue_scripts', array(__CLASS__, 'fp_enqueue_fontAwesome'));
        
        global $post;
        global $fp_min_m;

        // if (function_exists('fp_log_error')) fp_log_error('FP_MIN_M: ' . $fp_min_m);
        
        if (!self::$current_post_id) {
            self::$current_post_id = get_the_ID();
            if (!self::$current_post_id && $post) {
                // error_log('No post ID found | 1st attempt');

                self::$current_post_id = $post->ID;
            }
            if (!self::$current_post_id) {
                // error_log('No post ID found | 2nd attempt');
                return;
            }
            self::$current_post_type = get_post_meta(self::$current_post_id, '_content_type', true);
        }

        if (self::$current_post_id && !self::$current_post_type) {
            self::$current_post_type = get_post_meta(self::$current_post_id, '_content_type', true);
        }


        // wp_enqueue_style('fontawesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css', array(), '5.15.3', 'all');

        if (!self::$php_enqueued) {
            require_once FP_MOVIES_DIR . 'templates/global/global.php';
            self::$php_enqueued = true;
        }

        if (!self::$font_enqueued) {
            // error_log('Enqueuing local font');
            add_action('wp_enqueue_scripts', array(__CLASS__, 'enqueue_local_poppins_font'));
            self::$font_enqueued = true;
        }

        if (!self::$css_enqueued) {
            wp_enqueue_style('fp_movies_global_css', FP_MOVIES_URL . '/templates/global/global' . $fp_min_m . '.css', array(), FP_MOVIES_FILES, 'all');
            self::$css_enqueued = true;
        }
        if (!self::$js_enqueued) {
            wp_enqueue_script('fp_movies_global_js', FP_MOVIES_URL . '/templates/global/global' . $fp_min_m . '.js', array('jquery'), FP_MOVIES_FILES, true);
            if (is_singular('post')) {
                wp_localize_script('fp_movies_global_js', 'local_ajax_object', array(
                    'ajax_url' => FP_MOVIES_AJAX,
                    'nonce' => wp_create_nonce('track_post_views_nonce'),
                    'post_id' => get_the_ID()
                ));
            }
            self::$js_enqueued = true;
        }
        if (!self::$get_post_meta_enqueued) {
            require_once FP_MOVIES_DIR . 'helper/fp_get_all_meta.php';
            self::$get_post_meta_enqueued = true;
        }
        if (!self::$fallback_func_enqueued) {
            require_once FP_MOVIES_DIR . 'helper/fp_get_option_with_fallback.php';
            self::$fallback_func_enqueued = true;
        }
        if (!defined('FP_DOWNLOAD_URL')) {
            $dBU = !empty(self::$template_settings['sDownload_BaseURL']) ? self::$template_settings['sDownload_BaseURL'] : 'https://fpgo.xyz';
            define('FP_DOWNLOAD_URL', $dBU);
        }

        if (!self::$meta_data_enqueued) {
            require_once FP_MOVIES_DIR . 'templates/fp_metadata_cache.php';
            self::$meta_data_enqueued = true;
        }

        if (!defined('FP_MOVIES_HELPER_LOADED')) {
            define('FP_MOVIES_HELPER_LOADED', true);
            require_once FP_MOVIES_DIR . 'inc/helper.php';
        }

        // run once
        if (self::$isCacheEnabled === null) {
            $user_cache_value = self::$template_settings['enable_shortcode_cache'] ?? '0';
            // $cache_value = get_option_with_fallback('mtg_template_enable_shortcode_cache', '0');
            self::$isCacheEnabled = $user_cache_value === '1' ? true : false;
            // error_log('Cache enabled: ' . self::$isCacheEnabled);
        }
    }

    private static function enqueue_views_files()
    {
        global $fp_min_m;
        if (!self::$view_enqueued) {
            wp_enqueue_script('fp_views_global_js', FP_MOVIES_URL . '/templates/global/views' . $fp_min_m . '.js', array('jquery'), FP_MOVIES_FILES, true);
            wp_localize_script('fp_views_global_js', 'fp_views_data', array(
                'ajax_url' => FP_MOVIES_AJAX,
                'nonce' => wp_create_nonce('fp_views_nonce'),
                'post_id' => get_the_ID()
            ));
            self::$view_enqueued = true;
        }
    }

    public static function get_color_option($option_name, $default = '')
    {
        if (isset(self::$color_settings[$option_name])) {
            return self::$color_settings[$option_name];
        }
        return $default;
    }

    public static function get_template_settings() {
        if (self::$template_settings === null) {
            self::$template_settings = get_option('mtg_template_settings', []);
        }
        return self::$template_settings;
    }

    public static function shortcode_post_title($atts)
    {
        // $start = microtime(true);
        global $fp_min_m;
        self::enqueue_views_files();
        self::enqueue_global_files();
        wp_enqueue_style('fp-post-title', FP_MOVIES_URL . '/templates/css/fp_postTitle' . $fp_min_m . '.css', array(), FP_MOVIES_FILES, 'all');
        // $post_title_color = get_option_with_fallback('mtg_post_title_color', "white");
        // $post_title_wrapper_color = get_option_with_fallback('mtg_post_title_wrapper_color', "black");
        $post_title_color = self::get_color_option('post_title_color', "white");
        $post_title_wrapper_color = self::get_color_option('post_title_wrapper_color', "black");
        $custom_css = "
        :root {
            --fp-post-title-color: $post_title_color;
            --fp-post-title-wrapper-color: $post_title_wrapper_color;
        }
        ";
        wp_add_inline_style('fp-post-title', $custom_css);

        ob_start();
        include FP_MOVIES_DIR . 'templates/fp_postTitle.php';
        // $end = microtime(true);
        // error_log('Time taken for TITLE: ' . ($end - $start)) . 'PostID: ' . self::$current_post_id;
        return ob_get_clean();
    }

    public static function shortcode_imdb_view($atts)
    {
        global $fp_min_m;
        $start = microtime(true);
        self::enqueue_views_files();
        self::enqueue_global_files();
        wp_enqueue_style('fp-tmdb-view', FP_MOVIES_URL . '/templates/css/fp_imdbBox' . $fp_min_m . '.css', array(), FP_MOVIES_FILES, 'all');

        // $imdb_wrapper_bg_color = get_option_with_fallback('mtg_imdb_wrapper_bg_color', "black");
        // $imdb_box_bg_color = get_option_with_fallback('mtg_imdb_box_bg_color', "#222");
        // $imdb_title_color = get_option_with_fallback('mtg_imdb_title_color', "white");
        // $imdb_title_year_color = get_option_with_fallback('mtg_imdb_title_year_color', "white");
        // $imdb_meta_key_items = get_option_with_fallback('mtg_imdb_meta_key_color', "white");
        // $imdb_genre_color = get_option_with_fallback('mtg_imdb_genre_color', "rgb(140, 196, 17)");
        // $imdb_audio_color = get_option_with_fallback('mtg_imdb_audio_color', "#ec39d9");
        // $imdb_network_color = get_option_with_fallback('mtg_imdb_network_color', "cornflowerblue");
        // $imdb_rating_color = get_option_with_fallback('mtg_imdb_rating_color', "white");
        // $imdb_teaser_color = get_option_with_fallback('mtg_imdb_teaser_color', "white");

        $imdb_wrapper_bg_color = self::get_color_option('imdb_wrapper_bg_color', "black");
        $imdb_box_bg_color = self::get_color_option('imdb_box_bg_color', "#222");
        $imdb_title_color = self::get_color_option('imdb_title_color', "white");
        $imdb_title_year_color = self::get_color_option('imdb_title_year_color', "white");
        $imdb_meta_key_items = self::get_color_option('imdb_meta_key_color', "white");
        $imdb_genre_color = self::get_color_option('imdb_genre_color', "rgb(140, 196, 17)");
        $imdb_audio_color = self::get_color_option('imdb_audio_color', "#ec39d9");
        $imdb_network_color = self::get_color_option('imdb_network_color', "cornflowerblue");
        $imdb_rating_color = self::get_color_option('imdb_rating_color', "white");
        $imdb_teaser_color = self::get_color_option('imdb_teaser_color', "white");



        $custom_css1 = "
        :root {
            --imdb-wrapper-bg-color: $imdb_wrapper_bg_color;
            --imdb-box-bg-color: $imdb_box_bg_color;
            --imdb-title-color: $imdb_title_color;
            --imdb-title-year-color: $imdb_title_year_color;
            --imdb-genre-color: $imdb_genre_color;
            --imdb-audio-color: $imdb_audio_color;
            --imdb-network-color: $imdb_network_color;
            --imdb-rating-color: $imdb_rating_color;
            --imdb-teaser-color: $imdb_teaser_color;
            --imdb-meta-key-color: $imdb_meta_key_items;
            }
            /* End of FP Movies Custom CSS Variables */\n";
        wp_add_inline_style('fp-tmdb-view', $custom_css1);
        ob_start();
        include FP_MOVIES_DIR . 'templates/fp_imdbBox.php';
        $end = microtime(true);
        // error_log('Time taken for IMDB: ' . ($end - $start)) . 'PostID: ' . self::$current_post_id;
        return ob_get_clean();
    }

    public static function shortcode_screenshots($atts)
    {
        global $fp_min_m;
        $start = microtime(true);
        self::enqueue_views_files();
        self::enqueue_global_files();
        wp_enqueue_style('fp-screenshot-view', FP_MOVIES_URL . '/templates/css/fp_screenshots' . $fp_min_m . '.css', array(), FP_MOVIES_FILES, 'all');
        wp_enqueue_script('fp-screenshot-view', FP_MOVIES_URL . '/templates/js/fp_screenshots' . $fp_min_m . '.js', array(), FP_MOVIES_FILES, true);
        // $image_gallery_bg_color = get_option_with_fallback('mtg_screenshot_gallery_bg_color', "black");
        // $image_gallery_heading_color = get_option_with_fallback('mtg_screenshot_gallery_heading_color', "white");

        $image_gallery_bg_color = self::get_color_option('screenshot_gallery_bg_color', "black");
        $image_gallery_heading_color = self::get_color_option('screenshot_gallery_heading_color', "white");

        $custom_css = "
        :root {
            --image-gallery-bg-color: $image_gallery_bg_color;
            --image-gallery-heading-color: $image_gallery_heading_color;
        }
        ";
        wp_add_inline_style('fp-screenshot-view', $custom_css);
        ob_start();
        require_once FP_MOVIES_DIR . 'templates/fp_screenshots.php';
        $end = microtime(true);
        // error_log('Time taken for SCREENSHOTS: ' . ($end - $start)) . 'PostID: ' . self::$current_post_id;
        return ob_get_clean();
    }

    public static function shortcode_post_info($atts)
    {
        // $start = microtime(true);
        global $fp_min_m;
        self::enqueue_views_files();
        self::enqueue_global_files();
        wp_enqueue_style('fp-post-info', FP_MOVIES_URL . '/templates/css/fp_postInfo' . $fp_min_m . '.css', array(), FP_MOVIES_FILES, 'all');
        // $post_info_wrapper_bg_color = get_option_with_fallback('mtg_post_info_wrapper_bg_color', "black");
        // $post_info_heading_color = get_option_with_fallback('mtg_post_info_heading_color', "white");
        // $post_info_li_color = get_option_with_fallback('mtg_post_info_li_color', "white");
        // $post_info_li_span_color = get_option_with_fallback('mtg_post_info_li_span_color', "white");

        $post_info_wrapper_bg_color = self::get_color_option('post_info_wrapper_bg_color', "black");
        $post_info_heading_color = self::get_color_option('post_info_heading_color', "white");
        $post_info_li_color = self::get_color_option('post_info_li_color', "white");
        $post_info_li_span_color = self::get_color_option('post_info_li_span_color', "white");

        $custom_css = "
        :root {
            --fp-post-info-wrapper-bg-color: $post_info_wrapper_bg_color;
            --fp-post-info-heading-color: $post_info_heading_color;
            --fp-post-info-li-color: $post_info_li_color;
            --fp-post-info-li-span-color: $post_info_li_span_color;
        }
        ";
        wp_add_inline_style('fp-post-info', $custom_css);
        // add custom js from templates/js/fp_postInfo.js
        ob_start();


        // require_once FP_MOVIES_DIR . 'templates/fp_postInfo.php';
        include(FP_MOVIES_DIR . 'templates/fp_postInfo.php');
        $end = microtime(true);
        // error_log('Time taken for INFO: ' . ($end - $start)) . 'PostID: ' . self::$current_post_id;
        // return ob_get_clean();
        $content = ob_get_clean();
        $content = trim($content);
        $content = preg_replace('/\s+/', ' ', $content);
        $content = str_replace("> <", "><", $content);
        return $content;
    }

    public static function shortcode_synopsis($atts)
    {
        $start = microtime(true);
        global $fp_min_m;
        self::enqueue_views_files();
        self::enqueue_global_files();
        wp_enqueue_style('fp-synopsis-view', FP_MOVIES_URL . '/templates/css/fp_synopsis' . $fp_min_m . '.css', array(), FP_MOVIES_FILES, 'all');
        // $synopsis_wrapper_bg_color = get_option_with_fallback('mtg_synopsis_wrapper_bg_color', "black");
        // $synopsis_heading_color = get_option_with_fallback('mtg_synopsis_heading_color', "white");
        // $synopsis_content_color = get_option_with_fallback('mtg_synopsis_content_color', "white");

        $synopsis_wrapper_bg_color = self::get_color_option('synopsis_wrapper_bg_color', "black");
        $synopsis_heading_color = self::get_color_option('synopsis_heading_color', "white");
        $synopsis_content_color = self::get_color_option('synopsis_content_color', "white");

        $custom_css = "
        :root {
            --fp-synopsis-wrapper-bg-color: $synopsis_wrapper_bg_color;
            --fp-synopsis-heading-color: $synopsis_heading_color;
            --fp-synopsis-content-color: $synopsis_content_color;
        }
        ";
        wp_add_inline_style('fp-synopsis-view', $custom_css);

        ob_start();
        include FP_MOVIES_DIR . 'templates/fp_synopsis.php';
        $end = microtime(true);
        // error_log('Time taken for SYNOPSIS: ' . ($end - $start)) . 'PostID: ' . self::$current_post_id;
        return ob_get_clean();
    }

    public static function shortcode_links($atts)
    {
        $start = microtime(true);
        global $fp_min_m;
        self::enqueue_views_files();
        self::enqueue_global_files();
        wp_enqueue_style('fp-post-links', FP_MOVIES_URL . '/templates/css/fp_postLinks' . $fp_min_m . '.css', array(), FP_MOVIES_FILES, 'all');
        /*
            $post_links_wrapper_bg_color = get_option_with_fallback('mpl_wrapper_bg_color', "#000000");
            $post_links_heading_color = get_option_with_fallback('mpl_heading_color', "#ffffff");
            $post_links_mov_single_item = get_option_with_fallback('mpl_movie_single_item_color', "#ffffff");

            $post_links_mov_single_item_hover = get_option_with_fallback('mpl_movie_single_item_hover_color', "#808080");
            $post_links_mov_single_item_bg = get_option_with_fallback('mpl_movie_single_item_bg_color', "#242222");

            $post_links_mov_single_item_size = get_option_with_fallback('mpl_single_item_size', "#ff8f00");
            $post_links_mov_single_item_quality = get_option_with_fallback('mpl_single_item_quality', "#0c97c2");
            $post_links_mov_single_item_audio = get_option_with_fallback('mpl_single_item_audio', "#9b0a6a");

            $post_links_tv_season_bg = get_option_with_fallback('mpl_tv_season_bg_color', "#004dbb");
            $post_links_tv_season_bg_hover = get_option_with_fallback('mpl_tv_season_bg_color_hover', "#03268e");
            $post_links_tv_season_color = get_option_with_fallback('mpl_tv_season_color', "#ffffff");

            $post_links_tv_quality_bg_color = get_option_with_fallback('mpl_tv_quality_bg_color', "#059862");
            $post_links_tv_quality_color_hover = get_option_with_fallback('mpl_tv_quality_bg_color_hover', "#d2c315");
            $post_links_tv_quality_color_hover_color = get_option_with_fallback('mpl_tv_quality_bg_color_hover_color', "#000000");
            $post_links_tv_quality_color = get_option_with_fallback('mpl_tv_quality_color', "#ffffff");

            $post_links_tv_ep_pack_item_bg_color = get_option_with_fallback('mpl_tv_ep_pack_item_bg_color', "#282727");
            $post_links_tv_ep_pack_item_bg_color_hover = get_option_with_fallback('mpl_tv_ep_pack_item_bg_color_hover', "#3e3838");

            $post_links_tv_episode_color = get_option_with_fallback('mpl_tv_episode_color', "#ffffff");
            $post_links_tv_episode_meta_color = get_option_with_fallback('mpl_tv_episode_meta_color', "#708090");
            $post_links_tv_episode_packs_single_season_color = get_option_with_fallback('mpl_tv_episode_packs_single_season_color', "#d2691e");
        */
        $post_links_wrapper_bg_color = self::get_color_option('wrapper_bg_color', "#000000");
        $post_links_heading_color = self::get_color_option('heading_color', "#ffffff");
        $post_links_mov_single_item = self::get_color_option('movie_single_item_color', "#ffffff");

        $post_links_mov_single_item_hover = self::get_color_option('movie_single_item_hover_color', "#808080");
        $post_links_mov_single_item_bg = self::get_color_option('movie_single_item_bg_color', "#242222");

        $post_links_mov_single_item_size = self::get_color_option('single_item_size', "#ff8f00");
        $post_links_mov_single_item_quality = self::get_color_option('single_item_quality', "#0c97c2");
        $post_links_mov_single_item_audio = self::get_color_option('single_item_audio', "#9b0a6a");

        $post_links_tv_season_bg = self::get_color_option('tv_season_bg_color', "#004dbb");
        $post_links_tv_season_bg_hover = self::get_color_option('tv_season_bg_color_hover', "#03268e");
        $post_links_tv_season_color = self::get_color_option('tv_season_color', "#ffffff");

        $post_links_tv_quality_bg_color = self::get_color_option('tv_quality_bg_color', "#059862");
        $post_links_tv_quality_color_hover = self::get_color_option('tv_quality_bg_color_hover', "#d2c315");
        $post_links_tv_quality_color_hover_color = self::get_color_option('tv_quality_bg_color_hover_color', "#000000");
        $post_links_tv_quality_color = self::get_color_option('tv_quality_color', "#ffffff");

        $post_links_tv_ep_pack_item_bg_color = self::get_color_option('tv_ep_pack_item_bg_color', "#282727");
        $post_links_tv_ep_pack_item_bg_color_hover = self::get_color_option('tv_ep_pack_item_bg_color_hover', "#3e3838");

        $post_links_tv_episode_color = self::get_color_option('tv_episode_color', "#ffffff");
        $post_links_tv_episode_meta_color = self::get_color_option('tv_episode_meta_color', "#708090");
        $post_links_tv_episode_packs_single_season_color = self::get_color_option('tv_episode_packs_single_season_color', "#d2691e");
        $custom_css = "
        :root {
            --fp-post-links-wrapper-bg-color: $post_links_wrapper_bg_color;
            --fp-post-links-title-color: $post_links_heading_color;
            --fp-post-link-movie-single-item-color: $post_links_mov_single_item;
            --fp-post-link-movie-single-item-hover-bg-color: $post_links_mov_single_item_hover;
            --fp-post-link-movie-single-item-bg-color: $post_links_mov_single_item_bg;

            --fp-post-link-meta-item-size: $post_links_mov_single_item_size;
            --fp-post-link-meta-item-quality: $post_links_mov_single_item_quality;
            --fp-post-link-meta-item-audio: $post_links_mov_single_item_audio;

            --fp-post-link-tv-season-bg-color: $post_links_tv_season_bg;
            --fp-post-link-tv-season-bg-color-hover: $post_links_tv_season_bg_hover;
            --fp-post-link-tv-season-color: $post_links_tv_season_color;
            --fp-post-link-tv-quality-bg-color: $post_links_tv_quality_bg_color;
            --fp-post-link-tv-quality-bg-color-hover: $post_links_tv_quality_color_hover;
            --fp-post-link-tv-quality-text-color-hover: $post_links_tv_quality_color_hover_color;
            --fp-post-link-tv-quality-color: $post_links_tv_quality_color;
            --fp-post-link-tv-ep-pack-single-item-bg-color: $post_links_tv_ep_pack_item_bg_color;
            --fp-post-link-tv-ep-pack-single-item-bg-color-hover: $post_links_tv_ep_pack_item_bg_color_hover;
            --fp-post-link-tv-ep-pack-single-item-color: $post_links_tv_episode_color;
            --fp-post-link-tv-single-meta-color: $post_links_tv_episode_meta_color;
            --fp-post-link-tv-pack-season: $post_links_tv_episode_packs_single_season_color;

        }
        ";
        wp_add_inline_style('fp-post-links', $custom_css);
        $cache_key = 'fp_cache_postLinks_' . self::$current_post_id;
        if (self::$isCacheEnabled) {

            $cache_content = fp_get_cache($cache_key);
            // $cache_content = get_transient($cache_key);
            if ($cache_content) {
                self::enqueue_post_links_dependencies();
                // error_log('Cache HIT for post links');
                $end = microtime(true);
                // error_log('Time taken for LINKS: ' . ($end - $start)) . 'PostID: ' . self::$current_post_id;
                return $cache_content;
            }
        }
        require_once FP_MOVIES_DIR . 'helper/fp_get_full_lang_name.php';
        remove_filter('the_content', 'wpautop');
        ob_start();
        include FP_MOVIES_DIR . 'templates/links/fp_postLinks.php';
        $content = ob_get_clean();
        $content = trim($content);
        $content = preg_replace('/\s+/', ' ', $content);  // This collapses all whitespace into single spaces
        $content = str_replace("> <", "><", $content);
        add_filter('the_content', 'wpautop');
        if (self::$isCacheEnabled) {
            // error_log('Cache MISS for post links');
            fp_store_cache($cache_key, $content);
            // set_transient($cache_key, $content, 60); // Cache for 1 hour : 60 seconds * 60 minutes
        }
        $end = microtime(true);
        // error_log('Time taken for LINKS: ' . ($end - $start)) . 'PostID: ' . self::$current_post_id;
        return $content;
    }

    private static function enqueue_post_links_dependencies()
    {
        global $fp_min_m;
        $_postType = self::$current_post_type;
        if ($_postType == 'movie') {
            wp_enqueue_style('fp-post-movie-links-css', FP_MOVIES_URL . '/templates/css/fp_movieLinks' . $fp_min_m . '.css', array(), FP_MOVIES_FILES, 'all');
        } else if ($_postType == 'tv') {
            wp_enqueue_script('fp-post-link-js', FP_MOVIES_URL . '/templates/js/fp_postLinks' . $fp_min_m . '.js', array(), FP_MOVIES_FILES, true);
            wp_enqueue_script('fp-post-tv-links-js', FP_MOVIES_URL . '/templates/js/fp_seriesLinks' . $fp_min_m . '.js', array(), FP_MOVIES_FILES, true);
            wp_enqueue_style('fp-post-tv-links-css', FP_MOVIES_URL . '/templates/css/fp_seriesLinks' . $fp_min_m . '.css', array(), FP_MOVIES_FILES, 'all');
        }
    }

    private static function enqueue_player_dependencies()
    {
        global $fp_min_m;
        $_postType = self::$current_post_type;

        wp_enqueue_style('fp-movie-player-css', FP_MOVIES_URL . '/templates/css/fp_playerMovie' . $fp_min_m . '.css', array(), FP_MOVIES_FILES, 'all');
        wp_enqueue_script('fp-movie-player-js', FP_MOVIES_URL . '/templates/js/fp_playerMovie' . $fp_min_m . '.js', array('jquery'), FP_MOVIES_FILES, true);
        wp_localize_script('fp-movie-player-js', 'fp_pAjax', array(
            'ajax_url' => FP_MOVIES_AJAX,
            'nonce' => wp_create_nonce('fp_player_nonce')
        ));
        if ($_postType == 'tv') {
            wp_enqueue_style('fp-tv-player-css', FP_MOVIES_URL . '/templates/css/fp_playerTV' . $fp_min_m . '.css', array(), FP_MOVIES_FILES, 'all');
        }
    }

    public static function shortcode_post_player($atts)
    {
        // track time and log it
        $start = microtime(true);
        self::enqueue_views_files();
        self::enqueue_global_files();
        // wp_enqueue_style('fp-post-player', FP_MOVIES_URL . '/templates/css/fp_postPlayer.css', array(), FP_MOVIES_FILES, 'all');

        $cache_key = 'fp_cache_postPlayer_' . self::$current_post_id;

        if (self::$isCacheEnabled) {
            $cache_content = fp_get_cache($cache_key);
            if ($cache_content) {
                self::enqueue_player_dependencies();
                // error_log('Cache HIT for PLAYER');
                $end = microtime(true);
                // error_log('Time taken for PLAYER: ' . ($end - $start)) . 'PostID: ' . self::$current_post_id;
                return $cache_content;
            }
        }

        ob_start();
        include FP_MOVIES_DIR . 'templates/fp_player.php';
        $content = ob_get_clean();
        $content = trim($content);
        $content = preg_replace('/\s+/', ' ', $content);
        $content = str_replace("> <", "><", $content);

        if (self::$isCacheEnabled) {
            // error_log('Cache MISS for PLAYER');
            fp_store_cache($cache_key, $content);
            // set_transient($cache_key, $content, 60);
        }
        $end = microtime(true);
        // error_log('Time taken for PLAYER: ' . ($end - $start)) . 'PostID: ' . self::$current_post_id;
        return $content;
    }

    public static function fp_homepage_view($atts)
    {
        // $start = microtime(true);
        self::enqueue_global_files();
        global $fp_min_m;
        wp_enqueue_style('fp-homepage-view', FP_MOVIES_URL . '/templates/css/fp_homepage' . $fp_min_m . '.css', array(), FP_MOVIES_FILES, 'all');
        include FP_MOVIES_DIR . 'helper/fp_get_img_gradient.php';
        include FP_MOVIES_DIR . 'templates/home/fp_homepage.php';

        $content = fp_display_home($atts);
        $content = trim($content);
        $content = preg_replace('/\s+/', ' ', $content);
        $content = str_replace("> <", "><", $content);

        $end = microtime(true);
        // error_log('Time taken for HOMEPAGE: ' . ($end - $start)) . 'PostID: ' . self::$current_post_id;
        return $content;
    }
}



add_action('init', array('FP_Movies_Shortcodes', 'init'));

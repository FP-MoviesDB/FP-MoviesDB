<?php
/*
 * Plugin Name:         FP MoviesDB
 * Plugin URI:          https://github.com/FP-MoviesDB/FP-MoviesDB
 * Description:         A advanced WordPress plugin to publish movies and TV shows. Join Telegram Channel for all Future Updates and support: <a href="https://t.me/FP_MoviesDB">FP MoviesDB</a>
 * Author:              MSHTeam
 * Author URI:          https://t.me/FP_MoviesDB
 * Version:             1.1.0
 * Text Domain:         fp_movies
 * Requires PHP:        8.2
 * Requires at least:   6.5
 * License:             GPL2
 */


if (!defined('ABSPATH')) die();

if (!class_exists('MoviePostGenerator')) {

    class MoviePostGenerator
    {
        function __construct()
        {
            register_activation_hook(__FILE__, array($this, 'fp_moviesdb_activate'));
            $this->constants();
            require_once FP_MOVIES_DIR . 'helper/fp_before_initAjax.php';
            add_action('wp_ajax_fp_dismiss_admin_notice', 'fp_dismiss_admin_notice_handler');
            add_action('wp_ajax_fp_dismiss_d_notice', 'fp_dismiss_d_notice_handler');
            add_action('admin_init', [$this, 'fp_req']);

            if (FP_MOVIES_LOGS) {
                require_once FP_MOVIES_DIR . 'helper/fp_global_error.php';
                add_action('wp_enqueue_scripts', [$this, 'fp_enqueue_global_scripts']);
                add_action('admin_enqueue_scripts', [$this, 'fp_enqueue_global_scripts']);
            }

            $this->includes();

            // ADMIN HOOKS
            if (is_admin()) {
                $this->setup_admin_hooks();
            }
            add_action('init', 'fp_register_taxonomies', 10);
            add_action('admin_bar_menu', 'fp_movies_admin_bar', 100);
        }

        function setup_admin_hooks()
        {
            add_action('admin_init', array($this, 'setup_admin_init_hooks'));
            add_action('admin_enqueue_scripts', 'load_assets');
            add_action('admin_menu', 'add_admin_menus');
            add_action('wp_ajax_check_tmdb_id_exists', 'check_tmdb_exists_currentPosts');
            if (get_option('mtg_fp_api_key') && get_option('mtg_tmdb_api_key')) {
                add_action('save_post', 'save_custom_fields_meta_box_data');
                add_action('save_post', 'fp_save_video_meta_box_data');
                add_action('save_post', 'fp_save_links_meta_box_data');
            }
            add_action('save_post', 'clear_post_specific_transient', 10, 3);
            add_action('add_meta_boxes', 'custom_meta_boxes_init');
            add_action('add_meta_boxes', 'video_meta_box_init');
            add_action('add_meta_boxes', 'links_meta_box_init');
            add_action('update_option_mtg_encryption_settings', [$this, 'flush_rewrite_rules_on_save'], 10, 2);
            add_action('admin_notices', function () {
                if ($feedback = get_transient('fp_user_feedback')) {
                    $class = 'notice notice-' . ($feedback['type'] === 'error' ? 'error' : 'success');
                    $message = $feedback['message'];
                    echo "<div class=\"$class\"><p>$message</p></div>";
                    delete_transient('fp_user_feedback');
                }
            });
        }

        function setup_admin_init_hooks()
        {
            $all_selector_options = get_option('mtg_checked_options');
            $all_selector_options = wp_parse_args($all_selector_options, [
                'genre' => 'on',
                'audio' => 'on',
                'year' => 'on',
                'network' => 'on',
                'quality' => 'on',
                'resolution' => 'on',
                'activeClassicEditor' => 'on',
                'displayAllSizes' => 'off',
            ]);
            register_mts_generator_settings($all_selector_options);
            fp_enable_classic_editor($all_selector_options['activeClassicEditor']);
            fp_hook_new_media_columns($all_selector_options['displayAllSizes']);
            mtg_register_color_settings();
            fp_handle_clear_cache();
        }

        function is_ssl()
        {
            if (isset($_SERVER['HTTP_CF_VISITOR'])) {
                $cf_visitor = json_decode($_SERVER['HTTP_CF_VISITOR'], true);
                if (isset($cf_visitor['scheme']) && $cf_visitor['scheme'] === 'https') {
                    return true;
                }
            }
            if (isset($_SERVER['HTTPS'])) {
                if ('on' == strtolower($_SERVER['HTTPS']) || '1' == $_SERVER['HTTPS']) {
                    return true;
                }
            } elseif (isset($_SERVER['SERVER_PORT']) && '443' == $_SERVER['SERVER_PORT']) {
                return true;
            }
            return false;
        }


        function constants()
        {
            $ajax_url = admin_url('admin-ajax.php', 'https');
            if (!defined('FP_MOVIES_MODE')) define('FP_MOVIES_MODE', 'prod');
            if (!defined('FP_MOVIES_VERSION')) define('FP_MOVIES_VERSION', '1.1.0');
            if (!defined('FP_MOVIES_REQUIRE')) define('FP_MOVIES_REQUIRE', '6.0');
            if (!defined('FP_MOVIES_FILES')) define('FP_MOVIES_FILES', '1.1.0');
            if (!defined('FP_MOVIES_AUTHOR'))  define('FP_MOVIES_AUTHOR',  'WP_DEBUG');
            if (!defined('FP_MOVIES_NAME'))    define('FP_MOVIES_NAME',    'FP Movies');
            if (!defined('FP_MOVIES_AJAX'))    define('FP_MOVIES_AJAX',    $ajax_url);
            if (!defined('FP_MOVIES_URL'))     define('FP_MOVIES_URL',     plugin_dir_url(__FILE__));
            if (!defined('FP_MOVIES_DIR'))     define('FP_MOVIES_DIR',     plugin_dir_path(__FILE__));
            if (!defined('FP_MOVIES_FILE'))    define('FP_MOVIES_FILE',    plugin_basename(__FILE__));
            if (!defined('FP_MOVIES_SLUG'))    define('FP_MOVIES_SLUG',    basename(dirname(__FILE__)));
            if (!defined('FP_CACHE_DIR'))      define('FP_CACHE_DIR',      WP_CONTENT_DIR . '/cache/fp_movies');
            if (!defined('FP_MOVIES_FILEPRESS_URL'))    define('FP_MOVIES_FILEPRESS_URL',    esc_url('https://filebee.xyz'));
            if (!defined('FP_MOVIES_GLOBAL_STREAM_URL'))    define('FP_MOVIES_GLOBAL_STREAM_URL',    esc_url('https://v1.sdsp.xyz'));
            if (!defined('FP_MOVIES_WEBSITE_HOME_URL')) define('FP_MOVIES_WEBSITE_HOME_URL', get_home_url());
            if (!defined('FP_MOVIES_IMDB_BASE_URL')) define('FP_MOVIES_IMDB_BASE_URL', 'https://www.imdb.com/title');
            if (!defined('FP_MOVIES_TMDB_BASE_URL')) define('FP_MOVIES_TMDB_BASE_URL', 'https://www.themoviedb.org');
            if (!defined('FP_MOVIES_TMDB_API_BASE_URL')) define('FP_MOVIES_TMDB_API_BASE_URL', 'https://api.themoviedb.org/3');
            if (!defined('FP_MOVIES_TMDB_IMG_BASE_URL')) define('FP_MOVIES_TMDB_IMG_BASE_URL', 'https://image.tmdb.org/t/p/');
            if (!defined('FP_MOVIES_FP_BASE_URL')) define('FP_MOVIES_FP_BASE_URL', 'https://filebee.xyz/api/v1/files');
            if (get_option('mtg_tmdb_api_key')) define('FP_MOVIES_TMDB_API_KEY', get_option('mtg_tmdb_api_key'));
            if (get_option('mtg_fp_api_key')) define('FP_MOVIES_FP_API_KEY', get_option('mtg_fp_api_key'));
            if (!defined('FP_MOVIES_ENCRYPTION_KEY')) define('FP_MOVIES_ENCRYPTION_KEY', '1CDF5D16859FFACA4C265E8E26FB1');
            if (!defined('FP_MOVIES_ENCRYPTION_METHOD')) define('FP_MOVIES_ENCRYPTION_METHOD', 'AES-256-CBC');

            $logs = get_option('mtg_logs_status', 0);
            if ($logs == 1 || $logs == 'on' || $logs == 'true' || $logs == '1') {
                define('FP_MOVIES_LOGS', true);
            } else {
                define('FP_MOVIES_LOGS', false);
            }

            $GLOBALS['fp_min_m'] = (defined('FP_MOVIES_MODE') && FP_MOVIES_MODE === 'dev') ? '' : '.min';
        }

        function includes()
        {
            require_once FP_MOVIES_DIR . 'helper/fp_global_cache.php';
            require_once FP_MOVIES_DIR . 'inc/plugin-menu-display.php';

            if (is_admin()) {
                require_once FP_MOVIES_DIR . 'helper/fp_plugin_updates.php';
                require_once FP_MOVIES_DIR . 'inc/resources-enqueue.php';
                require_once FP_MOVIES_DIR . 'inc/settings-display.php';
                require_once FP_MOVIES_DIR . 'inc/template-settings.php';
                require_once FP_MOVIES_DIR . 'inc/homepage-template-settings.php';
                require_once FP_MOVIES_DIR . 'inc/bulk-import.php';
                require_once FP_MOVIES_DIR . 'inc/predefined-shortcodes.php';
                require_once FP_MOVIES_DIR . 'inc/register-options.php';
                require_once FP_MOVIES_DIR . 'inc/homepage-display.php';
                require_once FP_MOVIES_DIR . 'extra/classic-editor.php';
                require_once FP_MOVIES_DIR . 'extra/media-add-size.php';
            }
            require_once FP_MOVIES_DIR . 'inc/register_taxonomies.php';
            require_once FP_MOVIES_DIR . 'templates/links/link-handler.php';

            if (get_option('mtg_fp_api_key') && get_option('mtg_tmdb_api_key')) {
                require_once FP_MOVIES_DIR . 'helper/fp_helpers.php';
                if (!defined('FP_MOVIES_HELPER_LOADED')) {
                    define('FP_MOVIES_HELPER_LOADED', true);
                    require_once FP_MOVIES_DIR . 'inc/helper.php';
                }
                if (is_admin()) {
                    require_once FP_MOVIES_DIR . 'helper/fp_get_all_ids.php';
                    require_once FP_MOVIES_DIR . 'inc/app_search.php';
                    require_once FP_MOVIES_DIR . 'inc/check_tmdb_exist.php';
                    require_once FP_MOVIES_DIR . 'inc/create_post.php';
                    require_once FP_MOVIES_DIR . 'inc/update_post.php';
                    require_once FP_MOVIES_DIR . 'inc/post-buttons.php';
                }
            }

            if (is_admin()) {
                require_once FP_MOVIES_DIR . 'inc/metabox.php';
                require_once FP_MOVIES_DIR . 'inc/meta/player/fp_metabox_video.php';
                require_once FP_MOVIES_DIR . 'inc/meta/links/fp_metabox_links.php';
                require_once FP_MOVIES_DIR . 'helper/fp_clear_transient.php';
                require_once FP_MOVIES_DIR . 'helper/fp_settings_validator.php';
            }

            require_once FP_MOVIES_DIR . 'inc/templates-enqueue.php';
            require_once FP_MOVIES_DIR . 'helper/fp_player_ajax.php';
        }

        function flush_rewrite_rules_on_save($old_value, $new_value)
        {
            flush_rewrite_rules();
        }

        function fp_moviesdb_activate()
        {
            $default_checkbox_settings = array(
                'genre' => 'on',
                'audio' => 'on',
                'year' => 'on',
                'network' => 'on',
                'quality' => 'on',
                'resolution' => 'on',
                'activeClassicEditor' => 'on',
                'displayAllSizes' => 'off',
            );

            $default_post_settings = [
                'title' => '',
                'slug' => '',
                'category' => '',
                'tags' => '',
                'image_name' => '',
                'default_network' => '',
                'default_quality' => '',
                'status' => 'publish',
                'language' => 'en-US',
                'featured_image_size' => 'w342',
            ];
            $current_checkbox_settings = get_option('mtg_checked_options', false);
            if ($current_checkbox_settings === false) update_option('mtg_checked_options', $default_checkbox_settings);

            $current_post_settings = get_option('mtg_postDefault_settings', false);
            if ($current_post_settings === false) update_option('mtg_postDefault_settings', $default_post_settings);

            set_transient('fp_moviesdb_d_notice', 'waiting', WEEK_IN_SECONDS);
        }

        function fp_req()
        {
            if (version_compare(get_bloginfo('version'), '6.5', '<') && get_user_meta(get_current_user_id(), 'fp_wordpress_version_notice', true) !== 'dismissed') {
                add_action('admin_notices', function () {
                    echo '<div class="notice error is-dismissible"><p>FP MoviesDB recommends WordPress version 6.5 or higher.</p></div>';
                    $this->fp_enqueue_dismiss_script('fp_wordpress_version_notice');
                });
                return;
            }

            if (version_compare(PHP_VERSION, '8.2', '<') && get_user_meta(get_current_user_id(), 'fp_php_version_notice', true) !== 'dismissed') {
                add_action('admin_notices', function () {
                    echo '<div class="notice error is-dismissible"><p>FP MoviesDB requires PHP version 8.2.</p></div>';
                });
                $this->fp_enqueue_dismiss_script('fp_php_version_notice');
                return;
            }

            $memory_limit = ini_get('memory_limit');
            if ($this->convert_memory_size($memory_limit) < 256 * 1024 * 1024 && get_user_meta(get_current_user_id(), 'fp_memory_limit_notice', true) !== 'dismissed') {
                add_action('admin_notices', function () {
                    echo '<div class="notice error is-dismissible"><p>FP MoviesDB recommends 256MB or higher.</p></div>';
                    $this->fp_enqueue_dismiss_script('fp_memory_limit_notice');
                });
                return;
            }

            $execution_time = ini_get('max_execution_time');
            if ($execution_time != 0 && $execution_time < 120 && get_user_meta(get_current_user_id(), 'fp_execution_time_notice', true) !== 'dismissed') {
                add_action('admin_notices', function () use ($execution_time) {
                    echo '<div class="notice error is-dismissible"><p>Plugin recommends execution time of 120 seconds or higher. Current value: ' . $execution_time . ' seconds.</p></div>';
                });
                $this->fp_enqueue_dismiss_script('fp_execution_time_notice');
                return;
            }

            require_once FP_MOVIES_DIR . 'helper/fp_show_d_notice.php';
            add_action('admin_notices', 'fp_display_d_notice');
        }


        function fp_enqueue_dismiss_script($notice_key)
        {
            wp_enqueue_script('jquery');
            $ajax_nonce = wp_create_nonce('fp_dismiss_notice_nonce');
            ?>
            <script type="text/javascript">
                jQuery(document).ready(function($) {
                    $(document).on('click', '.notice.is-dismissible', function() {
                        $.post(ajaxurl, {
                            action: 'fp_dismiss_admin_notice',
                            notice_key: '<?php echo $notice_key; ?>',
                            _ajax_nonce: '<?php echo $ajax_nonce; ?>'

                        });
                    });
                });
            </script>


            <?php
        }

        function convert_memory_size($size)
        {
            $unit = strtolower(substr($size, -1));
            $size = (int) $size;
            switch ($unit) {
                case 'g':
                    $size *= 1024;
                case 'm':
                    $size *= 1024;
                case 'k':
                    $size *= 1024;
            }
            return $size;
        }

        function fp_enqueue_global_scripts()
        {
            global $fp_min_m;
            wp_enqueue_script('fp-movies-global', FP_MOVIES_URL . 'js/fp_global_log' . $fp_min_m . '.js', array('jquery'), FP_MOVIES_VERSION, true);
            wp_localize_script('fp-movies-global', 'fpAjax', array('ajaxurl' => FP_MOVIES_AJAX));
        }
    }
    new MoviePostGenerator;
}

<?php
if (!defined('ABSPATH')) exit;

// ┌───────────────────────┐
// │  -> LOAD ASSETS <-  │
// └───────────────────────┘
function load_assets($hook)
{
    global $fp_min_m;
    if ( ! wp_script_is( 'jquery-ui-sortable' ) ) {
        wp_enqueue_script( 'jquery-ui-sortable' );
      }
    
    wp_enqueue_style(
        'mts-admin-style',
        FP_MOVIES_URL  . 'css/mts-admin' . $fp_min_m . '.css',
        array(),
        FP_MOVIES_FILES,
        'all'
    );

    

    if ('toplevel_page_mts_generator' === $hook) {
        wp_enqueue_script(
            'mts_generator',
            FP_MOVIES_URL  . 'js/fp_main' . $fp_min_m . '.js',
            array('jquery'),
            FP_MOVIES_FILES,
            true
        );
    }
    // error_log("HOOK: $hook");

    if ('fp-movies_page_mts_gen_settings' === $hook) {
        // error_log('mts_gen_settings');
        wp_enqueue_script(
            'mts_generator',
            FP_MOVIES_URL  . 'js/mts_gen_settings' . $fp_min_m . '.js',
            array('jquery'),
            FP_MOVIES_FILES,
            true
        );
        // error_log('mts_gen_settings');
    }

    // error_log("HOOK: $hook");

    if ('fp-movies_page_fp_template_single_settings' === $hook) {
        wp_enqueue_style('wp-color-picker');
        wp_enqueue_script('wp-color-picker');

        wp_enqueue_script(
            'mts_template_settings',
            FP_MOVIES_URL  . 'js/fp_colorpicker' . $fp_min_m . '.js',
            array('jquery', 'wp-color-picker'),
            FP_MOVIES_FILES,
            true
        );
    }

    $api_key_array = array(
        'mtg_tmdb_api_key' => get_option('mtg_tmdb_api_key'),
        'mtg_fp_api_key' => get_option('mtg_fp_api_key')
    );
    wp_localize_script('mts_generator', 'apiKeys', $api_key_array);

    wp_localize_script('mts_generator', 'movieTvVars', array(
        'ajax_url' => FP_MOVIES_AJAX,
        'nonce' => wp_create_nonce('movie_tv_nonce'),
        'wp_edit_post_link' => FP_MOVIES_WEBSITE_HOME_URL,
        'plugin_url' => FP_MOVIES_URL,
        'apiKeys' => $api_key_array
    ));
}

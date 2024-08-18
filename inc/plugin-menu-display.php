<?php

if (!defined('ABSPATH')) exit;

function add_admin_menus()
{
    add_menu_page('FP Movie/TV Generator', 'FP Movies', 'manage_options', 'mts_generator', 'plugin_options_page', 'dashicons-video-alt3');
    add_submenu_page('mts_generator', 'Movies/TV', 'Movies/TV', 'manage_options', 'mts_generator');
    add_submenu_page('mts_generator', 'Main Settings', 'Main Settings', 'manage_options', 'mts_gen_settings', 'fp_settings_page');
    add_submenu_page('mts_generator', 'SinglePost Settings', 'SinglePage Settings', 'manage_options', 'fp_template_single_settings', 'theme_template_settings');
    add_submenu_page('mts_generator', 'HomePage Settings', 'HomePage Settings', 'manage_options', 'fp_template_homepage_settings', 'homepage_template_settings');
    add_submenu_page('mts_generator', 'Bulk Import', 'Bulk Import Tool', 'manage_options', 'fp_template_bulk_import', 'mts_bulk_import');
    add_submenu_page('mts_generator', 'Shortcode Help', 'Shortcode Help', 'manage_options', 'fp_template_pre_defined_shortcodes', 'mts_predefined_shortcodes');
}

// add_action('admin_menu', 'add_admin_menus');

function fp_movies_admin_bar($wp_admin_bar)
{
    global $post;

    // Main menu item
    if (!$wp_admin_bar->get_node('fp_movies')) {
        $args = array(
            'id'    => 'fp_movies', // Node ID
            'title' => 'FP Movies', // Node title
            'href'  => admin_url('admin.php?page=mts_generator'), // Link to the admin page
            'meta'  => array(
                'class' => 'fp-movies-admin-bar' // CSS class for the node
            )
        );
        $wp_admin_bar->add_node($args); // Add the node to the admin bar
    }


    // Clear cache submenu item
    $args = array(
        'id'    => 'fp_movies_clear_cache',
        'parent' => 'fp_movies',
        'title' => 'Clear All Plugin Cache',
        'href'  => wp_nonce_url(admin_url('admin.php?action=fp_clear_cache&redirect_to=' . urlencode($_SERVER['REQUEST_URI'])), 'fp_clear_cache_action'),
        'meta'  => array(
            'class' => 'fp-movies-clear-cache'
        )
    );
    $wp_admin_bar->add_node($args);

    // Clear page cache submenu item || Only show on single post page exclude homepage
    if (get_post_type() == 'post' && !is_home()) {
        $args = array(
            'id'    => 'fp_clear_page_cache',
            'parent' => 'fp_movies',
            'title' => 'Clear Page Cache',
            'href'  => wp_nonce_url(admin_url('admin.php?action=fp_clear_page_cache&post_id=' . $post->ID . '&redirect_to=' . urlencode($_SERVER['REQUEST_URI'])), 'fp_clear_page_cache_action'),
            'meta'  => array(
                'class' => 'fp-clear-page-cache'
            )
        );
        $wp_admin_bar->add_node($args);
    }

    


}

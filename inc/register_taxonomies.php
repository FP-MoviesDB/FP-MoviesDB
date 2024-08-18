<?php

if (!defined('ABSPATH')) exit;

function fp_register_taxonomies()
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
        'displayAllSizes' => 'on',
    ]);

    if (isset($all_selector_options['audio']) && $all_selector_options['audio'] == 'on') {
        register_taxonomy('mtg_audio', 'post', array(
            'label' => 'Audio',
            'rewrite' => array('slug' => 'audio'),
            'public' => true,
            'hierarchical' => false,
            'show_in_nav_menus' => true,
            'show_admin_column' => true,
            'show_ui' => true,
        ));
    }

    // Register fp_year taxonomy
    if (isset($all_selector_options['year']) && $all_selector_options['year'] == 'on') {
        register_taxonomy('mtg_year', 'post', array(
            'label' => 'Year',
            'rewrite' => array('slug' => 'year'),
            'public' => true,
            'hierarchical' => false,
            'show_in_nav_menus' => true,
            'show_admin_column' => true,
            'show_ui' => true,
        ));
    }

    // Register fp_genre taxonomy
    if (isset($all_selector_options['genre']) && $all_selector_options['genre'] == 'on') {
        register_taxonomy('mtg_genre', 'post', array(
            'label' => 'Genre',
            'rewrite' => array('slug' => 'genre'),
            'public' => true,
            'hierarchical' => false,
            'show_in_nav_menus' => true,
            'show_admin_column' => true,
            'show_ui' => true,
        ));
    }

    // Register fp_resolution taxonomy
    if (isset($all_selector_options['resolution']) && $all_selector_options['resolution'] == 'on') {
        register_taxonomy('mtg_resolution', 'post', array(
            'label' => 'Resolution',
            'rewrite' => array('slug' => 'resolution'),
            'public' => true,
            'hierarchical' => false,
            'show_in_nav_menus' => true,
            'show_admin_column' => true,
            'show_ui' => true,
        ));
    }

    // Register fp_quality taxonomy
    if (isset($all_selector_options['quality']) && $all_selector_options['quality'] == 'on') {
        register_taxonomy('mtg_quality', 'post', array(
            'label' => 'Quality',
            'rewrite' => array('slug' => 'quality'),
            'public' => true,
            'hierarchical' => false,
            'show_in_nav_menus' => true,
            'show_admin_column' => true,
            'show_ui' => true,
        ));
    }

    // Register fp_network taxonomy
    if (isset($all_selector_options['network']) && $all_selector_options['network'] == 'on') {
        register_taxonomy('mtg_network', 'post', array(
            'label' => 'Network',
            'rewrite' => array('slug' => 'network'),
            'public' => true,
            'hierarchical' => false,
            'show_in_nav_menus' => true,
            'show_admin_column' => true,
            'show_ui' => true,
        ));
    }
}

<?php

if (!defined('ABSPATH')) exit;
function register_mts_generator_settings()
{
    // Register a new setting for "movie_tv_publisher" page = 1
    register_setting('mts_generator_settings', 'mtg_fp_key_type');
    register_setting('mts_generator_settings', 'mtg_fp_api_key', 'validate_fp_api_key');
    register_setting('mts_generator_settings', 'mtg_global_access');
    register_setting('mts_generator_settings', 'mtg_tmdb_api_key', 'validate_tmdb_api_key');

    //mtg_logs_status
    register_setting('mts_generator_settings', 'mtg_logs_status');



    register_setting('mts_generator_settings', 'mtg_postDefault_settings');
    register_setting('mts_generator_settings', 'mtg_checked_options');
    register_setting('mts_generator_settings', 'mtg_encryption_settings');


    register_setting('mts_generator_template_settings', 'mtg_template_settings', 'mtg_sanitize_template_settings');
    register_setting('mts_generator_homepage_template_settings', 'mtg_homepage_template_settings', 'mtg_sanitize_template_settings');
}



function mtg_register_color_settings()
{
    register_setting('mts_generator_template_settings', 'mtg_color_settings', 'mtg_sanitize_template_settings');
}
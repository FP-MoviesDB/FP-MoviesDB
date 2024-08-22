<?php

if (!defined('ABSPATH')) exit;

function fp_moviesdb_check_for_updates($transient)
{
    if (empty($transient->checked)) {
        return $transient;
    }
    $plugin_file = FP_MOVIES_FILE;

    if (!isset($transient->checked[$plugin_file])) {
        return $transient;
    }

    // Sending Homepage as Referer
    $siteURL = get_site_url();

    $url = 'https://update.fpmoviesdb.xyz/?referer=' . $siteURL;
    $response = wp_remote_get($url);
    if (is_wp_error($response)) {
        return $transient;
    }

    $data = json_decode(wp_remote_retrieve_body($response));
    if (!$data) return $transient;

    if ($data && version_compare($transient->checked[$plugin_file], $data->version, '<')) {
        $obj = new stdClass();
        $obj->slug = 'fp-moviesdb';
        $obj->plugin = $plugin_file;
        $obj->new_version = $data->version;
        $obj->url = $data->details_url;
        $obj->package = $data->download_url;
        $obj->requires = $data->requires;
        $obj->tested = $data->tested;
        $obj->requires_php = $data->requires_php;
        $transient->response[$plugin_file] = $obj;
    }

    return $transient;
}

function fp_moviesdb_plugin_info($false, $action, $args)
{
    $siteURL = get_site_url();

    if ($action !== 'plugin_information' || $args->slug !== 'fp-moviesdb') {
        return false;
    }

    $url = 'https://update.fpmoviesdb.xyz/?referer=' . $siteURL;
    $response = wp_remote_get($url);
    if (is_wp_error($response)) {
        return false;
    }

    $data = json_decode(wp_remote_retrieve_body($response), true);
    if (!$data) return false;

    $changelog_url = 'https://update.fpmoviesdb.xyz/changelog.html';
    $changelog_response = wp_remote_get($changelog_url);
    $changelog_content = wp_remote_retrieve_body($changelog_response);

    $plugin_info = new stdClass();
    $plugin_info->name = 'FP MoviesDB';
    $plugin_info->slug = $args->slug;
    $plugin_info->version = $data['version'];
    $plugin_info->author = '<a href="https://t.me/FP_MoviesDB_chat">FP MoviesDB</a>';
    $plugin_info->homepage = 'https://fpmoviesdb.xyz';
    $plugin_info->tested = $data['tested'];
    $plugin_info->requires_php = $data['requires_php'];
    $plugin_info->downloaded = 1234;
    $plugin_info->last_updated = $data['last_updated'];
    $plugin_info->sections = array(
        'description' => 'The best plugin for managing movies and series.',
        'changelog' => is_wp_error($changelog_response) ? 'Changelog is currently unavailable.' : $changelog_content
    );
    $plugin_info->download_link = $data['download_url'];
    return $plugin_info;
}

add_filter('plugins_api', 'fp_moviesdb_plugin_info', 20, 3);
add_filter('pre_set_site_transient_update_plugins', 'fp_moviesdb_check_for_updates');

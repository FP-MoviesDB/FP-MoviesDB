<?php

if (!defined('ABSPATH')) die();

function getBackdrop($meta_data, $fallback_image, $userQuality) {
    $backdrop = $meta_data['fp_backdrop'];
    
    if (strpos($backdrop, 'http') === false  && !empty($backdrop)) {
        if (empty($userQuality)) $userQuality = 'original';
        $backdrop = FP_MOVIES_TMDB_IMG_BASE_URL . $userQuality . $backdrop;
    }

    if (empty($backdrop)) {
        $default_img = FP_MOVIES_URL . 'img/image-not-found.webp';
        // error_log('Default image: ' . $default_img);
        // $fallback_image = get_option_with_fallback('mtg_template_player_fallback_image_url', $default_img);
        if (empty($fallback_image)) $fallback_image = $default_img;
        // error_log('Fallback image: ' . $fallback_image);
        $backdrop = $fallback_image;
    }

    return $backdrop;

    

}
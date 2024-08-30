<?php

if (!defined('ABSPATH')) exit;

if (!function_exists('term_to_link')) {
    function term_to_link($genre_terms_link, $output_base, $limit = 5) {
        if (!is_array($genre_terms_link)) {
            return '';
        }
        $i = 1;
        foreach ($genre_terms_link as $term) {
            if ($i > $limit) {
                $output_base .= '...';
                break;
            }
            $output_base .= '<a href="' . esc_url(get_term_link($term)) . '">' . esc_html($term->name) . '</a>, ';
            $i++;
        }
        $output_base = rtrim($output_base, ', ');
        return $output_base;
    }
}

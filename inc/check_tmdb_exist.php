<?php

if (!defined('ABSPATH')) exit;

function check_tmdb_exists_currentPosts()
{
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'movie_tv_nonce')) {
        wp_send_json_error(array('message' => 'Nonce verification failed'), 400);
        return;
    }

    // TMDB_IDS //
    if (empty($_POST['tmdb_ids'])) {
        wp_send_json_error(array('message' => 'TMDB ID is missing'), 400);
        return;
    }
    $tmdb_ids = isset($_POST['tmdb_ids']) ? json_decode(stripslashes($_POST['tmdb_ids'])) : [];
    $per_page = 100;
    $paged = 1;
    $content_post_type = $_POST['mtg_post_type'];
    $existing_ids = [];

    $query_args  = [
        'post_type' => 'post',
        'posts_per_page' => $per_page,
        'paged' => $paged,
        'fields' => 'ids',
        'meta_query' => [
            'relation' => 'AND',
            [
                'key' => 'mtg_tmdb_id',
                'value' => $tmdb_ids,
                'compare' => 'IN'
            ],
            [
                'key' => 'mtg_post_type',
                'value' => $content_post_type,
                'compare' => '='
            ]
        ]
    ];

    do {
        $query_args['paged'] = $paged;
        $query = new WP_Query($query_args );
        if ($query->have_posts()) {
            foreach ($query->posts as $post_id) {
                $existing_id = get_post_meta($post_id, 'mtg_tmdb_id', true);
                if (in_array($existing_id, $tmdb_ids)) {
                    $existing_ids[$existing_id] = [
                        'exists' => true,
                        'post_id' => $post_id
                    ];
                }
            }
        }

        $paged++;
    } while ($paged <= $query->max_num_pages);

    $existence_map = array_fill_keys($tmdb_ids, ['post_id' => null, 'exists' => false]);
    foreach ($existing_ids as $id => $data) {
        $existence_map[$id] = $data;
    }

    wp_send_json_success(array('existence_map' => $existence_map));
}

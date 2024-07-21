<?php

if (!defined('ABSPATH')) exit;

if (!function_exists('fp_load_posts_view')) {
    function fp_load_posts_view()
    {
        // error_log('fp_load_posts');
        check_ajax_referer('fp_homepage_nonce', 'nonce');

        $home_data = $_POST['home_data'];

        $type = sanitize_text_field($home_data['type']);
        $content_type = sanitize_text_field($home_data['content_type']);
        $taxonomy = sanitize_text_field($home_data['taxonomy']);
        $limit = intval($home_data['limit']);
        $page = intval($_POST['page']);
        $sort_type = sanitize_text_field($home_data['sort_type']);

        // error_log('Page: ' . $page);

        $args = array(
            'post_type' => 'post',
            'posts_per_page' => $limit,
            'paged' => $page,
            'post_status' => 'publish',
        );

        if ($type === 'meta' && !empty($content_type)) {
            $args['meta_query'] = array(
                'relation' => 'AND',
                array(
                    'key' => 'mtg_post_type',
                    'value' => $content_type,
                    'compare' => '='
                ),
            );
        } elseif ($type === 'taxonomy' && !empty($taxonomy) && !empty($content_type)) {
            $taxonomies = explode(',', $content_type);
            $args['tax_query'] = array(
                array(
                    'taxonomy' => $taxonomy,
                    'field' => 'slug',
                    'terms' => $taxonomies,
                ),
            );
        }

        switch ($sort_type) {
            case 'trending':
                $args['meta_query'][] = array(
                    'key' => 'mtg_post_views_count',
                    'compare' => 'EXISTS'
                );
                $args['orderby'] = array(
                    'meta_value_num' => 'DESC',
                    'date' => 'DESC',
                );
                $args['meta_key'] = 'mtg_post_views_count';
                break;
            case 'top-rated':
                // $args['meta_key'] = 'mtg_vote_average';
                // $args['orderby'] = 'meta_value_num';
                // $args['order'] = 'DESC';
                $args['meta_query'][] = array(
                    'key' => 'mtg_vote_average',
                    'compare' => 'EXISTS'
                );
                $args['orderby'] = array(
                    'meta_value_num' => 'DESC',
                    'date' => 'DESC',
                );
                $args['meta_key'] = 'mtg_vote_average';
                break;
            case 'random':
                $args['orderby'] = 'rand';
                break;
            case 'latest':
            default:
                $args['orderby'] = 'date';
                $args['order'] = 'DESC';
                break;
        }

        // error_log('args: ' . print_r($args, true));

        $query = new WP_Query($args);
        // error_log('query: ' . print_r($query, true));
        $image_size_mapping = [
            'small' => 'w342',
            'medium' => 'w500',
            'large' => 'w780',
            'original' => 'original',
        ];
        ob_start();
        if ($query->have_posts()) {
            // wp_list_pluck($query->posts, 'ID');
            update_post_caches($query->posts, ['post_meta']);
            while ($query->have_posts()) {
                $query->the_post();
                $post_id = get_the_ID();
                if ($home_data['post_title'] === 'tmdb') {
                    $title = get_post_meta($post_id, 'mtg_tmdb_title', true);
                } else {
                    $title = get_the_title($post_id);
                }
                if ($home_data['title_length'] !== 'auto') {
                    $length = intval($home_data['title_length']);
                    $title = wp_trim_words($title, $length, '...');
                }
                $permalink = get_permalink($post_id);
                $image_url = '';

                if ($home_data['image_source'] === 'tmdb') {
                    $poster_path = get_post_meta($post_id, 'mtg_poster_path', true);
                    if (!empty($poster_path)) {
                        if (str_starts_with($poster_path, '/')) {
                            $image_size = $image_size_mapping[$home_data['image_size']] ?? 'w500';
                            $image_url = 'https://image.tmdb.org/t/p/' . $image_size . $poster_path;
                        } else {
                            $image_url = $poster_path;
                        }
                    } else {
                        $image_url = get_the_post_thumbnail_url($post_id, $home_data['image_size']);
                    }
                } else {
                    $image_url = get_the_post_thumbnail_url($post_id, $home_data['image_size']);
                }

                if (empty($image_url) || !filter_var($image_url, FILTER_VALIDATE_URL)) {
                    $image_url = FP_MOVIES_URL . 'img/poster-not-found.png';
                }

                $quality = get_the_terms($post_id, 'mtg_quality');
                $quality = !empty($quality) ? $quality[0]->name : 'HD';

                $rating = get_post_meta($post_id, 'mtg_vote_average', true);
                $formatted_rating = sprintf("%.1f", $rating);
                $rating = str_replace('.0', '', $formatted_rating);
                $rating = get_post_meta($post_id, 'mtg_vote_average', true);

                $gradientCss = '';
                if ($home_data['title_background'] === 'gradient') {
                    $post_gradient = get_post_meta($post_id, 'mtg_gradient_color', true);
                    if (!empty($post_gradient)) {
                        $gradientCss = $post_gradient;
                    } else {
                        $gradientCss = fp_calculateImageGradient($image_url);
                        update_post_meta($post_id, 'mtg_gradient_color', $gradientCss);
                    }
                }
                $title_wrap = $home_data['title_wrap'] === 'wrap' ? 'fp-image-title-textWrap' : 'fp-image-title-textNoWrap';
                $ratings_html = '';
                if ($home_data['show_ratings'] === 'true' && !empty($rating)) {
                    $ratings_html = '
                                <img class="fp-image-rating-icon" src="' . FP_MOVIES_URL . 'img/star-dark.svg" alt="IMDb" width="15" height="auto">
                                <div class="fp-image-rating">' . esc_html($rating) . '</div>';
                }

                $play_background_url = FP_MOVIES_URL . 'img/play_1.svg';

                $quality_html = '';
                if ($home_data['show_quality'] === 'true') {
                    $quality_html = '<div class="fp-image-quality">' . esc_html(strtoupper($quality)) . '</div>';
                }

?>
                <div class="fp-homepage-item">
                    <div class="fp-homepage-thumb">
                        <a href="<?php echo esc_url($permalink); ?>">
                            <img src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr($title); ?>" width="100%" height="100%">
                            <div class="h-play" style="background: url('<?php echo esc_url($play_background_url); ?>') no-repeat center center; background-size: 80px 80px;"></div>
                        </a>
                    </div>
                    <div class="fp-image-title <?php echo esc_attr($title_wrap) ?>" style="background: <?php echo esc_attr($gradientCss); ?>"><?php echo esc_html($title); ?></div>

                    <?php if ($home_data['show_ratings'] === 'true') : ?>
                        <div class="fp-image-ab-wrapper-base fp-image-rating-wrapper"> <?php echo $ratings_html; ?> </div>
                    <?php endif; ?>

                    <?php if ($home_data['show_quality'] === 'true') : ?>
                        <div class="fp-image-ab-wrapper-base fp-image-quality-wrapper"><?php echo $quality_html; ?></div>
                    <?php endif; ?>

                </div>
<?php
            }
        } else {
            echo '<p>No content found.</p>';
        }
        $output = ob_get_clean();
        // id random then max_num_pages = 1
        if ($sort_type === 'random') {
            $max_num_pages = 1;
        } else {
            $max_num_pages = $query->max_num_pages;
        }
        // $max_num_pages = $query->max_num_pages;
        // error_log('max_num_pages: ' . $max_num_pages);
        wp_send_json_success([
            'content' => $output,
            'max_num_pages' => $max_num_pages,
        ]);
        wp_reset_postdata();
        wp_die();
    }
}

add_action('wp_ajax_fp_load_posts', 'fp_load_posts_view');
add_action('wp_ajax_nopriv_fp_load_posts', 'fp_load_posts_view');

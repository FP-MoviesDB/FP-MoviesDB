<?php
if (!defined('ABSPATH')) exit;

if (!function_exists('fp_display_home')) {
    function fp_display_home($atts)
    {
        global $fp_min_m;
        static $fp_instance_counter_home = 0;
        static $fp_home_localized_data = array();
        $instance_id = 'fp_homepage_' . $fp_instance_counter_home++;

        $homepage_template_settings = get_option('mtg_homepage_template_settings', []);
        $default_settings = [
            'title_background' => 'normal',
            'title_length' => 'auto',
            'title_wrap' => 'nowrap',
            'layout_type' => 'vertical',
            'image_source' => 'local',
            'image_size' => 'original',
        ];
        $homepage_template_settings = wp_parse_args($homepage_template_settings, $default_settings);
        $default_atts = [
            'type' => 'meta',               // meta, taxonomy
            'content_type' => 'movie',      // movie, series, taxonomy terms
            'taxonomy' => '',               // mtg_year, mtg_language, mtg_genre
            'heading' => '',                // Title of the section
            'limit' => 10,                  // Number of posts to display
            'image_source' => '',           // local, imdb
            'image_size' => '',             // small, medium, large, original
            'sort_type' => 'latest',        // latest, trending, top-rated, random
            'post_title' => 'original',
            'title_background' => 'normal',     // normal, gradient
            'show_ratings' => 'true',
            'show_quality' => 'true',
        ];

        $atts = shortcode_atts($default_atts, $atts, 'fp-homepage-view');
        $all_keys = array_keys(array_merge($homepage_template_settings, $atts));
        $f_atts = [];

        foreach ($all_keys as $key) {
            if (!empty($atts[$key])) {
                $f_atts[$key] = $atts[$key];
            } elseif (!empty($homepage_template_settings[$key])) {
                $f_atts[$key] = $homepage_template_settings[$key];
            } else {
                $f_atts[$key] = '';
            }
        }

        // error_log('$atts Image Source: ' . $f_atts['image_source']);

        $fp_home_localized_data[$instance_id] = array(
            'ajax_url' => FP_MOVIES_AJAX,
            'nonce' => wp_create_nonce('fp_homepage_nonce'),
            'home_data' => $f_atts,

        );

        wp_enqueue_script('fp-homepage-view', esc_url(FP_MOVIES_URL) . '/templates/js/fp_homepage' . $fp_min_m . '.js', array('jquery'), FP_MOVIES_FILES, true);
        wp_localize_script('fp-homepage-view', 'fp_homepage_data', $fp_home_localized_data);

        $args = array(
            'post_type' => 'post',
            'posts_per_page' => $f_atts['limit'],
            'paged' => 1,
            'post_status' => 'publish',
        );

        if ($f_atts['type'] === 'meta') {
            $f_atts['content_type'] = $f_atts['content_type'] === 'movie' ? 'movie' : 'series';
        } elseif ($f_atts['type'] === 'featured') {
            $f_atts['content_type'] = in_array($f_atts['content_type'], ['movie', 'series']) ? $f_atts['content_type'] : 'both';
        }

        if ($f_atts['type'] === 'meta' && !empty($f_atts['content_type'])) {
            $args['meta_query'] = array(
                'relation' => 'AND',
                array(
                    'key' => 'mtg_post_type',
                    'value' => $f_atts['content_type'],
                    'compare' => '='
                ),
            );
        } elseif (
            $f_atts['type'] === 'taxonomy' &&
            !empty($f_atts['taxonomy']) &&
            !empty($f_atts['content_type'])
        ) {
            $taxonomies = explode(',', $f_atts['content_type']);
            // error_log('Taxonomies: ' . print_r($taxonomies, true));
            $args['tax_query'] = array(
                array(
                    'taxonomy' => $f_atts['taxonomy'],
                    'field' => 'slug',
                    'terms' => $taxonomies,
                ),
            );
        } else if ($f_atts['type'] === 'featured') {
            $args['meta_query'] = array(
                'relation' => 'AND',
                array(
                    'key' => 'mtg_is_featured',
                    'value' => '1',
                    'compare' => '='
                ),
            );
            if (in_array($f_atts['content_type'], ['movie', 'series'])) {
                $args['meta_query'][] = array(
                    'key' => 'mtg_post_type',
                    'value' => $f_atts['content_type'],
                    'compare' => '='
                );
            }
        } else {
            return '<p>Invalid shortcode attributes provided. Ensure content_type and taxonomy are set correctly.</p>';
        }

        switch ($f_atts['sort_type']) {
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

        $query = new WP_Query($args);
        $image_size_mapping = [
            'small' => 'w342',
            'medium' => 'w500',
            'large' => 'w780',
            'original' => 'original',
        ];

        ob_start();
        if ($f_atts['type'] === 'featured') {
            if ($query->have_posts()) {
                update_post_caches($query->posts, ['post_meta']);
                $image_size = $image_size_mapping[$f_atts['image_size']] ?? 'w500';
                $max_num_pages = $query->max_num_pages;
                $current_post = 0;
?>
                <div class="featured-posts-main-wrapper">
                    <div class="image-slider-wrapper">
                        <?php
                        while ($query->have_posts()) : $query->the_post();
                            $landscape_img_tmdb_path = get_post_meta(get_the_ID(), 'mtg_backdrop_path', true);
                            if (empty($landscape_img_tmdb_path)) {
                                $landscape_img = esc_url(FP_MOVIES_URL) . 'img/image-not-found.webp';
                            } else {
                                $landscape_img = FP_MOVIES_TMDB_IMG_BASE_URL . 'original' . $landscape_img_tmdb_path;
                            }
                            $portrait_img_tmdb_path = get_post_meta(get_the_ID(), 'mtg_poster_path', true);


                            $image_url = '';
                            if ($f_atts['image_source'] === 'tmdb') {
                                $poster_path = get_post_meta(get_the_ID(), 'mtg_poster_path', true);
                                if (!empty($poster_path)) {
                                    if (str_starts_with($poster_path, '/')) {
                                        $image_size = $image_size_mapping[$f_atts['image_size']] ?? 'w500';
                                        $image_url = 'https://image.tmdb.org/t/p/' . $image_size . $poster_path;
                                    } else {
                                        $image_url = $poster_path;
                                    }
                                } else {
                                    $image_url = get_the_post_thumbnail_url(get_the_ID(), $f_atts['image_size']);
                                }
                            } else {
                                $image_url = get_the_post_thumbnail_url(get_the_ID(), $f_atts['image_size']);
                            }


                            if (empty($image_url)) {
                                $image_url = esc_url(FP_MOVIES_URL) . 'img/poster-not-found.png';
                            } 
                            // else {
                            //     $image_url = FP_MOVIES_TMDB_IMG_BASE_URL . $image_size . $portrait_img_tmdb_path;
                            // }
                            $post_count = $query->post_count;
                            // error_log('Post Count: ' . $post_count);
                            $title = get_the_title();
                            $title = strlen($title) > 20 ? substr($title, 0, 30) . '...' : $title;
                            $permalink = get_the_permalink();
                            $storyline = get_the_excerpt();
                            $storyline = strlen($storyline) > 150 ? substr($storyline, 0, 200) . '...' : $storyline;
                            $gradient_color = get_post_meta(get_the_ID(), 'mtg_gradient_color', true);
                            if ($f_atts['title_background'] === 'gradient' && !empty($gradient_color)) {
                                $bk_gd = 'background: ' . $gradient_color . ';';
                            } else {
                                $bk_gd = '';
                            }

                            $btn_heading = 'Watch Now';
                            if (!empty($f_atts['heading'])) {
                                $btn_heading = $f_atts['heading'];
                            }

                            $mtg_tmdb_tagline = get_post_meta(get_the_ID(), 'mtg_tmdb_tagline', true);
                            $tagline = '';
                            if (!empty($mtg_tmdb_tagline)) {
                                $tagline = '<div class="tagline">' . '-' . $mtg_tmdb_tagline . '</div>';
                            }


                        ?>
                            <div class="slide">
                                <img src="<?php echo esc_url($landscape_img); ?>" alt="<?php echo esc_attr(get_the_title()); ?>" class="landscape-image" loading="lazy">
                                <div class="image-absolute-content">
                                    <div class="absolute-wrapper">
                                        <a href="<?php echo esc_url($permalink); ?>">
                                            <div class="image-wrapper">
                                                <img src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr(get_the_title()); ?>" class="portrait-image" loading="lazy">
                                            </div>
                                        </a>
                                        <div class="content-wrapper">
                                            <div class="f-title">
                                                <h2 class="title" data-text="<?php echo esc_attr($title); ?>">
                                                    <?php echo esc_html($title); ?></h2>
                                                <?php echo $tagline; ?>
                                            </div>
                                            <div class="content">

                                                <?php echo esc_html($storyline); ?>
                                            </div>
                                            <a href="<?php echo esc_url($permalink); ?>">
                                                <div class="featured-button" style="<?php echo esc_attr($bk_gd); ?>">
                                                    <img src="<?php echo esc_url(FP_MOVIES_URL . 'img/play_light.svg'); ?>" alt="Play Icon" width="50" height="auto" loading="lazy">
                                                    <span class="read-more">
                                                        <?php echo esc_html($btn_heading); ?>
                                                    </span>
                                                </div>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        <?php
                            $current_post++;
                        endwhile;
                        ?>

                        <div class="slider-controls">
                            <button class="prev-slide">&#10094;</button>
                            <button class="next-slide">&#10095;</button>
                        </div>
                    </div>
                </div>
            <?php
            }
        } else {


            echo '<div class="fp-homepage-grid-wrapper-main" data-instance-id="' . esc_attr($instance_id) . '">';

            if (empty($f_atts['type']) || !in_array($f_atts['type'], ['meta', 'taxonomy'])) {
                return '<p>!! Something went WrOng !!</p>';
            }

            echo '<div class="fp-homepage-grid-wrapper">';
            if ($f_atts['heading']) {
            ?>
                <div class="fp-homepage-title-wrapper">
                    <div class="start-tab-wrapper">
                        <h2 class="fp-homepage-title"><?php echo esc_html($f_atts['heading']); ?></h2>
                        <div class="pagination-wrapper-mobile">
                            <div class="pg_arrow pagination-left pagination-left-mobile"> &lt; </div>
                            <div class="page_number page_number-mobile">1</div>
                            <div class="pg_arrow pagination-right pagination-right-mobile"> &gt; </div>
                        </div>
                    </div>
                    <div class="end-tab-wrapper">
                        <div class="end-tab">
                            <div id='latest' class='end-tab-item active'>Latest</div>
                            <div id='trending' class='end-tab-item'>Trending</div>
                            <div id='top-rated' class='end-tab-item'>Top Rated</div>
                            <div id='random' class='end-tab-item'>Random</div>
                        </div>
                        <div class="pagination-wrapper-pc">
                            <div class="pg_arrow pagination-left pagination-left-pc"> &lt; </div>
                            <div class="page_number page_number-pc">1</div>
                            <div class="pg_arrow pagination-right pagination-right-pc"> &gt; </div>
                        </div>
                    </div>
                </div>
            <?php
            }
            if ($query->have_posts()) {
                // $post_ids = wp_list_pluck($query->posts, 'ID');
                // update_post_caches($query->posts, ['post_meta']);
                update_post_caches($query->posts, 'post', true, true);
                $max_num_pages = $query->max_num_pages;

            ?>
                <div class="fp-homepage-grid-parent" data-total-pages="<?php echo esc_attr($max_num_pages); ?>">
                    <div class="fp-homepage-grid">
                        <?php while ($query->have_posts()) : $query->the_post();
                            $post_id = get_the_ID();
                            if ($f_atts['post_title'] === 'tmdb') {
                                $title = get_post_meta($post_id, 'mtg_tmdb_title', true);
                            } else {
                                $title = get_the_title($post_id);
                            }

                            if ($f_atts['title_length'] !== 'auto') {
                                $length = intval($f_atts['title_length']);
                                if (function_exists('fp_log_error')) fp_log_error('Title Length: ' . $length);
                                // error_log('Title Length: ' . $length);
                                // check if $length is a valid number if not then don't truncate
                                if ($length > 0 && strlen($title) > $length) {
                                    $title = substr($title, 0, $length) . '...';
                                }
                            }

                            $permalink = get_permalink($post_id);
                            $image_url = '';

                            if ($f_atts['image_source'] === 'tmdb') {
                                $poster_path = get_post_meta($post_id, 'mtg_poster_path', true);
                                if (!empty($poster_path)) {
                                    if (str_starts_with($poster_path, '/')) {
                                        $image_size = $image_size_mapping[$f_atts['image_size']] ?? 'w500';
                                        $image_url = 'https://image.tmdb.org/t/p/' . $image_size . $poster_path;
                                    } else {
                                        $image_url = $poster_path;
                                    }
                                } else {
                                    $image_url = get_the_post_thumbnail_url($post_id, $f_atts['image_size']);
                                }
                            } else {
                                $image_url = get_the_post_thumbnail_url($post_id, $f_atts['image_size']);
                            }
                            if (empty($image_url) || !filter_var($image_url, FILTER_VALIDATE_URL)) {
                                $image_url = esc_url(FP_MOVIES_URL) . 'img/poster-not-found.png';
                            }
                            // get quality taxonomy at 1st position
                            $quality = get_the_terms($post_id, 'mtg_quality');
                            $quality = !empty($quality) ? $quality[0]->name : 'HD';

                            $rating = get_post_meta($post_id, 'mtg_vote_average', true);
                            $formatted_rating = sprintf("%.1f", $rating);
                            $rating = str_replace('.0', '', $formatted_rating);
                            $gradientCss = '';

                            if ($f_atts['title_background'] === 'gradient') {
                                $post_gradient = get_post_meta($post_id, 'mtg_gradient_color', true);
                                if (!empty($post_gradient)) {
                                    $gradientCss = $post_gradient;
                                } else {
                                    $gradientCss = fp_calculateImageGradient($image_url);
                                    update_post_meta($post_id, 'mtg_gradient_color', $gradientCss);
                                }
                            }
                            // <!-- add css fp-image-title-textNoWrap : fp-image-title-textWrap -> $f_atts['title_wrap'] -->
                            $title_wrap = $f_atts['title_wrap'] === 'wrap' ? 'fp-image-title-textWrap' : 'fp-image-title-textNoWrap';

                            $ratings_html = '';
                            if ($f_atts['show_ratings'] === 'true' && !empty($rating)) {
                                $ratings_html = '
                                <img class="fp-image-rating-icon" src="' . esc_url(FP_MOVIES_URL) . 'img/star-dark.svg" alt="IMDb" width="15" height="auto">
                                <div class="fp-image-rating">' . esc_html($rating) . '</div>';
                            }
                            $play_background_url = esc_url(FP_MOVIES_URL) . 'img/play_1.svg';

                            $quality_html = '';
                            if ($f_atts['show_quality'] === 'true') {
                                $quality_html = '<div class="fp-image-quality">' . esc_html(strtoupper($quality)) . '</div>';
                            }

                        ?>
                            <div class="fp-homepage-item">
                                <div class="fp-homepage-thumb">
                                    <a href="<?php echo esc_url($permalink); ?>">
                                        <img src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr($title); ?>" width="100%" height="100%" loading="lazy">
                                        <div class="h-play" style="background: url('<?php echo esc_url($play_background_url); ?>') no-repeat center center; background-size: 80px 80px;"></div>
                                    </a>
                                </div>

                                <div class="fp-image-title <?php echo esc_attr($title_wrap) ?>" style="background: <?php echo esc_attr($gradientCss); ?>"><?php echo esc_html($title); ?></div>
                                <?php if ($f_atts['show_ratings'] === 'true') : ?>
                                    <div class="fp-image-ab-wrapper-base fp-image-rating-wrapper"> <?php echo $ratings_html; ?> </div>
                                <?php endif; ?>

                                <?php if ($f_atts['show_quality'] === 'true') : ?>
                                    <div class="fp-image-ab-wrapper-base fp-image-quality-wrapper"><?php echo $quality_html; ?></div>
                                <?php endif; ?>
                            </div>
        <?php
                        endwhile;
                        echo '</div>';
                        echo '</div>';
                    } else {
                        echo '<p>No content found.</p>';
                    }
                    echo '</div>';
                    echo '</div>';
                }

                wp_reset_postdata();
                return ob_get_clean();
            }
        }

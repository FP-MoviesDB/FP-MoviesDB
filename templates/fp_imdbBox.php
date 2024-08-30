<?php

if (!defined('ABSPATH')) exit;




if (!function_exists('fp_tmdbview')) {
    function fp_tmdbview()
    {
        $post_id = get_the_ID();
        $allowed_html = [
            'a' => [
                'href' => [],
                'title' => [],
                'class' => []
            ],
            'span' => [
                'class' => []
            ]
        ];

        if (!$post_id) {
            // error_log('POST ID NOT FOUND');
            return 'POST ID NOT FOUND';
        }

        include FP_MOVIES_DIR . 'templates/helpers/fp_imdbBox.php';

        // error_log('Post ID: ' . $post_id);

        $meta_data = FP_Movies_Metadata_Cache::get_meta_data($post_id);
        if (empty($meta_data)) {
            $meta_data = get_movie_tv_post_meta($post_id);
            // error_log('Falling back to direct metadata fetching for post ID: ' . $post_id);
        }

        $genre_terms_link = $meta_data['fp_genres_ORG'];
        $audio_terms_link = $meta_data['fp_audio_ORG'];
        $network_terms_link = $meta_data['fp_network_ORG'];
        $cast_terms_link = $meta_data['fp_cast_ORG'];
        $crew_terms_link = $meta_data['fp_crew_ORG'];
        $collection_terms_link = $meta_data['fp_collection_ORG'];

        $genre_links = term_to_link($genre_terms_link, '');
        $audio_links = term_to_link($audio_terms_link, '');
        $network_links = term_to_link($network_terms_link, '');
        $cast_links = term_to_link($cast_terms_link, '', 20);
        $crew_links = term_to_link($crew_terms_link, '', 20);
        $collection_links = term_to_link($collection_terms_link, '', 20);


        if (!empty($meta_data['fp_imdb'])) {
            $box_href = FP_MOVIES_IMDB_BASE_URL . '/' . esc_attr($meta_data['fp_imdb']);
        } else {
            if ($meta_data['_postType'] === "movie") {
                $box_href = FP_MOVIES_TMDB_BASE_URL . '/movie/' . esc_attr($meta_data['fp_tmdb']);
            } else {
                $box_href = FP_MOVIES_TMDB_BASE_URL . '/tv/' . esc_attr($meta_data['fp_tmdb']);
            }
        }
        $link_tmdb = FP_MOVIES_TMDB_BASE_URL . '/' . strtolower($meta_data['_postType']) . '/' . $meta_data['fp_tmdb'];
        $avg_rating = $meta_data['fp_vote_average'] ?? 7;
        $vote_count = $meta_data['fp_vote_count'] ?? 1;
?>
        <div class="imdb_wrapper">
            <div class="imdbwp imdbwp--movie dark">
                <div class="imdbwp__thumb">
                    <a class="imdbwp__link" target="_blank" title="<?php echo esc_attr($meta_data['fp_tmdb_title']) ?>" href="<?php echo esc_url($box_href) ?>" rel="noopener nofollow external noreferrer" data-wpel-link="external"><img decoding="async" class="imdbwp__img" src="https://image.tmdb.org/t/p/w154<?php echo esc_attr($meta_data['fp_poster']) ?>" alt="Download <?php echo esc_attr($meta_data['fp_tmdb_title']) ?> (<?php echo esc_attr($meta_data['fp_latest_year']) ?>) <?php echo esc_attr($meta_data['fp_audio']) ?> Audio" title="Download <?php echo esc_attr($meta_data['fp_tmdb_title']) ?> (<?php echo esc_attr($meta_data['fp_latest_year']) ?>) <?php echo esc_attr($meta_data['fp_audio']) ?> Audio <?php echo esc_attr($meta_data['fp_quality']) ?> <?php echo esc_attr($meta_data['_postType']) ?>" width="150px" height="230px" />
                    </a>
                </div>
                <div class="imdbwp__content">
                    <div class="imdbwp__header">
                        <div class="imdbwp__title_wrapper"><a href="<?php echo esc_url($link_tmdb) ?>" target="_blank" title="<?php echo esc_html($meta_data['fp_tmdb_title']) ?>"><span class="imdbwp__title"><?php echo esc_html($meta_data['fp_tmdb_title']) ?></span><?php if (!empty($meta_data['fp_latest_year'])) : ?><span class="imdbwp__title_year"> (<?php echo esc_html($meta_data['fp_latest_year']) ?>)</span><?php endif; ?></a></div>
                        <?php if (!empty($genre_links)) : ?>
                            <div class="imdbwp__meta__wrapper">
                                <div class="imdbwp__meta">
                                    <span class="imdbwp__meta_key" style="font-weight: bold;">Genres: </span><span class="imdb__meta_value genre-imdb"><?php
                                                                                                                                                        // Safe output: $genre_links is constructed with proper escaping of URLs and text.
                                                                                                                                                        echo wp_kses($genre_links, $allowed_html); ?></span>
                                </div>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($audio_links)) : ?>
                            <div class="imdbwp__meta__wrapper">
                                <div class="imdbwp__meta">
                                    <span class="imdbwp__meta_key" style="font-weight: bold;">Audios: </span><span class="imdb__meta_value audio-imdb"><?php
                                                                                                                                                        // Safe output: $genre_links is constructed with proper escaping of URLs and text.
                                                                                                                                                        echo wp_kses($audio_links, $allowed_html); ?></span>
                                </div>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($meta_data['fp_network_ORG'])) : ?>
                            <div class="imdbwp__meta__wrapper">
                                <div class="imdbwp__meta">
                                    <span class="imdbwp__meta_key" style="font-weight: bold;">Network: </span><span class="imdb__meta_value network-imdb"><?php echo wp_kses($network_links, $allowed_html); ?></span>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($meta_data['fp_cast_ORG'])) : ?>
                            <div class="imdbwp__meta__wrapper">
                                <div class="imdbwp__meta">
                                    <span class="imdbwp__meta_key" style="font-weight: bold;">Cast: </span><span class="imdb__meta_value cast-imdb"><?php echo wp_kses($cast_links, $allowed_html); ?></span>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($meta_data['fp_crew_ORG'])) : ?>
                            <div class="imdbwp__meta__wrapper">
                                <div class="imdbwp__meta">
                                    <span class="imdbwp__meta_key" style="font-weight: bold;">Crew: </span><span class="imdb__meta_value crew-imdb"><?php echo wp_kses($crew_links, $allowed_html); ?></span>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($meta_data['fp_collection_ORG'])) : ?>
                            <div class="imdbwp__meta__wrapper">
                                <div class="imdbwp__meta">
                                    <span class="imdbwp__meta_key" style="font-weight: bold;">Collection: </span><span class="imdb__meta_value collection-imdb"><?php echo wp_kses($collection_links, $allowed_html); ?></span>
                                </div>
                            </div>
                        <?php endif; ?>



                    </div>

                    <div class="imdbwp__belt">
                        <span class="imdbwp__star" style="background-image: url('<?php echo esc_attr(FP_MOVIES_URL . "/img/yellow-slant-star.png") ?>');"><?php echo esc_html($avg_rating) ?></span><span class="imdbwp__rating"><strong>Rating:</strong> <?php echo esc_html($avg_rating) ?> / 10 from <?php echo esc_html($vote_count) ?> users</span>
                    </div>
                    <?php if (!empty($meta_data['fp_overview'])) : ?>
                        <div class="imdbwp__teaser">
                            <?php
                            $content = $meta_data['fp_overview'];
                            $trimmed_content = wp_trim_words($content, 25, '...etc ');
                            echo esc_html($trimmed_content);
                            ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
<?php
    }
    fp_tmdbview();
}
?>
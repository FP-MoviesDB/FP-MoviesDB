<?php

if (!defined('ABSPATH')) exit;

if (!function_exists('fp_tmdbview')) {
    function fp_tmdbview()
    {
        $post_id = get_the_ID();

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
        $genre_links = term_to_link($genre_terms_link, '');
        $audio_links = term_to_link($audio_terms_link, '');
        $network_links = term_to_link($network_terms_link, '');

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
                    <a class="imdbwp__link" target="_blank" title="<?php echo $meta_data['fp_tmdb_title'] ?>" href="<?php echo $box_href ?>" rel="noopener nofollow external noreferrer" data-wpel-link="external"><img decoding="async" class="imdbwp__img" src="https://image.tmdb.org/t/p/w154<?php echo $meta_data['fp_poster'] ?>" alt="Download <?php echo $meta_data['fp_tmdb_title'] ?> (<?php echo $meta_data['fp_latest_year'] ?>) <?php echo $meta_data['fp_audio'] ?> Audio" title="Download <?php echo $meta_data['fp_tmdb_title'] ?> (<?php echo $meta_data['fp_latest_year'] ?>) <?php echo $meta_data['fp_audio'] ?> Audio <?php echo $meta_data['fp_quality'] ?> <?php echo $meta_data['_postType'] ?>" width="150px" height="230px" />
                    </a>
                </div>
                <div class="imdbwp__content">
                    <div class="imdbwp__header">
                        <div class="imdbwp__title_wrapper"><a href="<?php echo $link_tmdb ?>" target="_blank" title="<?php echo $meta_data['fp_tmdb_title'] ?>"><span class="imdbwp__title"><?php echo $meta_data['fp_tmdb_title'] ?></span><?php if (!empty($meta_data['fp_latest_year'])) : ?><span class="imdbwp__title_year"> (<?php echo $meta_data['fp_latest_year'] ?>)</span><?php endif; ?></a></div>
                        <?php if (!empty($genre_links)) : ?>
                        <div class="imdbwp__meta__wrapper">
                            <div class="imdbwp__meta">
                                <span class="imdbwp__meta_key" style="font-weight: bold;">Genres: </span><span class="imdb__meta_value genre-imdb"><?php echo $genre_links; ?></span>
                            </div>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($audio_links)) : ?>
                        <div class="imdbwp__meta__wrapper">
                            <div class="imdbwp__meta">
                                <span class="imdbwp__meta_key" style="font-weight: bold;">Audios: </span><span class="imdb__meta_value audio-imdb"><?php echo $audio_links; ?></span>
                            </div>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($meta_data['fp_network_ORG'])) : ?>
                            <div class="imdbwp__meta__wrapper">
                                <div class="imdbwp__meta">
                                    <span class="imdbwp__meta_key" style="font-weight: bold;">Network: </span><span class="imdb__meta_value network-imdb"><?php echo $network_links; ?></span>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="imdbwp__belt">
                        <span class="imdbwp__star" style="background-image: url('<?= FP_MOVIES_URL . "/img/yellow-slant-star.png" ?>');"><?= $avg_rating ?></span><span class="imdbwp__rating"><strong>Rating:</strong> <?php echo $avg_rating ?> / 10 from <?php echo $vote_count ?> users</span>
                    </div>
                    <?php if (!empty($meta_data['fp_overview'])) : ?>
                    <div class="imdbwp__teaser">
                        <?php
                        $content = $meta_data['fp_overview'];
                        $trimmed_content = wp_trim_words($content, 25, '...etc ');
                        echo $trimmed_content;
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
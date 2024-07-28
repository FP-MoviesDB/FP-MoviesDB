<?php

if (!defined('ABSPATH')) die();

class FP_PlayerTV
{
    function prepare_fp_player_data($post_id, $tmdb_id, $meta_data)
    {
        // Attempt to retrieve the existing transient data
        $fp_player_data_key = 'fp_cache_playerData_' . $post_id;
        $fp_player_data = fp_get_cache($fp_player_data_key);
        // $fp_player_data = get_transient('fp_cache_playerData_' . $post_id);

        // Check if the transient exists and has data
        if (false !== $fp_player_data && !empty($fp_player_data)) {
            // If data is valid, no need to process it again
            // error_log('Transient data found for post ID: ' . $post_id);
            return $fp_player_data;
        }

        // If the transient is empty or doesn't exist, prepare the data
        $fp_player_data = array();

        // FETCH USER ADDED FIELDS
        $user_added_fields = $meta_data['fp_player_data'];

        // set $user_added_fields to empty array if it's not an array
        if (!is_array($user_added_fields)) {
            $user_added_fields = array();
        }

        // error_log(print_r($user_added_fields, true));


        foreach ($user_added_fields as $season => $episodes) {
            foreach ($episodes as $episode => $sources) {
                if (!isset($fp_player_data[$season])) {
                    $fp_player_data[$season] = [];
                }

                foreach ($sources as $source) {
                    if (!isset($fp_player_data[$season][$episode])) {
                        $fp_player_data[$season][$episode] = [];
                    }

                    $currentPosition = count($fp_player_data[$season][$episode]) + 1;

                    $fp_player_data[$season][$episode][$currentPosition] = [
                        'position' => $currentPosition,
                        'type' => 'i', // Defaulting to 'i' if not provided
                        'title' => $source['title'] ?? '',
                        'language' => $source['language'] ?? '',
                        'url' => $source['url'] ?? '' // Including URL in the data structure
                    ];
                }
            }
        }

        // error_log(print_r($fp_player_data, true));

        // FETCH FP API DATA
        $data = fetch_fpTVData($tmdb_id);
        if ($data && $data['status']) {
            $files = $data['data']['files'];
            foreach ($files as $file) {
                $season = $file['seasonNumber'];
                $episode = $file['episodeNumber'];

                if ($season < 0 || $episode < 0) {
                    continue;
                }

                if (!isset($fp_player_data[$season][$episode])) {
                    $fp_player_data[$season][$episode] = [];
                }
                $global_id = 'g'; // 'g' for global sources
                if (!array_key_exists($global_id, $fp_player_data[$season][$episode])) {
                    $fp_player_data[$season][$episode][$global_id] = [
                        'type' => 'g',
                    ];
                }
            }
        }

        ksort($fp_player_data);
        foreach ($fp_player_data as $season => $episodes) {
            ksort($episodes);
            $fp_player_data[$season] = $episodes;
        }

        // error_log('Prepared data for post ID: ' . $post_id);
        // error_log(print_r($fp_player_data, true));

        // Store the freshly prepared data into the transient
        $fp_player_data_key = 'fp_cache_playerData_' . $post_id;
        // store in browser session
        fp_store_cache($fp_player_data_key, $fp_player_data);
        // set_transient('fp_cache_playerData_' . $post_id, $fp_player_data, 60);

        return $fp_player_data;
    }


    public function fp_tvPlayer($post_id, $meta_data)
    {
        if (!$post_id) {
            if (function_exists('fp_log_error')) fp_log_error('No post ID');
            // error_log('No post ID');
            return;
        }
        $template_settings = FP_Movies_Shortcodes::get_template_settings();
        require_once FP_MOVIES_DIR . 'helper/fp_get_backdrop.php';
        $quality = 'original';
        if (isset($template_settings['sPlayer_Backdrop_Quality'])) {
            $quality = $template_settings['sPlayer_Backdrop_Quality'];
        }
        if (isset($template_settings['sPlayer_Fallback_Image_URL'])) {
            $fallback_image = $template_settings['sPlayer_Fallback_Image_URL'];
        } else {
            $fallback_image = FP_MOVIES_URL . 'img/image-not-found.webp';
        }
        $backdrop = getBackdrop($meta_data, $fallback_image, $quality);
        $title = $meta_data['fp_tmdb_title'];

        if (strlen($title) > 25) {
            $title = substr($title, 0, 25) . '...';
        }

        // SET UP YouTube Trailer
        $youtube_array = array();
        $trailer = $meta_data['fp_trailer'];
        if (!empty($trailer)) $youtube_array = array('title' => 'Trailer', 'type' => 'trailer', 'extra' => '◎ YouTube ◉');


        $tmdb_id = $meta_data['fp_tmdb'];
        $fp_player_data = $this->prepare_fp_player_data($post_id, $tmdb_id, $meta_data);

?>
        <div class="fp-player-wrapper">
            <div class="fp-player">
                <!-- BACKDROP  -->
                <div id="fp-backdrop-container" class="fp_backdrop">
                    <img src="<?php echo esc_attr($backdrop); ?>" alt="<?php echo esc_attr($meta_data['fp_title']); ?>" width="500px" height="300px">
                </div>
                <!-- PLAY ICON  -->
                <div class="play-icon-wrapper">
                    <div class="play-icon">
                        <!-- <i class="fas fa-play" style="font-size: 50px;"></i> -->
                        <img src="<?php echo esc_url(FP_MOVIES_URL . 'img/play_light.svg'); ?>" alt="Play Icon" width="50" height="auto">
                    </div>
                </div>
                <!-- DISPLAY TITLE  -->
                <?php if (!empty($title)) : ?>
                    <div class="play-title-wrapper">
                        <div class="play-title">
                            <h1 data-text="<?php echo esc_attr($title); ?>"><?php echo esc_html($title); ?></h1>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- DISPLAY LOADER  -->
                <div class="loader-wrapper">
                    <div class="loader"></div>
                </div>
                <div id="fp_player_response"></div>
            </div>
            <div class="tv-media-controller">
                <div class="stream-sources">
                    <ul class="stream-sources-list ajax_mode">
                        <?php if (!empty($youtube_array) && is_array($youtube_array)) : ?>
                            <?php
                            $title = $youtube_array['title'];
                            $title = ucwords($title);
                            $type = $youtube_array['type'];
                            $post_type = $meta_data['_postType'];
                            $position = 1;
                            $extra_meta = $youtube_array['extra'];
                            $icon = 'fab fa-youtube';
                            ?>
                            <li id="stream-source-<?php echo esc_attr($position + 1); ?>" class="fp_player_option" data-type="<?php echo esc_attr($type); ?>" data-position="<?php echo esc_attr($position); ?>" data-post="<?php echo esc_attr($post_id); ?>" data-pType="<?php echo esc_attr($post_type); ?>" data-title="<?php echo esc_attr($youtube_array['title']); ?>">
                                <i class="<?php echo esc_attr($icon); ?>"></i><span class="title"><?php echo esc_html($title); ?></span><span class="extra-meta
                                "><?php echo esc_html($extra_meta); ?></span>
                            </li>
                        <?php endif; ?>
                    </ul>
                    <?php if (!empty($fp_player_data) && is_array($fp_player_data)) : ?>
                        <!-- Initial Empty -->
                        <div class="episodes-sources-wrapper">
                        </div>
                        <div class="tv-show-data-wrapper">
                            <div class="season">

                                <select id="season-selector">
                                    <?php
                                    $firstSeason = key($fp_player_data);
                                    foreach ($fp_player_data as $season => $episodes) : ?>
                                        <option value="season-<?php echo esc_attr($season); ?>" <?php echo esc_attr($season == $firstSeason ? 'selected' : ''); ?>>
                                            Season <?php echo esc_html($season); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div id="episodes-container">
                                <?php foreach ($fp_player_data as $season => $episodes) : ?>
                                    <div class="episodes" id="episodes-season-<?php echo esc_attr($season); ?>" style="display: none;">
                                        <?php foreach ($episodes as $episode => $sources) : ?>
                                            <div class="episode-box" data-season="<?php echo esc_attr($season); ?>" data-episode="<?php echo esc_attr($episode); ?>" data-postid="<?php echo esc_attr($post_id); ?>">
                                                <span class="episode-number"><?php echo esc_html($episode < 10 ? '0' . $episode : $episode); ?></span>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                    <?php endif; ?>
                </div>
            </div>


        </div>
<?php
    }
}

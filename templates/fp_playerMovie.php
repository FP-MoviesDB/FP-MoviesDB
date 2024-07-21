<?php

if (!defined('ABSPATH')) die();

class FP_PlayerMovie
{
    public function fp_moviePlayer($post_id, $meta_data)
    {
        if (!$post_id) {
            // error_log('No post ID');
            if (function_exists('fp_log_error')) fp_log_error('No post ID');
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

        $player_fields = $meta_data['fp_player_data'];
        if (empty($player_fields)) {
            $player_fields = array();
        }
        $trailer = $meta_data['fp_trailer'];
        if (!empty($trailer) && is_array($player_fields)) {
            array_unshift($player_fields, array('title' => 'Trailer', 'type' => 'trailer', 'extra' => '◎ YouTube ◉'));
        }

        // add another data to $player_fields in the last
        $player_fields[] = array('title' => 'Watch Now', 'type' => 'global', 'extra' => '◎ Global ◉');
?>
        <div class="fp-player-wrapper">
            <div class="fp-player">
                <!-- BACKDROP  -->
                <div id="fp-backdrop-container" class="fp_backdrop">
                    <div id="jwplayer-container" style="display:none;"></div>
                    <img src="<?php echo $backdrop; ?>" alt="<?php echo $meta_data['fp_title']; ?>" width="500px" height="300px">
                </div>
                <!-- PLAY ICON  -->
                <div class="play-icon-wrapper">
                    <div class="play-icon">
                        <!-- <i class="fas fa-play" style="font-size: 50px;"></i> -->
                        <img src="<?php echo FP_MOVIES_URL . 'img/play_light.svg' ?>" alt="Play Icon" width="50" height="auto">
                    </div>
                </div>
                <!-- DISPLAY TITLE  -->
                <?php if (!empty($title)) : ?>
                    <div class="play-title-wrapper">
                        <div class="play-title">
                            <h1 data-text="<?php echo htmlspecialchars($title); ?>"><?php echo $title; ?></h1>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- DISPLAY LOADER  -->
                <div class="loader-wrapper">
                    <div class="loader"></div>
                </div>
                <div id="fp_player_response"></div>
            </div>

            <?php if (!empty($player_fields) && is_array($player_fields)) : ?>
                <div class="movie-media-controller">
                    <div class="stream-sources">
                        <ul class="stream-sources-list ajax_mode">
                            <?php foreach ($player_fields as $index => $player_field) : ?>
                                <?php
                                $title = $player_field['title'];
                                $title = ucwords($title);
                                $type = $player_field['type'];
                                $post_type = $meta_data['_postType'];
                                $position = $index + 1;
                                $extra_meta = '';
                                $icon = 'play_light.svg';
                                // $icon = 'fas fa-play-circle';
                                if ($player_field['type'] == 'trailer') {
                                    $type = 'trailer';
                                    $extra_meta = $player_field['extra'];
                                    $icon = 'youtube_light.svg';
                                    // $icon = 'fab fa-youtube';
                                } elseif ($player_field['type'] == 'global') {
                                    $type = 'global';
                                    $extra_meta = $player_field['extra'];
                                    $icon = 'globe_light.svg';
                                    // $icon = 'fas fa-globe';
                                }
                                ?>
                                <li id="stream-source-<?php echo $position + 1; ?>" class="fp_player_option" data-type="<?php echo esc_attr($type); ?>" data-position="<?php echo esc_attr($position); ?>" data-post="<?php echo esc_attr($post_id); ?>" data-pType="<?php echo esc_attr($post_type); ?>" data-title="<?php echo esc_attr($player_field['title']); ?>">
                                    <!-- <i class="<?php // echo esc_attr($icon); ?>"></i> -->
                                    <img src="<?php echo FP_MOVIES_URL . 'img/' . $icon ?>" alt="Youtube Icon" width="20" height="auto">
                                    
                                    <span class="title"><?php echo esc_html($title); ?></span><span class="extra-meta"><?php echo esc_html($extra_meta); ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            <?php endif; ?>



        </div>
<?php
    }
}

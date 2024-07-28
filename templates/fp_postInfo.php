<?php

if (!defined('ABSPATH')) exit;

class FP_PostInfo extends CreatePostHelper
{

    public function __construct()
    {
        $this->fp_postInfo(); // Call your function immediately
    }


    function fp_postInfo()
    {
        $post_id = get_the_ID();
        if (!$post_id) {
            return '';
        }

        $meta_data = FP_Movies_Metadata_Cache::get_meta_data($post_id);
        $template_settings = FP_Movies_Shortcodes::get_template_settings();

        $postData = array();
        if (empty($meta_data)) {
            $meta_data = get_movie_tv_post_meta($post_id);
            // error_log('Falling back to direct metadata fetching for post ID: ' . $post_id);
            $postData = array(
                'title' => $meta_data['fp_title'],
                't_title' => $meta_data['fp_tmdb_title'],
                'genre' => $meta_data['fp_genres'],
                'release_year' => $meta_data['fp_oldest_year'],
                'latest_year' => $meta_data['fp_latest_year'],
                'quality' => $meta_data['fp_quality'],
                'audio' => $meta_data['fp_audio'],
                'c_audio' => $meta_data['fp_audio_count_customValue'],
                'c_subs' => $meta_data['fp_subtitles_count_customValue'],
                'p_type' => $meta_data['fp_post_type_upper'],
                'network' => $meta_data['fp_network'],
                'separator' => $meta_data['fp_separator'],
                'post_type' => $meta_data['fp_post_type'],
                'resolutions' => $meta_data['fp_resolution'],
            );
        } else {
            try {
                $postData = FP_Movies_Metadata_Cache::get_processed_data();
            } catch (Exception $e) {
                if (function_exists('fp_log_error')) fp_log_error('Processed data not found. Fetching directly.');
                // error_log('Processed data not found. Fetching directly.');
                return '';
            }
        }

        // $postInfo_title = get_option_with_fallback('mtg_template_post_info_title_shortcodes', "{title} Info:");
        $postInfo_title = $this->get_arrayValue_with_fallback($template_settings, 'sInfo_Title', "{title} Info:");
        $postInfo_title = $this->format_title($this->replace_template_placeholders($postInfo_title, $postData));

        $resolutions = $postData['resolutions'];
        $resolutions = explode('-', $resolutions);
        $resolutions = array_map('trim', $resolutions);
        usort($resolutions, function ($a, $b) {
            // Extract numbers and cast to integer
            $numA = $numB = 0; // Default values if no numbers are found
            if (preg_match('/(\d+)/', $a, $matchesA)) {
                $numA = (int) $matchesA[1];
            }
            if (preg_match('/(\d+)/', $b, $matchesB)) {
                $numB = (int) $matchesB[1];
            }

            // Compare numerical values
            return $numA <=> $numB;
        });

        $resolutions = implode(' || ', $resolutions);
        $sizes = "";  // Initialize the string to store sizes
        $last_index = count(array_filter($meta_data['fp_sizes'], function ($value) {
            return $value !== null && $value !== '';
        })) - 1;
        $current_index = 0;

        foreach ($meta_data['fp_sizes'] as $size) :
            if ($size) :
                $sizes .= $size;
                if ($current_index < $last_index) :
                    $sizes .= "  ||  ";
                endif;
                $current_index++;
            endif;
        endforeach;



?>

        <div class="fp__post_info_wrapper">
            <!-- <div style="width: 100%;"> -->
            <h2 class="fp__post_info_heading"><?php echo esc_html($postInfo_title) ?></h2>
            <ul class="fp__post_info_grid">
            <?php if (!empty($meta_data['fp_title'])): ?>
                <li class="fp__post_info_li"><span class='fp__post_info_li_name'>Name</span><span class="fp__post_info_colon">:</span><span class="fp__post_info_span">
                    <?php echo esc_html($meta_data['fp_title']) ?>
                    <!-- release_year -->
                    <?php if (!empty($meta_data['fp_oldest_year'])) : ?>
                        <span class="fp__post_info_span__year"> (<?php echo esc_html($meta_data['fp_oldest_year']) ?>)</span>
                    <?php endif; ?>
                </span></li>
            <?php endif; ?>
            <?php if (!empty($meta_data['fp_post_type_upper'])): ?>
                <li class="fp__post_info_li"><span class='fp__post_info_li_name'>Type</span><span class="fp__post_info_colon">:</span><span class="fp__post_info_span"><?php echo esc_html($meta_data['fp_post_type_upper']) ?></span></li>
            <?php endif; ?>
            <?php if (!empty($meta_data['fp_years'])): ?>
                <li class="fp__post_info_li"><span class='fp__post_info_li_name'>Year</span><span class="fp__post_info_colon">:</span><span class="fp__post_info_span"><?php echo esc_html($meta_data['fp_years']) ?></span></li>
            <?php endif; ?>
            <?php if (!empty($meta_data['fp_network'])): ?>
                <li class="fp__post_info_li"><span class='fp__post_info_li_name'>Network</span><span class="fp__post_info_colon">:</span><span class="fp__post_info_span"><?php echo esc_html($meta_data['fp_network']) ?></span></li>
            <?php endif; ?>
            <?php if (!empty($meta_data['fp_default_language'])): ?>
                <li class="fp__post_info_li"><span class='fp__post_info_li_name'> Audio</span><span class="fp__post_info_colon">:</span><span class="fp__post_info_span"><?php echo esc_html($meta_data['fp_default_language']) ?> <span style="color: #32bb00; font-weight: 500;"><?php echo esc_html($meta_data['fp_language_extend']) ?></span></span> </li>
            <?php endif; ?>
            <?php if (!empty($meta_data['fp_subtitles_count_customValue_2'])): ?>
                <li class="fp__post_info_li"><span class='fp__post_info_li_name'> Subtitle</span><span class="fp__post_info_colon">:</span><span><?php echo esc_html($meta_data['fp_subtitles_count_customValue_2']) ?></span> </li>
            <?php endif; ?>
            <?php if (!empty($sizes)): ?>
                <li class="fp__post_info_li"> <span class='fp__post_info_li_name'>Size</span> <span class="fp__post_info_colon">:</span> <span class="fp__post_info_span"> <span class="fp__post_info_span__size"><?php echo esc_html($sizes) ?> </span> </span> </li>
            <?php endif; ?>
            <?php if (!empty($resolutions)): ?>
                <li class="fp__post_info_li"><span class='fp__post_info_li_name'>Quality</span><span class="fp__post_info_colon">:</span><span class="fp__post_info_span"><?php echo esc_html($resolutions) ?>
                <?php if (!empty($meta_data['fp_quality'])) : ?>
                <span class="fp__post_info_span__quality"><?php echo esc_html("  -->  " . $meta_data['fp_quality']) ?></span>
                <?php endif; ?>
            </span></li>
            <?php endif; ?>
                <li class="fp__post_info_li"><span class='fp__post_info_li_name'>Format</span><span class="fp__post_info_colon">:</span><span class="fp__post_info_span">MKV</span></li>
                <?php if ($meta_data['fp_imdb']) : ?>
                    <li class="fp__post_info_li fp__post_info_li_imdb">
                        <div class="fp__post_info_li_imdb_link"><a href="<?php esc_url(FP_MOVIES_IMDB_BASE_URL . '/' . $meta_data['fp_imdb']); ?>" target="_blank">
                            <span style="display: inline-flex; align-items: center; justify-content: center;">
                                <!-- <i class="fab fa-imdb fp_info_imdb_icon"></i> -->
                                 <!-- USE LOCAL SVG IMAGE -->
                                  <img class="fp_info_imdb_icon imdb_icon_pc" src="<?php echo esc_html(esc_url(FP_MOVIES_URL) . 'img/imdb_light.svg') ?>" alt="IMDb" width="25" height="auto">

                                  <img class="fp_info_imdb_icon imdb_icon_mobile" src="<?php echo esc_html(esc_url(FP_MOVIES_URL) . 'img/imdb_dark.svg') ?>" alt="IMDb" width="30" height="auto">
                            </span><span class="fp_info_imdb_link">IMDb Rating:- <?php echo esc_html($meta_data['fp_vote_average']) ?>/10</span></a></div>
                    </li>
                <?php endif; ?>
            </ul>
            <!-- </div> -->
        </div>

<?php
    }
}
new FP_PostInfo();
?>
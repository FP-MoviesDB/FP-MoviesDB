<?php

if (!defined('ABSPATH')) exit;



function theme_template_settings()
{

    if (!current_user_can('manage_options')) {
        wp_die('You do not have sufficient permissions to access this page.');
    }
?>
    <h2>Single Post Page Shortcodes Template Settings</h2>
    <form method="post" action="options.php">
        <?php
        settings_errors();
        settings_fields('mts_generator_template_settings');
        do_settings_sections('mts_generator_template_settings');
        wp_nonce_field('mts_generator_settings_action', 'mts_generator_settings_nonce');
        $text_settings_old = [
            'mtg_template_post_title' => ['label' => 'Post Main Title', 'placeholder' => '{title} ({l_year}) {p_type}'],
            'mtg_template_post_title_separator' => ['label' => 'Title Taxonomy Separator', 'placeholder' => '-'],
            'mtg_template_post_info_title_shortcodes' => ['label' => 'PostInfo Title', 'placeholder' => '{p_type} Info:'],
            'mtg_template_synopsis_title' => ['label' => 'Synopsis/Storyline Title', 'placeholder' => 'SYNOPSIS/PLOT:'],
            'mtg_template_post_screenshot_title_shortcodes' => ['label' => 'Screenshot Title', 'placeholder' => '{title} {p_type} Screenshots:'],
            'mtg_template_single_screenshot_limit' => ['label' => 'Single Screenshot Limit', 'placeholder' => '5 (Max 10)', 'type' => 'number'],
            'mtg_template_splash_screenshot_limit' => ['label' => 'Splash Screenshot Limit', 'placeholder' => '3 (Max 5)', 'type' => 'number'],
            'mtg_template_movies_links_title' => ['label' => 'Movies Links Title', 'placeholder' => '{title} {p_type} Links:'],
            'mtg_template_series_links_title' => ['label' => 'Series Links Title', 'placeholder' => '{title} {p_type} Links:'],
            'mtg_template_download_baseURL' => ['label' => 'Download Base URL', 'placeholder' => 'https://fpgo.xyz || https://filepress.skin'],
            'mtg_template_player_fallback_image_url' => ['label' => 'Player Fallback Image URL', 'placeholder' => 'img/image-not-found.webp']
        ];
        $text_settings = [
            'sTitle_Title' => ['label' => 'Post Main Title', 'placeholder' => '{title} ({l_year}) {p_type}'],
            'sTitle_Separator' => ['label' => 'Title Taxonomy Separator', 'placeholder' => '-'],
            'sInfo_Title' => ['label' => 'PostInfo Title', 'placeholder' => '{p_type} Info:'],
            'sSynopsis_Title' => ['label' => 'Synopsis/Storyline Title', 'placeholder' => 'SYNOPSIS/PLOT:'],
            'sScreenshot_Title' => ['label' => 'Screenshot Title', 'placeholder' => '{title} {p_type} Screenshots:'],
            'sSingle_Screenshot_Limit' => ['label' => 'Single Screenshot Limit', 'placeholder' => '5 (Max 10)'],
            'sSplash_Screenshot_Limit' => ['label' => 'Splash Screenshot Limit', 'placeholder' => '3 (Max 5)'],
            'sLinks_Movies_Title' => ['label' => 'Movies Links Title', 'placeholder' => '{title} {p_type} Links:'],
            'sLinks_Series_Title' => ['label' => 'Series Links Title', 'placeholder' => '{title} {p_type} Links:'],
            'sDownload_BaseURL' => ['label' => 'Download Base URL', 'placeholder' => 'https://fpgo.xyz || https://filepress.skin'],
            'sPlayer_Fallback_Image_URL' => ['label' => 'Player Fallback Image URL', 'placeholder' => 'img/image-not-found.webp']
        ];
        $defaults_template_options = array_reduce(array_keys($text_settings), function ($carry, $item) use ($text_settings) {
            $carry[$item] = '';
            return $carry;
        }, [
            'enable_shortcode_cache' => '0',
            'sPlayer_Backdrop_Quality' => 'original'
        ]);

        // error_log("DEFAULTS: " . print_r($defaults_template_options, true));

        // $template_settings = get_option('mtg_template_settings', []);
        // $template_settings = wp_parse_args($template_settings, array_column($text_settings, 'default', array_keys($text_settings)));

        $template_settings = get_option('mtg_template_settings', []);
        // error_log("TEMPLATE SETTINGS: " . print_r($template_settings, true));
        $template_settings = wp_parse_args($template_settings, $defaults_template_options);

        // $template_settings = wp_parse_args($template_settings, [
        //     'enable_shortcode_cache' => 0,
        //     'sTitle_Title' => '',
        //     'sTitle_Separator' => '',
        //     'sInfo_Title' => '',
        //     'sSynopsis_Title' => '',
        //     'sScreenshot_Title' => '',
        //     'sSingle_Screenshot_Limit' => '',
        //     'sSplash_Screenshot_Limit' => '',
        //     'sLinks_Movies_Title' => '',
        //     'sLinks_Series_Title' => '',
        //     'sDownload_BaseURL' => '',
        //     'sPlayer_Fallback_Image_URL' => ''
        // ]);





        $color_settings = [
            'post_title_wrapper_color' => ['label' => 'Post Title Wrapper', 'description' => 'post title background color.', 'default' => '#000000'],
            'post_title_color' => ['label' => 'Post Title', 'description' => 'post main title.', 'default' => '#ffffff'],

            'synopsis_wrapper_bg_color' => ['label' => 'Synopsis Wrapper Background', 'description' => 'background color for the synopsis wrapper.', 'default' => '#000000'],
            'synopsis_heading_color' => ['label' => 'Synopsis Heading', 'description' => 'heading color for the synopsis.', 'default' => '#ffffff'],
            'synopsis_content_color' => ['label' => 'Synopsis Content', 'description' => 'content color for the synopsis.', 'default' => '#ffffff'],

            'screenshot_gallery_bg_color' => ['label' => 'Screenshots Background', 'description' => 'background color for the screenshot gallery.', 'default' => '#000000'],
            'screenshot_gallery_heading_color' => ['label' => 'Screenshot Heading', 'description' => 'heading color for the screenshot gallery.', 'default' => '#ffffff'],

            'imdb_wrapper_bg_color' => ['label' => 'IMDB Wrapper Background', 'description' => 'background color for the IMDB wrapper.', 'default' => '#000000'],
            'imdb_box_bg_color' => ['label' => 'IMDB Box Background', 'description' => 'background color for the IMDB box.', 'default' => '#222'],
            'imdb_title_color' => ['label' => 'IMDB Title', 'description' => 'color for the IMDB title.', 'default' => '#ffffff'],
            'imdb_title_year_color' => ['label' => 'IMDB Title [Year]', 'description' => 'color for the IMDB title year.', 'default' => '#ffffff'],
            'imdb_meta_key_color' => ['label' => 'IMDB Genre Option Name', 'description' => 'color for the IMDB items [All_KEYs].', 'default' => '#ffffff'],
            'imdb_genre_color' => ['label' => 'IMDB Genre value', 'description' => 'color for the IMDB genre.', 'default' => '#8CC411'],
            'imdb_audio_color' => ['label' => 'IMDB Audio value', 'description' => 'color for the IMDB audio.', 'default' => '#ec39d9'],
            'imdb_network_color' => ['label' => 'IMDB Network value', 'description' => 'color for the IMDB network.', 'default' => 'cornflowerblue'],
            'imdb_rating_color' => ['label' => 'IMDB Rating', 'description' => 'color for the IMDB rating.', 'default' => '#ffffff'],
            'imdb_teaser_color' => ['label' => 'IMDB Teaser', 'description' => 'color for the IMDB teaser.', 'default' => '#ffffff'],

            'post_info_wrapper_bg_color' => ['label' => 'Post Info Wrapper Background', 'description' => 'background color for the post info wrapper.', 'default' => '#000000'],
            'post_info_heading_color' => ['label' => 'Post Info Heading', 'description' => 'heading color for the post info.', 'default' => '#ffffff'],
            'post_info_li_color' => ['label' => 'Post Info List', 'description' => 'color for the post info list.', 'default' => '#ffffff'],
            'post_info_li_span_color' => ['label' => 'Post Info List Span', 'description' => 'color for the post info list span.', 'default' => '#ffffff'],

            // mpl: movie-post-links
            'wrapper_bg_color' => ['label' => 'Links Wrapper Background', 'description' => 'background color for the post links wrapper.', 'default' => '#000000'],
            'heading_color' => ['label' => 'Links Heading', 'description' => 'color for the download links heading.', 'default' => '#ffffff'],

            'single_item_size' => ['label' => 'Links Meta Item Size', 'description' => 'font size for the download links single item.', 'default' => '#ff8f00'],
            'single_item_quality' => ['label' => 'Links Meta Item Quality', 'description' => 'color for the download links single item quality.', 'default' => '#0c97c2'],
            'single_item_audio' => ['label' => 'Links Meta Item Audio', 'description' => 'color for the download links single item audio.', 'default' => '#9b0a6a'],

            'movie_single_item_color' => ['label' => 'Links Movie Single Item', 'description' => 'color for the download links single item.', 'default' => '#ffffff'],
            'movie_single_item_hover_color' => ['label' => 'Links Movie Single Item Hover', 'description' => 'color for the download links single item hover.', 'default' => '#808080'],
            'movie_single_item_bg_color' => ['label' => 'Links Movie Single Item Background', 'description' => 'background color for the download links single item.', 'default' => '#242222'],

            'tv_season_bg_color' => ['label' => 'Post Links TV Season Background', 'description' => 'background color for the download links tv season.', 'default' => '#004dbb'],
            'tv_season_bg_color_hover' => ['label' => 'Post Links TV Season Background Hover', 'description' => 'background color for the download links tv season hover.', 'default' => '#03268e'],
            'tv_season_color' => ['label' => 'Post Links TV Season', 'description' => 'text color for the tv "seasons".', 'default' => '#ffffff'],

            'tv_quality_bg_color' => ['label' => 'Post Links TV Quality Background', 'description' => 'background color for the download links tv quality.', 'default' => '#059862'],
            'tv_quality_bg_color_hover' => ['label' => 'Post Links TV Quality Background Hover', 'description' => 'background color for the download links tv quality hover.', 'default' => '#f4e32c'],
            'tv_quality_bg_color_hover_color' => ['label' => 'Post Links TV Quality Background Hover Color', 'description' => 'text color for the download links tv quality hover.', 'default' => '#000000'],
            'tv_quality_color' => ['label' => 'Post Links TV Quality', 'description' => 'text color for the tv "quality".', 'default' => '#ffffff'],


            'tv_ep_pack_item_bg_color' => ['label' => 'Post Links TV Episode Background', 'description' => 'background color for the download links tv episode.', 'default' => '#282727'],
            'tv_ep_pack_item_bg_color_hover' => ['label' => 'Post Links TV Episode Background Hover', 'description' => 'background color for the download links tv episode hover.', 'default' => '#3e3838'],


            'tv_episode_color' => ['label' => 'Post Links TV Episode', 'description' => 'text color for the tv "episode".', 'default' => '#ffffff'],
            'tv_episode_meta_color' => ['label' => 'Post Links TV Episode Meta', 'description' => 'text color for the tv "episode meta".', 'default' => '#708090'],
            'tv_episode_packs_single_season_color' => ['label' => 'Post Links TV Episode Packs Single Season', 'description' => 'text color for the tv "episode packs single season".', 'default' => '#d2691e'],

        ];
        ?> <!-- CLOSED ABOVE PHP -->

        <table class="form-table" style="max-width: 80%;">
            <div class="mtg_submit_btn" style="text-align: center;">
                <?php submit_button(); ?>
            </div>

            <!-- Enable Shortcode Cache -->
            <tr valign="top">
                <th scope="row">Enable Shortcode Cache</th>
                <td>
                    <input type="checkbox" name="mtg_template_settings[enable_shortcode_cache]" value="1" <?php checked(isset($template_settings['enable_shortcode_cache']) ? $template_settings['enable_shortcode_cache'] : 0, 1); ?> />
                    <!-- <input type="checkbox" name="mtg_template_enable_shortcode_cache" value="1" <?php // checked(get_option('mtg_template_enable_shortcode_cache'), 1); 
                                                                                                        ?> /> -->
                    <p class="description">Enable Shortcode Cache.</p>
                </td>
            </tr>

            <!-- TEXT SETTINGS -->
            <?php foreach ($text_settings as $name => $data) :
                $option_value = isset($template_settings[$name]) ? $template_settings[$name] : '';
                // if type is available then use it else use text
                $type = $data['type'] ?? 'text';
            ?>
                <tr valign="top">
                    <th scope="row"><?php echo esc_html($data['label']); ?></th>
                    <td>
                        
                    <!-- <input type="text" name="<?php // echo esc_attr($name); ?>" value="<?php // echo esc_attr(get_option($name)); ?>" class="regular-text" placeholder="<?php // echo esc_attr($data['placeholder']); ?>" /> -->
                        
                        <input type="<?php echo esc_attr($type); ?>" name="mtg_template_settings[<?php echo esc_attr($name); ?>]" value="<?php echo esc_attr($option_value); ?>" class="regular-text" placeholder="<?php echo esc_attr($data['placeholder']); ?>" />

                    </td>
                </tr>
            <?php endforeach; ?>

            <!-- Selector for mtg_template_player_backdrop_quality -->
            <tr valign="top">
                <th scope="row">Player Backdrop Quality</th>
                <td>
                    <select name="mtg_template_player_backdrop_quality">
                        <option value="original" <?php selected($template_settings['sPlayer_Backdrop_Quality'], 'original'); ?>>Original</option>
                        <option value="w300" <?php selected($template_settings['sPlayer_Backdrop_Quality'], 'w300'); ?>>Low</option>
                        <option value="w780" <?php selected($template_settings['sPlayer_Backdrop_Quality'], 'w780'); ?>>Medium</option>
                        <option value="w1280" <?php selected($template_settings['sPlayer_Backdrop_Quality'], 'w1280'); ?>>High</option>
                    </select>
                </td>
            </tr>

            <!-- COLORS SETTINGS -->
            <?php
            $stored_color_settings = get_option('mtg_color_settings', []);
            foreach ($color_settings as $name => $data) :
                $default_color = $data['default'] ?? '#24A1DE';
                $option_value = $stored_color_settings[$name] ?? $default_color;
            ?>
                <tr valign="top">
                    <th scope="row"><?php echo esc_html($data['label']); ?></th>
                    <td>
                        <input type="text" name="mtg_color_settings[<?php echo esc_attr($name); ?>]" value="<?php echo esc_attr($option_value); ?>" class="mtg-color-field" data-default-color="<?php echo esc_attr($default_color); ?>" />
                        <p class="description"><?php echo esc_html($data['description']); ?></p>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>

        <div class="mtg_submit_btn" style="text-align: center;">
            <?php submit_button(); ?>
        </div>



        <div class="mtg_settings" style="text-align: left;">
            <div class="customizations-grid">
                <div class="grid-title">Available customizations: =></div>

                <div class="grid-row grid-row-bold">
                    <div class="grid-item">Usage</div>
                    <div class="grid-item">-</div>
                    <div class="grid-item">Information</div>
                    <div class="grid-item">Example</div>
                </div>

                <div class="grid-row">
                    <div class="grid-item">{p_type}</div>
                    <div class="grid-item">-</div>
                    <div class="grid-item">PostType</div>
                    <div class="grid-item">Movie</div>
                </div>

                <div class="grid-row">
                    <div class="grid-item">{title}</div>
                    <div class="grid-item">-</div>
                    <div class="grid-item">PostTitle</div>
                    <div class="grid-item">MovieName...</div>
                </div>

                <div class="grid-row">
                    <div class="grid-item">{t_title}</div>
                    <div class="grid-item">-</div>
                    <div class="grid-item">TmdbTitle</div>
                    <div class="grid-item">MovieName</div>
                </div>

                <div class="grid-row">
                    <div class="grid-item">{r_year}</div>
                    <div class="grid-item">-</div>
                    <div class="grid-item">Release Year</div>
                    <div class="grid-item">1970</div>
                </div>

                <div class="grid-row">
                    <div class="grid-item">{l_year}</div>
                    <div class="grid-item">-</div>
                    <div class="grid-item">Latest Release Year</div>
                    <div class="grid-item">2024</div>
                </div>

                <div class="grid-row">
                    <div class="grid-item">{quality}</div>
                    <div class="grid-item">-</div>
                    <div class="grid-item">Quality</div>
                    <div class="grid-item">HD</div>
                </div>

                <div class="grid-row">
                    <div class="grid-item">{audio}</div>
                    <div class="grid-item">-</div>
                    <div class="grid-item">Audio</div>
                    <div class="grid-item">English-Hindi-...</div>
                </div>

                <div class="grid-row">
                    <div class="grid-item">{c_audio}</div>
                    <div class="grid-item">-</div>
                    <div class="grid-item">Audio [c]</div>
                    <div class="grid-item">Dual/Multi Audio [only if Audio > 1]</div>
                </div>

                <div class="grid-row">
                    <div class="grid-item">{c_subs}</div>
                    <div class="grid-item">-</div>
                    <div class="grid-item">Subs [c]</div>
                    <div class="grid-item">ESubs/MSubs [if contain 1 subs then ESubs else MSubs]</div>
                </div>

                <div class="grid-row">
                    <div class="grid-item">{separator}</div>
                    <div class="grid-item">-</div>
                    <div class="grid-item">Separator</div>
                    <div class="grid-item">-</div>
                </div>

            </div>
            <div class="shortcode-usage">
                <div>
                    <h3>Shortcodes Usage</h3><span class="shortcode-usage-head-sub">[Single Type Page Only]:</span>
                </div>
                <div class="shortcode-usage-content">
                    <p style="margin: 0px; font-weight: 600;"><span class="text-bold">Method 1: </span>Use this PHP code in your theme single.php or its referenced file:</p>
                    <pre class="text-wrap text-mobile-center" style="margin-top: 2px;">&lt;?php echo do_shortcode( '[replace_shortcodes]'); ?&gt;
                    <p style="margin: 0px;">If you already inside <span style='font-weight: 600;'>&lt;?php</span> block then just use 'echo do_shortcode( '[replace_shortcodes]');' directly.</p><img src="<?php echo esc_url(esc_url(FP_MOVIES_URL) . 'img/method_1_demo.png'); ?>" alt="shortcode-usage" style="width: 500px; margin-top: 1px;"></pre>

                </div>
                <div class="shortcode-usage-content">
                    <p style="margin: 0px; font-weight: 600;"><span class="text-bold">Method 2: </span>Use Shortcode in theme [Gutenberg]: Editor -> Template -> Single Post
                        </br>Add as Shortcode [don't forget brackets]</br>
                    <pre class="text-wrap text-mobile-center" style="margin-top: 2px;">[replace_shortcodes]</br><img src="<?php echo esc_url(esc_url(FP_MOVIES_URL) . 'img/method_2_demo.png'); ?>" alt="shortcode-usage" style="width: 500px; margin-top: 1px;"></pre>
                </div>
            </div>



            <div class="mtg_settings" style="text-align: left;">
                <div class="template-settings-grid">
                    <div class="grid-title">Available Shortcodes</div>

                    <div class="grid-row">
                        <div class="grid-item-template-settings text-semibold">Video Player</div>
                        <div class="grid-item-template-settings"></div>
                        <div class="grid-item-template-settings">
                            <pre class="fp-no-margin text-wrap">[fp-post-player]</pre>
                        </div>
                    </div>

                    <div class="grid-row">
                        <div class="grid-item-template-settings text-semibold">Post Title</div>
                        <div class="grid-item-template-settings"></div>
                        <div class="grid-item-template-settings">
                            <pre class="fp-no-margin text-wrap">[fp-post-title]</pre>
                        </div>
                    </div>

                    <div class="grid-row">
                        <div class="grid-item-template-settings text-semibold">iMDB BOX</div>
                        <div class="grid-item-template-settings"></div>
                        <div class="grid-item-template-settings">
                            <pre class="fp-no-margin text-wrap">[fp-imdb-box-view]</pre>
                        </div>
                    </div>

                    <div class="grid-row">
                        <div class="grid-item-template-settings text-semibold">Synopsis/Storyline</div>
                        <div class="grid-item-template-settings"></div>
                        <div class="grid-item-template-settings">
                            <pre class="fp-no-margin text-wrap">[fp-synopsis-view]</pre>
                        </div>
                    </div>

                    <div class="grid-row">
                        <div class="grid-item-template-settings text-semibold">Post Info:</div>
                        <div class="grid-item-template-settings"></div>
                        <div class="grid-item-template-settings">
                            <pre class="fp-no-margin text-wrap">[fp-post-info]</pre>
                        </div>
                    </div>

                    <div class="grid-row">
                        <div class="grid-item-template-settings text-semibold">Screenshots:</div>
                        <div class="grid-item-template-settings"></div>
                        <div class="grid-item-template-settings">
                            <pre class="fp-no-margin text-wrap">[fp-screenshot-view]</pre>
                        </div>
                    </div>

                    <div class="grid-row">
                        <div class="grid-item-template-settings text-semibold">Download Links:</div>
                        <div class="grid-item-template-settings"></div>
                        <div class="grid-item-template-settings">
                            <pre class="fp-no-margin text-wrap">[fp-post-links]</pre>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </form>
<?php
}



        /*
            $color_settings = [
                'mtg_post_title_wrapper_color' => ['label' => 'Post Title Wrapper', 'description' => 'post title background color.', 'default' => '#000000'],
                'mtg_post_title_color' => ['label' => 'Post Title', 'description' => 'post main title.', 'default' => '#ffffff'],
                
                'mtg_synopsis_wrapper_bg_color' => ['label' => 'Synopsis Wrapper Background', 'description' => 'background color for the synopsis wrapper.', 'default' => '#000000'],
                'mtg_synopsis_heading_color' => ['label' => 'Synopsis Heading', 'description' => 'heading color for the synopsis.', 'default' => '#ffffff'],
                'mtg_synopsis_content_color' => ['label' => 'Synopsis Content', 'description' => 'content color for the synopsis.', 'default' => '#ffffff'],
                
                'mtg_screenshot_gallery_bg_color' => ['label' => 'Screenshots Background', 'description' => 'background color for the screenshot gallery.', 'default' => '#000000'],
                'mtg_screenshot_gallery_heading_color' => ['label' => 'Screenshot Heading', 'description' => 'heading color for the screenshot gallery.', 'default' => '#ffffff'],
                
                'mtg_imdb_wrapper_bg_color' => ['label' => 'IMDB Wrapper Background', 'description' => 'background color for the IMDB wrapper.', 'default' => '#000000'],
                'mtg_imdb_box_bg_color' => ['label' => 'IMDB Box Background', 'description' => 'background color for the IMDB box.', 'default' => '#222'],
                'mtg_imdb_title_color' => ['label' => 'IMDB Title', 'description' => 'color for the IMDB title.', 'default' => '#ffffff'],
                'mtg_imdb_title_year_color' => ['label' => 'IMDB Title [Year]', 'description' => 'color for the IMDB title year.', 'default' => '#ffffff'],
                'mtg_imdb_meta_key_color' => ['label' => 'IMDB Genre Option Name', 'description' => 'color for the IMDB items [All_KEYs].', 'default' => '#ffffff'],
                'mtg_imdb_genre_color' => ['label' => 'IMDB Genre value', 'description' => 'color for the IMDB genre.', 'default' => '#8CC411'],
                'mtg_imdb_audio_color' => ['label' => 'IMDB Audio value', 'description' => 'color for the IMDB audio.', 'default' => '#ec39d9'],
                'mtg_imdb_network_color' => ['label' => 'IMDB Network value', 'description' => 'color for the IMDB network.', 'default' => 'cornflowerblue'],
                'mtg_imdb_rating_color' => ['label' => 'IMDB Rating', 'description' => 'color for the IMDB rating.', 'default' => '#ffffff'],
                'mtg_imdb_teaser_color' => ['label' => 'IMDB Teaser', 'description' => 'color for the IMDB teaser.', 'default' => '#ffffff'],
                
                'mtg_post_info_wrapper_bg_color' => ['label' => 'Post Info Wrapper Background', 'description' => 'background color for the post info wrapper.', 'default' => '#000000'],
                'mtg_post_info_heading_color' => ['label' => 'Post Info Heading', 'description' => 'heading color for the post info.', 'default' => '#ffffff'],
                'mtg_post_info_li_color' => ['label' => 'Post Info List', 'description' => 'color for the post info list.', 'default' => '#ffffff'],
                'mtg_post_info_li_span_color' => ['label' => 'Post Info List Span', 'description' => 'color for the post info list span.', 'default' => '#ffffff'],
                
                // mpl: movie-post-links
                'mpl_wrapper_bg_color' => ['label' => 'Links Wrapper Background', 'description' => 'background color for the post links wrapper.', 'default' => '#000000'],
                'mpl_heading_color' => ['label' => 'Links Heading', 'description' => 'color for the download links heading.', 'default' => '#ffffff'],
                
                'mpl_single_item_size' => ['label' => 'Links Meta Item Size', 'description' => 'font size for the download links single item.', 'default' => '#ff8f00'],
                'mpl_single_item_quality' => ['label' => 'Links Meta Item Quality', 'description' => 'color for the download links single item quality.', 'default' => '#0c97c2'],
                'mpl_single_item_audio' => ['label' => 'Links Meta Item Audio', 'description' => 'color for the download links single item audio.', 'default' => '#9b0a6a'],
                
                'mpl_movie_single_item_color' => ['label' => 'Links Movie Single Item', 'description' => 'color for the download links single item.', 'default' => '#ffffff'],
                'mpl_movie_single_item_hover_color' => ['label' => 'Links Movie Single Item Hover', 'description' => 'color for the download links single item hover.', 'default' => '#808080'],
                'mpl_movie_single_item_bg_color' => ['label' => 'Links Movie Single Item Background', 'description' => 'background color for the download links single item.', 'default' => '#242222'],
                
                'mpl_tv_season_bg_color' => ['label' => 'Post Links TV Season Background', 'description' => 'background color for the download links tv season.', 'default' => '#004dbb'],
                'mpl_tv_season_bg_color_hover' => ['label' => 'Post Links TV Season Background Hover', 'description' => 'background color for the download links tv season hover.', 'default' => '#03268e'],
                'mpl_tv_season_color' => ['label' => 'Post Links TV Season', 'description' => 'text color for the tv "seasons".', 'default' => '#ffffff'],
                
                'mpl_tv_quality_bg_color' => ['label' => 'Post Links TV Quality Background', 'description' => 'background color for the download links tv quality.', 'default' => '#059862'],
                'mpl_tv_quality_bg_color_hover' => ['label' => 'Post Links TV Quality Background Hover', 'description' => 'background color for the download links tv quality hover.', 'default' => '#f4e32c'],
                'mpl_tv_quality_bg_color_hover_color' => ['label' => 'Post Links TV Quality Background Hover Color', 'description' => 'text color for the download links tv quality hover.', 'default' => '#000000'],
                'mpl_tv_quality_color' => ['label' => 'Post Links TV Quality', 'description' => 'text color for the tv "quality".', 'default' => '#ffffff'],
                
                
                'mpl_tv_ep_pack_item_bg_color' => ['label' => 'Post Links TV Episode Background', 'description' => 'background color for the download links tv episode.', 'default' => '#282727'],
                'mpl_tv_ep_pack_item_bg_color_hover' => ['label' => 'Post Links TV Episode Background Hover', 'description' => 'background color for the download links tv episode hover.', 'default' => '#3e3838'],
                
                
                'mpl_tv_episode_color' => ['label' => 'Post Links TV Episode', 'description' => 'text color for the tv "episode".', 'default' => '#ffffff'],
                'mpl_tv_episode_meta_color' => ['label' => 'Post Links TV Episode Meta', 'description' => 'text color for the tv "episode meta".', 'default' => '#708090'],
                'mpl_tv_episode_packs_single_season_color' => ['label' => 'Post Links TV Episode Packs Single Season', 'description' => 'text color for the tv "episode packs single season".', 'default' => '#d2691e'],

            ];
        */
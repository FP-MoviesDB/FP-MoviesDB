<?php

if (!defined('ABSPATH')) exit;

function mts_predefined_shortcodes()
{
    global $fp_min_m;
    wp_enqueue_style('fp-pre-defined-shortcodes', esc_url(FP_MOVIES_URL) . 'css/fp_pds_' . $fp_min_m . '.css', array(), FP_MOVIES_FILES, 'all');
?>
    <div class="fp_pre_defined_main_wrapper">
        <div class="pds_section">

            <div class="pds_section_title">Direct Usage</div>
            <div class="pds_entry-content">
                <div class="pds_shortcode_wrapper">


                    <div class="pds_entry">
                        <div class="pds_shortcode_title">Implementation:</div>
                        <div class="pds_shortcode_desc">
                            <div class="pds_shortcode_desc_items pds_direct">
                                <div>1. Assuming you are using Twenty Twenty-Four theme.</div>
                                <div>2. Go to Appearance > Editor.</div>
                                <div>3. Click on Templates on left side.</div>
                                <div>4. Select Your Theme > "Blog Home".</div>
                                <div>5. Click on any displaying blocks.</div>
                                <div>6. Click on 3 Dots and select Add before/after.</div>
                                <div>7. Either click on + icon or search for Shortcode by typing "/".</div>
                                <div>8. Type the shortcode and click on Save.</div>
                                <div>9. Done!</div>
                                <div>10. For "Single Post" search Single Post Template instead of "Blog Home".</div>
                                <!-- https://t.me/FP_MoviesDB -->
                                <div>11. For more help, message us at Telegram <a href="https://t.me/FP_MoviesDB" target="_blank">FP MoviesDB</a></div>
                            </div>
                        </div>


                    </div>

                    <div class="pds_entry">
                        <div class="pds_shortcode_title">HomePage</div>
                        <div class="pds_shortcode_desc">
                            <div class="pds_shortcode_desc_items pds_direct">
                                <div>[fp-homepage-view type='featured' content_type='both' title_background="normal"]</div>
                                <div>[fp-homepage-view type='meta' content_type='movie' heading="Movies"]</div>
                                <div>[fp-homepage-view type='meta' content_type='series' heading="Movies"]</div>
                                <div>[fp-homepage-view type='taxonomy' taxonomy='mtg_genre' content_type='action' heading="Action"]</div>
                                <div>[fp-homepage-view type='taxonomy' taxonomy='mtg_audio' content_type='english' heading="English"]</div>
                            </div>
                        </div>
                    </div>

                    <div class="pds_entry">
                        <div class="pds_shortcode_title">Single Page</div>
                        <div class="pds_shortcode_desc">
                            <div class="pds_shortcode_desc_items pds_direct">
                                <div>[fp-post-player]</div>
                                <div>[fp-post-title]</div>
                                <div>[fp-imdb-box-view]</div>
                                <div>[fp-synopsis-view]</div>
                                <div>[fp-post-info]</div>
                                <div>[fp-screenshot-view]</div>
                                <div>[fp-post-links]</div>
                            </div>
                        </div>
                    </div>



                    <div class="pds_entry">
                        <div class="pds_shortcode_title">Adding in PHP File ?</div>
                        <div class="pds_shortcode_desc">
                            <div class="pds_shortcode_desc_items pds_direct">
                                <div>1. Open your theme's PHP file.</div>
                                <div>2. Add Shortcode like below:</div>
                                <div><code>&lt;?php<br/>echo do_shortcode('[fp-homepage-view type="featured" content_type="both" title_background="normal"]'); <br/>?&gt;</code></div>
                                <div>Note: Don't use <b>&lt;?php</b> and <b>?&gt;</b> if you are already inside that.</div>
                                <div>4. Done!</div>
                            </div>
                        </div>
                    </div>

                </div>










                <div class="pds_section_title">SinglePost Shortcodes Usage Details</div>
                <div class="pds_entry-content">

                    <div class="pds_shortcode_wrapper">
                        <div class="pds_entry">
                            <div class="pds_shortcode_title">Name:</div>
                            <div class="pds_shortcode_desc">Player</div>
                        </div>
                        <div class="pds_entry">
                            <div class="pds_shortcode_title">Description:</div>
                            <div class="pds_shortcode_desc">This shortcode will display the player on the single post.</div>
                        </div>
                        <div class="pds_entry">
                            <div class="pds_shortcode_title">Usage:</div>
                            <div class="pds_shortcode_desc">[fp-post-player]</div>
                        </div>
                        <div class="pds_entry">
                            <div class="pds_shortcode_title">Screenshot:</div>
                            <div class="pds_shortcode_desc">
                                <div>
                                    <img src="<?php echo esc_url(FP_MOVIES_URL) . 'img/movie_player.webp'; ?>" alt="Single Post Player">
                                </div>
                                <div>
                                    <img src="<?php echo esc_url(FP_MOVIES_URL) . 'img/tv_player.webp'; ?>" alt="Single Post Player">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="pds_shortcode_wrapper">
                        <div class="pds_entry">
                            <div class="pds_shortcode_title">Name:</div>
                            <div class="pds_shortcode_desc">Title</div>
                        </div>
                        <div class="pds_entry">
                            <div class="pds_shortcode_title">Description:</div>
                            <div class="pds_shortcode_desc">This shortcode will display the title on the single post.</div>
                        </div>
                        <div class="pds_entry">
                            <div class="pds_shortcode_title">Usage:</div>
                            <div class="pds_shortcode_desc">[fp-post-title]</div>
                        </div>
                        <div class="pds_entry">
                            <div class="pds_shortcode_title">Screenshot:</div>
                            <div class="pds_shortcode_desc">
                                <div>
                                    <img src="<?php echo esc_url(FP_MOVIES_URL) . 'img/title.webp'; ?>" alt="Single Post Title">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="pds_shortcode_wrapper">
                        <div class="pds_entry">
                            <div class="pds_shortcode_title">Name:</div>
                            <div class="pds_shortcode_desc">IMDB BOX</div>
                        </div>
                        <div class="pds_entry">
                            <div class="pds_shortcode_title">Description:</div>
                            <div class="pds_shortcode_desc">This shortcode will display the IMDB box on the single post.</div>
                        </div>
                        <div class="pds_entry">
                            <div class="pds_shortcode_title">Usage:</div>
                            <div class="pds_shortcode_desc">[fp-imdb-box-view]</div>
                        </div>
                        <div class="pds_entry">
                            <div class="pds_shortcode_title">Screenshot:</div>
                            <div class="pds_shortcode_desc">
                                <div>
                                    <img src="<?php echo esc_url(FP_MOVIES_URL) . 'img/imdb_box.webp'; ?>" alt="IMDB Box">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="pds_shortcode_wrapper">
                        <div class="pds_entry">
                            <div class="pds_shortcode_title">Name:</div>
                            <div class="pds_shortcode_desc">Storyline/Plot/Content</div>
                        </div>
                        <div class="pds_entry">
                            <div class="pds_shortcode_title">Description:</div>
                            <div class="pds_shortcode_desc">This shortcode will display the synopsis on the single post.</div>
                        </div>
                        <div class="pds_entry">
                            <div class="pds_shortcode_title">Usage:</div>
                            <div class="pds_shortcode_desc">[fp-synopsis-view]</div>
                        </div>
                        <div class="pds_entry">
                            <div class="pds_shortcode_title">Screenshot:</div>
                            <div class="pds_shortcode_desc">
                                <div>
                                    <img src="<?php echo esc_url(FP_MOVIES_URL) . 'img/synopsis.webp'; ?>" alt="Synopsis">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="pds_shortcode_wrapper">
                        <div class="pds_entry">
                            <div class="pds_shortcode_title">Name:</div>
                            <div class="pds_shortcode_desc">Post Info</div>
                        </div>
                        <div class="pds_entry">
                            <div class="pds_shortcode_title">Description:</div>
                            <div class="pds_shortcode_desc">This shortcode will display the post info on the single post.</div>
                        </div>
                        <div class="pds_entry">
                            <div class="pds_shortcode_title">Usage:</div>
                            <div class="pds_shortcode_desc">[fp-post-info]</div>
                        </div>
                        <div class="pds_entry">
                            <div class="pds_shortcode_title">Screenshot:</div>
                            <div class="pds_shortcode_desc">
                                <div>
                                    <img src="<?php echo esc_url(FP_MOVIES_URL) . 'img/post_info.webp'; ?>" alt="Post Info">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="pds_shortcode_wrapper">
                        <div class="pds_entry">
                            <div class="pds_shortcode_title">Name:</div>
                            <div class="pds_shortcode_desc">Screenshot</div>
                        </div>
                        <div class="pds_entry">
                            <div class="pds_shortcode_title">Description:</div>
                            <div class="pds_shortcode_desc">This shortcode will display the screenshots on the single post.</div>
                        </div>
                        <div class="pds_entry">
                            <div class="pds_shortcode_title">Usage:</div>
                            <div class="pds_shortcode_desc">[fp-screenshot-view]</div>
                        </div>
                        <div class="pds_entry">
                            <div class="pds_shortcode_title">Screenshot:</div>
                            <div class="pds_shortcode_desc">
                                <div>
                                    <img src="<?php echo esc_url(FP_MOVIES_URL) . 'img/screenshots.webp'; ?>" alt="Screenshot">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="pds_shortcode_wrapper">
                        <div class="pds_entry">
                            <div class="pds_shortcode_title">Name:</div>
                            <div class="pds_shortcode_desc">Post Links</div>
                        </div>
                        <div class="pds_entry">
                            <div class="pds_shortcode_title">Description:</div>
                            <div class="pds_shortcode_desc">This shortcode will display the post links on the single post.</div>
                        </div>
                        <div class="pds_entry">
                            <div class="pds_shortcode_title">Usage:</div>
                            <div class="pds_shortcode_desc">[fp-post-links]</div>
                        </div>
                        <div class="pds_entry">
                            <div class="pds_shortcode_title">Screenshot:</div>
                            <div class="pds_shortcode_desc">
                                <div>
                                    <img src="<?php echo esc_url(FP_MOVIES_URL) . 'img/movie_links.webp'; ?>" alt="Single Post Player">
                                </div>
                                <div>
                                    <img src="<?php echo esc_url(FP_MOVIES_URL) . 'img/tv_links.webp'; ?>" alt="Single Post Player">
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="pds_section_title">HomePage Shortcodes Usage Details</div>
                <div class="pds_entry-content">
                    <div class="pds_shortcode_wrapper">
                        <div class="pds_entry">
                            <div class="pds_shortcode_title">Name:</div>
                            <div class="pds_shortcode_desc">[fp-homepage-view]</div>
                        </div>
                        <div class="pds_entry">
                            <div class="pds_shortcode_title">Description:</div>
                            <div class="pds_shortcode_desc">This shortcode will display the slider on the homepage.</div>
                        </div>
                        <div class="pds_entry">
                            <div class="pds_shortcode_title">Usage:</div>
                            <div class="pds_shortcode_desc"> [fp-homepage-view type='featured' content_type='both' heading="Watch Now" limit=10 title_background="gradient"] </div>
                        </div>

                        <div class="pds_entry">
                            <div class="pds_shortcode_title">Attributes</div>
                            <div class="pds_shortcode_desc">
                                <div class="pds_s_atts">
                                    <div class="pds_s_atts_flex"><span>type:</span><span>featured [FIXED]</span></div>
                                    <div class="pds_s_atts_flex"><span>content_type:</span><span>both/movie/series [Type of Posts]</span></div>
                                    <div class="pds_s_atts_flex"><span>heading:</span><span>Visit Now [Title for the Button]</span></div>
                                    <div class="pds_s_atts_flex"><span>limit:</span><span>10 [Number of Posts]</span></div>
                                    <div class="pds_s_atts_flex"><span>title_background:</span><span>gradient/normal [Button Background color]</span></div>
                                </div>
                            </div>
                        </div>

                        <div class="pds_entry">
                            <div class="pds_shortcode_title">Screenshot:</div>
                            <div class="pds_shortcode_desc"><img src="<?php echo esc_url(FP_MOVIES_URL) . 'img/homepage-slider.webp'; ?>" alt="Homepage Slider"></div>
                        </div>

                    </div>

                    <div class="pds_shortcode_wrapper">
                        <div class="pds_entry">
                            <div class="pds_shortcode_title">Name:</div>
                            <div class="pds_shortcode_desc">[mts_homepage_view]</div>
                        </div>
                        <div class="pds_entry">
                            <div class="pds_shortcode_title">Description:</div>
                            <div class="pds_shortcode_desc">This shortcode will display Movies/Series/Taxonomy posts on the homepage.</div>
                        </div>
                        <div class="pds_entry">
                            <div class="pds_shortcode_title">Usage:</div>
                            <div class="pds_shortcode_desc">
                                <div class="pds_shortcode_desc_items">
                                    <div>[fp-homepage-view type='meta' content_type='movie' heading="Movies" post_title="tmdb" limit=10 image_source="tmdb" image_size="medium" title_background="gradient" show_ratings="true" show_quality="true"]</div>
                                    <div>[fp-homepage-view type='meta' content_type='series' heading="Series" limit=5]</div>
                                    <div>[fp-homepage-view type='taxonomy' taxonomy='mtg_genre' content_type='action' heading="Action" limit=5]</div>
                                    <div>[fp-homepage-view type='taxonomy' taxonomy='mtg_year' content_type='2019,2020,2022' heading="Year: 2019-22" limit=10]</div>
                                </div>
                            </div>
                        </div>
                        <div class="pds_entry">
                            <div class="pds_shortcode_title">Attributes</div>
                            <div class="pds_shortcode_desc">
                                <div class="pds_s_atts">
                                    <div class="pds_s_atts_flex"><span>type:</span><span>meta/taxonomy [Type of Posts]</span></div>
                                    <div class="pds_s_atts_flex"><span>content_type:</span><span>movie/series [Type of Posts]</span></div>
                                    <div class="pds_s_atts_flex"><span>heading:</span><span>Block Title</span></div>
                                    <div class="pds_s_atts_flex"><span>post_title:</span><span>tmdb/original [each post title from TMDB (meta) or current post | Default original]</span></div>
                                    <div class="pds_s_atts_flex"><span>limit:</span><span>10 [Number of Posts] | Default 10</span></div>
                                    <div class="pds_s_atts_flex"><span>image_source:</span><span>tmdb/local [TMDB poster/Featured Image]</span></div>
                                    <div class="pds_s_atts_flex"><span>image_size:</span><span>small/medium/large/original [Image Size]</span></div>
                                    <div class="pds_s_atts_flex"><span>title_background:</span><span>gradient/normal [Title Background color | Default normal]</span></div>
                                    <div class="pds_s_atts_flex"><span>taxonomy:</span><span>mtg_network/mtg_quality/mtg_resolution/mtg_genre/mtg_year [Make sure you have activated the taxonomy]</span></div>
                                    <div class="pds_s_atts_flex"><span>content_type:</span><span>action/comedy/netflix/hd/720p/2024 [SLUG of the taxonomy]</span></div>
                                    <div class="pds_s_atts_flex"><span>show_ratings:</span><span>true/false [Show Ratings | Default true]</span></div>
                                    <div class="pds_s_atts_flex"><span>show_quality:</span><span>true/false [Show Quality | Default true]</span></div>
                                </div>
                            </div>
                        </div>
                        <div class="pds_entry">
                            <div class="pds_shortcode_title">Screenshot:</div>
                            <div class="pds_shortcode_desc"><img src="<?php echo esc_url(FP_MOVIES_URL) . 'img/homepage_meta.webp'; ?>" alt="Homepage Meta"></div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    <?php
}

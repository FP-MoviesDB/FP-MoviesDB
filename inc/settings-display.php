<?php

if (!defined('ABSPATH')) exit;

// ┌──────────────────────────────┐
// │ Plugin Setting Options    │
// └──────────────────────────────┘
function fp_settings_page()
{
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }
    $links_options = get_option('mtg_encryption_settings');
    $links_options = wp_parse_args($links_options, [
        'mtg_encryption_method' => 'none',
        'mtg_encryption_reCaptcha_site_key' => '',
        'mtg_encryption_reCaptcha_secret_key' => '',
        'mtg_encryption_turnstile_site_key' => '',
        'mtg_encryption_turnstile_secret_key' => '',
        'mtg_soralink_status' => 'off',
        'mtg_hide_link_refer' => 'off',
    ]);

    $checked_options = get_option('mtg_checked_options');
    $checked_options = wp_parse_args($checked_options, [
        'genre' => 'on',
        'audio' => 'on',
        'year' => 'on',
        'network' => 'on',
        'quality' => 'on',
        'resolution' => 'on',
        'activeClassicEditor' => 'on',
        'displayAllSizes' => 'off',
    ]);

    $postDefault_settings = get_option('mtg_postDefault_settings', []);
    $postDefault_settings = wp_parse_args($postDefault_settings, [
        'title' => '',
        'slug' => '',
        'category' => '',
        'tags' => '',
        'image_name' => '',
        'default_network' => '',
        'default_quality' => '',
        'status' => 'publish',
        'language' => 'en-US',
        'featured_image_size' => 'w342',
    ]);

?>
    <h2>Main Settings</h2>
    <form method="post" action="options.php">

        <?php
        settings_errors();
        settings_fields('mts_generator_settings');
        do_settings_sections('mts_generator_settings');
        wp_nonce_field('mts_generator_settings_action', 'mts_generator_settings_nonce');
        ?>
        <table class="form-table" style="max-width: 80%;">

            <tr valign="top">
                <th scope="row">Logs Status:</th>
                <td>
                    <input type="checkbox" name="mtg_logs_status" <?php checked(get_option('mtg_logs_status'), 'on'); ?> />
                    <p>Enable Logs for Debugging.</p>
                </td>
            </tr>

            <tr valign="top" id="global_access_row" style="display: none;">
                <th scope="row">Global Files:</th>
                <td>
                    <input type="checkbox" name="mtg_global_access" <?php checked(get_option('mtg_global_access'), 'on'); ?> />
                    <p>Only if you have Permission - <a href="https://fpgo.xyz/premium-files-manager" target="_blank">clickHere</a></p>
                </td>
            </tr>

            <tr valign="top">
                <th scope="row">FP KEY Type:</th>
                <td>
                    <select name="mtg_fp_key_type" id="mtg_fp_key_type">
                        <option value="personal" <?php selected(get_option('mtg_fp_key_type'), 'personal'); ?>>Personal</option>
                        <option value="organization" <?php selected(get_option('mtg_fp_key_type'), 'organization'); ?>>Organization</option>
                    </select>
                    <p>Personal = Personal Files + Global Files [OPTIONAL].</p>
                    <p>Organization = Organization Files only.</p>
                </td>
            </tr>

            <tr valign="top">
                <th scope="row">FP API Key:</th>
                <td><input type="text" style="width: 100%;" name="mtg_fp_api_key" value="<?php echo esc_attr(get_option('mtg_fp_api_key')); ?>" placeholder="FilePress API Key (Required)" />
                    <p>Get your <a href="https://fpgo.xyz/profile" target="_blank">Personal</a> / <a href="https://fpgo.xyz/organization" target="_blank">Organization</a> API key.</p>
                </td>
            </tr>


            <tr valign="top">
                <th scope="row">TMDB API:</th>
                <td><input type="text" style="width: 100%;" name="mtg_tmdb_api_key" value="<?php echo esc_attr(get_option('mtg_tmdb_api_key')); ?>" placeholder="TMDB API Key (Required)" />
                    <p>Get your <a href="https://www.themoviedb.org/settings/api" target="_blank">TMDB API key</a>.</p>
                </td>
            </tr>

            <tr valign="top">
                <th scope="row">Link Protection Type:</th>
                <td>
                    <select name="mtg_encryption_settings[mtg_encryption_method]" id="mtg_encryption_method">
                        <option value="none" <?php selected($links_options['mtg_encryption_method'], 'none'); ?>>None</option>
                        <option value="recaptcha" <?php selected($links_options['mtg_encryption_method'], 'recaptcha'); ?>>Google reCaptcha v2</option>
                        <option value="turnstile" <?php selected($links_options['mtg_encryption_method'], 'turnstile'); ?>>Cloudflare Turnstile</option>
                    </select>
                    <p><a href="https://www.google.com/recaptcha/admin/create" target="_blank">reCaptcha</a> | <a href="https://dash.cloudflare.com/?to=/:account/turnstile" target="_blank">Turnstile</a></p>
                </td>
            </tr>

            <tr valign="top" class="recaptcha_row" style="display: none;">
                <th scope="row">reCaptcha API Site Key:</th>
                <td><input type="text" style="width: 100%;" name="mtg_encryption_settings[mtg_encryption_reCaptcha_site_key]" value="<?php echo esc_attr($links_options['mtg_encryption_reCaptcha_site_key']); ?>" placeholder="Site Key" /></td>
            </tr>

            <tr valign="top" class="recaptcha_row" style="display: none;">
                <th scope="row">reCaptcha API Secret Key:</th>
                <td><input type="text" style="width: 100%;" name="mtg_encryption_settings[mtg_encryption_reCaptcha_secret_key]" value="<?php echo esc_attr($links_options['mtg_encryption_reCaptcha_secret_key']); ?>" placeholder="Secret Key" /></td>
            </tr>

            <tr valign="top" class="turnstile_row" style="display: none;">
                <th scope="row">Turnstile API Site Key:</th>
                <td><input type="text" style="width: 100%;" name="mtg_encryption_settings[mtg_encryption_turnstile_site_key]" value="<?php echo esc_attr($links_options['mtg_encryption_turnstile_site_key']); ?>" placeholder="Site Key" /></td>
            </tr>

            <tr valign="top" class="turnstile_row" style="display: none;">
                <th scope="row">Turnstile API Secret Key:</th>
                <td><input type="text" style="width: 100%;" name="mtg_encryption_settings[mtg_encryption_turnstile_secret_key]" value="<?php echo esc_attr($links_options['mtg_encryption_turnstile_secret_key']); ?>" placeholder="Secret Key" /></td>
            </tr>

            <tr valign="top">
                <th scope="row">Hide Link Refer:</th>
                <td>
                    <input type="checkbox" name="mtg_encryption_settings[mtg_hide_link_refer]" <?php checked($links_options['mtg_hide_link_refer'], 'on'); ?> />
                    <p>Adds "https://href.li/?" to link. <a href="https://href.li/?https://google.com/" target="_blank">Demo</a></p>
                </td>
            </tr>

            <tr valign="top">
                <th scope="row">Enable SoraLink:</th>
                <td>
                    <input type="checkbox" name="mtg_encryption_settings[mtg_soralink_status]" <?php checked($links_options['mtg_soralink_status'], 'on'); ?> />
                    <p> Make sure you have <strong>"SoraLink Client"</strong> Plugin installed and activated.</p>
                </td>
            </tr>


            <tr valign="top">
                <th scope="row">
                    Title:
                    <a href=<?php echo FP_MOVIES_URL . '/img/setting1.webp' ?> target="_blank" style="text-decoration: none;" class="help-icon"> ? </a>
                </th>
                <td><input type="text" style="width: 100%;" name="mtg_postDefault_settings[title]" value="<?php echo esc_attr($postDefault_settings['title']); ?>" placeholder="{title} {year}" /></td>
            </tr>

            <tr valign="top">
                <th scope="row">Slug:
                    <a href=<?php echo FP_MOVIES_URL . '/img/setting1.webp' ?> target="_blank" style="text-decoration: none;" class="help-icon"> ? </a>
                </th>
                <td><input type="text" style="width: 100%;" name="mtg_postDefault_settings[slug]" value="<?php echo esc_attr($postDefault_settings['slug']); ?>" placeholder="{title}-{year}-{p_type}" /></td>
            </tr>

            <tr valign="top">
                <th scope="row">Category:
                    <a href=<?php echo FP_MOVIES_URL . '/img/setting2.webp' ?> target="_blank" style="text-decoration: none;" class="help-icon"> ? </a>
                </th>
                <td><input type="text" style="width: 100%;" name="mtg_postDefault_settings[category]" value="<?php echo esc_attr($postDefault_settings['category']); ?>" placeholder="movies,tv,etc" /><br /><span>*multiple comma separated</span></td>
            </tr>

            <tr valign="top">
                <th scope="row">Tags:
                    <a href=<?php echo FP_MOVIES_URL . '/img/setting2.webp' ?> target="_blank" style="text-decoration: none;" class="help-icon"> ? </a>
                </th>
                <td><input type="text" style="width: 100%;" name="mtg_postDefault_settings[tags]" value="<?php echo esc_attr($postDefault_settings['tags']); ?>" placeholder="movies,tv,etc" /><br /><span>*multiple comma separated</span></td>
            </tr>

            <!-- Default Quality -->
            <tr valign="top">
                <th scope="row">Default Quality:
                    <a href=<?php echo FP_MOVIES_URL . '/img/setting2.webp' ?> target="_blank" style="text-decoration: none;" class="help-icon"> ? </a>
                </th>
                <td><input type="text" style="width: 100%;" name="mtg_postDefault_settings[default_quality]" value="<?php echo esc_attr($postDefault_settings['default_quality']); ?>" placeholder="HD" /></td>
            </tr>

            <!-- Default Network -->
            <tr valign="top">
                <th scope="row">Default Network:
                    <a href=<?php echo FP_MOVIES_URL . '/img/setting2.webp' ?> target="_blank" style="text-decoration: none;" class="help-icon"> ? </a>
                </th>
                <td><input type="text" style="width: 100%;" name="mtg_postDefault_settings[default_network]" value="<?php echo esc_attr($postDefault_settings['default_network']); ?>" placeholder="Hollywood" /></td>
            </tr>

            <!-- Featured Image Name -->
            <tr valign="top">
                <th scope="row">Featured Image Name:
                    <a href=<?php echo FP_MOVIES_URL . '/img/setting2.webp' ?> target="_blank" style="text-decoration: none;" class="help-icon"> ? </a>
                </th>
                <td><input type="text" style="width: 100%;" name="mtg_postDefault_settings[image_name]" value="<?php echo esc_attr($postDefault_settings['image_name']); ?>" placeholder="{title} {year} {p_type}" /></td>
            </tr>

            <!-- Post Status -->
            <tr valign="top">
                <th scope="row">Post Status:
                    <a href=<?php echo FP_MOVIES_URL . '/img/setting1.webp' ?> target="_blank" style="text-decoration: none;" class="help-icon"> ? </a>
                </th>
                <td>
                    <select name="mtg_postDefault_settings[status]" id="status">
                        <option value="publish" <?php selected($postDefault_settings['status'], 'publish'); ?>>Publish</option>
                        <option value="draft" <?php selected($postDefault_settings['status'], 'draft'); ?>>Draft</option>
                        <option value="pending" <?php selected($postDefault_settings['status'], 'pending'); ?>>Pending</option>
                    </select>
                </td>
            </tr>


            <!-- Language -->
            <tr valign="top">
                <th scope="row">Language:</th>
                <td>
                    <select name="mtg_postDefault_settings[language]" id="language">
                        <option value="en-US" <?php selected($postDefault_settings['language'], 'en-US'); ?>>English</option>
                        <option value="hi-IN" <?php selected($postDefault_settings['language'], 'hi-IN'); ?>>Hindi</option>
                        <option value="es-ES" <?php selected($postDefault_settings['language'], 'es-ES'); ?>>Spanish</option>
                        <option value="fr-FR" <?php selected($postDefault_settings['language'], 'fr-FR'); ?>>French</option>
                        <option value="de-DE" <?php selected($postDefault_settings['language'], 'de-DE'); ?>>German</option>
                        <option value="it-IT" <?php selected($postDefault_settings['language'], 'it-IT'); ?>>Italian</option>
                        <option value="ja-JP" <?php selected($postDefault_settings['language'], 'ja-JP'); ?>>Japanese</option>
                        <option value="ko-KR" <?php selected($postDefault_settings['language'], 'ko-KR'); ?>>Korean</option>
                        <option value="pt-BR" <?php selected($postDefault_settings['language'], 'pt-BR'); ?>>Portuguese</option>
                        <option value="ru-RU" <?php selected($postDefault_settings['language'], 'ru-RU'); ?>>Russian</option>
                        <option value="zh-CN" <?php selected($postDefault_settings['language'], 'zh-CN'); ?>>Chinese</option>
                    </select>
                </td>
            </tr>

            <!-- Featured Image Size -->
            <tr valign="top">
                <th scope="row">Featured Image Size:
                    <a href=<?php echo FP_MOVIES_URL . '/img/setting2.webp' ?> target="_blank" style="text-decoration: none;" class="help-icon"> ? </a>
                </th>
                <td>
                    <!-- 
                    "poster_sizes": [
                        "w342", = Thumbnail
                        "w500", = Medium
                        "w780", = High
                        "original"
                    ],
                        -->
                    <select name="mtg_postDefault_settings[featured_image_size]" id="mtg_featured_image_size">
                        <option value="w342" <?php selected($postDefault_settings['featured_image_size'], 'w342'); ?>>Thumbnail</option>
                        <option value="w500" <?php selected($postDefault_settings['featured_image_size'], 'w500'); ?>>Medium</option>
                        <option value="w780" <?php selected($postDefault_settings['featured_image_size'], 'w780'); ?>>High</option>
                        <option value="original" <?php selected($postDefault_settings['featured_image_size'], 'original'); ?>>Original</option>
                    </select>
                </td>
            </tr>


            <tr valign="top">
                <th scope="row">FP Additional Options:</th>

                <td class="mtg_checked_options_td">
                    <div class="mtg_checked_options">
                        <input type="checkbox" name="mtg_checked_options[genre]" <?php checked($checked_options['genre'], 'on'); ?> />
                        <span>Enable Genre Taxonomy</span>
                    </div>
                    <div class="mtg_checked_options">
                        <input type="checkbox" name="mtg_checked_options[audio]" <?php checked($checked_options['audio'], 'on'); ?> />
                        <span>Enable Audio Taxonomy</span>
                    </div>
                    <div class="mtg_checked_options">
                        <input type="checkbox" name="mtg_checked_options[year]" <?php checked($checked_options['year'], 'on'); ?> />
                        <span>Enable Year Taxonomy</span>
                    </div>
                    <div class="mtg_checked_options">
                        <input type="checkbox" name="mtg_checked_options[network]" <?php checked($checked_options['network'], 'on'); ?> />
                        <span>Enable Network Taxonomy</span>
                    </div>
                    <div class="mtg_checked_options">
                        <input type="checkbox" name="mtg_checked_options[quality]" <?php checked($checked_options['quality'], 'on'); ?> />
                        <span>Enable Quality Taxonomy</span>
                    </div>
                    <div class="mtg_checked_options">
                        <input type="checkbox" name="mtg_checked_options[resolution]" <?php checked($checked_options['resolution'], 'on'); ?> />
                        <span>Enable Resolution Taxonomy</span>
                    </div>
                    <div class="mtg_checked_options">
                        <input type="checkbox" name="mtg_checked_options[activeClassicEditor]" <?php checked($checked_options['activeClassicEditor'], 'on'); ?> />
                        <span>Enable Classic Editor</span>
                    </div>
                    <div class="mtg_checked_options">
                        <input type="checkbox" name="mtg_checked_options[displayAllSizes]" <?php checked($checked_options['displayAllSizes'], 'on'); ?> />
                        <span>Enable Media Show All Sizes</span>
                    </div>
                    <p><em>*Changing options in LIVE site can affect shortcodes, i.e., missing values.</em></p>
                    <p><em>*Only change if you know what you doing.</em></p>
                </td>
            </tr>
        </table>
        <!-- Available customizations -->
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
                    <div class="grid-item">Movie/TV</div>
                </div>

                <div class="grid-row">
                    <div class="grid-item">{p_type_2}</div>
                    <div class="grid-item">-</div>
                    <div class="grid-item">PostType</div>
                    <div class="grid-item">Movie/Series</div>
                </div>

                <div class="grid-row">
                    <div class="grid-item">{title}</div>
                    <div class="grid-item">-</div>
                    <div class="grid-item">Title</div>
                    <div class="grid-item">MovieName</div>
                </div>

                <div class="grid-row">
                    <div class="grid-item">{r_year}</div>
                    <div class="grid-item">-</div>
                    <div class="grid-item">Release Year</div>
                    <div class="grid-item">1990</div>
                </div>

                <div class="grid-row">
                    <div class="grid-item">{l_year}</div>
                    <div class="grid-item">-</div>
                    <div class="grid-item">Latest Year</div>
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
                    <div class="grid-item">Dual/Multi Audio [audio count 2 -> Dual OR > 2 then Multi]</div>
                </div>

                <div class="grid-row">
                    <div class="grid-item">{c_subs}</div>
                    <div class="grid-item">-</div>
                    <div class="grid-item">Subs [c]</div>
                    <div class="grid-item">ESubs/MSubs [subs count 1 -> ESubs OR 1 then MSubs]</div>
                </div>
            </div>

            <div class="customizations-grid">
                <div class="grid-title">Default Behavior: =></div>

                <div class="grid-row grid-row-bold">
                    <div class="grid-item">Item</div>
                    <div class="grid-item">-</div>
                    <div class="grid-item">Usage</div>
                    <div class="grid-item">Example</div>
                </div>

                <div class="grid-row">
                    <div class="grid-item">Title</div>
                    <div class="grid-item">-</div>
                    <div class="grid-item">{title}</div>
                    <div class="grid-item">Title</div>
                </div>

                <div class="grid-row">
                    <div class="grid-item">Slug</div>
                    <div class="grid-item">-</div>
                    <div class="grid-item">{title}</div>
                    <div class="grid-item">title</div>
                </div>

                <div class="grid-row">
                    <div class="grid-item">Network</div>
                    <div class="grid-item">-</div>
                    <div class="grid-item">{network}</div>
                    <div class="grid-item">Hollywood</div>
                </div>

                <div class="grid-row">
                    <div class="grid-item">Quality</div>
                    <div class="grid-item">-</div>
                    <div class="grid-item">{quality}</div>
                    <div class="grid-item">HD</div>
                </div>
            </div>
        </div>
        <div class="mtg_submit_btn" style="text-align: center;">
            <?php submit_button(); ?>
        </div>
    </form>
<?php
}

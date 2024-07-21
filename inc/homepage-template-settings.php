<?php

if (!defined('ABSPATH')) exit;

function homepage_template_settings()
{
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }

?>
    <h2>Homepage Global Shortcode Settings</h2>
    <form method="post" action="options.php">
        <?php
        settings_fields('mts_generator_homepage_template_settings');
        do_settings_sections('mts_generator_homepage_template_settings');
        wp_nonce_field('mts_template_homepage_action', 'mts_template_homepage_nonce');

        $homepage_settings = get_option('mtg_homepage_template_settings', []);
        $homepage_settings = wp_parse_args($homepage_settings, [
            'title_background' => 'normal',                 // normal or gradient option select
            'title_length' => 'auto',                       // auto or fixed text field
            'title_wrap' => 'nowrap',                       // nowrap or wrap option select
            'layout_type' => 'vertical',                    // vertical or horizontal option select
            'image_source' => 'local',                      // local or IMDB option select
            'image_size' => 'original',                     // small, medium, large, or original option select
        ]);
        ?>

        <table class="form-table" style="max-width: 80%;">
            <div class="mtg_submit_btn" style="text-align: center;">
                <?php submit_button(); ?>
            </div>

            <tr>
                <th scope="row">
                    <label for="title_background">Title Background</label>
                </th>
                <td>
                    <select name="mtg_homepage_template_settings[title_background]" id="title_background">
                        <option value="normal" <?php selected($homepage_settings['title_background'], 'normal'); ?>>Normal</option>
                        <option value="gradient" <?php selected($homepage_settings['title_background'], 'gradient'); ?>>Gradient</option>
                    </select>
                    <p> Gradient will take time on load, until cache version is not served.</p>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="title_length">Title Length</label>
                </th>
                <td>
                    <input type="text" name="mtg_homepage_template_settings[title_length]" id="title_length" value="<?php echo esc_attr($homepage_settings['title_length']); ?>">
                     <p>auto for default || number to specify limit.</p>

                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="title_wrap">Title Wrap</label>
                </th>
                <td>
                    <select name="mtg_homepage_template_settings[title_wrap]" id="title_wrap">
                        <option value="nowrap" <?php selected($homepage_settings['title_wrap'], 'nowrap'); ?>>No Wrap</option>
                        <option value="wrap" <?php selected($homepage_settings['title_wrap'], 'wrap'); ?>>Wrap</option>
                    </select>
                    <p>Wrap will use multiple lines.</p>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="layout_type">Layout Type</label>
                </th>
                <td>
                    <select name="mtg_homepage_template_settings[layout_type]" id="layout_type">
                        <option value="vertical" <?php selected($homepage_settings['layout_type'], 'vertical'); ?>>Vertical</option>
                        <option value="horizontal" <?php selected($homepage_settings['layout_type'], 'horizontal'); ?>>Horizontal</option>
                    </select>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="image_source">Image Source</label>
                </th>
                <td>
                    <select name="mtg_homepage_template_settings[image_source]" id="image_source">
                        <option value="local" <?php selected($homepage_settings['image_source'], 'local'); ?>>Local</option>
                        <option value="tmdb" <?php selected($homepage_settings['image_source'], 'tmdb'); ?>>TMDB</option>
                    </select>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="image_size">Image Size</label>
                </th>
                <td>
                    <select name="mtg_homepage_template_settings[image_size]" id="image_size">
                        <option value="small" <?php selected($homepage_settings['image_size'], 'small'); ?>>Small</option>
                        <option value="medium" <?php selected($homepage_settings['image_size'], 'medium'); ?>>Medium</option>
                        <option value="large" <?php selected($homepage_settings['image_size'], 'large'); ?>>Large</option>
                        <option value="original" <?php selected($homepage_settings['image_size'], 'original'); ?>>Original</option>
                    </select>
                </td>
            </tr>

        </table>
         <p> Note: all settings are global settings and can be overwrite using inLine shortcode. </p>
        <div class="mtg_submit_btn" style="text-align: center;">
            <?php submit_button(); ?>
        </div>
    </form>








        <?php
    }

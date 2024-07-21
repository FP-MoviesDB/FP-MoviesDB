<?php

if (!defined('ABSPATH')) exit;
if (!class_exists('PostButtons')) {
    class PostButtons
    {

        function __construct()
        {
            global $pagenow;
            add_action('wp_ajax_fp_update_post_data', array($this, 'handle_update_post'));
            add_action('wp_ajax_fp_set_featured_post', array($this, 'handle_feature_post'));

            if ($pagenow == 'edit.php') {
                add_filter('manage_post_posts_columns', array($this, 'add_custom_columns'));
                add_action('manage_post_posts_custom_column', array($this, 'render_custom_columns'), 10, 2);
                add_action('admin_enqueue_scripts', array($this, 'enqueue_custom_admin_script'));
                add_filter('manage_edit-post_sortable_columns', array($this, 'add_sortable_columns'));
                add_action('pre_get_posts', array($this, 'sort_posts_by_featured_meta'));
            }
        }

        function add_sortable_columns($columns)
        {
            $columns['featured_button'] = 'mtg_is_featured';
            return $columns;
        }

        function add_custom_columns($columns)
        {
            $columns['featured_button'] = 'Featured';
            $columns['update_button'] = 'Re-Fetch';
            return $columns;
        }

        function render_custom_columns($column, $post_id)
        {
            $all_meta = get_post_meta($post_id);
            $mtg_is_featured = isset($all_meta['mtg_is_featured'][0]) ? $all_meta['mtg_is_featured'][0] : '0';
            $f_text = $mtg_is_featured == '1' ? 'Remove' : 'Add';
            $f_value = $mtg_is_featured == '1' ? '1' : '0';

            $tmdb_id = isset($all_meta['mtg_tmdb_id'][0]) ? $all_meta['mtg_tmdb_id'][0] : '';
            $post_type = isset($all_meta['_content_type'][0]) ? $all_meta['_content_type'][0] : '';

            if ($column == 'update_button') {
                echo '<div class="fp-custom-button update-post" data-page="all" data-id="' . $post_id . '" data-tmdb="' . $tmdb_id . '" data-type="' . $post_type . '"><img src="' . FP_MOVIES_URL . 'img/sync-blue.svg' . '" alt="Sync Icon" width="15" height="auto"></div>';
            } elseif ($column == 'featured_button') {
                echo '<div class="fp-custom-button feature-post" data-id="' . $post_id . '" data-value="' . $f_value . '">' . $f_text . '</div>';
            }
        }

        function sort_posts_by_featured_meta($query)
        {
            if (!is_admin() || !$query->is_main_query()) {
                return;
            }

            $orderby = $query->get('orderby');
            if ('mtg_is_featured' == $orderby) {
                $query->set('meta_key', 'mtg_is_featured');  // Specify the meta key to sort by
                $query->set('orderby', 'meta_value_num');    // Change how the posts are ordered
            }
        }

        function enqueue_custom_admin_script()
        {
            global $fp_min_m;
            wp_enqueue_script('post-button-admin-script', FP_MOVIES_URL . 'js/fp_post_button' . $fp_min_m . '.js', array('jquery'), FP_MOVIES_FILES, true);
            wp_localize_script('post-button-admin-script', 'btnData', array(
                'ajax_url' => FP_MOVIES_AJAX,
                'update-nonce' => wp_create_nonce('fp_post_update_nonce'),
                'featured-nonce' => wp_create_nonce('fp-set-featured-nonce'),
                'web_url' => FP_MOVIES_URL
            ));

            wp_enqueue_style('post-button-admin-style', FP_MOVIES_URL . '/css/fp_post_button' . $fp_min_m . '.css', array(), FP_MOVIES_FILES);
        }


        function handle_update_post()
        {
            if (!isset($_POST['post_id']) || !wp_verify_nonce($_POST['nonce'], 'fp-update-post-nonce')) {
                wp_send_json_error('Invalid request');
                wp_die();
            }
            require_once FP_MOVIES_DIR . 'inc/update_post.php';
            // $post_id = intval($_POST['post_id']);
            wp_send_json_success('Post updated successfully');
        }

        function handle_feature_post()
        {
            if (!isset($_POST['post_id']) || !wp_verify_nonce($_POST['nonce'], 'fp-set-featured-nonce')) {
                wp_send_json_error('Invalid request');
                wp_die();
            }
            $post_id = intval($_POST['post_id']);
            $post_value  = $_POST['post_value'];
            $new_value = ($post_value  == '1') ? '0' : '1';
            update_post_meta($post_id, 'mtg_is_featured', $new_value);
            $response = [
                'post_value' => $new_value
            ];
            wp_send_json_success($response);
        }
    }
}
new PostButtons();

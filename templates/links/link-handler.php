<?php
/* Template Name: Link Handler */
if (!defined('ABSPATH')) exit;

class LinkHandler
{
    public function __construct()
    {
        // Register template
        add_filter('theme_page_templates', [$this, 'register_template']);
        add_filter('template_include', [$this, 'load_template']);
        // Page creation
        add_action('admin_notices', [$this, 'admin_notice']);
        add_action('admin_init', [$this, 'handle_page_creation']);
        // URL processing
        add_action('template_redirect', [$this, 'link_template_redirect']);
        add_action('init', [$this, 'add_rewrite_rule']);
        add_filter('query_vars', [$this, 'add_query_vars']);
    }

    public function register_template($templates)
    {
        $templates['link-handler-template.php'] = 'Link Handler Template';
        return $templates;
    }

    public function load_template($template)
    {
        if (is_page_template('link-handler-template.php')) {
            $plugin_template = FP_MOVIES_DIR . 'templates/links/link-handler-template.php';
            if (file_exists($plugin_template)) {
                return $plugin_template;
            }
        }
        return $template;
    }

    public function admin_notice()
    {
        if (!get_page_by_path('fp-links-encryption')) {
?>
            <div class="notice notice-warning is-dismissible">
                <p>The Link Handler page is not created yet. Click the button below to create it.</p>
                <p><a href="<?php echo esc_url(wp_nonce_url(admin_url('?create_link_handler_page=1'), 'create_link_handler_page')); ?>" class="button-primary">Create Link Handler Page</a></p>
            </div>
<?php
        }
    }

    public function handle_page_creation()
    {
        if (isset($_GET['create_link_handler_page']) && $_GET['create_link_handler_page'] == '1') {

            if (!isset($_GET['_wpnonce']) || !wp_verify_nonce($_GET['_wpnonce'], 'create_link_handler_page')) {
                wp_die('Security check failed');
            }


            $this->create_page();
            wp_redirect(admin_url());
            exit();
        }
    }

    public function create_page()
    {
        $page = get_page_by_path('fp-links-encryption');
        if (!$page) {
            $page_data = array(
                'post_title'    => 'FP Link Handler',
                'post_content'  => '',
                'post_status'   => 'publish',
                'post_author'   => 1,
                'post_type'     => 'page',
                'post_name'     => 'fp-links-encryption',
                'page_template' => 'link-handler-template.php'
            );
            $page_id = wp_insert_post($page_data);
            if (!is_wp_error($page_id)) {
                $this->flush_rewrite_rules();
            } else {
                echo esc_html('Error creating page: ' . $page_id->get_error_message());
            }
        } else {
            echo 'Link Handler page already exists.';
        }
    }

    public function add_rewrite_rule()
    {
        // error_log('Adding rewrite rule');
        add_rewrite_rule('link/([^/]+)/?$', 'index.php?encrypted_url=$matches[1]', 'top');
    }

    public function add_query_vars($query_vars)
    {
        // error_log('Adding query vars');
        $query_vars[] = 'encrypted_url';
        return $query_vars;
    }

    public function link_template_redirect()
    {
        // error_log('Redirecting to link handler');
        if ($encrypted_url = get_query_var('encrypted_url')) {
            include FP_MOVIES_DIR . 'templates/links/link-handler-template.php';
            exit();
        }
    }

    function flush_rewrite_rules()
    {
        flush_rewrite_rules();
    }
}

new LinkHandler();
?>
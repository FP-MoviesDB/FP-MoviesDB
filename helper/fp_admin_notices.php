<?php
/*
* -------------------------------------------------------------------------------------
* @author: FP Movies Classic Theme
* @author URI: https://fpmoviesdb.xyz/
* @copyright: (c) | All rights reserved
* -------------------------------------------------------------------------------------
*
* @since 1.0.0
*
*/

if (!defined('ABSPATH')) exit;

if (!function_exists('fp_admin_notice')) {
    function fp_admin_notice($type = 'info', $message = 'No Message Provided.')
    {
        $c = 'notice is-dismissible';
        switch ($type) {
            case 'error':
                $c .= ' notice-error';
                break;
            case 'warning':
                $c .= ' notice-warning';
                break;
            case 'success':
                $c .= ' notice-success';
                break;
            default:
                $c .= ' notice-info';
                break;
        }

        $m = __($message, FP_MOVIES_TEXT_DOMAIN);
?>
        <div class="<?php echo esc_attr($c); ?>">
            <p><?php echo esc_html($m); ?></p>
        </div>

<?php
    }
}

if (!function_exists('fp_show_transient_notice')) {
    function fp_show_transient_notice()
    {
        if ($notice = get_transient('fp_t_admin_notice')) {
            list($type, $message) = $notice;
            fp_admin_notice($type, $message);
            delete_transient('fp_t_admin_notice');
        }
    }
}


if (!function_exists('fp_notice')) {
    function fp_notice($type = '', $message = '')
    {
        if (empty($type) || empty($message)) return;

        set_transient('fp_t_admin_notice', array($type, $message), 30);
    }
}

<?php
/* Template Name: Link Handler Template */
if (!defined('ABSPATH')) exit;

require_once FP_MOVIES_DIR . 'helper/fp_links_encryption.php';
require_once FP_MOVIES_DIR . 'helper/fp_get_sLink.php';
require_once FP_MOVIES_DIR . 'helper/fp_get_gLink.php';


if (session_status() === PHP_SESSION_NONE) {
    if (!file_exists(FP_CACHE_DIR . '/sessions')) mkdir(FP_CACHE_DIR . '/sessions', 0755, true);
    session_save_path(FP_CACHE_DIR . '/sessions');
    if (session_start() === false) {
        fp_log_error('Session failed to start.');
    }
}

$encrypted_url = get_query_var('encrypted_url');
$captcha_valid = $_SESSION['captcha_valid'] ?? false;
$encryption_settings = get_option('mtg_encryption_settings', array());
$captcha_method = $encryption_settings['mtg_encryption_method'] ?? 'none';
$site_key = ($captcha_method === 'recaptcha') ? $encryption_settings['mtg_encryption_reCaptcha_site_key'] : $encryption_settings['mtg_encryption_turnstile_site_key'];
$secret_key = ($captcha_method === 'recaptcha') ? $encryption_settings['mtg_encryption_reCaptcha_secret_key'] : $encryption_settings['mtg_encryption_turnstile_secret_key'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['link_verify_captcha_nonce']) || !wp_verify_nonce($_POST['link_verify_captcha_nonce'], 'link_verify_captcha')) {
        wp_die('Invalid Request', 'Invalid Request', array('response' => 403));
    }

    $nonce = $_POST['validate_captcha_nonce'] ?? '';
    $captcha_valid = validate_captcha($captcha_method, $secret_key, $nonce);

    if ($captcha_valid) {
        $_SESSION['captcha_valid'] = true;
    }
}

if ($encrypted_url) {
    $decrypted_url = fp_decrypt_url($encrypted_url);
    if ($decrypted_url) {
        if ($captcha_method === 'none' || $captcha_valid || current_user_can('administrator')) {
            $soraLinksEnabled = isset($encryption_settings['mtg_soralink_status']) ? $encryption_settings['mtg_soralink_status'] : 'off';
            $gyaniLinksEnabled = isset($encryption_settings['mtg_gyanilink_status']) ? $encryption_settings['mtg_gyanilink_status'] : 'off';
            $hideLinkRefer = isset($encryption_settings['mtg_hide_link_refer']) ? $encryption_settings['mtg_hide_link_refer'] : 'off';

            $soraLinksEnabled = $soraLinksEnabled === 'on' ? true : false;
            $gyaniLinksEnabled = $gyaniLinksEnabled === 'on' ? true : false;
            $hideLinkRefer = $hideLinkRefer === 'on' ? true : false;

            if ($hideLinkRefer) {
                // adds https://href.li/? to the decrypted url beginning
                $decrypted_url = 'https://href.li/?' . $decrypted_url;
            }

            $final_url = $decrypted_url;

            if ($gyaniLinksEnabled && $soraLinksEnabled) {
                $soraPriority = isset($encryption_settings['mtg_soralink_priority']) ? intval($encryption_settings['mtg_soralink_priority']) : 1;
                $gyaniPriority = isset($encryption_settings['mtg_gyanilink_priority']) ? intval($encryption_settings['mtg_gyanilink_priority']) : 2;
                if ($soraPriority < $gyaniPriority) {
                    $sora_encrypted_url = process_sora_encryption($decrypted_url);
                    if ($sora_encrypted_url) {
                        $gyani_encrypted_url = process_gyani_encryption($sora_encrypted_url, $encryption_settings);
                    } else {
                        $gyani_encrypted_url = process_gyani_encryption($decrypted_url, $encryption_settings);
                    }
                    if ($gyani_encrypted_url) {
                        $final_url = $gyani_encrypted_url;
                    } else if ($sora_encrypted_url) {
                        $final_url = $sora_encrypted_url;
                    } else {
                        $final_url = $decrypted_url;
                    }
                } else {
                    $gyani_encrypted_url = process_gyani_encryption($decrypted_url, $encryption_settings);
                    if ($gyani_encrypted_url) {
                        $sora_encrypted_url = process_sora_encryption($gyani_encrypted_url);
                    } else {
                        $sora_encrypted_url = process_sora_encryption($decrypted_url);
                    }
                    if ($sora_encrypted_url) {
                        $final_url = $sora_encrypted_url;
                    } else if ($gyani_encrypted_url) {
                        $final_url = $gyani_encrypted_url;
                    } else {
                        $final_url = $decrypted_url;
                    }
                }
            } else {
                if ($soraLinksEnabled) {
                    $sora_encrypted_url = process_sora_encryption($decrypted_url);
                    $final_url = $sora_encrypted_url ? $sora_encrypted_url : $decrypted_url;
                }
                if ($gyaniLinksEnabled) {
                    $gyani_encrypted_url = process_gyani_encryption($decrypted_url, $encryption_settings);
                    $final_url = $gyani_encrypted_url ? $gyani_encrypted_url : $decrypted_url;
                }
            }

            global $fp_min_m;
            // $poppins_url = esc_url(FP_MOVIES_URL) . 'fonts/poppins' . $fp_min_m . '.css';
            $font_url = 'https://fonts.googleapis.com/css2?family=Nunito:wght@500;600;700;800&family=Poppins:wght@500;600;700;800&family=Roboto:wght@500;600;700;800&display=swap';
            if (current_user_can('administrator')) {
                echo '<!DOCTYPE html><html lang="en-US"><head>';
                // echo '<link href="' . esc_url($poppins_url) . '" rel="stylesheet" type="text/css">';
                echo '<link rel="preconnect" href="https://fonts.gstatic.com">';
                echo '<link href="' . esc_url($font_url) . '" rel="stylesheet" type="text/css">';
                echo '<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />';
                display_admin_links();
                echo '</head><body>';
                echo '<div class="admin-link-container">';
                echo "<h1>Only Administrator can see this page.</h1>";
                echo "<p><b>Decrypted URL: </b>" . esc_url($decrypted_url) . "</p>";
                if ($soraLinksEnabled) {
                    if (function_exists('sora_client_url')) {
                        echo "<p>Sora Encrypted URL: " . esc_url($sora_encrypted_url) . "</p>";
                    } else {
                        echo "<p>Sora Links Plugin either not installed or not activated.</p>";
                    }
                }
                if ($gyaniLinksEnabled) {
                    echo "<p>Gyani Encrypted URL: " . esc_url($gyani_encrypted_url) . "</p>";
                }
                echo '<div class="redirect-btn"> <a href="' . esc_url($decrypted_url) . '" class="button-primary">Open Main URL</a></div>';
                if ($soraLinksEnabled && function_exists('sora_client_url')) {
                    echo '<div class="redirect-btn"> <a href="' . esc_url($sora_encrypted_url) . '" class="button-primary">Open Sora URL</a></div>';
                }
                if ($gyaniLinksEnabled) {
                    echo '<div class="redirect-btn"> <a href="' . esc_url($gyani_encrypted_url) . '" class="button-primary">Open Gyani URL</a></div>';
                }
            } else {
                // if ($soraLinksEnabled && function_exists('sora_client_url')) {
                //     wp_redirect($sora_encrypted_url, 301);
                // } else {
                //     wp_redirect($decrypted_url, 301);
                // }
                wp_redirect($final_url);
                echo '</div>';
                echo '</body></html>';
                exit();
            }
        } else {
            // if site_key or secret_key is not set, display error message and exit
            if (empty($site_key) || empty($secret_key)) {
                echo "Invalid/Missing captcha settings. Please contact the administrator.";
                exit();
            }
            display_captcha_form($captcha_method, $site_key, $encrypted_url);
        }
    } else {
        // Use this Template: templates/fp_404.php
        require_once FP_MOVIES_DIR . 'templates/fp_404.php';
        // echo "Invalid URL.";
    }
} else {
    require_once FP_MOVIES_DIR . 'templates/fp_404.php';
}

// Session end
if (isset($_SESSION)) session_destroy();





function validate_captcha($captcha_method, $secret_key, $nonce)
{

    if (!wp_verify_nonce($nonce, 'validate_captcha_action')) {
        return false;
    }

    $captcha_response = '';
    if ($captcha_method === 'recaptcha' && isset($_POST['g-recaptcha-response'])) {
        $captcha_response = $_POST['g-recaptcha-response'];
        $verify_url = "https://www.google.com/recaptcha/api/siteverify";
    } elseif ($captcha_method === 'turnstile' && isset($_POST['cf-turnstile-response'])) {
        $captcha_response = $_POST['cf-turnstile-response'];
        $verify_url = "https://challenges.cloudflare.com/turnstile/v0/siteverify";
    }


    if (!empty($captcha_response)) {
        $response = wp_remote_post($verify_url, array(
            'body' => array(
                'secret' => $secret_key,
                'response' => $captcha_response
            )
        ));
        if (is_wp_error($response)) {
            error_log('Captcha API request failed: ' . $response->get_error_message());
            return false;
        }
        $response_body = wp_remote_retrieve_body($response);
        $result = json_decode($response_body, true);
        // error_log('Captcha validation result: ' . print_r($result, true));
        return isset($result['success']) && $result['success'];
    }
    return false;
}


function display_captcha_form($captcha_method, $site_key, $encrypted_url)
{

    echo '<!DOCTYPE html><html lang="en-US"><head>';
    if ($captcha_method === 'recaptcha') {
        echo '<script src="https://www.google.com/recaptcha/api.js" async defer></script>';

        // wp_enqueue_script('sp-l-safety', FP_MOVIES_URL . 'templates/js/fp_cSecurity.js', array(''), FP_MOVIES_VERSION, true);
    } elseif ($captcha_method === 'turnstile') {
        echo '<script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>';
    }
    // page title: Verifying Request
    echo '<title>Verifying Request</title>';
    echo '<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />';
    echo '<meta name="robots" content="noindex, nofollow">';
    global $fp_min_m;
    $poppins_url = esc_url(FP_MOVIES_URL) . 'fonts/poppins' . $fp_min_m . '.css';
    echo '<link href="' . esc_url($poppins_url) . '" rel="stylesheet" type="text/css">';
    display_captcha_styles();
    echo '</head><body>';
    echo '<div class="verification-container">';
    echo '<h1>Please Complete the captcha to Proceed</h1>';
    echo '<form id="captcha-form" method="post" action="' . esc_url($_SERVER['REQUEST_URI']) . '">';
    wp_nonce_field('validate_captcha_action', 'validate_captcha_nonce');
    echo '<div class="captcha-error"></div>';
    echo '<div class="captcha-box-wrapper">';

    if ($captcha_method === 'recaptcha') {
        // Add reCAPTCHA v2 checkbox
        echo '<div id="captcha-box" class="g-recaptcha" data-sitekey="' . esc_attr($site_key) . '"></div>';
    } elseif ($captcha_method === 'turnstile') {
        echo '<div id="captcha-box" class="cf-turnstile" data-sitekey="' . esc_attr($site_key) . '"></div>';
    }
    echo '</div>';
    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Safe output from wp_nonce_field()
    echo wp_nonce_field('link_verify_captcha', 'link_verify_captcha_nonce', true, false);
    echo '<input type="hidden" name="encrypted_url" value="' . esc_attr($encrypted_url) . '">';
    echo '<div class="captcha-submit-wrapper"><input class="captcha-submit" type="submit" name="submit" value="Verify"></div>';
    echo '</form>';
    echo '</div>';
    // echo '<script src=' . esc_url(FP_MOVIES_URL) . 'templates/js/fp_cSecurity.js></script>';
    echo '</body></html>';
}


function display_captcha_styles()
{
    echo '<style> body{margin:0;padding:0}a,body,h1,h2,h3,h4,h5,h6,p{font-family:Nunito,Roboto,Poppins,sans-serif}.verification-container{display:flex;flex-direction:column;align-items:center;justify-content:center;height:100vh;font-family:Nunito,Poppins,sans-serif}.verification-container h1{text-align:center}.g-recaptcha{margin-bottom:20px}.captcha-submit{padding:10px 20px;background-color:#0073aa;color:#fff;border:none;border-radius:5px;cursor:pointer}.captcha-box-wrapper,.captcha-submit-wrapper{display:flex;justify-content:center;align-items:center}#captcha-box{min-width:300px;min-height:80px} </style>';
}

// NON-MINIFIED CSS CODE
/*

        body {
            margin: 0;
            padding: 0;
        }
        body,p,a,h1,h2,h3,h4,h5,h6 {
            font-family: "Nunito", "Roboto", "Poppins", sans-serif;
        }
        .verification-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100vh;
            font-family: "Nunito", "Poppins", sans-serif;
        }
        .verification-container h1 {
            text-align: center;
        }
        .g-recaptcha {
            margin-bottom: 20px;
        }
        .captcha-submit {
            padding: 10px 20px;
            background-color: #0073aa;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .captcha-submit-wrapper{
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .captcha-box-wrapper{
            display: flex;
            justify-content: center;
            align-items: center;
        }
        #captcha-box{
            min-width: 300px;
            min-height: 80px;
        }


*/


function display_admin_links()
{
    echo '<style>
    body {
        margin: 0;
        padding: 0;
        font-family: "Nunito", "Roboto", "Poppins", sans-serif;
    }
    .admin-link-container{
        height: 100vh;
        line-height: normal;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
    }
    .admin-link-container h1 {
        text-align: center;
        font-family: "Poppins", sans-serif;
    }
    h1, p, a {
        font-family: inherit;
    }
    .redirect-btn{
        margin-top: 10px;
        padding: 10px 20px;
        background-color: #007bff;
        color: white;
        min-width: 200px;
        text-align: center;
        max-width: 200px;
        border-radius: 5px;
        overflow: hidden;
    }
    .redirect-btn a {
        text-decoration: none;
        color: white;
    }
    .redirect-btn:hover {
        background-color: #333;
    }
    </style>';
}

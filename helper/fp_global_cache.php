<?php

if (!defined('ABSPATH')) die();

define('isEncrypted', true);

// Ensure the cache directory exists
if (!file_exists(FP_CACHE_DIR)) {
    mkdir(FP_CACHE_DIR, 0755, true);
}

function fp_encrypt_data($data)
{
    $data = serialize($data);
    $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length(FP_MOVIES_ENCRYPTION_METHOD));
    $encrypted = openssl_encrypt($data, FP_MOVIES_ENCRYPTION_METHOD, FP_MOVIES_ENCRYPTION_KEY, 0, $iv);
    return base64_encode($encrypted . '::' . $iv);
}

function fp_decrypt_data($data)
{
    list($encrypted_data, $iv) = explode('::', base64_decode($data), 2);
    $decrypted = openssl_decrypt($encrypted_data, FP_MOVIES_ENCRYPTION_METHOD, FP_MOVIES_ENCRYPTION_KEY, 0, $iv);
    return unserialize($decrypted); // Unserialize the decrypted data
}


function fp_store_cache($key, $data, $expiry = 60*60*24) //60*60*24
{
    // error_log('Storing cache for key: ' . $key);
    $cache_file = FP_CACHE_DIR . '/' . md5($key) . '.cache';
    if (isEncrypted) {
        $data = serialize(['data' => fp_encrypt_data($data), 'expiry' => time() + $expiry]);
    } else {
        $data = serialize(['data' => $data, 'expiry' => time() + $expiry]);
    }
    file_put_contents($cache_file, $data);
}


function fp_get_cache($key)
{
    $cache_file = FP_CACHE_DIR . '/' . md5($key) . '.cache';
    if (file_exists($cache_file)) {
        $cache_data = unserialize(file_get_contents($cache_file));
        if (time() < $cache_data['expiry']) {
            if (isEncrypted) {
                return fp_decrypt_data($cache_data['data']);
            } else {
                return $cache_data['data'];
            }
        } else {
            unlink($cache_file);
            // $resp = ['error' => 'Cache expired'];
            return false;
        }
    }
    return false;
}

function fp_delete_cache($key)
{
    $cache_file = FP_CACHE_DIR . '/' . md5($key) . '.cache';
    if (file_exists($cache_file)) {
        unlink($cache_file);
    }
}


function fp_handle_clear_cache()
{
    $redirect_to = isset($_GET['redirect_to']) ? urldecode($_GET['redirect_to']) : admin_url();

    if (isset($_GET['action']) && $_GET['action'] == 'fp_clear_cache' && check_admin_referer('fp_clear_cache_action')) {
        fp_delete_all_cache();
        wp_redirect($redirect_to);
        // error_log('Cache cleared');
        exit();
    }

    if (isset($_GET['action']) && $_GET['action'] == 'fp_clear_page_cache' && check_admin_referer('fp_clear_page_cache_action')) {
        $post_id = isset($_GET['post_id']) ? intval($_GET['post_id']) : 0;
        if ($post_id) {
            clear_post_specific_transient($post_id, get_post($post_id), true);
        }
        wp_redirect($redirect_to);
        exit();
    }
}


function fp_delete_all_cache()
{
    $cache_dir = FP_CACHE_DIR;
    if (is_dir($cache_dir)) {
        delete_directory($cache_dir);
        mkdir($cache_dir, 0755, true);
    }
}

function delete_directory($dir)
{
    if (!is_dir($dir)) {
        return;
    }

    $items = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );

    foreach ($items as $item) {
        if ($item->isDir()) {
            rmdir($item->getRealPath());
        } else {
            unlink($item->getRealPath());
        }
    }
    rmdir($dir);
}

function delete_all_expired()
{
    $files = glob(FP_CACHE_DIR . '/*.cache');
    foreach ($files as $file) {
        if (is_file($file)) {
            $cache_data = unserialize(file_get_contents($file));
            if (time() >= $cache_data['expiry']) {
                unlink($file);
            }
        }
    }
}

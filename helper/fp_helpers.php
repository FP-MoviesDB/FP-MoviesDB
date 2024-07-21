<?php

if (!defined('ABSPATH')) exit;

class FP_moviesHelpers
{

    public function Disset($data = '', $key = '')
    {
        return isset($data[$key]) ? $data[$key] : null;
    }

    public function TimeExe($time = '')
    {
        $micro    = microtime(TRUE);
        return number_format($micro - $time, 2);
    }

    public function RemoteJson($args = array(), $api = '', $append = '')
    {
        // error_log('RemoteJson API Args: ' . print_r($args, TRUE));
        // $sapi = esc_url_raw(add_query_arg($args,$api));
        $query = http_build_query($args);
        $sapi = $api . '?' . $query . $append;
        $sapi = esc_url_raw($sapi);
        // error_log('RemoteJson API URL: ' . print_r($sapi, TRUE));
        $json = wp_remote_retrieve_body(wp_remote_get($sapi));
        // error_log('RemoteJson API URL: ' . print_r($sapi, TRUE));
        return json_decode($json, true);
    }

    public function get_option_with_fallback($option_name, $default = '')
    {
        $option_value = get_option($option_name);

        // Check if the option is either not set or empty
        if (empty($option_value)) {
            // Alert the Option_value is empty
            // error_log("$option_name Option Value is Empty: " . print_r($option_value, TRUE));
            return $default;
        }

        return $option_value;
    }

    public function get_arrayValue_with_fallback($array_data, $option_name, $default = '')
    {
        $option_value = isset($array_data[$option_name]) ? $array_data[$option_name] : $default;
        if (empty($option_value)) return $default;
        return $option_value;
    }
}

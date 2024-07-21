<?php

if (!defined('ABSPATH')) exit;

// ┌────────────────────────────────────────────────────────────────────────┐
// │ This take Cares option its set Empty in Database with fallback │
// └────────────────────────────────────────────────────────────────────────┘
function get_option_with_fallback($option_name, $default = '')
{
    // error_log('get_option_with_fallback: ' . $option_name);
    // error_log('get_option_with_fallback: ' . $default);
    if (strpos($option_name, '[') !== false) {
        // error_log('Under array');
        preg_match('/(.*)\[(.*)\]/', $option_name, $matches);
        if (isset($matches[1]) && isset($matches[2])) {
            // error_log('Under array: ' . $matches[1]);
            $main_option_name = $matches[1];    // option name
            $array_key = $matches[2];           // array key
            // error_log('Under array | main_option_name: ' . $main_option_name);
            // error_log('Under array | array_key: ' . $array_key);
            $option_value = get_option($main_option_name);
            // error_log('Under array | option_value: ' . $option_value);
            if (!empty($option_value) && isset($option_value[$array_key])) {
                // error_log('VALUE FOUND');
                // error_log('Under array | option_value[array_key]: ' . $option_value[$array_key]);
                return $option_value[$array_key];
            } else {
                // error_log('USING DEFAULT');
                return $default;
            }
        }
    } else {
        $option_value = get_option($option_name);
        if (empty($option_value)) {
            return $default;
        }
        return $option_value;
    }

    return $default;
}

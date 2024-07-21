<?php

if (!defined('ABSPATH')) die();

# Mobile or not mobile
function fp_mobile() {
	$mobile = ( wp_is_mobile() == true ) ? '1' : 'false';
	return $mobile;
}

# Echo translated text
function _d($text){
	echo translate($text,'dooplay');
}

# Return Translated Text
function __d($text) {
    return translate($text,'dooplay');
}

# is set
function doo_isset($data, $meta, $default = ''){
    return isset($data[$meta]) ? $data[$meta] : $default;
}

# Trailer / url embed
function doo_trailer_iframe_url_embed($id, $autoplay = '0') {
	if (!empty($id)) {
        $autoplay = true;
	    $val = str_replace( array("[","]",),array('https://www.youtube.com/embed/','?autoplay='.$autoplay.'&autohide=1'), $id);
		return $val;
	}
}
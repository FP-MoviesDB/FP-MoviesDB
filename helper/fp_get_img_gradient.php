<?php

if (!defined('ABSPATH')) exit;

if (!function_exists('fp_calculateImageGradient')) {
    function fp_calculateImageGradient($imageUrl)
    {
        $numSlices = 5;
        $response = wp_remote_get($imageUrl);
        
        // Check for HTTP request errors
        if (is_wp_error($response) || wp_remote_retrieve_response_code($response) != 200) {
            error_log('Failed to retrieve image: ' . $imageUrl);
            return 'linear-gradient(90deg, rgba(0, 0, 0, 0.8), rgba(0, 0, 0, 0.8))';
        }

        $imageContent = @wp_remote_retrieve_body($response);
        
        if ($imageContent === false) {
            return 'linear-gradient(90deg, rgba(0, 0, 0, 0.8), rgba(0, 0, 0, 0.8))';
        }

        $image = @imagecreatefromstring($imageContent);
        if ($image === false) {
            return 'linear-gradient(90deg, rgba(0, 0, 0, 0.8), rgba(0, 0, 0, 0.8))';
        }

        $width = imagesx($image);
        $height = imagesy($image);
        $sliceHeight = (int)($height / $numSlices);

        $gradientColors = [];

        for ($i = 0; $i < $numSlices; $i++) {
            $sliceData = imagecreatetruecolor($width, $sliceHeight);
            imagecopy($sliceData, $image, 0, 0, 0, $i * $sliceHeight, $width, $sliceHeight);

            $averageColor = fp_averageColor($sliceData);
            $gradientColors[] = $averageColor;
            imagedestroy($sliceData);
        }

        imagedestroy($image);

        $gradientStops = array_map(function ($color, $index) use ($numSlices) {
            $position = ($index / ($numSlices - 1)) * 100;
            $darker_color = fp_darken_color($color, 0.5);
            return "{$color} " . intval($position) . "%";
        }, $gradientColors, array_keys($gradientColors));

        return 'linear-gradient(90deg, ' . implode(', ', $gradientStops) . ')';
    }
}


if (!function_exists('fp_darken_color')) {
    function fp_darken_color($rgb_color, $factor)
    {
        $rgb = sscanf($rgb_color, "rgb(%d, %d, %d)");
        $r = max(0, intval($rgb[0] * $factor));
        $g = max(0, intval($rgb[1] * $factor));
        $b = max(0, intval($rgb[2] * $factor));
        return "rgb({$r}, {$g}, {$b})";
    }
}

if (!function_exists('fp_averageColor')) {
    function fp_averageColor($image)
    {
        $width = imagesx($image);
        $height = imagesy($image);

        $r = $g = $b = $total = 0;

        for ($x = 0; $x < $width; $x++) {
            for ($y = 0; $y < $height; $y++) {
                $rgb = imagecolorat($image, $x, $y);
                $colors = imagecolorsforindex($image, $rgb);

                $r += $colors['red'];
                $g += $colors['green'];
                $b += $colors['blue'];
                $total++;
            }
        }

        $r = round($r / $total);
        $g = round($g / $total);
        $b = round($b / $total);

        return "rgb({$r}, {$g}, {$b})";
    }
}

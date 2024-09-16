<?php

if (!defined('ABSPATH')) exit;

// if 'FP_moviesHelpers' class does not exist, then include the file
// if (!class_exists('FP_moviesHelpers')) {
//     require_once FP_MOVIES_DIR . '/helper/fp_helpers.php';
// }

class CreatePostHelper extends FP_moviesHelpers
{

    function remove_text_accents($string)
    {
        if (!preg_match('/[\x80-\xff]/', $string)) {
            return $string; // If no special characters, return the string as is
        }

        if (class_exists('Normalizer', false)) {
            $string = Normalizer::normalize($string, Normalizer::FORM_D);
        }

        $string = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $string); // Transliterate characters to ASCII
        // error_log("remove_accents__string: " . print_r($string, TRUE));
        return $string;
    }

    function formatBytes($size, $precision = 2)
    {
        $base = log($size, 1024);
        $suffixes = array('', 'KB', 'MB', 'GB', 'TB');
        return round(pow(1024, $base - floor($base)), $precision) . ' ' . $suffixes[floor($base)];
    }

    function format_slug($slug)
    {
        // check if wordpress has the function remove_accents
        if (!function_exists('remove_accents')) {
            $slug = remove_accents($slug); // Remove accents and normalize characters
        } else {
            $slug = $this->remove_text_accents($slug); // Remove accents and normalize characters
        }
        $slug = preg_replace('/[\s]+/', '-', $slug); // Replace spaces with dashes
        $slug = preg_replace('/[^A-Za-z0-9\-]/', '', $slug); // Remove all characters except alphanumeric and dash
        $slug = preg_replace('/-+/', '-', $slug); // Replace multiple dashes with a single dash

        return strtolower($slug); // Ensure slug is all lower case for consistency
    }

    function format_title($title)
    {
        $title = preg_replace('/\s+/', ' ', $title);    // Normalize space (replace multiple spaces with a single space)
        // $title = preg_replace('/[^A-Za-z0-9 \-\']/', '', $title); // Remove special characters except dash and apostrophe
        // error_log("format_title__title: " . print_r($title, TRUE));
        return trim($title); // Trim any leading or trailing spaces
    }

    function format_simple($title)
    {
        $title = preg_replace('/\s+/', ' ', $title);    // Normalize space (replace multiple spaces with a single space)
        // replace all special characters with space
        $title = preg_replace('/[^A-Za-z0-9 \-\']/', ' ', $title); // Remove special characters except dash and apostrophe
        // replace french accents to normal
        $title = $this->remove_text_accents($title);
        // error_log("format_title__title: " . print_r($title, TRUE));
        return trim($title); // Trim any leading or trailing spaces

    }

    function replace_template_placeholders($template, $data)
    {
        $pattern = '/\{(\w+)\}/';
        // Replace all placeholders with corresponding data or an empty string if not set
        $keyMap = [
            'title' => 'title',
            't_title' => 't_title',
            'r_year' => 'release_year',
            'l_year' => 'latest_year',
            'quality' => 'quality',
            'audio' => 'audio',
            'c_audio' => 'c_audio',
            'c_subs' => 'c_subs',
            'network' => 'network',
            'separator' => 'separator',
            'p_type' => 'p_type',
            'p_type_2' => 'p_type_2',
            'resolution' => 'resolution'
        ];
        $replaced = preg_replace_callback($pattern, function ($matches) use ($data, $keyMap) {
            $templateKey = $matches[1];
            if (isset($keyMap[$templateKey])) {
                $dataKey = $keyMap[$templateKey];
                // Check if the data exists and is an array (specifically for 'audio')
                if (isset($data[$dataKey])) {
                    if (is_array($data[$dataKey])) {
                        // Join array elements with a hyphen
                        return implode('-', $data[$dataKey]);
                    } else {
                        // Return the data as is if it's not an array
                        return $data[$dataKey];
                    }
                }
            }
            // Return the original placeholder if no data is found
            return $matches[0];
        }, $template);

        // fp_log_error("REPLACED VALUE: " . print_r($replaced, TRUE));

        // error_log("replace_template_placeholders__replaced: " . print_r($replaced, TRUE));
        return $replaced;
    }

    function replace_template_placeholders_2($template, $data)
    {
        $pattern = '/\{(\w+)\}/';

        $replaced = preg_replace_callback($pattern, function ($matches) use ($data) {
            $templateKey = $matches[1];

            // Check if the data exists and is an array (specifically for 'audio')
            if (isset($data[$templateKey])) {
                if (is_array($data[$templateKey])) {
                    // Join array elements with a hyphen
                    return implode('-', $data[$templateKey]);
                } else {
                    // Return the data as is if it's not an array
                    return $data[$templateKey];
                }
            }

            // Return the original placeholder if no data is found
            return $matches[0];
        }, $template);

        return $replaced;
    }


    function normalize_to_array($input)
    {
        if (empty($input)) {
            return array();  // Return an empty array if input is empty
        }
        if (is_array($input)) {
            return $input;  // Return the input as is if it's already an array
        }
        $response = array_map('trim', explode(',', $input));  // Split by commas, trim each item, and return the array
        // error_log("normalize_to_array__response as String: " . print_r($response, TRUE));
        // error_log(json_encode($response));
        return $response;  // Split by commas, trim each item, and return the array
    }

    function process_taxonomy_terms($taxonomy_name, array $term_names)
    {
        $term_ids = [];

        foreach ($term_names as $term) {
            if (is_array($term) && isset($term['slug'])) {
                $term_slug = $term['slug'];  // Use the slug for lookup
                $term_title = $term['name']; // Use the name for the title
            } else {
                if ($term) {
                    $term_slug = $term;  // Otherwise, use the term as both slug and title
                    $term_title = ucwords($term_slug); // Capitalize for title
                } else {
                    continue;
                }
            }

            $term = term_exists($term_slug, $taxonomy_name);
            if ($term) {
                // Term exists, use its ID | save as integer
                $term_ids[] = (int)$term['term_id'];
            } else {
                // Term does not exist, create it
                $new_term = wp_insert_term(
                    $term_title,
                    $taxonomy_name,
                    ['slug' => $term_slug]
                );
                if (is_wp_error($new_term)) {
                    continue;
                }
                $term_ids[] = $new_term['term_id'];  // Use the new term ID
            }
        }

        return $term_ids;
    }



    function isTaxonomyTermExists($term_name, $taxonomy_name)
    {
        /**
         * Check if a term exists in a taxonomy
         * @param string $term_name
         * @param string $taxonomy_name
         * @return bool
         */
        $term = term_exists($term_name, $taxonomy_name);

        if ($term) {
            return true;
        } else {
            return false;
        }
    }


    function genre_id_to_name($type, $genres = array())
    {
        $api_key = get_option('mtg_tmdb_api_key');
        // $baseURL = "https://api.themoviedb.org/3";

        $args = array(
            'api_key' => $api_key,
        );
        $rmtapi = $this->RemoteJson($args, FP_MOVIES_TMDB_API_BASE_URL . '/genre/' . $type . '/list');
        $genre_list = $this->Disset($rmtapi, 'genres');

        if (!is_array($genre_list)) {
            $genre_list = [];
        }

        $genre_name = array();
        foreach ($genres as $genre) {
            foreach ($genre_list as $g) {
                if ($g['id'] == $genre) {
                    // convert these "Action & Adventure" in 2 separate -> "Action" and "Adventure"
                    if (strpos($g['name'], ' & ') !== false) {
                        $genre_name = array_merge($genre_name, explode(' & ', $g['name']));
                    } else {
                        $genre_name[] = $g['name'];
                    }
                }
            }
        }

        return $genre_name;
    }

    function fp_get_mime_type($file_path)
    {
        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime_type = finfo_file($finfo, $file_path);
            finfo_close($finfo);
            return $mime_type;
        }

        if (function_exists('mime_content_type')) {
            return mime_content_type($file_path);
        }

        $ext = pathinfo($file_path, PATHINFO_EXTENSION);
        $mime_types = [
            'jpg'  => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png'  => 'image/png',
            'gif'  => 'image/gif',
            'webp' => 'image/webp',
        ];
        return isset($mime_types[$ext]) ? $mime_types[$ext] : 'image/jpeg';
    }


    function set_featured_image_from_url($post_id, $tmdbData, $image_size = 'w780', $image_name = '', $check_existing = false)
    {
        include FP_MOVIES_DIR . 'helper/fp_get_img_gradient.php';
        $poster_path = $tmdbData['poster_path'];
        if (empty($poster_path)) return true;

        if ($check_existing) {
            $existing_poster_path = get_post_meta($post_id, 'poster_path', true);
            if ($existing_poster_path == $poster_path && has_post_thumbnail($post_id)) {
                return true;
            }
        }

        require_once(ABSPATH . 'wp-admin/includes/image.php');
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/media.php');

        $p_type = $tmdbData['p_type'];
        $title = $tmdbData['title'];

        $image_url = "https://image.tmdb.org/t/p/" . $image_size . $poster_path;
        $temp_file = download_url($image_url);

        if (is_wp_error($temp_file)) {
            // error_log('Error downloading image: ' . $temp_file->get_error_message());
            return false;
        }

        // check if the image name is set in the settings else we use original name
        if (empty($image_name)) {
            // if empty then use url $tempfile name
            $org_name = str_replace('/', '', $poster_path);
            $final_title = (!empty($org_name)) ? $org_name : $title . " " . $p_type;
        } else {
            $final_title = $this->replace_template_placeholders($image_name, $tmdbData);
            // $final_title = $this->format_title($this->replace_template_placeholders($image_name, $tmdbData));
        }

        // fp_log_error("final_title: " . print_r($final_title, TRUE));
        // final_title: The Boy in the Striped Pyjamas (2008)~PMZRips

        // Prepare an array of post data for the attachment.
        $file = [
            'name'     => $final_title . '.' . pathinfo($image_url, PATHINFO_EXTENSION),  // Ensure proper extension
            'type'     => $this->fp_get_mime_type($temp_file),
            'tmp_name' => $temp_file,
            'error'    => 0,
            'size'     => filesize($temp_file),
        ];

        // This function creates the attachment in the database.
        $attachment_id = media_handle_sideload($file, $post_id);

        if (is_wp_error($attachment_id)) {
            // @unlink($temp_file);  // Clean up temporary file
            @wp_delete_file($temp_file);
            // error_log('Error sideloading image: ' . $attachment_id->get_error_message());
            return false;
        } else {
            // update media title, caption, alt text, description with the $final_title
            wp_update_post([
                'ID'         => $attachment_id,
                'post_title' => $final_title,
                'post_excerpt' => $final_title,
                'post_content' => $final_title,
            ]);
        }

        // Set the image as the post's thumbnail.
        if (!set_post_thumbnail($post_id, $attachment_id)) {
            // error_log('Failed to set post thumbnail.');
            return false;
        }


        $gradient_color = fp_calculateImageGradient($image_url);

        try {
            update_post_meta($post_id, 'mtg_gradient_color', $gradient_color);
        } catch (Exception $e) {
            // error_log('Failed to set gradient color.');
            return true;
        }

        return true;
    }


    function getQualityFromString($name)
    {
        $quality = [
            '4k' => '2160p',
            '2160p' => '2160p',
            '2160' => '2160p',
            '4k' => '2160p',
            '1080p' => '1080p',
            '1080' => '1080p',
            '720p' => '720p',
            '720' => '720p',
            '480p' => '480p',
            '480' => '480p',
            '360p' => '360p',
            '360' => '360p',
            '240p' => '240p',
            '240' => '240p',
            '144p' => '144p',
            '144' => '144p',
        ];
        $name = strtolower($name);
        foreach ($quality as $key => $value) {
            if (strpos($name, $key) !== false) {
                return $value;
            }
        }
        return 'unknown';
    }


    function getAudioString($audioLangList, $name)
    {
        $audio_from_name = [
            'tam' => 'Tamil',
            'tamil' => 'Tamil',
            'tel' => 'Telugu',
            'telugu' => 'Telugu',
            'hin' => 'Hindi',
            'hindi' => 'Hindi',
            'eng' => 'English',
            'english' => 'English',
            'kan' => 'Kannada',
            'kannada' => 'Kannada',
            'mal' => 'Malayalam',
            'malayalam' => 'Malayalam',
            'jpn' => 'Japanese',
            'japanese' => 'Japanese',
            'mar' => 'Marathi',
            'marathi' => 'Marathi',
            'ben' => 'Bengali',
            'bengali' => 'Bengali',
            'pun' => 'Punjabi',
            'punjabi' => 'Punjabi',
            'pan' => 'Punjabi',
            'punjabi' => 'Punjabi',
            'kor' => 'Korean',
            'korean' => 'Korean',
            'chi' => 'Chinese',
            'chinese' => 'Chinese',
            'spa' => 'Spanish',
            'spanish' => 'Spanish',
            'fre' => 'French',
            'french' => 'French',
            'ger' => 'German',
            'german' => 'German',
            'ita' => 'Italian',
            'italian' => 'Italian',
            'rus' => 'Russian',
            'russian' => 'Russian',
            'ara' => 'Arabic',
            'arabic' => 'Arabic',
            'tur' => 'Turkish',
            'turkish' => 'Turkish',
        ];
        if (empty($audioLangList)) {
            $name_split = preg_split('/[\s,.-]+/', $name);
            foreach ($name_split as $name_part) {
                $name_part_lower = strtolower($name_part);
                if (isset($audio_from_name[$name_part_lower])) {
                    $audioLangList[] = $audio_from_name[$name_part_lower];
                }
            }
        } else {
            foreach ($audioLangList as $key => &$value) {
                if (empty($value) || in_array($value, ['unknown', 'und', 'unspecified', 'Unknown'], true)) {
                    $name_split = preg_split('/[\s,.-]+/', $name);
                    foreach ($name_split as $name_part) {
                        $name_part_lower = strtolower($name_part);
                        if (isset($audio_from_name[$name_part_lower])) {
                            $value = $audio_from_name[$name_part_lower];
                            break;
                        }
                    }
                } else {
                    $value = getLocaleCodeForDisplayLanguage($value);
                }
            }
        }
        unset($value);

        return implode(" - ", $audioLangList);
    }

    function fetchFPdata($tmdb_id, $postType, $fpkey)
    {
        $args = array(
            'api_key' => $fpkey,
            'video_meta_data' => 'true',
            'unique'
        );

        $fp_apiType = get_option_with_fallback('mtg_fp_key_type', 'personal');
        if ($fp_apiType === 'personal') {
            $args['show_global_org_files'] = get_option_with_fallback('mtg_global_access', 'false') === 'on' ? 'true' : 'false';
        }
        $args['request_type'] = $fp_apiType;
        $fp_apiURL = FP_MOVIES_FP_BASE_URL . '/' . $postType . '/' . $tmdb_id;
        $json_fp = $this->RemoteJson($args, $fp_apiURL);
        if ($this->Disset($json_fp, 'statusCode') === 400) {
            wp_send_json_error(array('message' => 'FilePress: Invalid API Key'), 400);
            return;
        }
        if (is_array($json_fp) && $this->Disset($json_fp, 'status_code') && count($json_fp['data']['files']) < 1) {
            return false;
        }
        $fp_data = $this->Disset($json_fp, 'data');
        $files = $this->Disset($fp_data, 'files');
        return $files;
    }

    function get_network_details($name_split)
    {

        $network_from_name = [
            'netflix' => 'nf',
            'nf' => 'nf',
            'netflix movie' => 'nf',
            'netflix original' => 'nf',
            'netflixseries' => 'nf',
            'netflixfilm' => 'nf',
            'amazon prime' => 'amzn',
            'amazon prime video' => 'amzn',
            'amazon primevideo' => 'amzn',
            'prime video' => 'amzn',
            'primevideo' => 'amzn',
            'prime' => 'amzn',
            'amzn' => 'amzn',
            'amzn mini tv' => 'amzn',
            'mini tv' => 'amzn',
            'amzn mini-tv' => 'amzn',
            'mini-tv' => 'amzn',
            'hotstar' => 'hs',
            'hs' => 'hs',
            'hotstarmovie' => 'hs',
            'hotstarseries' => 'hs',
            'disney hotstar' => 'hs',
            'disney+' => 'dsnp',
            'disneyplus' => 'dsnp',
            'disney plus' => 'dsnp',
            'dsnp' => 'dsnp',
            'disney' => 'dsnp',
            'disneyplushotstar' => 'dsnp',
            'zee5' => 'zee5',
            'zee5original' => 'zee5',
            'zee5series' => 'zee5',
            'zee5film' => 'zee5',
            'sony liv' => 'sonyliv',
            'sonyliv' => 'sonyliv',
            'sony' => 'sonyliv',
            'sonylivseries' => 'sonyliv',
            'sonylivfilm' => 'sonyliv',
            'voot' => 'voot',
            'voot select' => 'voot',
            'chtv' => 'chtv',
            'chaupal' => 'chtv',
            'vootselect' => 'voot',
            'vootseries' => 'voot',
            'vootfilm' => 'voot',
            'jio cinema' => 'jio',
            'jiocinema' => 'jio',
            'jio' => 'jio',
            'jiocinemafilm' => 'jio',
            'jiocinemaseries' => 'jio',
            'mx player' => 'mx',
            'mxplayer' => 'mx',
            'mx' => 'mx',
            'mxplayertv' => 'mx',
            'mxplayermovie' => 'mx',
            'alt balaji' => 'altbalaji',
            'altbalaji' => 'altbalaji',
            'alt' => 'altbalaji',
            'altseries' => 'altbalaji',
            'ullu' => 'ullu',
            'ullu app' => 'ullu',
            'ulluapp' => 'ullu',
            'ullumovie' => 'ullu',
            'ulluseries' => 'ullu',
            'crunchyroll' => 'crunchyroll',
            'crunchy roll' => 'crunchyroll',
            'crunchyrollanime' => 'crunchyroll',
            'crunchyrolldubbed' => 'crunchyroll',
            'hulu' => 'hulu',
            'huluoriginal' => 'hulu',
            'huluseries' => 'hulu',
            'hulufilm' => 'hulu',
            'hbo' => 'hbo',
            'hbomax' => 'hbo',
            'hbooriginal' => 'hbo',
            'hboseries' => 'hbo',
            'hbofilm' => 'hbo',
            'hoichoi' => 'hoichoi',
            'hoichoitv' => 'hoichoi',
            'hoichoitvseries' => 'hoichoi',
            'tvf' => 'tvf',
            'tvf play' => 'tvf',
            'tvfplay' => 'tvf',
            'acorn tv' => 'acorntv',
            'acorntv' => 'acorntv',
            'acorn' => 'acorntv',
            'the cw' => 'thecw',
            'cw' => 'cw',
            'the cw network' => 'thecw',
            'cwnetwork' => 'thecw',
            'cbs' => 'cbs',
            'cbs all access' => 'cbs',
            'cbsallaccess' => 'cbs',
            'book my show' => 'bms',
            'bookmyshow' => 'bms',
            'bms' => 'bms',
            'bmsstream' => 'bms',
            'bmsstreaming' => 'bms',
            'atv' => 'atvp',
            'atvp' => 'atvp',
            'apple tv+' => 'atvp',
            'appletv+' => 'atvp',
            'max' => 'hmax',
            'hbo max' => 'max',
            'hbo max original' => 'max',
            'maxoriginal' => 'max',
            'abc' => 'abc',
            'aubc' => 'aubc',
            'as' => 'as',
            'apps' => 'apps',
            'bbc' => 'bbc',
            'bcore' => 'bcore',
            'bluray' => 'bluray',
            'bd' => 'bluray',
            'boom' => 'boom',
            'cbc' => 'cbc',
            'cc' => 'cc',
            'cnlp' => 'cnlp',
            'crav' => 'crav',
            'criterion' => 'criterion',
            'dcu' => 'dcu',
            'dscp' => 'dscp',
            'dsny' => 'dsny',
            'fbwatch' => 'fbwatch',
            'free' => 'free',
            'fox' => 'fox',
            'gplay' => 'gplay',
            'hmax' => 'hmax',
            'hplay' => 'hplay',
            'htsr' => 'htsr',
            'ip' => 'ip',
            'it' => 'it',
            'jc' => 'jc',
            'lgp' => 'lgp',
            'life' => 'life',
            'ma' => 'ma',
            'mmax' => 'mmax',
            'mtv' => 'mtv',
            'mubi' => 'mubi',
            'nbc' => 'nbc',
            'nick' => 'nick',
            'oar' => 'oar',
            'pcok' => 'pcok',
            'pmtp' => 'pmtp',
            'pf' => 'pf',
            'red' => 'red',
            'roku' => 'roku',
            'saina' => 'saina',
            'sp' => 'sp',
            'sho' => 'sho',
            'ss' => 'ss',
            'snxt' => 'snxt',
            'stan' => 'stan',
            'stz' => 'stz',
            'tbs' => 'tbs',
            'tk' => 'tk',
            'tvnz' => 'tvnz',
            'wtch' => 'wtch',
            'abma' => 'abma',
            'adn' => 'adn',
            'animax' => 'animax',
            'ao' => 'ao',
            'aha' => 'aha',
            'at-x' => 'at-x',
            'baha' => 'baha',
            'b-global' => 'b-global',
            'bstation' => 'b-global',
            'bsp' => 'bsp',
            'nhk-bsp' => 'bsp',
            'bs4' => 'bs4',
            'bs5' => 'bs5',
            'ex-bs' => 'bs5',
            'bs-ex' => 'bs5',
            'bs6' => 'bs6',
            'bs7' => 'bs7',
            'bsj' => 'bs7',
            'bs-tx' => 'bs7',
            'bs8' => 'bs8',
            'bs-fuji' => 'bs8',
            'bs11' => 'bs11',
            'bs12' => 'bs12',
            'cr' => 'cr',
            'cs-fuji one' => 'cs-fuji one',
            'cx' => 'cx',
            'dmm' => 'dmm',
            'ex' => 'ex',
            'cs3' => 'cs3',
            'ex-cs1' => 'cs3',
            'cs-ex1' => 'cs3',
            'csa' => 'cs3',
            'fod' => 'fod',
            'funi' => 'funi',
            'hidive' => 'hidive',
            'hidi' => 'hidive',
            'kbc' => 'kbc',
            'm-on!' => 'm-on!',
            'nhkg' => 'nhkg',
            'nhke' => 'nhke',
            'ntv' => 'ntv',
            'tx' => 'tx',
            'unxt' => 'unxt',
            'vmax' => 'vmax',
            'u-next' => 'unxt',
            'waka' => 'waka',
            'wowow' => 'wowow',
            'ytv' => 'ytv',
            'iDragon' => 'iDragon',
            'vroot' => 'vroot',
            'shemaroo' => 'shemaroo',
        ];

        $common_networks = [
            'vroot' => [
                'name' => 'VRoot',
                'slug' => 'vroot',
            ],
            'Shemaroo' => [
                'name' => 'Shemaroo',
                'slug' => 'shemaroo',
            ],
            'nf' =>
            [
                'name' => 'NetFlix',
                'slug' => 'netflix',
            ],
            'amzn' =>
            [
                'name' => 'Amazon Prime Video',
                'slug' => 'amazon-prime-video',
            ],
            'hs' =>
            [
                'name' => 'Hotstar',
                'slug' => 'hotstar',
            ],
            'dsnp' =>
            [
                'name' => 'Disney+',
                'slug' => 'disney-plus',
            ],
            'zee5' =>
            [
                'name' => 'ZEE5',
                'slug' => 'zee5',
            ],
            'sonyliv' =>
            [
                'name' => 'Sony Liv',
                'slug' => 'sony-liv',
            ],
            'voot' =>
            [
                'name' => 'VOOT',
                'slug' => 'voot',
            ],
            'jio' =>
            [
                'name' => 'JioCinema',
                'slug' => 'jio-cinema',
            ],
            'mx' =>
            [
                'name' => 'Tokyo MX',
                'slug' => 'tokyo-mx',
            ],
            'altbalaji' =>
            [
                'name' => 'ALTBalaji',
                'slug' => 'alt-balaji',
            ],
            'ullu' =>
            [
                'name' => 'Ullu',
                'slug' => 'ullu',
            ],
            'crunchyroll' =>
            [
                'name' => 'Crunchyroll',
                'slug' => 'crunchyroll',
            ],
            'hulu' =>
            [
                'name' => 'Hulu Networks',
                'slug' => 'hulu',
            ],
            'hbo' =>
            [
                'name' => 'HBO',
                'slug' => 'hbo',
            ],
            'hoichoi' =>
            [
                'name' => 'Hoichoi',
                'slug' => 'hoichoi',
            ],
            'tvf' =>
            [
                'name' => 'TVF',
                'slug' => 'tvf',
            ],
            'acorntv' =>
            [
                'name' => 'Acorn TV',
                'slug' => 'acorn-tv',
            ],
            'aha' =>
            [
                'name' => 'Aha',
                'slug' => 'aha',
            ],
            'thecw' =>
            [
                'name' => 'The CW',
                'slug' => 'the-cw',
            ],
            'cbs' =>
            [
                'name' => 'CBS Corporation',
                'slug' => 'cbs',
            ],
            'chtv' =>
            [
                'name' => 'Chaupal',
                'slug' => 'chaupal',
            ],
            'bms' =>
            [
                'name' => 'BookMyShow',
                'slug' => 'bookmyshow',
            ],
            'atvp' =>
            [
                'name' => 'Apple TV+',
                'slug' => 'apple-tv-plus',
            ],
            'max' =>
            [
                'name' => 'MAX',
                'slug' => 'max',
            ],
            'abc' =>
            [
                'name' => 'American Broadcasting Company',
                'slug' => 'abc',
            ],
            'aubc' =>
            [
                'name' => 'Australian Broadcasting Corporation',
                'slug' => 'aubc',
            ],
            'as' =>
            [
                'name' => 'Adult Swim',
                'slug' => 'adult-swim',
            ],
            'apps' =>
            [
                'name' => 'Disney Plus MENA',
                'slug' => 'disney-plus-mena',
            ],
            'bbc' =>
            [
                'name' => 'British Broadcasting Corporation',
                'slug' => 'bbc',
            ],
            'bcore' =>
            [
                'name' => 'Sony Pictures Core',
                'slug' => 'sony-pictures-core',
            ],
            'bluray' =>
            [
                'name' => 'BluRay Disc',
                'slug' => 'blu-ray-disc',
            ],
            'boom' =>
            [
                'name' => 'Boomerang',
                'slug' => 'boomerang',
            ],
            'cbc' =>
            [
                'name' => 'Canadian Broadcasting Corporation',
                'slug' => 'cbc',
            ],
            'cc' =>
            [
                'name' => 'Comedy Central',
                'slug' => 'comedy-central',
            ],
            'cnlp' =>
            [
                'name' => 'Canal+',
                'slug' => 'canal-plus',
            ],
            'crav' =>
            [
                'name' => 'Crave',
                'slug' => 'crave',
            ],
            'criterion' =>
            [
                'name' => 'The Criterion Collection',
                'slug' => 'criterion',
            ],
            'cw' =>
            [
                'name' => 'The CW',
                'slug' => 'the-cw',
            ],
            'dcu' =>
            [
                'name' => 'DC Universe',
                'slug' => 'dc-universe',
            ],
            'dscp' =>
            [
                'name' => 'Discovery+',
                'slug' => 'discovery-plus',
            ],
            'dsny' =>
            [
                'name' => 'Disney Networks',
                'slug' => 'disney-networks',
            ],
            'fbwatch' =>
            [
                'name' => 'Facebook Watch',
                'slug' => 'facebook-watch',
            ],
            'free' =>
            [
                'name' => 'Freeform',
                'slug' => 'freeform',
            ],
            'fox' =>
            [
                'name' => 'Fox Broadcasting Company',
                'slug' => 'fox',
            ],
            'gplay' =>
            [
                'name' => 'Google Play',
                'slug' => 'google-play',
            ],
            'hmax' =>
            [
                'name' => 'HBOMax',
                'slug' => 'hbo-max',
            ],
            'hplay' =>
            [
                'name' => 'Hungama Play',
                'slug' => 'hungama-play',
            ],
            'htsr' =>
            [
                'name' => 'Hotstar',
                'slug' => 'hotstar',
            ],
            'ip' =>
            [
                'name' => 'BBC iPlayer',
                'slug' => 'bbc-iplayer',
            ],
            'it' =>
            [
                'name' => 'iTunes',
                'slug' => 'itunes',
            ],
            'jc' =>
            [
                'name' => 'JioCinema',
                'slug' => 'jio-cinema',
            ],
            'lgp' =>
            [
                'name' => 'Lionsgate Play',
                'slug' => 'lionsgate-play',
            ],
            'life' =>
            [
                'name' => 'Lifetime',
                'slug' => 'lifetime',
            ],
            'ma' =>
            [
                'name' => 'Movies Anywhere',
                'slug' => 'movies-anywhere',
            ],
            'mmax' =>
            [
                'name' => 'ManoramaMAX',
                'slug' => 'manoramamax',
            ],
            'mtv' =>
            [
                'name' => 'MTV Networks',
                'slug' => 'mtv',
            ],
            'mubi' =>
            [
                'name' => 'Mubi',
                'slug' => 'mubi',
            ],
            'nbc' =>
            [
                'name' => 'National Broadcasting Company',
                'slug' => 'nbc',
            ],
            'nick' =>
            [
                'name' => 'Nickelodeon',
                'slug' => 'nickelodeon',
            ],
            'oar' =>
            [
                'name' => 'Original Aspect Ratio',
                'slug' => 'original-aspect-ratio',
            ],
            'pcok' =>
            [
                'name' => 'Peacock',
                'slug' => 'peacock',
            ],
            'pmtp' =>
            [
                'name' => 'Paramount Plus',
                'slug' => 'paramount-plus',
            ],
            'pf' =>
            [
                'name' => 'PureFlix',
                'slug' => 'pureflix',
            ],
            'red' =>
            [
                'name' => 'YouTube Premium',
                'slug' => 'youtube-premium',
            ],
            'roku' =>
            [
                'name' => 'Roku',
                'slug' => 'roku',
            ],
            'saina' =>
            [
                'name' => 'SainaPlay',
                'slug' => 'saina-play',
            ],
            'sp' =>
            [
                'name' => 'SainaPlay',
                'slug' => 'saina-play',
            ],
            'sho' =>
            [
                'name' => 'Showtime',
                'slug' => 'showtime',
            ],
            'ss' =>
            [
                'name' => 'Simply South',
                'slug' => 'simply-south',
            ],
            'snxt' =>
            [
                'name' => 'SunNXT',
                'slug' => 'sun-nxt',
            ],
            'stan' => ['name' => 'Stan', 'slug' => 'stan',],
            'stz' => ['name' => 'STARZ', 'slug' => 'starz',],
            'tbs' => ['name' => 'Turner Broadcasting System', 'slug' => 'turner-broadcasting-system',],
            'tk' => ['name' => 'Tentkotta', 'slug' => 'tentkotta',],
            'tvnz' => ['name' => 'TVNZ', 'slug' => 'tvnz',],
            'wtch' => ['name' => 'Watcha', 'slug' => 'watcha',],
            'abma' => ['name' => 'Abema', 'slug' => 'abema',],
            'adn' => ['name' => 'Anime Digital Network (French)', 'slug' => 'anime-digital-network',],
            'animax' => ['name' => 'Animax', 'slug' => 'animax',],
            'ao' => ['name' => 'Anime Onegai (Spanish)', 'slug' => 'anime-onegai',],
            'at-x' => ['name' => 'Anime Theatre X', 'slug' => 'anime-theatre-x',],
            'baha' => ['name' => 'Bahamut Animation Madness (Chinese)', 'slug' => 'bahamut-animation-madness',],
            'b-global' => ['name' => 'Bilibili', 'slug' => 'bilibili',],
            'bsp' => ['name' => 'NHK BS Premium', 'slug' => 'nhk-bs-premium',],
            'bs4' => ['name' => 'BS Nippon TV', 'slug' => 'bs-nippon-tv',],
            'bs5' => ['name' => 'BS TV Asahi', 'slug' => 'bs-tv-asahi',],
            'bs6' => ['name' => 'BS-TBS', 'slug' => 'bs-tbs',],
            'bs7' => ['name' => 'BS TV TOKYO', 'slug' => 'bs-tv-tokyo',],
            'bs8' => ['name' => 'BS Fuji', 'slug' => 'bs-fuji',],
            'bs11' => ['name' => 'Nippon BS Broadcasting', 'slug' => 'nippon-bs-broadcasting',],
            'bs12' => ['name' => 'BS12 トゥエルビ', 'slug' => 'bs12',],
            'cr' => ['name' => 'Crunchyroll', 'slug' => 'crunchyroll',],
            'cs-fuji one' => ['name' => 'Fuji TV One', 'slug' => 'fuji-tv-one',],
            'cx' => ['name' => 'Fuji TV', 'slug' => 'fuji-tv',],
            'dmm' => ['name' => 'DMM', 'slug' => 'dmm',],
            'ex' => ['name' => 'TV Asahi', 'slug' => 'tv-asahi',],
            'cs3' => ['name' => 'TV Asahi Channel 1', 'slug' => 'tv-asahi-channel-1',],
            'fod' => ['name' => 'Fuji TV On Demand', 'slug' => 'fuji-tv-on-demand',],
            'funi' => ['name' => 'Funimation', 'slug' => 'funimation',],
            'hidive' => ['name' => 'HIDIVE', 'slug' => 'hidive',],
            'kbc' => ['name' => 'Kyushu Asahi Broadcasting', 'slug' => 'kyushu-asahi-broadcasting',],
            'm-on!' => ['name' => 'MUSIC ON! TV', 'slug' => 'music-on-tv',],
            'nhkg' => ['name' => 'NHK General TV', 'slug' => 'nhk-general-tv',],
            'nhke' => ['name' => 'NHK Education TV', 'slug' => 'nhk-education-tv',],
            'ntv' => ['name' => 'Nippon TV', 'slug' => 'nippon-tv',],
            'tx' => ['name' => 'TV TOKYO', 'slug' => 'tv-tokyo',],
            'unxt' => ['name' => 'U-NEXT', 'slug' => 'u-next',],
            'vmax' => ['name' => 'VMAX', 'slug' => 'vivamax',],
            'waka' => ['name' => 'Wakanim', 'slug' => 'wakanim',],
            'wowow' => ['name' => 'Wowow', 'slug' => 'wowow',],
            'ytv' => ['name' => 'Yomiuri TV', 'slug' => 'yomiuri-tv',],
            'iDragon' => ['name' => 'iDragon', 'slug' => 'idragon',],
        ];


        // $name_split = preg_split('/[\s._-]+/', strtolower($name));
        $result = [];

        foreach ($name_split as $part) {
            // fp_log_error("Current part: " . $part);
            $part_lower = strtolower($part);
            if (array_key_exists($part_lower, $network_from_name)) {
                $slug = $network_from_name[$part_lower];
                if (array_key_exists($slug, $common_networks)) {
                    $result[] = [
                        'name' => $common_networks[$slug]['name'],
                        'slug' => $common_networks[$slug]['slug'],
                    ];
                }
            }
        }

        return $result;
    }

    function processFPData($fpData)
    {
        $single_screenshot = [];
        $splash_screenshot = [];
        $resolution = [];
        $audios = [];
        $subtitles = [];
        $qualities = [];
        $networks = [];

        $single_screenshot_quality_sorted = array();
        $splash_screenshot_quality_sorted = array();

        $size_quality_type = array();

        $quality_from_name = [
            'bluray'            =>           'BluRay',
            'blu ray'           =>           'BluRay',
            'blu-ray'           =>           'BluRay',
            'blu.ray'           =>           'BluRay',
            'brrip'             =>           'BRRip',
            'br rip'            =>           'BRRip',
            'br.rip'            =>           'BRRip',
            'brrip'             =>           'BRRip',

            'camrip'            =>           'CAMRip',
            'cam rip'           =>           'CAMRip',
            'cam-rip'           =>           'CAMRip',
            'cam.rip'           =>           'CAMRip',

            'dvdrip'            =>           'DVDRip',
            'dvd rip'           =>           'DVDRip',
            'dvd-rip'           =>           'DVDRip',
            'dvd.rip'           =>           'DVDRip',
            'dvdrip'            =>           'DVDRip',

            'dvdscr'            =>           'DVDScr',
            'dvd scr'           =>           'DVDScr',
            'dvd-scr'           =>           'DVDScr',
            'dvd.scr'           =>           'DVDScr',
            'dvdscr'            =>           'DVDScr',

            'imax'              =>           'IMAX',

            'hdcam'             =>           'HDCAM',
            'hd cam'            =>           'HDCAM',
            'hd.cam'            =>           'HDCAM',
            'hd-cam'            =>           'HDCAM',
            'hdts'              =>           'HDTS',
            'hd.ts'             =>           'HDTS',
            'hd ts'             =>           'HDTS',
            'hdtc'              =>           'HDTC',
            'hd-tc'             =>           'HDTC',
            'hd tc'             =>           'HDTC',
            'hd.tc'             =>           'HDTC',
            'hdtv'              =>           'HDTV',
            'hd tv'             =>           'HDTV',
            'hd-tv'             =>           'HDTV',
            'hd.tv'             =>           'HDTV',
            'hdtv'              =>           'HDTV',
            'hdrip'             =>           'HDRip',
            'hd rip'            =>           'HDRip',
            'hd-rip'            =>           'HDRip',
            'hd.rip'            =>           'HDRip',
            'hdrip'             =>           'HDRip',
            'hq s'              =>           'HQ-S',
            'hq-s'              =>           'HQ-S',
            'hq.s'              =>           'HQ-S',
            'hq s'              =>           'HQ-S',

            'pre-dvd'           =>           'PreDVD',
            'pre dvd'           =>           'PreDVD',
            'pre.dvd'           =>           'PreDVD',
            'pre-dvd'           =>           'PreDVD',

            'web-dl'            =>           'WEB-DL',
            'web dl'            =>           'WEB-DL',
            'web.dl'            =>           'WEB-DL',
            'webdl'             =>           'WEB-DL',
            'webrip'            =>           'WEBRip',
            'web rip'           =>           'WEBRip',
            'web-rip'           =>           'WEBRip',
            'web.rip'           =>           'WEBRip',
            'webrip'            =>           'WEBRip',

        ];


        $audio_from_name = [
            'tam' => 'Tamil',
            'tamil' => 'Tamil',
            'tel' => 'Telugu',
            'telugu' => 'Telugu',
            'hin' => 'Hindi',
            'hindi' => 'Hindi',
            'eng' => 'English',
            'english' => 'English',
            'kan' => 'Kannada',
            'kannada' => 'Kannada',
            'mal' => 'Malayalam',
            'malayalam' => 'Malayalam',
            'jpn' => 'Japanese',
            'japanese' => 'Japanese',
            'mar' => 'Marathi',
            'marathi' => 'Marathi',
            'ben' => 'Bengali',
            'bengali' => 'Bengali',
            'pun' => 'Punjabi',
            'punjabi' => 'Punjabi',
            'pan' => 'Punjabi',
            'punjabi' => 'Punjabi',
            'kor' => 'Korean',
            'korean' => 'Korean',
            'chi' => 'Chinese',
            'chinese' => 'Chinese',
            'spa' => 'Spanish',
            'spanish' => 'Spanish',
            'fre' => 'French',
            'french' => 'French',
            'ger' => 'German',
            'german' => 'German',
            'ita' => 'Italian',
            'italian' => 'Italian',
            'rus' => 'Russian',
            'russian' => 'Russian',
            'ara' => 'Arabic',
            'arabic' => 'Arabic',
            'tur' => 'Turkish',
            'turkish' => 'Turkish',
        ];

        foreach ($fpData as $file) {
            $resolution[$file['quality'] . 'p'] = true;

            $size_quality_type[$file['quality']][] = $file['size'];

            if (isset($file['name'])) {
                $name_split = preg_split('/[\s,.-]+/', $file['name']);
            } else {
                $name_split = [];
            }

            if (!empty($file['images'])) {
                foreach ($file['images'] as $image) {
                    if (strpos($image, 'https://filepress.imgpress.xyz') === false) {
                        // $single_screenshot[] = $image;
                        $single_screenshot_quality_sorted[$file['quality']][] = $image;
                    }
                }
            }
            if (!empty($file['splashImg']) && strpos($file['splashImg'], 'https://filepress.imgpress.xyz') === false) {
                // $splash_screenshot[] = $file['splashImg'];
                $splash_screenshot_quality_sorted[$file['quality']][] = $file['splashImg'];
            }

            $fileDetails = $file['videoFileDetails'];
            if (!empty($fileDetails['audioLangList']) && is_array($fileDetails['audioLangList'])) {
                foreach ($fileDetails['audioLangList'] as $audioLang) {
                    if (in_array($audioLang, ['unknown', 'und', 'unspecified', 'Unknown'], true)) {
                        // $name_split = preg_split('/[\s,.-]+/', $file['name']);
                        foreach ($name_split as $name) {
                            $name_lowerCase = strtolower($name);
                            if (isset($audio_from_name[$name_lowerCase])) {
                                $translatedLang = $audio_from_name[$name_lowerCase];
                            }
                        }
                    } else {
                        $translatedLang = getLocaleCodeForDisplayLanguage($audioLang);
                    }
                    if (!empty($translatedLang)) {
                        $audios[$translatedLang] = true;
                    }
                }
            }

            if (!empty($fileDetails['subLangList']) && is_array($fileDetails['subLangList'])) {
                foreach ($fileDetails['subLangList'] as $subLang) {
                    $translatedLang = getLocaleCodeForDisplayLanguage($subLang);
                    if (!empty($translatedLang)) {
                        $subtitles[$translatedLang] = true;
                    }
                }
            }

            // if array not empty
            if (!empty($name_split)) {
                $networks = $this->get_network_details($name_split);
                // fp_log_error("NETWORK -- : " . print_r($networks, TRUE));

                foreach ($name_split as $name) {
                    $name_lowerCase = strtolower($name);
                    if (isset($quality_from_name[$name_lowerCase])) {
                        $qualities[$quality_from_name[$name_lowerCase]] = true;
                    }
                }
            }
        }

        // error_log("SIZE_QUALITY_TYPE: " . print_r($size_quality_type, TRUE));
        // error_log("SINGLE_SCREENSHOT_QUALITY_SORTED: " . print_r($single_screenshot_quality_sorted, TRUE));
        // error_log("SPLASH_SCREENSHOT_QUALITY_SORTED: " . print_r($splash_screenshot_quality_sorted, TRUE));

        // $single_screenshot will have 10 images   ||  from highest quality to lowest quality
        krsort($single_screenshot_quality_sorted);
        foreach ($single_screenshot_quality_sorted as $quality => $images) {
            if (count($single_screenshot) >= 10) {
                break;
            }
            $single_screenshot = array_merge($single_screenshot, $images);
        }
        krsort($splash_screenshot_quality_sorted);
        foreach ($splash_screenshot_quality_sorted as $quality => $images) {
            if (count($splash_screenshot) >= 5) {
                break;
            }
            $splash_screenshot = array_merge($splash_screenshot, $images);
        }

        // error_log("SIZE_SCREENSHOT: " . print_r($single_screenshot, TRUE));
        // error_log("SPLASH_SCREENSHOT: " . print_r($splash_screenshot, TRUE));

        $size_480p = $size_quality_type['480'] ?? '';
        if (!empty($size_480p) && is_array($size_480p)) {
            $size_480p = array_sum($size_480p) / count($size_480p);
            $size_480p = $this->formatBytes($size_480p);
        } else {
            $size_480p = '';    // 400 MB
        }
        // error_log("SIZE_480P: " . print_r($size_480p, TRUE));

        $size_720p = $size_quality_type['720'] ?? '';
        if (!empty($size_720p) && is_array($size_720p)) {
            $size_720p = array_sum($size_720p) / count($size_720p);
            $size_720p = $this->formatBytes($size_720p);
        } else {
            $size_720p = '';    // 1 GB
        }
        // error_log("SIZE_720P: " . print_r($size_720p, TRUE));

        $size_1080p = $size_quality_type['1080'] ?? '';
        if (!empty($size_1080p) && is_array($size_1080p)) {
            $size_1080p = array_sum($size_1080p) / count($size_1080p);
            $size_1080p = $this->formatBytes($size_1080p);
        } else {
            $size_1080p = '';   // 1.8 GB
        }
        // error_log("SIZE_1080P: " . print_r($size_1080p, TRUE));

        $size_2160p = $size_quality_type['2160'] ?? '';
        if (!empty($size_2160p) && is_array($size_2160p)) {
            $size_2160p = array_sum($size_2160p) / count($size_2160p);
            $size_2160p = $this->formatBytes($size_2160p);
        } else {
            // fallback avg size
            $size_2160p = ''; //5 GB
        }
        // error_log("SIZE_2160P: " . print_r($size_2160p, TRUE));

        // error_log("Single Screenshot Count" . count($single_screenshot));
        // error_log("Splash Screenshot Count" . count($splash_screenshot));

        // shuffle($single_screenshot);
        $single_screenshot = array_slice($single_screenshot, 0, 10);
        $single_screenshot = implode("\n", $single_screenshot);

        // shuffle($splash_screenshot);
        $splash_screenshot = array_slice($splash_screenshot, 0, 5);
        $splash_screenshot = implode("\n", $splash_screenshot);

        // error_log("Single Screenshot Count" . $single_screenshot);
        // error_log("Splash Screenshot Count" . $splash_screenshot);

        $resolution = array_keys($resolution);
        $audios = array_keys($audios);
        $subtitles = array_keys($subtitles);
        $qualities = array_keys($qualities);

        return [
            'single_screenshot' => $single_screenshot,
            'splash_screenshot' => $splash_screenshot,
            'resolution' => $resolution,
            'audios' => $audios,
            'subtitles' => $subtitles,
            'size_480p' => $size_480p,
            'size_720p' => $size_720p,
            'size_1080p' => $size_1080p,
            'size_2160p' => $size_2160p,
            'qualities' => $qualities,
            'networks' => $networks,
        ];
    }

    function fetchTMDBdata($tmdb_id, $postType, $tmdbkey, $apilang = 'en-US')
    {
        $args = array(
            'api_key' => $tmdbkey,
            'language' => $apilang,
        );
        $append_to_response = '&append_to_response=external_ids,videos,credits';
        $tmdb_apiURL = FP_MOVIES_TMDB_API_BASE_URL . '/' . $postType . '/' . $tmdb_id;
        $json_tmdb = $this->RemoteJson($args, $tmdb_apiURL, $append_to_response);
        // Verify status code
        if ($this->Disset($json_tmdb, 'status_code')) {
            $response = array(
                'response' => false,
                'message' => $this->Disset($json_tmdb, 'status_message')
            );
            wp_send_json_error($response);
        }

        if ($postType === 'movie') {
            $title = $this->Disset($json_tmdb, 'title');
            $release_date = $this->Disset($json_tmdb, 'release_date');
            $release_years = array();
            if (!empty($release_date)) {
                $release_years = substr($release_date, 0, 4);
                $release_years = array($release_years);
            }
            $imdb_id = $this->Disset($json_tmdb, 'imdb_id');
        } else {
            $title = $this->Disset($json_tmdb, 'name');
            $release_date = $this->Disset($json_tmdb, 'first_air_date');
            $all_release_date = $this->Disset($json_tmdb, 'seasons');
            $release_years = array();
            if (is_array($all_release_date) && !empty($all_release_date)) {
                foreach ($all_release_date as $season) {
                    $date = $this->Disset($season, 'air_date');
                    if (!empty($date)) {
                        $year = substr($date, 0, 4);
                        $release_years[$year] = $year;
                    }
                }
                $release_years = array_values($release_years);
                sort($release_years, SORT_STRING);
            }
            $external_ids = $this->Disset($json_tmdb, 'external_ids');
            $imdb_id = $this->Disset($external_ids, 'imdb_id');
        }

        $trailer_key = '';
        $trailer = $this->Disset($json_tmdb, 'videos');
        if (!empty($trailer)) {
            $trailer = $this->Disset($trailer, 'results');
            if (!empty($trailer) && is_array($trailer)) {
                $trailer = array_filter($trailer, function ($video) {
                    return $video['site'] === 'YouTube';
                });
                if (!empty($trailer)) {
                    $trailer = array_values($trailer); // Reset keys of the filtered array
                    $trailer_key = $this->Disset($trailer[0], 'key');
                }
            }
        }

        $genres = $this->Disset($json_tmdb, 'genres');

        if (is_array($genres) && !empty($genres)) {
            $genre_names = array_map(function ($genre) {
                // return id of genre as int only
                return (int) $genre['id'];
            }, $genres);
        } else {
            $genre_names = array();
        }

        $adult = $this->Disset($json_tmdb, 'adult');

        $credits = $this->Disset($json_tmdb, 'credits');

        if (!empty($credits) && is_array($credits)) {
            $cast = $this->Disset($credits, 'cast');
            $crew = $this->Disset($credits, 'crew');

            if (!empty($cast) && is_array($cast)) {
                // $cast = array_slice($cast, 0, 5);
                $cast = array_map(function ($actor) {
                    return $actor['name'];
                }, $cast);
            } else {
                $cast = array();
            }

            if (!empty($crew) && is_array($crew)) {
                $crew = array_map(function ($member) {
                    // only Directing, Writing, Production
                    if (in_array($member['department'], ['Directing', 'Writing', 'Production'])) {
                        return $member['name'];
                    }
                }, $crew);
            } else {
                $crew = array();
            }
        }

        $collection = $this->Disset($json_tmdb, 'belongs_to_collection');
        if (!empty($collection)) {
            $collection_name = $this->Disset($collection, 'name');
        } else {
            $collection_name = '';
        }

        // vote average is 7 if no vote average and if vote count is less than 5 and vote average is 10 then put 7.5
        $vote_average = $this->Disset($json_tmdb, 'vote_average');
        $vote_count = $this->Disset($json_tmdb, 'vote_count');
        if (empty($vote_average) && $vote_count < 5) {
            $vote_average = 7;
        } elseif ($vote_average === 10 && $vote_count < 5) {
            $vote_average = 7.5;
        }

        $postData = array(
            'p_type' => $postType,
            'title' => $title,
            'genres' => $genre_names,
            'overview' => $this->Disset($json_tmdb, 'overview'),
            'poster_path' => $this->Disset($json_tmdb, 'poster_path'),
            'backdrop_path' => $this->Disset($json_tmdb, 'backdrop_path'),
            'vote_average' => $vote_average,
            'vote_count' => $vote_count,
            'release_date' => $release_date,
            'release_years' => $release_years,
            'release_year' => $release_years[0] ?? '',
            'latest_year' => end($release_years) ?? '',
            'tmdb_id' => $tmdb_id,
            'imdb_id' => $imdb_id,
            'tagline' => $this->Disset($json_tmdb, 'tagline'),
            'trailer' => $trailer_key,
            'cast' => (isset($cast)) ? $cast : array(),
            'crew' => (isset($crew)) ? $crew : array(),
            'collection' => $collection_name,
            'adult' => $adult,
        );

        if ($postType === 'tv') {
            $postData['seasons'] = $this->Disset($json_tmdb, 'number_of_seasons');
            $postData['episodes'] = $this->Disset($json_tmdb, 'number_of_episodes');
            $postData['last_air_date'] = $this->Disset($json_tmdb, 'last_air_date');
            $network = $this->Disset($json_tmdb, 'networks');
            $networks = array();
            if (is_array($network) && !empty($network)) {
                foreach ($network as $net) {
                    $networks[] = [
                        'name' => $net['name'],
                        'slug' => $net['name'],
                    ];
                }
            }
            $postData['networks'] = $networks;
        }

        return $postData;
    }

    function validate_array($array, $message = 'Invalid data')
    {
        if (!is_array($array) || empty($array)) {
            wp_send_json_error(array('message' => $message), 400);
            return false;
        }
        return true;
    }

    function validate_string($string, $message = 'Invalid data')
    {
        if (!is_string($string) || empty($string)) {
            wp_send_json_error(array('message' => $message), 400);
            return false;
        }
        return true;
    }
}

<?php

if (!defined('ABSPATH')) exit;

class FPMovieLinks extends CreatePostHelper
{
    function fp_movies_links($tmdb_id, $meta_data, $user_links_data = [])
    {
        if (empty($tmdb_id)) {
            // error_log('TMDB ID is empty. Cannot fetch movie links.');
            return '';
        }
        $template_settings = FP_Movies_Shortcodes::get_template_settings();
        $site_url = get_site_url(null, '', 'https');
        $link_site_url = $site_url . '/link/';
        $data = "INIT_STATE";
        // $tmdb_id = $meta_data['fp_tmdb'];
        $files_length = 0;
        // $user_download_link = get_option_with_fallback('mtg_template_download_baseURL', FP_DOWNLOAD_URL);
        $user_download_link = $this->get_arrayValue_with_fallback($template_settings, 'sDownload_BaseURL', FP_DOWNLOAD_URL);
        $user_download_link = rtrim($user_download_link, '/');  // if ends with / then remove ending /
        $filepressOutputDownloadUrl = $user_download_link . '/file/';
        $request_url = FP_MOVIES_FP_BASE_URL . '/movie/' . $tmdb_id;
        $request_url .= '?api_key=' . FP_MOVIES_FP_API_KEY;

        $fp_apiType = get_option_with_fallback('mtg_fp_key_type', 'personal');
        if ($fp_apiType === 'personal') {
            $mtg_global_access = get_option_with_fallback('mtg_global_access', 'false') === 'on' ? 'true' : 'false';
            $request_url .= '&show_global_org_files=' . $mtg_global_access;
        }
        $request_url .= '&video_meta_data=true&unique=true';
        $request_url .= '&request_type=' . $fp_apiType;

        // error_log('Request URL: ' . print_r($request_url, TRUE));
        // $start_time = microtime(true);
        if (function_exists('curl_version')) {
            $curl = curl_init($request_url);
            curl_setopt_array($curl, array(
                // CURLOPT_URL => $request_url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
            ));
            $response = curl_exec($curl);
        } else {
            $response = file_get_contents($request_url);
        }
        $data = json_decode($response, TRUE);
        $combined_data = [];
        if ($data['status']) {
            $files = $data['data']['files'];
            $files_length = count($files);
            for ($x = 0; $x < $files_length; $x++) {
                $elm = $files[$x];
                $name = $elm['name'];
                // remove extension from name
                $name = preg_replace('/\\.[^.\\s]{3,4}$/', '', $name);
                $size = formatBytes($elm['size']);
                // $audio = $elm['videoFileDetails'];
                // $audioLangList = $audio['audioLangList'];
                // $audio = $this -> getAudioString($audioLangList, $name);
                $audio = $elm['videoFileDetails'];
                $audio = $audio['audioList'];
                $audioLangList = array_map(function ($audio) {
                    return $audio['language'];
                }, $audio);
                $audio = $this->getAudioString($audioLangList ?? [], $name);
                $url = $filepressOutputDownloadUrl . $elm['_id'];
                $quality = $elm['quality'];
                if (empty($quality)) $quality = $this->getQualityFromString($name);
                if (empty($quality)) $quality = '144';
                $combined_data[] =  [
                    'name' => $name,
                    'size' => $size,
                    'audio' => $audio,
                    'url' => $url,
                    'quality' => $quality
                ];
            }
        }

        if (!empty($user_links_data) && is_array($user_links_data)) {
            foreach ($user_links_data as $link) {
                $name = $link['l_title'];
                // Replace {title} {t_title} {year} {quality} {audio}
                // {title} {t_title} {year} from post meta and {quality} {audio} from $link
                $pattern = '/\{(\w+)\}/';
                $replace_type = [
                    'title' => $meta_data['fp_title'],
                    't_title' => $meta_data['fp_title'],
                    'year' => $meta_data['fp_latest_year'] ?? '',
                    'quality' => $link['l_quality'] . 'p',
                    'audio' => $link['l_audio']
                ];
                $name = preg_replace_callback($pattern, function ($matches) use ($replace_type) {
                    // if match found in $replace_type then return that value else return same initial value
                    return $replace_type[$matches[1]] ?? $matches[0];
                }, $name);
                // error_log('User Link Name: ' . $name);
                $quality = $link['l_quality'];
                $quality = preg_replace('/p$/', '', $quality);
                if (empty($quality)) $quality = '144';
                $combined_data[] = [
                    'name' => $name,
                    'size' => $link['l_size'] ?? '',
                    'audio' => $link['l_audio'] ?? '',
                    'url' => $link['l_url'] ?? '',
                    'quality' => $quality ?? '144'
                ];
            }
            // error_log('Combined Data: ' . print_r($combined_data, TRUE));
        }

        usort($combined_data, function ($a, $b) {
            return intval($a['quality']) - intval($b['quality']);
        });



        $movie_data = '';
        foreach ($combined_data as $item) {
            if (!empty($item['name']) && !empty($item['url'])) {
                $file_link = fp_encrypt_url($item['url']);
                $movie_data .= "
        <div class='post-link-item'>
            <div class='post-link-item-content-wrapper'>
                <a href='{$link_site_url}{$file_link}' target='_blank'>
                    <span class='post-link-item-content'>{$item['name']}</span><br/>
                </a>
                <div class='sub-item-wrapper'>" .
                    (!empty($item['size']) ? "<span class='sub-item sub-item-size'>{$item['size']}</span>" : "") .
                    (!empty($item['quality']) ? "<span class='sub-item sub-item-quality'>{$item['quality']}p</span>" : "") .
                    (!empty($item['audio']) ? "<span class='sub-item sub-item-audio'>{$item['audio']}</span>" : "<span class='sub-item sub-item-audio'>Unknown</span>")
                    . "
                </div>
            </div>
        </div>
        ";
            }
        }

        return $movie_data;
    }
}

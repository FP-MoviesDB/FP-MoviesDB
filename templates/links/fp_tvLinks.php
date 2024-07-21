<?php

if (!defined('ABSPATH')) exit;

class FPSeriesLinks extends CreatePostHelper
{
    function fp_series_links($tmdb_id, $meta_data, $user_links_data = [])
    {
        $post_id = get_the_ID();
        if (!$post_id) {
            return '';
        }
        $site_url = get_site_url(null, '', 'https');
        $link_site_url = $site_url . '/link/';
        $request_url = FP_MOVIES_FP_BASE_URL . '/tv/' . $tmdb_id;
        $request_url .= '?api_key=' . FP_MOVIES_FP_API_KEY;
        $fp_apiType = get_option_with_fallback('mtg_fp_key_type', 'personal');
        if ($fp_apiType === 'personal') {
            $mtg_global_access = get_option_with_fallback('mtg_global_access', 'false') === 'on' ? 'true' : 'false';
            $request_url .= '&show_global_org_files=' . $mtg_global_access;
        }
        $request_url .= '&video_meta_data=true&unique=true';
        $request_url .= '&request_type=' . $fp_apiType;

        $filepressUrl = FP_DOWNLOAD_URL . '/file/';
        $seasons_output = "";
        $packs_output = "";
        $seasons_data = [];

        if (function_exists('curl_version')) {
            $curl = curl_init($request_url);
            curl_setopt_array($curl, array(
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
            ));
            $response = curl_exec($curl);
            $data = json_decode($response, TRUE);
        } else {
            $response = file_get_contents($request_url);
            $data = json_decode($response, TRUE);
        }

        $combined_data = [];
        if ($data && $data['status']) {
            $files = $data['data']['files'];
            $search1 = [
                'H.265', 'H.264', 'DDP2.0', 'DDP5.1', '5.1', '2.0',
                '1080p', '2160p', ' - mkvcinemas', ' - mkvcinema', '-mkvcinemas',
                ' webrip', '-Telly', '.Cinevood', ' Cinevood', '-kyogo', '-infinit3',
                '-Thedefiler', ' ~ Immortal', ' ~ BunnyJMB', '-Archie', '-KatmovieHD.cat',
                '(1)', ' ~ TheAvi', ' ~ LSSJBroly', '-Vegamovies.to', ' Dual Audio', ' Bluray',
                '-Honey', '[Telly]', 'Vegamovies.to', 'PôgoLińks', ' - 1XBET', ' - 1XBET', 'BollyHolic.me', '-BollyHolic', 'BollyHolic',
            ];
            $replace1 = [
                'x265', 'x264', 'DDP5_1', 'DDP5_1', '5_1', '2_0',
                '1080p', '2160p', ' ', ' ', ' ', ' ',
                ' ', ' ', ' ', ' ', ' ', ' ',
                ' ', ' ', ' ', ' ', ' ',
                ' ', ' ', ' ', ' ', ' ',
                ' ', ' ', ' ', ' ', ' ',
                ' ', ' ', ' ',
            ];
            $search2 = ['5_1', '2_1', '2_0'];
            $replace2 = ['5.1', '2.1', '2.0'];



            foreach ($files as $elm) {
                // error_log(print_r($elm, TRUE));
                $seasonNumber = $elm['seasonNumber'];
                $episodeNumber = $elm['episodeNumber'];
                $name = $elm['name'];
                $name = preg_replace('/\\.[^.\\s]{3,4}$/', '', $name);
                $name = preg_replace('/\(\d{4}\)\s*/', '', $name);
                $name = str_replace($search1, $replace1, $name);
                $name = str_replace($search2, $replace2, $name);
                $name = rtrim($name, '- ');
                $name = preg_replace('/\s+/', ' ', $name);
                $name = trim($name);
                $size = formatBytes($elm['size']) ?? '';
                $episodeNumber = $elm['episodeNumber'];
                $audio = $elm['videoFileDetails'];
                $audio = $audio['audioList'];
                $audioLangList = array_map(function ($audio) {
                    return $audio['language'];
                }, $audio);
                $audio = $this->getAudioString($audioLangList, $name);
                $audio = $audio ?? '';

                $url = $filepressUrl . $elm['_id'];
                $url = fp_encrypt_url($url);

                $quality = $elm['quality'];
                if (empty($quality)) $quality = $this->getQualityFromString($name);
                if (empty($quality)) $quality = '144';

                $combined_data[] = [
                    'name' => $name,
                    'size' => $size,
                    'audio' => $audio,
                    'url' => $url,
                    'quality' => $quality,
                    'season' => $seasonNumber,
                    'episode' => $episodeNumber
                ];
            }
        }

        if (!empty($user_links_data) && is_array($user_links_data)) {
            foreach ($user_links_data as $link_data) {
                foreach ($link_data as $link) {

                    $name = $link['l_title'];
                    $pattern = '/\{(\w+)\}/';
                    $audio = isset($link['l_audio']) ? $link['l_audio'] : '';
                    $quality = isset($link['l_quality']) ? $link['l_quality'] : '';
                    $replace_type = [
                        'title' => $meta_data['fp_title'],
                        't_title' => $meta_data['fp_title'],
                        'year' => $meta_data['fp_latest_year'] ?? '',
                        'quality' => $quality . 'p',
                        'audio' => $audio,
                        'season' => $link['l_season'],
                        'episode' => $link['l_episode']
                    ];
                    $name = preg_replace_callback($pattern, function ($matches) use ($replace_type) {
                        return $replace_type[$matches[1]] ?? $matches[0];
                    }, $name);

                    $quality = $link['l_quality'];
                    $quality = preg_replace('/p$/', '', $quality);
                    $file_link = fp_encrypt_url($link['l_url']);
                    // $file_link = '/link/' . $file_link;
                    if (empty($quality)) $quality = '144';
                    $combined_data[] = [
                        'name' => $name,
                        'size' => $link['l_size'] ?? '',
                        'audio' => $link['l_audio'] ?? '',
                        'url' => $file_link ?? '',
                        'quality' => $quality ?? '144',
                        'season' => $link['l_season'] ?? 0,
                        'episode' => $link['l_episode'] ?? 0
                    ];
                }
            }
        }

        usort($combined_data, function ($a, $b) {
            if ($a['season'] == $b['season']) {
                if ($a['episode'] == $b['episode']) {
                    return intval($a['quality']) - intval($b['quality']);
                }
                return $a['episode'] - $b['episode'];
            }
            return $a['season'] - $b['season'];
        });



        foreach ($combined_data as $item) {
            if ($item['episode'] == -1) {
                $packs_output .= "<div class='packs'><div class='sublists_base all-zip-sublists'><span class='series-meta-num'>S0{$item['season']}</span><span> - </span><a href='{$link_site_url}{$item['url']}' target='_blank' class='sublists-link'>{$item['name']}</a></div><div class='series-additional-wrapper'><div class='series-meta-quality'>{$item['quality']}p</div><div class='series-meta-size'>[{$item['size']}]</div></div></div>";
            } else {
                $seasons_data[$item['season']][$item['quality']][] = $item;
            }
        }

        foreach ($seasons_data as $seasonNumber => $qualities) {
            $seasons_output .= "<div class='down-btn' onclick='toggleSeason(\"season-{$seasonNumber}-content\")'>Season {$seasonNumber}</div>";
            $seasons_output .= "<div id='season-{$seasonNumber}-content' class='season-content' style='display:none;'>";
            ksort($qualities);
            foreach ($qualities as $quality => $episodes) {
                $seasons_output .= "<div class='down-qty-btn' onclick='toggleQuality(\"season-{$seasonNumber}-quality-{$quality}-content\")'>{$quality}p</div>";
                $seasons_output .= "<div class='single-tv-items' id='season-{$seasonNumber}-quality-{$quality}-content' style='display:none;'>";
                $seasons_output .= '<div class="mdownlinks mdownlinks-single">';
                foreach ($episodes as $episode) {
                    $episodeNumber = $episode['episode'] < 10 ? 'EP0' . $episode['episode'] : 'EP' . $episode['episode'];
                    $episodeNumberDiv = !empty($episode['episode']) ? "<div class='series-meta-quality sub-item'>{$episodeNumber}</div>" : '';
                    $sizeDiv = !empty($episode['size']) ? "<div class='series-meta-size sub-item'>[{$episode['size']}]</div>" : '';
                    $audioDiv = !empty($episode['audio']) ? "<div class='series-meta-audio sub-item'>[{$episode['audio']}]</div>" : '';
                    $seasons_output .=
                        "<div class='sublists_base episode-sublists'>
                        <a href='{$link_site_url}{$episode['url']}' target='_blank' class='sublists-link'>{$episode['name']}</a>
                        <div class='series-additional-wrapper'>
                            {$episodeNumberDiv}
                            {$sizeDiv}
                            {$audioDiv}
                        </div>
                    </div>";
                }
                $seasons_output .= '</div>';
                $seasons_output .= "</div>";
            }
            $seasons_output .= "</div>";
        }

        // error_log(print_r($combined_data, TRUE));


        return array(
            'seasons_output' => $seasons_output,
            'packs_output' => $packs_output
        );
    }
}

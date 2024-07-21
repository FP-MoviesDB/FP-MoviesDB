<?php

if (!defined('ABSPATH')) exit;

if (defined('DOING_AJAX') && DOING_AJAX && isset($_POST['action']) && $_POST['action'] === 'fp_app_search') {
    class FPmoviesFilters extends FP_moviesHelpers
    {

        protected $tmdbkey = '';
        protected $fpkey = '';
        protected $apilang = '';

        public function __construct()
        {
            // error_log('FPmoviesFilters instantiated');
            $this->tmdbkey = get_option('mtg_tmdb_api_key', FP_MOVIES_TMDB_API_KEY);
            $this->fpkey = get_option('mtg_fp_api_key', FP_MOVIES_FP_API_KEY);
            $this->apilang = get_option('mtg_postDefault_settings', []);
            $this->apilang = $this->get_arrayValue_with_fallback($this->apilang, 'language', 'en-US');

            add_action('wp_ajax_fp_app_search', array($this, 'fp_app_search_query'));
        }

        public function fp_app_search_query()
        {
            // Check nonce
            if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'movie_tv_nonce')) {
                wp_send_json_error('Invalid nonce');
                return;
            }
            $mtime = microtime(TRUE);
            // type, s_type, query, page_number
            $type = isset($_POST['type']) ? sanitize_text_field($_POST['type']) : '';
            $s_type = isset($_POST['s_type']) ? sanitize_text_field($_POST['s_type']) : '';
            $query = isset($_POST['query']) ? sanitize_text_field($_POST['query']) : '';
            $source = isset($_POST['source']) ? sanitize_text_field($_POST['source']) : '';
            $page_number = isset($_POST['page_number']) ? sanitize_text_field($_POST['page_number']) : 1;

            // error_log('type: ' . print_r($type, TRUE));
            // error_log('s_type: ' . print_r($s_type, TRUE));
            // error_log('query: ' . print_r($query, TRUE));
            // error_log('page_number: ' . print_r($page_number, TRUE));
            // error_log('args: ' . print_r($args, TRUE));
            // error_log('source: ' . print_r($source, TRUE));

            if (empty($type) || empty($s_type)) {
                wp_send_json_error('Invalid request');
            }

            if ($s_type !== 'trending' && empty($query)) {
                wp_send_json_error('Invalid request');
            }

            if ($s_type === 'trending' && $source === 'fp') {
                $args = array(
                    'api_key' => $this->fpkey,
                );
            } else {
                $args = array(
                    'api_key' => $this->tmdbkey,
                    'language' => $this->apilang,
                );
            }

            if ($s_type == 'trending' && $source == 'fp') {
                $fp_key_type = get_option('mtg_fp_key_type');
                if ($fp_key_type == 'personal') {
                    // error_log('inside personal');
                    $isGlobalAccess = get_option('mtg_global_access');
                    $args['request_type'] = $fp_key_type;
                    $args['show_global_org_files'] = ($isGlobalAccess == 'on' ? 'true' : 'false');
                } else {
                    // error_log('inside organization');
                    $args['request_type'] = $fp_key_type;
                }
                $args['genre'] = $type;
                $args['page'] = $page_number;
                $args['per_page'] = 20;
                $api = FP_MOVIES_FP_BASE_URL . '/latest';
            } else {
                if ($s_type == 'search') {
                    $args['query'] = $query;
                    $args['page'] = $page_number;
                    $api = FP_MOVIES_TMDB_API_BASE_URL . '/search/' . $type;
                } else if ($s_type == 'id') {
                    $api = FP_MOVIES_TMDB_API_BASE_URL . '/' . $type . '/' . $query;
                } else if ($s_type == 'trending') {
                    $api = FP_MOVIES_TMDB_API_BASE_URL . '/trending/' . $type . '/day';
                }
            }

            // error_log('apiURL: ' . print_r($api, TRUE));
            // error_log('args: ' . print_r($args, TRUE));
            $json_tmdb = $this->RemoteJson($args, $api);

            // error_log('json_tmdb: ' . print_r($json_tmdb, TRUE));
            // Verify status code

            if ($s_type == 'trending' && $source == 'fp') {

                $response = $this->handleFPResponse($json_tmdb, $type, $s_type, $mtime);
            } else {
                if (!$this->Disset($json_tmdb, 'status_code')) {
                    // error_log('inside status_code');
                    // Verify Errors
                    if (!$this->Disset($json_tmdb, 'errors')) {
                        // error_log('inside errors');
                        if ($s_type == 'search' || $s_type == 'trending') {
                            // error_log('inside search');
                            $tmdb_page    = $this->Disset($json_tmdb, 'page');
                            $tmdb_pages   = $this->Disset($json_tmdb, 'total_pages');
                            $tmdb_results = $this->Disset($json_tmdb, 'results');
                            $tmdb_total   = $this->Disset($json_tmdb, 'total_results');

                            // Compose Response
                            $response = array(
                                'response' => true,
                                'type'     => $type,
                                's_type'   => $s_type,
                                'page'     => $tmdb_page,
                                'pages'    => $tmdb_pages,
                                'items'    => $tmdb_page * 20,
                                'total'    => $tmdb_total,
                                'results'  => $tmdb_results, $type,
                                'mtime'    => $this->TimeExe($mtime)
                            );
                        } else {
                            // error_log('inside else');
                            $response = array(
                                'response' => true,
                                'type'     => $type,
                                's_type'   => $s_type,
                                'results'  => $json_tmdb,
                                'mtime'    => $this->TimeExe($mtime)
                            );
                        }
                    } else {
                        $response = array(
                            'response' => false,
                            'message' => $json_tmdb['errors'][0]
                        );
                    }
                } else {
                    $response = array(
                        'response' => false,
                        'message' => $this->Disset($json_tmdb, 'status_message')
                    );
                }
            }
            // The Response
            // error_log('Response: ' . print_r($response, TRUE));
            wp_send_json($response);
        }

        function handleFPResponse($json_tmdb, $type, $s_type, $mtime)
        {

            if (!$this->Disset($json_tmdb, 'status')) {
                $response = array(
                    'response' => false,
                    'message' => 'Invalid response from FP API'
                );
                wp_send_json($response);
            } else {
                $tmdb_items = $json_tmdb['data']['fileList'];
                $tmdb_results = array();
                $tmdb_args = array(
                    'api_key' => $this->tmdbkey,
                    'language' => $this->apilang,
                );
                $multiHandle = curl_multi_init();
                $curlHandles = [];
                $batchSize = 50;
                $currentIndex = 0;
                $totalItems = count($tmdb_items);
                $total_results = $json_tmdb['data']['totalResult'];
                $current_page = $json_tmdb['data']['pageNo'];
                $per_page_limit = $json_tmdb['data']['limit'];
                $pages = ceil($total_results / $per_page_limit);
                $result_total_items = $json_tmdb['data']['pageResultCount'];

                // Function to add a request to the multi handle
                function addRequest($multiHandle, $requestUrl)
                {
                    $curlHandle = curl_init();
                    curl_setopt($curlHandle, CURLOPT_URL, $requestUrl);
                    curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, 1);
                    curl_multi_add_handle($multiHandle, $curlHandle);
                    return $curlHandle;
                }

                while ($currentIndex < $totalItems) {
                    // Add up to $batchSize requests to the multi handle
                    for ($i = 0; $i < $batchSize && $currentIndex < $totalItems; $i++, $currentIndex++) {
                        $file = $this->Disset($tmdb_items[$currentIndex], 'file');
                        $tmdbId = $this->Disset($file, 'tmdb_id');
                        $tmdbType = $this->Disset($file, 'genre');
                        $requestUrl = FP_MOVIES_TMDB_API_BASE_URL . '/' . $tmdbType . '/' . $tmdbId . '?' . http_build_query($tmdb_args);
                        $curlHandle = addRequest($multiHandle, $requestUrl);
                        $curlHandles[] = $curlHandle;
                    }

                    // Execute the handles
                    $activeHandles = null;
                    do {
                        $status = curl_multi_exec($multiHandle, $activeHandles);
                        if ($activeHandles) {
                            curl_multi_select($multiHandle);
                        }
                    } while ($activeHandles && $status == CURLM_OK);

                    // Collect responses and clean up handles
                    foreach ($curlHandles as $curlHandle) {
                        $httpCode = curl_getinfo($curlHandle, CURLINFO_HTTP_CODE);
                        if ($httpCode >= 200 && $httpCode < 300) {
                            $response = curl_multi_getcontent($curlHandle);
                            $decodedResponse = json_decode($response, true);
                            if (json_last_error() === JSON_ERROR_NONE) {
                                // Check if the response contains an error
                                if (!isset($decodedResponse['success']) || $decodedResponse['success'] !== false) {
                                    $tmdb_results[] = $decodedResponse;
                                } else {
                                    // error_log('TMDB error: ' . $decodedResponse['status_message'] . ' (Code: ' . $decodedResponse['status_code'] . ')');
                                }
                            } else {
                                // error_log('JSON decode error: ' . json_last_error_msg());
                            }
                        } else {
                            // error_log('HTTP error: ' . $httpCode . ' for URL: ' . curl_getinfo($curlHandle, CURLINFO_EFFECTIVE_URL));
                        }
                        curl_multi_remove_handle($multiHandle, $curlHandle);
                        curl_close($curlHandle);
                    }

                    // Clear handles array for the next batch
                    $curlHandles = array();
                }

                curl_multi_close($multiHandle);

                // error_log('tmdbResults: ' . print_r($tmdb_results, TRUE));
                $response = array(
                    'response' => true,
                    'type' => $type,
                    's_type' => $s_type,
                    'page' => $current_page,
                    'pages' => $pages,
                    'items' => $result_total_items,
                    'total' => $total_results,
                    'results' => $tmdb_results,
                    'mtime' => $this->TimeExe($mtime)
                );
                return $response;
            }
        }

        function __destruct()
        {
            // error_log('CLASS FPmoviesFilters destructed');

        }
    }
    new FPmoviesFilters();
}

<?php

if (!defined('ABSPATH')) exit;

require_once FP_MOVIES_DIR . 'helper/fp_manage_optimizations.php';

function mts_bulk_import()
{
    global $fp_min_m;
    if (!current_user_can('manage_options')) {
        wp_die('You do not have sufficient permissions to access this page.');
    }
    require_once FP_MOVIES_DIR . 'inc/check_tmdb_exist.php';
    wp_enqueue_style('fp-bulk-import', FP_MOVIES_URL . 'css/fp_bulk_import' . $fp_min_m . '.css', array(), FP_MOVIES_FILES, 'all');
    wp_enqueue_script('fp-bulk-import', FP_MOVIES_URL . 'js/fp_bulk_import' . $fp_min_m . '.js', array('jquery'), FP_MOVIES_FILES, true);
    $tmdb_exist = get_option('mtg_tmdb_api_key');
    $fp_exist = get_option('mtg_fp_api_key');
    $is_ready = true;
    if (!$tmdb_exist || !$fp_exist) {
        $is_ready = false;
    }
    // error_log('tmdb_exist: ' . $tmdb_exist);
    wp_localize_script('fp-bulk-import', 'fp_bi_data', array(
        'ajax_url' => FP_MOVIES_AJAX,
        'nonce' => wp_create_nonce('movie_tv_nonce'),
        'is_ready' => $is_ready,
        'update_post_nonce' => wp_create_nonce('fp_post_update_nonce'),
        'disable_opt_nonce' => wp_create_nonce('disable_opt_nonce'),
        'enable_opt_nonce' => wp_create_nonce('enable_opt_nonce'),
        'fp_get_all_ids_nonce' => wp_create_nonce('fp_get_all_ids_nonce'),
    ));

?>
    <?php if (!$tmdb_exist || !$fp_exist) : ?>
        <div class="fp-bulk-main-wrapper">
            <div class="fp-bulk-option-heading">
                <h3>API Key Missing</h3>
            </div>
            <div class="fp-bulk-add-wrapper">
                <p>TMDB API Key or FP API Key is missing. Please add the API Key in the settings.</p>
            </div>
        </div>
    <?php else : ?>
        <div class="fp-bulk-main-wrapper">
            <div class="bulk-import-errors-wrapper">
                <span class="bulk-import-error" id="full_width"></span>
                <!-- <span class="close-error">x</span> -->
            </div>

            <div class="fp-processing-wrapper">
                <div class="fp-processing-progress" id="full_width">
                    <div class="fp-processing-progress-alert" id="fp-processing-progress-alert"><span>&#x26A0;</span> Keep this page open until the process gets completed. <span>&#x26A0;</span></div>
                    <div class="fp-processing-progress-bar-wrapper" id="full_width">
                        <div class="fp-processing-progress-bar" id="fp-processing-progress-bar"></div>
                        <div class="fp-processing-progress-bar-text" id="fp-processing-progress-bar-text">0%</div>
                    </div>
                    <div class="fp-processing-progress-text" id="fp-processing-progress-text"></div>
                    <div class="fp-processing-progress-status" id="fp-processing-progress-status">
                        Adding TMDB ID:
                        <span class="fp-progress-current-tmdb"></span>
                    </div>
                    <div class="fp-processing-complete">
                        <span class="fp-processing-failed-number">Failed: <span class="fp-processing-failed-count">0</span></span>
                        <span class="fp-processing-failed-ids">Failed IDs: <span class="fp-processing-failed-tmdb-ids"></span></span>
                    </div>
                    <!-- cancel -->
                    <div class="bulk-actions-buttons">
                        <div class="fp-processing-progress-btn fp-processing-progress-pause" id="fp-processing-progress-pause">Pause</div>
                        <div class="fp-processing-progress-btn fp-processing-progress-cancel" id="fp-processing-progress-cancel">Cancel</div>
                    </div>
                </div>
            </div>


            <div class="mtg_bulk_import_wrapper">
                <div class="heading-wrapper" id="full_width">
                    <h2>Bulk Import Tool</h2>
                    <div class="sub-heading">
                        <span> This tool will help you to import multiple movies or tv shows at once. You can import movies or tv shows from TMDB or FP.</span><span><strong>This tool consumes resources, so be mindful while using it.</strong></span>
                    </div>
                </div>
                <div class="fp-bulk-single-wrapper">
                    <div class="fp-bulk-option-heading">
                        <h3>Import Type</h3>
                    </div>
                    <div class="tab-selector import-type">
                        <button class="tab-button active" id='fp-import-type' value='tmdb'>TMDB</button>
                        <button class="tab-button" id='fp-import-type' value='fp'>FP</button>
                    </div>
                </div>

                <div class="fp-bulk-single-wrapper">
                    <div class="fp-bulk-option-heading">
                        <h3>Post Type</h3>
                    </div>
                    <div class="tab-selector post-type">
                        <button class="tab-button active" id='fp-bulk-post-type' value='movie'>Movies</button>
                        <button class="tab-button" id='fp-bulk-post-type' value='tv'>TV</button>
                    </div>
                </div>

                <div class="fp-bulk-single-wrapper">
                    <div class="fp-bulk-option-heading">
                        <h3>Skip Existing</h3>
                    </div>
                    <div class="fp-bulk-add-wrapper">
                        <div class="tab-selector skip-existing">
                            <button class="tab-button active" id='skipExisting' value="true">True</button>
                            <button class="tab-button" id='skipExisting' value="false">False</button>
                        </div>
                        <p class="skip-existing-true">Skip existing will skip the posts which are already imported.</p>
                        <p class="skip-existing-false">&#x26A0; It will Re-Update and overwrite all post-meta, post-content, taxonomies and add featured image if its missing.</p>
                    </div>
                </div>

                <div class="fp-bulk-single-wrapper tmdb-id-input-box">
                    <div class="fp-bulk-option-heading">
                        <h3>TMDB IDs</h3>
                    </div>

                    <div class="fp-bulk-add-wrapper">
                        <textarea class="tmdb-ids-textarea" name="tmdb-ids" id="tmdb-ids" placeholder="100, 6941, 1152"></textarea>
                        <p>Enter TMDB IDs separated by comma or new line.</p>
                    </div>
                </div>


                <div class="fp-bulk-single-wrapper fp-auto-option">
                    <div class="fp-bulk-option-heading">
                        <h3>FP Additional Option</h3>
                    </div>
                    <div class="tab-selector fp-import-type">
                        <button class="tab-button active" id='fp-additional-option' value='all'>All</button>
                        <button class="tab-button" id='fp-additional-option' value='recent'>Recent Posts</button>
                    </div>
                </div>

                <div class="fp-bulk-single-wrapper fp-auto-option-recent">
                    <div class="fp-bulk-option-heading">
                        <h3>Recent Numbers</h3>
                    </div>
                    <div class="fp-bulk-add-wrapper">
                        <input type="number" name="recent-numbers" id="recent-numbers" placeholder="Enter Number" style="min-height: 40px;" />
                        <p>Enter the number of recent posts to import.</p>
                    </div>
                </div>


                <!-- Submit Button -->
                <div class="fp-bulk-single-submit">
                    <div class="loader-area">
                        <div class="lds-ellipsis">
                            <div></div>
                            <div></div>
                            <div></div>
                            <div></div>
                        </div>
                    </div>
                    <div class="fp-bulk-submit_btn" id="fp-bulk-submit">Submit</div>
                </div>



            </div>
        </div>
    <?php endif; ?>




<?php
}

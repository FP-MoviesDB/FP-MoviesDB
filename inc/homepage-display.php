<?php

if (!defined('ABSPATH')) exit;

// ┌────────────────────┐
// │ Plugin MAIN Page │
// └────────────────────┘
function plugin_options_page()
{ ?>
    <div class="mtg_wrapper">
        <h2>Movie/TV Generator</h2>
        <div class="selector-wrapper">
            <div class="tab-selector">
                <button class="tab-button active" id='movie'>Movies</button>
                <button class="tab-button" id='tv'>TV</button>
            </div>
            
            <div class="source-selector">
                <button class="source-button active" id='tmdb'>TMDB</button>
                <button class="source-button" id='fp'>FP</button>
            </div>
        </div>
        <div class="tmdb_input">
            <!-- Input onPress enter call the postGen.searchInitiator() -->
            <input class="search_input_field" type="text" id="mtg_query" name="mtg_query" placeholder="Enter Keyword/TMDB ID" onkeypress="if(event.keyCode==13) postGen.searchInitiator()" />
            <button class="submit_btn" id="search_movie_btn" style="height: 100%;" onclick=postGen.searchInitiator()>Search</button>
        </div>
        <div id="response-list" class="tab-content">
            <div class="result-report-wrapper"><div id='result-report' class="search-report"></div></div>
            <div id="results-outer-container">
                <div id="search_loading" class="animate-spin" style="display: none; justify-content: center; align-items: center; width: 100%; height: 100%; min-height: 300px;">
                    <img src="<?php echo FP_MOVIES_URL . 'img/loading.webp'; ?>" alt="Loading..." />
                </div>
                <!-- <div id="no-result-found" style="display: none;">
                    <h3 id="no-result-text">No Results Found</h3>
                </div> -->
                <div id="results-inner-container_status" style="display: none;">
                    <p id="results-inner-container_status_text"></p>
                </div>
                <div id="results-inner-container"></div>
                <div id="load-more" style="display: none;">
                </div>
            </div>
        </div>
    </div>

<?php
}

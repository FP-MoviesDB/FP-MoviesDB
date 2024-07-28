<?php

if (!defined('ABSPATH')) die();

class FPPlayer
{
	public $post_meta;
	public function __construct()
	{
		// error_log('FPPlayer Constructor Initialized');
		$this->fp_ready_player();
	}

	public function fp_ready_player() {
		$post_id = get_the_ID();
		if (!$post_id) {
			if (function_exists('fp_log_error')) fp_log_error('No post ID');
			// error_log('No post ID');
			return;
		}

		$meta_data = FP_Movies_Metadata_Cache::get_meta_data($post_id);

		$_postType = $meta_data['_postType'];
		$tmdb_id = $meta_data['fp_tmdb'];
		global $fp_min_m;

		if ($_postType == 'movie') {
			wp_enqueue_style('fp-movie-player-css', esc_url(FP_MOVIES_URL) . '/templates/css/fp_playerMovie' . $fp_min_m . '.css', array(), FP_MOVIES_FILES, 'all');
			wp_enqueue_script('fp-movie-player-js', esc_url(FP_MOVIES_URL) . '/templates/js/fp_playerMovie' . $fp_min_m . '.js', array('jquery'), FP_MOVIES_FILES, true);
			wp_localize_script('fp-movie-player-js', 'fp_pAjax', array(
				'ajax_url' => FP_MOVIES_AJAX,
				'nonce' => wp_create_nonce('fp_player_nonce')
				
			));
			require_once FP_MOVIES_DIR . 'templates/fp_playerMovie.php';
			$moviePlayer = new FP_PlayerMovie();
			$moviePlayer->fp_moviePlayer($post_id, $meta_data);
		}else if ($_postType == 'tv') {
			wp_enqueue_style('fp-movie-player-css', esc_url(FP_MOVIES_URL) . '/templates/css/fp_playerMovie' . $fp_min_m . '.css', array(), FP_MOVIES_FILES, 'all');
			wp_enqueue_style('fp-tv-player-css', esc_url(FP_MOVIES_URL) . '/templates/css/fp_playerTV' . $fp_min_m . '.css', array(), FP_MOVIES_FILES, 'all');

			wp_enqueue_script('fp-movie-player-js', esc_url(FP_MOVIES_URL) . '/templates/js/fp_playerMovie' . $fp_min_m . '.js', array('jquery'), FP_MOVIES_FILES, true);
			wp_localize_script('fp-movie-player-js', 'fp_pAjax', array(
				'ajax_url' => FP_MOVIES_AJAX,
				'nonce' => wp_create_nonce('fp_player_nonce')
				
			));
			require_once FP_MOVIES_DIR . 'helper/fp_get_fp_tv_data.php';
			require_once FP_MOVIES_DIR . 'templates/fp_playerTV.php';
			$tvPlayer = new FP_PlayerTV();
			$tvPlayer->fp_tvPlayer($post_id, $meta_data);
		}
	}
}

new FPPlayer();

?>
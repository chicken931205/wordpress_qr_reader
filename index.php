<?php
/**
 * Plugin Name: QR Reader
 * Plugin URI: https://github.com/chicken931205/gutenberg_offers_block
 * Description: This is a plugin for QR code scanning only on mobile device.
 * Version: 1.1.0
 * Author: Golden Chicken
 *
 * @package qr-reader
 */

define( 'qr_reader_version', '1.0.0' );
define( 'qr_reader_plugin_file', __FILE__ );

if ( !class_exists( 'QR_Reader' ) ) {
   class QR_Reader {

	   	public function __construct() {
			add_action( 'init', array( &$this, 'qr_reader__register_block' ) );
			add_action( 'enqueue_block_assets', array( &$this, 'load_block_editor_assets' ) );
	   	}

		function qr_reader__register_block() {
			register_block_type( 
				__DIR__ . '/build' , 
				array(
					'render_callback' => array( &$this, 'render_block_qr_reader' ),
				)
			);
		}	

		function load_block_editor_assets() {
			$current_user = wp_get_current_user();
			$user_id = $current_user->ID;

			if (is_user_logged_in()) {
				$team_id = get_field('team_id', 'user_' . $user_id);
				$minecraft_id = get_field('minecraft_id', 'user_' . $user_id);
				$server_id = get_field('server_id', 'user_' . $user_id);
				$game_id = get_field('game_id', 'user_' . $user_id);
				$group_id = get_field('group_id', 'user_' . $user_id);
				$is_logged_in = true;
			} else {
				$is_logged_in = false;
			}

			$plugin_dir_path = plugin_dir_url(qr_reader_plugin_file);
			wp_enqueue_style( 'style', $plugin_dir_path . 'src/asset/css/style.scss', [], qr_reader_version );
			wp_enqueue_script( 'qr_packed_js', "https://rawgit.com/sitepoint-editors/jsqrcode/master/src/qr_packed.js", array(), qr_reader_version, true );
			
			wp_register_script('qrCodeScanner_js', $plugin_dir_path . 'src/asset/js/qrCodeScanner.js', array(), qr_reader_version, true);
			wp_enqueue_script('qrCodeScanner_js');
			wp_localize_script('qrCodeScanner_js', 'user_profile', [
				'team_id' => $team_id,
				'minecraft_id' => $minecraft_id,
				'server_id' => $server_id,
				'game_id' => $game_id,
				'group_id' => $group_id,
				'is_logged_in' => $is_logged_in
			]);
		}

		function render_block_qr_reader() {
			$plugin_dir_path = plugin_dir_url(qr_reader_plugin_file);

			return 
				'<div id="container">
					<h1>QR Code Scanner</h1>

					<a id="btn-scan-qr">
						<img src="' . $plugin_dir_path . 'src/asset/qr_icon.svg">
					<a/>

					<canvas hidden="" id="qr-canvas"></canvas>

					<div id="qr-result" hidden="">
						<b>Data:</b> <span id="outputData"></span>
					</div>

					<div id="qr-warning"  hidden="">
						<b>Warning:</b> <span id="warningData"></span>
					</div>
				</div>';
		}
   }
}

$qr_reader = new QR_Reader;
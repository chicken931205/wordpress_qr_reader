<?php
/**
 * Plugin Name: QR Reader
 * Plugin URI: https://github.com/chicken931205/wordpress_qr_reader
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

		private $_gameplay_metakey = "gameplay";

	   	public function __construct() {
			add_action( 'init', array( &$this, 'qr_reader__register_block' ) );
			add_action( 'enqueue_block_assets', array( &$this, 'load_block_editor_assets' ) );
			add_filter( 'script_loader_tag', array( &$this, 'add_type_attribute_to_script_tag' ), 10, 3 );
			add_filter( 'acf/load_field/name=pages', array( &$this, 'set_pages_field' ), 10, 1 );
			add_action( 'profile_update', array( &$this, 'save_gameplay_fields' ), 10, 2 );
			add_action( 'current_screen', array( &$this, 'user_profile_init' ) );
			add_action('wp_ajax_change_select_page', array( &$this, 'change_select_page_callback' ) );
			add_action('wp_ajax_nopriv_change_select_page', array( &$this, 'change_select_page_callback' ) );
	   	}

		function qr_reader__register_block() {
			register_block_type( 
				__DIR__ . '/build' , 
				array(
					'render_callback' => array( &$this, 'render_block_qr_reader' ),
				)
			);
		}

		function add_type_attribute_to_script_tag($tag, $handle, $src) {
			if ('qrCodeScanner_js' === $handle) {
				$tag = '<script type="module" src="' . esc_url($src) . '"></script>';
			}
			return $tag;
		}

		private function _get_acf_field_key($field_name) {
			$field = acf_get_field($field_name);
			if ($field) {
				return $field['key'];
			}
			return '';
		}

		//add js to user profile page
		function user_profile_init() {
			$current_screen = get_current_screen();

			if (!is_admin() || !($current_screen && $current_screen->base == 'profile')) {
				return;
			}

			$pages_field_key = $this->_get_acf_field_key('pages');
			$team_id_field_key = $this->_get_acf_field_key('team_id');
			$minecraft_id_field_key = $this->_get_acf_field_key('minecraft_id');
			$server_id_field_key = $this->_get_acf_field_key('server_id');
			$game_id_field_key = $this->_get_acf_field_key('game_id');
			$group_id_field_key = $this->_get_acf_field_key('group_id');

			$plugin_dir_path = plugin_dir_url(qr_reader_plugin_file);
			wp_register_script('userProfile_js', $plugin_dir_path . 'src/asset/js/userProfile.js', array('jquery'), qr_reader_version, true);
			wp_enqueue_script('userProfile_js');
			wp_localize_script('userProfile_js', 'gameplay', [
				'pages_field_key' => $pages_field_key,
				'team_id_field_key' => $team_id_field_key,
				'minecraft_id_field_key' => $minecraft_id_field_key,
				'server_id_field_key' => $server_id_field_key,
				'game_id_field_key' => $game_id_field_key,
				'group_id_field_key' => $group_id_field_key,
				'ajax_url' => admin_url('admin-ajax.php'),
        		'nonce'    => wp_create_nonce('ajax_nonce')
			]);
		}

		//handle change event of pages field
		function change_select_page_callback() {
			if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'ajax_nonce' ) ) {
				wp_send_json_error( 'Unauthorized request' );
			}

			$page_id = $_POST['page_id'];

			$current_user = wp_get_current_user();
			$user_id = $current_user->ID;

			$gameplay = get_user_meta($user_id, $this->_gameplay_metakey);
		
			$response = array(
				'gameplay' => $gameplay[0][$page_id] ? $gameplay[0][$page_id] : [] 
			);

			wp_send_json_success($response);
		}

		//set options to pages field in user profile page
		function set_pages_field( $field ) {
			$field['choices'] = [];
		
			$pages = get_pages();
			foreach ($pages as $page) {
				$value = $page->ID;
				$label = $page->post_title;
				$field['choices'][ $value ] = $label;
			}
		
			return $field;
		}

		//update user profile
		function save_gameplay_fields($user_id, $old_user_data) {
			// delete_user_meta($user_id, $this->_gameplay_metakey);
			// return false;

			if (!current_user_can('edit_user', $user_id)) {
				return false;
			}

			$page_id = get_field('pages', 'user_' . $user_id);
			$team_id = get_field('team_id', 'user_' . $user_id);
			$minecraft_id = get_field('minecraft_id', 'user_' . $user_id);
			$server_id = get_field('server_id', 'user_' . $user_id);
			$game_id = get_field('game_id', 'user_' . $user_id);
			$group_id = get_field('group_id', 'user_' . $user_id);

			$gameplay = get_user_meta($user_id, $this->_gameplay_metakey);
			if ($gameplay) $gameplay = $gameplay[0];
			$gameplay[$page_id] = array(
				'team_id' => $team_id,
				'minecraft_id' => $minecraft_id,
				'server_id' => $server_id,
				'game_id' => $game_id,
				'group_id' => $group_id,
			);

			update_user_meta($user_id, $this->_gameplay_metakey, $gameplay);
		}
		
		function load_block_editor_assets() {
			$current_user = wp_get_current_user();
			$user_id = $current_user->ID;

			global $post;
			$current_page_id = $post->ID;

			if (is_user_logged_in()) {
				$is_logged_in = true;
			} else {
				$is_logged_in = false;
			}

			$gameplay = get_user_meta($user_id, $this->_gameplay_metakey);
			$team_id = $gameplay[0][$current_page_id]['team_id'] ? $gameplay[0][$current_page_id]['team_id'] : "";
			$minecraft_id = $gameplay[0][$current_page_id]['minecraft_id'] ? $gameplay[0][$current_page_id]['minecraft_id']: "";
			$server_id = $gameplay[0][$current_page_id]['server_id'] ? $gameplay[0][$current_page_id]['server_id'] : "";
			$game_id = $gameplay[0][$current_page_id]['game_id'] ? $gameplay[0][$current_page_id]['game_id'] : "";
			$group_id = $gameplay[0][$current_page_id]['group_id'] ? $gameplay[0][$current_page_id]['group_id'] : "";

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

					<div id="video-container" hidden="">
						<video id="qr-video"></video>
					</div>

					<a id="btn-scan-qr">
						<img src="' . $plugin_dir_path . 'src/asset/qr_icon.svg">
					</a>

					<button id="btn-stop-scan" type="submit" hidden="">Stop Scanning</button>
					
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
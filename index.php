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

define( 'qr_reader_version', '1.0.10' );
define( 'qr_reader_plugin_file', __FILE__ );

if ( !class_exists( 'QR_Reader' ) ) {
   class QR_Reader {

		private $_param_enable_key = "params_enable";

	   	public function __construct() {
			add_action( 'init', array( &$this, 'qr_reader__register_block' ) );
			add_action( 'enqueue_block_assets', array( &$this, 'load_block_editor_assets' ) );
			add_filter( 'script_loader_tag', array( &$this, 'add_type_attribute_to_script_tag' ), 10, 3 );
			add_filter( 'acf/load_field/name=pages', array( &$this, 'set_pages_field' ), 10, 1 );
			add_action( 'acf/save_post', array( &$this, 'save_param_enable_fields' ), 10, 1 );
			add_action( 'current_screen', array( &$this, 'qr_reader_settings_page_init' ) );
			add_action( 'wp_ajax_change_select_page', array( &$this, 'change_select_page_callback' ) );
			add_action( 'wp_ajax_nopriv_change_select_page', array( &$this, 'change_select_page_callback' ) );
			add_action( 'admin_menu', array( &$this, 'add_qr_reader_menu' ) );
			add_action( 'admin_init', array( &$this, 'add_acf_form_head') );
			register_deactivation_hook( qr_reader_plugin_file, array( &$this, 'clear_param_enable_settings' ) );
	   	}

		function clear_param_enable_settings() {
			delete_option($this->_param_enable_key);
			update_field('team_id_enable', 0, 'param_enable_settings');
			update_field('minecraft_id_enable', 0, 'param_enable_settings');
			update_field('server_id_enable', 0, 'param_enable_settings');
			update_field('game_id_enable', 0, 'param_enable_settings');
			update_field('group_id_enable', 0, 'param_enable_settings');
			update_field('gamipress_ranks_enable', 0, 'param_enable_settings');
			update_field('gamipress_points_enable', 0, 'param_enable_settings');
		}

		function qr_reader__register_block() {
			register_block_type( 
				__DIR__ . '/build' , 
				array(
					'render_callback' => array( &$this, 'render_block_qr_reader' ),
				)
			);
		}

		function add_acf_form_head() {
			if (isset($_GET['page']) && $_GET['page'] === 'qr-reader-settings') {
				acf_form_head();
			}
		}

		function add_qr_reader_menu() {
			add_menu_page(
				'QR Reader', // Page title
				'QR Reader', // Menu title
				'manage_options', // Capability
				'qr-reader',  // Menu slug
				array(&$this, 'display_qr_reader_settings'), 
				'dashicons-admin-generic', // Icon URL or Dashicon class
				6                    // Position
			);

			add_submenu_page(
				'qr-reader',  // Parent slug
				'QR Reader Settings',  // Page title
				'Settings',           // Menu title
				'manage_options',    // Capability
				'qr-reader-settings', // Menu slug
				array(&$this, 'display_qr_reader_settings')
			);

			remove_submenu_page('qr-reader', 'qr-reader');
		}

		function display_qr_reader_settings() {
			$field_group_key = $this->_get_acf_field_group_key("Settings");
			?>
				<div class="wrap">
					<h1>QR Reader Settings</h1>
					<?php
						acf_form([
							'post_id' => 'param_enable_settings', 
							'field_groups' => [$field_group_key],
							'submit_value' => 'Save Settings', 
						]);  
					?>
				</div>
			<?php
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

		private function _get_acf_field_group_key($title) {
			if (!function_exists('acf_get_field_groups')) {
				return false;
			}
			
			$field_groups = acf_get_field_groups();
			foreach ($field_groups as $field_group) {
				if ($field_group['title'] === $title) {
					return $field_group['key'];
				}
			}
			
			return false;
		}
		
		//add js to user qr reader settings page
		function qr_reader_settings_page_init() {
			$current_screen = get_current_screen();

			if (!is_admin() || !($current_screen && $current_screen->base == 'qr-reader_page_qr-reader-settings')) {
				return;
			}

			$pages_field_key = $this->_get_acf_field_key('pages');
			$show_debug_data_field_key = $this->_get_acf_field_key('show_debug_data');
			$header_text_field_key = $this->_get_acf_field_key('header_text');
			$info_text_field_key = $this->_get_acf_field_key('info_text');
			$team_id_enable_field_key = $this->_get_acf_field_key('team_id_enable');
			$minecraft_id_enable_field_key = $this->_get_acf_field_key('minecraft_id_enable');
			$server_id_enable_field_key = $this->_get_acf_field_key('server_id_enable');
			$game_id_enable_field_key = $this->_get_acf_field_key('game_id_enable');
			$group_id_enable_field_key = $this->_get_acf_field_key('group_id_enable');
			$gamipress_ranks_enable_field_key = $this->_get_acf_field_key('gamipress_ranks_enable');
			$gamipress_points_enable_field_key = $this->_get_acf_field_key('gamipress_points_enable');

			$plugin_dir_path = plugin_dir_url(qr_reader_plugin_file);
			wp_register_script('qrReaderSettings_js', $plugin_dir_path . 'src/asset/js/qrReaderSettings.js', array('jquery'), qr_reader_version, true);
			wp_enqueue_script('qrReaderSettings_js');
			wp_localize_script('qrReaderSettings_js', 'param_enable', [
				'pages_field_key' => $pages_field_key,
				'show_debug_data_field_key' => $show_debug_data_field_key,
				'header_text_field_key' => $header_text_field_key,
				'info_text_field_key' => $info_text_field_key,
				'team_id_enable_field_key' => $team_id_enable_field_key,
				'minecraft_id_enable_field_key' => $minecraft_id_enable_field_key,
				'server_id_enable_field_key' => $server_id_enable_field_key,
				'game_id_enable_field_key' => $game_id_enable_field_key,
				'group_id_enable_field_key' => $group_id_enable_field_key,
				'gamipress_ranks_enable_field_key' => $gamipress_ranks_enable_field_key,
				'gamipress_points_enable_field_key' => $gamipress_points_enable_field_key,
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
			$param_enable = get_option($this->_param_enable_key, []);
		
			$response = array(
				'param_enable' => $param_enable[$page_id] ? $param_enable[$page_id] : [] 
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

		//update param enable settings
		function save_param_enable_fields($post_id) {
			// delete_option($this->_param_enable_key);
			// return false;

			if (!is_admin() || $post_id !== 'param_enable_settings') {
				return;
			}

			$page_id = get_field('pages', $post_id);
			$header_text = get_field('header_text', $post_id);
			$show_debug_data = get_field('show_debug_data', $post_id);
			$info_text = get_field('info_text', $post_id);
			$team_id_enable = get_field('team_id_enable', $post_id);
			$minecraft_id_enable = get_field('minecraft_id_enable', $post_id);
			$server_id_enable = get_field('server_id_enable', $post_id);
			$game_id_enable = get_field('game_id_enable', $post_id);
			$group_id_enable = get_field('group_id_enable', $post_id);
			$gamipress_ranks_enable = get_field('gamipress_ranks_enable', $post_id);
			$gamipress_points_enable = get_field('gamipress_points_enable', $post_id);

			$param_enable = get_option($this->_param_enable_key, []);
			$param_enable[$page_id] = array(
				'header_text' => $header_text,
				'show_debug_data' => $show_debug_data,
				'info_text' => $info_text,
				'team_id_enable' => $team_id_enable,
				'minecraft_id_enable' => $minecraft_id_enable,
				'server_id_enable' => $server_id_enable,
				'game_id_enable' => $game_id_enable,
				'group_id_enable' => $group_id_enable,
				'gamipress_ranks_enable' => $gamipress_ranks_enable,
				'gamipress_points_enable' => $gamipress_points_enable,
			);

			update_option($this->_param_enable_key, $param_enable);
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

			$param_enable = get_option($this->_param_enable_key, []);
			if (isset($param_enable[$current_page_id])){
				$show_debug_data = $param_enable[$current_page_id]['show_debug_data']; 
				$team_id_enable = $param_enable[$current_page_id]['team_id_enable'];
				$minecraft_id_enable = $param_enable[$current_page_id]['minecraft_id_enable'];
				$server_id_enable = $param_enable[$current_page_id]['server_id_enable'];
				$game_id_enable= $param_enable[$current_page_id]['game_id_enable'];
				$group_id_enable = $param_enable[$current_page_id]['group_id_enable'];
				$gamipress_ranks_enable = $param_enable[$current_page_id]['gamipress_ranks_enable'];
				$gamipress_points_enable = $param_enable[$current_page_id]['gamipress_points_enable'];	
			} else {
				$show_debug_data = 0;
				$team_id_enable = 0;
				$minecraft_id_enable = 0;
				$server_id_enable = 0;
				$game_id_enable= 0;
				$group_id_enable = 0;
				$gamipress_ranks_enable = 0;
				$gamipress_points_enable = 0;
			}

			//get gameplay info
			$team_id = get_field('team_id', 'user_' . $user_id);
            $minecraft_id = get_field('minecraft_id', 'user_' . $user_id);
            $server_id = get_field('server_id', 'user_' . $user_id);
            $game_id = get_field('game_id', 'user_' . $user_id);
            $group_id = get_field('group_id', 'user_' . $user_id);

			//get user game rank and points from gamipress
			$user_points = gamipress_get_user_points($user_id, 'point');
			$user_rank = gamipress_get_user_rank($user_id, "gamerank");
			$user_rank = strtolower($user_rank->post_title);

			$plugin_dir_path = plugin_dir_url(qr_reader_plugin_file);
			wp_enqueue_style( 'style', $plugin_dir_path . 'src/asset/css/style.scss', [], qr_reader_version );
			wp_enqueue_script( 'qr_packed_js', "https://rawgit.com/sitepoint-editors/jsqrcode/master/src/qr_packed.js", array(), qr_reader_version, true );
			
			wp_register_script('qrCodeScanner_js', $plugin_dir_path . 'src/asset/js/qrCodeScanner.js', array(), qr_reader_version, true);
			wp_enqueue_script('qrCodeScanner_js');
			wp_localize_script('qrCodeScanner_js', 'param_enable', [
				'show_debug_data' => $show_debug_data,
				'team_id_enable' => $team_id_enable,
				'minecraft_id_enable' => $minecraft_id_enable,
				'server_id_enable' => $server_id_enable,
				'game_id_enable' => $game_id_enable,
				'group_id_enable' => $group_id_enable,
				'gamipress_ranks_enable' => $gamipress_ranks_enable,
				'gamipress_points_enable' => $gamipress_points_enable,
				'team_id' => $team_id,
				'minecraft_id' => $minecraft_id,
				'server_id' => $server_id,
				'game_id' => $game_id,
				'group_id' => $group_id,
				'user_rank' => $user_rank,
				'user_points' => $user_points,
				'is_logged_in' => $is_logged_in
			]);
		}

		function render_block_qr_reader() {
			$plugin_dir_path = plugin_dir_url(qr_reader_plugin_file);

			global $post;
			$current_page_id = $post->ID;

			$param_enable = get_option($this->_param_enable_key, []);
			if (isset($param_enable[$current_page_id])){
				$show_debug_data = $param_enable[$current_page_id]['show_debug_data'];
				$header_text = $param_enable[$current_page_id]['header_text'];
				$info_text = $param_enable[$current_page_id]['info_text'];
			} else {
				$show_debug_data = 0;
				$header_text = "QR Code Scanner";
				$info_text = "Click the picture above to scan QR code";
			}

			$html .= '<div id="container">
					<h1>' . $header_text . '</h1>

					<div id="video-container" hidden="">
						<video id="qr-video"></video>
					</div>

					<a id="btn-scan-qr">
						<img src="' . $plugin_dir_path . 'src/asset/qr_icon.svg">
					</a>

					<div id="qr-info">
						<span>' . $info_text . '</span>
					</div>

					<button id="btn-stop-scan" type="submit" hidden="">Stop Scanning</button>';
			
			if ($show_debug_data) {
				$html .= '<div id="qr-result" hidden="">
							<b>Data:</b> <span id="outputData"></span>
						</div>';
			}
			
			$html .= '<div id="qr-warning"  hidden="">
						<b>Warning:</b> <span id="warningData"></span>
					</div>';
			$html .= '</div>';
			return $html;
		}
   }
}

$qr_reader = new QR_Reader;
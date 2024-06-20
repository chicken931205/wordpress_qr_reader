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
			$path = plugin_dir_url(qr_reader_plugin_file) . "src/asset/js/admin.js";
			wp_enqueue_script( 'admin_js', $path, array( 'jquery' ), qr_reader_version, true );
	   }

	   function render_block_qr_reader( $attributes ) {
			return sprintf( '<h1>%s</h1>', "Hello! I am a QR Reader!" );
	   }
   }
}

$qr_reader = new QR_Reader;
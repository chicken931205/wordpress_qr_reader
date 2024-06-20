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
			$plugin_dir_path = plugin_dir_url(qr_reader_plugin_file);

			wp_enqueue_script( 'qr_grid_js', $plugin_dir_path . 'library/src/grid.js', array(), qr_reader_version, true );
			wp_enqueue_script( 'qr_version_js', $plugin_dir_path . 'library/src/version.js', array(), qr_reader_version, true );
			wp_enqueue_script( 'qr_detector_js', $plugin_dir_path . 'library/src/detector.js', array(), qr_reader_version, true );
			wp_enqueue_script( 'qr_formatinf_js', $plugin_dir_path . 'library/src/formatinf.js', array(), qr_reader_version, true );
			wp_enqueue_script( 'qr_errorlevel_js', $plugin_dir_path . 'library/src/errorlevel.js', array(), qr_reader_version, true );
			wp_enqueue_script( 'qr_bitmat_js', $plugin_dir_path . 'library/src/bitmat.js', array(), qr_reader_version, true );
			wp_enqueue_script( 'qr_datablock_js', $plugin_dir_path . 'library/src/datablock.js', array(), qr_reader_version, true );
			wp_enqueue_script( 'qr_bmparser_js', $plugin_dir_path . 'library/src/bmparser.js', array(), qr_reader_version, true );
			wp_enqueue_script( 'qr_datamask_js', $plugin_dir_path . 'library/src/datamask.js', array(), qr_reader_version, true );
			wp_enqueue_script( 'qr_rsdecoder_js', $plugin_dir_path . 'library/src/rsdecoder.js', array(), qr_reader_version, true );
			wp_enqueue_script( 'qr_gf256poly_js', $plugin_dir_path . 'library/src/gf256poly.js', array(), qr_reader_version, true );
			wp_enqueue_script( 'qr_gf256_js', $plugin_dir_path . 'library/src/gf256.js', array(), qr_reader_version, true );
			wp_enqueue_script( 'qr_decoder_js', $plugin_dir_path . 'library/src/decoder.js', array(), qr_reader_version, true );
			wp_enqueue_script( 'qr_qrcode_js', $plugin_dir_path . 'library/src/qrcode.js', array(), qr_reader_version, true );
			wp_enqueue_script( 'qr_findpat_js', $plugin_dir_path . 'library/src/findpat.js', array(), qr_reader_version, true );
			wp_enqueue_script( 'qr_alignpat_js', $plugin_dir_path . 'library/src/alignpat.js', array(), qr_reader_version, true );
			wp_enqueue_script( 'qr_databr_js', $plugin_dir_path . 'library/src/databr.js', array(), qr_reader_version, true );

			wp_enqueue_script( 'admin_js', $plugin_dir_path . 'src/asset/js/admin.js', array( 'jquery' ), qr_reader_version, true );

			wp_enqueue_style( 'style', $plugin_dir_path . 'src/asset/css/style.scss', [], '1.0.1' );
	   }

	   function render_block_qr_reader() {
		$plugin_dir_path = plugin_dir_url(qr_reader_plugin_file);

		return '<div id="container">
					<h1>QR Code Scanner</h1>

					<a id="btn-scan-qr">
						<img src="' . $plugin_dir_path . 'src/asset/qr_icon.svg">
					<a/>

					<canvas hidden="" id="qr-canvas"></canvas>

					<div id="qr-result" hidden="">
						<b>Data:</b> <span id="outputData"></span>
					</div>
				</div>';
	   }
   }
}

$qr_reader = new QR_Reader;
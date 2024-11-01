<?php

namespace GPLSCore\GPLS_PLUGIN_WMFW;

/**
 * Plugin Name:       Grand Watermark Images [[GrandPlugins]]
 * Description:       Add Text and Image watermarks to your images in your WordPress website
 * Author:            GrandPlugins
 * Author URI:        https://profiles.wordpress.org/grandplugins/
 * Plugin URI:        https://grandplugins.com/product/wp-images-watermark/
 * Domain Path:       /languages
 * Requires at least: 5.6
 * Requires PHP:      7.0
 * Text Domain:       watermark-images-for-wp-and-woo-grandpluginswp
 * Std Name:          gpls-wmfw-watermark-image-for-wordpress
 * Version:           1.1.7
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use GPLSCore\GPLS_PLUGIN_WMFW\Core;
use GPLSCore\GPLS_PLUGIN_WMFW\Settings;
use GPLSCore\GPLS_PLUGIN_WMFW\Image;
use GPLSCore\GPLS_PLUGIN_WMFW\Image_Edit_Page;
use GPLSCore\GPLS_PLUGIN_WMFW\Watermark_Base;
use GPLSCore\GPLS_PLUGIN_WMFW\Watermarks_Templates;
use GPLSCore\GPLS_PLUGIN_WMFW\Apply_Watermarks_Templates;
use GPLSCore\GPLS_PLUGIN_WMFW\Image_Watermark;
use GPLSCore\GPLS_PLUGIN_WMFW\Single_Apply_Watermarks;

if ( ! class_exists( __NAMESPACE__ . '\GPLS_WMFW_Watermark_Images_For_WordPress' ) ) :


	/**
	 * WP Images Watermarks Main Class.
	 */
	class GPLS_WMFW_Watermark_Images_For_WordPress {

		/**
		 * Single Instance
		 *
		 * @var object
		 */
		private static $instance;

		/**
		 * Plugin Info
		 *
		 * @var array
		 */
		private static $plugin_info;

		/**
		 * Debug Mode Status
		 *
		 * @var bool
		 */
		protected $debug = false;

		/**
		 * Core Object
		 *
		 * @var object
		 */
		private static $core;

		/**
		 * Settings Class Object.
		 *
		 * @var object
		 */
		private static $settings;

		/**
		 * Singular init Function.
		 *
		 * @return Object
		 */
		public static function init() {
			if ( ! isset( self::$instance ) ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		/**
		 * Core Actions Hook.
		 *
		 * @return void
		 */
		public static function core_actions( $action_type ) {
			require_once trailingslashit( plugin_dir_path( __FILE__ ) ) . 'core/bootstrap.php';
			self::$core = new Core( self::$plugin_info );
			if ( 'activated' === $action_type ) {
				self::$core->plugin_activated();
			} elseif ( 'deactivated' === $action_type ) {
				self::$core->plugin_deactivated();
			} elseif ( 'uninstall' === $action_type ) {
				self::$core->plugin_uninstalled();
			}
		}

		/**
		 * Plugin Activated Hook.
		 *
		 * @return void
		 */
		public static function plugin_activated() {
			self::setup_plugin_info();
			self::includes();
			self::disable_duplicate();
			Watermark_Base::init( self::$plugin_info );
			self::core_actions( 'activated' );
			register_uninstall_hook( __FILE__, array( __NAMESPACE__ . '\GPLS_WMFW_Watermark_Images_For_WordPress', 'plugin_uninstalled' ) );
		}

		/**
		 * Plugin Deactivated Hook.
		 *
		 * @return void
		 */
		public static function plugin_deactivated() {
			self::setup_plugin_info();
			self::core_actions( 'deactivated' );
			Watermark_Base::remove_preview_image_file();
		}

		/**
		 * Plugin Installed hook.
		 *
		 * @return void
		 */
		public static function plugin_uninstalled() {
			self::setup_plugin_info();
			self::core_actions( 'uninstall' );
		}

		/**
		 * Constructor
		 */
		public function __construct() {
			self::setup_plugin_info();
			$this->load_languages();
			self::includes();
			$this->init_classes();
		}

		/**
		 * Initialize the plugin Classes.
		 *
		 * @return void
		 */
		public function init_classes() {
			self::$core     = new Core( self::$plugin_info );
			self::$settings = new Settings( self::$core, self::$plugin_info );

			Image::init( self::$plugin_info );
			Image_Edit_Page::init( self::$plugin_info, self::$core );
			Watermarks_Templates::init( self::$plugin_info, self::$core );
			Single_Apply_Watermarks::init( self::$plugin_info, self::$core );
			Apply_Watermarks_Templates::init( self::$plugin_info, self::$core );
			Watermark_Base::init( self::$plugin_info );
			Image_Watermark::init( self::$plugin_info );
			MasterStudyLMS::init( self::$core, self::$plugin_info );
			WooImageWatermark::init( self::$core, self::$plugin_info );
		}

		/**
		 * Includes Files
		 *
		 * @return void
		 */
		public static function includes() {
			require_once \ABSPATH . \WPINC . '/class-wp-image-editor.php';
			require_once trailingslashit( plugin_dir_path( __FILE__ ) ) . 'core/bootstrap.php';
		}

		/**
		 * Load languages Folder.
		 *
		 * @return void
		 */
		public function load_languages() {
			load_plugin_textdomain( self::$plugin_info['text_domain'], false, trailingslashit( dirname( self::$plugin_info['basename'] ) ) . 'languages/' );
		}

		/**
		 * Set Plugin Info
		 *
		 * @return array
		 */
		public static function setup_plugin_info() {
			$plugin_data = get_file_data(
				__FILE__,
				array(
					'Version'     => 'Version',
					'Name'        => 'Plugin Name',
					'URI'         => 'Plugin URI',
					'SName'       => 'Std Name',
					'text_domain' => 'Text Domain',
				),
				false
			);

			self::$plugin_info = array(
				'id'                => 1008,
				'basename'          => plugin_basename( __FILE__ ),
				'version'           => $plugin_data['Version'],
				'name'              => $plugin_data['SName'],
				'text_domain'       => $plugin_data['text_domain'],
				'file'              => __FILE__,
				'plugin_url'        => $plugin_data['URI'],
				'public_name'       => $plugin_data['Name'],
				'path'              => trailingslashit( plugin_dir_path( __FILE__ ) ),
				'url'               => trailingslashit( plugin_dir_url( __FILE__ ) ),
				'options_page'      => $plugin_data['SName'] . '-settings-tab',
				'single_apply_page' => $plugin_data['SName'] . '-single-apply-tab',
				'bulk_apply_page'   => $plugin_data['SName'] . '-bulk-apply-tab',
				'backups_page'      => $plugin_data['SName'] . '-backups-tab',
				'localize_var'      => str_replace( '-', '_', $plugin_data['SName'] ) . '_localize_data',
				'type'              => 'free',
				'prefix'            => 'gpls-wmfw',
				'general_prefix'    => 'gpls-plugins-general-prefix',
				'classes_prefix'    => 'gpls-wmfw',
				'classes_general'   => 'gpls-general',
				'pro_link'          => 'https://grandplugins.com/product/wp-images-watermark?utm_source=free_plugin&utm_medium=wp&utm_campaign=ref',
				'review_link'       => 'https://wordpress.org/support/plugin/watermark-images-for-wp-and-woo-grandpluginswp/reviews/#new-post',
				'duplicate_base'    => 'gpls-wmfw-watermark-images-for-wordpress-pro/gpls-wmfw-watermark-image-for-wordpress.php',
			);
		}

		/**
		 * Disable duplicate.
		 *
		 * @return void
		*/
		private static function disable_duplicate() {
			require_once \ABSPATH . 'wp-admin/includes/plugin.php';
		   if ( ! empty( self::$plugin_info['duplicate_base'] ) && is_plugin_active( self::$plugin_info['duplicate_base'] ) ) {
				require_once \ABSPATH . 'wp-admin/includes/plugin.php';
			   deactivate_plugins( self::$plugin_info['duplicate_base'] );
		   }
	    }

	}

	add_action( 'plugins_loaded', array( __NAMESPACE__ . '\GPLS_WMFW_Watermark_Images_For_WordPress', 'init' ), 10 );
	register_activation_hook( __FILE__, array( __NAMESPACE__ . '\GPLS_WMFW_Watermark_Images_For_WordPress', 'plugin_activated' ) );
	register_deactivation_hook( __FILE__, array( __NAMESPACE__ . '\GPLS_WMFW_Watermark_Images_For_WordPress', 'plugin_deactivated' ) );
endif;

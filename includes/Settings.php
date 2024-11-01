<?php
namespace GPLSCore\GPLS_PLUGIN_WMFW;

use GPLSCore\GPLS_PLUGIN_WMFW\Image_Watermark;
use GPLSCore\GPLS_PLUGIN_WMFW\Watermarks_Templates;

/**
 * Redirects To Checkout Class.
 */
class Settings {

	/**
	 * Core Object
	 *
	 * @var object
	 */
	public $core;

	/**
	 * Plugin Info
	 *
	 * @var object
	 */
	public static $plugin_info;

	/**
	 * Settings Name.
	 *
	 * @var string
	 */
	public static $settings_name;

	/**
	 * Settings Tab Key
	 *
	 * @var string
	 */
	protected $settings_tab_key;

	/**
	 * Settings Tab name
	 *
	 * @var array
	 */
	protected $settings_tab;


	/**
	 * Current Settings Active Tab.
	 *
	 * @var string
	 */
	protected $current_active_tab;

	/**
	 * Settings Array.
	 *
	 * @var array
	 */
	public static $settings;

	/**
	 * Settings Tab Fields
	 *
	 * @var Array
	 */
	protected $fields = array();


	/**
	 * Constructor.
	 *
	 * @param object $core Core Object.
	 * @param array $plugin_info Plugin Info Object.
	 */
	public function __construct( $core, $plugin_info ) {
		$this->core             = $core;
		self::$plugin_info      = $plugin_info;
		$this->settings_tab_key = self::$plugin_info['options_page'];
		self::$settings_name    = self::$plugin_info['name'] . '-main-settings-name';
		$this->hooks();
	}

	/**
	 * Filters and Actions Hooks.
	 *
	 * @return void
	 */
	public function hooks() {
		add_action( 'admin_menu', array( $this, 'settings_menu_page' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'settings_assets' ) );
		add_action( 'wp_ajax_' . self::$plugin_info['name'] . '-upload-custom-font-file-action', array( $this, 'ajax_upload_font_file' ) );
		add_filter( 'plugin_action_links_' . self::$plugin_info['basename'], array( $this, 'plugin_pro_button' ), 10, 1 );
	}

	/**
	 * Plugin Pro Link.
	 *
	 * @param array $links
	 * @return array
	 */
	public function plugin_pro_button( $links ) {
		$links[] = '<a target="_blank" href="' . esc_url_raw( self::$plugin_info['pro_link'] ) . '"><b style="color:#F00;">' . esc_html__( 'Premium', 'watermark-images-for-wp-and-woo-grandpluginswp' ) . '</b></a>';
		return $links;
	}

	/**
	 * Settings Assets.
	 *
	 * @return void
	 */
	public function settings_assets() {
		if ( ! empty( $_GET['page'] ) && self::$plugin_info['options_page'] === sanitize_text_field( wp_unslash( $_GET['page'] ) ) ) {
			wp_enqueue_style( self::$plugin_info['name'] . '-settings-menu-bootstrap-style', $this->core->core_assets_lib( 'bootstrap', 'css' ), array(), self::$plugin_info['version'], 'all' );
			wp_enqueue_style( self::$plugin_info['name'] . '-settings-css', self::$plugin_info['url'] . 'assets/dist/css/admin/admin-styles.min.css', array(), self::$plugin_info['version'], 'all' );

			if ( ! wp_script_is( 'jquery' ) ) {
				wp_enqueue_script( 'jquery' );
			}
			wp_enqueue_media();

			wp_enqueue_style( 'wp-jquery-ui-dialog' );
			wp_enqueue_script( 'jquery-ui-dialog' );

			wp_enqueue_script( self::$plugin_info['name'] . '-bootstrap-js', $this->core->core_assets_lib( 'bootstrap.bundle', 'js' ), array(), self::$plugin_info['version'], true );
			wp_enqueue_script( self::$plugin_info['name'] . '-dmuploader-js', $this->core->core_assets_lib( 'jquery.dm-uploader', 'js' ), array( 'jquery' ), self::$plugin_info['version'], true );
			wp_enqueue_script( self::$plugin_info['name'] . '-settings-js', self::$plugin_info['url'] . 'assets/dist/js/admin/settings.min.js', array( 'jquery' ), self::$plugin_info['version'], true );
			wp_localize_script(
				self::$plugin_info['name'] . '-settings-js',
				str_replace( '-', '_', self::$plugin_info['name'] . '_localize_vars' ),
				array(
					'ajaxUrl'              => admin_url( 'admin-ajax.php' ),
					'spinner'              => admin_url( 'images/spinner.gif' ),
					'nonce'                => wp_create_nonce( self::$plugin_info['name'] . '-ajax-nonce' ),
					'uploadFontFileAction' => self::$plugin_info['name'] . '-upload-custom-font-file-action',
					'classes_prefix'       => self::$plugin_info['classes_prefix'],
					'labels'               => array(
						'only_ttf'      => esc_html__( 'Only True Type fonts are allowed', 'watermark-images-for-wp-and-woo-grandpluginswp' ),
						'select_images' => esc_html__( 'Select images', 'watermark-images-for-wp-and-woo-grandpluginswp' ),
					),
				)
			);
		}
	}

	/**
	 * AJAX upload font file.
	 *
	 * @return void
	 */
	public function ajax_upload_font_file() {
		check_admin_referer( self::$plugin_info['name'] . '-ajax-nonce', 'nonce' );

		if ( empty( $_FILES['file'] ) ) {
			wp_send_json_success(
				array(
					'result'  => false,
					'status'  => 'error',
					'message' => esc_html__( 'no file uploaded!', 'watermark-images-for-wp-and-woo-grandpluginswp' ),
				)
			);
		}
		$uploads              = wp_upload_dir();
		$fonts_path           = trailingslashit( $uploads['basedir'] ) . trailingslashit( self::$plugin_info['name'] );
		$font_file            = $_FILES['file'];
		$font_file_name       = sanitize_text_field( wp_unslash( $font_file['name'] ) );
		$font_file_ext        = pathinfo( $font_file['name'], PATHINFO_EXTENSION );
		$upload_error_strings = array(
			false,
			sprintf(
				/* translators: 1: upload_max_filesize, 2: php.ini */
				esc_html__( 'The uploaded file exceeds the %1$s directive in %2$s.' ),
				'upload_max_filesize',
				'php.ini'
			),
			sprintf(
				/* translators: %s: MAX_FILE_SIZE */
				esc_html__( 'The uploaded file exceeds the %s directive that was specified in the HTML form.' ),
				'MAX_FILE_SIZE'
			),
			esc_html__( 'The uploaded file was only partially uploaded.' ),
			esc_html__( 'No file was uploaded.' ),
			'',
			esc_html__( 'Missing a temporary folder.' ),
			esc_html__( 'Failed to write file to disk.' ),
			esc_html__( 'File upload stopped by extension.' ),
		);
		$ttf_mime_types       = array(
			'font/ttf',
		);
							// === apply checks on the file === //.
		// uploaded check.
		if ( ! is_uploaded_file( $font_file['tmp_name'] ) ) {
			wp_send_json_success(
				array(
					'result'  => false,
					'status'  => 'error',
					'message' => esc_html__( 'file upload is failed!', 'watermark-images-for-wp-and-woo-grandpluginswp' ),
				)
			);
		}

		// Already Exists?.
		if ( file_exists( $fonts_path . $font_file_name ) ) {
			wp_send_json_success(
				array(
					'result'  => false,
					'status'  => 'error',
					'message' => esc_html__( 'file already exists!', 'watermark-images-for-wp-and-woo-grandpluginswp' ),
				)
			);
		}

		// Unknow Error.
		if ( isset( $font_file['error'] ) && ! is_numeric( $font_file['error'] ) && ( 0 !== $font_file['error'] ) ) {
			wp_send_json_success(
				array(
					'result'  => false,
					'status'  => 'error',
					'message' => esc_html( sanitize_text_field( wp_unslash( $font_file['error'] ) ) ),
				)
			);
		}

		// Known Error.
		if ( isset( $font_file['error'] ) && $font_file['error'] > 0 ) {
			wp_send_json_success(
				array(
					'result'  => false,
					'status'  => 'error',
					'message' => esc_html( $upload_error_strings[ absint( $font_file['error'] ) ] ),
				)
			);
		}

		// Size Check.
		$file_size = filesize( $font_file['tmp_name'] );
		if ( ! ( $file_size > 0 ) ) {
			wp_send_json_success(
				array(
					'result'  => false,
					'status'  => 'error',
					'message' => esc_html__( 'file is empty!', 'watermark-images-for-wp-and-woo-grandpluginswp' ),
				)
			);
		}

		// mime and ext Check.
		if ( extension_loaded( 'fileinfo' ) ) {
			$finfo     = finfo_open( FILEINFO_MIME_TYPE );
			$mime_type = finfo_file( $finfo, $font_file['tmp_name'] );
			finfo_close( $finfo );
		} else {
			$mime_type = @mime_content_type( $font_file['tmp_name'] );
		}

		if ( ( false === strpos( $mime_type, 'font' ) ) || ( 'ttf' !== $font_file_ext ) ) {
			wp_send_json_success(
				array(
					'result'  => false,
					'status'  => 'error',
					'message' => esc_html__( 'File type is invalid!', 'watermark-images-for-wp-and-woo-grandpluginswp' ),
				)
			);
		}
								// == End Checks == //.

		if ( ! wp_mkdir_p( $fonts_path ) ) {
			wp_send_json_success(
				array(
					'result'  => false,
					'status'  => 'error',
					'message' => esc_html__( 'Failed to create directory for custom fonts!', 'watermark-images-for-wp-and-woo-grandpluginswp' ),
				)
			);
		}

		// Move the file to the plugin fonts folder.
		$new_font_file  = $fonts_path . $font_file_name;
		$moved_new_file = @move_uploaded_file( $font_file['tmp_name'], $new_font_file );

		if ( false === $moved_new_file ) {
			wp_send_json_success(
				array(
					'result'  => false,
					'status'  => 'error',
					'message' => sprintf( esc_html__( 'The uploaded file could not be moved to %s.', 'watermark-images-for-wp-and-woo-grandpluginswp' ), $new_font_file ),
				)
			);
		}

		// Set correct file permissions.
		$stat  = stat( dirname( $new_font_file ) );
		$perms = $stat['mode'] & 0000666;
		chmod( $new_font_file, $perms );

		// Get all available fonts and return.
		$fonts = Image_Watermark::get_available_fonts( true );
		wp_send_json_success(
			array(
				'result'  => true,
				'file'    => $font_file,
				'status'  => 'success',
				'fonts'   => $fonts,
				'message' => esc_html__( 'Font file is added successfully!', 'watermark-images-for-wp-and-woo-grandpluginswp' ),
			)
		);

	}

	/**
	 * Settings Link.
	 *
	 * @param array $links Plugin Row Links.
	 * @return array
	 */
	public function settings_link( $links ) {
		$links[] = '<a href="' . esc_url_raw( admin_url( 'upload.php?page=' . self::$plugin_info['options_page'] ) ) . '">' . esc_html__( 'GIF Editor' ) . '</a>';
		return $links;
	}

	/**
	 * Settings Menu Page Func.
	 *
	 * @return void
	 */
	public function settings_menu_page() {
		// Settings Page.
		add_submenu_page(
			'edit.php?post_type=' . Watermarks_Templates::$post_type_key,
			esc_html__( 'Settings', 'watermark-images-for-wp-and-woo-grandpluginswp' ),
			esc_html__( 'Settings', 'watermark-images-for-wp-and-woo-grandpluginswp' ),
			'upload_files',
			self::$plugin_info['options_page'],
			array( $this, 'main_settings_page' )
		);
		// Single Apply Watermarks on image.
		add_submenu_page(
			'edit.php?post_type=' . Watermarks_Templates::$post_type_key,
			esc_html__( 'Single Editor', 'watermark-images-for-wp-and-woo-grandpluginswp' ),
			esc_html__( 'Single Editor', 'watermark-images-for-wp-and-woo-grandpluginswp' ),
			'upload_files',
			self::$plugin_info['single_apply_page'],
			array( $this, 'single_apply_page' )
		);
		// Bulk Apply Watermarks Template Page.
		add_submenu_page(
			'edit.php?post_type=' . Watermarks_Templates::$post_type_key,
			esc_html__( 'Bulk Apply', 'watermark-images-for-wp-and-woo-grandpluginswp' ),
			esc_html__( 'Bulk Apply', 'watermark-images-for-wp-and-woo-grandpluginswp' ),
			'upload_files',
			self::$plugin_info['bulk_apply_page'],
			array( $this, 'bulk_apply_page' )
		);
	}

	/**
	 * Is settings page.
	 *
	 * @return boolean
	 */
	public function is_settings_page( $tab = '' ) {
		if ( ! empty( $_GET['page'] ) && self::$plugin_info['options_page'] === sanitize_text_field( wp_unslash( $_GET['page'] ) ) ) {
			if ( ! empty( $tab ) ) {
				if ( ! empty( $_GET['tab'] ) && ( sanitize_text_field( wp_unslash( $_GET['tab'] ) ) === $tab ) ) {
					return true;
				} else {
					return false;
				}
			}
			return true;
		}
		return false;
	}

	/**
	 * Main Settings page.
	 *
	 * @return void
	 */
	public function main_settings_page() {
		$plugin_info  = self::$plugin_info;
		$core         = $this->core;
		$settings_obj = $this;

		require_once self::$plugin_info['path'] . 'templates/settings-page-template.php';
	}

	/**
	 * Single Apply page.
	 *
	 * @return void
	 */
	public function single_apply_page() {
		$plugin_info = self::$plugin_info;
		$core        = $this->core;

		require_once self::$plugin_info['path'] . 'templates/single-apply-watermarks-template.php';
	}

	/**
	 * Bulk Apply page.
	 *
	 * @return void
	 */
	public function bulk_apply_page() {
		$plugin_info  = self::$plugin_info;
		$core         = $this->core;
		$settings_obj = $this;

		require_once self::$plugin_info['path'] . 'templates/bulk-apply-watermarks-template.php';
	}
}

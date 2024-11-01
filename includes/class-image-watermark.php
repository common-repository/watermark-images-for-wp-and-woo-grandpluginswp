<?php
namespace GPLSCore\GPLS_PLUGIN_WMFW;

use GPLSCore\GPLS_PLUGIN_WMFW\GD_Image;
use GPLSCore\GPLS_PLUGIN_WMFW\Imagick_Image;

/**
 * Image Watermark.
 */
class Image_Watermark {


	/**
	 * Plugin Info Array.
	 *
	 * @var array
	 */
	private static $plugin_info;

	/**
	 * Image Library Class Name.
	 *
	 * @var string
	 */
	private static $img_lib_class = null;

	/**
	 * Image Library Object.
	 *
	 * @var GD_Image|Imagick_Image
	 */
	private static $img_lib = null;
	/**
	 * Watermarks Array.
	 *
	 * @var array
	 */
	protected $watermarks = array();

	/**
	 * Image Details Array.
	 *
	 * @var array
	 */
	protected $img = array();

	/**
	 * Position Spots Mapping.
	 *
	 * @var array
	 */
	public static $spots_mapping = array(
		'tl' => array(
			'left' => 0,
			'top'  => 0,
		),
		'tm' => array(
			'left' => 1,
			'top'  => 0,
		),
		'tr' => array(
			'left' => 2,
			'top'  => 0,
		),
		'ml' => array(
			'left' => 0,
			'top'  => 1,
		),
		'mm' => array(
			'left' => 1,
			'top'  => 1,
		),
		'mr' => array(
			'left' => 2,
			'top'  => 1,
		),
		'bl' => array(
			'left' => 0,
			'top'  => 2,
		),
		'bm' => array(
			'left' => 1,
			'top'  => 2,
		),
		'br' => array(
			'left' => 2,
			'top'  => 2,
		),
	);

	/**
	 * Setup Image for Watermarking.
	 *
	 * @param array $img
	 * @param array $watermark
	 * @return self|\WP_Error
	 */
	public static function setup_image_for_watermarking( $img, $watermark ) {
		$watermark_img = new self( $img, $watermark );
		$result        = $watermark_img->init_image_library();
		if ( ! $result ) {
			return new \WP_Error(
				self::$plugin_info['name'] . '-unsupported-type',
				esc_html( sprintf( esc_html__( 'Image format is not supported : %s' ), $img['path'] ) )
			);
		}

		return $watermark_img;
	}

	/**
	 * Constructor.
	 */
	public function __construct( $img, $watermarks ) {
		$this->img        = $img;
		$this->watermarks = $watermarks;
	}

	/**
	 * Initialize GIF Watermark Class.
	 *
	 * @param array $plugin_info Plugin Info Array.
	 * @return void
	 */
	public static function init( $plugin_info ) {
		self::$plugin_info = $plugin_info;
		self::hooks();
	}

	/**
	 * Setup available Image library.
	 *
	 * @return boolean
	 */
	private function setup_image_library() {
		// Check Libs avilability.
		if ( ! self::is_imagick_enabled() && ! self::is_gd_enabled() ) {
			return false;
		}

		$img_mime_type = @mime_content_type( $this->img['path'] );
		if ( ! $img_mime_type ) {
			return false;
		}

		if ( Imagick_Image::supports_mime_type( $img_mime_type ) ) {
			self::$img_lib_class = __NAMESPACE__ . '\Imagick_Image';
			self::$img_lib       = new self::$img_lib_class( $this->img );
			return true;
		}

		if ( GD_Image::supports_mime_type( $img_mime_type ) ) {
			self::$img_lib_class = __NAMESPACE__ . '\GD_Image';
			self::$img_lib       = new self::$img_lib_class( $this->img );
			return true;
		}

		return false;
	}

	/**
	 * Check if the imagick lib is enabled.
	 *
	 * @return boolean
	 */
	public static function is_imagick_enabled() {
		return ( extension_loaded( 'imagick' ) && class_exists( '\Imagick', false ) && class_exists( '\ImagickPixel', false ) );
	}

	/**
	 * Check if the gd lib is enabled.
	 *
	 * @return boolean
	 */
	public static function is_gd_enabled() {
		return ( extension_loaded( 'gd' ) && function_exists( 'gd_info' ) );
	}

	/**
	 * Initialize the available image extension.
	 *
	 * @param array $img Image Details Array.
	 * @return boolean
	 */
	private function init_image_library() {
		return $this->setup_image_library();
	}

	/**
	 * Get Image Resource.
	 *
	 * @param string  $img_path Image FILE PATH.
	 * @param boolean $include_type Return key value.
	 * @return object
	 */
	public static function get_image_resource( $img_path, $include_type = false ) {
		return self::$img_lib_class::get_image_resource( $img_path, $include_type );
	}

	/**
	 * Resize Image.
	 *
	 * @param object $img_resource Image Resource.
	 * @param string $img_resource Image PATH.
	 * @param int    $width Image Width.
	 * @param int    $height Image Height.
	 *
	 * @return object
	 */
	public static function resize( &$img_resource, $img_path, $width, $height ) {
		return self::$img_lib_class::resize( $img_resource, $img_path, $width, $height );
	}

	/**
	 * Resource to String.
	 *
	 * @param object $img_resource
	 * @param string $img_type
	 * @return string
	 */
	public static function resource_to_string( $img_resource, $img_type ) {
		return self::$img_lib_class::resource_to_string( $img_resource, $img_type );
	}

	/**
	 * GIF Watermark Hooks.
	 *
	 * @return void
	 */
	private static function hooks() {
		add_filter( 'wp_prepare_attachment_for_js', array( get_called_class(), 'add_watermark_chosen_size_to_js_object' ), 100, 3 );
		add_filter( 'wp_get_attachment_url', array( get_called_class(), 'force_cache_bust_after_watermarking' ), 100, 2 );
		add_filter( 'wp_get_attachment_image_src', array( get_called_class(), 'force_cache_bust_in_media_listing' ), 100, 4 );
	}


	/**
	 * Force Cache Busting for images after adding watermarks to purge the browser cached version.
	 *
	 * @param string $image Image Array ( 0 => URL, 1 => width, 2 => height, 3 => is a resized image boolean ).
	 * @param int    $attachment_id Attachment Post ID.
	 * @param int    $size Target Size.
	 * @param int    $icon where fallback to icon.
	 * @return array
	 */
	public static function force_cache_bust_in_media_listing( $image, $attachment_id, $size, $icon ) {
		if ( is_array( $image ) && ! empty( $_SERVER['HTTP_REFERER'] ) && ( admin_url( 'upload.php' ) === esc_url( strtok( wp_unslash( $_SERVER['HTTP_REFERER'] ), '?' ) ) ) ) {
			$image[0] = add_query_arg(
				array(
					'refresh'     => wp_generate_password( 5, false, false ),
					'dontreplace' => '',
				),
				$image[0]
			);
		}
		return $image;
	}

	/**
	 * Force Cache Busting for images after adding watermarks to purge the browser cached version.
	 *
	 * @param string $url
	 * @param int    $post_id
	 * @return string $url
	 */
	public static function force_cache_bust_after_watermarking( $url, $post_id ) {
		if ( ! empty( $url ) && ! empty( $_REQUEST[ self::$plugin_info['classes_prefix'] . '-force-img-refresh' ] ) || ( ( ! empty( $_SERVER['HTTP_REFERER'] ) ) && admin_url( 'upload.php' ) === esc_url( strtok( wp_unslash( $_SERVER['HTTP_REFERER'] ), '?' ) ) ) ) {
			$url = add_query_arg(
				array(
					'refresh'     => wp_generate_password( 5, false, false ),
					'dontreplace' => '',
				),
				$url
			);
		}
		return $url;
	}

	/**
	 * Initialize the watermarks images sources.
	 *
	 * @return false|\WP_Error
	 */
	public function get_img_watermark_resource( &$watermark ) {
		// 1) Get the watermark PATH.
		$watermark_original_full_path = wp_get_original_image_path( $watermark['imgID'] );
		$watermark_original_file_name = wp_basename( $watermark_original_full_path );
		$watermark_size_file_name     = wp_basename( $watermark['url'] );
		$watermark_path               = str_replace( $watermark_original_file_name, $watermark_size_file_name, $watermark_original_full_path );

		// 2) Get Image Resource from the watermark PATH.
		$resource = self::get_image_resource( $watermark_path );
		self::resize( $resource, $watermark_path, $watermark['width'], $watermark['height'] );
		return $resource;
	}

	/**
	 * Add Image Watermark to Image.
	 *
	 * @param \GdImage $img_resource  GD Resource.
	 * @param array    $watermark Watermark Details Array.
	 * @param \GdImage $watermark_resource Watermark Resource Object.
	 * @return void
	 */
	public function add_image_watermark( &$img_resource, $watermark, $watermark_resource ) {
		self::$img_lib->add_image_watermark( $img_resource, $watermark, $watermark_resource );
	}

	/**
	 * Add Text Watermark to Image.
	 *
	 * @param \GdImage $img_resource GD Resource.
	 * @param array    $watermark Watermark Info Array.
	 * @return void
	 */
	public function add_text_watermark( &$img_resource, $watermark ) {
		self::$img_lib->add_text_watermark( $img_resource, $watermark );
	}

	/**
	 * Draw Watermarks On Given Image.
	 *
	 * @return array|\WP_Error
	 */
	public function draw_watermarks_on_image( $is_preview = false ) {
		if ( is_null( self::$img_lib ) ) {
			return new \WP_Error( 'image_no_editor', esc_html__( 'No editor could be selected.' ) );
		}
		$result = array();
		// 1) Load the Image resource.
		$image_details = self::get_image_resource( $this->img['path'], true );
		if ( is_wp_error( $image_details ) ) {
			return $image_details;
		}
		$result['img_details'] = $this->img;

		// 2) Loop over watermarks and draw watermarks on the image.
		foreach ( $this->watermarks as $watermark ) {
			$watermark['styles']['opacity'] = 1;
			if ( 'image' === $watermark['type'] ) {
				// 1) Get the image watermark resource.
				$watermark_resource = $this->get_img_watermark_resource( $watermark );
				// 2) Draw the watermark.
				$this->add_image_watermark( $image_details['resource'], $watermark, $watermark_resource );
				// 3) Clear the watermark resource.
				self::clear_image_resource( $watermark_resource );
			} elseif ( 'text' === $watermark['type'] ) {
				// TODO: maybe used later!
				// $watermark = $this->scale_text_watermarks( $watermark );
				$this->add_text_watermark( $image_details['resource'], $watermark );
			}
		}

		// 3) Convert the image resource to string.
		$result['img_string'] = Image::resource_to_string( $image_details['resource'], $image_details['type'] );

		// Clear Image resource.
		self::clear_image_resource( $image_details['resource'] );

		return $result;
	}

	/**
	 * Clear Image Resource.
	 *
	 * @param \GdImage|\Imagick $img_resource Image Resrouce.
	 * @return boolean
	 */
	public static function clear_image_resource( $img_resource ) {
		return self::$img_lib::clear_resource( $img_resource );
	}

	/**
	 * Get Font PATH.
	 *
	 * @param string $font_family_name the name of the font file.
	 * @return string
	 */
	public static function get_font_path( $font_family_name ) {
		$fonts = self::get_available_fonts( true );
		if ( ! empty( $fonts[ $font_family_name ] ) ) {
			return $fonts[ $font_family_name ]['path'];
		} else {
			return $fonts['georgia']['path'];
		}
	}

	/**
	 * List Fonts CSS.
	 *
	 * @return string
	 */
	public static function list_fonts_css( $plugin_info ) {
		$css   = '';
		$fonts = self::get_available_fonts( true );
		ob_start();
		foreach ( $fonts as $font_family_name => $font ) :
			?>@font-face {
			font-family: "<?php echo esc_attr( $font_family_name ); ?>";
			src: url( '<?php echo esc_url_raw( $font['url'] ); ?>' ) format('truetype');
		}
			<?php
		endforeach;
		$css .= ob_get_clean();
		return $css;
	}

	/**
	 * Add subsizes to watermarks Modal media and force refresh param on images URLs.
	 *
	 * @param array  $response
	 * @param object $attachment
	 * @param array  $meta
	 * @return array
	 */
	public static function add_watermark_chosen_size_to_js_object( $response, $attachment, $meta ) {
		// Check if its auto apply watermarks, force refresh link.
		if ( ( ! empty( $GLOBALS[ self::$plugin_info['name'] . '-is-auto-apply-watermarks-template' ] ) && ! empty( $response['type'] ) && ( 'image' === $response['type'] ) ) || ( ! empty( $_SERVER['HTTP_REFERER'] ) && admin_url( 'upload.php' ) === esc_url( strtok( wp_unslash( $_SERVER['HTTP_REFERER'] ), '?' ) ) ) ) {
			// cache bust refresh.
			$cache_bust_refresh = wp_generate_password( 5, false, false );
			if ( ! empty( $response['size']['url'] ) ) {
				$response['size']['url'] = add_query_arg(
					array(
						'refresh'     => $cache_bust_refresh,
						'dontreplace' => '',
					),
					$response['size']['url']
				);
			}
			if ( ! empty( $response['sizes'] ) ) {
				foreach ( $response['sizes'] as $size_name => $size_arr ) {
					if ( ! empty( $response['sizes'][ $size_name ]['url'] ) ) {
						$response['sizes'][ $size_name ]['url'] = add_query_arg(
							array(
								'refresh'     => $cache_bust_refresh,
								'dontreplace' => '',
							),
							$response['sizes'][ $size_name ]['url']
						);
					}
				}

			}
		}
		return $response;
	}


	/**
	 * Get available Fonts for Text watermark.
	 *
	 * @param boolean $prepare Return paths only or paths with names.
	 * @return array
	 */
	public static function get_available_fonts( $prepare = false ) {
		$fonts_path  = self::$plugin_info['path'] . 'assets/dist/fonts/';
		$fonts_url   = self::$plugin_info['url'] . 'assets/dist/fonts/';
		$fonts_files = array();

		require_once \ABSPATH . 'wp-admin/includes/file.php';

		$fonts = list_files( $fonts_path, 1 );
		if ( ! $prepare ) {
			return $fonts;
		}

		foreach ( $fonts as $font_file ) {
			$font_name                        = wp_basename( $font_file );
			$font_ext                         = pathinfo( $font_file, PATHINFO_EXTENSION );
			$font_name_without_ext            = wp_basename( $font_file, '.' . $font_ext );
			$font_family_name                 = sanitize_title_with_dashes( $font_name_without_ext );
			$font_title                       = str_replace( array( '-', '_' ), ' ', $font_name_without_ext );
			$fonts_files[ $font_family_name ] = array(
				'title'       => $font_title,
				'path'        => $font_file,
				'font_family' => $font_family_name,
				'url'         => $fonts_url . $font_name,
				'name'        => $font_name,
			);
		}

		// Custom PATH.
		$uploads            = wp_upload_dir();
		$fonts_path         = trailingslashit( $uploads['basedir'] ) . trailingslashit( self::$plugin_info['name'] );
		$fonts_url          = trailingslashit( $uploads['baseurl'] ) . trailingslashit( self::$plugin_info['name'] );
		$custom_fonts_files = list_files( $fonts_path, 1 );
		foreach ( $custom_fonts_files as $font_file ) {
			$font_name                        = wp_basename( $font_file );
			$font_ext                         = pathinfo( $font_file, PATHINFO_EXTENSION );
			$font_name_without_ext            = wp_basename( $font_file, '.' . $font_ext );
			$font_family_name                 = sanitize_title_with_dashes( $font_name_without_ext );
			$font_title                       = str_replace( array( '-', '_' ), ' ', $font_name_without_ext );
			$fonts_files[ $font_family_name ] = array(
				'title'       => $font_title,
				'path'        => $font_file,
				'font_family' => $font_family_name,
				'url'         => $fonts_url . $font_name,
				'name'        => $font_name,
			);
		}

		return $fonts_files;
	}

}

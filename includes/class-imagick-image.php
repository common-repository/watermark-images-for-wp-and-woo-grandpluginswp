<?php
namespace GPLSCore\GPLS_PLUGIN_WMFW;

use GPLSCore\GPLS_PLUGIN_WMFW\Watermark_Base;
use GPLSCore\GPLS_PLUGIN_WMFW\Image_Watermark;

/**
 * Image Operation Using Imagick Library.
 */
class Imagick_Image extends Watermark_Base {

	/**
	 * Image Details Array.
	 *
	 * @var Array
	 */
	protected $img = array();

	/**
	 * Imagick WP Editor.
	 *
	 * @var object
	 */
	private static $imagick_editor = null;

	/**
	 * Constructor.
	 *
	 * @param array $img Image Details Array.
	 */
	public function __construct( $img ) {
		$this->img = $img;
	}

	/**
	 * Add Image Watermark to Image.
	 *
	 * @param \GdImage $img_resource  GD Resource.
	 * @param array    $watermark Watermark Details Array.
	 * @param \GdImage $watermark_resource Watermark Resource Object.
	 *
	 * @return void
	 */
	public function add_image_watermark( &$img_resource, $watermark, $watermark_resource ) {
		$degree               = (float) round( $watermark['styles']['degree'] );
		$degree               = ( $degree < 0 ? ( 360 + $degree ) : $degree );
		$position             = $this->calculate_watermark_position( $watermark, $this->img );
		$watermark['absLeft'] = $position['left'];
		$watermark['absTop']  = $position['top'];

		if ( ! empty( $watermark['centerOffset'] ) && ( 'true' === $watermark['centerOffset'] || 'yes' === $watermark['centerOffset'] ) ) {
			$watermark = $this->top_left_after_rotation_around_center( $watermark, $degree );
		}

		$watermark = $this->image_watermark_position_from_rotation( $watermark, $degree );

		$watermark_resource->setImageAlphaChannel( \Imagick::ALPHACHANNEL_SET );

		$watermark_resource->evaluateImage( \Imagick::EVALUATE_MULTIPLY, $watermark['styles']['opacity'], \Imagick::CHANNEL_ALPHA );

		$watermark_resource->setImageGravity( \imagick::GRAVITY_CENTER );

		$watermark_resource->rotateImage( new \ImagickPixel( 'none' ), $degree );

		$img_resource->setGravity( \imagick::GRAVITY_CENTER );

		$img_resource->compositeImage( $watermark_resource, \Imagick::COMPOSITE_OVER, (int) $watermark['absLeft'], (int) $watermark['absTop'] );

		$this->repeat_watermark( $img_resource, $watermark_resource, $watermark, $watermark['absLeft'], $watermark['absTop'], 'image' );
	}

	/**
	 * Checks to see if editor supports the mime-type specified.
	 *
	 * @since 3.5.0
	 *
	 * @param string $mime_type
	 * @return bool
	 */
	public static function supports_mime_type( $mime_type ) {
		$imagick_extension = strtoupper( self::get_extension( $mime_type ) );

		if ( ! $imagick_extension ) {
			return false;
		}

		// setIteratorIndex is optional unless mime is an animated format.
		// Here, we just say no if you are missing it and aren't loading a jpeg.
		if ( ! method_exists( 'Imagick', 'setIteratorIndex' ) && 'image/jpeg' !== $mime_type ) {
				return false;
		}

		try {
			// phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
			return ( class_exists( '\Imagick' ) && (bool) @\Imagick::queryFormats( $imagick_extension ) );
		} catch ( \Exception $e ) {
			return false;
		}
	}

	/**
	 * Returns first matched extension from Mime-type,
	 * as mapped from wp_get_mime_types()
	 *
	 * @since 3.5.0
	 *
	 * @param string $mime_type
	 * @return string|false
	 */
	protected static function get_extension( $mime_type = null ) {
		if ( empty( $mime_type ) ) {
			return false;
		}
		return wp_get_default_extension_for_mime_type( $mime_type );
	}

	/**
	 * Load needed image libs.
	 *
	 * @return void
	 */
	private static function load_img_editor() {
		if ( is_null( self::$imagick_editor ) ) {
			require_once \ABSPATH . \WPINC . '/class-wp-image-editor.php';
			require_once \ABSPATH . \WPINC . '/class-wp-image-editor-imagick.php';

			self::$imagick_editor = new \WP_Image_Editor_Imagick();
		}
	}

	/**
	 * Add Text Watermark to Image.
	 *
	 * @param \GdImage $img_resource GD Resource.
	 * @param array    $watermark Watermark Info Array.
	 * @return void
	 */
	public function add_text_watermark( &$img_resource, $watermark ) {
		// Setup the position.
		$degree                        = (int) ( $watermark['styles']['degree'] );
		$degree                        = ( $degree < 0 ? ( 360 + $degree ) : $degree );
		$degree_in_radians             = round( $degree * M_PI / 180, 2 );
		$position                      = $this->calculate_watermark_position( $watermark, $this->img );
		$watermark['styles']['degree'] = $degree;
		$watermark['absLeft']          = $position['left'];
		$watermark['absTop']           = $position['top'];

		if ( ! empty( $watermark['centerOffset'] ) && ( 'true' === $watermark['centerOffset'] || 'yes' === $watermark['centerOffset'] ) ) {
			$watermark = $this->top_left_after_rotation_around_center( $watermark, $degree );
		}

		$watermark['botLeft']          = round( $watermark['absLeft'] ) - round( ( $watermark['height'] ) * sin( $degree_in_radians ) );
		$watermark['botTop']           = round( $watermark['absTop'] ) + round( ( $watermark['height'] ) * cos( $degree_in_radians ) );
		list( $h_spacing, $v_spacing ) = $this->text_watermark_position_from_rotation( $watermark, $degree );
		$font_baseline_left            = intval( $watermark['botLeft'] ) + $h_spacing + 2;
		$font_baseline_top             = intval( $watermark['botTop'] ) - $v_spacing;

		$draw = new \ImagickDraw();

		$draw->setFont( Image_Watermark::get_font_path( $watermark['styles']['font']['fontFamily'] ) );
		$draw->setFontSize( $watermark['styles']['font']['fontSize'] );
		$draw->setFillColor( $watermark['styles']['font']['color'] );
		$draw->setFillOpacity( floatval( $watermark['styles']['opacity'] ) );

		$img_resource->annotateImage( $draw, $font_baseline_left, $font_baseline_top, $degree, $watermark['text'] );

		$this->repeat_watermark( $img_resource, $draw, $watermark, $font_baseline_left, $font_baseline_top, 'text' );

		// Clear other resources.
		$draw->clear();
	}

	/**
	 * Copy the watermark on the image.
	 *
	 * @param object $img_resource        Image Resource.
	 * @param object $watermark_resource  Watermark Resource
	 * @param array  $watermark           Watermark Details Array.
	 * @param int    $left                Left coordinate.
	 * @param int    $top                 Top Coordinate
	 * @param string $type                Watermark Type [ image | text ].
	 * @return void
	 */
	protected function draw_watermark_on_image( &$img_resource, &$watermark_resource, $watermark, $left, $top, $type = 'image' ) {
		// 1) Get Image Resource from the frame string.
		if ( 'image' === $type ) {
			$img_resource->compositeImage( $watermark_resource, \Imagick::COMPOSITE_OVER, (int) $left, (int) $top );
		} elseif ( 'text' === $type ) {
			$img_resource->annotateImage( $watermark_resource, $left, $top, $watermark['styles']['degree'], $watermark['text'] );
		}
	}

	/**
	 * Get Image Resource
	 *
	 * @param string  $img_path
	 * @param boolean $include_type
	 * @return mixed|\WP_Error
	 */
	public static function get_image_resource( $img_path, $include_type = false ) {
		try {
			$imgick = new \Imagick( $img_path );
			if ( $include_type ) {
				return array(
					'resource' => $imgick,
					'type'     => $imgick->getImageType(),
				);
			} else {
				return $imgick;
			}
		} catch ( \Exception $e ) {
			return new \WP_Error(
				self::$plugin_info['name'] . '-imagick-failed-img-resource',
				esc_html__( 'Image type is not supported' )
			);
		}

	}

	/**
	 * Clear Resource.
	 *
	 * @param \Imagick $img_resource Image Resource.
	 * @return boolean
	 */
	public static function clear_resource( $img_resource ) {
		return $img_resource->clear();
	}

	/**
	 * Convert Image Resource to string.
	 *
	 * @param \GdImage $img_resource  Image Resource.
	 * @param string   $img_type  Image Type.
	 * @return string|\WP_Error
	 */
	public static function resource_to_string( $img_resource, $img_type ) {
		return $img_resource->getImageBlob();
	}

	/**
	 * Resize Image.
	 *
	 * @param \Imagick $img_resource
	 * @param string   $img_path
	 * @param int      $dst_w
	 * @param int      $dst_h
	 * @return true|\WP_Error
	 */
	public static function resize( &$img_resource, $img_path, $dst_w, $dst_h ) {
		$allowed_filters = array(
			'FILTER_POINT',
			'FILTER_BOX',
			'FILTER_TRIANGLE',
			'FILTER_HERMITE',
			'FILTER_HANNING',
			'FILTER_HAMMING',
			'FILTER_BLACKMAN',
			'FILTER_GAUSSIAN',
			'FILTER_QUADRATIC',
			'FILTER_CUBIC',
			'FILTER_CATROM',
			'FILTER_MITCHELL',
			'FILTER_LANCZOS',
			'FILTER_BESSEL',
			'FILTER_SINC',
		);

		$width     = $img_resource->getImageWidth();
		$height    = $img_resource->getImageHeight();
		$mime_type = $img_resource->getImageMimeType();
		$filter    = defined( 'Imagick::FILTER_TRIANGLE' ) ? \Imagick::FILTER_TRIANGLE : false;
		try {

			if ( is_callable( array( $img_resource, 'sampleImage' ) ) ) {
				$resize_ratio  = ( $dst_w / $width ) * ( $dst_h / $height );
				$sample_factor = 5;

				if ( $resize_ratio < .111 && ( $dst_w * $sample_factor > 128 && $dst_h * $sample_factor > 128 ) ) {
					$img_resource->sampleImage( $dst_w * $sample_factor, $dst_h * $sample_factor );
				}
			}

			if ( is_callable( array( $img_resource, 'resizeImage' ) ) && $filter ) {
				$img_resource->setOption( 'filter:support', '2.0' );
				$img_resource->resizeImage( $dst_w, $dst_h, $filter, 1 );
			} else {
				$img_resource->scaleImage( $dst_w, $dst_h );
			}

			// Set appropriate quality settings after resizing.
			if ( 'image/jpeg' === $mime_type ) {
				if ( is_callable( array( $img_resource, 'unsharpMaskImage' ) ) ) {
					$img_resource->unsharpMaskImage( 0.25, 0.25, 8, 0.065 );
				}

				$img_resource->setOption( 'jpeg:fancy-upsampling', 'off' );
			}

			if ( in_array( $mime_type, array( 'image/png', 'image/x-png' ) ) ) {
				$img_resource->setOption( 'png:compression-filter', '5' );
				$img_resource->setOption( 'png:compression-level', '9' );
				$img_resource->setOption( 'png:compression-strategy', '1' );
				$img_resource->setOption( 'png:exclude-chunk', 'all' );
			}
			if ( is_callable( array( $img_resource, 'getImageAlphaChannel' ) )
				&& is_callable( array( $img_resource, 'setImageAlphaChannel' ) )
				&& defined( 'Imagick::ALPHACHANNEL_UNDEFINED' )
				&& defined( 'Imagick::ALPHACHANNEL_OPAQUE' )
			) {
				if ( $img_resource->getImageAlphaChannel() === \Imagick::ALPHACHANNEL_UNDEFINED ) {
					$img_resource->setImageAlphaChannel( \Imagick::ALPHACHANNEL_OPAQUE );
				}
			}

			if ( is_callable( array( $img_resource, 'getImageDepth' ) ) && is_callable( array( $img_resource, 'setImageDepth' ) ) ) {
				if ( 8 < $img_resource->getImageDepth() ) {
					$img_resource->setImageDepth( 8 );
				}
			}

			if ( is_callable( array( $img_resource, 'setInterlaceScheme' ) ) && defined( 'Imagick::INTERLACE_NO' ) ) {
				$img_resource->setInterlaceScheme( \Imagick::INTERLACE_NO );
			}

			return true;
		} catch ( \Exception $e ) {
			return new \WP_Error( 'image_resize_error', $e->getMessage() );
		}
	}

}

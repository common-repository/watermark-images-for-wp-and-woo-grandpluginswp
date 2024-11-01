<?php
namespace GPLSCore\GPLS_PLUGIN_WMFW;

/**
 * Watemarks Base Class.
 */
class Watermark_Base {

	/**
	 * Plugin Info array.
	 *
	 * @var array
	 */
	protected static $plugin_info = array();

	/**
	 * Preview Image Transient Key.
	 *
	 * @var string
	 */
	protected static $preview_transient_key;

	/**
	 * Preview Image Filename
	 *
	 * @var string
	 */
	protected static $preview_filename;

	/**
	 * Transient Expiry Duration.
	 *
	 * @var int
	 */
	protected static $transient_expiry = 60 * 60;

	/**
	 * Image Types mapping.
	 *
	 * @var array
	 */
	protected static $img_types = array(
		1  => 'gif',
		2  => 'jpeg',
		3  => 'png',
		15 => 'wbmp',
		19 => 'avif',
	);

	/**
	 * Init Function.
	 *
	 * @param array $plugin_info Plugin Info.
	 * @return void
	 */
	public static function init( $plugin_info ) {
		self::$plugin_info           = $plugin_info;
		self::$preview_transient_key = self::$plugin_info['name'] . '-watermark-img-transient-key';
		self::$preview_filename      = 'preview-img-tmp-' . self::$plugin_info['name'];
		self::hooks();
	}

	/**
	 * Base Hooks.
	 *
	 * @return void
	 */
	public static function hooks() {
		add_action( 'delete_expired_transients', array( get_called_class(), 'remove_preview_image_file' ), 9 );
	}

	/**
	 * Remove temp preview File when [ deleting expired transients | Deactivate ].
	 *
	 * @param boolean $force_delete  Whether to force delete the preview file or check for expiration.
	 *
	 * @return void
	 */
	public static function remove_preview_image_file( $force_delete = false ) {
		$uploads           = wp_get_upload_dir();
		$transient_option  = '_transient_' . self::$preview_transient_key;
		$transient_timeout = '_transient_timeout_' . self::$preview_transient_key;
		$timeout           = get_option( $transient_timeout );
		$preview_img_arr   = get_option( $transient_option );
		if ( $force_delete || ( false !== $timeout && $timeout < time() ) ) {
			if ( ! empty( $preview_img_arr ) && is_array( $preview_img_arr ) && ! empty( $preview_img_arr['relative_path'] ) ) {
				$preview_path = untrailingslashit( $uploads['basedir'] ) . $preview_img_arr['relative_path'];
				@unlink( $preview_path );
				delete_option( $transient_option );
				delete_option( $transient_timeout );
			}
		}
	}

	/**
	 * Get Preview Image.
	 *
	 * @return string|\WP_Error
	 */
	public static function get_preview_image( $part = null ) {
		$preview_image_arr = get_transient( self::$preview_transient_key );
		if ( false === $preview_image_arr ) {
			return new \WP_Error(
				self::$plugin_info['name'] . '-get-preview-image-error',
				esc_html__( 'Preview Image is expired, Please click on Preview Watermarks button again!', 'watermark-images-for-wp-and-woo-grandpluginswp' )
			);
		}
		if ( ! empty( $part ) ) {
			return $preview_image_arr[ $part ];
		}
		return $preview_image_arr;
	}

	/**
	 * Save Preview Image.
	 *
	 * @param string $img_string Image String.
	 * @return mixed|\WP_Error
	 */
	public static function save_preview( $img_string, $img_details, $return_part = null ) {
		if ( ! WP_Filesystem() ) {
			return new \WP_Error(
				self::$plugin_info['name'] . '-save-preview-image-error',
				esc_html__( 'Unable to connect to the filesystem', 'watermark-images-for-wp-and-woo-grandpluginswp' )
			);
		}
		global $wp_filesystem;
		$uploads = wp_upload_dir();
		$ext     = pathinfo( $img_details['path'], PATHINFO_EXTENSION );
		$result  = $wp_filesystem->put_contents( trailingslashit( $uploads['path'] ) . self::$preview_filename . '.' . $ext, $img_string, 0666 );
		if ( ! $result ) {
			return new \WP_Error(
				self::$plugin_info['name'] . '-save-preview-image-error',
				esc_html__( 'Unable to save the preview image', 'watermark-images-for-wp-and-woo-grandpluginswp' )
			);
		}
		$preview_image_arr = array(
			'title'         => self::$preview_filename,
			'relative_path' => trailingslashit( $uploads['subdir'] ) . self::$preview_filename . '.' . $ext,
		);
		set_transient( self::$preview_transient_key, $preview_image_arr, self::$transient_expiry );
		$preview_image_arr['url']  = trailingslashit( $uploads['url'] ) . self::$preview_filename . '.' . $ext;
		$preview_image_arr['path'] = trailingslashit( $uploads['path'] ) . self::$preview_filename . '.' . $ext;
		if ( ! empty( $return_part ) ) {
			return $preview_image_arr[ $return_part ];
		}
		return $preview_image_arr;
	}

	/**
	 * Save Image into FILe.
	 *
	 * @param string $img Image String.
	 * @param array  $image_details  Image details Array.
	 * @return true|\WP_Error
	 */
	public static function save_watermarked_image( $img, $img_details ) {
		$time    = current_time( 'mysql' );
		$uploads = wp_upload_dir( $time );

		// 1) Put the imge stream string into the file path.
		$watermarked = file_put_contents( $img_details['path'], $img );
		if ( false === $watermarked ) {
			return new \WP_Error(
				self::$plugin_info['name'] . '-save-watermarked-image-error',
				esc_html__( 'Failed to create watermarked image!', 'watermark-images-for-wp-and-woo-grandpluginswp' )
			);
		}

		// 2) Set correct file permissions.
		$stat  = stat( dirname( $img_details['path'] ) );
		$perms = $stat['mode'] & 0000666; // Same permissions as parent folder, strip off the executable bits.
		chmod( $img_details['path'], $perms );

		return true;
	}


	/**
	 * Get Top and Left Point after rotating watermark around center.
	 *
	 * @param int $x
	 * @param int $y
	 * @param int $width
	 * @param int $height
	 * @param int $rotationAngle
	 * @return array
	 */
	public function top_left_after_rotation_around_center( $watermark, $rotation_angle ) {
		$center_x             = $watermark['absLeft'];
		$center_y             = $watermark['absTop'];
		$width                = $watermark['width'];
		$height               = $watermark['height'];
		$rad                  = sqrt( $width * $width + $height * $height ) / 2;
		$rect_deg             = atan2( $height, $width );
		$rot_deg              = $rotation_angle * M_PI / 180;
		$top_left_x           = $rad * cos( $rot_deg + ( $rect_deg ) );
		$top_left_y           = $rad * sin( $rot_deg + ( $rect_deg ) );
		$watermark['absLeft'] = $center_x - $top_left_x;
		$watermark['absTop']  = $center_y - $top_left_y;

		return $watermark;
	}

	/**
	 * Calculate Watermark Position based on the position Spot and offset.
	 *
	 * @param array $watmark
	 * @param array $img
	 * @return array|false
	 */
	public function calculate_watermark_position( $watermark, $img ) {
		$box_mapping   = Image_Watermark::$spots_mapping[ $watermark['positionSpot'] ];
		$square_width  = round( $img['width'] / 3 );
		$square_height = round( $img['height'] / 3 );

		if ( 'pixel' === $watermark['positionType'] ) {
			$pos = array(
				'left' => round( $box_mapping['left'] * $square_width ) + intval( ! empty( $img['width_ratio'] ) ? round( $img['width_ratio'] * $watermark['absLeft'] ) : $watermark['absLeft'] ),
				'top'  => round( $box_mapping['top'] * $square_height ) + intval( ! empty( $img['height_ratio'] ) ? round( $img['height_ratio'] * $watermark['absTop'] ) : $watermark['absTop'] ),
			);
		} elseif ( 'percent' === $watermark['positionType'] ) {
			$pos = array(
				'left' => round( $box_mapping['left'] * $square_width ) + intval( intval( $square_width ) * floatval( $watermark['leftPercent'] ) / 100 ),
				'top'  => round( $box_mapping['top'] * $square_height ) + intval( intval( $square_height ) * floatval( $watermark['topPercent'] ) / 100 ),
			);
		}
		if ( $pos ) {
			if ( ( 'image' === $watermark['type'] ) && ! empty( $watermark['centerOffset'] ) && ( 'true' === $watermark['centerOffset'] || 'yes' === $watermark['centerOffset'] ) ) {
				$pos['left'] = $pos['left'] - round( $watermark['width'] / 2 );
				$pos['top']  = $pos['top'] - round( $watermark['height'] / 2 );
			}
			return $pos;
		}
		return false;
	}

	/**
	 * Repeat Watermark.
	 *
	 * @param object $img_resource       Image Resource.
	 * @param object $watermark_resource Watermark Resource.
	 * @param array  $watermark          Watermark Details Array.
	 * @return void
	 */
	protected function repeat_watermark( &$img_resource, &$watermark_resource, $watermark, $x, $y, $type = 'image' ) {
		// 1) Check if the watermark is repeated.
		if ( ! $watermark['isRepeat'] || empty( $watermark['repeatAxis'] ) ) {
			return;
		}
		$base_x        = $x;
		$base_y        = $y;
		$x_axis_offset = absint( $watermark['repeatXAxisOffset'] );
		$y_axis_offset = absint( $watermark['repeatYAxisOffset'] );
		if ( 'x' === $watermark['repeatAxis'] ) {
			if ( $x_axis_offset <= 0 ) {
				return;
			}
			$x += $x_axis_offset;
			while ( $x < $this->img['width'] ) {
				$this->draw_watermark_on_image( $img_resource, $watermark_resource, $watermark, $x, $y, $type );
				$x += $x_axis_offset;
			}
		} elseif ( 'y' === $watermark['repeatAxis'] ) {
			if ( $y_axis_offset <= 0 ) {
				return;
			}
			$y    += $y_axis_offset;
			$y_top = intval( $y - ( intval( $watermark['height'] ) * cos( floatval( $watermark['styles']['degree'] ) ) ) );
			while ( $y < $this->img['height'] || $y_top < $this->img['height'] ) {
				$this->draw_watermark_on_image( $img_resource, $watermark_resource, $watermark, $x, $y, $type );
				$y    += $y_axis_offset;
				$y_top = intval( $y - ( intval( $watermark['height'] ) * cos( floatval( $watermark['styles']['degree'] ) ) ) );
			}
		} elseif ( 'diagonal' === $watermark['repeatAxis'] ) {
			if ( $y_axis_offset <= 0 && $x_axis_offset <= 0 ) {
				return;
			}
			$y_top = intval( $y - ( intval( $watermark['height'] ) * cos( floatval( $watermark['styles']['degree'] ) ) ) );
			while ( ( $y < $this->img['height'] || $y_top < $this->img['height'] ) && ( $x < $this->img['width'] ) ) {
				$x += $x_axis_offset;
				$y += $y_axis_offset;
				$this->draw_watermark_on_image( $img_resource, $watermark_resource, $watermark, $x, $y, $type );
			}
		} elseif ( 'both' === $watermark['repeatAxis'] ) {
			if ( $x_axis_offset > 0 ) {
				$x += $x_axis_offset;
				while ( $x < $this->img['width'] ) {
					$this->draw_watermark_on_image( $img_resource, $watermark_resource, $watermark, $x, $y, $type );
					$x += $x_axis_offset;
				}
				$x = $base_x;
			}
			if ( $y_axis_offset > 0 ) {
				$y    += $y_axis_offset;
				$y_top = intval( $y - ( intval( $watermark['height'] ) * cos( floatval( $watermark['styles']['degree'] ) ) ) );
				while ( $y < $this->img['height'] || $y_top < $this->img['height'] ) {
					$this->draw_watermark_on_image( $img_resource, $watermark_resource, $watermark, $x, $y, $type );
					$y    += $y_axis_offset;
					$y_top = intval( $y - ( intval( $watermark['height'] ) * cos( floatval( $watermark['styles']['degree'] ) ) ) );
				}
			}
		} elseif ( 'full' === $watermark['repeatAxis'] ) {
			if ( $x_axis_offset <= 0 || $y_axis_offset <= 0 ) {
				return;
			}
			$x    += $x_axis_offset;
			$y_top = intval( $y - ( intval( $watermark['height'] ) * cos( floatval( $watermark['styles']['degree'] ) ) ) );
			while ( $y < $this->img['height'] || $y_top < $this->img['height'] ) {
				while ( $x < $this->img['width'] ) {
					$this->draw_watermark_on_image( $img_resource, $watermark_resource, $watermark, $x, $y, $type );
					$x += $x_axis_offset;
				}
				$x     = $base_x;
				$y    += $y_axis_offset;
				$y_top = intval( $y - ( intval( $watermark['height'] ) * cos( floatval( $watermark['styles']['degree'] ) ) ) );
			}
		}
	}

	/**
	 * Convert color hex string to rgb hex.
	 *
	 * @param string $hex Hex String.
	 * @return array|false
	 */
	protected function hex_color_2_allocate( $hex ) {
		if ( mb_eregi( '[#]?([0-9a-f]{2})([0-9a-f]{2})([0-9a-f]{2})', $hex, $ret ) ) {
			$red   = hexdec( $ret[1] );
			$green = hexdec( $ret[2] );
			$blue  = hexdec( $ret[3] );

			return array(
				'red'   => $red,
				'green' => $green,
				'blue'  => $blue,
			);
		}

		return false;
	}

	/**
	 * Scale Text Watermarks based on the sub-size.
	 *
	 * @param array $watermark
	 * @return array
	 */
	protected function scale_text_watermarks( $watermark ) {
		if ( ! empty( $this->img['width_ratio'] ) || ! empty( $this->img['height_ratio'] ) ) {
			$new_fontsize = intval( $this->img['width_ratio'] * $watermark['styles']['font']['fontSize'] );
			$box          = imagettfbbox( $new_fontsize, 0, self::get_font_path( $watermark['styles']['font']['fontFamily'] ), $watermark['text'] );

			if ( is_array( $box ) ) {
				$x_coords                                = array( $box[0], $box[2], $box[4], $box[6] );
				$y_coords                                = array( $box[1], $box[3], $box[5], $box[7] );
				$box_width                               = max( $x_coords ) - min( $x_coords );
				$box_height                              = max( $y_coords ) - min( $y_coords );
				$base_x                                  = abs( min( $x_coords ) );
				$base_y                                  = abs( max( $y_coords ) );
				$watermark['styles']['font']['fontSize'] = $new_fontsize;
				$watermark['width']                      = $box_width;
				$watermark['height']                     = $box_height;
				$watermark['baselineOffset']             = $base_y;
				$watermark['exactWidth']                 = $box_width - ( 2 * $base_x );
			}
		}
		return $watermark;
	}

	/**
	 * Clear Watermarks Resources.
	 *
	 * @return void
	 */
	public function clear_watermarks() {
		foreach ( $this->watermarks as &$watermark ) {
			if ( 'image' === $watermark['type'] && ! empty( $watermark['resource'] ) ) {
				imagedestroy( $watermark['resource'] );
				unset( $watermark['resource'] );
			}
		}
	}

	/**
	 * Setup Image Watermark Position based on rotation.
	 *
	 * @param array $watermark Watermark Details.
	 * @return array Watermark details array.
	 */
	protected function image_watermark_position_from_rotation( $watermark, $degree ) {
		$degree_in_radians = $degree * M_PI / 180;
		if ( 90.0 === $degree ) {
			$watermark['absLeft'] -= $watermark['height'];
		} elseif ( 180.0 === $degree ) {
			$watermark['absLeft'] -= $watermark['width'];
			$watermark['absTop']  -= $watermark['height'];
		} elseif ( 270.0 === $degree ) {
			$watermark['absTop'] -= $watermark['width'];
		} elseif ( $degree > 0.0 && $degree < 90.0 ) {
			$watermark['absLeft'] -= round( $watermark['height'] * sin( $degree_in_radians ) );
		} elseif ( $degree > 180.0 && $degree < 270.0 ) {
			$degree               -= 180;
			$degree_in_radians     = $degree * M_PI / 180;
			$watermark['absLeft'] -= round( $watermark['width'] * cos( $degree_in_radians ) );
			$watermark['absTop']  -= round( ( $watermark['width'] * sin( $degree_in_radians ) ) + ( $watermark['height'] * cos( $degree_in_radians ) ) );
		} elseif ( $degree > 90.0 && $degree < 180.0 ) {
			$watermark['absTop']  += round( $watermark['height'] * cos( $degree_in_radians ) );
			$watermark['absLeft'] += round( - ( $watermark['height'] * sin( $degree_in_radians ) ) + ( $watermark['width'] * cos( $degree_in_radians ) ) );
		} elseif ( $degree > 270.0 && $degree < 360.0 ) {
			$watermark['absTop'] += round( $watermark['width'] * sin( $degree_in_radians ) );
		}
		if ( ! empty( $watermark['centerOffset'] ) && ( 'true' === $watermark['centerOffset'] || 'yes' === $watermark['centerOffset'] ) ) {
			$watermark['absLeft'] += round( $watermark['width'] / 2 );
			$watermark['absTop']  += round( $watermark['height'] / 2 );
		}
		return $watermark;
	}

	/**
	 * Get the Text position based on the text rotation.
	 *
	 * @param array $watermark
	 * @param float $degree
	 * @return array
	 */
	protected function text_watermark_position_from_rotation( $watermark, $degree ) {
		$y_spacing         = round( $watermark['baselineOffset'] );
		$x_spacing         = round( ( $watermark['width'] - $watermark['exactWidth'] ) / 2 );
		$degree_in_radians = $degree * M_PI / 180;
		if ( 0 === $degree ) {
			$h_spacing = abs( $x_spacing );
			$v_spacing = abs( $y_spacing );
		} elseif ( 90 === $degree ) {
			$h_spacing = abs( $y_spacing );
			$v_spacing = abs( $x_spacing );
		} elseif ( 270 === $degree ) {
			$h_spacing = - abs( $y_spacing );
			$v_spacing = abs( $x_spacing );
		} elseif ( $degree > 0.0 && $degree < 90.0 ) {
			$h_spacing = round( abs( ( $y_spacing - ( $x_spacing / tan( $degree_in_radians ) ) ) * sin( $degree_in_radians ) ) );
			$diag2     = pow( $x_spacing, 2 ) + pow( $y_spacing, 2 );
			$v_spacing = round( sqrt( absint( $diag2 - pow( $h_spacing, 2 ) ) ) );
		} elseif ( $degree > 270 ) {
			$abs_degree        = 360 - $degree;
			$degree_in_radians = $abs_degree * M_PI / 180;
			$diag2             = pow( $x_spacing, 2 ) + pow( $y_spacing, 2 );
			$h_spacing         = - round( abs( absint( $y_spacing - ( $x_spacing / tan( $degree_in_radians ) ) ) * sin( $degree_in_radians ) ) );
			$v_spacing         = sqrt( absint( $diag2 - pow( $h_spacing, 2 ) ) );
		} else {
			$h_spacing = $x_spacing;
			$v_spacing = $y_spacing;
		}

		return array( $h_spacing, $v_spacing );
	}
}

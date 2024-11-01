<?php

namespace GPLSCore\GPLS_PLUGIN_WMFW;

use GPLSCore\GPLS_PLUGIN_WMFW\Watermarks_Templates;

class Single_Apply_Watermarks {

	/**
	 * Plugin Info Array.
	 *
	 * @var array
	 */
	private static $plugin_info;

	/**
	 * Core Object.
	 *
	 * @var object
	 */
	private static $core;

	/**
	 * Init Function.
	 *
	 * @return void
	 */
	public static function init( $plugin_info, $core ) {
		self::$plugin_info = $plugin_info;
		self::$core        = $core;
		self::hooks();
	}

	/**
	 * Actions and Filters Hooks.
	 *
	 * @return void
	 */
	public static function hooks() {
		add_action( 'admin_enqueue_scripts', array( get_called_class(), 'page_assets' ) );
		add_action( 'wp_ajax_' . self::$plugin_info['name'] . '-single-apply-watermarks-template', array( get_called_class(), 'ajax_single_apply_watermarks' ) );
	}

	/**
	 * Page Assets.
	 *
	 * @return void
	 */
	public static function page_assets() {

		// Single Apply Page.
		if ( ! empty( $_GET['page'] ) && self::$plugin_info['single_apply_page'] === sanitize_text_field( wp_unslash( $_GET['page'] ) ) ) {
			wp_enqueue_style( self::$plugin_info['name'] . '-settings-menu-bootstrap-style', self::$core->core_assets_lib( 'bootstrap', 'css' ), array(), self::$plugin_info['version'], 'all' );
			wp_enqueue_style( self::$plugin_info['name'] . '-watermark-template-css', self::$plugin_info['url'] . 'assets/dist/css/admin/admin-styles.min.css', array(), self::$plugin_info['version'], 'all' );
			wp_add_inline_style(
				self::$plugin_info['name'] . '-watermark-template-css',
				Image_Watermark::list_fonts_css( self::$plugin_info )
			);
			wp_enqueue_style( self::$plugin_info['name'] . '-select2-css', self::$core->core_assets_lib( 'select2', 'css' ), array(), self::$plugin_info['version'], 'all' );

			if ( ! wp_script_is( 'jquery' ) ) {
				wp_enqueue_script( 'jquery' );
			}

			wp_enqueue_media();

			if ( ! wp_script_is( 'jquery-ui-core' ) ) {
				wp_enqueue_script( 'jquery-ui-core' );
			}
			if ( ! wp_script_is( 'jquery-touch-punch' ) ) {
				wp_enqueue_script( 'jquery-touch-punch' );
			}
			if ( ! wp_script_is( 'jquery-ui-draggable' ) ) {
				wp_enqueue_script( 'jquery-ui-draggable' );
			}
			if ( ! wp_script_is( 'jquery-ui-droppable' ) ) {
				wp_enqueue_script( 'jquery-ui-droppable' );
			}
			if ( ! wp_script_is( 'jquery-ui-accordion' ) ) {
				wp_enqueue_script( 'jquery-ui-accordion' );
			}
			if ( ! wp_script_is( 'jquery-ui-sortable' ) ) {
				wp_enqueue_script( 'jquery-ui-sortable' );
			}
			wp_enqueue_script( self::$plugin_info['name'] . '-bootstrap-js', self::$core->core_assets_lib( 'bootstrap.bundle', 'js' ), array(), self::$plugin_info['version'], true );
			wp_enqueue_script( self::$plugin_info['name'] . '-jquery-ui-rotatable-js', self::$core->core_assets_lib( 'jquery.ui.rotatable', 'js' ), array( 'jquery-ui-core', 'jquery-ui-draggable' ), self::$plugin_info['version'], true );
			wp_enqueue_script( self::$plugin_info['name'] . '-select2-js', self::$core->core_assets_lib( 'select2.full', 'js' ), array( 'jquery' ), self::$plugin_info['version'], true );
			wp_enqueue_script( self::$plugin_info['name'] . '-watermark-template-js', self::$plugin_info['url'] . 'assets/dist/js/admin/single-apply-watermarks.min.js', array( 'jquery', self::$plugin_info['name'] . '-select2-js' ), self::$plugin_info['version'], true );
			wp_localize_script(
				self::$plugin_info['name'] . '-watermark-template-js',
				str_replace( '-', '_', self::$plugin_info['name'] . '_localize_vars' ),
				array(
					'ajaxUrl'                         => admin_url( 'admin-ajax.php' ),
					'spinner'                         => admin_url( 'images/spinner.gif' ),
					'nonce'                           => wp_create_nonce( self::$plugin_info['name'] . '-ajax-nonce' ),
					'previewWatermarkstemplateAction' => self::$plugin_info['name'] . '-preview-watermarks-template-action',
					'saveWatermarkstemplateAction'    => self::$plugin_info['name'] . '-save-watermarks-template-action',
					'singleApplyWatermarksAction'     => self::$plugin_info['name'] . '-single-apply-watermarks-template',
					'watermarks_limit'                => Watermarks_Templates::$watermarks_limit,
					'labels'                          => array(
						'watermark'                 => esc_html__( 'Watermark', 'watermark-images-for-wp-and-woo-grandpluginswp' ),
						'select_images'             => esc_html__( 'Select images', 'watermark-images-for-wp-and-woo-grandpluginswp' ),
						'select_image'              => esc_html__( 'Select Image', 'watermark-images-for-wp-and-woo-grandpluginswp' ),
						'select_watermark'          => esc_html__( 'Select Watermark', 'watermark-images-for-wp-and-woo-grandpluginswp' ),
						'choose_watermark'          => esc_html__( 'Choose Watermark', 'watermark-images-for-wp-and-woo-grandpluginswp' ),
						'big_watermark_notice'      => esc_html__( 'The selected watermark is bigger than the image', 'watermark-images-for-wp-and-woo-grandpluginswp' ),
						'search_term'               => esc_html__( 'Search Term', 'watermark-images-for-wp-and-woo-grandpluginswp' ),
						'search_terms'              => esc_html__( 'Search Terms', 'watermark-images-for-wp-and-woo-grandpluginswp' ),
						'limited_watermarks_notice' => sprintf( esc_html__( 'Maximum %d watermarks can be added in Free Version', 'watermark-images-for-wp-and-woo-grandpluginswp' ), Watermarks_Templates::$watermarks_limit ),
						'remove_watermark'          => esc_html__( 'You are about to remove a watermark, confirm?', 'watermark-images-for-wp-and-woo-grandpluginswp' ),
					),
					'classes_prefix'                  => self::$plugin_info['classes_prefix'],
				)
			);
		}
	}

		/**
		 * Ajax Apply Watermarks.
		 *
		 * @return void
		 */
	public static function ajax_single_apply_watermarks() {
		if ( ! empty( $_POST['nonce'] ) && wp_verify_nonce( wp_unslash( $_POST['nonce'] ), self::$plugin_info['name'] . '-ajax-nonce' ) ) {
			$errors        = array();
			$preview_image = ! empty( $_POST['preview_img'] ) ? map_deep( wp_unslash( $_POST['preview_img'] ), 'sanitize_text_field' ) : array();
			$watermarks    = map_deep( wp_unslash( $_POST['watermarks'] ), 'sanitize_text_field' );
			$options       = ! empty( $_POST['options'] ) ? map_deep( wp_unslash( $_POST['options'] ), 'sanitize_text_field' ) : array();

			if ( empty( $options ) ) {
				wp_send_json_error(
					array(
						'status' => 'danger',
						'msg'    => esc_html__( 'Apply rules are empty!', 'watermark-images-for-wp-and-woo-grandpluginswp' ),
					)
				);
			}
			if ( ! $preview_image ) {
				wp_send_json_error(
					array(
						'status' => 'danger',
						'msg'    => esc_html__( 'No selected image!', 'watermark-images-for-wp-and-woo-grandpluginswp' ),
					)
				);
			}
			$options = array_merge(
				array(
					'applyTemplateType' => 1,
					'createBackup'      => false,
					'imageSizes'        => array(),
				),
				$options
			);
			if ( empty( $options['imageSizes'] ) ) {
				wp_send_json_error(
					array(
						'status' => 'danger',
						'msg'    => esc_html__( 'No sizes selected!', 'watermark-images-for-wp-and-woo-grandpluginswp' ),
					)
				);
			}
			// Fix any boolean values.
			foreach ( $options as $option_key => $option_value ) {
				if ( 'false' === $option_value ) {
					$options[ $option_key ] = false;
				} elseif ( 'true' === $option_value ) {
					$options[ $option_key ] = true;
				}
			}
			$preview_image = Watermarks_Templates::handle_preview_img( $preview_image );
			$result        = self::single_apply_watermarks( $preview_image, $watermarks, $options );
			if ( is_array( $result ) ) {
				$errors[] = $result;
			}

			if ( ! empty( $errors ) ) {
				wp_send_json_error(
					array(
						'status' => 'danger',
						'errors' => $errors,
					)
				);
			} else {
				// Get Image Box.
				wp_send_json_success(
					array(
						'status'  => 'primary',
						'msg'     => esc_html__( 'Watermarks have been applied successfully!', 'watermark-images-for-wp-and-woo-grandpluginswp' ),
						'display' => self::display_img_icon_box( $result ),
					)
				);
			}
		}
		wp_send_json_error(
			array(
				'status' => 'danger',
				'msg'    => esc_html__( 'The link has expired, please refresh the page!', 'watermark-images-for-wp-and-woo-grandpluginswp' ),
			)
		);
	}


	/**
	 * Single Apply Watermarks on an image.
	 *
	 * @param array $img Target Image Details Array.
	 * @param array $watermarks Watermarks Array.
	 *
	 * @return array|int
	 */
	private static function single_apply_watermarks( $img, $watermarks, $apply_options ) {
		$errors         = array();
		$uploads        = wp_get_upload_dir();
		$attachment_id  = $img['id'];
		$image_metadata = wp_get_attachment_metadata( $img['id'] );

		// 2) Apply the Template on the Full-Size Image.
		$img_details = Image::get_image_file_details( $attachment_id, 'original' );
		if ( is_wp_error( $img_details ) ) {
			return $img_details->get_error_message();
		}

		// Create New.
		if ( 1 == $apply_options['applyTemplateType'] ) {
			// 1) Generate Unique Filename with the same details.
			$original_path           = $img_details['path'];
			$filename                = wp_unique_filename( $img_details['full_path_without_name'], $img_details['filename'] );
			$img_details['filename'] = $filename;
			$img_details['url']      = trailingslashit( $uploads['baseurl'] ) . trailingslashit( $img_details['relative_path'] ) . $filename;
			$img_details['path']     = $img_details['full_path_without_name'] . $filename;

			// 2) Create a copy of the image to the new Filename.
			$copied = copy( $original_path, $img_details['path'] );
			if ( ! $copied ) {
				$errors[] = esc_html__( 'Failed to create watermarked image file!', 'watermark-images-for-wp-and-woo-grandpluginswp' );
				return $errors;
			}

			// 3) Create an Image media post.
			$attachment_obj = get_post( $attachment_id );
			$attachment     = array(
				'post_mime_type' => $img_details['mime_type'],
				'guid'           => $img_details['url'],
				'post_parent'    => $attachment_obj->post_parent,
				'post_title'     => wp_basename( $filename, '.' . $img_details['ext'] ),
				'post_content'   => '',
				'post_excerpt'   => '',
			);
			$attachment_id  = wp_insert_attachment( $attachment, $img_details['path'] );
			if ( is_wp_error( $attachment_id ) ) {
				$errors[] = esc_html__( 'Failed to create watermarked attachment!', 'watermark-images-for-wp-and-woo-grandpluginswp' );
				return $errors;
			}
			$img_details = Image::get_image_file_details( $attachment_id, 'original' );
		} else {
			// Overwrite.
			// If original is among the sizes and its scaled, apply the template on scaled image.
			if ( in_array( 'original', $apply_options['imageSizes'] ) && ! empty( $img_details['scaled_path'] ) && @file_exists( $img_details['scaled_path'] ) ) {
				$scaled_img_details = Image::get_image_file_details_direct( $img_details['scaled_path'] );

				$scaled_img_details   = Image::get_image_file_details_direct( $img_details['scaled_path'] );
				$scaled_watermark_img = Image_Watermark::setup_image_for_watermarking( $scaled_img_details, $watermarks );
				if ( is_wp_error( $scaled_watermark_img ) ) {
					$errors[] = $scaled_watermark_img->get_error_message();
					return $errors;
				}
				$scaled_result        = $scaled_watermark_img->draw_watermarks_on_image();

				$scaled_result = Watermark_Base::save_watermarked_image( $scaled_result['img_string'], $scaled_img_details );
				if ( is_wp_error( $scaled_result ) ) {
					$errors[] = $scaled_result->get_error_message();
				}
			}
		}

		// 4) Execute Apply Template on the Full-size Image.
		$result = Apply_Watermarks_Templates::_apply_template( $attachment_id, $watermarks, 'original' );
		if ( is_wp_error( $result ) ) {
			$errors[] = $result->get_error_message();
		}

		// 5) Save the watermarked image [ New - Overwrite ].
		$result = Watermark_Base::save_watermarked_image( $result['img_string'], $img_details );
		if ( is_wp_error( $result ) ) {
			$errors[] = $result->get_error_message();
			return $errors;
		}

		// 6) Generate Sub-sizes of the image after watermarks.
			// Create New.
		if ( 1 == $apply_options['applyTemplateType'] ) {
			Apply_Watermarks_Templates::$selected_subsizes = $apply_options['imageSizes'];
			do_action( self::$plugin_info['name'] . '-apply-watermark-template-before-creating-subsizes' );
			wp_update_attachment_metadata( $attachment_id, wp_generate_attachment_metadata( $attachment_id, $img_details['path'] ) );
		} else {
			// Overwrite.
			$untouched_sizes         = array_diff_key( $image_metadata['sizes'], array_flip( $apply_options['imageSizes'] ) );
			$image_metadata['sizes'] = $untouched_sizes;
			$sizes_to_watermark      = array_intersect_key( wp_get_registered_image_subsizes(), array_flip( $apply_options['imageSizes'] ) );
			$image_metadata          = _wp_make_subsizes( $sizes_to_watermark, $img_details['path'], $image_metadata, $attachment_id );
			$image_metadata          = apply_filters( 'wp_generate_attachment_metadata', $image_metadata, $attachment_id, 'update' );
			wp_update_attachment_metadata( $attachment_id, $image_metadata );
		}

		if ( ! empty( $errors ) ) {
			return $errors;
		} else {
			return $attachment_id;
		}
	}

	/**
	 * Display Image Box after save.
	 *
	 * @return void
	 */
	public static function display_img_icon_box( $media_id ) {
		$media_post = get_post( $media_id );
		$title      = get_the_title( $media_post );
		$thumb      = wp_get_attachment_image( $media_id, array( 150, 150 ), true, array( 'alt' => '' ) );
		$edit_link  = get_edit_post_link( $media_id );
		$file       = get_attached_file( $media_id );
		$edit_link  = add_query_arg(
			self::$plugin_info['classes_prefix'] . '-force-img-refresh',
			'true',
			$edit_link
		);
		?>
		<div class="img-media-icon-box card mb-3 w-auto mx-auto border container px-0 py-0">
			<div class="card-body">
				<h5 class="card-title"><a target="_blank" href="<?php echo esc_url_raw( $edit_link ); ?>"><strong><?php echo esc_html( $title ); ?></strong></a></h5>
				<p class="card-text mt-4"><?php echo esc_html( wp_basename( $file ) ); ?></p>
				<a class="mt-3" target="_blank" href="<?php echo esc_url_raw( $edit_link ); ?>"><?php echo wp_kses_post( $thumb ); ?></a>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}

}

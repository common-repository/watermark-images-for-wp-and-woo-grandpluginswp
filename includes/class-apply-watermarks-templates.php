<?php
namespace GPLSCore\GPLS_PLUGIN_WMFW;

use GPLSCore\GPLS_PLUGIN_WMFW\Image_Watermark;
use GPLSCore\GPLS_PLUGIN_WMFW\Apply_Watermarks_Queries;

/**
 * Watermarks Templates CPT Class.
 */
class Apply_Watermarks_Templates {

	/**
	 * Core Object
	 *
	 * @var object
	 */
	private static $core;

	/**
	 * Plugin Info
	 *
	 * @var object
	 */
	protected static $plugin_info;

	/**
	 * Apply Offset Length on every step.
	 *
	 * @var integer
	 */
	protected static $apply_offset_length = 10;

	/**
	 * Selected image Sub-sizes.
	 *
	 * @var array
	 */
	public static $selected_subsizes = array();

	/**
	 * GIF Editor Initialization.
	 *
	 * @param array  $plugin_info Plugin Info Array.
	 * @param object $core Core Object.
	 * @return void
	 */
	public static function init( $plugin_info, $core ) {
		self::$plugin_info = $plugin_info;
		self::$core        = $core;

		self::setup();
		self::hooks();
	}

	/**
	 * Setup Variables.
	 *
	 * @return void
	 */
	public static function setup() {

	}

	/**
	 * Actions and filters hooks.
	 *
	 * @return void
	 */
	public static function hooks() {
		add_action( 'admin_enqueue_scripts', array( get_called_class(), 'include_assets' ), 1000, 1 );
		add_filter( 'intermediate_image_sizes_advanced', array( get_called_class(), 'filter_image_subsizes_creation_at_watermarks_template_applying' ), PHP_INT_MAX, 3 );
		add_action( 'wp_ajax_' . self::$plugin_info['name'] . '-apply-watermarks-template', array( get_called_class(), 'ajax_apply_watermarks_template' ) );
	}

	/**
	 * Filter the image sub-sizes creation after applying watermarks template.
	 *
	 * @param array $sub_sizes Sub-Sizes Array.
	 * @param array $image_meta Image Meta Details.
	 * @param int   $attachment_id Attachment Post ID.
	 */
	public static function filter_image_subsizes_creation_at_watermarks_template_applying( $sub_sizes, $image_meta, $attachment_id ) {
		// Check if its a Watermarks Template Apply Process.
		if ( did_action( self::$plugin_info['name'] . '-apply-watermark-template-before-creating-subsizes' ) ) {
			// Filter the sub-sizes with the selected ones.
			return array_intersect_key( $sub_sizes, array_flip( self::$selected_subsizes ) );
		}
		return $sub_sizes;
	}

	/**
	 * Include JS - Css ASSETS.
	 *
	 * @return void
	 */
	public static function include_assets( $hook ) {
		global $post;
		if ( ! empty( $_GET['page'] ) && self::$plugin_info['bulk_apply_page'] === sanitize_text_field( wp_unslash( $_GET['page'] ) ) ) {
			wp_enqueue_style( self::$plugin_info['name'] . '-select2-css', self::$core->core_assets_lib( 'select2', 'css' ), array(), self::$plugin_info['version'], 'all' );
			wp_enqueue_style( self::$plugin_info['name'] . '-settings-menu-bootstrap-style', self::$core->core_assets_lib( 'bootstrap', 'css' ), array(), self::$plugin_info['version'], 'all' );
			wp_enqueue_style( self::$plugin_info['name'] . '-watermark-template-css', self::$plugin_info['url'] . 'assets/dist/css/admin/admin-styles.min.css', array(), self::$plugin_info['version'], 'all' );

			if ( ! wp_script_is( 'jquery' ) ) {
				wp_enqueue_script( 'jquery' );
			}
			wp_enqueue_media();

			wp_enqueue_style( 'wp-jquery-ui-dialog' );
			wp_enqueue_script( 'jquery-ui-dialog' );

			wp_enqueue_script( self::$plugin_info['name'] . '-select2-js', self::$core->core_assets_lib( 'select2.full', 'js' ), array( 'jquery' ), self::$plugin_info['version'], true );
			wp_enqueue_script( self::$plugin_info['name'] . '-bootstrap-js', self::$core->core_assets_lib( 'bootstrap.bundle', 'js' ), array(), self::$plugin_info['version'], true );
			wp_enqueue_script( self::$plugin_info['name'] . '-watermark-template-js', self::$plugin_info['url'] . 'assets/dist/js/admin/apply-watermarks-templates.min.js', array( 'jquery' ), self::$plugin_info['version'], true );
			wp_localize_script(
				self::$plugin_info['name'] . '-watermark-template-js',
				str_replace( '-', '_', self::$plugin_info['name'] . '_localize_vars' ),
				array(
					'ajaxUrl'                       => admin_url( 'admin-ajax.php' ),
					'spinner'                       => admin_url( 'images/spinner.gif' ),
					'offsetLength'                  => self::$apply_offset_length,
					'nonce'                         => wp_create_nonce( self::$plugin_info['name'] . '-ajax-nonce' ),
					'selectCptListAction'           => self::$plugin_info['name'] . '-view-selected-cpt-posts-action',
					'findImagesInPostsAction'       => self::$plugin_info['name'] . '-view-selected-images-before-apply',
					'searchCPTTermsAction'          => self::$plugin_info['name'] . '-search-cpt-terms-action',
					'applyWatermarksTemplateAction' => self::$plugin_info['name'] . '-apply-watermarks-template',
					'classes_prefix'                => self::$plugin_info['classes_prefix'],
					'labels'                        => array(
						'select_images'        => esc_html__( 'Select images', 'watermark-images-for-wp-and-woo-grandpluginswp' ),
						'preview_image'        => esc_html__( 'Preview Image', 'watermark-images-for-wp-and-woo-grandpluginswp' ),
						'select_preview_image' => esc_html__( 'Select Preview Image', 'watermark-images-for-wp-and-woo-grandpluginswp' ),
						'select_watermark'     => esc_html__( 'Select Watermark', 'watermark-images-for-wp-and-woo-grandpluginswp' ),
						'choose_watermark'     => esc_html__( 'Choose Watermark', 'watermark-images-for-wp-and-woo-grandpluginswp' ),
						'search_term'          => esc_html__( 'Search Term', 'watermark-images-for-wp-and-woo-grandpluginswp' ),
						'big_watermark_notice' => esc_html__( 'The selected watermark is bigger than the GIF', 'watermark-images-for-wp-and-woo-grandpluginswp' ),
					),
				)
			);
		}
	}

	/**
	 * Ajax Apply Watermarks Template.
	 *
	 * @return void
	 */
	public static function ajax_apply_watermarks_template() {
		if ( ! empty( $_POST['nonce'] ) && wp_verify_nonce( wp_unslash( $_POST['nonce'] ), self::$plugin_info['name'] . '-ajax-nonce' ) ) {
			$errors      = array();
			$step        = isset( $_POST['step'] ) ? absint( sanitize_text_field( wp_unslash( $_POST['step'] ) ) ) : 'end';
			$total_steps = isset( $_POST['totalSteps'] ) ? absint( sanitize_text_field( wp_unslash( $_POST['totalSteps'] ) ) ) : 1;
			$options     = ! empty( $_POST['options'] ) ? map_deep( wp_unslash( $_POST['options'] ), 'sanitize_text_field' ) : array();
			$imgs_ids    = ! empty( $_POST['images'] ) ? array_map( 'absint', array_map( 'sanitize_text_field', wp_unslash( $_POST['images'] ) ) ) : array();

			if ( empty( $options ) ) {
				wp_send_json_error(
					array(
						'status'  => 'danger',
						'message' => esc_html__( 'Apply rules are empty!', 'watermark-images-for-wp-and-woo-grandpluginswp' ),
					)
				);
			}
			if ( empty( $options['templateID'] ) ) {
				wp_send_json_error(
					array(
						'status'  => 'danger',
						'message' => esc_html__( 'Watermarks Template is empty!', 'watermark-images-for-wp-and-woo-grandpluginswp' ),
					)
				);
			}
			$options = array_merge(
				array(
					'applyTemplateType' => 1,
					'createBackup'      => false,
					'imageSizes'        => array_keys( wp_get_registered_image_subsizes() ),
				),
				$options
			);

			// Fix any boolean values.
			foreach ( $options as $option_key => $option_value ) {
				if ( 'false' === $option_value ) {
					$options[ $option_key ] = false;
				} elseif ( 'true' === $option_value ) {
					$options[ $option_key ] = true;
				}
			}
			$imgs_ids = array_slice( $imgs_ids, $step * self::$apply_offset_length, self::$apply_offset_length );
			foreach ( $imgs_ids as $img_id ) {
				$result = self::apply_watermarks_template( $options['templateID'], $img_id, $options );
				if ( is_array( $result ) ) {
					$errors = array_merge( $errors, $result );
				}
			}
			$step += 1;
			if ( $step >= $total_steps ) {
				$message = '<p>' . esc_html__( 'Watermarks Template has been applied successfully on selected images', 'watermark-images-for-wp-and-woo-grandpluginswp' ) . '</p><a class ="button" target="_blank" href="' . esc_url_raw( admin_url( 'upload.php' ) ) . '" ><strong>' . esc_html__( 'Media Library', 'watermark-images-for-wp-and-woo-grandpluginswp' ) . '</strong></a>  ';
			} else {
				$message = '';
			}

			if ( ! empty( $errors ) ) {
				array_unshift( $errors, 'Errors log:' );
			}
			wp_send_json_success(
				array(
					'status'  => 'primary',
					'step'    => ( ( $step >= $total_steps ) ? 'end' : $step ),
					'message' => $message,
					'errors'  => $errors,
				)
			);
		}
		wp_send_json_error(
			array(
				'status'  => 'danger',
				'message' => esc_html__( 'The link has expired, please refresh the page!', 'watermark-images-for-wp-and-woo-grandpluginswp' ),
			)
		);
	}

	/**
	 * Apply Watermarks Template on attachment.
	 *
	 * @param int   $template_id Watermarks Template ID.
	 * @param int   $attachment_id Attachment ID.
	 * @param array $apply_options The Template apply options array.
	 * @return array
	 */
	private static function apply_watermarks_template( $template_id, $attachment_id, $apply_options ) {
		$errors  = array();
		$uploads = wp_get_upload_dir();
		// 1) Get the Watermarks from the Template.
		$watermarks     = Watermarks_Templates::get_template_watermarks( $template_id, 'watermarks' );
		$watermarks     = Watermarks_Templates::adjust_template_watermarks_data( $watermarks );
		$image_metadata = wp_get_attachment_metadata( $attachment_id );

		// 2) Apply the Template on the Full-Size Image.
		$img_details = Image::get_image_file_details( $attachment_id, 'original' );
		if ( is_wp_error( $img_details ) ) {
			$errors[] = $img_details->get_error_message();
			return $errors;
		}

		// Check if the image is supported.
		$watermark_img_check = Image_Watermark::setup_image_for_watermarking( $img_details, $watermarks );
		if ( is_wp_error( $watermark_img_check ) ) {
			$errors[] = $watermark_img_check->get_error_message();
			return $errors;
		}

		// 3) Generate Unique Filename with the same details.
		$original_path           = $img_details['path'];
		$filename                = wp_unique_filename( $img_details['full_path_without_name'], $img_details['filename'] );
		$img_details['filename'] = $filename;
		$img_details['url']      = trailingslashit( $uploads['baseurl'] ) . trailingslashit( $img_details['relative_path'] ) . $filename;
		$img_details['path']     = trailingslashit( $img_details['full_path_without_name'] ) . $filename;

		// 2) Create a copy of the image to the new Filename.
		$copied = copy( $original_path, $img_details['path'] );
		if ( ! $copied ) {
			$errors[] = esc_html__( 'Failed to create watermarked image file!', 'watermark-images-for-wp-and-woo-grandpluginswp' );
			return $errors;
		}

		// 4) Create an Image media post.
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

		// 5) Execute Apply Template on the Full-size Image.
		$result = self::_apply_template( $attachment_id, $watermarks, 'original' );
		if ( is_wp_error( $result ) ) {
			$errors[] = $result->get_error_message();
			return $errors;
		}

		// 6) Save the watermarked image [ New - Overwrite ].
		$result = Watermark_Base::save_watermarked_image( $result['img_string'], $img_details );
		if ( is_wp_error( $result ) ) {
			$errors[] = $result->get_error_message();
			return $errors;
		}

		// 7) Generate Sub-sizes of the image after watermarks.
			// Create New.
		self::$selected_subsizes = $apply_options['imageSizes'];
		do_action( self::$plugin_info['name'] . '-apply-watermark-template-before-creating-subsizes' );
		wp_update_attachment_metadata( $attachment_id, wp_generate_attachment_metadata( $attachment_id, $img_details['path'] ) );

		return $errors;
	}

	/**
	 * Start The Apply Template Process.
	 *
	 * @param int    $attachment_id Attachment ID.
	 * @param array  $watermarks Watermarks Details Array.
	 * @param string $img_size_name Image Size Name [ original - thumanil - ... ].
	 * @return string|\WP_Error
	 */
	public static function _apply_template( $attachment_id, $watermarks, $img_size_name ) {
		// 1) Prepare the Image before applying watermarks.
		$img_details = Image::get_image_file_details( $attachment_id, $img_size_name );
		if ( is_wp_error( $img_details ) ) {
			return $img_details->get_error_message();
		}

		// 2) Draw the Watermarks on the image.
		$watermark_img = Image_Watermark::setup_image_for_watermarking( $img_details, $watermarks );
		if ( is_wp_error( $watermark_img ) ) {
			return $watermark_img;
		}
		$result = $watermark_img->draw_watermarks_on_image();

		return $result;
	}

	/**
	 * Checkbox Column in Table.
	 *
	 * @param int     $post_id
	 * @param boolean $is_checked
	 * @return void
	 */
	public static function column_cb( $post_id, $is_checked ) {
		?>
		<input type="checkbox" <?php echo esc_attr( $is_checked ? 'checked' : '' ); ?> class="cb-select-all cb-select-all-selected-images" name="cb-select-all-selected-images[]" id="cb-select-all-<?php echo esc_attr( $post_id ); ?>" data-id="<?php echo esc_attr( $post_id ); ?>">
		<?php
	}

	/**
	 * Selected Images Row Title.
	 *
	 * @param object $post
	 * @return void
	 */
	public static function column_title( $post ) {
		if ( ! $post || is_wp_error( $post ) ) {
			return;
		}
		list( $mime ) = explode( '/', $post->post_mime_type );

		$title      = esc_html( _draft_or_post_title( $post ) );
		$thumb      = wp_get_attachment_image( $post->ID, array( 60, 60 ), true, array( 'alt' => '' ) );
		$link_start = '';
		$link_end   = '';

		if ( current_user_can( 'edit_post', $post->ID ) ) {
			$link_start = sprintf(
				'<a target="_blank" href="%s" aria-label="%s">',
				esc_url_raw( get_edit_post_link( $post->ID ) ),
				/* translators: %s: Attachment title. */
				esc_attr( sprintf( esc_html__( '&#8220;%s&#8221; (Edit)' ), $title ) )
			);
			$link_end = '</a>';
		}

		$class = $thumb ? ' class=has-media-icon' : '';
		?>
		<strong<?php echo esc_attr( $class ); ?>>
			<?php
			echo wp_kses_post( $link_start );

			if ( $thumb ) :
				?>
				<span class="media-icon <?php echo sanitize_html_class( $mime . '-icon' ); ?>"><?php echo wp_kses_post( $thumb ); ?></span>
				<?php
			endif;

			echo wp_kses_post( $title . $link_end );

			_media_states( $post );
			?>
			<p class="filename">
			<span class="screen-reader-text"><?php esc_html_e( 'File name:' ); ?> </span>
			<?php
			$file = get_attached_file( $post->ID );
			echo esc_html( wp_basename( $file ) );
			?>
			</p>
		</strong>

		<?php
	}

	/**
	 * Handles the parent column output.
	 *
	 * @param \WP_Post $post The current WP_Post object.
	 */
	public static function column_parent( $post ) {
		if ( $post->post_parent > 0 ) {
			$parent = get_post( $post->post_parent );
		} else {
			$parent = false;
		}
		if ( $parent ) {
			$title       = _draft_or_post_title( $post->post_parent );
			$parent_type = get_post_type_object( $parent->post_type );
			if ( $parent_type && $parent_type->show_ui && current_user_can( 'edit_post', $post->post_parent ) ) {
				printf( '<strong><a target="_blank" href="%s">%s</a></strong>', esc_url_raw( get_edit_post_link( $post->post_parent ) ), esc_html( $title ) );
			} elseif ( $parent_type && current_user_can( 'read_post', $post->post_parent ) ) {
				printf( '<strong>%s</strong>', $title );
			} else {
				esc_html_e( '(Private post)' );
			}
		} else {
			esc_html_e( '(Unattached)' );
		}
	}


	/**
	 * Handles the author column output.
	 *
	 * @since 4.3.0
	 *
	 * @param \WP_Post $post The current WP_Post object.
	 */
	public static function column_author( $post ) {
		printf(
			'<a target="_blank" href="%s">%s</a>',
			esc_url_raw( add_query_arg( array( 'author' => get_the_author_meta( 'ID', $post->post_author ) ), 'upload.php' ) ),
			get_the_author_meta( 'nicename', $post->post_author )
		);
	}

	/**
	 * Handles the date column output.
	 *
	 * @since 4.3.0
	 *
	 * @param \WP_Post $post The current WP_Post object.
	 */
	public static function column_date( $post ) {
		if ( is_null( $post ) ) {
			return;
		}
		if ( '0000-00-00 00:00:00' === $post->post_date ) {
			$h_time = esc_html__( 'Unpublished' );
		} else {
			$time      = get_post_timestamp( $post );
			$time_diff = time() - $time;

			if ( $time && $time_diff > 0 && $time_diff < DAY_IN_SECONDS ) {
				/* translators: %s: Human-readable time difference. */
				$h_time = sprintf( esc_html__( '%s ago' ), human_time_diff( $time ) );
			} else {
				$h_time = get_the_time( esc_html__( 'Y/m/d' ), $post );
			}
		}

		echo esc_html( $h_time );
	}
}

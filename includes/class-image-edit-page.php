<?php
namespace GPLSCore\GPLS_PLUGIN_WMFW;

use GPLSCore\GPLS_PLUGIN_WMFW\Image;

/**
 * Image Class.
 */
class Image_Edit_Page {

	/**
	 * Plugin Info Array.
	 *
	 * @var array
	 */
	protected static $plugin_info;

	/**
	 * Core Object.
	 *
	 * @var object
	 */
	protected static $core;

	/**
	 * Initialize Function.
	 *
	 * @return void
	 */
	public static function init( $plugin_info, $core ) {
		self::$plugin_info = $plugin_info;
		self::$core        = $core;
		self::hooks();
	}

	/**
	 * Actions - Filters Hooks.
	 *
	 * @return void
	 */
	public static function hooks() {
		add_action( 'add_meta_boxes', array( get_called_class(), 'register_image_metabox' ), 100 );
		add_action( 'admin_enqueue_scripts', array( get_called_class(), 'image_edit_page_scripts' ) );
		add_action( 'do_meta_boxes', array( get_called_class(), 'filter_image_metaboxes' ), 1000 );
	}

	/**
	 * Filter Metaboxes for image attachments only.
	 *
	 * @return void
	 */
	public static function filter_image_metaboxes() {
		global $post;
		if ( $post && ! is_wp_error( $post ) && ! wp_attachment_is_image( $post->ID ) ) {
			remove_meta_box( self::$plugin_info['name'] . '-image-edit-page-watermark-options-metabox', 'attachment', 'side' );
			remove_meta_box( self::$plugin_info['name'] . '-image-subsizes-list-metabox', 'attachment', 'normal' );
		}
	}

	/**
	 * Register Watermarks Options Metabox.
	 *
	 * @return void
	 */
	public static function register_image_metabox() {
		add_meta_box(
			self::$plugin_info['name'] . '-image-subsizes-list-metabox',
			esc_html__( 'Subsizes', 'watermark-images-for-wp-and-woo-grandpluginswp' ),
			array( get_called_class(), 'image_subsizes_list_metabox' ),
			'attachment',
			'normal',
			'low'
		);
	}

	/**
	 * List Image Subsizes List metabox.
	 *
	 * @param \WP_Post $attachment_post Attachment Post.
	 * @return void
	 */
	public static function image_subsizes_list_metabox( $attachment_post ) {
		if ( ! wp_attachment_is_image( $attachment_post->ID ) ) {
			return;
		}
		$img_metadata = wp_get_attachment_metadata( $attachment_post->ID );
		$img_url      = wp_get_attachment_url( $attachment_post->ID );
		if ( ! empty( $img_metadata['sizes'] ) ) :
			$sizes = $img_metadata['sizes'];
			?>
			<div class="card-group w-100">
				<div class="row row-cols-1 row-cols-md-3 g-4 w-100">
			<?php
			foreach ( $sizes as $size_name => $size_arr ) :
				$size_url = str_replace( wp_basename( $img_url ), $size_arr['file'], $img_url );
				$size_url = add_query_arg(
					array(
						'refresh'     => wp_generate_password( 5, false, false ),
						'dontreplace' => '',
					),
					$size_url
				);
				?>
				<div class="col mb-4">
					<div class="card h-100 shadow-sm border">
						<div class="card-body d-block">
							<p class="card-title text-center border p-4 bg-secondary text-white"><?php echo esc_html( $size_name ); ?> <span>&#91; <?php echo esc_html( $size_arr['width'] . 'x' . $size_arr['height'] ); ?> &#93;</span></p>
						</div>
						<div class="thumbnail-wrapper flex-grow-1 d-flex justify-contnet-center align-items-center w-100 flex-column">
							<a class="thumbnail border" href="<?php echo esc_url_raw( $size_url ); ?>" target="_blank">
								<img src="<?php echo esc_url_raw( $size_url ); ?>" alt="image-subsize" class="card-img-bottom">
							</a>
						</div>
					</div>
				</div>
				<?php
			endforeach;
			?>
				</div>
			</div>
		<?php
		endif;
	}

	/**
	 * Restore Original Image from backup.
	 *
	 * @param string $backup_path Backup PATH.
	 * @param string $img_path Original Image PATH.
	 * @return boolean
	 */
	public static function restore_original( $backup_path, $img_path ) {
		return @copy( $backup_path, $img_path );
	}

	/**
	 * Restore Scaled.
	 *
	 * @param string $original_path Original Image FULL PATH.
	 * @param int    $attachment_id Attachment Post ID.
	 * @return boolean|\WP_Error
	 */
	public static function restore_scaled( $original_path, $attachment_id ) {
		$imagesize  = wp_getimagesize( $original_path );
		$img_width  = $imagesize[0];
		$img_height = $imagesize[1];
		$exif_meta  = wp_read_image_metadata( $original_path );
		$threshold  = (int) apply_filters( 'big_image_size_threshold', 2560, $imagesize, $original_path, $attachment_id );
		if ( $threshold && ( $img_width > $threshold || $img_height > $threshold ) ) {
			$editor = wp_get_image_editor( $original_path );
			if ( is_wp_error( $editor ) ) {
				return $editor;
			}
			$resized = $editor->resize( $threshold, $threshold );
			if ( ! is_wp_error( $resized ) && is_array( $exif_meta ) ) {
				$editor->maybe_exif_rotate();
			}
			if ( ! is_wp_error( $resized ) ) {
				$saved = $editor->save( $editor->generate_filename( 'scaled' ) );
				if ( is_wp_error( $saved ) ) {
					return $editor;
				}
			}
		}
		return true;
	}

	/**
	 * Image Edit Page Assets.
	 *
	 * @return void
	 */
	public static function image_edit_page_scripts() {
		$current_screen = get_current_screen();
		if ( is_object( $current_screen ) && ( 'post' === $current_screen->base ) && ( 'attachment' === $current_screen->post_type ) ) {
			wp_enqueue_style( self::$plugin_info['name'] . '-settings-menu-bootstrap-style', self::$core->core_assets_lib( 'bootstrap', 'css' ), array(), self::$plugin_info['version'], 'all' );
			wp_enqueue_style( self::$plugin_info['name'] . '-css-admin', self::$plugin_info['url'] . 'assets/dist/css/admin/admin-styles.min.css', array(), self::$plugin_info['version'], 'all' );
			if ( ! wp_script_is( 'jquery' ) ) {
				wp_enqueue_script( 'jquery' );
			}
			wp_enqueue_media();

			wp_enqueue_style( 'wp-jquery-ui-dialog' );
			wp_enqueue_script( 'jquery-ui-dialog' );
			wp_enqueue_script( self::$plugin_info['name'] . '-bootstrap-js', self::$core->core_assets_lib( 'bootstrap.bundle', 'js' ), array(), self::$plugin_info['version'], true );
			wp_enqueue_script( self::$plugin_info['name'] . '-js-actions', self::$plugin_info['url'] . 'assets/dist/js/admin/image-edit-page.min.js', array( 'jquery' ), self::$plugin_info['version'], true );
			wp_localize_script(
				self::$plugin_info['name'] . '-js-actions',
				str_replace( '-', '_', self::$plugin_info['name'] . '_localize_vars' ),
				array(
					'ajaxUrl'                          => admin_url( 'admin-ajax.php' ),
					'spinner'                          => admin_url( 'images/spinner.gif' ),
					'nonce'                            => wp_create_nonce( self::$plugin_info['name'] . '-ajax-nonce' ),
					'classes_prefix'                   => self::$plugin_info['classes_prefix'],
					'restoreOriginalImageBackupAction' => self::$plugin_info['name'] . '-restore-original-image-backup',
					'deleteOriginalImageBackupAction'  => self::$plugin_info['name'] . '-delete-original-image-backup',
					'labels'                           => array(
						'restore_backup_prompt' => esc_html__( 'Restore selected size from the original image, proceed?', 'watermark-images-for-wp-and-woo-grandpluginswp' ),
						'delete_backup_prompt'  => esc_html__( 'Backup image will be deleted, proceed?', 'watermark-images-for-wp-and-woo-grandpluginswp' ),
					),
				)
			);
		}
	}
}

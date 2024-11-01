<?php
namespace GPLSCore\GPLS_PLUGIN_WMFW;

defined( 'ABSPATH' ) || exit;

use GPLSCore\GPLS_PLUGIN_WMFW\Image_Watermark;
use GPLSCore\GPLS_PLUGIN_WMFW\Apply_Watermarks_Queries;
use GPLSCore\GPLS_PLUGIN_WMFW\Utils\WatermarkUtilsTrait;

/**
 * Watermarks Templates CPT Class.
 */
class Watermarks_Templates {

	use WatermarkUtilsTrait;

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
	public static $plugin_info;

	/**
	 * Post Type Key
	 *
	 * @var string
	 */
	public static $post_type_key;

	/**
	 * Template Meta Data Key.
	 *
	 * @var string
	 */
	private static $watermarks_template_meta_key;

	/**
	 * Available watermarks Type.
	 *
	 * @var array
	 */
	private static $watermarks_types = array( 'text', 'image' );

	/**
	 * Text Watermark Fields
	 *
	 * @var array
	 */
	private static $text_watermark_fields = array( 'id', 'type', 'width', 'height', 'title', 'isRepeat', 'centerOffset', 'repeatAxis', 'repeatXAxisOffset', 'repeatYAxisOffset', 'positionSpot', 'positionType', 'absLeft', 'absTop', 'leftPercent', 'topPercent', 'baselineOffset', 'exactWidth', 'botLeft', 'botTop', 'opacity', 'degree', 'color', 'fontsize', 'fontfamily' );

	/**
	 * Image Watermark Fields.
	 *
	 * @var array
	 */
	private static $image_watermark_fields = array( 'id', 'type', 'width', 'height', 'imgID', 'isRepeat', 'centerOffset', 'repeatAxis', 'repeatXAxisOffset', 'repeatYAxisOffset', 'positionSpot', 'positionType', 'absLeft', 'absTop', 'leftPercent', 'topPercent', 'url', 'opacity', 'degree' );

	/**
	 * Distinct Date Options For All CPTs.  [ cpt_slug ] => array( array( author_obj => , post_type => ) )
	 *
	 * @var array
	 */
	protected static $cpts_author_options = array();

	/**
	 * Default Preview Images.
	 *
	 * @return array
	 */
	public static $default_preview_imgs;

	/**
	 * Available Auto Apply At Upload Contexts.
	 *
	 * @var array
	 */
	private static $auto_apply_contexts = array( 'media', 'posts' );

	/**
	 * Watermarks Limit Free Version.
	 */
	public static $watermarks_limit = 3;

	/**
	 * Watermarks Templates Count Limit Free Version.
	 */
	public static $templates_count_limit = 3;

	/**
	 * Default Watermarks Template MetaData Structure.
	 *
	 * @var array
	 */
	private static $default_metadata = array(
		'preview_img_id' => 0,
		'watermarks'     => array(),
		'auto_apply'     => array(
			'status'        => false,
			'context_type'  => array(),
			'context_posts' => array(),
			'apply_type'    => 'new',
			'create_backup' => false,
		),
	);

	/**
	 * GIF Editor Initialization.
	 *
	 * @param array  $plugin_info Plugin Info Array.
	 * @param object $core Core Object.
	 * @return void
	 */
	public static function init( $plugin_info, $core ) {
		self::$plugin_info                  = $plugin_info;
		self::$core                         = $core;
		self::$post_type_key                = self::$plugin_info['classes_prefix'] . '-watermark';
		self::$watermarks_template_meta_key = self::$plugin_info['classes_prefix'] . '-watermarks-template-meta-key';
		self::setup();
		self::hooks();
	}

	/**
	 * Setup Variables.
	 *
	 * @return void
	 */
	public static function setup() {
		self::$default_preview_imgs = array(
			'preview_bg_white' => array(
				'url'    => self::$plugin_info['url'] . 'assets/dist/images/preview-bg-white.jpg',
				'path'   => self::$plugin_info['path'] . 'assets/dist/images/preview-bg-white.jpg',
				'width'  => 1200,
				'height' => 801,
			),
		);
	}
	/**
	 * Actions and filters hooks.
	 *
	 * @return void
	 */
	public static function hooks() {
		add_action( 'init', array( get_called_class(), 'register_watermark_template_cpt' ), 100 );
		add_action( 'admin_head', array( get_called_class(), 'load_fonts' ), 2 );
		add_action( 'add_meta_boxes', array( get_called_class(), 'create_watermark_template_metabox' ) );
		add_action( 'admin_enqueue_scripts', array( get_called_class(), 'include_assets' ), 1000, 1 );
		add_action( 'save_post_' . self::$post_type_key, array( get_called_class(), 'save_watermarks_template' ), 100, 3 );
		add_action( 'wp_ajax_' . self::$plugin_info['name'] . '-preview-watermarks-template-action', array( get_called_class(), 'ajax_preview_watermarks_template' ) );
		add_filter( 'wp_insert_post_data', array( get_called_class(), 'limit_watermarks_templates' ), 100, 3 );
	}

	/**
	 *
	 *
	 * @param array $data
	 * @param array $postarr
	 * @param array $unsanitized_postarr
	 * @return array
	 */
	public static function limit_watermarks_templates( $data, $postarr, $unsanitized_postarr ) {
		if ( ! empty( $data['post_type'] ) && ( self::$post_type_key === $data['post_type'] ) ) {
			$statuses              = array( 'publish', 'future', 'draft', 'pending', 'private', 'trash' );
			$templates_count       = 0;
			$templates_posts_count = wp_count_posts( self::$post_type_key );
			foreach ( $statuses as $status ) {
				if ( property_exists( $templates_posts_count, $status ) ) {
					$templates_count += absint( $templates_posts_count->{ $status } );
				}
			}
			if ( self::$templates_count_limit <= $templates_count && ( 'auto-draft' === $data['post_status'] ) ) {
				ob_start();
				?>
					<div>
						<h4><?php esc_html_e( 'You have reached the maximum watermarks templates!', 'watermark-images-for-wp-and-woo-grandpluginswp' ); ?></h4>
						<h3><?php echo wp_kses_post( esc_html__( 'Upgrade to', 'watermark-images-for-wp-and-woo-grandpluginswp' ) . ' <a href="' . esc_url_raw( self::$plugin_info['pro_link'] ) . '" target="_blank" ><strong style="font-weight:bolder;">' . esc_html( 'Pro' ) . '</strong></a> ' . esc_html__( 'for unlimited watermarks templates', 'watermark-images-for-wp-and-woo-grandpluginswp' ) ); ?></h3>
					</div>
					<?php
					$limit_msg = ob_get_clean();
					wp_die( $limit_msg );
			}
		}

		return $data;
	}

	/**
	 * Load Custom Fonts Files.
	 *
	 * @return void
	 */
	public static function load_fonts() {
		$screen = get_current_screen();
		if (
			( is_object( $screen ) && ! is_wp_error( $screen ) && ! empty( $screen->post_type ) && ( 'post' === $screen->base ) && ( $screen->post_type === self::$post_type_key ) )
			||
			( ! empty( $_GET['page'] ) && self::$plugin_info['single_apply_page'] === sanitize_text_field( wp_unslash( $_GET['page'] ) ) )
		) {
			$fonts = Image_Watermark::get_available_fonts( true );
			ob_start();
			foreach ( $fonts as $font_family_name => $font ) :
				?>
				<link rel="preload" as="font" href="<?php echo esc_url( $font['url'] ); ?>" crossorigin="anonymous" >
				<?php
			endforeach;
		}
	}

	/**
	 * Include JS - Css ASSETS.
	 *
	 * @return void
	 */
	public static function include_assets( $hook ) {
		global $post;
		if ( ( 'post-new.php' === $hook || 'post.php' === $hook ) && ( is_object( $post ) && ( self::$post_type_key === $post->post_type ) ) ) {
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

			wp_enqueue_script( 'plupload-handlers' );

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
			wp_enqueue_script( self::$plugin_info['name'] . '-watermark-template-js', self::$plugin_info['url'] . 'assets/dist/js/admin/create-watermarks-templates.min.js', array( 'jquery', self::$plugin_info['name'] . '-select2-js' ), self::$plugin_info['version'], true );
			wp_localize_script(
				self::$plugin_info['name'] . '-watermark-template-js',
				str_replace( '-', '_', self::$plugin_info['name'] . '_localize_vars' ),
				array(
					'ajaxUrl'                         => admin_url( 'admin-ajax.php' ),
					'spinner'                         => admin_url( 'images/spinner.gif' ),
					'nonce'                           => wp_create_nonce( self::$plugin_info['name'] . '-ajax-nonce' ),
					'previewWatermarkstemplateAction' => self::$plugin_info['name'] . '-preview-watermarks-template-action',
					'saveWatermarkstemplateAction'    => self::$plugin_info['name'] . '-save-watermarks-template-action',
					'searchAuthorAction'              => self::$plugin_info['name'] . '-search-author-action',
					'searchCPTTermsAction'            => self::$plugin_info['name'] . '-search-cpt-terms-action',
					'filterRowPlaceholderAction'      => self::$plugin_info['name'] . '-filter-group-row',
					'watermarks_limit'                => self::$watermarks_limit,
					'labels'                          => array(
						'watermark'                 => esc_html__( 'Watermark', 'watermark-images-for-wp-and-woo-grandpluginswp' ),
						'select_images'             => esc_html__( 'Select images', 'watermark-images-for-wp-and-woo-grandpluginswp' ),
						'preview_image'             => esc_html__( 'Preview Image', 'watermark-images-for-wp-and-woo-grandpluginswp' ),
						'select_preview_image'      => esc_html__( 'Select Preview Image', 'watermark-images-for-wp-and-woo-grandpluginswp' ),
						'select_watermark'          => esc_html__( 'Select Watermark', 'watermark-images-for-wp-and-woo-grandpluginswp' ),
						'choose_watermark'          => esc_html__( 'Choose Watermark', 'watermark-images-for-wp-and-woo-grandpluginswp' ),
						'big_watermark_notice'      => esc_html__( 'The selected watermark is bigger than the image', 'watermark-images-for-wp-and-woo-grandpluginswp' ),
						'search_author'             => esc_html__( 'Search Author', 'watermark-images-for-wp-and-woo-grandpluginswp' ),
						'search_term'               => esc_html__( 'Search Term', 'watermark-images-for-wp-and-woo-grandpluginswp' ),
						'search_terms'              => esc_html__( 'Search Terms', 'watermark-images-for-wp-and-woo-grandpluginswp' ),
						'limited_watermarks_notice' => esc_html__( 'Maximum ' . self::$watermarks_limit . ' watermarks can be added in Free Version', 'watermark-images-for-wp-and-woo-grandpluginswp' ),
						'remove_watermark'          => esc_html__( 'You are about to remove a watermark, confirm?', 'watermark-images-for-wp-and-woo-grandpluginswp' ),
					),
					'current_watermarks'              => self::prepare_current_watermarks_for_js(),
					'classes_prefix'                  => self::$plugin_info['classes_prefix'],
				)
			);
		}
	}

	/**
	 * Register the Watermarks Templates Custom Post Type.
	 *
	 * @return void
	 */
	public static function register_watermark_template_cpt() {
		register_post_type(
			self::$post_type_key,
			array(
				'labels'              => array(
					'name'          => esc_html__( 'Watermarks', 'watermark-images-for-wp-and-woo-grandpluginswp' ),
					'all_items'     => esc_html__( 'Watermarks Templates', 'watermark-images-for-wp-and-woo-grandpluginswp' ),
					'singular_name' => esc_html__( 'Watermarks Template', 'watermark-images-for-wp-and-woo-grandpluginswp' ),
				),
				'description'         => esc_html__( 'Watermarks Templates to be applied on selected images', 'watermark-images-for-wp-and-woo-grandpluginswp' ),
				'public'              => false,
				'hierarchical'        => false,
				'exclude_from_search' => true,
				'publicly_queryable'  => false,
				'show_in_nav_menus'   => false,
				'show_ui'             => true,
				'supports'            => array( 'title', 'author', 'thumbnail' ),
				'menu_icon'           => self::$plugin_info['url'] . 'assets/dist/images/watermark.png',
			)
		);
	}

	/**
	 * Handle saving the Watermarks Template.
	 *
	 * @param int     $post_id Post ID.
	 * @param object  $post Post Object.
	 * @param boolean $is_update New Post or Update.
	 * @return void
	 */
	public static function save_watermarks_template( $post_id, $post, $is_update ) {
		$data = self::$default_metadata;

		if ( ! empty( $_POST['action'] ) && 'inline-save' === sanitize_text_field( wp_unslash( $_POST['action'] ) ) ) {
			return;
		}

		// Preview Image.
		if ( ! empty( $_POST['selected-preview-img-id'] ) ) {
			$data['preview_img_id'] = absint( sanitize_text_field( wp_unslash( $_POST['selected-preview-img-id'] ) ) );
		}

		// Watermarks.
		if ( ! empty( $_POST['watermarks'] ) && is_array( $_POST['watermarks'] ) ) {
			$watermarks = wp_unslash( $_POST['watermarks'] );
			foreach ( $watermarks as $watermark ) {
				$watermark = array_map( 'sanitize_text_field', $watermark );
				if ( ! empty( $watermark['id'] ) && ! empty( $watermark['type'] ) && in_array( $watermark['type'], self::$watermarks_types ) ) {
					$data['watermarks'][ $watermark['id'] ] = array();
					foreach ( self::${ $watermark['type'] . '_watermark_fields' } as $field ) {
						if ( ! empty( $watermark[ $field ] ) ) {
							$data['watermarks'][ $watermark['id'] ][ $field ] = $watermark[ $field ];
						} else {
							$data['watermarks'][ $watermark['id'] ][ $field ] = 0;
						}
					}
				}
			}
		}

		update_post_meta( $post_id, self::$watermarks_template_meta_key, $data );

	}

	/**
	 * AJAX Preview Watermarks Template.
	 *
	 * @return void
	 */
	public static function ajax_preview_watermarks_template() {
		if ( ! empty( $_POST['nonce'] ) && wp_verify_nonce( wp_unslash( $_POST['nonce'] ), self::$plugin_info['name'] . '-ajax-nonce' ) ) {
			if ( ! empty( $_POST['watermarks'] ) ) {
				$preview_image = ! empty( $_POST['preview_img'] ) ? map_deep( wp_unslash( $_POST['preview_img'] ), 'sanitize_text_field' ) : array();
				$watermarks    = map_deep( wp_unslash( $_POST['watermarks'] ), 'sanitize_text_field' );
				$preview_image = self::handle_preview_img( $preview_image, true );
				$preview_url   = self::preview_watermarks_template( $preview_image, $watermarks );
				if ( is_wp_error( $preview_url ) ) {
					wp_send_json_error(
						array(
							'status' => 'danger',
							'msg'    => $preview_url->get_error_message(),
						)
					);
				}
				wp_send_json_success(
					array(
						'status' => 'primary',
						'result' => $preview_url,
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
	 * Get Terms for Select Input.
	 *
	 * @param string $tax Taxonomy Name.
	 * @param array  $terms Terms IDs Array.
	 *
	 * @return object
	 */
	public static function get_term_details( $term_id ) {
		return get_term( $term_id );
	}

	/**
	 * Handle Preview Image [ default or Saved ].
	 *
	 * @param string|int $img The Preview Image.
	 * @return array
	 */
	public static function handle_preview_img( $img, $is_preview = false ) {
		if ( is_array( $img ) && ! empty( $img['id'] ) ) {
			$img['path'] = get_attached_file( $img['id'] );
		} else {
			if ( ! $is_preview ) {
				$img = self::$default_preview_imgs['preview_bg_white'];
			}
		}
		return $img;
	}

	/**
	 * Perview Watermarks Template.
	 *
	 * @param array $preview_img Preview Image Array.
	 * @param array $watermarks Watermarks Array.
	 * @return string|\WP_Error [Preview String]
	 */
	public static function preview_watermarks_template( $preview_img, $watermarks ) {
		$watermark_img = Image_Watermark::setup_image_for_watermarking( $preview_img, $watermarks );
		if ( is_wp_error( $watermark_img ) ) {
			return $watermark_img;
		}
		$result        = $watermark_img->draw_watermarks_on_image( true );
		if ( is_wp_error( $result ) ) {
			return $result;
		}
		$preview_url = Watermark_Base::save_preview( $result['img_string'], $result['img_details'], 'url' );
		$preview_url = add_query_arg(
			array(
				'refresh'     => wp_generate_password( 5, false, false ),
				'dontreplace' => '',
			),
			$preview_url
		);
		return $preview_url;
	}

	/**
	 * Create Watemark Template Metabox.
	 *
	 * @return void
	 */
	public static function create_watermark_template_metabox() {
		add_meta_box(
			self::$plugin_info['name'] . '-added-watermarks-list',
			esc_html__( 'Current Watermarks', 'watermark-images-for-wp-and-woo-grandpluginswp' ),
			array( get_called_class(), 'current_watermarks_list' ),
			self::$post_type_key,
			'side',
			'high'
		);
		add_meta_box(
			self::$plugin_info['name'] . '-create-watermarks-template',
			esc_html__( 'Create Watermark Template', 'watermark-images-for-wp-and-woo-grandpluginswp' ),
			array( get_called_class(), 'create_watermark_template' ),
			self::$post_type_key,
			'advanced',
			'high'
		);
		add_meta_box(
			self::$plugin_info['name'] . '-auto-resize-watermarks-template',
			esc_html__( 'Auto resize watermarks (Pro)', 'gpls-wmfw-watermark-image-for-wordpress' ),
			array( get_called_class(), 'auto_resize_watermarks_template' ),
			self::$post_type_key,
			'advanced',
			'high'
		);
		add_meta_box(
			self::$plugin_info['name'] . '-auto-apply-watermarks-template',
			esc_html__( 'Auto Apply Settings (Pro)', 'watermark-images-for-wp-and-woo-grandpluginswp' ),
			array( get_called_class(), 'auto_apply_watermarks_template' ),
			self::$post_type_key,
			'advanced',
			'high'
		);
	}

	/**
	 * List Current added Watermarks in the watermark template.
	 *
	 * @param object $post Curernt Post Object.
	 * @return void
	 */
	public static function current_watermarks_list( $post ) {
		$template_watermarks = self::get_template_watermarks( $post->ID );
		?>
		<div class="accordion watermarks-list-accordion" id="<?php echo esc_attr( self::$plugin_info['classes_prefix'] . '-watermarks-list-accordion' ); ?>">
			<?php
			if ( ! empty( $template_watermarks['watermarks'] ) ) :
				$index = 0;
				foreach ( $template_watermarks['watermarks'] as $watermark_id => $watermark_data ) :
					?>
					<div class="accordion-item" data-id="<?php echo esc_attr( $watermark_id ); ?>">
						<h4 class="accordion-header watermark-specs-header" id="<?php echo esc_attr( $watermark_id . '_header' ); ?>" data-index="<?php echo esc_attr( $index ); ?>" data-id="<?php echo esc_attr( $watermark_id ); ?>">
							<div class="header-wrapper d-flex flex-row align-items-center" style="height: 60px;">
								<button class="accordion-button" type="button" data-id="<?php echo esc_attr( $watermark_id ); ?>" data-bs-toggle="collapse" data-bs-target="#<?php echo esc_attr( $watermark_id . '_specs' ); ?>" aria-expanded="false" aria-controls="<?php echo esc_attr( $watermark_id . '_specs' ); ?>">
									<?php echo esc_html( 'Watermark ' . ( $index + 1 ) . '  [' . $watermark_data['type'] . ']' ); ?>
								</button>
								<span class="dashicons dashicons-dismiss action action-remove mx-2 bg-white" style="color:#F00;" type="button" data-id="<?php echo esc_attr( $watermark_id ); ?>"></span>
							</div>
							<input type="hidden" name="watermarks[<?php echo esc_attr( $watermark_id ); ?>][type]" value="<?php echo esc_attr( $watermark_data['type'] ); ?>" />
							<input type="hidden" name="watermarks[<?php echo esc_attr( $watermark_id ); ?>][id]" value="<?php echo esc_attr( $watermark_id ); ?>" />
								<?php if ( ! empty( 'image' === $watermark_data['type'] ) ) : ?>
								<input type="hidden" name="watermarks[<?php echo esc_attr( $watermark_id ); ?>][url]" value="<?php echo esc_url_raw( $watermark_data['url'] ); ?>" />
								<input type="hidden" name="watermarks[<?php echo esc_attr( $watermark_id ); ?>][imgID]" value="<?php echo esc_attr( $watermark_data['imgID'] ); ?>" />
								<input type="hidden" name="watermarks[<?php echo esc_attr( $watermark_id ); ?>][width]" value="<?php echo esc_attr( $watermark_data['width'] ); ?>" />
								<input type="hidden" name="watermarks[<?php echo esc_attr( $watermark_id ); ?>][height]" value="<?php echo esc_attr( $watermark_data['height'] ); ?>" />
							<?php endif; ?>
						</h4>
						<div id="<?php echo esc_attr( $watermark_id . '_specs' ); ?>" class="accordion-collapse collapse" data-id="<?php echo esc_attr( $watermark_id ); ?>"  aria-labelledby="<?php echo esc_attr( $watermark_id . '_header' ); ?>" data-bs-parent="#<?php echo esc_attr( self::$plugin_info['classes_prefix'] . '-watermarks-list-accordion' ); ?>" >
							<div class="accordion-body">
								<?php self::watermark_specs( $watermark_data ); ?>
							</div>
						</div>
					</div>
					<?php
					$index++;
				endforeach;
			endif;
			?>
		</div>
		<?php
		self::watermark_specs( array(), true );
	}

	/**
	 * Watermark Specs HTML.
	 *
	 * @param array   $watermark_data Watermark Specs Data Array.
	 * @param boolean $is_placeholder Is the The Specs HTML a placeholder or actual Watermark.
	 * @return void
	 */
	public static function watermark_specs( $watermark_data = array(), $is_placeholder = false, $context = 'create' ) {
		$plugin_info     = self::$plugin_info;
		$core            = self::$core;
		$available_fonts = Image_Watermark::get_available_fonts( true );
		include self::$plugin_info['path'] . 'templates/watermark-specs-template-metabox.php';
	}

	/**
	 * Create Watemark Template File.
	 *
	 * @param object $post Watermark Current Post Object.
	 * @return void
	 */
	public static function create_watermark_template( $post ) {
		$core                     = self::$core;
		$plugin_info              = self::$plugin_info;
		$available_fonts          = Image_Watermark::get_available_fonts( true );
		$template_preview_img_url = self::get_watermarks_template_preview_img( $post->ID, 'url' );
		$template_watermarks      = self::get_template_watermarks( $post->ID );

		require_once self::$plugin_info['path'] . 'templates/create-watermarks-template-metabox.php';
	}

	/**
	 * Auto Resize Watermarks Template.
	 *
	 * @param object $post Watermarks Template Post Object.
	 * @return void
	 */
	public static function auto_resize_watermarks_template( $post ) {
		$plugin_info         = self::$plugin_info;
		$template_watermarks = self::get_template_watermarks( $post->ID );
		$core                = self::$core;
		require_once self::$plugin_info['path'] . 'templates/auto-resize-watermarks-template-metabox.php';
	}

	/**
	 * Auto apply Watermarks Template.
	 *
	 * @param object $post Watermarks Template Post Object.
	 * @return void
	 */
	public static function auto_apply_watermarks_template( $post ) {
		$plugin_info         = self::$plugin_info;
		$template_watermarks = self::get_template_watermarks( $post->ID );
		$core                = self::$core;
		require_once self::$plugin_info['path'] . 'templates/auto-apply-watermarks-template-metabox.php';
	}

	/**
	 * Get Watermarks Template Watermarks Array.
	 *
	 * @param int $template_id Template ID.
	 * @return array
	 */
	public static function get_template_watermarks( $template_id, $return_part = '' ) {
		$watermarks_template_meta = get_post_meta( $template_id, self::$watermarks_template_meta_key, true );
		$watermarks_template_meta = ( empty( $watermarks_template_meta ) || false === $watermarks_template_meta ) ? self::$default_metadata : $watermarks_template_meta;
		return ( ! empty( $return_part ) && ! empty( $watermarks_template_meta[ $return_part ] ) ? $watermarks_template_meta[ $return_part ] : $watermarks_template_meta );
	}

	/**
	 * Adjust the Template Watermarks Data for Drawing the Watermarks.
	 *
	 * @param array $watermarks Watermarks Data Array.
	 * @return array
	 */
	public static function adjust_template_watermarks_data( $watermarks ) {
		foreach ( $watermarks as &$watermark ) {
			$watermark['styles']            = array();
			$watermark['styles']['opacity'] = $watermark['opacity'];
			$watermark['styles']['degree']  = $watermark['degree'];

			unset( $watermark['opacity'] );
			unset( $watermark['degree'] );

			if ( 'text' === $watermark['type'] ) {
				$watermark['text']                         = $watermark['title'];
				$watermark['styles']['font']               = array();
				$watermark['styles']['font']['color']      = $watermark['color'];
				$watermark['styles']['font']['fontFamily'] = $watermark['fontfamily'];
				$watermark['styles']['font']['fontSize']   = $watermark['fontsize'];

				unset( $watermark['title'] );
				unset( $watermark['fontfamily'] );
				unset( $watermark['fontsize'] );
				unset( $watermark['color'] );
			}
		}
		return $watermarks;
	}

	/**
	 * Get Current Watermarks Template Preview Image.
	 *
	 * @param int    $template_id Watermarks Template Post ID.
	 * @param string $return_part Preview Image Part to return.
	 * @return string
	 */
	private static function get_watermarks_template_preview_img( $template_id, $return_part ) {
		$template_watermarks = self::get_template_watermarks( $template_id );
		if ( ! empty( $template_watermarks['preview_img_id'] ) ) {
			$preview_img_id = absint( $template_watermarks['preview_img_id'] );
			$img_url        = wp_get_original_image_url( $preview_img_id );
			if ( ! $img_url ) {
				return self::$default_preview_imgs['preview_bg_white']['url'];
			}
			return $img_url;
		} else {
			return self::$default_preview_imgs['preview_bg_white']['url'];
		}
	}

	/**
	 * Prepare Current Saved Watermarks Objects for watermarks template post.
	 *
	 * @param int $watermarks_template_id Watermarks Template Post ID.
	 * @return array
	 */
	public static function prepare_current_watermarks_for_js( $watermarks_template_id = null ) {
		global $post;
		if ( is_null( $watermarks_template_id ) ) {
			$watermarks_template_id = $post->ID;
		}
		$js_response         = array();
		$template_watermarks = self::get_template_watermarks( $watermarks_template_id );
		if ( ! empty( $template_watermarks['preview_img_id'] ) ) {
			$js_response['preview'] = wp_prepare_attachment_for_js( $template_watermarks['preview_img_id'] );
		}
		if ( empty( $template_watermarks['preview_img_id'] ) || is_null( $js_response['preview'] ) ) {
			$js_response['default_preview'] = self::$default_preview_imgs['preview_bg_white'];
		}

		if ( ! empty( $template_watermarks['watermarks'] ) ) {
			foreach ( $template_watermarks['watermarks'] as $watermark_id => $watermark_data ) {
				if ( 'text' === $watermark_data['type'] ) {
					$js_response['watermarks'][ $watermark_id ] = array(
						'id'                => $watermark_id,
						'type'              => 'text',
						'width'             => $watermark_data['width'],
						'height'            => $watermark_data['height'],
						'text'              => $watermark_data['title'],
						'isRepeat'          => $watermark_data['isRepeat'],
						'repeatAxis'        => $watermark_data['repeatAxis'],
						'repeatXAxisOffset' => $watermark_data['repeatXAxisOffset'],
						'repeatYAxisOffset' => $watermark_data['repeatYAxisOffset'],
						'positionType'      => $watermark_data['positionType'],
						'positionSpot'      => $watermark_data['positionSpot'],
						'centerOffset'      => ! empty( $watermark_data['centerOffset'] ) ? true : false,
						'absLeft'           => $watermark_data['absLeft'],
						'absTop'            => $watermark_data['absTop'],
						'leftPercent'       => $watermark_data['leftPercent'],
						'topPercent'        => $watermark_data['topPercent'],
						'baselineOffset'    => $watermark_data['baselineOffset'],
						'exactWidth'        => $watermark_data['exactWidth'],
						'botLeft'           => $watermark_data['botLeft'],
						'botTop'            => $watermark_data['botTop'],
						'styles'            => array(
							'font'    => array(
								'color'      => $watermark_data['color'],
								'fontSize'   => $watermark_data['fontsize'],
								'fontFamily' => $watermark_data['fontfamily'],
							),
							'opacity' => $watermark_data['opacity'],
							'degree'  => $watermark_data['degree'],
						),
					);
				} elseif ( 'image' === $watermark_data['type'] ) {
					$js_response['watermarks'][ $watermark_id ] = array(
						'id'                => $watermark_id,
						'type'              => 'image',
						'width'             => $watermark_data['width'],
						'height'            => $watermark_data['height'],
						'isRepeat'          => $watermark_data['isRepeat'],
						'repeatAxis'        => $watermark_data['repeatAxis'],
						'repeatXAxisOffset' => $watermark_data['repeatXAxisOffset'],
						'repeatYAxisOffset' => $watermark_data['repeatYAxisOffset'],
						'positionType'      => $watermark_data['positionType'],
						'positionSpot'      => $watermark_data['positionSpot'],
						'centerOffset'      => ! empty( $watermark_data['centerOffset'] ) ? true : false,
						'absLeft'           => $watermark_data['absLeft'],
						'absTop'            => $watermark_data['absTop'],
						'leftPercent'       => $watermark_data['leftPercent'],
						'topPercent'        => $watermark_data['topPercent'],
						'url'               => $watermark_data['url'],
						'imgID'             => $watermark_data['imgID'],
						'styles'            => array(
							'opacity' => $watermark_data['opacity'],
							'degree'  => $watermark_data['degree'],
						),
					);
				}
			}
		}
		return $js_response;
	}

	/**
	 * Get Watermarks Templates Posts.
	 *
	 * @param boolean $include_watermarks Include Watermarks in the Array.
	 * @return array
	 */
	public static function get_watermark_templates( $include_watermarks = true ) {
		$watermarks_templates = array();
		$templates            = get_posts(
			array(
				'post_type'   => self::$post_type_key,
				'post_status' => array( 'publish' ),
			)
		);
		foreach ( $templates as $template ) {
			$template_id                          = $template->ID;
			$watermarks_templates[ $template_id ] = array(
				'id'    => $template_id,
				'title' => get_the_title( $template_id ),
			);

			if ( $include_watermarks ) {
				$watermarks_templates[ $template_id ]['watermarks'] = self::get_template_watermarks( $template_id );
			}
		}
		return $watermarks_templates;
	}

	/**
	 * Watermarks Text meta select.
	 * @return void
	 */
	public static function watermarks_text_meta_select( $watermark_data = array() ) {
		$dynamic_fields = self::get_dynamic_keys();
		?>
		<div class="<?php echo esc_attr( self::$plugin_info['classes_prefix'] . '-disabled' ); ?>">
			<select class="watermark-dynamic-data-field" data-watermarkid="<?php echo esc_attr( ! empty( $watermark_data ) ? $watermark_data['id'] : '' ); ?>" data-type="dynamic" class="edit edit-dynamic-data-field watermark-dynamic-data-field mt-1">
				<option selected value=""><?php esc_html_e( '--- Select data field ---', 'gpls-wmfw-watermark-image-for-wordpress' ); ?></option>
		
				<?php foreach ( $dynamic_fields as $dynamic_group_key => $group_arr ) : ?>
				<optgroup label="<?php echo esc_html( $group_arr['title'] ); ?>" >
					<?php foreach ( $group_arr['keys'] as $group_arr_key ) : ?>
						<option value="<?php echo esc_attr( '{{' . $group_arr['prefix'] . $group_arr_key . '}}' ); ?>"><?php echo esc_attr( $group_arr_key ); ?></option>
					<?php endforeach; ?>
				</optgroup>
				<?php endforeach; ?>
			</select>
		</div>
		<?php
	}


	/**
	 * Authors Options For CPT.
	 *
	 * @param string $post_type
	 * @return void
	 */
	public static function select_author_options( $post_type = 'post', $selected_authors = array(), $counter = 1 ) {
		global $wpdb;
		if ( empty( self::$cpts_author_options ) ) {
			$cpts_types = Apply_Watermarks_Queries::get_cpts();
			$query      =
				"SELECT
					u.ID, u.user_login, u.display_name, p.post_type
				FROM
					$wpdb->posts p
				INNER JOIN
					$wpdb->users u
				ON
					p.post_author = u.ID
				WHERE
					p.post_type IN ('" . implode( "','", $cpts_types ) . "')
				AND
					p.post_status != 'auto-draft'
				GROUP BY
					p.post_type, u.ID
				ORDER BY
					p.post_date DESC";

			$cpts_authors = $wpdb->get_results( $query, ARRAY_A );
			foreach ( $cpts_authors as $author ) {
				self::$cpts_author_options[ $author['post_type'] ][] = $author;
			}
		}
		?>
		<select class="cpt-authors-select" data-cpt_type="<?php echo esc_attr( $post_type ); ?>" name="rule_group[<?php echo esc_attr( $post_type ); ?>][<?php echo esc_attr( $counter ); ?>][author]">
			<option value="">&mdash; <?php esc_html_e( 'Select' ); ?> &mdash;</option>
			<?php
			foreach ( (array) self::$cpts_author_options[ $post_type ] as $user ) :
				$display = sprintf( esc_html__( '%1$s (%2$s)', 'user dropdown' ), $user['display_name'], $user['user_login'] );
				?>
				<option <?php echo esc_attr( in_array( $user['ID'], $selected_authors ) ? 'selected="selected"' : '' ); ?> value="<?php echo esc_attr( $user['ID'] ); ?>"><?php echo esc_html( $display ); ?></option>
				<?php
			endforeach;
			?>
		</select>
		<?php
	}
}

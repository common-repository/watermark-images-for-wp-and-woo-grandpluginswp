<?php
namespace GPLSCore\GPLS_PLUGIN_WMFW;

defined( 'ABSPATH' ) || exit;

use GPLSCore\GPLS_PLUGIN_WMFW\Utils\WatermarkUtilsTrait;

/**
 * Woo Downloadable Image Watermarker Class.
 * 
 */
class WooImageWatermark {

	use WatermarkUtilsTrait;

	/**
	 * Singular instance.
	 *
	 * @var self
	 */
	protected static $instance = null;

    /**
     * Core
     * @var Core
     */
    protected static $core;

    /**
     * Plugin Info.
     * @var array
     */
    protected static $plugin_info;

	/**
	 * Singular init.
	 *
	 * @return self
	 */
	public static function init( $core, $plugin_info ) {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self( $core, $plugin_info );
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	private function __construct( $core, $plugin_info ) {
		$this->setup( $core, $plugin_info );
		$this->hooks();
	}

	/**
	 * Setup.
	 *
	 * @return void
	 */
	private function setup( $core, $plugin_info ) {
        self::$core        = $core;
        self::$plugin_info = $plugin_info;
    }

	/**
	 * Hooks.
	 *
	 * @return void
	 */
	private function hooks() {
        // Password Protected IMGs Meta.
		add_action( 'woocommerce_product_options_downloads', array( $this, 'watermark_img_template_select' ), PHP_INT_MAX );
	}

    /**
     * Watermark Image Template Select.
     * @return void
     */
    public function watermark_img_template_select() {
		global $post;
        $template_id = get_post_meta( $post->ID, self::$plugin_info['prefix'] . '-selected-watermark-img-template-id', true );
		?>
		<div class="<?php echo esc_attr( self::$plugin_info['classes_prefix'] . '-disabled' ); ?>">
			<div style="border:2px solid #EEE;padding:10px;margin:10px;display:block;overflow:hidden;" class="<?php echo esc_attr( self::$plugin_info['prefix'] . '-watermarked-downloadable-images-wrapper' ); ?>">
				<h3><?php esc_html_e( 'Watermark Downloadable Images [GrandPlugins]', 'gpls-wmfw-watermark-image-for-wordpress' ); ?> <span><?php self::$core->pro_btn(); ?></span></h3>
				<div class="wrapper">
					<div class="input-field" style="overflow:hidden;">
						<!-- 1| Watermarks Template Selection -->
						<div class="mb-5">
							<h5 class="form-label"><?php esc_html_e( 'Select Watermarks Template', 'gpls-wmfw-watermark-image-for-wordpress' ); ?></h5>
							<select class="form-control" name="<?php echo esc_attr( self::$plugin_info['prefix'] . '-selected-watermark-img-template-id' ); ?>">
								<option selected value="0"><?php esc_html_e( '-- Select Watermarks Template --', 'gpls-wmfw-watermark-image-for-wordpress' ); ?></option>
								<?php
								$watermarks_templates = Watermarks_Templates::get_watermark_templates( false );
								foreach ( $watermarks_templates as $watermarks_template ) :
									?>
									<option <?php selected( $template_id, $watermarks_template['id'] ); ?> value="<?php echo esc_attr( $watermarks_template['id'] ); ?>"><?php echo esc_html( $watermarks_template['title'] ); ?></option>
								<?php endforeach; ?>
							</select>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php
    }

}

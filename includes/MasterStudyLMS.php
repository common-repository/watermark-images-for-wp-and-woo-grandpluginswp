<?php
namespace GPLSCore\GPLS_PLUGIN_WMFW;

defined( 'ABSPATH' ) || exit;

use GPLSCore\GPLS_PLUGIN_WMFW\Utils\WatermarkUtilsTrait;

/**
 * MasterStudy LMS Class.
 */
class MasterStudyLMS {

    use WatermarkUtilsTrait;

	/**
	 * Singular instance.
	 *
	 * @var self
	 */
	protected static $instance = null;

    /**
     * Plugin Info
     * @var array
     */
    protected static $plugin_info;

    /**
     * Core
     * @var array
     */
    protected static $core;

    /**
     * MasterStudy LMS Watermarks Template Key.
     *
     * @var string
     */
    protected $ms_lms_watermarks_template_key = '';

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
        self::$plugin_info                    = $plugin_info;
        self::$core                           = $core;
        $this->ms_lms_watermarks_template_key = self::$plugin_info['prefix'] . '-ms-lms-watermarks-template';
	}

	/**
	 * Hooks.
	 *
	 * @return void
	 */
	private function hooks() {
		add_action( 'add_meta_boxes', array( $this, 'setup_img_lms_template_metabox' ), 100, 2 );
	}

	/**
	 * Setup Image LMS template select metabox.
	 *
	 * @return void
	 */
	public function setup_img_lms_template_metabox( $post_type, $post ) {
		if ( ! $this->is_masterstudy_lms_plugin_active() ) {
			return;
		}

		if ( ! wp_attachment_is_image( $post ) ) {
			return;
		}

		add_meta_box(
			self::$plugin_info['prefix'] . '-pdf-lms-template-select',
			esc_html__( 'Image Masterstudy LMS Watermark template', 'gpls-wmfw-watermark-image-for-wordpress' ),
			array( $this, 'img_lms_watermark_template_metabox' ),
			'attachment',
			'side',
            'high'
        );
	}

	/**
	 * Image LMS Watermark Template Metabox.
	 *
	 * @param \WP_Post $post
	 * @return void
	 */
	public function img_lms_watermark_template_metabox( $post ) {
        ?>
		<div>
			<span>
                <?php esc_html_e( 'Select watermarks template', 'gpls-wmfw-watermark-image-for-wordpress' ); ?>
                <span><?php self::$core->pro_btn(); ?></span>
            </span>
            <select class="form-control" name="<?php echo esc_attr( self::$plugin_info['prefix'] . '-selected-watermark-img-template-id' ); ?>">
                <option selected value="0"><?php esc_html_e( '-- Select Watermarks Template --', 'gpls-wmfw-watermark-image-for-wordpress' ); ?></option>
                <?php
                $watermarks_templates = Watermarks_Templates::get_watermark_templates( false );
                foreach ( $watermarks_templates as $watermarks_template ) :
                    ?>
                    <option value="<?php echo esc_attr( $watermarks_template['id'] ); ?>"><?php echo esc_html( $watermarks_template['title'] ); ?></option>
                <?php endforeach; ?>
            </select>
		</div>
		<?php
	}


	/**
	 * Check if MasterStudy LMS Plugin is active.
	 * @return bool
	 */
	public function is_masterstudy_lms_plugin_active() {
		return self::is_plugin_active( 'masterstudy-lms-learning-management-system/masterstudy-lms-learning-management-system.php' );
	}

}

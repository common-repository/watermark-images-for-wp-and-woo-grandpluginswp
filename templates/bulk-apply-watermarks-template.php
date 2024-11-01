<?php
use GPLSCore\GPLS_PLUGIN_WMFW\Watermarks_Templates;
use GPLSCore\GPLS_PLUGIN_WMFW\Apply_Watermarks_Templates;
use GPLSCore\GPLS_PLUGIN_WMFW\Apply_Watermarks_Queries;

defined( 'ABSPATH' ) || exit();

?>
<div class="wrap">
	<div class="container-fluid watermark-creator-wrapper w-100 p-5">
		<div class="<?php echo esc_attr( Watermarks_Templates::$plugin_info['classes_prefix'] . '-upgrade-notice' ); ?>">
			<h6 class="my-2"><?php esc_html_e( 'Upgrade to', 'watermark-images-for-wp-and-woo-grandpluginswp' ); ?><?php $core->pro_btn( '', 'Pro', 'me-2' ); ?><?php esc_html_e( 'for full features', 'watermark-images-for-wp-and-woo-grandpluginswp' ); ?>
			</h6>
		</div>
		<div class="row">
			<div class="col-12 apply-watermarks-template-settings-wrapper">
				<div class="mb-5 border p-3 mb-5">
					<h5 class="mb-4"><?php esc_html_e( 'Bulk Apply Watermarks Template', 'watermark-images-for-wp-and-woo-grandpluginswp' ); ?></h5>
					<p class="mt-3"><?php esc_html_e( 'Select watermarks template and apply it on Bulk images', 'watermark-images-for-wp-and-woo-grandpluginswp' ); ?></p>
				</div>
				<!-- Select Watermarks Template -->
				<?php
				$watermarks_templates = Watermarks_Templates::get_watermark_templates( false );
				?>
				<!-- 1| Watermarks Template Selection -->
				<div class="mb-5">
					<h5 class="form-label" for="watermarks-template-selection">
						<?php esc_html_e( 'Select Watermarks Template', 'watermark-images-for-wp-and-woo-grandpluginswp' ); ?>
					</h5>
					<select class="form-control" id="watermarks-template-selection">
						<option selected value="0"><?php esc_html_e( '-- Select Watermarks Template --', 'watermark-images-for-wp-and-woo-grandpluginswp' ); ?></option>
					<?php foreach ( $watermarks_templates as $watermarks_template ) : ?>
					<option value="<?php echo esc_attr( $watermarks_template['id'] ); ?>"><?php echo esc_html( $watermarks_template['title'] ); ?></option>
					<?php endforeach; ?>
					</select>
				</div>

				<!-- 2| Select Images to apply Teamplate on -->
				<div class="step-2 collapse">
					<div class="accordion mb-5" id="watermarks-template-images-selection-accordion">
						<h5 class="mb-3" for="watermarks-template-images-selection">
							<?php esc_html_e( 'Select Images', 'watermark-images-for-wp-and-woo-grandpluginswp' ); ?>
						</h5>
						<!-- Select direct images. -->
						<div class="mb-3">
							<input checked type="radio" id="select-images-direct" class="select-images-by-option" name="select-images-type" value="direct" >
							<label for="select-images-direct" class="mb-1"><?php esc_html_e( 'Select images directly', 'watermark-images-for-wp-and-woo-grandpluginswp' ); ?></label>
							<small class="ms-4 d-block text-muted"><?php esc_html_e( 'Select images from media', 'watermark-images-for-wp-and-woo-grandpluginswp' ); ?></small>
						</div>
						<!-- Select Images by post type -->
						<div class="mb-3 <?php echo esc_attr( $plugin_info['classes_prefix'] . '-disabled' ); ?>">
							<input disabled type="radio" id="select-images-by-post-type" class="select-images-by-option" name="select-images-type" value="cpt" >
							<label for="select-images-by-post-type" class="mb-1"><?php esc_html_e( 'Select Images by posts ( Pro )', 'watermark-images-for-wp-and-woo-grandpluginswp' ); ?></label>
							<small class="ms-4 d-block text-muted"><?php esc_html_e( 'Select images attached to posts [ images uploaded to posts - featured images - WooCommerce products gallery ]', 'watermark-images-for-wp-and-woo-grandpluginswp' ); ?></small>
						</div>
						<!-- Selections Content -->
						<div class="my-5 subtitle border p-5">
							<!-- Direct Images Selection -->
							<div id="select-images-by-direct" class="select-images-by-option-content collapse show">
								<button data-context="apply-watermarks-template-selection-direct" class="button apply-watermarks-template-selection-direct">
									<span class="wp-media-butons-icon"></span>
									<?php esc_html_e( 'Media Gallery', 'watermark-images-for-wp-and-woo-grandpluginswp' ); ?>
								</button>

								<div class="<?php echo esc_attr( $plugin_info['classes_prefix'] . '-selected-images-direct' ); ?> selected-images-direct m-5 p-2 overflow-hidden border">
									<div class="img-item-clone d-none">
										<ul class="actions">
											<li class="action frame-remove" data-id=""><span class="dashicons dashicons-no"></span></li>
										</ul>
										<a target="_blank" href="" ><img src="" alt=""></a>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>

				<div class="apply-watermarks-template-step-3 step-3 mb-3 collapse">
					<!-- Apply Type -->
					<div class="apply-watermarks-template-type-wrapper mb-5">
						<h5 class="mb-3">
							<?php esc_html_e( 'How to apply the watermarks template', 'watermark-images-for-wp-and-woo-grandpluginswp' ); ?>
						</h5>
						<!-- Create new -->
						<div class="form-check my-2">
							<input selected type="radio" value="1" id="apply-watermarks-template-type-add-new" name="apply-watermarks-template-type" class="form-check-input apply-watermarks-template-type" selected>
							<label class="form-check-label mb-1" for="apply-watermarks-template-type-add-new"><?php esc_html_e( 'Create new', 'watermark-images-for-wp-and-woo-grandpluginswp' ); ?></label>
						</div>
						<small class="d-block subtitle mb-4"><?php esc_html_e( 'Create a separate watermarked image', 'watermark-images-for-wp-and-woo-grandpluginswp' ); ?></small>

						<!-- Overwrite -->
						<div class="<?php echo esc_attr( $plugin_info['classes_prefix'] . '-disabled' ); ?> form-check my-2">
							<input disabled type="radio" value="2" id="apply-watermarks-template-type-overwrite" name="apply-watermarks-template-type" class="form-check-input apply-watermarks-template-type" >
							<label class="form-check-label mb-1" for="apply-watermarks-template-type-overwrite"><?php esc_html_e( 'Overwrite ( Pro )', 'watermark-images-for-wp-and-woo-grandpluginswp' ); ?></label>
						</div>
						<small class="d-block subtitle mb-4"><?php esc_html_e( 'Overwrite the original image', 'watermark-images-for-wp-and-woo-grandpluginswp' ); ?></small>

						<!-- Image Sub-Sizes to apply on -->
						<div class="subtitle border p-3 my-4 apply-watermarks-template-apply-subsizes-options collapse">
							<div class="select-image-sizes-option my-3">
								<div class="heading d-flex align-items-center">
									<input type="checkbox" class="apply-watermarks-image-sizes-options-all" />
									<h5 class="image-sizes-options-new collapse"><?php esc_html_e( 'Select sizes to create', 'watermark-images-for-wp-and-woo-grandpluginswp' ); ?></h5>
									<h5 class="image-sizes-options-overwrite collapse"><?php esc_html_e( 'Select sizes to apply the watermarks template on', 'watermark-images-for-wp-and-woo-grandpluginswp' ); ?></h5>
								</div>
								<div class="subtitle select-image-sizes-checkboxes">
									<div class="form-check my-3 collapse original-image-size-option">
										<input type="checkbox" id="apply-watermarks-image-sizes-option-original" class="apply-watermarks-image-sizes-option" name="apply-watermarks-image-sizes-option[]" value="original" >
										<label class="form-check-label" for="apply-watermarks-image-sizes-option-original"><?php esc_html_e( 'Original', 'watermark-images-for-wp-and-woo-grandpluginswp' ); ?></label>
									</div>
									<?php
									$image_subsizes = wp_get_registered_image_subsizes();
									foreach ( $image_subsizes as $subsize_name => $subsize_arr ) :
										?>
									<div class="form-check my-3">
										<input type="checkbox" id="apply-watermarks-image-sizes-option-<?php echo esc_attr( $subsize_name ); ?>" class="apply-watermarks-image-sizes-option" name="apply-watermarks-image-sizes-option[]" value="<?php echo esc_attr( $subsize_name ); ?>" >
										<label class="form-check-label" for="apply-watermarks-image-sizes-option-<?php echo esc_attr( $subsize_name ); ?>"><?php echo esc_html( $subsize_name . '  [ ' . $subsize_arr['width'] . ' x ' . $subsize_arr['height'] . ' ]' ); ?></label>
									</div>
									<?php endforeach; ?>
								</div>
							</div>
						</div>
						<!-- Create Backup -->
						<div class="create-backup-option mt-5 mb-3 collapse">
							<h5 class="mb-4"><?php esc_html_e( 'Original Backup', 'watermark-images-for-wp-and-woo-grandpluginswp' ); ?></h5>
							<div class="subtitle create-backup-checkbox">
								<div>
									<input type="checkbox" id="apply-watermarks-create-backup-option" class="apply-watermarks-create-backup-option" >
									<label checked class="" for="apply-watermarks-create-backup-option"><?php esc_html_e( 'Create backup of the original image', 'watermark-images-for-wp-and-woo-grandpluginswp' ); ?></label>
								</div>
							</div>
						</div>
					</div>

					<!-- Submit -->
					<div class="step-4 mb-3 apply-watermarks-template-final-step collapse">
						<button class="button submit apply-watermarks-template-submit-btn"><?php esc_html_e( 'Apply', 'watermark-images-for-wp-and-woo-grandpluginswp' ); ?></button>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<div class="main-loader loader w-100 h-100 position-fixed top-0 left-0">
	<div class="text-white wrapper text-center position-absolute d-block w-100 h-100">
		<img class="loader-icon position-fixed" src="<?php echo esc_url_raw( admin_url( 'images/spinner-2x.gif' ) ); ?>"  />
		<div class="d-none loader-progress-num" ></div>
		<progress class="d-none loader-progress" value="0" max="100" ></progress>
	</div>
	<div class="overlay position-absolute d-block w-100 h-100"></div>
</div>

<div role="alert" aria-live="assertive" aria-atomic="true" class="fixed-top mx-auto text-white toast <?php echo esc_attr( $plugin_info['classes_prefix'] . '-msgs-toast' ); ?>">
	<div class="toast-header">
		<button type="button" class="btn close-toast bg-transparent me-2 m-auto border-0" data-bs-dismiss="toast" aria-label="close">
			<span class="bg-transparent dashicons dashicons-dismiss border-0"></span>
		</button>
	</div>
	<div class="toast-body">
	</div>
</div>

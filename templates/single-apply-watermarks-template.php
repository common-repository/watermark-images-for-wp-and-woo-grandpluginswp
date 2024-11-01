<?php

use GPLSCore\GPLS_PLUGIN_WMFW\Image_Watermark;
use GPLSCore\GPLS_PLUGIN_WMFW\Watermarks_Templates;

defined( 'ABSPATH' ) || exit();
?>
<div class="wrap">
	<div class="watermark-template-creator-wrapper overflow-hidden">
		<div id="poststuff">
			<div id="post-body" class="metabox-holder columns-2">
				<!-- === Create Watemark Template === -->
				<div class="mb-5 border p-3 mb-5">
				<div class="<?php echo esc_attr( Watermarks_Templates::$plugin_info['classes_prefix'] . '-upgrade-notice' ); ?>">
					<h6 class="my-2"><?php esc_html_e( 'Upgrade to ', 'watermark-images-for-wp-and-woo-grandpluginswp' ); ?><?php $core->pro_btn( '', 'Pro', 'me-2' ); ?><?php esc_html_e( 'for full features', 'watermark-images-for-wp-and-woo-grandpluginswp' ); ?>
					</h6>
				</div>
				<h5 class="mb-4"><?php esc_html_e( 'Single Image Watermarks Editor', 'watermark-images-for-wp-and-woo-grandpluginswp' ); ?></h5>
					<p class="mt-3"><?php esc_html_e( 'Select a single image and apply watermarks on it', 'watermark-images-for-wp-and-woo-grandpluginswp' ); ?></p>
				</div>

				<!-- Watermarks List -->
				<div class="postbox-container" id="postbox-container-1">
					<div id="side-sortables" class="meta-box-sortables ui-sortable">
						<div class="postbox" id="<?php echo esc_attr( $plugin_info['name'] . '-added-watermarks-list' ); ?>">
							<div class="postbox-header text-left px-3 py-1">
								<h5><?php esc_html_e( 'Current Watermarks', 'watermark-images-for-wp-and-woo-grandpluginswp' ); ?></h5>
							</div>
							<div class="inside">
								<div class="accordion watermarks-list-accordion" id="<?php echo esc_attr( $plugin_info['classes_prefix'] . '-watermarks-list-accordion' ); ?>">
								</div>
								<?php Watermarks_Templates::watermark_specs( array(), true, 'single' ); ?>
							</div>
						</div>
					</div>
				</div>

				<div class="postbox-container pt-5" id="postbox-container-2">
					<div class="create-watermark-template-container pt-5">
						<!-- Preview Image select - Display -->
						<div class="image-select">
							<div class="row">
								<div class="col-md-6 select-image-btn-section">

									<h3 class="my-3"><?php esc_html_e( 'Select image', 'watermark-images-for-wp-and-woo-grandpluginswp' ); ?></h3>
									<button data-context="select-preview-image" id="insert-media-button" class="<?php echo esc_attr( $plugin_info['classes_prefix'] . '-open-gallery-btn' ); ?> button">
										<span class="wp-media-butons-icon"></span>
										<?php esc_html_e( 'Media Gallery', 'watermark-images-for-wp-and-woo-grandpluginswp' ); ?>
									</button>
								</div>
								<div class="col-md-6 select-watermark-btn-section d-none">
									<h3 class="my-3"><?php esc_html_e( 'Add Watermark', 'watermark-images-for-wp-and-woo-grandpluginswp' ); ?></h3>
									<button data-context="select-watermark" id="insert-media-button" class="float-left me-2 <?php echo esc_attr( $plugin_info['classes_prefix'] . '-open-gallery-btn' ); ?> button d-inline-block">
										<span class="wp-media-butons-icon"></span>
										<?php esc_html_e( 'Image Watermark', 'watermark-images-for-wp-and-woo-grandpluginswp' ); ?>
									</button>
									<button data-context="select-watermark" class="float-left me-2 <?php echo esc_attr( $plugin_info['classes_prefix'] . '-add-text-watermark' ); ?> button d-inline-block">
										<span class="wp-media-butons-icon"></span>
										<?php esc_html_e( 'Text Watermark', 'watermark-images-for-wp-and-woo-grandpluginswp' ); ?>
									</button>
								</div>
							</div>
							<!-- === Preview Image section === -->
							<div class="preview-selected-wrapper mx-auto my-5">
								<div class="img-item preview-selected-item text-center overflow-auto">
									<div id="selected-preview-container" class="selected-preview-container position-relative d-inline-block" style="overflow: auto !important; width: auto; margin: 0px;">
										<img class="selected-preview" src="" alt="" style="max-width: none !important;">
										<input type="hidden" name="selected-preview-img-id" class="selected-preview-img-id" value="<?php echo absint( esc_attr( ! empty( $template_watermarks['preview_img_id'] ) ? $template_watermarks['preview_img_id'] : 0 ) ); ?>" >
										<!-- === Watermark Image Placeholder === -->
										<div class="watermark-image-placeholder-none ui-draggable ui-draggable-handle d-none">
											<div class="wrapper position-relative">
												<div class="img-placeholder watermark-placeholder-wrapper watermark-img-wrapper">
													<img src="#" alt="preview">
												</div>
												<div class="actions">
													<span class="dashicons dashicons-dismiss action action-remove"></span>
													<span class="watermark-placeholder-rotate-handle dashicons dashicons-image-rotate action action-rotate"></span>
													<span class="dashicons dashicons-admin-settings action action-edit"></span>
												</div>
											</div>
										</div>
										<!-- === Watermark Text Placeholder === -->
										<div class="watermark-text-placeholder-none ui-draggable ui-resizable ui-draggable-handle d-none">
											<div class="wrapper position-relative">
												<div class="watermark-text-wrapper watermark-placeholder-wrapper">
													<div spellcheck="false" contenteditable="true" class="overflow-hidden watermark-text-textarea w-100 h-100"></div>
												</div>
												<div class="actions">
													<span class="dashicons dashicons-dismiss action action-remove"></span>
													<span class="watermark-placeholder-rotate-handle dashicons dashicons-image-rotate action action-rotate"></span>
													<span class="dashicons dashicons-admin-settings action action-edit"></span>
												</div>
											</div>
										</div>
										<div class="repeated-clones-wrapper position-absolute">

										</div>
									</div>
								</div>
							</div>
							<!-- Preview Watermarks Start section -->
							<div class="img-preview d-flex align-items-center mb-4">
								<button class="button preview-watermark-preview-btn disabled me-2" disabled="disabled"><?php esc_html_e( 'Preview Watermarks', 'watermark-images-for-wp-and-woo-grandpluginswp' ); ?></button>
								<button class="mt-1 py-0 px-2 tooltip-btn btn btn-secondary rounded-circle" type="button" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-container="body" title="<?php esc_html_e( 'This will show how the watermarks will be applied on images, It is applied on a separate image which is created temporarily for the preview', 'watermark-images-for-wp-and-woo-grandpluginswp' ); ?>" >?</button>
								<span class="spinner"></span>
							</div>
							<!-- Preview Result Section -->
							<div class="preview-result d-none">
								<div class="wrapper text-center" style="overflow: auto !important;">
									<img src="" alt="" class="border img-thubmnail preview-img" style="max-width: none !important;">
								</div>
							</div>

							<!-- Save Section -->
							<div class="save-section collapse my-5 subtitle card">
								<!-- Apply Type -->
								<div class="apply-type">
									<h5 class="mb-3">
										<?php esc_html_e( 'How to apply the watermarks', 'watermark-images-for-wp-and-woo-grandpluginswp' ); ?>
									</h5>

									<!-- Create New -->
									<div class="my-4">
										<input type="radio" value="1" id="apply-watermarks-type-add-new" name="apply-watermarks-type" class="form-check-input apply-watermarks-type">
										<label class="mb-1" for="apply-watermarks-type-add-new"><?php esc_html_e( 'Create new', 'watermark-images-for-wp-and-woo-grandpluginswp' ); ?></label>
										<small class="d-block mt-2 subtitle"><?php esc_html_e( 'Create a separate watermarked image', 'watermark-images-for-wp-and-woo-grandpluginswp' ); ?></small>
									</div>

									<!-- Image Sub-Sizes to apply on -->
									<div class="subtitle border p-3 my-4 apply-watermarks-apply-subsizes-options collapse">
										<div class="select-image-sizes-option my-3">
											<div class="heading d-flex align-items-center">
												<input type="checkbox" class="apply-watermarks-image-sizes-options-all" />
												<h5 class="image-sizes-options-new collapse"><?php esc_html_e( 'Select sizes to create', 'watermark-images-for-wp-and-woo-grandpluginswp' ); ?></h5>
												<h5 class="image-sizes-options-overwrite collapse"><?php esc_html_e( 'Select sizes to apply the watermarks on', 'watermark-images-for-wp-and-woo-grandpluginswp' ); ?></h5>
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


									<!-- Overwrite -->
									<div class="my-4 p-2 <?php echo esc_attr( $plugin_info['classes_prefix'] . '-disabled' ); ?>">
										<input type="radio" disabled class="form-check-input apply-watermarks-type">
										<label class="mb-1" for="apply-watermarks-type-add-new"><?php esc_html_e( 'Overwrite ( Pro )', 'watermark-images-for-wp-and-woo-grandpluginswp' ); ?></label>
										<small class="d-block mt-2 subtitle"><?php esc_html_e( 'Overwrite the image', 'watermark-images-for-wp-and-woo-grandpluginswp' ); ?></small>
									</div>
								</div>
								<!-- Submit -->
								<div class="step-4 mb-3 apply-watermarks-final-step collapse">
									<button class="button submit apply-watermarks-submit-btn"><?php esc_html_e( 'Apply Watermarks', 'watermark-images-for-wp-and-woo-grandpluginswp' ); ?></button>
									<span class="spinner"></span>
								</div>
							</div>

									<!-- Result Image Holder -->
							<div class="p-2 m-2 d-none img-icon-box-container"></div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<div class="review-wrapper mt-5">
		<?php $core->review_notice(); ?>
	</div>


<div role="alert" aria-live="assertive" aria-atomic="true" class="fixed-top mx-auto text-white toast <?php echo esc_attr( $plugin_info['classes_prefix'] . '-msgs-toast' ); ?>" >
	<div class="toast-header">
		<button type="button" class="btn close-toast bg-transparent me-2 m-auto border-0" data-bs-dismiss="toast" aria-label="close">
			<span class="bg-transparent dashicons dashicons-dismiss border-0"></span>
		</button>
	</div>
	<div class="toast-body">
	</div>
</div>

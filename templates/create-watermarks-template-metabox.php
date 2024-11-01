<?php
namespace GPLSCore\GPLS_PLUGIN_WMFW;

use GPLSCore\GPLS_PLUGIN_WMFW\Watermarks_Templates;

defined( 'ABSPATH' ) || exit();
?>

<div class="col-md-12 watermark-template-creator-wrapper">
	<div class="<?php echo esc_attr( Watermarks_Templates::$plugin_info['classes_prefix'] . '-upgrade-notice' ); ?>">
		<h6 class="my-2"><?php esc_html_e( 'Upgrade to ', 'watermark-images-for-wp-and-woo-grandpluginswp' ); ?><?php $core->pro_btn( '', 'Pro', 'me-2' ); ?><?php esc_html_e( 'for full features', 'watermark-images-for-wp-and-woo-grandpluginswp' ); ?>
		</h6>
	</div>
	<!-- === Create Watemark Template === -->
	<div class="create-watermark-template-container">
		<h4 class="text-muted bg-light p-3 my-4 text-small"><?php esc_html_e( 'Watermarks template is used for auto watermarking and Bulk watermarking', 'watermark-images-for-wp-and-woo-grandpluginswp' ); ?></h4>
		<!-- Preview Image select - Display -->
		<div class="image-select">
			<div class="row">
				<div class="col-md-6 select-image-btn-section">

					<h3 class="my-3"><?php esc_html_e( 'Select Preview Image', 'watermark-images-for-wp-and-woo-grandpluginswp' ); ?> <button class="py-0 px-2 tooltip-btn btn btn-secondary rounded-circle" type="button" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-container="body" title="<?php esc_html_e( 'You can select an image to try out and preview the watermarks on it, watermarks wont be applied on the preview image', 'watermark-images-for-wp-and-woo-grandpluginswp' ); ?>" >?</button></h3>
					<button data-context="select-preview-image" id="insert-media-button" class="<?php echo esc_attr( $plugin_info['classes_prefix'] . '-open-gallery-btn' ); ?> button">
						<span class="wp-media-butons-icon"></span>
						<?php esc_html_e( 'Media Gallery', 'watermark-images-for-wp-and-woo-grandpluginswp' ); ?>
					</button>
				</div>
				<div class="col-md-6 select-watermark-btn-section <?php echo esc_attr( empty( $template_preview_img_url ) ? 'd-none' : '' ); ?>">
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
						<img class="selected-preview" src="<?php echo esc_url_raw( $template_preview_img_url ); ?>" alt="" style="max-width: none !important;">
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
				<button class="button preview-watermark-preview-btn me-2 disabled" disabled="disabled"><?php esc_html_e( 'Preview Watermarks', 'watermark-images-for-wp-and-woo-grandpluginswp' ); ?></button>
				<button class="mt-1 py-0 px-2 tooltip-btn btn btn-secondary rounded-circle" type="button" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-container="body" title="<?php esc_html_e( 'This will show how the watermarks will be applied on images, It is applied on a separate image which is created temporarily for the preview', 'watermark-images-for-wp-and-woo-grandpluginswp' ); ?>" >?</button>
				<span class="spinner"></span>
			</div>
			<!-- Preview Result Section -->
			<div class="preview-result d-none">
				<div class="wrapper text-center" style="overflow: auto !important;">
					<img src="" alt="" class="border img-thubmnail preview-img " style="max-width: none !important;">
				</div>
			</div>
		</div>
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
</div>

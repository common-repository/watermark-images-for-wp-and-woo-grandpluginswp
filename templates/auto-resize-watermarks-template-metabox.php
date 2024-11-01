<?php
namespace GPLSCorePro\GPLS_PLUGIN_WMFW;

defined( 'ABSPATH' ) || exit();

?>
<div class="col-md-12 watermark-template-auto-apply-wrapper <?php echo esc_attr( $plugin_info['classes_prefix'] . '-disabled' ); ?>">
	<h1 class="display-4 border shadow-sm mt-3 p-4 d-flex align-items-center"><?php esc_html_e( 'Auto resize watermarks in ( auto / bulk ) watermarking', 'gpls-wmfw-watermark-image-for-wordpress' ); ?>
		<span class="<?php echo esc_attr( $plugin_info['classes_prefix'] . '-new-keyword' ); ?> ms-2" style="padding: 5px 10px;border-radius: 5px;background: #e17b7b;color: #FFF;"><?php esc_html_e( 'New', 'gpls-wmfw-watermark-image-for-wordpress' ); ?></span><?php $core->pro_btn(); ?>
	</h1>
	<h6 class="mt-3"><?php esc_html_e( 'Image watermarks size and text watermarks fontsize will be resized proportionally with the image size', 'gpls-wmfw-watermark-image-for-wordpress' ); ?></h6>
	<!-- Auto Apply Options -->
	<div class="auto-apply-options my-5">
		<!-- Width Reference -->
		<div class="row my-3">
			<div class="col-3 mb-2 d-flex align-items-center">
				<label class="me-1 fw-bold" for="auto-resize-img-width-ref"><?php esc_html_e( 'Reference width ', 'gpls-wmfw-watermark-image-for-wordpress' ); ?></label>
			</div>
			<div class="col-9 mb-2">
				<div class="input-wrapper">
					<input type="number" disabled id="auto-resize-img-ref" class="auto-resize-img-ref"  value="1000" >
					<span class="ms-1">px</span>
				</div>
				<span class="muted d-block my-1"><?php esc_html_e( 'This is the reference width to use watermarks at without resizing.', 'gpls-wmfw-watermark-image-for-wordpress' ); ?></span>
			</div>
		</div>
		<!-- Scale Offsets -->
		<div class="row my-3">
			<div class="col-3 mb-2 d-flex align-items-center">
				<label class="me-1 fw-bold" for="auto-resize-img-width-ref"><?php esc_html_e( 'Auto scale offsets', 'gpls-wmfw-watermark-image-for-wordpress' ); ?></label>
			</div>
			<div class="col-9 mb-2">
				<div class="input-wrapper">
					<input type="checkbox" disabled checked="checked" id="auto-resize-scale-offset" class="auto-resize-scale-offset" >
					<span class="muted ms-1 my-1"><?php esc_html_e( 'Auto scale repeat axis offset.', 'gpls-wmfw-watermark-image-for-wordpress' ); ?></span>
					<span class="muted ms-1 my-1 d-block"><?php esc_html_e( 'The repeat offset values in x and y will be scaled up/down depending on the image width relative to the reference width.', 'gpls-wmfw-watermark-image-for-wordpress' ); ?></span>
				</div>
			</div>
		</div>
		<!-- Context Select -->
		<div class="row my-3">
			<div class="col-3 mb-2 d-flex align-items-center">
				<label class="me-1 fw-bold" for="auto-resize-img-width-ref"><?php esc_html_e( 'Auto resize context', 'gpls-wmfw-watermark-image-for-wordpress' ); ?></label>
			</div>
			<div class="col-9 mb-2">
				<div class="form-check my-1 ps-0">
					<input type="checkbox" disabled checked="checked" id="auto-resize-context" class="m-0 auto-resize-context me-2" value="auto" >
					<label for="auto-apply-context"><?php esc_html_e( 'Auto watermarking', 'gpls-wmfw-watermark-image-for-wordpress' ); ?></label>
				</div>
				<div class="form-check my-1 ps-0">
					<input type="checkbox" disabled checked="checked" id="auto-resize-context" class="m-0 auto-resize-context me-2" value="bulk" >
					<label for="auto-apply-context"><?php esc_html_e( 'Bulk watermarking', 'gpls-wmfw-watermark-image-for-wordpress' ); ?></label>
				</div>
			</div>
		</div>
	</div>
</div>

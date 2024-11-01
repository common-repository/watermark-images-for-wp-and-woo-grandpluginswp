<?php
namespace GPLSCore\GPLS_PLUGIN_WMFW;

use GPLSCore\GPLS_PLUGIN_WMFW\Apply_Watermarks_Queries;

defined( 'ABSPATH' ) || exit();

$auto_apply_options = Watermarks_Templates::get_template_watermarks( $post->ID, 'auto_apply' );

?>
<div class="col-md-12 watermark-template-auto-apply-wrapper <?php echo esc_attr( $plugin_info['classes_prefix'] . '-disabled' ); ?>">
	<h1 class="display-4 border shadow-sm mt-3 p-4"><?php esc_html_e( 'Set rules to apply the watermarks template automatically on uploaded images ( Pro )', 'watermark-images-for-wp-and-woo-grandpluginswp' ); ?><?php $core->pro_btn(); ?></h1>
	<!-- Auto Apply Options -->
	<div class="auto-apply-options my-5">
		<!-- Auto Apply Status -->
		<div class="form-check">
			<input disabled type="checkbox" id="auto-apply-status" class="auto-apply-status" name="auto-apply-status" value="cpt" >
			<label for="auto-apply-status"><?php esc_html_e( 'Enable auto apply', 'watermark-images-for-wp-and-woo-grandpluginswp' ); ?></label>
		</div>
		<!-- Image Dimensions -->
		<div class="subtitle apply-img-dimensions step-2 my-5 collapse show">
			<label>
				<input disabled type="checkbox" class="auto-apply-img-dimension-status" >
				<h5 class="d-inline-block"><?php esc_html_e( 'Image Dimensions Constraint', 'watermark-images-for-wp-and-woo-grandpluginswp' ); ?></h5>
			</label>
			<p><?php esc_html_e( 'Apply the template on images that have min|max width and height.', 'watermark-images-for-wp-and-woo-grandpluginswp' ); ?></p>
			<div class="auto-apply-img-dimension subtitle row collapse show">
				<div class="col-3">
					<div class="auto-apply-img-dimension-width-container" >
						<label class="my-3"><?php esc_html_e( 'width' ); ?></label>
						<div class="auto-apply-img-dimension-width-wrapper">
							<select disabled class="auto-apply-img-dimension-width-type" >
								<option><?php esc_html_e( 'Min' ); ?>
								<option><?php esc_html_e( 'Max' ); ?>
							</select>
							<input disabled type="number" class="auto-apply-img-dimension-width">
							<?php echo esc_attr( 'px' ); ?>
						</div>
					</div>
				</div>
				<div class="col-3">
					<div class="auto-apply-img-dimension-height-container" >
						<label class="my-3"><?php esc_html_e( 'height' ); ?></label>
						<div class="auto-apply-img-dimension-height-wrapper">
							<select disabled class="auto-apply-img-dimension-height-type" data-type="img-height-type" >
								<option><?php esc_html_e( 'Min' ); ?>
								<option><?php esc_html_e( 'Max' ); ?>
							</select>
							<input disabled type="number" class="auto-apply-img-dimension-height" value="">
							<?php echo esc_attr( 'px' ); ?>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!-- Name Prefix -->
		<div class="subtitle apply-img-name-prefix step-2 my-5 collapse show">
			<h5><?php esc_html_e( 'Image name prefix', 'watermark-images-for-wp-and-woo-grandpluginswp' ); ?></h5>
			<small><?php esc_html_e( 'Apply the template on images that start with a prefix name', 'watermark-images-for-wp-and-woo-grandpluginswp' ); ?></small><small class="ms-2"><?php echo esc_html( 'Ex: ' ); ?><strong><?php echo esc_html( 'prefix-name-' ); ?></strong><?php echo esc_html( 'imagename.png' ); ?></small>
			<input disabled class="d-block regular-text my-3" type="text" placeholder="<?php esc_html_e( 'Add a prefix', 'watermark-images-for-wp-and-woo-grandpluginswp' ); ?>" >
		</div>
		<!-- Apply Context -->
		<div class="subtitle apply-context step-2 my-5 collapse show">
			<h5 class="mb-3"><?php esc_html_e( 'Upload Location', 'watermark-images-for-wp-and-woo-grandpluginswp' ); ?></h5>
			<p><?php esc_html_e( 'Select where the template should be applied on uploaded images', 'watermark-images-for-wp-and-woo-grandpluginswp' ); ?></p>
			<!-- Media Context -->
			<div class="context-select context-media my-4">
				<label class="w-100" for="auto-apply-context-media">
					<div class="row">
						<div class="col-md-3 my-2">
							<input disabled type="checkbox" class="auto-apply-context" id="auto-apply-context-media" >
							<strong class="me-5"><?php esc_html_e( 'Media' ); ?></strong>
						</div>
						<div class="col-md-9 my-2 ps-3">
						</div>
					</div>
				</label>
			</div>
			<!-- Posts Context -->
			<div class="context-select context-posts my-4">
				<label class="w-100" for="auto-apply-context-posts">
					<div class="row">
						<div class="col-md-3 my-2">
							<input disabled type="checkbox" class="auto-apply-context auto-apply-context-posts" id="auto-apply-context-posts">
							<strong class="me-4"><?php esc_html_e( 'Post Type' ); ?></strong>
						</div>
						<div class="col-md-9 my-2 ps-3">
						</div>
					</div>
				</label>
			</div>
		</div>

		<!-- Apply Type -->
		<div class="subtitle apply-type step-3 my-5 collapse show">
			<h6><?php esc_html_e( 'Apply Type', 'watermark-images-for-wp-and-woo-grandpluginswp' ); ?></h6>
			<!-- Overwrite -->
			<div class="subtitle apply-type-select apply-type-select-overwrite my-4">
				<label for="apply-type-overwrite">
					<input disabled type="radio"  class="apply-type" id="apply-type-overwrite" >
					<strong ><?php esc_html_e( 'Overwrite', 'watermark-images-for-wp-and-woo-grandpluginswp' ); ?></strong>
				</label>
				<small class="d-block mt-2 subtitle"><?php esc_html_e( 'Overwrite the original image', 'watermark-images-for-wp-and-woo-grandpluginswp' ); ?></small>

				<!-- Create Backup -->
				<div class="subtitle create-backup my-4 collapse <?php echo esc_attr( $auto_apply_options['status'] && 'overwrite' === $auto_apply_options['apply_type'] ? 'show' : '' ); ?>">
					<label for="create-backup">
						<input disabled type="checkbox" name="create_backup" class="create-backup" id="create-backup" >
						<strong><?php esc_html_e( 'Create Backup', 'watermark-images-for-wp-and-woo-grandpluginswp' ); ?></strong>
					</label>
					<small class="d-block mt-2 subtitle"><?php esc_html_e( 'Create backup of the original image', 'watermark-images-for-wp-and-woo-grandpluginswp' ); ?></small>
				</div>
			</div>
			<!-- Create New -->
			<div class="subtitle apply-type-select apply-type-select-new my-4">
				<label for="apply-type-new">
					<input disabled type="radio" class="apply-type" id="apply-type-new" >
					<strong><?php esc_html_e( 'Create New', 'watermark-images-for-wp-and-woo-grandpluginswp' ); ?></strong>
				</label>
				<small class="d-block mt-2 subtitle"><?php esc_html_e( 'Create a separate watermarked image', 'watermark-images-for-wp-and-woo-grandpluginswp' ); ?></small>

				<!-- Create in custom directory -->
				<div class="subtitle custom-dir-wrapper my-4 collapse show">
					<label for="custom-dir">
						<input disabled type="checkbox" <?php echo esc_attr( $auto_apply_options['create_backup'] ? 'checked="checked"' : '' ); ?> name="custom_dir" class="custom-dir" id="custom-dir" value="custom_dir" >
						<strong><?php esc_html_e( 'Create in a custom Directory', 'gpls-wmfw-watermark-image-for-wordpress' ); ?></strong>
					</label>
					<?php $upload_dir = wp_upload_dir(); ?>
					<?php echo esc_attr( trailingslashit( $upload_dir['basedir'] ) ); ?><input disabled type="text" name="custom_dir_path" class="custom-dir-path regular-text" id="custom-dir-path" value="" >
					<small class="d-block mt-2 subtitle"><?php esc_html_e( 'Place the watermarked image in custom directory, suitable for custom code snippets to show watermarked images with the same name from a separated directory.', 'gpls-wmfw-watermark-image-for-wordpress' ); ?></small>
				</div>

				<!-- Image Sub-Sizes to apply on -->
				<div class="subtitle border p-3 my-4 apply-subsizes-options collapse show">
					<div class="select-image-sizes-option my-3">
						<div class="heading d-flex align-items-center">
							<input disabled type="checkbox" class="apply-watermarks-image-sizes-options-all" />
							<h5 class="image-sizes-options-new"><?php esc_html_e( 'Select sub-sizes to create', 'gpls-wmfw-watermark-image-for-wordpress' ); ?></h5>
						</div>
						<div class="subtitle select-image-sizes-checkboxes">
							<?php
							$image_subsizes = wp_get_registered_image_subsizes();
							foreach ( $image_subsizes as $subsize_name => $subsize_arr ) :
								?>
							<div class="form-check my-3">
								<input disabled type="checkbox" id="apply-watermarks-image-sizes-option-<?php echo esc_attr( $subsize_name ); ?>" class="apply-watermarks-image-sizes-option" name="apply-watermarks-image-sizes-option[]" value="<?php echo esc_attr( $subsize_name ); ?>" >
								<label class="form-check-label" for="apply-watermarks-image-sizes-option-<?php echo esc_attr( $subsize_name ); ?>"><?php echo esc_html( $subsize_name . '  [ ' . $subsize_arr['width'] . ' x ' . $subsize_arr['height'] . ' ]' ); ?></label>
							</div>
							<?php endforeach; ?>
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

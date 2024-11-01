<?php

use GPLSCore\GPLS_PLUGIN_WMFW\Image_Watermark;
use GPLSCore\GPLS_PLUGIN_WMFW\Watermarks_Templates;

defined( 'ABSPATH' ) || exit();
?>
<!-- Main Settings Template -->
<div class="wrap position-relative">
	<div class="container-fluid watermark-creator-wrapper w-100 p-5">
		<div class="row">
			<h3 class="mb-5"><?php esc_html_e( 'Settings', 'watermark-images-for-wp-and-woo-grandpluginswp' ); ?></h3>
			<div id="custom-fonts-file-wrapper" class="col-12 custom-fonts card">
				<h4 class="mb-3"><?php esc_html_e( 'Custom Font files', 'watermark-images-for-wp-and-woo-grandpluginswp' ); ?></h4>
				<span class="mb-5"><?php esc_html_e( 'Upload custom font files for Text Watermarks', 'watermark-images-for-wp-and-woo-grandpluginswp' ); ?> <strong><?php echo esc_html( 'True Type Font Files ( .ttf )' ); ?> <?php esc_html_e( 'only allowed', 'watermark-images-for-wp-and-woo-grandpluginswp' ); ?></strong></span>
				<div class="input-group mb-3">
					<input accept=".ttf" name="custom_font_file" class="form-control custom-fonts-file-input" type="file" id="<?php echo esc_attr( $plugin_info['name'] . '-custom-font-file-input' ); ?>">
				</div>
				<div class="notice custom-font-file-notice notice-error d-none">
					<div class="notice-message p-1"></div>
				</div>
				<div class="card subtitle collapse show fonts-list">
					<button class="button button-primary available-fonts-toggle "><?php esc_html_e( 'Available Fonts Files', 'watermark-images-for-wp-and-woo-grandpluginswp' ); ?><span class="ms-2 pt-1 dashicons dashicons-arrow-down"></span></button>
					<ul class="mt-3 available-fonts list-group collapse">
						<?php
						$fonts = Image_Watermark::get_available_fonts( true );
						foreach ( $fonts as $font ) :
						?>
						<li class="row font-item list-group-item d-flex flex-row">
							<div class="col-6 font-title"><?php echo esc_html( $font['title'] ); ?></div>
							<div class="col-6 font-file-name"><?php echo esc_html( $font['name'] ); ?></div>
						</li>
						<?php
						endforeach;
						?>
					</ul>
				</div>
			</div>
		</div>
	</div>
	<div class="plugins-wrapper mt-5">
	<?php $core->plugins_sidebar(); ?>
	</div>
	<div class="main-loader loader w-100 h-100 position-absolute top-0 left-0  d-none">
		<div class="text-white wrapper text-center position-absolute d-block w-100 ">
			<img class="loader-icon position-fixed" src="<?php echo esc_url_raw( admin_url( 'images/spinner-2x.gif' ) ); ?>"  />
		</div>
		<div class="overlay position-absolute d-block w-100 h-100"></div>
	</div>

</div>

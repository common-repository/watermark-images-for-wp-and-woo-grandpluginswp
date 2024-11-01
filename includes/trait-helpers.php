<?php

namespace GPLSCore\GPLS_PLUGIN_WMFW;

/**
 * Helper Traits.
 */
trait Helpers {


	/**
	 * Loader HTML Code.
	 *
	 * @return void
	 */
	public static function loader_html() {
		?>
		<div class="loader w-100 h-100 position-absolute">
			<div class="text-white wrapper text-center position-absolute d-block w-100 ">
				<img src="<?php echo esc_url_raw( admin_url( 'images/spinner-2x.gif' ) ); ?>"  />
			</div>
			<div class="overlay position-absolute d-block w-100 h-100"></div>
		</div>
		<?php
	}
}

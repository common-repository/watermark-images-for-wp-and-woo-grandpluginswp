<?php
namespace GPLSCore\GPLS_PLUGIN_WMFW\Utils;

defined( 'ABSPATH' ) || exit;

/**
 * Watermark utils Trait.
 * 
 */
trait WatermarkUtilsTrait {

	/**
	 * Get Dynamic Keys.
	 * 
	 * @return array
	 */
	protected static function get_dynamic_keys( $full_list = false ) {
		$new_customer = new \WP_User();
		$user_data    = array(
			'first_name_last_name',
			'first_name',
			'user_firstname',
			'last_name',
			'user_lastname',
			'user_login',
			'user_pass',
			'user_nicename',
			'user_email',
			'user_url',
			'user_registered',
			'user_activation_key',
			'user_status',
			'user_level',
			'display_name',
			'locale',
			'nickname',
			'description',
			'user_description',
		);

		$dynamic_keys['wp_user'] = array(
			'title'  => esc_html__( 'User data', 'gpls-wmfw-watermark-image-for-wordpress' ),
			'prefix' => 'wp_user_',
			'keys'   => $user_data,
		);

		if ( self::is_plugin_active( 'woocommerce/woocommerce.php' ) ) {

			// Woo Order Keys.

			$new_order                                   = new \WC_Order();
			$order_data                                  = $new_order->get_data();
			$order_data['billing_first_name_last_name']  = '';
			$order_data['shipping_first_name_last_name'] = '';
			$order_data_formatted                        = array();

			foreach ( $order_data as $data_key => $data_value ) {
				if ( is_array( $data_value ) ) {
					foreach ( $data_value as $data_subkey => $data_subvalue ) {
						$order_data_formatted[] = $data_key . '_' . $data_subkey;
					}
				} else {
					$order_data_formatted[] = $data_key;
				}
			}
			$dynamic_keys['woo_order'] = array(
				'title'  => esc_html__( 'Woo order data', 'gpls-wmfw-watermark-image-for-wordpress' ),
				'prefix' => 'woo_order_',
				'keys'   => $order_data_formatted,
			);

			// Woo Customer Keys.

			$new_customer                          = new \WC_Customer();
			$customer_data                         = $new_customer->get_data();
			$customer_data['first_name_last_name'] = '';
			$customer_data_formatted               = array();

			foreach ( $customer_data as $data_key => $data_value ) {
				if ( is_array( $data_value ) ) {
					foreach ( $data_value as $data_subkey => $data_subvalue ) {
						$customer_data_formatted[] = $data_key . '_' . $data_subkey;
					}
				} else {
					$customer_data_formatted[] = $data_key;
				}
			}
			$dynamic_keys['woo_customer'] = array(
				'title'  => esc_html__( 'Woo Customer data', 'gpls-wmfw-watermark-image-for-wordpress' ),
				'prefix' => 'woo_customer_',
				'keys'   => $customer_data_formatted,
			);
		}

		if ( $full_list ) {
			$full_list = array();
			foreach ( $dynamic_keys as $group_key => $group_arr ) {
				foreach ( $group_arr['keys'] as $group_field_key ) {
					$full_list[] = '{{' . $group_arr['prefix'] . $group_field_key . '}}';
				}
			}
			return $full_list;
		}

		return $dynamic_keys;
	}

    /**
	 * Is Plugin Active.
	 *
	 * @param string $plugin_basename
	 * @return boolean
	 */
	public static function is_plugin_active( $plugin_basename ) {
		require_once \ABSPATH . 'wp-admin/includes/plugin.php';
		return is_plugin_active( $plugin_basename );
	}
}
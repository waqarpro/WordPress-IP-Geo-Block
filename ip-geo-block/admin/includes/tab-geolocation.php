<?php
require_once( IP_GEO_BLOCK_PATH . 'classes/class-ip-geo-block-apis.php' );

class IP_Geo_Block_Admin_Tab {

	public static function tab_setup( $context ) {
		$option_slug = IP_Geo_Block::PLUGIN_NAME;
		$option_name = IP_Geo_Block::OPTION_NAME;
		$options = IP_Geo_Block::get_option();

		register_setting(
			$option_slug,
			$option_name
		);

		/*----------------------------------------*
		 * Geolocation
		 *----------------------------------------*/
		$section = IP_Geo_Block::PLUGIN_NAME . '-search';
		add_settings_section(
			$section,
			__( 'Search IP address geolocation', 'ip-geo-block' ),
			NULL,
			$option_slug
		);

		// make providers list
		$list = array();
		$providers = IP_Geo_Block_Provider::get_providers( 'key' );

		foreach ( $providers as $provider => $key ) {
			if ( ! is_string( $key ) ||
				 ! empty( $options['providers'][ $provider ] ) ) {
				$list += array( $provider => $provider );
			}
		}

		$field = 'service';
		$provider = array_keys( $providers );
		add_settings_field(
			$option_name.'_'.$field,
			__( 'Geolocation service', 'ip-geo-block' ),
			array( $context, 'callback_field' ),
			$option_slug,
			$section,
			array(
				'type' => 'select',
				'option' => $option_name,
				'field' => $field,
				'value' => $provider[0],
				'list' => $list,
			)
		);

		$field = 'ip_address';
		add_settings_field(
			$option_name.'_'.$field,
			__( 'IP address', 'ip-geo-block' ),
			array( $context, 'callback_field' ),
			$option_slug,
			$section,
			array(
				'type' => 'text',
				'option' => $option_name,
				'field' => $field,
				'value' => '',
			)
		);

		$field = 'get_location';
		add_settings_field(
			$option_name.'_'.$field,
			__( 'Find geolocation', 'ip-geo-block' ),
			array( $context, 'callback_field' ),
			$option_slug,
			$section,
			array(
				'type' => 'button',
				'option' => $option_name,
				'field' => $field,
				'value' => __( 'Search now', 'ip-geo-block' ),
				'after' => '<div id="ip-geo-block-loading"></div>',
			)
		);
	}

}
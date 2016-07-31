<?php
/**
 * IP Geo Block
 *
 * A WordPress plugin that blocks undesired access based on geolocation of IP address.
 *
 * @package   IP_Geo_Block
 * @author    tokkonopapa <tokkonopapa@yahoo.com>
 * @license   GPL-2.0+
 * @link      http://www.ipgeoblock.com/
 * @copyright 2013-2016 tokkonopapa
 *
 * Plugin Name:       IP Geo Block MU
 * Plugin URI:        http://wordpress.org/plugins/ip-geo-block/
 * Description:       It blocks any spams, login attempts and malicious access to the admin area posted from outside your nation, and also prevents zero-day exploit.
 * Version:           3.0.0a
 * Author:            tokkonopapa
 * Author URI:        http://www.ipgeoblock.com/
 * Text Domain:       ip-geo-block
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/*----------------------------------------------------------------------------*
 * Global definition
 *----------------------------------------------------------------------------*/
define( 'IP_GEO_BLOCK_MU_PLUGINS', TRUE );

/*----------------------------------------------------------------------------*
 * Detect plugin. For use on Front End only.
 *----------------------------------------------------------------------------*/
include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

$plugin_name = 'ip-geo-block/ip-geo-block.php';

if ( is_plugin_active( $plugin_name ) || is_plugin_active_for_network( $plugin_name ) ) {
	// Load plugin class
	include_once( WP_PLUGIN_DIR . '/' . $plugin_name );

	// Remove instanciation
	remove_action( 'plugins_loaded', array( 'IP_Geo_Block', 'run' ) );

	// Instanciate immediately
	IP_Geo_Block::get_instance();
}

unset( $plugin_name );

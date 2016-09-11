<?php
/**
 * Plugin Name: IP Geo Block advanced cache module
 *
 * @package   IP_Geo_Block
 * @author    tokkonopapa <tokkonopapa@yahoo.com>
 * @license   GPL-2.0+
 * @link      http://www.ipgeoblock.com/
 * @copyright 2016 tokkonopapa
 */
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Abort if WordPress is upgrading
 *
 */
if ( defined( 'WP_INSTALLING' ) && WP_INSTALLING ) {
	return;
}

/**
 * Fake identifier for caching plugins
 *
 * WP SUPER CACHE 1.2
 * W3_PgCache & w3_instance
 * Module Name: advanced-cache by yasakani cache
 */

/**
 * Load main class of IP Geo Block.
 *
 */
define( 'IP_GEO_BLOCK_ADVANCED_CACHE', TRUE );

include WP_CONTENT_DIR . '/plugins/ip-geo-block/ip-geo-block.php';

// Remove instanciation
//remove_action( 'plugins_loaded', array( 'IP_Geo_Block', 'run' ) );

// Instanciate immediately
//IP_Geo_Block::get_instance();

/**
 * Continue to execute the original caching plugin.
 *
 */
if ( file_exists( WP_CONTENT_DIR . '/advanced-cache-2nd.php' ) )
	include WP_CONTENT_DIR . '/advanced-cache-2nd.php';

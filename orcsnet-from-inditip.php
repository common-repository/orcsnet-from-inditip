<?php
/*
 * Plugin Name: ORCSNET from IndiTip
 * Version: 1.0.12
 * Plugin URI: https://wordpress.org/plugins/orcsnet-from-inditip/
 * Description: ORCSNET plugin for WordPress
 * Author: IndiTip
 * Author URI: https://orcsnet.com/
 * Requires at least: 4.0
 * Tested up to: 5.1.1
 *
 * Text Domain: orcsnet-from-inditip
 * Domain Path: /lang/
 *
 * @package WordPress
 * @author Rohit Chatterjee
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// Load plugin class files
require_once( 'includes/class-orcsnet-from-inditip.php' );
require_once( 'includes/class-orcsnet-from-inditip-settings.php' );

// Load plugin libraries
require_once( 'includes/lib/class-orcsnet-from-inditip-admin-api.php' );
require_once( 'includes/lib/class-orcsnet-from-inditip-post-type.php' );
require_once( 'includes/lib/class-orcsnet-from-inditip-taxonomy.php' );

/**
 * Returns the main instance of OrcsNet_from_IndiTip to prevent the need to use globals.
 *
 * @since  1.0.0
 * @return object OrcsNet_from_IndiTip
 */
function OrcsNet_from_IndiTip () {
	$instance = OrcsNet_from_IndiTip::instance( __FILE__, '1.0.12' );

	if ( is_null( $instance->settings ) ) {
		$instance->settings = OrcsNet_from_IndiTip_Settings::instance( $instance );
	}

	return $instance;
}

OrcsNet_from_IndiTip();

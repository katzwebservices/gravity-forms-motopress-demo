<?php
/*
* Plugin Name: MotoPress Gravity Forms
* Plugin URI: https://gravityview.co
* Description: Use Gravity Forms to register sites instead of MotoPress Demo Builders' built-in registration.
* Version: 1.0
* Author: GravityView
* Author URI: https://gravityview.co
* License: GPLv2 or later
* Text Domain: gf-motopress
* Domain Path: /languages
* Network: True
*/

define( 'GF_MOTOPRESS_DEMO_VERSION', '0.1' );

add_action( 'gform_loaded', 'register_gf_motopress_demo' );

function register_gf_motopress_demo() {

	if ( ! class_exists( 'GFForms' ) ) {
		return;
	}

	if ( ! class_exists( 'motopress_demo\classes\Shortcodes' ) ) {
		return;
	}

	require_once plugin_dir_path( __FILE__ ) . 'class-gf-motopress-demo.php';

	GFAddon::register( 'GF_MotoPress_Demo' );
}
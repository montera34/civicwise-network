<?php
/*
Plugin Name: Civicwise network extra configurations
Description: This plugin adds some common functionalities and configurations to Civicwise sites.
Version: 0.2
Author: Montera34
Author URI: https://montera34.com
License: GPLv3
Text Domain: cwnet
Domain Path: /lang/
 */

// LOAD CONSTANTS
// TODO: to include in plugin config page in dashboard
require_once("config.php");
add_action('init','cwnet_plugin_vars');

// LOAD PLUGIN TEXT DOMAIN
// FOR STRING TRANSLATIONS
add_action( 'plugins_loaded', 'cwnet_load_textdomain' );
function cwnet_load_textdomain() {
	load_plugin_textdomain( 'cwnet', false, plugin_basename( dirname( __FILE__ ) ) . '/lang/' ); 
}


function cwnet_enqueue_scripts() {
	wp_enqueue_script( 'multiple-select', plugins_url( '/js/multiple-select.min.js', __FILE__ ), array('jquery-core'), NULL, true );
//	wp_enqueue_script( 'popper', 'https://unpkg.com/@popperjs/core@^2.0.0', NULL, NULL, true );
	wp_enqueue_script( 'popper', 'https://unpkg.com/@popperjs/core@2/dist/umd/popper.min.js', NULL, NULL, true );
	wp_enqueue_script( 'tippy', 'https://unpkg.com/tippy.js@6/dist/tippy-bundle.umd.js', array('popper'), NULL, true );
	wp_enqueue_script( 'cwnet', plugins_url( '/js/cwnet.js' , __FILE__ ), array('multiple-select','tippy'), NULL, true );
}
add_action( 'wp_enqueue_scripts', 'cwnet_enqueue_scripts',99 );

function cwnet_enqueue_styles() {
	wp_enqueue_style('cwnet',  plugins_url( '/css/cwnet.css', __FILE__ ), array(), NULL);
	wp_enqueue_style('multiple-select',  plugins_url( '/css/multiple-select.min.css', __FILE__ ), NULL, NULL);
	wp_enqueue_style('tippy', 'https://unpkg.com/tippy.js@6/themes/material.css', NULL, NULL);
}
add_action( 'wp_footer', 'cwnet_enqueue_styles' );

// include user registration functions
require_once("inc/user-signup.php");

// include user registration functions
require_once("inc/user.php");

// include template tags functions
require_once("inc/templates-tags.php");

// include gform functions
require_once("inc/gforms.php");

?>

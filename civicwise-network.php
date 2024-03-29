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
	
//	wp_enqueue_script( 'popper', 'https://unpkg.com/@popperjs/core@^2.0.0', NULL, NULL, true );
	wp_enqueue_script( 'popper', 'https://unpkg.com/@popperjs/core@2/dist/umd/popper.min.js', NULL, NULL, true );
	wp_enqueue_script( 'tippy', 'https://unpkg.com/tippy.js@6/dist/tippy-bundle.umd.js', array('popper'), NULL, true );
	wp_enqueue_script( 'd3', plugins_url( '/js/d3.min.js', __FILE__ ), array(), null, true );
	wp_enqueue_script( 'topojson', plugins_url( '/js/topojson.min.js', __FILE__ ), array('d3'), null, true );
	wp_enqueue_script( 'multiple-select', plugins_url( '/js/multiple-select.min.js', __FILE__ ), array('jquery-core'), NULL, true );
	wp_enqueue_script( 'cwnet', plugins_url( '/js/cwnet.js' , __FILE__ ), array('multiple-select'), NULL, true );

}
add_action( 'wp_enqueue_scripts', 'cwnet_enqueue_scripts',99 );

function cwnet_enqueue_styles() {
	wp_enqueue_style('fontello',  plugins_url( '/css/fonts.css', __FILE__ ), NULL, NULL);
	wp_enqueue_style('cwnet',  plugins_url( '/css/cwnet.css', __FILE__ ), array('fontello'), NULL);
	wp_enqueue_style('multiple-select',  plugins_url( '/css/multiple-select.min.css', __FILE__ ), NULL, NULL);
	wp_enqueue_style('tippy', 'https://unpkg.com/tippy.js@6/themes/material.css', NULL, NULL);
}
add_action( 'wp_footer', 'cwnet_enqueue_styles' );

function cwnet_slugify($urlString) {
	$search = array('Ș', 'Ț', 'ş', 'ţ', 'Ş', 'Ţ', 'ș', 'ț', 'î', 'â', 'ă', 'Î', ' ', 'Ă', 'ë', 'Ë');
	$replace = array('s', 't', 's', 't', 's', 't', 's', 't', 'i', 'a', 'a', 'i', 'a', 'a', 'e', 'E');
	$str = str_replace($search, $replace, strtolower(trim($urlString)));
	$str = preg_replace('/[^\w\d\-\ ]/', '', $str);
	$str = str_replace(' ', '-', $str);
	$str = preg_replace('/-{2,}/', '-', $str);
	return $str;
}

// include functions to import content
require_once("inc/import.php");

// include wordpress functions filters and redefinitions
require_once("inc/wordpress-redefinitions.php");

// include user registration functions
require_once("inc/user-signup.php");

// custom Rest API endpoints
include_once('inc/rest-api.php');

// include user registration functions
require_once("inc/user.php");

// include template tags functions
require_once("inc/templates-tags.php");

// include gform functions
require_once("inc/gforms.php");

// add_action('init','cwnet_insert_countries',999);
?>

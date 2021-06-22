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

// CONSTANTS
// TODO: to include in plugin config page in dashboard
require_once("config.php");

// LOAD PLUGIN TEXT DOMAIN
// FOR STRING TRANSLATIONS
add_action( 'plugins_loaded', 'cwnet_load_textdomain' );
function cwnet_load_textdomain() {
	load_plugin_textdomain( 'cwnet', false, plugin_basename( dirname( __FILE__ ) ) . '/lang/' ); 
}

// include user registration functions
require_once("inc/user-signup.php");

// include user registration functions
require_once("inc/user.php");

?>

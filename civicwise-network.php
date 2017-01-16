<?php
/*
Plugin Name: Civicwise network extra configurations
Description: This plugin adds some common functionalities and configurations to Civicwise sites.
Version: 0.1
Author: montera34
Author URI: https://montera34.com
License: GPLv3
 */

// unfilter admin and editor roles to allow them to include HTML tags in content when activating theme
add_action( 'init', 'cwnet_kses_init', 11 );
add_action( 'set_current_user', 'cwnet_kses_init', 11 );
register_activation_hook(__FILE__,'cwnet_unfilter_roles' );

// refilter admin and editor roles when deactivating theme
register_deactivation_hook(__FILE__,'cwnet_refilter_roles' );

// remove KSES filters for admins and editors
function cwnet_kses_init() {
 	if ( current_user_can( 'edit_others_posts' ) )
		kses_remove_filters();
}

// add unfiltered_html capability for admins and editors
function cwnet_unfilter_roles() {
	// Makes sure $wp_roles is initialized
	get_role( 'administrator' );

	global $wp_roles;
	// Dont use get_role() wrapper, it doesn't work as a one off.
	// (get_role does not properly return as reference)
	$wp_roles->role_objects['administrator']->add_cap( 'unfiltered_html' );
	$wp_roles->role_objects['editor']->add_cap( 'unfiltered_html' );
}

?>

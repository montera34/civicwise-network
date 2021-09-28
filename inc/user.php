<?php
/**
 * User system customization
 *
 * @package cwnet
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Checks if user is staff as defined in config.php
 *
 * @param user object. default current user
 * @link https://developer.wordpress.org/reference/functions/current_user_can/
 * @return true on success or false 
 */
function cwnet_is_user_staff($user) {

	if ( ! is_user_logged_in() )
		return false;

	if ( empty($user) )
		$user = wp_get_current_user();	

	$us_allowed = CWNET_U_STAFF;
	if ( array_intersect( $us_allowed, $user->roles ) )
		return true;

	return false;

}

/**
 * Hides admin bar if user is not staff as defined in config.php
 *
 * @param user object. default current user
 * @link https://developer.wordpress.org/reference/functions/current_user_can/
 * @return null
 */
function cwnet_hide_admin_bar($user) {

	if ( ! cwnet_is_user_staff($user) )
		add_filter( 'show_admin_bar', '__return_false' );

	return;
}
add_action('init','cwnet_hide_admin_bar');

function cwnet_dashboard_access($user) {
	
	if ( get_current_blog_id() != '1' )
		return;

	if ( is_admin() && ! cwnet_is_user_staff($user) && ! ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
		$p = get_page_by_path(CWNET_P_PROFILE);
		$perma = get_permalink($p->ID);
		wp_redirect( $perma );
		exit;
	}
}
add_action( 'init', 'cwnet_dashboard_access' );

/**
 *
 * Redirect to login page
 * if user is not logged in in 'must be logged in' pages
 *
 */
function cwnet_redirect() {

	if ( get_current_blog_id() != '1' )
		return;

	if ( !is_page(CWNET_P_PROFILE) && !is_page(CWNET_P_MESSAGE) )
		return;

	if ( !is_user_logged_in() )
		cwnet_go_out();

}
add_action('get_header', 'cwnet_redirect');

/**
 *
 * Redirect to login page
 * if user is not logged in in
 *
 */
function cwnet_go_out() {
	global $post;
	ob_start();
	$u_current = esc_url_raw( get_permalink($post->ID) );
	wp_safe_redirect( wp_login_url($u_current) );
	exit;
}

/**
 *
 * Log in / Sign up form style
 *
 */
function cwnet_user_form_style() {

	wp_enqueue_style('cwnet-login', get_stylesheet_directory_uri() . '/css/login.css', false );
	
	
}
//add_action( 'login_enqueue_scripts', 'cwnet_user_form_style', 10 );

// USER CAPABILITIES
////

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


// USER PROFILE
////

/**
 * Removes the leftover 'Visual Editor', 'Keyboard Shortcuts' and 'Toolbar' options.
 */
if ( ! function_exists( 'cwnet_remove_personal_options' ) ) {
	
	function cwnet_remove_personal_options( $subject ) {
		echo "\n" . '<script type="text/javascript">jQuery(document).ready(function($) { $(\'form#your-profile > h3:first\').hide(); $(\'form#your-profile > table:first\').hide(); $(\'form#your-profile\').show(); });</script>' . "\n";
	}

}
add_action( 'show_user_profile', 'cwnet_remove_personal_options',10,1 );


// ADD USERS TO CW SITES
////

function cwnet_user_sites_update( $user_id ) {
	$sites = array('1');
	$role = 'subscriber';
	// global $current_user;
	// $user_circle = get_user_meta($current_user->ID,'user_circle',true);
	// $user_circle = $_POST['user_circle'];
	// if ( array_key_exists('ID',$user_circle) && $user_circle['ID'] != '' ) {
	//	$circle_site = get_post_meta($user_circle['ID'],'_circle_site',true);
	//	if ( array_key_exists('blog_id', $circle_site) && $circle_site['blog_id'] != '' )
	//		$sites[] = $circle_site['blog_id'];
	// }
	foreach ( $sites as $s ) {
//		if ( ! is_user_member_of_blog( $user_id, $s ) )
			add_user_to_blog( $s, $user_id, $role );
	}
	return;
}
// add_action('personal_options_update','cwnet_user_sites_update');
// add_action('user_register','cwnet_user_sites_update',10,1);
// add_action('wpmu_activate_user','cwnet_user_sites_update',10,1);

?>

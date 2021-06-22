<?php

// theme magic vars
function cwnet_theme_vars() {

	if (!defined('CWNET_BLOGNAME'))
	    define('CWNET_BLOGNAME', get_bloginfo('name'));

	if (!defined('CWNET_BLOGDESC'))
	    define('CWNET_BLOGDESC', get_bloginfo('description','display'));

	if (!defined('CWNET_BLOGURL'))
	    define('CWNET_BLOGURL', esc_url( home_url( '/' ) ));
	
	if (!defined('CWNET_AJAX'))
	    define('CWNET_AJAX', admin_url( 'admin-ajax.php' , __FILE__));

	// define environment: development or production
	// possible values : dev or prod
	if (!defined('CWNET_ENV'))
	    define('CWNET_ENV', 'dev' );

	// USER SYSTEM VARS
	if (!defined('CWNET_U_STAFF'))
	    define('CWNET_U_STAFF', array() );
	
	// POST TYPES
	// projects
	if (!defined('CWNET_PT_PJ'))
	    define('CWNET_PT_PJ', '' );
	if (!defined('CWNET_PT_PJ_PFX'))
	    define('CWNET_PT_PJ_PFX', '' );

	// circles
	if (!defined('CWNET_PT_CL'))
	    define('CWNET_PT_CL', '' );
	if (!defined('CWNET_PT_CL_PFX'))
	    define('CWNET_PT_CL_PFX', '' );

	// FAQs
	if (!defined('CWNET_PT_FQ'))
	    define('CWNET_PT_FQ', '' );
	if (!defined('CWNET_PT_FQ_PFX'))
	    define('CWNET_PT_FQ_PFX', '' );

	// people
	if (!defined('CWNET_PT_P'))
	    define('CWNET_PT_P', '' );
	if (!defined('CWNET_PT_P_PFX'))
	    define('CWNET_PT_P_PFX', '' );

	// block
	if (!defined('CWNET_PT_BK'))
	    define('CWNET_PT_BK', '' );
	if (!defined('CWNET_PT_BK_PFX'))
	    define('CWNET_PT_BK_PFX', '' );

	// TAXONOMIES
	// status
	if (!defined('CWNET_TX_ST'))
	    define('CWNET_TX_ST', '' );

	// location
	if (!defined('CWNET_TX_LC'))
	    define('CWNET_TX_LC', '' );

	// country
	if (!defined('CWNET_TX_CO'))
	    define('CWNET_TX_CO', '' );

	// tags
	if (!defined('CWNET_TX_TG'))
	    define('CWNET_TX_TG', '' );

	// HEADER METAS
	if (!defined('CWNET_META_IMG'))
	    define('CWNET_META_IMG', '' );

	// PAGES
	// profile page
	if (!defined('CWNET_P_PROFILE'))
		define('CWNET_P_PROFILE','');
	
}

?>

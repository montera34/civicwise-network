<?php
/**
 * Filters for wordpress functions
 *
 * @package mestura
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 *
 * Add mestura theme query vars
 *
 */
function cwnet_query_vars( $vars ) {
	$vars[] = "to";
	$vars[] = "action";
	$vars[] = "horror";
	return $vars;
}
add_filter( 'query_vars', 'cwnet_query_vars' );

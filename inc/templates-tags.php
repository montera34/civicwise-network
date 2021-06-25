<?php
/**
 * Templates tags
 *
 * @package cwnet
 */


// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

// render user profile edit form
function cwnet_get_u_edit_form() {

	$u = wp_get_current_user();
	if ( 0 == $u->ID )
		return;

	$fields = array();

	$txs_m = array(
		CWNET_TX_IN,
	);
	foreach ( $txs_m as $tx ) {
		$u_ts = get_user_meta($u->ID,'user_'.$tx);
		if ( !empty($u_ts) ) {
			$inputs = wp_list_pluck($u_ts,'term_id');
			$fields[$tx] = $inputs;
		}
	}
	$u_cls = get_user_meta($u->ID,'user_'.CWNET_PT_CL);
	if ( !empty($u_cls) ) {
		$inputs = wp_list_pluck($u_cls,'ID');
		$fields[CWNET_PT_CL] = $inputs;
	}
	

	$txs = array(
		CWNET_TX_CO,
		CWNET_TX_LC,
	);
	foreach ( $txs as $tx ) {
		$ts = get_user_meta($u->ID,'user_'.$tx, true);
		$fields[$tx] = $ts;
	}

	$form_out = gravity_form( '2', false, false, false, $fields, false);
	
	return $form_out;
}
?>

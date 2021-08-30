<?php
/**
 * Templates tags
 *
 * @package cw
 */


// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 *
 *  render user profile edit form
 *
 */
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

/**
 *
 * Render users mosaic
 *
 */
function cwnet_wisers_mosaic() {
	
	$args = array(
		'role' => 'Subscriber',
		'number' => '-1',
		'count_total' => false,
		'fields' => array('ID'),
		'meta_query' => array(
			array(
				'key' => 'user_in_map',
				'value' => 1,
			)

		)
	);
	$us = get_users($args);

	$us_out = '<div class="mosac">';
	
	if ( !empty($us) ) {
		shuffle($us);
		$count = 0;
		foreach ( $us as $u ) {
			$count++;
			$m = get_userdata($u->ID);
	
			// name
			$u_name = trim($m->first_name.' '.$m->last_name);
	
			// bio
			$u_bio = trim($m->description);
			$u_bio_out = ( !empty($u_bio) ) ? '<div class="mosac-text mosac-bio">'.$u_bio.'</div>' : '';
	
			// organization
			// $u_org_out = ( !empty($m->_u_org) ) ? '<dt>'.__('Organisation','hcanarias').'</dt><dd>'.$m->_u_org.'</dd>' : '';
	
			// location
			$u_loc = array();
			$u_loc_class = '';
			foreach ( array(CWNET_TX_LC,CWNET_TX_CO) as $tx) {
				$ts = get_user_meta( $u->ID, 'user_'.$tx,true );
				if ( empty($ts) )
					continue;
				array_push($u_loc,$ts['name']);
				$u_loc_class .= ' '.$ts['slug'];
	
			}
			$u_loc_out = ( !empty($u_loc) ) ? '<div class="mosac-list mosac-location"><i class="fa fa-map-marker" aria-hidden="true"></i><span>'.implode(', ',$u_loc).'</span></div>' : '';
	
			// interests
			$u_ins = get_user_meta( $u->ID, 'user_'.CWNET_TX_IN);
			if ( empty($u_ins) ) {
				$u_ins_out = '';
				$u_ins_class = '';
			}
			else {
				$u_ins_names = wp_list_pluck($u_ins,'name');
				$u_ins_slugs = wp_list_pluck($u_ins,'slug');
				$u_ins_out = '<div class="mosac-list mosac-interest"><i class="fa fa-heart" aria-hidden="true"></i><span>'.implode(', ',$u_ins_names).'</span></div>';
				$u_ins_class = ' '.implode(' ',$u_ins_slugs);
			}
	
			// connector
			$u_con_tt = __('This person is a Civicwise connector. You can contact her or him to ask anything in relation with Civicwise. ','cwnet');
			$u_con_out = ( !empty($m->user_connector) ) ? '<span class="mosac-connector" data-tippy-content="'.$u_con_tt.'"><i class="fa fa-hand-peace-o"></i></span>' : '';

			// circles
			$u_cls = get_user_meta( $u->ID, 'user_'.CWNET_PT_CL);
			if ( empty($u_cls) ) {
				$u_cls_out = '';
				$u_cls_class = '';
			}
			else {
				$u_cls_names = wp_list_pluck($u_cls,'post_title');
				$u_cls_slugs = wp_list_pluck($u_cls,'post_name');
				$u_cls_out = '<div class="mosac-list mosac-circles"><i class="fa fa-circle-o" aria-hidden="true"></i><span>'.implode(', ',$u_cls_names).'</span></div>';
				$u_cls_class = ' '.implode(' ',$u_cls_slugs);
			}
	
			// contact btn
			$u_contact_url = get_permalink(CWNET_P_MESSAGE).'?to='.$u->ID;
			$u_contact_out = ' <a class="contact-btn" href="'.$u_contact_url.'" title="'.__('Send a message to this wiser','cw').'"><i class="fa fa-envelope" aria-hidden="true"></i></a>';
	
			// social networks
			$u_nets = array();
			$nets_data = array(
				'instagram' => 'fa fa-instagram',
				'twitter' => 'fa fa-twitter',
				'facebook' => 'fa fa-facebook-square',
				'linkedin' => 'fa fa-linkedin',
				'other' => 'fa fa-plus'
			);
			if ( !empty($m->user_url) )
				$u_nets[] = '<a class="mosac-link" href="'.$m->user_url.'" target="_blank"><i class="fa fa-link" aria-hidden="true"></i></a>';
	
			foreach ( $nets_data as $f => $i ) {
				$url = get_user_meta($u->ID,'user_'.$f,true);
				if ( !empty($url) )
					$u_nets[] = '<a class="mosac-link" href="'.$url.'" target="_blank"><i class="'.$i.'" aria-hidden="true"></i></a>';
			}
			$u_nets_out = ( !empty($u_nets) ) ? '<div class="mosac-links">'.implode(' ',$u_nets).$u_contact_out.'</div>' : '';
	
			$us_out .= '
			<div class="mosac-item'.$u_ins_class.$u_loc_class.$u_cls_class.'"><div class="bg-item">
				<header><h2 class="mosac-tit">'.$u_name.$u_con_out.'</h2></header>
				<div class="mosac-main">'
					.$u_bio_out
//					.$u_org_out
					.$u_loc_out
					.$u_cls_out
					.$u_ins_out
				.'</div>
				<footer class="mosac-footer">
				'.$u_nets_out.'
				</footer>
			</div></div>
			';
		}
	}
	
	$us_out .= '</div>';
	return $us_out;
}
?>

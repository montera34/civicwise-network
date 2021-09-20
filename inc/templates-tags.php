<?php
/**
 * Templates tags
 *
 * @package cwnet
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
	
	$u_langs = get_user_meta($u->ID,'user_lang');
	if ( !empty($u_langs) )
		$fields['lang'] = $u_langs;

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

		// load langs name
		$url = plugins_url('/data/lang.iso.639.1.csv', dirname(__FILE__));
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_HEADER, false);
		$data = curl_exec($curl);
		curl_close($curl);
		$csv = str_getcsv($data, "\n");
		foreach ( $csv as &$row ) {
			$row = str_getcsv($row, "|");
		}
		$langs = array_column($csv,'1','0');

		// connector tooltip
		$u_con_tt = __('This person is a CivicWise connector. That means she or he is an active member of our network that you can contact with questions or concerns, giving you everything you need to take full advantage of our organization.','cwnet');
		foreach ( $us as $u ) {
			$m = get_userdata($u->ID);
	
			// name
			$u_name = trim($m->first_name.' '.$m->last_name);
	
			// bio
			$u_bio = trim($m->description);
			$u_bio_out = ( !empty($u_bio) ) ? '<div class="mosac-text mosac-bio">'.$u_bio.'</div>' : '';
	
			// avatar
			$u_img = get_user_meta($u->ID, 'user_image',true);
			$u_img_out = ( ! empty($u_img) ) ? wp_get_attachment_image($u_img['ID'],'thumbnail','',array('class' => 'circular-square') ) : '';
	
			// languages
			$u_lgs_class = '';
			$u_lgs = get_user_meta( $u->ID, 'user_lang');
			$u_lgs_list = array();
			if ( !empty($u_lgs) ) {
				foreach ( $u_lgs as $lg ) {
					
					array_push($u_lgs_list,$langs[$lg]);
					$u_lgs_class .= ' lang-'.$lg;
				}
			}
			$u_lgs_out = ( !empty($u_lgs_list) ) ? '<div class="mosac-list mosac-lang"><i class="icon-language" aria-hidden="true"></i><span>'.implode(', ',$u_lgs_list).'</span></div>' : '';

			// location
			$u_loc = array();
			$u_loc_class = '';
			foreach ( array(CWNET_TX_LC,CWNET_TX_CO) as $tx) {
				$ts = get_user_meta( $u->ID, 'user_'.$tx,true );
				if ( empty($ts) )
					continue;
				array_push($u_loc,$ts['name']);
				$u_loc_class .= ' location-'.$ts['slug'];
	
			}
			$u_loc_out = ( !empty($u_loc) ) ? '<div class="mosac-list mosac-location"><i class="icon-location" aria-hidden="true"></i><span>'.implode(', ',$u_loc).'</span></div>' : '';
	
			// interests
			$u_ins = get_user_meta( $u->ID, 'user_'.CWNET_TX_IN);
			if ( empty($u_ins) ) {
				$u_ins_out = '';
				$u_ins_class = '';
			}
			else {
				$u_ins_names = wp_list_pluck($u_ins,'name');
				$u_ins_slugs = wp_list_pluck($u_ins,'slug');
				foreach ( $u_ins_slugs as &$i )
					$i = 'interest-'.$i;

				$u_ins_out = '<div class="mosac-list mosac-interest"><i class="icon-heart" aria-hidden="true"></i><span>'.implode(', ',$u_ins_names).'</span></div>';
				$u_ins_class = ' '.implode(' ',$u_ins_slugs);
			}
	
			// connector
			$u_con_out = ( !empty($m->user_connector) ) ? '<span class="mosac-connector" data-tippy-content="'.$u_con_tt.'"><i class="icon-cw"></i></span>' : '';
			$u_con_class = ( !empty($m->user_connector) ) ? ' u-connector' : '';

			// circles
			$u_cls = get_user_meta( $u->ID, 'user_'.CWNET_PT_CL);
			if ( empty($u_cls) ) {
				$u_cls_out = '';
				$u_cls_class = '';
			}
			else {
				$u_cls_names = wp_list_pluck($u_cls,'post_title');
				$u_cls_slugs = wp_list_pluck($u_cls,'post_name');
				foreach ( $u_cls_slugs as &$i )
					$i = 'circle-'.$i;

				$u_cls_out = '<div class="mosac-list mosac-circles"><i class="icon-circle-empty" aria-hidden="true"></i><span>'.implode(', ',$u_cls_names).'</span></div>';
				$u_cls_class = ' '.implode(' ',$u_cls_slugs);
			}
	
			// contact btn
			$u_contact_url = get_permalink(CWNET_P_MESSAGE).'?to='.$u->ID;
			$u_contact_out = ' <a class="contact-btn" href="'.$u_contact_url.'" data-tippy-content="'.__('Send a message to this wiser','cw').'"><i class="icon-mail-alt" aria-hidden="true"></i></a>';
	
			// social networks
			$u_nets = array();
			$nets_data = array(
				'instagram' => 'icon-instagram',
				'twitter' => 'icon-twitter',
				'facebook' => 'icon-facebook',
				'linkedin' => 'icon-linkedin',
				'other' => 'icon-plus'
			);
			if ( !empty($m->user_url) )
				$u_nets[] = '<a class="mosac-link" href="'.$m->user_url.'" target="_blank"><i class="icon-link" aria-hidden="true"></i></a>';
	
			foreach ( $nets_data as $f => $i ) {
				$url = get_user_meta($u->ID,'user_'.$f,true);
				if ( !empty($url) )
					$u_nets[] = '<a class="mosac-link" href="'.$url.'" target="_blank"><i class="'.$i.'" aria-hidden="true"></i></a>';
			}
			$u_nets_out = ( !empty($u_nets) ) ? '<div class="mosac-links">'.implode(' ',$u_nets).$u_contact_out.'</div>' : '<div class="mosac-links">'.$u_contact_out.'</div>';
	
			$us_out .= '
			<div class="mosac-item'.$u_ins_class.$u_loc_class.$u_cls_class.$u_con_class.'"><div class="bg-item">
				<figure class="mosac-img">'.$u_img_out.'</figure>
				<div class="mosac-text">
					<header><h2 class="mosac-tit">'.$u_name.$u_con_out.'</h2></header>
					<div class="mosac-main">'
						.$u_bio_out
						.$u_lgs_out
						.$u_loc_out
						.$u_cls_out
						.$u_ins_out
					.'</div>
					<footer class="mosac-footer">
						'.$u_nets_out.'
					</footer>
				</div>
			</div></div>
			';
		}
	}
	
	$us_out .= '</div>';
	return $us_out;
}

/**
 *
 * Render users mosaic filters block
 *
 */
function cwnet_wisers_filters() {

	// connectors
	$args = array(
		'role' => 'Subscriber',
		'number' => '-1',
		'count_total' => false,
		'fields' => array('ID'),
		'meta_query' => array(
			array(
				'key' => 'user_connector',
				'value' => 1,
			)
		)
	);
	$cons = get_users($args);
	$filter = 'connector';
	$cons_out = '
		<ul class="filter-list filter-group" data-filter-group="'.$filter.'">
			<li class="filter-group-item"><button data-filter="" class="filter-group-btn filter-btn disabled">'.__('All wisers','cwnet').'</button></li>
			<li class="filter-group-item"><button data-filter=".u-connector" class="filter-group-btn filter-btn"><i class="icon-cw"></i> '.__('Connectors','cwnet').'</button></li>
		</ul>
	';

	// interests
	$args = array(
		'taxonomy' => CWNET_TX_IN,
		'hide_empty' => false
	);
	$filter = CWNET_TX_IN;
	$ins = get_terms($args);
	$ins_out = '
		<ul class="filter-list filter-group" data-filter-group="'.$filter.'">
			<li class="filter-group-item"><button data-filter="" class="filter-group-btn filter-btn disabled"><i class="icon-heart"></i> '.__('All interests','cwnet').'</button></li>
	';
	if ( ! empty($ins) ) {
		foreach ( $ins as $in )
			$ins_out .= '<li class="filter-group-item"><button data-filter=".'.$filter.'-'.$in->slug.'" class="filter-group-btn filter-btn">'.$in->name.'</button></li>';
	
	}
	$ins_out .= '</ul>';

	// circles
//	$args = array(
//		'post_type' => CWNET_PT_CL,
//		'nopaging' => true,
//		'orderby' => 'post_title',
//		'order' => 'ASC'
//	);
//	$filter = CWNET_PT_CL;
//	$cls = get_posts($args);
//	$cls_out = '
//		<ul class="filter-list filter-group" data-filter-group="'.$filter.'">
//			<li class="filter-group-item"><button data-filter="" class="filter-group-btn filter-btn disabled"><i class="icon-circle-empty"></i> '.__('All circles','cwnet').'</button></li>
//	';
//	if ( ! empty($cls) ) {
//		foreach ( $cls as $cl )
//			$cls_out .= '<li class="filter-group-item"><button data-filter=".'.$filter.'-'.$cl->post_name.'" class="filter-group-btn filter-btn">'.$cl->post_title.'</button></li>';
//	
//	}
//	$cls_out .= '</ul>';

	$filters_out ='<div class="filters">'.$cons_out.$ins_out.'</div>';

	return $filters_out;
}

/**
 *
 * Render users map
 *
 */
function cwnet_wisers_map() {

	return '<div id="map" class="svg-content"></div>';
}
?>

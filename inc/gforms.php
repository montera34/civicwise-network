<?php
/**
 * User system customization
 *
 * @package cwnet
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

function cwnet_change_upload_path( $path_info, $form_id ) {

	$u = wp_upload_dir();
	$path_info['path'] = $u['path'];
	$path_info['url'] = $u['url'].'/';
	return $path_info;
}
add_filter( 'gform_upload_path', 'cwnet_change_upload_path', 10, 2 );

/**
 *
 * POPULATE TAXS
 *
 * @link https://resoundingechoes.net/development/pre-populating-checkboxes-gravity-forms/
 *
 */
function cwnet_gform_populate_u_taxs( $form ) {

	$u = wp_get_current_user();
	if ( 0 == $u->ID )
		return;

	foreach ( $form['fields'] as &$field ) {
 		if ( strpos( $field->cssClass, 'auto-populate' ) === false )
	    		continue;

		if ( $field->inputName == CWNET_PT_CL ) {
			$pt = $field->inputName;
			$args = array(
				'post_type' => $pt,
				'nopaging' => true,
				'orderby' => 'name',
				'order' => 'ASC',
			);
			$cls = get_posts($args);
			if ( empty($cls) )
				$field->cssClass .= ' empty';

			$choices = array();
			$dv = '';
			$i = 0;
			foreach ( $cls as $c )
				$choices[] = array( 'text' => $c->post_title, 'value' => $c->ID );
			$field->placeholder = ' ';
			$field->choices = $choices;
			$field->defaultValue = $dv;
		
		}
		else {
			$tx = $field->inputName;
			$args = array(
				'taxonomy' => $tx,
				'hide_empty' => false,
				'orderby' => 'name',
				'order' => 'ASC',
			);
			$ts = get_terms($args);
			if ( !array_key_exists(0,$ts) )
				$field->cssClass .= ' empty';
	
			$choices = array();
			$dv = '';
			$i = 0;
			foreach ( $ts as $t )
				$choices[] = array( 'text' => $t->name, 'value' => $t->term_id );
			$field->placeholder = ' ';
			$field->choices = $choices;
			$field->defaultValue = $dv;
	
		}
	}
	return $form;
}

// POPULATE PROFILE IMAGE FIELD
function cwnet_gform_populate_u_img( $form ) {

	$u = wp_get_current_user();
	if ( 0 == $u->ID )
		return;

	$u_img = get_user_meta($u->ID,'user_image');

	if ( empty($u_img) )
		return $form;

	foreach ( $form['fields'] as &$field ) {
 		if ( strpos( $field->cssClass, 'populate-img' ) === false )
			continue;

		$u_img_src = wp_get_attachment_image_src($u_img[0]['ID']);
		$field->description = '<div class="r-img"><img style="width: auto; max-height: 200px;" src="'.$u_img_src[0].'" /></div>'.__('Change this image by selecting another with the following button:','cwnet').'';
	} 
	return $form;
}

/**
 *
 * UPDATE RESOURCES FIELDS
 *
 */
function cwnet_gform_update_u_fields( $entry,$form ) {

	$u = wp_get_current_user();
	if ( 0 == $u->ID )
		return;

	$imgtype = '';
	$cls = array();
	$ins = array();
	$ins_new = array();
	$cos = array();
	$cos_new = array();
	$lcs = array();
	$lcs_new = array();

	foreach ( $form['fields'] as $f ) {
		if ( strpos( $f->cssClass, 'auto-save' ) === false )
	    		continue;

		$tx = $f->inputName;

		if ( strpos($f->cssClass, 'user-image') !== false ) {
			$imgurl = $f->get_value_export( $entry );
			if ( $imgurl != '' ) {
				preg_match('/\/\d{4}\/\d{2}\/[^\/]*$/',$imgurl,$matches);
				$wp_upload = wp_upload_dir();
				$imgname = basename($imgurl);
				$imgpath = $wp_upload['basedir'].$matches[0];
				$imgtype = wp_check_filetype($imgname, null );
			}
		}
		elseif ( $f->inputName == CWNET_PT_CL ) {
			$v = is_object( $f ) ? $f->get_value_export( $entry ) : '';
			$cls = explode( ',', $v );
		}
		elseif ( $f->inputName == CWNET_TX_IN ) {
			$v = is_object( $f ) ? $f->get_value_export( $entry ) : '';
			$ins = explode( ',', $v );
		}
		elseif ( $f->inputName == CWNET_TX_IN.'-new' ) {
			$v = $entry[$f->id];
			if ( $v == '' )
				continue;
			$ins_new = unserialize($v);
			$ins_new = array_map('trim',$ins_new);
		}
		elseif ( $f->inputName == CWNET_TX_CO ) {
			$v = is_object( $f ) ? $f->get_value_export( $entry ) : '';
			$cos = explode( ',', $v );
		}
		elseif ( $f->inputName == CWNET_TX_CO.'-new' ) {
			$v = $entry[$f->id];
			if ( $v == '' )
				continue;
			$cos_new = trim($v);
		}
		elseif ( $f->inputName == CWNET_TX_LC ) {
			$v = is_object( $f ) ? $f->get_value_export( $entry ) : '';
			$lcs = explode( ',', $v );
		}
		elseif ( $f->inputName == CWNET_TX_LC.'-new' ) {
			$v = $entry[$f->id];
			if ( $v == '' )
				continue;
			$lcs_new = trim($v);
		}
	}

	delete_user_meta($u->ID,'user_'.CWNET_PT_CL);
	if ( !empty($cls) ) {
		foreach ( $cls as $t )
			add_user_meta($u->ID,'user_'.CWNET_PT_CL,$t);
	}

	$ins_ids = array();
	delete_user_meta($u->ID,'user_'.CWNET_TX_IN);
	if ( !empty($ins_new) ) {
		foreach ( $ins_new as $t ) {
			$tn = wp_insert_term($t,CWNET_TX_IN);
			if ( ! is_wp_error($tn) ) {
				$ins_ids[] = $tn['term_id'];
			}
		}
	}
	if ( !empty($ins) ) {
		foreach ( $ins as $t )
			$ins_ids[] = $t;
	}
	if ( !empty($ins_ids) ) {
		foreach ( $ins_ids as $t )
			add_user_meta($u->ID,'user_'.CWNET_TX_IN,$t);
	}

	delete_user_meta($u->ID,'user_'.CWNET_TX_CO);
	if ( !empty($cos_new) ) {
		$tn = wp_insert_term($cos_new,CWNET_TX_CO);
		if ( ! is_wp_error($tn) )
			add_user_meta($u->ID,'user_'.CWNET_TX_CO,$tn['term_id']);
	}
	elseif ( !empty($cos) ) {
		foreach ( $cos as $t ) {
			add_user_meta($u->ID,'user_'.CWNET_TX_CO,$t);
		}
	}

	delete_user_meta($u->ID,'user_'.CWNET_TX_LC);
	if ( !empty($lcs_new) ) {
		$tn = wp_insert_term($lcs_new,CWNET_TX_LC);
		if ( ! is_wp_error($tn) )
			add_user_meta($u->ID,'user_'.CWNET_TX_LC,$tn['term_id']);
	}
	elseif ( !empty($lcs) ) {
		foreach ( $lcs as $t ) {
			add_user_meta($u->ID,'user_'.CWNET_TX_LC,$t);
		}
	}

	// Attach profile image to user
	delete_user_meta($u->ID,'user_image');
	if ( $imgtype != '' ) {
		$attach = array(
			'post_mime_type' => $imgtype['type'],
			'post_title' => sanitize_file_name($imgname),
			'post_content' => '',
			'post_status' => 'inherit'
		);
		$attach_id = wp_insert_attachment( $attach, $imgpath );
		require_once(ABSPATH . 'wp-admin/includes/image.php');
		$attach_data = wp_generate_attachment_metadata( $attach_id, $imgpath );
		wp_update_attachment_metadata( $attach_id, $attach_data );
		add_user_meta( $u->ID, 'user_image', $attach_id );
	}

	$profile = get_page_by_path(CWNET_P_PROFILE);
	$sep =  '?';
	$target = esc_url_raw( get_permalink($profile->ID).$sep.'action=edit' );

	ob_start();
	wp_safe_redirect( $target );
	exit;
}

// FORM ACTIONS

// EDIT PROFILE
// populate taxs fields
add_filter( 'gform_pre_render_2', 'cwnet_gform_populate_u_taxs' );
add_filter( 'gform_pre_validation_2', 'cwnet_gform_populate_u_taxs' );
add_filter( 'gform_pre_submission_filter_2', 'cwnet_gform_populate_u_taxs' );
// populate image fields
add_filter( 'gform_pre_render_2', 'cwnet_gform_populate_u_img' );
add_filter( 'gform_pre_validation_2', 'cwnet_gform_populate_u_img' );
add_filter( 'gform_pre_submission_filter__2', 'cwnet_gform_populate_u_img' );
// update user
add_action( 'gform_after_submission_2', 'cwnet_gform_update_u_fields', 11, 2);


?>

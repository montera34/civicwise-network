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
			foreach ( $cls as $c )
				$choices[] = array( 'text' => $c->post_title, 'value' => $c->ID );
			$field->placeholder = ' ';
			$field->choices = $choices;
			$field->defaultValue = $dv;
		
		}
		elseif ( $field->inputName == 'lang' ) {
			$url = plugins_url('/data/lang.iso.639.1.csv', dirname(__FILE__));
			// fetch
			$curl = curl_init();
			curl_setopt($curl, CURLOPT_URL, $url);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($curl, CURLOPT_HEADER, false);
			$data = curl_exec($curl);
			curl_close($curl);

			$choices = array();
			$dv = '';
			$csv = str_getcsv($data, "\n");
			$count = 0;
			foreach ( $csv as &$row ) {
				$count++;
				if ( $count == 1 )
					continue;
				$row = str_getcsv($row, '|');
				$choices[] = array( 'text' => $row[1], 'value' => $row[0] );
			}	
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

/**
 * 
 * POPULATE PROFILE IMAGE FIELD
 *
 */
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
	$lcs = array();
	$lcs_new = array();
	$lgs = array();

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
		elseif ( $f->inputName == 'lang' ) {
			$v = is_object( $f ) ? $f->get_value_export( $entry ) : '';
			$lgs = explode( ',', $v );
		}
	}

	delete_user_meta($u->ID,'user_'.CWNET_PT_CL);
	if ( !empty($cls) ) {
		foreach ( $cls as $t ) {
			$tmp_ = add_user_meta($u->ID,'user_'.CWNET_PT_CL,$t);
		}
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
	if ( !empty($cos) ) {
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

	delete_user_meta($u->ID,'user_lang');
	if ( !empty($lgs) ) {
		foreach ( $lgs as $t ) {
			$tmp_ = add_user_meta($u->ID,'user_lang',trim($t));
		}
	}

	// Attach profile image to user
	// delete_user_meta($u->ID,'user_image');
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
		add_user_meta( $u->ID, 'user_image', $attach_id, true );
	}

	$profile = get_page_by_path(CWNET_P_PROFILE);
	$sep =  '?';
	$target = esc_url_raw( get_permalink($profile->ID).$sep.'action=edit' );

	ob_start();
	wp_safe_redirect( $target );
	exit;
}

/**
 *
 *  SEND MESSAGE TO USER
 *
 */
function cwnet_gform_send_message( $entry,$form ) {

	$fs = array();
	foreach ( $form['fields'] as $f ) {
		if ( $f->inputName == '' )
			continue;

		if ( $f->inputName == 'to' && $f->inputName != '' ) {

			$to = $entry[$f->id];
			$args = array(
				'include' => array($to)
			);
			$to_data = get_users($args);
			$fs[$f->inputName] = $to_data[0]->user_email;
		}
		elseif ( $f->inputName == 'message' ) {
			
			$fs[$f->inputName] = wp_strip_all_tags( $entry[$f->id] );
		}
	}

	if ( $fs['to'] == '' ) {
		$target = esc_url_raw( get_permalink(CWNET_P_NETWORK) );
		ob_start();
		wp_safe_redirect( $target );
		exit;
	}

	$from = wp_get_current_user();
	$from_meta = array_map( function( $a ){ return $a[0]; }, get_user_meta( $from->ID ) );
	
	$from_email = $from->user_email;
	$from_name = $from_meta['first_name']. ' ' .$from_meta['last_name'];
	
	$from_lcs = get_user_meta($from->ID,'user_location');
	$from_lcs_out = implode(', ', wp_list_pluck( $from_lcs,'name' ) );
	
	$from_cos = get_user_meta($from->ID,'user_country');
	$from_cos_out = implode(', ', wp_list_pluck( $from_cos,'name' ) );

	$from_ins = get_user_meta($from->ID,'user_interest');
	$from_ins_out = implode(', ', wp_list_pluck( $from_ins,'name' ) );

	$headers = array(
		'Reply-To: '.$from_name.' <'.$from_email.'>',
	);

	$subject = '['.CWNET_BLOGNAME.'] '.sprintf(__('%s has sent you a message','cwnet'),$from_name);

	$message =
__('Information about the person that is contacting you using civicwise\'s texting service:','cwnet'). "\r\n".
__('Name','cwnet').'. '.$from_name. "\r\n" .
__('Email','cwnet').'. '.$from_email. "\r\n".
__('Location','cwnet').'. '.$from_lcs_out. "\r\n".
__('Country','cwnet').'. '.$from_cos_out. "\r\n".
__('Interests','cwnet').'. '.$from_ins_out. "\r\n\r\n".
__('Message:','cwnet'). "\r\n".
$fs['message']. "\r\n\r\n".
__('To respond this person you can direcly reply this email.','cwnet'). "\r\n".
__('This message has been sent using Civicwise\'s texting service available in https://civicwise.org/our-people. Regarding your privacy, your email address has not been shown to the person contacting you.','cwnet'). "\r\n";

	$sent = wp_mail($fs['to'],$subject,$message,$headers);

	if ( $sent === false )
		$target = esc_url_raw( get_permalink(CWNET_P_NETWORK).'?horror=contact' );
	else
		$target = esc_url_raw( get_permalink(CWNET_P_NETWORK).'?action=contact&to='.$to );

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

// SEND MESSAGE
add_action( 'gform_after_submission_4', 'cwnet_gform_send_message', 10, 2);

/**
 *
 * Render populated send message form
 *
 */
function cwnet_get_send_message_form() {

	if ( !is_user_logged_in() )
		exit;

	$to = get_query_var('to');
	$u_data = get_userdata($to);
	$u_fname = $u_data->first_name;
	$u_lname = $u_data->last_name;
	$u_name = ( !empty($u_fname) ) ? $u_fname. ' ' .$u_lname : $u_data->user_login;

	$fields = array(
		'to_name'=> $u_name,
	);
	$form_out = gravity_form( 4, false, false, false, $fields, false);

	$btn_back_out = '<div class="btn-back"><a class="button" href="'.get_permalink(CWNET_P_NETWORK).'">'.__('Go back to the mosaic','cwnet').'</a></div>';

	return $btn_back_out.$form_out;
}


/**
 *
 * Render send message feedback
 *
 */
function cwnet_get_send_message_feedback() {

	$to = get_query_var('to');
	$action = get_query_var('action');
	$horror = get_query_var('horror');

	$msg_out = '';
	$msg_class = '';
	if ( !empty($action) && !empty($to) ) {
		$to_data = get_userdata($to);
		$to_fname = $to_data->first_name;
		$to_lname = $to_data->last_name;
		$to_name = ( !empty($to_fname) ) ? $to_fname. ' ' .$to_lname : $to_data->user_login;
		$msg = sprintf(__('Your message has been sent successfully to %s.','cwnet'),$to_name);
		$msg_class = 'info';
	}
	elseif ( !empty($action) && empty($to) ) {
		$msg = __('Your message has been sent successfully.','cwnet');
		$msg_class = 'info';
	}
	elseif ( !empty($horror) ) {
		$msg = __('There was a problem sending your message. Sorry about that. Try again, please.','cwnet');
		$msg_class = 'error';
	}
	else {
		return;
	}
	
	if ( !empty($msg) )
		$msg_out = '<div class="msg-container msg-'.$msg_class.'"><strong>'.$msg.'</strong></div>';

	return $msg_out;
}

?>

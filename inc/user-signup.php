<?php
/**
 * User sign up system
 *
 * https://codex.wordpress.org/Customizing_the_Registration_Form
 *
 * @package cwnet
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;
 

/**
 *
 * Add extra fields to signup form
 *
 */
function cwnet_signup_extra_fields() {

	$first_name = ( ! empty( $_POST['first_name'] ) ) ? sanitize_text_field( $_POST['first_name'] ) : '';
	$last_name = ( ! empty( $_POST['last_name'] ) ) ? sanitize_text_field( $_POST['last_name'] ) : '';
	$gender = ( ! empty( $_POST['_u_gender'] ) ) ? sanitize_text_field( $_POST['_u_gender'] ) : '';
	$city = ( ! empty( $_POST['_u_city'] ) ) ? sanitize_text_field( $_POST['_u_city'] ) : '';
	$neighborhood = ( ! empty( $_POST['_u_heighborhood'] ) ) ? sanitize_text_field( $_POST['_u_neighborhood'] ) : '';

	$genders = array(
		'f' => __('Female','cwnet'),
		'm' => __('Male','cwnet'),
		'nb' => __('Not binary','cwnet')
	);
	$genders_list = '<option value=""></option>';
	foreach ( $genders as $v => $l ) {
		$selected = ( $v == $gender ) ? ' selected="selected"' : '';
		$genders_list .= '<option value="'.esc_attr(  $v  ).'"'.$selected.'>'.$l.'</option>';
	}

	$city_parent = '24';
	$args = array(
		'taxonomy' => CWNET_TX_LC,
		'hide_empty' => false,
		'parent' => 0
	);
	$cities = get_terms($args);
	$cities_list = '<option value=""></option>';
	foreach ( $cities as $c ) {
		$selected = ( $c->term_id == $city ) ? ' selected="selected"' : '';
		$cities_list .= '<option value="'.esc_attr(  $c->term_id  ).'"'.$selected.'>'.$c->name.'</option>';
	}

	$args = array(
		'taxonomy' => CWNET_TX_LC,
		'hide_empty' => false,
		'child_of' => $city_parent
	);
	$neighborhoods = get_terms($args);

	$neighborhoods_list = '<option value=""></option>';
	foreach ( $neighborhoods as $c ) {
		$selected = ( $c->term_id == $neighborhood ) ? ' selected="selected"' : '';
		$neighborhoods_list .= '<option value="'.esc_attr(  $c->term_id  ).'"'.$selected.'>'.$c->name.'</option>';
	}

	$signup_out = '
	<div class="signup-block">
		<p class="signup-fieldset signup-input">
			<label for="first_name">'.__( 'First name', 'cwnet' ).
			'<input type="text" name="first_name" id="first_name" class="signup-input" value="'.esc_attr(  $first_name  ).'" required />
		</p>

		<p class="signup-fieldset signup-input">
			<label for="last_name">'.__( 'Last name', 'cwnet' ).
			'<input type="text" name="last_name" id="last_name" class="" value="'.esc_attr(  $last_name  ).'" required />
		</p>

		<p class="signup-fieldset signup-select">
			<label for="_u_gender">'.__( 'Gender', 'cwnet' ).
			'<select name="_u_gender" id="_u_gender" required>'.$genders_list.'</select>
		</p>

		<p id="_u_city_wrap" class="signup-fieldset signup-select">
			<label for="_u_city">'.__( 'Which town do you live in?', 'cwnet' ).
			'<select name="_u_city" id="_u_city" required>'.$cities_list.'</select>
		</p>

		<p id="_u_neighborhood_wrap" class="signup-fieldset signup-select">
			<label for="_u_neighborhood">'.__( 'Ok. Puerto. Which neighborhood?', 'cwnet' ).
			'<select name="_u_neighborhood" id="_u_neighborhood" required>'.$neighborhoods_list.'</select>
		</p>
	</div>
	<div class="signup-block">
		<p class="signup-fieldset signup-checkbox">
			<label for="cwnet"><input type="checkbox" name="cwnet" value="accepted" checked> '.__('I uncheck because I am not a robot.','cwnet').'</label>
		</p>
		<p class="signup-fieldset signup-checkbox">
			<label for="privacy"><input type="checkbox" name="privacy" value="accepted"> <a target="_blank" href="/privacidad">'.__('Privacy policy','cwnet').'</a>. '.__( 'I have read and i accept the privacy policy.', 'cwnet' ).'</label>
		</p>
	</div>
	<script>
	(function($) {
		$(document).ready(function() {
			$("#_u_neighborhood_wrap").hide();
			$("#_u_city_wrap select").change(function(){
				if($("#_u_city_wrap select").val() == "'.$city_parent.'") {
					$("#_u_neighborhood_wrap").show();
				} else {
					$("#_u_neighborhood_wrap").hide();
				}
			});
		});
	})(jQuery);
	</script>
	';
	echo $signup_out;
	return;

}
//this is the hook for wp
// add_action( 'register_form', 'cwnet_signup_extra_fields' );
//add_action( 'signup_extra_fields', 'cwnet_signup_extra_fields' );

function cwnet_signup_scripts() {
	wp_enqueue_script('jquery-core', false, array(), NULL, false);
}
add_action( 'login_enqueue_scripts', 'cwnet_signup_scripts',10 );

/**
 * Add validation for extra fields
 *
 * @param $errors. Instance of registration errors
 * @param $user_name string. username to be registered
 * @param $user_mail string. email address to be registered
 *
 */
function cwnet_signup_validation( $errors, $user_name, $user_email ) {

	$fields = array(
		'first_name' => __('You must include a first name.','cwnet'),
		'last_name' => __('You must include a last name.','cwnet'),
		'_u_gender' => __('Gender is a mandatory field. You must chose one from the list.','cwnet'),
		'_u_city' => __('City is a mandatory field. You must chose one from the list.','cwnet'),
		'_u_neighborhood' => __('Neighborhood is a mandatory field. You must chose one from the list.','cwnet'),
	);
//	foreach( $fields as $f => $e )
//		if ( empty( $_POST[$f] ) || ! empty( $_POST[$f] ) && trim( $_POST[$f] ) == '' )
//			$errors->add( $f.'_error', $e );
	if ( ! array_key_exists('privacy',$_POST) || $_POST['privacy'] != 'accepted' )
		$errors->add('privacy_error', '<strong>'.__('Error','cwnet').'</strong>: '.__('You have to accept the privacy policy.','cwnet') );
	if ( array_key_exists('cwnet',$_POST) &&  $_POST['cwnet'] == 'accepted' )
		$errors->add('cwnet_error', '<strong>'.__('Error','cwnet').'</strong>: '.__('You are a robot.','cwnet') );

	return $errors;
}
//add_filter( 'registration_errors', 'cwnet_signup_validation', 10, 3 );

/**
 * Save sign up extra fields
 *
 * @param $user_id int. User ID
 *
 */
function cwnet_user_register( $user_id ) {
	
	$fields = array(
		'first_name',
		'last_name',
		'_u_gender',
		'_u_city',
		'_u_neighborhood',
	);
	foreach ( $fields as $f )
		if( isset( $_POST[$f] ) && ! empty( $_POST[$f] ) )
			update_user_meta( $user_id, $f, sanitize_text_field( $_POST[$f] ) );

	$p = cwnet_create_persona($user_id);

	if ( is_wp_error($p) )
		var_dump($p->get_error_message());

	return;

}
//add_action( 'user_register', 'cwnet_user_register' );

/**
 * Create persona entry based in user data
 *
 * @param $u_id int. User ID
 *
 */
function cwnet_create_persona( $u_id ) {

	$u = get_userdata($u_id);
	$gender = get_user_meta($u_id,'_u_gender',true);
	$city = get_user_meta($u_id,'_u_city',true);
	$neighborhood = get_user_meta($u_id,'_u_neighborhood',true);

	$t = ( !empty($u->first_name) && !empty($u->last_name) ) ? $u->first_name.' '.$u->last_name : $u->user_login;
	$args = array(
		'post_type' => CWNET_PT_P,
		'post_status' => 'publish',
		'post_author' => $user_id,
		'post_title' => $t,
		'post_content' => '',
		'meta_input' => array(
			'_p_user' => $u_id,
		)
	);
	if ( !empty($gender) )
		$args['meta_input']['_p_gender'] = $gender;
	if ( !empty($u->first_name) )
		$args['meta_input']['_p_firstname'] = $u->first_name;
	if ( !empty($u->last_name) )
		$args['meta_input']['_p_lastname'] = $u->last_name;

	$p = wp_insert_post($args,true);

	$cs_slugs = array();
	if ( !empty($city) )
		$cs_slugs[] = $city['slug'];
	if ( !empty($neighborhood) )
		$cs_slugs[] = $neighborhood['slug'];
	$tc = wp_set_object_terms( $p, $cs_slugs, CWNET_TX_LC, false);
	clean_object_term_cache( $p, CWNET_TX_LC );

	return $p;

}

?>

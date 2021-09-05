<?php
/**
 * Custom end points
 *
 * @package cwnet
 */


// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 *
 *  Countries with user per country
 *
 */ 
function cwnet_api_users_per_location( $data ) {

	$url = plugins_url('/data/world.topo.json', dirname(__FILE__));

	// fetch
	$curl = curl_init();

	curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_HEADER, false);
	$data = curl_exec($curl);

	curl_close($curl);

	$world = json_decode($data,true);

	// build index of cities with users
	// build array
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
	
	$cos = array();
	foreach ( $us as $u ) {
		$u_co = get_user_meta( $u->ID, 'user_country',true);

		if ( empty($u_co) || is_wp_error($u_co) )
			continue;

		$c = $u_co['name'];
		$s = $u_co['slug'];
		$k = array_search($c,array_column($cos,'name'));
		if ( $k === false )
			$cos[] = array(
				'name' => $c,
				'slug' => $s,
				'user_count' => 1,
			);
		else
			$cos[$k]['user_count']++;

	}

	// rebuild topojson
	foreach ( $world['objects']['world']['geometries'] as &$m ) {
		$k = array_search($m['properties']['name'],array_column($cos,'name'));
		if ( $k !== false ) {
			$m['properties']['user_count'] = $cos[$k]['user_count'];
			$m['properties']['slug'] = $cos[$k]['slug'];
		}
		else {
			$m['properties']['user_count'] = 0;
			$m['properties']['slug'] = cwnet_slugify($m['properties']['name']);
		}
	}

	return rest_ensure_response( $world );

}
add_action( 'rest_api_init', function () {
	register_rest_route( 'map', '/wisers', array(
		'methods' => 'GET',
		'callback' => 'cwnet_api_users_per_location',
	));
} );
?>

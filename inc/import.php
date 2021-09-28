<?php
/**
 * Functions to import content
 *
 * @package cwnet
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

function get_headers_from_curl_response($response) {
    $headers = array();

    $header_text = substr($response, 0, strpos($response, "\r\n\r\n"));

    foreach (explode("\r\n", $header_text) as $i => $line)
        if ($i === 0)
            $headers['http_code'] = $line;
        else
        {
            list ($key, $value) = explode(': ', $line);

            $headers[$key] = $value;
        }

    return $headers;
}


/**
 * Insert a list of terms from a CSV file into a taxonomy 
 *
 * @param $tx string. Taxonomy name
 * @param $url string. URL of the data set
 * @param $langs array. Array of lang codes in order
 *
 */
function cwnet_insert_terms_from_csv($tx,$url,$langs) {

	if ( ! taxonomy_exists($tx) ) {
		echo 'The taxonomy does not exist. Check it!';
		return;
	}

	// fetch term list
	$curl = curl_init();

	curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_HEADER, false);
	$data_string = curl_exec($curl);

	curl_close($curl);

	$lines = explode(PHP_EOL, $data_string);
	$data = array();
	foreach ( $lines as $l )
		$data[] = str_getcsv($l);

	foreach ( $data as $c => $t ) {
		if ( $c == 0 )
			continue;

		if ( count($t) > 0 && empty($t[0]) )
			continue;

		echo '<br><br><h2>Term '.$c.'</h2>';
		echo '<p><strong>Inserting '.$t[0].'...</strong></p>';
		$ti_langs = array();
		foreach ( $langs as $k => $l ) {
			echo '<div>Inserting version '.$l.' ('.$t[$k].')</div>';
			$ti = wp_insert_term($t[$k], $tx);
			if ( is_wp_error($ti) ) {
				echo '<div style="color: red;">'.$ti->get_error_message().'</div>';
				$ti = wp_insert_term($t[$k], $tx, array('slug'=> sanitize_title($t[$k]).'-'.$l));
				if ( is_wp_error($ti) ) {
					echo '<div style="color: red;">'.$ti->get_error_message().'</div>';
					echo '<div style="color: red;"><strong>Version '.$l.' not inserted.</strong></div>';
				}
			}
			if ( ! is_wp_error($ti) && !empty($langs) && function_exists('pll_set_term_language') && pll_is_translated_taxonomy($tx) ) {
				pll_set_term_language($ti['term_id'],$langs[$k]);
				$ti_langs[$l] = $ti['term_id'];
				echo '<div>Setting up the language of the term '.$t[$k].' to <strong>'.$l.'</strong></div>';
			}
		}
		if ( !empty($langs) && function_exists('pll_save_term_translations') && pll_is_translated_taxonomy($tx) )
			pll_save_term_translations($ti_langs);
			echo '<div>Setting up the relation among the translations of the term '.$t[0].'</strong></div>';
	
	}
	return;
	
}

/**
 * Insert a list of countries from a JSON file into the country taxonomy 
 *
 * @param $tx string. Taxonomy name
 * @param $url string. URL of the data set
 *
 */
function cwnet_insert_countries($tx='country',$url='https://civicwise.org/wp-content/plugins/civicwise-network/data/world.json') {

	if ( ! is_admin() )
		return;

	if ( get_current_blog_id() != '1' )
		return;
	
	$tx = 'country';
	if ( ! taxonomy_exists($tx) ) {
		echo 'The taxonomy does not exist. Check it!';
		return;
	}

	// fetch term list
	$curl = curl_init();

	curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_HEADER, false);
	$data_string = curl_exec($curl);

	curl_close($curl);

	$cos = json_decode($data_string);

	foreach ( $cos->features as $c ) {

		$name = $c->properties->name;

		if ( ! term_exists($name,$tx) ) {
			$ti = wp_insert_term($name, $tx);
			if ( ! is_wp_error($ti) && function_exists('pll_set_term_language') && pll_is_translated_taxonomy($tx) )
				pll_set_term_language($ti['term_id'],'en');
		}
	}

	return;
	
}


?>

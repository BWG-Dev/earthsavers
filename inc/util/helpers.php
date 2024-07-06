<?php

function es_error_list($error){
    switch  ($error){
        case 'email_exist':
            return 'The email already exist in the system, please try with a new one';
            exit;
    }
}


//https://earthsavers.nextsitehosting.com/checkout/order-pay/24471/?pay_for_order=true&key=wc_order_l2PpmaFz8MggD

function es_generate_string($len){
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randomString = '';

    for ($i = 0; $i < $len; $i++) {
        $index = rand(0, strlen($characters) - 1);
        $randomString .= $characters[$index];
    }

    return $randomString;
}

function es_template( $file, $args ){
    // ensure the file exists
    if ( !file_exists( $file ) ) {
        return '';
    }

    // Make values in the associative array easier to access by extracting them
    if ( is_array( $args ) ){
        extract( $args );
    }

    // buffer the output (including the file is "output")
    ob_start();
    include $file;
    return ob_get_clean();
}

function es_send_admin_business_request_email($email, $data){
    $title   = 'Business Account Request';
    $content = es_template(ES_PLUGIN_PATH . '/templates/admin_business_request_email.php',$data);
    $headers[] = 'Content-Type: text/html; charset=UTF-8';

    return wp_mail( $email, $title, $content,$headers);
}

function es_send_business_shop_email($email, $data){
    $title   = 'Your Account was approved';
    $content = es_template(ES_PLUGIN_PATH . '/templates/business_shop_approval_email.php',$data);
    $headers[] = 'Content-Type: text/html; charset=UTF-8';

    return wp_mail( $email, $title, $content,$headers);
}

function es_send_customer_route_change_notification($email, $data){
    $title   = 'Your Recycling Collection Route changed';
    $content = es_template(ES_PLUGIN_PATH . '/templates/send_notification_customer_route_change.php',$data);
    $headers[] = 'Content-Type: text/html; charset=UTF-8';
    $headers[] = 'From: EARTHSAVERS, LLC <billling@earthsavers.org>';

    return wp_mail( $email, $title, $content,$headers);
}

function sendSubaccountCredential($email,$data){
    $title   = 'Your account is ready';
    $content = es_template(ES_PLUGIN_PATH . '/templates/subaccount-invitation.php',$data);
    $headers = array('Content-Type: text/html; charset=UTF-8');

    wp_mail( $email, $title, $content,$headers);
}

if (!function_exists('write_log')) {

	function write_log($log) {
		if (true === WP_DEBUG) {
			if (is_array($log) || is_object($log)) {
				error_log(print_r($log, true));
			} else {
				error_log($log);
			}
		}
	}

}

//Check if a subscription has a specific product
if (!function_exists('es_check_sub_product')) {

	function es_check_sub_product($subscription, $product_id, $remove = false) {
		$flag = false;
		$items = $subscription->get_items();
		foreach( $items as $item_id => $item ) {
			if($item['product_id'] == $product_id){
				$flag = true;
				if($remove){
					$subscription->remove_item($item_id);
					$subscription->calculate_totals();
					$subscription->save();
				}
			}
		}

		return $flag;
	}

}

if (!function_exists('es_check_sub_product')) {

	function es_check_sub_product($subscription, $product_id) {
		$flag = false;
		$items = $subscription->get_items();
		foreach( $items as $item ) {
			if($item['product_id'] == $product_id){
				$flag = true;
			}
		}

		return $flag;
	}

}

function es_timeago($date) {
	$timestamp = strtotime($date);

	$strTime = array("second", "minute", "hour", "day", "month", "year");
	$length = array("60","60","24","30","12","10");

	$currentTime = time();
	if($currentTime >= $timestamp) {
		$diff     = time()- $timestamp;
		for($i = 0; $diff >= $length[$i] && $i < count($length)-1; $i++) {
			$diff = $diff / $length[$i];
		}

		$diff = round($diff);
		return $diff . " " . $strTime[$i] . "(s) ago ";
	}
}

function es_km($num) {

	if($num>1000) {

		$x = round($num);
		$x_number_format = number_format($x);
		$x_array = explode(',', $x_number_format);
		$x_parts = array('k', 'm', 'b', 't');
		$x_count_parts = count($x_array) - 1;
		$x_display = $x;
		$x_display = $x_array[0] . ((int) $x_array[1][0] !== 0 ? '.' . $x_array[1][0] : '');
		$x_display .= $x_parts[$x_count_parts - 1];

		return $x_display;

	}

	return $num;
}

function es_group_routes_by_wday($routes){
	$routes_by_wday=array();
	foreach ($routes as $route){
		$recycling_collection_interval = get_field('recycling_collection_interval', $route->ID);
		$recycling_collection_date = get_field('recycling_collection_date', $route->ID);
		$routes_by_wday[$recycling_collection_date['label']][]=	$recycling_collection_interval['label'];
	}
	return $routes_by_wday;
}

function es_echo_route_frecuency($wday, $frecuencies){

	$current_user = wp_get_current_user();
    if ( $current_user->exists() ) {
		$user_roles = $current_user->roles;
		if ( in_array( 'business', $user_roles ) ) {
			if(count($frecuencies)==1){
				echo '<p> Every four weeks on '.$wday.'s</p>';
			}elseif (count($frecuencies)==2) {
				echo '<p> Every two weeks on '.$wday.'s</p>';
			}else{
				echo '<p> Every '.$wday.'</p>';
			}
		}else{
			usort($frecuencies, function($a, $b) {
				return es_getOrder($a) - es_getOrder($b);
			});
			if(count($frecuencies)==1){
				echo '<p>'.$frecuencies[0].' '.$wday.' '.get_field('colection_note', 'option').'</p>';
			}elseif (count($frecuencies)==2) {
				echo '<p>'.$frecuencies[0].' & '.$frecuencies[1].' '.$wday.' '.get_field('colection_note', 'option').'</p>';
			}elseif (count($frecuencies)==3) {
				echo '<p>'.$frecuencies[0].', '.$frecuencies[1].' & '.$frecuencies[2].' '.$wday.' '.get_field('colection_note', 'option').'</p>';
			}else{
				echo '<p> Every '.$wday.'</p>';
			}
		}
	}

}

function es_getOrder($day) {
	if ($day == "Each") {
		return PHP_INT_MAX;
	}
	preg_match('/(\d+)/', $day, $matches);
	return intval($matches[1]);
}

function es_remove_filters_with_method_name( $hook_name = '', $method_name = '', $priority = 0 ) {
	global $wp_filter;

	// Take only filters on right hook name and priority
	if ( ! isset( $wp_filter[ $hook_name ][ $priority ] ) || ! is_array( $wp_filter[ $hook_name ][ $priority ] ) ) {
		return false;
	}

	// Loop on filters registered
	foreach ( (array) $wp_filter[ $hook_name ][ $priority ] as $unique_id => $filter_array ) {
		// Test if filter is an array ! (always for class/method)
		if ( isset( $filter_array['function'] ) && is_array( $filter_array['function'] ) ) {
			// Test if object is a class and method is equal to param !
			if ( is_object( $filter_array['function'][0] ) && get_class( $filter_array['function'][0] ) && $filter_array['function'][1] == $method_name ) {
				// Test for WordPress >= 4.7 WP_Hook class (https://make.wordpress.org/core/2016/09/08/wp_hook-next-generation-actions-and-filters/)
				if ( is_a( $wp_filter[ $hook_name ], 'WP_Hook' ) ) {
					unset( $wp_filter[ $hook_name ]->callbacks[ $priority ][ $unique_id ] );
				} else {
					unset( $wp_filter[ $hook_name ][ $priority ][ $unique_id ] );
				}
			}
		}

	}

	return false;
}


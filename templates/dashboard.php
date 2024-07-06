<?php
/**
 * My Account Dashboard
 *
 * Shows the first intro screen on the account dashboard.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/myaccount/dashboard.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 4.4.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$allowed_html = array(
	'a' => array(
		'href' => array(),
	),
);

$user = wp_get_current_user();

$user_roles = $user->roles;
$company = get_user_meta($user->ID, '_es_company', true);
if(empty($company)){
	$company = get_user_meta( $user->ID, 'billing_company', true );
}

//$address = wc_get_account_formatted_address( 'billing' );

?>

<style>.recycling-section{display: none;} .dashboard-info p{ margin-bottom: 0}</style>
<div class="container">
    <div class="row my-3">
        <div class="col-12">
            <h1>
	            <?= !empty($company) ?  $company : ''; ?>
            </h1>
        </div>
    </div>
	<div class="row">
		<div class="col-md-4 dashboard-info">
			<h3>Primary Contact:</h3>
			<p>
				<?= !empty($user->first_name) ?  $user->first_name : get_user_meta($user->ID, 'billing_first_name', true); ?>
				<?= !empty($user->last_name) ? $user->last_name : get_user_meta($user->ID, 'billing_last_name', true); ?>
            </p>
			<p><?= $user->user_email; ?></p>
			<p><?= $user->billing_phone; ?></p>
			<br>

            <h3>Service Address:</h3>
            <?php
                $billing_address = get_user_meta( $user->ID, 'billing_address_1', true );
                $billing_company = get_user_meta( $user->ID, 'billing_company', true );
			    $addresses = array();
                if( !empty( $billing_address ) ){
                    $addresses[] = $billing_address;
                    ?>
                    <p><strong><?= empty( $billing_company ) ? 'Primary' : $billing_company; ?></strong></p>
                    <p><?= $billing_address ?></p>
                    <p><?= get_user_meta($user->ID, 'billing_address_2', true); ?></p>
                    <p><?= get_user_meta($user->ID, 'billing_city', true) . ', ' . get_user_meta($user->ID, 'billing_state', true) . ' ' .get_user_meta($user->ID, 'billing_postcode', true); ?></p>

                <?php }
                $subscriptions = wcs_get_users_subscriptions($user->ID);


                if( count($subscriptions) > 1 ){

                    foreach ( $subscriptions as $sub ){
						$billing_address_company = get_post_meta($sub->ID, '_billing_address_1', true);
                        $billing_company_organization = get_post_meta($sub->ID, '_billing_company', true);
                        if( $billing_company && $billing_company_organization && $billing_company == $billing_company_organization ){ continue; }

                        ?>
                        <hr>
                        <p><strong><?= $billing_company_organization ?></strong></p>
                        <p><?= $billing_address_company; ?></p>
                        <p><?= get_post_meta($sub->ID, '_billing_address_2', true); ?></p>
                        <p><?= get_post_meta($sub->ID, '_billing_city', true) . ', ' . get_post_meta($sub->ID, '_billing_state', true) . ' ' .get_post_meta($sub->ID, '_billing_postcode', true); ?></p>
                    <?php }
                } ?>

		</div>
		<div class="col-md-4">
			<?php
			/*echo "<a class='ticket-link' href='/my-tickets'>My tickets</a>";
			echo "<a class='ticket-link' href='/submit-ticket'>New ticket</a><br><br>";*/

			$routes = get_field('route_id', 'user_' . $user->ID);
			if(count($routes) > 0){
				echo '<h3>Collection Schedule:</h3>';
				/*echo  get_field('pickup_date_note', 'option');*/
				/* foreach ($routes as $route){
					$recycling_collection_interval = get_field('recycling_collection_interval', $route->ID);
					$recycling_collection_date = get_field('recycling_collection_date', $route->ID);

					//echo '<p>' . $route->post_title . ': ' . $recycling_collection_interval['label'] . ' ' . $recycling_collection_date['label'] . '</p>';
					echo '<p>' . $recycling_collection_interval['label'] . ' ' . $recycling_collection_date['label'] . '</p>';
				} */
				$routes_by_wday=es_group_routes_by_wday($routes);
				$customOrder = [
					"Monday",
					"Tuesday",
					"Wednesday",
					"Thursday",
					"Friday",
					"Sunday"
				];
				uksort($routes_by_wday, function ($a, $b) use ($customOrder) {
					return array_search($a, $customOrder) - array_search($b, $customOrder);
				});
				echo'<div>';
				foreach ($routes_by_wday as $wday => $collectionDays) {
					es_echo_route_frecuency($wday, $collectionDays);
				}

				/* $payment_url='empty';
				$order_id = 24421;
				$order = wc_get_order($order_id);
				if ($order) {
					$order_key = $order->get_order_key();
					$payment_url = $order->get_checkout_payment_url();
					echo('<div>'.$payment_url.'</div>');
				} else {
					echo($payment_url);
				} */
				echo '</div>';
			}
			?>
            <p style="color: #789101; font-size: 14px; font-weight: bold"><?= get_field('pickup_date_note', 'option') ?></p>

		</div>
        <div class="col-md-4">
            <h3>Service Subscription:</h3>
	        <?php

	        $subscriptions = wcs_get_users_subscriptions($user->ID);
	        foreach ($subscriptions as $sub){
		        foreach( $sub->get_items() as $item_id => $product_subscription ){
			        ?>
                    <p><?php echo $product_subscription->get_name(); ?></p>
			        <?php
		        }
	        }

	        ?>
        </div>
	</div>
    <div class="row">
        <div class="col-12">
            <p class="text-center" style="font-weight: bold; color: #789101"> Thank you for choosing to recycle with EarthSavers!</p>
        </div>
    </div>
</div>

<?php
/**
 * My Account dashboard.
 *
 * @since 2.6.0
 */


/**
 * Deprecated woocommerce_before_my_account action.
 *
 * @deprecated 2.6.0
 */
do_action( 'woocommerce_before_my_account' );

/**
 * Deprecated woocommerce_after_my_account action.
 *
 * @deprecated 2.6.0
 */
do_action( 'woocommerce_after_my_account' );

/* Omit closing PHP tag at the end of PHP files to avoid "headers already sent" issues. */

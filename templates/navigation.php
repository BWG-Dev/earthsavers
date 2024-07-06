<?php
/**
 * My Account navigation
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/myaccount/navigation.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 2.6.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

do_action( 'woocommerce_before_account_navigation' );
$logout_endpoint = '';
$logout_label = '';
//var_dump(get_current_user());
?>

<nav class="woocommerce-MyAccount-navigation">
	<ul>
		<?php foreach ( wc_get_account_menu_items() as $endpoint => $label ) :

            ?>
			<?php if($label != 'Log out'){ ?>
                <li class="<?php echo wc_get_account_menu_item_classes( $endpoint ); ?>">
                    <a href="<?php echo esc_url( wc_get_account_endpoint_url( $endpoint ) ); ?>"><?php echo esc_html( $label ); ?></a>
                </li>
			<?php } else {
                $logout_endpoint = $endpoint;
                $logout_label = $label;
            } ?>
		<?php endforeach; ?>
		<li><a href='/my-tickets'>My tickets</a></li>
		<li><a href='/submit-ticket'>Submit a ticket</a></li>
        <li><a href="<?php echo esc_url( wc_get_account_endpoint_url( $logout_endpoint ) ); ?>"><?php echo esc_html( $label ); ?></a></li>
	</ul>
</nav>

<?php do_action( 'woocommerce_after_account_navigation' ); ?>

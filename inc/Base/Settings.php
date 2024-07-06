<?php

/*
*
* @package Yariko
*
*/

namespace Es\Inc\Base;

class Settings{

/**
 * @description Register various actions and filters for the plugin
 *
 * @return void
 */
	public function register() {



        add_action( 'admin_bar_menu', array( $this, 'my_plugin_add_admin_bar_items' ), 500 );

		add_action( 'after_setup_theme', array( $this, 'woocommerce_support' ) );

		add_action( 'template_redirect', array( $this, 'restrict_business_shop_page' ) );

        add_filter( 'wcs_calculate_next_payment_from_last_payment', '__return_false' );

        add_action( 'plugins_loaded', function () {
            es_remove_filters_with_method_name( 'template_redirect', 'maybe_setup_cart', 100 );
        });

        //add_filter( 'woocommerce_subscription_payment_pending_switch', '__return_false', 100 );

		add_shortcode( 'services_list', array( $this, 'services_list' ) );
		add_shortcode( 'es_business_form', array( $this, 'business_form' ) );
		add_shortcode( 'es_business_shop', array( $this, 'business_shop' ) );

        add_action( 'wpo_wcpdf_after_order_details', array( $this, 'invoice_terms' ), 10, 2 );
        add_filter( 'woocommerce_locate_template', array($this, 'locate_template'), 10, 3 );
        add_filter( 'woocommerce_checkout_fields', array($this, 'remove_checkout_fields') );
        add_action( 'woocommerce_checkout_billing', array($this, 'add_checkout_fields') , 9999, 1 );
        add_action( 'woocommerce_checkout_update_order_meta', array($this, 'update_checkout_fields') , 10, 1);
        add_action( 'woocommerce_after_checkout_validation', array($this, 'custom_fields_validation'), 10, 2 );

		//add_action( 'woocommerce_account_content', array($this, 'add_my_tickets_content') );

		add_action( 'init', array($this, 'cptui_register_my_routes') );

		add_action( 'wp', array($this, 'subaccount_logic'));

		//My accounts endpoint
		add_action( 'init', array($this, 'my_accounts_endpoint')  );
		add_filter( 'woocommerce_get_query_vars', array($this, 'my_accounts_vars'), 0 );
		add_filter( 'woocommerce_account_menu_items', array($this, 'my_accounts_link'), PHP_INT_MAX, 1 );
		add_action( 'woocommerce_account_my-accounts_endpoint', array($this, 'accounts_content') );


        add_filter('load-index.php', array($this, 'dashboard_redirect'));


        add_filter('acf/update_value', array($this, 'acf_update_route_id_in_user'), 10, 3);

		add_filter('wpas_submission_form_after', array($this, 'submit_ticket_logic'));

		add_action('woocommerce_account_dashboard', array($this, 'add_address_dashboard'));

		add_action('wp_logout', array($this, 'remove_user_session'));

		/*add_action('woocommerce_subscription_payment_complete', array($this, 'remove_fee_after_payment_completion'), 10, 1);

        add_action('es_check_due_subscriptions', array($this, 'es_check_due_subscriptions'));*/

        //Add the company name to the invoice
		add_filter( 'woocommerce_order_formatted_billing_address', function( $billing_address, $order ){
			if(!empty($billing_address['company'])){
				unset($billing_address['company']);
            }
			return $billing_address;
		}, 10, 2 );

        add_action('wpo_wcpdf_before_billing_address', function($type, $order){
	        $user_id = $order->get_user_id();
	        $company = get_user_meta($user_id, '_es_company', true);
	        if(empty($company)){
		        $company = get_user_meta( $user_id, 'billing_company', true );
	        }
            echo $company . '<br>';
        }, 10, 2);

		add_action('admin_head', function(){

			$user = wp_get_current_user();

			$user_roles = $user->roles;
            /*if(!empty($user) && is_account_page()){
	            */?><!--
                <style>
                    #menu-main-menu{
                        display: none !important;
                    }
                </style>
	            --><?php
/*            }*/


			if(in_array('wpas_support_manager', $user_roles)) {
				?>
                <style>
                    #toplevel_page_wpseo_workouts,
                    .user-rich-editing-wrap,
                    .user-admin-color-wrap, .user-comment-shortcuts-wrap, .user-description-wrap, .user-profile-picture,
                    .form-table:has(.user-profile-picture),.application-passwords.hide-if-no-js, #wpas_user_profile_segment, #wpas_user_profile_segment,
                    .yoast-settings, .user-admin-bar-front-wrap,
                    #fieldset-shipping, #wikipedia,
                    #fieldset-billing + h2,
                    .form-table:has(.user-email-wrap) + h2,
                    .user-wikipedia-wrap,
                    #menu-dashboard
                    {
                        display: none;
                    }
                </style>
				<?php
			}
		});

        add_filter('woocommerce_my_account_my_orders_columns', array($this, 'change_order_column_name'), 10, 1);
        add_filter('wpo_wcpdf_myaccount_button_text', array($this, 'change_invoice_pdf_link_name'), 10, 1);

        add_action( 'woocommerce_thankyou', array($this, 'auto_complete_order') );


       //add_filter('acf/load_field/name=customer_id', array($this, 'populate_user_id'), 10, 1);

        add_filter( 'woocommerce_quantity_input_step_admin', function( $step, $product ) {

            return 0.01;
        }, 10, 2 );

        add_filter( 'woocommerce_quantity_input_min_admin', function( $min, $product ) {

			return 0.01;
		}, 10, 2 ); // Simple products

        add_filter( "gettext_woocommerce-subscriptions", function($translated_text, $text, $domain){
	        if ( 'woocommerce-subscriptions' === $domain ) {
		        switch ( $text ) {
			        case 'Related orders':
				        $translated_text = 'Related invoices';
				        break;

		        }
	        }

	        return $translated_text;

        }, 10,3 );


        add_action( 'woocommerce_thankyou', array($this,'es_change_to_complete') );

        add_action('woocommerce_before_pay_action', function($order){
            if(isset($_GET['pay_for_order']) && $_GET['pay_for_order']){
		        if(isset($_POST['wc-stripe-new-payment-method']) && $_POST['wc-stripe-new-payment-method']){
                    if($sub_id = get_post_meta($order->get_id(), '_subscription_renewal', true)){

	                    $subscription = wcs_get_subscription( $sub_id );
	                    $subscription->set_payment_method( 'stripe' );
	                    update_post_meta($subscription->get_id(), '_requires_manual_renewal', 'false');

	                    $subscription->save();

                    }

		        }
            }


        },1,10);

        //add_filter( 'woocommerce_email_headers', array($this, 'custom_cc_email_headers'), 10, 3 );

        add_filter( 'user_has_cap', array($this, 'es_guest_pay'), 9999, 3 );
        add_filter( 'woocommerce_order_email_verification_required', '__return_false', 9999 );

        add_filter( 'wc_order_statuses', array($this, 'rename_woocommerce_order_status'), 10, 1 );


        add_filter( 'retrieve_password_message', array($this, 'es_wp_retrieve_password_message'), 10, 3 );


        add_filter( 'retrieve_password_notification_email', array($this, 'set_lost_password_email_content_type'),
            10,
            4
        );

        //Remove the Subscription details from all the emails
        add_action( 'woocommerce_email_after_order_table', array( $this, 'remove_subscription_details'), 5, 4 );

        //add_filter('woocommerce_email_recipient_customer_new_order', array( $this, 'stop_email_main_account'), 999, 3);
        add_filter('woocommerce_email_recipient_customer_processing_order', array( $this, 'stop_email_main_account'), 999, 3 );
        add_filter('woocommerce_email_recipient_customer_completed_order', array( $this, 'stop_email_main_account'), 999, 3 );
        add_filter('woocommerce_email_recipient_customer_refunded_order', array( $this, 'stop_email_main_account'), 999, 3 );
        add_filter('woocommerce_email_recipient_customer_invoice', array( $this, 'stop_email_main_account'), 999, 3 );
        add_filter('woocommerce_email_recipient_customer_new_renewal_order', array( $this, 'stop_email_main_account'), 999, 3 );
        add_filter('woocommerce_email_recipient_customer_renewal_invoice', array( $this, 'stop_email_main_account'), 999, 3 );

        //Add an overdue order filter to group them.
        add_action('restrict_manage_posts', array( $this, 'add_overdue_order_filter' ) );
        add_filter('request', array( $this, 'overdue_filter_order' ), 10, 1 );

        //Add fee to order due over 45 days.
        //add_action( 'es_daily_orders_check', array( $this, 'daily_orders_check' ) );

        add_action( 'woocommerce_email_header', function( $email_heading, $email ){
			$GLOBALS['email'] = $email;
		}, 1, 2 );

        //add_filter('wc_stripe_save_to_subs_text', function(){ return 'Click here to automate payments of future invoices.'; });
/*
        add_filter( 'woocommerce_default_address_fields' , array( $this, 'override_default_address_fields' ) );
        add_filter( 'woocommerce_checkout_fields' , array( $this, 'override_default_address_fields' ), 9999 );*/
        add_filter('woocommerce_customer_meta_fields', array( $this, 'override_default_address_fields' ), 9999);

       // add_action( 'woocommerce_edit_account_form', array( $this, 'woocommerce_edit_account_form'), 10 );
        add_action( 'woocommerce_save_account_details', array( $this, 'woocommerce_save_account_details'), 10 );

        //Add company column to the order tab admin
        add_filter( 'manage_edit-shop_order_columns', array( $this, 'add_order_company_column_header'), 20 );
        add_action( 'manage_shop_order_posts_custom_column', array( $this, 'add_order_company_column_content') );

    add_filter( 'manage_edit-shop_subscription_columns', array( $this, 'add_subscription_company_column_header'), 20 );
    add_action( 'manage_shop_subscription_posts_custom_column', array( $this, 'add_order_company_column_content') );

        //Add company column to the ticket tabs
        add_filter( 'manage_edit-ticket_columns', array( $this, 'add_ticket_custom_columns'), 20 );
        add_action( 'manage_ticket_posts_custom_column', array( $this, 'add_order_company_column_content_ticket') );

        //add_filter('wcs_renewal_order_created', array($this, 'change_order_and_subscription_status'), 10, 2);


}

    function add_order_company_column_content_ticket( $column ){
        if ( $column == 'user_company' ){
			global $post;
            $user_id = $post->post_author;
			$company = get_user_meta($user_id, '_es_company', true);
			if(empty($company)){
				$company = get_user_meta( $user_id, 'billing_company', true );
			}

			if(empty( $company )){
				$user = get_user_by('id', $user_id);
				echo "<a href='".site_url()."/wp-admin/admin.php?page=es-users&user_id=".$user_id."'>" . $user->display_name . "</a>";
			}else{
				echo "<a href='".site_url()."/wp-admin/admin.php?page=es-users&user_id=".$user_id."'>" . $company . "</a>";
			}

        }

    }

    function add_ticket_custom_columns($columns){
		$new_columns = array();
		foreach ( $columns as $column_name => $column_info ) {
			$new_columns[ $column_name ] = $column_info;
			if ( 'ticket-tag' === $column_name ) {
				$new_columns['user_company'] = 'Company'; // column title
			}
		}
		return $new_columns;

    }

    function add_order_company_column_header( $columns ) {
        $new_columns = array();
        foreach ( $columns as $column_name => $column_info ) {
            $new_columns[ $column_name ] = $column_info;
            if ( 'cb' === $column_name ) {
                $new_columns['order_company'] = 'Company'; // column title
            }
        }
        return $new_columns;
    }

    function add_subscription_company_column_header( $columns ) {
        $new_columns = array();
        foreach ( $columns as $column_name => $column_info ) {
            $new_columns[ $column_name ] = $column_info;
            if ( 'status' === $column_name ) {
                $new_columns['order_company'] = 'Company'; // column title
            }
        }
        return $new_columns;
    }

    function add_order_company_column_content( $column ) {
        global $post;
        if ( 'order_company' === $column ) {
            $order = wc_get_order( $post->ID );
			$user_id = $order->get_user_id();
			$company = get_user_meta($user_id, '_es_company', true);
			if(empty($company)){
				$company = get_user_meta( $user_id, 'billing_company', true );
			}

            if(empty( $company )){
                $user = get_user_by('id', $user_id);
				echo "<a href='".site_url()."/wp-admin/admin.php?page=es-users&user_id=".$user_id."'>" . $user->display_name . "</a>";
            }else{
				echo "<a href='".site_url()."/wp-admin/admin.php?page=es-users&user_id=".$user_id."'>" . $company . "</a>";
            }

        }
    }

    function woocommerce_edit_account_form() {

        $user_id = get_current_user_id();

        $custom_label = get_user_meta( $user_id, 'mobile_phone', true );
        ?>
        <p class="woocommerce-form-row woocommerce-FormRow--first form-row form-row-first">
            <label for="mobile_phone"><?php _e( 'Mobile Phone', 'woocommerce' ); ?> <span class="required">*</span></label>
            <input type="text" class="woocommerce-Input woocommerce-Input--email input-text" name="mobile_phone" id="mobile_phone" value="<?php echo esc_attr( $custom_label ); ?>" />
        </p>
        <div class="clear"></div>
        <?php
    }


    function woocommerce_save_account_details( $user_id ) {

            if ( isset( $_POST['mobile_phone'] ) ) {
                update_user_meta($user_id, 'mobile_phone', sanitize_text_field($_POST['custom_label']));
            }
    }
    function override_default_address_fields( $fields ) {

        $billing_email = $fields['billing']['fields']['billing_email'];

        unset($fields['billing']['fields']['billing_email']);

		$fields['billing']['fields']['billing_phone']['label'] = 'Main Phone';
		$fields['billing']['fields']['mobile_phone'] = array(
			'label'       => __( 'Mobile Phone', 'woocommerce' ),
			'description' => '',
		);

		$fields['billing']['fields']['billing_email'] = $billing_email;

		return $fields;

    }



    /**
     * @return void
     */
    public function daily_orders_check() {

        $late_fee = floatval( get_field( 'es_late_fee', 'option' ) );
        $days = intval( get_field( 'es_overdue_days', 'option' ) );

        $orders = get_posts( array(
            'numberposts' => -1,
            'post_type'   => 'shop_order', // WC orders post type
            'post_status' => array('wc-pending','wc-failed'),
            'fields'      => 'ids',
            'date_query'  => array(
                array(
                    'before'     => $days . ' days ago',
                    'column'    => 'post_date',
                ),
            ),
            'meta_query' => array(
                array(
                    'key' => '_overdue_fee_added',
                    'compare' => 'NOT EXISTS',
                )
            )
        ) );



        $cont = 0;

        foreach( $orders as $order_id ) {

            if( ! empty( get_post_meta( $order_id, '_overdue_fee_added', true ) ) ) {
                $cont++;
                continue;
            }

            $order = wc_get_order( $order_id );


            // Check if order status is 'pending'
            if( 'pending' === $order->get_status() || 'failed'  === $order->get_status() ) {

                // Set the fee
                $fee = new \WC_Order_Item_Fee();

                $fee->set_name( "Overdue Fee" );
                $fee->set_amount( $late_fee ); // Set the fee amount
                $fee->set_tax_class( '' );
                $fee->set_tax_status( 'none' );
                $fee->set_total( $late_fee );  // Set the fee total

                // Add Fee item to the order
                $order->add_item( $fee );


                update_post_meta( $order_id, '_overdue_fee_added', 1 );

                $order->calculate_totals();
                $order->save();
            }

            $cont++;
        }

    }

    public function add_overdue_order_filter(){
        global $typenow;

        // Checks if current type is a WooCommerce order.
        if ('shop_order' != $typenow) {
            return;
        }

        echo '<select name="overdue" id="dropdown_overdue">';
        echo '<option value="">' . __('Filter by overdue status', 'woocommerce') . '</option>';
        echo '<option value="_overdue_status">' . __('Overdue
                ', 'woocommerce') . '</option>';
        echo '</select>';
    }

    public function overdue_filter_order($vars) {
			global $typenow;

			// Checks if current type is a WooCommerce order.
			if ('shop_order' == $typenow && isset($_GET['overdue'])) {
				if ($_GET['overdue'] === '_overdue_status') {

					$vars['date_query'] = array(
						array(
							'column' => 'post_date_gmt',
							'before' => '-15 days',
						)
					);

					$vars['post_status'] = array( 'wc-failed', 'wc-pending', 'wc-on-hold' );

				}
			}

			return $vars;
    }

    function remove_subscription_details( $order, $sent_to_admin, $plain_text, $email ){
        remove_action( 'woocommerce_email_after_order_table', array( 'WC_Subscriptions_Order', 'add_sub_info_email' ), 15, 3 );
    }

    function es_wp_retrieve_password_message( $content, $key, $user_login ) {
        $data = array(
                'reset_link' => network_site_url( "resetpass?action=rp&key=$key&login=" . rawurlencode( $user_login ), 'login' ),
                'username' => $user_login,

        );
	    $message = es_template(ES_PLUGIN_PATH . '/templates/reset_password.php',$data);
        return $message;

    }

    function set_lost_password_email_content_type(array $defaults, string $key, string $user_login,$user_data): array
    {
        if (!isset($defaults['headers'])) {
            $defaults['headers'] = [];
        }

        $defaults['headers'] = (array) $defaults['headers'];

        $defaults['headers'][] = 'Content-Type: text/html';

        return $defaults;
    }

    function rename_woocommerce_order_status( $statuses) {

	    $statuses['wc-processing'] = 'Completed';
	    return $statuses;
    }

    function es_guest_pay( $allcaps, $caps, $args ) {
        if ( isset( $caps[0], $_GET['key'] ) ) {
            if ( $caps[0] == 'pay_for_order' ) {
                $order_id = isset( $args[2] ) ? $args[2] : null;
                $order = wc_get_order( $order_id );
                if ( $order ) {
                    $user_id = $order->get_user_id();
					$allow   = get_field( 'es_allow_guest_payments', 'user_' . $user_id);
                    if( $allow ){
						$allcaps['pay_for_order'] = true;
                    }

                }
            }
        }
        return $allcaps;
    }

    function stop_email_main_account( $email_recipient, $email_object, $email ) {

        $user = get_user_by( 'email', $email_recipient );

        if ( ! $user ) {
            return $email_recipient;
        }else{

            $login = $user->user_login;
            //get the user by username since email can change
			$user = get_user_by( 'login', $login );

			$stop     = get_field( 'es_stop_notifications', 'user_' . $user->ID );
			$payer_cc = get_field( 'es_payers_cc', 'user_' . $user->ID );

			if ( $stop ) {
				$email_recipient = $payer_cc;
			}else{
				$email_recipient .= ',' . $payer_cc;
			}
        }

        return $email_recipient;

    }

    function custom_cc_email_headers( $header, $email_id, $order ) {

        $invoice_emails = array(
                "customer_processing_order",
                "customer_completed_order",
                "customer_refunded_order",
                "customer_invoice",
                "new_renewal_order",
                "customer_renewal_invoice",
            );

        if( ! in_array( $email_id, $invoice_emails , true ) ) {
			return $header;
		}

        // Get the custom email from user meta data  (with the correct User ID)
        $payer_cc = get_field('es_payers_cc', 'user_' . $order->get_user_id() );

	    $billing_email  = $order->get_billing_email();

        $multi = explode('‚', $payer_cc);

        if(empty($payer_cc)){
            return $header;
        }

	    $user_name  = 'Payer';
        $cont = 1;
	    foreach ($multi as $payer){

            if(trim($payer) === $billing_email){
                continue;
            }

		    $formatted_email = utf8_decode($user_name . $cont++ . ' <' . trim($payer) . '>');

		    $header .= 'Cc: '.$formatted_email .'\r\n';

        }

        return $header;
    }

    function es_change_to_complete( $order_id ) {
        if ( ! $order_id ) {
            return;
        }

        $order = wc_get_order( $order_id );

        if( $order->has_status( 'processing' ) ) {
            $order->update_status( 'completed' );
        }
    }

    function populate_user_id( $field ) {

        if(isset($_GET['user_id'])){
	        $field['value'] = $_GET['user_id'];
        }

	    // if field should be disabled
	    $field['disabled'] = 1;

	    return $field;

    }

    function auto_complete_order( $order_id ) {
        if ( ! $order_id ) {
            return;
        }

        $order = wc_get_order( $order_id );
        $order->update_status( 'completed' );
    }

    function change_invoice_pdf_link_name(){
        return 'PDF';
    }

    function change_order_column_name($columns){
        $columns['order-number'] = 'Invoice';
        return $columns;
    }

    function my_plugin_add_admin_bar_items( $admin_bar ) {

	    global $current_user;
	    // is there a user ?
	    if(is_array($current_user->roles)) {
		    // check, whether user has the author role:
		    if(in_array('wpas_support_manager', $current_user->roles)) {
			    $admin_bar->add_menu(
				    array(
					    'id'    => 'log-out-custom',
					    'title' => 'Log Out',
					    'href'  => wp_logout_url( site_url('/wp-admin')),
                        'parent' => 'my-account',
					    'meta'  => array(
						    'class' => 'my-plugin-class',
						    'title' => 'Log Out',
					    ),
				    )
			    );
		    }
	    }

    }
    /*function es_check_due_subscriptions(){
        update_post_meta('10381', 'xxx_xxx', 'pepe');
	    // Get all customers subscriptions
	    $hold_subscriptions = get_posts( array(
		    'numberposts' => -1,
		    'post_type'   => 'shop_subscription', // WC orders post type
		    'post_status' => 'wc-on-hold' // Only orders with status "completed"
	    ) );

	    foreach ($hold_subscriptions as $sub){
		    $subscription = wc_get_order($sub->ID);
		    $related_orders = $subscription->get_related_orders();
		    foreach ( $related_orders as $key => $id ) {
			    //if the first order do not have the completed status this means the last renewal attempted failed and the new failed/pending order will have the created time field
			    //that will use to calculate if the subscription has more than 30 days due
			    $last_order = wc_get_order($key);
			    if($last_order->get_status() != 'completed' ){
				    $last_order_date = strtotime($last_order->order_date);
				    $days_due = ceil((strtotime(date('Y-m-d h:i:s')) - $last_order_date)/ 86400);
				    if($days_due > 2 && !es_check_sub_product($subscription, 10315)){

					    $subscription->add_product( wc_get_product(10315), 1 );
					    $subscription->calculate_totals();
					    update_post_meta($sub->ID, 'es_fee_order_from', $last_order->order_date);
					    update_post_meta($sub->ID, 'es_fee_added', date('Y-m-d h:i:s'));
					    write_log('Subscription ' . $sub->ID . ': A late fee was added, order reference used: ' . $id . ' that was created on ' . $last_order->order_date);
				    }
			    }
			    break;
		    }
	    }

    }

	function remove_fee_after_payment_completion($subscription){
		if(es_check_sub_product($subscription, 10315, true)) {
			delete_post_meta( $subscription->ID, 'es_fee_order_from' );
			delete_post_meta( $subscription->ID, 'es_fee_added' );
		}
	}*/

	function invoice_terms($type, $order){
		ob_start();
		if($notes = get_field('tfp_order_invoice_notes', $order->get_id())){
			?>
            <p><strong>Notes:</strong></p>
			<?php echo $notes;
		}

		if($terms = get_field('tfp_order_invoice_terms', $order->get_id())){
			?>
            <p><strong>Terms:</strong></p>
			<?php echo $terms;
		}

		echo ob_get_clean();
	}

    function dashboard_redirect($url) {
        global $current_user;
        // is there a user ?
        if(is_array($current_user->roles)) {
            // check, whether user has the author role:
            if(in_array('wpas_support_manager', $current_user->roles)) {
                $url = admin_url('admin.php?page=es-users');
	            wp_redirect(admin_url('admin.php?page=es-users'));
            }
        }
    }

	function remove_user_session(){
        if(isset($_SESSION['main_account_id'])){
			unset($_SESSION['main_account_id']);
        }

		/*wp_destroy_current_session();
		wp_clear_auth_cookie();*/
		//wp_logout();

      // exit;
	}

	function add_address_dashboard(){

		$customer_id = get_current_user_id();

		if ( ! wc_ship_to_billing_address_only() && wc_shipping_enabled() ) {
			$get_addresses = apply_filters(
				'woocommerce_my_account_get_addresses',
				array(
					'billing'  => __( 'Billing address', 'woocommerce' ),
					'shipping' => __( 'Shipping address', 'woocommerce' ),
				),
				$customer_id
			);
		} else {
			$get_addresses = apply_filters(
				'woocommerce_my_account_get_addresses',
				array(
					'billing' => __( 'Billing address', 'woocommerce' ),
				),
				$customer_id
			);
		}

		ob_start();
		foreach ( $get_addresses as $name => $address_title ) : ?>
			<?php
			$address = wc_get_account_formatted_address( $name );
			?>

            <div class="u-column woocommerce-Address">
                <div class="woocommerce-Address-title title">
                    <h3><?php echo esc_html( $address_title ); ?></h3>
                    <a href="<?php echo esc_url( wc_get_endpoint_url( 'edit-address', $name ) ); ?>" class="edit"><?php echo $address ? esc_html__( 'Edit', 'woocommerce' ) : esc_html__( 'Add', 'woocommerce' ); ?></a>
                </div>
                <address>
					<?php
					echo $address ? wp_kses_post( $address ) : esc_html_e( 'You have not set up this type of address yet.', 'woocommerce' );
					?>
                </address>
            </div>

		<?php endforeach; ?>

		<?php if ( ! wc_ship_to_billing_address_only() && wc_shipping_enabled() ) : ?>
            </div>
		<?php
		endif;

		echo ob_get_clean();
	}

	function subaccount_logic(){
		global $wp;
		$slug = $wp->request;

		if (!session_id()) {
			session_start();
		}

		/*wp_destroy_current_session();
		wp_clear_auth_cookie();
        wp_logout();
        exit;*/

		$user = wp_get_current_user();

		$user_roles = $user->roles;


		if($user){



			if(isset($_SESSION['main_account_id']) && (!in_array($slug, array('my-account/payment-methods', 'my-account/edit-address', 'help', 'submit-ticket', 'ticket/ticket-from-the-subaccount', 'my-tickets', 'my-account', 'my-account/orders', 'my-account/payment-methods')) && !str_contains( $slug, 'ticket/' ) && !str_contains($slug, 'view-order') && !str_contains($slug, 'order-pay') && !str_contains($slug, 'customer-logout') )){
				wp_clear_auth_cookie();
				wp_set_current_user ( $_SESSION['main_account_id'] );
				wp_set_auth_cookie  ( $_SESSION['main_account_id'] );
				/*wp_safe_redirect( '/' . $slug );
				exit();*/
			}

			if(in_array('sub-account', $user_roles)) {

				$main_account = get_user_meta($user->ID, 'main_account_id', true);
				if ($main_account && (in_array($slug, array('my-account/payment-methods', 'my-account/edit-address', 'help', 'submit-ticket', 'ticket/ticket-from-the-subaccount', 'my-tickets', 'my-account', 'my-account/orders', 'my-account/payment-methods')) || str_contains( $slug, 'ticket/' ) || str_contains($slug, 'view-order') || str_contains($slug, 'order-pay') || str_contains($slug, 'customer-logout')) ) {
					wp_clear_auth_cookie();
					wp_set_current_user($main_account);
					wp_set_auth_cookie($main_account);
					$_SESSION['main_account_id']= $user->ID;
					wp_safe_redirect( '/' . $slug );
					exit();
				}
			}
		}
	}

	function submit_ticket_logic(){
		$user = wp_get_current_user();

		$user_roles = $user->roles;
		if(in_array('business', $user_roles) || in_array('sub-account', $user_roles)){
			?>
            <script>
                setTimeout(function (){
                    jQuery('#wpas_ticket_type option[value="57"]').attr("selected", "selected");
                    jQuery('#wpas_ticket_type option[value="57"]').prop("selected", "selected");
                }, 1000);
            </script>
			<?php
		}else{
			?>
            <script>
                setTimeout(function (){
                    jQuery('#wpas_ticket_type option[value="58"]').attr("selected", "selected");
                    jQuery('#wpas_ticket_type option[value="58"]').prop("selected", "selected");
                }, 1000);
            </script>
			<?php
		}
	}

	function my_accounts_endpoint() {
		add_rewrite_endpoint( '/my-accounts', EP_ROOT | EP_PAGES );
	}

	function my_accounts_vars( $vars ) {
		$vars['my-accounts'] = 'my-accounts';
		return $vars;
	}

	function my_accounts_link( $items ) {

		// Remove the logout menu item.
		$logout = $items['customer-logout'];
		unset( $items['customer-logout'] );
		unset( $items['user-switching-switch-back'] );

		// Insert your custom endpoint.
		$items['my-accounts'] = 'My Users';
		$items['orders'] = 'All Invoices';

		// Insert back the logout item.
		$items['customer-logout'] = $logout;
		return $items;
	}

	function accounts_content() {
		ob_start();
		require ES_PLUGIN_PATH . '/templates/my-accounts.php';
		echo ob_get_clean();
	}

	function acf_update_route_id_in_user( $value, $user_id, $field  ) {
		// only do it to certain custom fields
		if( $field['name'] == 'route_id' ) {

			// get the old (saved) value
			$old_value = get_field('route_id', $user_id);

			// get the new (posted) value
			$new_value = $_POST['acf']['field_634ddf5b391c6'];

			// check if the old value is the same as the new value
			//  if( !$old_value || $old_value->ID != $new_value ) {

			$user_array = explode('_', $user_id);

			//$route = get_post($new_value);

			$user = get_user_by('id', $user_array[1]);

			$user_roles = $user->roles;

			// Check if the role you're interested in, is present in the array.
			if ( in_array( 'subscriber', $user_roles, true ) ) {
				// Do something.
				echo 'Yes, User is a subscriber';
			}

			if($new_value){
				$tags = [];

				//remove all the tags
				$routes = get_posts(array('post_status' => 'published', 'post_type' => 'route', 'numberposts' => -1));
				foreach ($routes as $route_obj){

					if($route_obj->post_title != 'Auto Draft' && in_array($route_obj->ID, $new_value)){
						$tags[] = ["name" => $route_obj->post_title, "status" => "active"];
					}else{
						$tags[] = ["name" => $route_obj->post_title, "status" => "inactive"];
					}
				}

				$mailchimp = new Mailchimp();

				$mailchimp->addContact($user->user_email);

				/*$recycling_collection_interval = get_field('recycling_collection_interval', $route->ID);
				$recycling_collection_date = get_field('recycling_collection_date', $route->ID);

				$collection_day = $recycling_collection_interval['label'] . ' ' . $recycling_collection_date['label'];*/

				//$first = $user->user_firstname ?: $user->display_name;

				$email_data = array('link' => site_url('/my-account'));

				//es_send_customer_route_change_notification($user->user_email, $email_data);

				$mailchimp->tagUpdate(md5(strtolower($user->user_email)),$tags);
			}
			// }
		}

		// don't forget to return to be saved in the database
		return $value;

	}

	function add_my_tickets_content(){
		echo "<div class='recycling-section'>";
		/*echo "<a class='ticket-link' href='/my-tickets'>My tickets</a>";
		echo "<a class='ticket-link' href='/submit-ticket'>New ticket</a><br><br>";*/
		$current_user = get_current_user_id();
		$routes = get_field('route_id', 'user_' . $current_user);
		if(count($routes) >0){
			echo '<h3 style="text-align: right"><strong>Collection Schedule:</strong></h3>';

			foreach ($routes as $route){
				$recycling_collection_interval = get_field('recycling_collection_interval', $route->ID);
				$recycling_collection_date = get_field('recycling_collection_date', $route->ID);

				//echo '<p style="text-align: right">' . $route->post_title . ': ' . $recycling_collection_interval['label'] . ' ' . $recycling_collection_date['label'] . '</p>';
				echo '<p style="text-align: right">' . $recycling_collection_interval['label'] . ' ' . $recycling_collection_date['label'] . '</p>';
				echo  "<p style='float: right;width: 300px; font-weight: bold;font-size: 14px; color: #789101'>". get_field('pickup_date_note', 'option') ."</p>";
			}
		}
		echo "</div>";
	}

	function restrict_business_shop_page(){

		global $post;
		if($post && $post->post_name === 'business-shop'){

			if(!is_user_logged_in() && isset($_GET['user_id']) && isset($_GET['token'])){

				wp_clear_auth_cookie();
				wp_set_current_user ( $_GET['user_id'] );
				wp_set_auth_cookie  ( $_GET['user_id'] );

				$pass = get_user_meta($_GET['user_id'], '_es_pass', true);

				if($_GET['token'] == $pass){

					wp_clear_auth_cookie();
					wp_set_current_user ( $_GET['user_id'] );
					wp_set_auth_cookie  ( $_GET['user_id'] );

					do_shortcode('[business_shop]');

				}else{

					wp_logout();
					exit();

				}

			}else{

				wp_redirect(site_url('/my-account'));
				exit();

			}

		}
	}

	function business_shop(){
		ob_start();

		//get product business
		$products = get_posts(array(
			'numberposts'	=> -1,
			'post_type'		=> 'product',
			'meta_key'		=> 'es_is_business',
			'meta_value'	=> '1'
		));


		?>
        <style>.pill{ display: none; }</style>
        <h2>Please select the service you want</h2>
        <br>
        <h4>TWO 32-gallon bins for plastics/cans</h4>
        <form action="">
            <div class="es_row">
                <div class="col">
                    <input disabled checked type="checkbox" name="initial_service" id="initial_service">
                    <label for="initial_service" class="es_label">Initial fee includes ($40)</label>
                </div>
            </div>
            <h4>Do you want inside collection?</h4>
            <div class="es_row">
                <div class="col">
                    <input type="radio" name="collect_inside" class="collect_inside" id="collect_inside_yes">
                    <label for="collect_inside_yes" value="1" class="es_label">Yes, I want inside collection ($12)</label>
                </div>
                <div class="col">
                    <input type="radio" name="collect_inside" class="collect_inside" id="collect_inside_no">
                    <label for="collect_inside_no" value="0" class="es_label">No, I don't want inside collection</label>
                </div>
            </div>
        </form>

        <div class="es_row">
            <div class="es_col">
                <input class="btn btn-primary btn-lg" type="submit" value="Send Request" id="btn_send_business_selection">
            </div>
        </div>

		<?php
		return  ob_get_clean();
	}

	function business_form(){

		ob_start();

		?>

        <form id="es_business_form" action="<?php echo esc_url( admin_url('admin-post.php') ); ?>" method="post">
            <div class="mask">
                <p>Loading...</p>
            </div>
            <input type="hidden" name="action" value="send_business_data" />
            <div class="es_row">
                <div class="es_col_50">
                    <label for="es_first" class="es_label">First Name</label>
                    <input required class="es_input" type="text" name="es_first" id="es_first">
                </div>
                <div class="es_col_50">
                    <label for="es_last" class="es_label">Last Name</label>
                    <input required class="es_input" type="text" name="es_last" id="es_last">
                </div>
            </div>
            <div class="es_row">
                <div class="es_col_50">
                    <label for="es_company" class="es_label">Company Name</label>
                    <input required class="es_input" type="text" name="es_company" id="es_company">
                </div>
                <div class="es_col_50">
                    <label for="es_business_type" class="es_label">Business Type</label>
                    <input required class="es_input" type="text" name="es_business_type" id="es_business_type">
                </div>
            </div>
            <div class="es_row">
                <div class="es_col_50">
                    <label for="es_email" class="es_label">Email</label>
                    <input required class="es_input" type="email" name="es_email" id="es_email">
                </div>
                <div class="es_col_50">
                    <label for="es_phone" class="es_label">Phone</label>
                    <input required class="es_input" type="number" name="es_phone" id="es_phone" pattern="/^[+]*[(]{0,1}[0-9]{1,3}[)]{0,1}[-\s\./0-9]*$/g">
                </div>
            </div>
            <div class="es_row">
                <div class="es_col">
                    <label for="es_address" class="es_label">Service Location(s) Business Address(es)</label>
                    <textarea type="text" name="es_address" id="es_address"></textarea>
                </div>
            </div>
            <div class="es_row">
                <div class="es_col">
                    <label for="es_number_of_employees" class="es_label">Number of Employees</label>
                    <input required class="es_input" type="number" min="1" max="100000"  step="1" value="10" name="es_number_of_employees" id="es_number_of_employees">
                </div>
            </div>

            <div class="es_row">
                <div class="es_col">
                    <label class="es_label">How did you find us?</label>
                    <ul class="list-how-find-us">
                        <li><input required type="checkbox" name="es_find" id="es_find_1" value="Online Search">
                            <label for="es_find_1">Online Search</label></li>

                        <li><input required type="checkbox" name="es_find" id="es_find_2" value="Saw Your Truck">
                            <label for="es_find_2">Saw Your Truck</label></li>

                        <li><input required type="checkbox" name="es_find" id="es_find_3" value="Your Customer Referred Us">
                            <label for="es_find_3">Your Customer Referred Us</label></li>

                        <li><input required type="checkbox" name="es_find" id="es_find_4" value="Got and Email">
                            <label for="es_find_4">Got and Email</label></li>

                        <li><input required type="checkbox" name="es_find" id="es_find_5" value="Got a Postcard">
                            <label for="es_find_5">Got a Postcard</label></li>
                    </ul>
                </div>
            </div>

            <div class="es_row">
                <div class="es_col">
                    <label for="es_referred" class="es_label">Who Referred You? Or Enter Promo Code</label>
                    <input required class="es_input" type="text" name="es_referred" id="es_referred">
                </div>
            </div>

            <div class="es_row">
                <div class="es_col">
                    <label for="es_description" class="es_label">Description of what you are looking for or any comments</label>
                    <textarea required type="text" name="es_description" id="es_description"></textarea>
                </div>
            </div>

            <div class="es_row">
                <div class="es_col">
                    <label class="es_label">Mark items you are interested in recycling</label>
                    <ul class="list-how-find-us">
                        <li><input required type="checkbox" name="es_items" id="es_find_1" value="Cardboard recycling">
                            <label for="es_item_1">Cardboard recycling</label></li>

                        <li><input required type="checkbox" name="es_items" id="es_item_2" value="Paper Recycling">
                            <label for="es_item_2">Paper Recycling</label></li>

                        <li><input required type="checkbox" name="es_items" id="es_item_3" value="Commingled plastic bottle and metal food/drink can recycling">
                            <label for="es_item_3">Commingled plastic bottle and metal food/drink can recycling</label></li>

                        <li><input required type="checkbox" name="es_items" id="es_item_4" value="Glass bottle/jar recycling">
                            <label for="es_item_4">Glass bottle/jar recycling</label></li>

                    </ul>
                </div>
            </div>


            <div class="es_row">
                <div>
                    <input required type="checkbox" name="es_terms" id="es_terms">
                    <label for="es_terms">I have read and agree to the terms and conditions for service. See the <a
                                href="/biz-t-and-c/" target="_blank">terms and conditions</a>  for service.</label>
                </div>

            </div>

            <div class="es_row">
                <div class="es_col">
					<?php wp_nonce_field( 'es_business_form', 'validate_business_form' ); ?>
                    <input class="btn btn-primary btn-lg" type="submit" value="Send Request" id="btn_send_business_info">
                </div>
            </div>
        </form>

		<?php
		return  ob_get_clean();
	}

	/**
	 * @return void
	 * @description Add Woo Support to the theme
	 */
	function woocommerce_support() {
		add_theme_support( 'woocommerce' );
		if( function_exists('acf_add_options_page') ) {

			acf_add_options_page(array(
				'page_title' 	=> 'General Settings',
				'menu_title'	=> 'General Settings',
				'menu_slug' 	=> 'es-general-settings',
				'capability'	=> 'edit_posts',
				'redirect'		=> false
			));

		}
	}

	/**
	 * @description Custom field checkout validation
	 */
	function custom_fields_validation( $data, $errors ) {
		/*if ( empty( $_POST['who_referred_you'] ) ) {
			$errors->add('required-field', __('PLease, select who referred you.', 'woocommerce'));
		}*/

		if ( empty( $_POST['term_and_conditions'] ) ) {
			$errors->add('required-field', __('PLease, to continue you must accept the terms and conditions.', 'woocommerce'));
		}

		$zipcodes  = get_field('es_zipcode_list', 'option');
		$flag = false;
		if( $zipcodes ) {
			foreach( $zipcodes as $row ) {
				$zipcode = $row['zipcode'];
				if($zipcode == $_POST['billing_postcode']){
					$flag = true;
					break;
				}
			}
		}

		if(!$flag){
			$errors->add('required-field', __('There is no service in you area, please check <a href="/residential-services#theform" target="_blank">here</a> to see what area we cover', 'woocommerce'));
		}

	}

	/**
	 * @description Add the custom field to the order
	 */
	function update_checkout_fields ( $order_id ) {
		if ( isset( $_POST ['who_referred_you'] ) &&  '' != $_POST ['who_referred_you'] ) {
			add_post_meta( $order_id, '_who_referred_you',  sanitize_text_field( $_POST ['who_referred_you'] ) );
		}
	}

	/**
	 * @description Add custom checkout fields
	 */
	function add_checkout_fields () {
		?>
        <br>
        <label><strong>Who Referred You?</strong></label>
        <ul class="referred_list">
            <li>
                <input name="who_referred_you" type="radio" id="choice_friend"  value="friend_or_neighbor"  />
                <label for="choice_friend">Friend or Neighbor</label>
            </li>
            <li>
                <input name="who_referred_you" type="radio" id="choice_website"  value="website"  />
                <label for="choice_website">Found your website</label>
            </li>
            <li>
                <input name="who_referred_you" type="radio" id="choice_truck"  value="truck"  />
                <label for="choice_truck">Saw your truck</label>
            </li>

            <li>
                <input name="who_referred_you" type="radio" id="choice_other"  value="other"  />
                <label for="choice_other">Other</label>
            </li>
        </ul>
        <div class="terms">
            <input name="term_and_conditions" type="checkbox" id="term_and_conditions"  value="1"  />
            <label for="term_and_conditions">I have read and agree to the terms and conditions for service. Click the <a style="color: #890014; font-weight: bold" target="_blank"
                                                                                                                       href="/terms-and-conditions/">terms and conditions</a> to view</label>
        </div>
        <!--<br>
        <label><strong>How did you find us?*</strong></label>
        <ul class="referred_list">
            <li>
                <label for="choice_friend">Online Search</label>
                <input name="who_referred_you" type="radio" id="choice_friend"  value="friend_or_neighbor"  />
            </li>
            <li>
                <label for="choice_website">Found your website</label>
                <input name="who_referred_you" type="radio" id="choice_website"  value="website"  />
            </li>
            <li>
                <label for="choice_truck">Saw your truck</label>
                <input name="who_referred_you" type="radio" id="choice_truck"  value="truck"  />
            </li>
        </ul>-->
		<?php
	}

	/**
	 * @description Override the checkout fields
	 */
	function remove_checkout_fields( $fields ) {
		// Billing fields
		unset( $fields['billing']['billing_company'] );

		// Shipping fields

		// Order fields
		unset( $fields['order']['order_comments'] );
		return $fields;
	}

	/**
	 * @return false|string
	 * @description Shortcode to create the ui to select the service and the service interval
	 */
	function services_list(){
		ob_start();
		$args = array(
			'orderby'  => 'ID',
			'order' => 'ASC',
			'status' => 'publish',
			'limit' => 50,


		);
		$products = wc_get_products( $args );
		?>
        <label class="gfield_label"><strong>Select Your Service (Fee includes one 96-gallon cart)</strong></label>
        <ul class="gfield_radio" id="es_service_list">
			<?php
			$first = true;
			foreach ($products as $product){
				$product_id = $product->get_id();
				if(get_field('es_is_business', $product_id)){
					continue;
				}
				?>
                <li class="gchoice es_choice">
                    <input name="es_service" <?php echo $first ? 'checked' : '' ?>  class="service_choice" type="radio" value="<?php echo $product_id; ?>" id="choice_<?php echo $product_id; ?>">
                    <label for="choice_<?php echo $product_id; ?>" id="label_<?php echo $product_id; ?>"><?php echo $product->get_title(); ?> (<?php echo wc_price($product->get_price()); ?>)</label>
                </li>
				<?php
				$first = false;
			}
			?>
        </ul>
        <div class="residential-subs-area" style="display: none">
            <br>
            <label class="gfield_label"><strong>Choose the payment interval?</strong></label>
            <ul class="es_interval">

                <li>
                    <input name="es_interval" checked class="interval_choice" type="radio" value="quarterly" id="choice_quarterly">
                    <label for="choice_quarterly" id="choice_quarterly"> Quarterly</label>
                </li>
            </ul>
        </div>
        <br>
        <button id="es_submit_service" class="gform_button button btn btn-primary btn-lg">Sign Up for Residential Recycling</button>
		<?php
		return  ob_get_clean();
	}

	/**
	 * @param $template
	 * @param $template_name
	 * @param $template_path
	 * @return mixed|string
	 * @description  Override the default woo templates
	 */
	function locate_template( $template, $template_name, $template_path ) {
		$basename = basename( $template );

		switch ($basename){
			case 'form-checkout.php':
				$template = ES_PLUGIN_PATH . 'templates/form-checkout.php';
				break;
			case 'my-address.php':
				$template = ES_PLUGIN_PATH . 'templates/my-address.php';
				break;
			case 'dashboard.php':
				$template = ES_PLUGIN_PATH . 'templates/dashboard.php';
				break;
            case 'navigation.php':
				$template = ES_PLUGIN_PATH . 'templates/navigation.php';
				break;
			/*case 'related-orders.php':
				$template = ES_PLUGIN_PATH . 'templates/related-orders.php';
				break;*/
		}

		return $template;
	}

	/**
	 *
	 */
	function cptui_register_my_routes() {

		/**
		 * Post Type: Routes.
		 */


		$labels = [
			"name" => esc_html__( "Routes", "custom-post-type-ui" ),
			"singular_name" => esc_html__( "Route", "custom-post-type-ui" ),
			"menu_name" => esc_html__( "My Routes", "custom-post-type-ui" ),
			"all_items" => esc_html__( "All Routes", "custom-post-type-ui" ),
			"add_new" => esc_html__( "Add new", "custom-post-type-ui" ),
			"add_new_item" => esc_html__( "Add new Route", "custom-post-type-ui" ),
			"edit_item" => esc_html__( "Edit Route", "custom-post-type-ui" ),
			"new_item" => esc_html__( "New Route", "custom-post-type-ui" ),
			"view_item" => esc_html__( "View Route", "custom-post-type-ui" ),
			"view_items" => esc_html__( "View Routes", "custom-post-type-ui" ),
			"search_items" => esc_html__( "Search Routes", "custom-post-type-ui" ),
			"not_found" => esc_html__( "No Routes found", "custom-post-type-ui" ),
			"not_found_in_trash" => esc_html__( "No Routes found in trash", "custom-post-type-ui" ),
			"parent" => esc_html__( "Parent Route:", "custom-post-type-ui" ),
			"featured_image" => esc_html__( "Featured image for this Route", "custom-post-type-ui" ),
			"set_featured_image" => esc_html__( "Set featured image for this Route", "custom-post-type-ui" ),
			"remove_featured_image" => esc_html__( "Remove featured image for this Route", "custom-post-type-ui" ),
			"use_featured_image" => esc_html__( "Use as featured image for this Route", "custom-post-type-ui" ),
			"archives" => esc_html__( "Route archives", "custom-post-type-ui" ),
			"insert_into_item" => esc_html__( "Insert into Route", "custom-post-type-ui" ),
			"uploaded_to_this_item" => esc_html__( "Upload to this Route", "custom-post-type-ui" ),
			"filter_items_list" => esc_html__( "Filter Routes list", "custom-post-type-ui" ),
			"items_list_navigation" => esc_html__( "Routes list navigation", "custom-post-type-ui" ),
			"items_list" => esc_html__( "Routes list", "custom-post-type-ui" ),
			"attributes" => esc_html__( "Routes attributes", "custom-post-type-ui" ),
			"name_admin_bar" => esc_html__( "Route", "custom-post-type-ui" ),
			"item_published" => esc_html__( "Route published", "custom-post-type-ui" ),
			"item_published_privately" => esc_html__( "Route published privately.", "custom-post-type-ui" ),
			"item_reverted_to_draft" => esc_html__( "Route reverted to draft.", "custom-post-type-ui" ),
			"item_scheduled" => esc_html__( "Route scheduled", "custom-post-type-ui" ),
			"item_updated" => esc_html__( "Route updated.", "custom-post-type-ui" ),
			"parent_item_colon" => esc_html__( "Parent Route:", "custom-post-type-ui" ),
		];

		$args = [
			"label" => esc_html__( "Routes", "custom-post-type-ui" ),
			"labels" => $labels,
			"description" => "",
			"public" => true,
			"publicly_queryable" => true,
			"show_ui" => true,

			"show_in_rest" => true,
			"rest_base" => "",
			"rest_controller_class" => "WP_REST_Posts_Controller",
			"rest_namespace" => "wp/v2",
			"has_archive" => false,
			"show_in_menu" => true,
			"show_in_nav_menus" => true,
			"delete_with_user" => false,
			"exclude_from_search" => false,
			"capability_type" => "post",
			"map_meta_cap" => true,
			"hierarchical" => false,
			"can_export" => false,
			"rewrite" => [ "slug" => "route", "with_front" => true ],
			"query_var" => true,
			"supports" => [ "title", "editor", "thumbnail" ],
			"show_in_graphql" => false,
		];

		register_post_type( "route", $args );
	}

}

function change_order_and_subscription_status($renewal_order, $subscription) {
  //  var_dump($renewal_order);exit;
	$renewal_order->update_status('processing'); // Set the renewal order status
	$subscription->update_status('active'); // Set the subscription status
	return $renewal_order;
}

<?php
/**
 * ES Subscriptions Plugin
 *
 * @package Yariko
 */

namespace Es\Inc\Base;
/**
 * Class Ajax
 *
 * The Ajax class handles the registration of necessary ajax actions and provides methods for handling ajax requests.
 */
class Ajax {

	/**
	 * Register the necessary ajax actions
	 *
	 * @return void
	 */
	public function register() {
		add_action( 'wp_ajax_es_add_service', array( $this, 'addService' ) );
		add_action( 'wp_ajax_nopriv_es_add_service', array( $this, 'addService' ) );

		add_action( 'wp_ajax_es_add_business', array( $this, 'addBusiness' ) );
		add_action( 'wp_ajax_nopriv_es_add_business', array( $this, 'addBusiness' ) );

		add_action( 'wp_ajax_get_business_account', array( $this, 'getBusinessAccounts' ) );

		add_action( 'wp_ajax_es_approve_deny_business', array( $this, 'approveDenyAccount' ) );

		add_action( 'wp_ajax_es_business_selection', array( $this, 'business_order ' ) );
		add_action( 'wp_ajax_nopriv_es_business_selection', array( $this, 'business_order ' ) );

		add_action( 'wp_ajax_es_add_subaccount', array( $this, 'add_subaccount' ) );
		add_action( 'wp_ajax_nopriv_es_add_subaccount', array( $this, 'add_subaccount' ) );

		add_action( 'wp_ajax_es_remove_subaccount', array( $this, 'remove_sub_account' ) );
		add_action( 'wp_ajax_nopriv_es_remove_subaccount', array( $this, 'remove_sub_account' ) );

		add_action( 'wp_ajax_es_export_users_csv', array( $this, 'export_users_csv' ) );

		add_action( 'wp_ajax_es_list_users', array( $this, 'list_users' ) );

		add_action( 'wp_ajax_es_save_relationship', array( $this, 'save_relationship' ) );
		add_action( 'wp_ajax_nopriv_es_save_relationship', array( $this, 'save_relationship' ) );

		add_action( 'wp_ajax_lineal_graph_data', array( $this, 'lineal_graph_data' ) );

		add_action( 'wp_ajax_series_graph_data', array( $this, 'series_graph_data' ) );

		add_action( 'wp_ajax_nopriv_invoice_info', array($this,'invoice_info'));
		add_action( 'wp_ajax_invoice_info', array($this,'invoice_info'));

		add_action( 'wp_ajax_nopriv_save_payers', array($this,'save_payers'));
		add_action( 'wp_ajax_save_payers', array($this,'save_payers'));
	}

	function save_payers(){
		$payers = $_POST['payers'];
		$user_id = $_POST['user_id'];

		if( empty($user_id) ){
			echo json_encode( array( 'success' => false, 'msg' => 'The change was not processed. Missing User ID!' ) );
			wp_die();
		}


		$payers_formatted = join( ',', $payers );

		if( empty( $payers_formatted ) ){
			$payers_formatted = '';
		}

		update_field('es_payers_cc', $payers_formatted, 'user_' . $user_id );

		echo json_encode( array( 'success' => true, 'payers' =>  $payers, 'msg' => 'Saved!' ) );
		wp_die();
	}

	function invoice_info(){

		$stop = $_POST['stop'];
		$user_id = $_POST['user_id'];

		update_user_meta( intval( $user_id ), 'stop_invoice_notification',  $stop === 'true' ? 1 : 0 );
		update_field('es_stop_notifications', $stop === 'true' ? 'no' : 0, 'user_' . $user_id );

		echo json_encode( array( 'success' => true, 'stop' => $stop === 'true' ? 1 : 0  ) );
		wp_die();
	}

	function SeriesGraphData (){

		echo json_encode(array( 'success' => true, 'data' => $this->getMonthOrders()));
		wp_die();
	}

	/**
	 * Return the series graph data in a json response
	 *
	 * @return void
	 */
	public function series_graph_data() {

		echo wp_json_encode(
			array(
				'success' => true,
				'data'    => $this->get_months_order(),
			)
		);
		wp_die();
	}

	/**
	 * Get the order by month
	 *
	 * @return array
	 */
	public function get_months_order() {

		$month_orders  = array();
		$current_month = intval( gmdate( 'n' ) );
		$current_year  = gmdate( 'Y' );

		$months = array(
			'January'   => 1,
			'February'  => 2,
			'March'     => 3,
			'April'     => 4,
			'May'       => 5,
			'June'      => 6,
			'July'      => 7,
			'August'    => 8,
			'September' => 9,
			'October'   => 10,
			'November'  => 11,
			'December'  => 12,
		);

		foreach ( $months as $label => $id ) {
			$month = $id;
			if ( $id <= $current_month ) {
				if ( 12 !== $id ) {
					$month = '0' . $id;
				}
				$month .= '';
				$args = array(
					'numberposts' => -1,
					'post_status' => array( 'wc-completed', 'wc-processing' ),
					'post_type'   => 'shop_order',
					'date_query'  => array(
						array(
							'year'  => $current_year,
							'month' => $month,
						),
					),
				);

				$orders = get_posts( $args );
				$monthly_revenue = 0;

				foreach ( $orders as $post ) {
					$order = wc_get_order($post->ID);
					$monthly_revenue += $order->get_total();
				}

				$month_orders[] = array(
					'month' => $label,
					'value' => $monthly_revenue,
				);

			}
		}

		return $month_orders;
	}

	/**
	 * Get the data for the Outstanding invoice report
	 *
	 * @return void
	 */
	public function lineal_graph_data() {

		$orders                 = get_posts(
			array(
				'post_type'   => 'shop_order',
				'post_status' => array( 'wc-failed', 'wc-pending', 'wc-on-hold' ),
				'numberposts' => -1,
			)
		);
		$balance                = 0;
		$count_outstanding_30   = 0;
		$count_outstanding_3160 = 0;
		$count_outstanding_6190 = 0;
		$count_outstanding_91   = 0;
		$overdue                = 0;
		$balance_upto_30        = 0;
		$balance_31_60          = 0;
		$balance_61_90          = 0;
		$balance_91             = 0;
		foreach ( $orders as $customer_order ) {
			//if ( 'wc-pending' === $customer_order->post_status ) {
				$order = wc_get_order( $customer_order->ID );

				if ( $order ) {
					$created_date = $order->order_date;
					$today        = gmdate( 'Y-m-d H:i:s' );
					$order_total  = intval( $order->get_total() );

					$balance += $order_total;
					$days     = floor( ( strtotime( $today ) - strtotime( $created_date ) ) / 86400 );

					if ( $days > 15 ) {
						$overdue += $order_total;
					}

					if ( $days < 30 ) {
						$balance_upto_30 += $order_total;
						++$count_outstanding_30;
					}

					if ( $days > 30 && $days <= 60 ) {
						$balance_31_60 += $order_total;
						++$count_outstanding_3160;
					}

					if ( $days > 60 && $days <= 90 ) {
						++$count_outstanding_6190;
						$balance_61_90 += $order_total;
					}

					if ( $days > 60 && $days <= 90 ) {
						++$count_outstanding_91;
						$balance_91 += $order_total;
					}
				}
			//}
		}

		echo wp_json_encode(
			array(
				'km'          => es_km( $balance ),
				'success'     => true,
				'count'       => count( $orders ),
				'outstanding' => $balance,
				'overdue'     => $overdue,
				'term_30'     => $balance_upto_30,
				'term_3160'   => $balance_31_60,
				'term_6190'   => $balance_61_90,
				'term_91'     => $balance_91,
				'count_30'    => $count_outstanding_30,
				'count_3160'  => $count_outstanding_3160,
				'count_6190'  => $count_outstanding_6190,
				'count_91'    => $count_outstanding_91,
			)
		);
		wp_die();
	}

    function remove_sub_account(){
        $user_id = $_POST['user_id'];

        if(wp_delete_user( $user_id )){

            $accounts_serialized = get_user_meta($user_id, 'subaccount_ids', true);
            $accounts = $accounts_serialized && count(unserialize($accounts_serialized)) > 0 ? unserialize($accounts_serialized) : [];
            $key = array_search($user_id, $accounts);
            if ($key !== false) {
                unset($accounts[$key]);
                update_user_meta($user_id,'subaccount_ids',serialize($accounts));
            }

            echo json_encode(array('success' => true, 'msg' => 'Account removed'));
            wp_die();
        }

        echo json_encode(array('error' => true, 'msg' => 'The user was not removed, try it later on!'));
        wp_die();

    }

    function add_subaccount(){
        $user_id = $_POST['user_id'];
        $email = $_POST['email'];
        $name = $_POST['name'];
        $phone = $_POST['phone'];

		$account_id = null;
		$sent_email = false;

        if(email_exists($email) ){

			$user = get_user_by('email', $email);

			if( in_array( 'business' , $user->roles ) ){
				echo json_encode(array('error' => true, 'msg' => 'This user is a primary account and cannot be added as sub-account.'));
				wp_die();
			}

			$main_account_id = get_user_meta($account_id,'main_account_id', true);

			if( ! empty( $main_account_id ) ){
				echo json_encode(array('error' => true, 'msg' => 'This user is already assigned to another primary account.'));
				wp_die();
			}

			$account_id = $user->ID;

        }else{

			$password = es_generate_string(12);



			$account_id = wp_insert_user( array('user_pass' => $password, 'user_email' => $email, 'first_name' => $name, 'user_login' => $email) );

			if(is_wp_error($account_id)){
				echo json_encode(array('error' => true, 'msg' => 'There is already an user with that email, please select other one'));
				wp_die();
			}

			$sent_email = true;

		}

        $new_user = get_user_by( 'ID', $account_id );
        $new_user->add_role( 'sub-account' );
		$accounts_serialized = get_user_meta($user_id, 'subaccount_ids', true);
		$accounts = $accounts_serialized && count(unserialize($accounts_serialized)) > 0 ? unserialize($accounts_serialized) : [];
        $accounts[] = $account_id;

        //Create the relation sub-account / main account
        update_user_meta($account_id,'main_account_id',$user_id);
        //Create the relation main account / sub-accounts
        update_user_meta($user_id,'subaccount_ids',serialize($accounts));
        update_user_meta($account_id,'user_phone',$phone);

        $data = array('name' => $name, 'link' => site_url('/my-account'), 'password' => $password, 'email' => $email);

        if( $sent_email ) {
			$email_sent = sendSubaccountCredential($email, $data);;
		}

        echo json_encode(array('success' => true, 'email_sent' => $email_sent, 'msg' => 'The account was created'));
        wp_die();
    }

    function approveDenyAccount(){
        $user_id = $_POST['user_id'];
        $action_account = $_POST['action_account'];

        if($action_account === 'approve'){
            update_user_meta($user_id, '_es_status', 'Approved');

            $user = get_user_by('id', $user_id);

            if($user){

                $data = [];

                $pass = get_user_meta($user_id, '_es_pass', true);

                $data['link'] = site_url() . '/business-shop?user_id=' . $user_id . '&token=' . $pass;
                $data['name'] = $user->display_name;
                $data['email'] = $user->user_email;
                $data['token'] = $pass;
                $data['my_account'] = site_url() . '/my-account';

				echo json_encode(array('success' => true));
				wp_die();

                /*if(es_send_business_shop_email($user->user_email, $data)){
                    echo json_encode(array('success' => true));
                    wp_die();
                }else{
                    echo json_encode(array('success' => false, 'msg' => 'The account was approved but the email was not sent to the customer, please try again in a couple minutes'));
                    wp_die();
                }*/

            }




        }else{
            update_user_meta($user_id, '_es_status', 'Denied');
        }

        echo json_encode(array('success' => true));
        wp_die();
    }

    function getBusinessAccounts(){

        $number      = $_POST['length'];
        $offset      = $_POST['start'];
        $users       = get_users(array(
            'role'    => 'business',
            'orderby' => 'ID',
        ));
        $query       = get_users(array(
            'role'    => 'business',
            'orderby' => 'ID',
            'order'   => 'DESC',
            'offset' => $offset,
            'number' => $number,
            'search' => '*'.$_POST['search'].'*',
            'search_columns' => array( 'user_login', 'user_email' )
        ));
        $total_users = count($users);
        // $total_pages = intval($total_users / $number) + 1;

        $users_formatted = [];

        foreach ($query as $user){

            $user_obj = [];

            $user_obj['id'] = $user->ID;
            $user_obj['company'] = get_user_meta($user->ID, '_es_company',true);
            $user_obj['pass'] = get_user_meta($user->ID, '_es_pass',true);
            $user_obj['business_type'] = get_user_meta($user->ID, '_es_business_type',true);
            $user_obj['address'] = get_user_meta($user->ID, '_es_address',true);
            $user_obj['referred'] = get_user_meta($user->ID, '_es_referred',true);
            $user_obj['phone'] = get_user_meta($user->ID, '_es_phone',true);
            $user_obj['description'] = get_user_meta($user->ID, '_es_description',true);
            $user_obj['status'] = get_user_meta($user->ID, '_es_status',true) ? get_user_meta($user->ID, '_es_status',true) : 'Pending';
            $user_obj['email'] = $user->data->user_email;
            $user_obj['name'] = $user->data->display_name;

            $users_formatted[] = $user_obj;

        }

        echo json_encode(array('success' => true, 'users' => $users_formatted, 'recordsFiltered' => $total_users ));
        wp_die();
    }

    function addBusiness(){

        //Getting the post fields
        $first = $_POST['first'];
        $last = $_POST['last'];
        $company = $_POST['company'];
        $business_type = $_POST['business_type'];
        $email = $_POST['email'];
        $phone = $_POST['phone'];
        $address = $_POST['address'];
        $referred = $_POST['es_referred'];
        $description = $_POST['description'];
        $number_of_employee = $_POST['es_number_of_employees'];
        $es_find = $_POST['es_find'];
        $es_items = $_POST['es_items'];

        if(email_exists($email)){
            echo json_encode(array('success' => false, 'msg' => 'The email already exists' ));
            wp_die();
        }

        $password = wp_generate_password(15, false, false);

        $userdata = array(
            'user_login' 			=> $email,
            'user_email' 			=> $email,
            'user_pass'             => $password,
            'display_name' 			=> $first . ' ' . $last,
            'nickname' 				=> $first . ' ' . $last,
            'first_name' 			=> $first,
            'last_name' 			=> $last,
            'role' 					=> 'business',
        );

        $user_id = wp_insert_user($userdata);

        if ( ! is_wp_error( $user_id ) ) {
            $user = get_user_by( 'ID', $user_id );
            $user->add_role( 'business' );

            add_user_meta($user_id, '_es_company', $company);
            add_user_meta($user_id, '_es_business_type', $business_type);
            add_user_meta($user_id, '_es_address', $address);
            add_user_meta($user_id, '_es_referred', $referred);
            add_user_meta($user_id, '_es_description', $description);
            add_user_meta($user_id, '_es_phone', $phone);
            add_user_meta($user_id, '_es_number_of_employees', $number_of_employee);
            add_user_meta($user_id, '_es_items', $es_items);
            add_user_meta($user_id, '_es_find', $es_find);
            add_user_meta($user_id, '_es_user_created', date("m/d/Y"));

            $userdata['company'] = $company;
            $userdata['business_type'] = $business_type;
            $userdata['address'] = $address;
            $userdata['phone'] = $phone;
            $userdata['number_of_employees'] = $number_of_employee;
            $userdata['referred'] = $referred;
            $userdata['description'] = $description;
            $userdata['items'] = $es_items;
            $userdata['find'] = $es_find;
            $userdata['user_id'] = $user_id;

            add_user_meta($user_id, '_es_pass', $password);

            //todo we need to send other email to the customer and change the above email for admin one since the above one is fro the admin
            if(es_send_admin_business_request_email(['office@earthsavers.org', 'dev@thomasgbennett.com'], $userdata)){
                echo json_encode(array('success' => true, 'msg' => 'The account was added, an email was sent to the admin' ));
                wp_die();
            }

        }else{
            echo json_encode(array('success' => false, 'msg' => 'The account was added ' . $email ));
            wp_die();
        }

        echo json_encode(array('success' => true, 'msg' => 'The account was added, it looks like the email was not sent to the admin' ));
        wp_die();
    }

    /**
     * Add Service to the cart
     */
    function addService(){

        $product_id = $_POST['id'];
        $interval = $_POST['interval'];

        $product = wc_get_product($product_id);
        $variations = $product->get_available_variations();

        if(count($variations > 0)){
            foreach ($variations as $variation){
                if($variation['attributes']['attribute_pa_billing-period'] == $interval){
                    WC()->cart->empty_cart();
                    WC()->cart->add_to_cart( $variation['variation_id'], 1);
                    echo json_encode(array('success' => true, 'variations' => $interval  ));
                    wp_die();
                }
            }
        }

        echo json_encode(array('success' => false, 'variations' => [] ));
        wp_die();
    }

    function business_order(){
        $collect_inside = $_POST['collect_inside'];

        WC()->cart->empty_cart();

        //Adding the initial product fee
        WC()->cart->add_to_cart( 6225 , 1);

        if($collect_inside === 'yes'){
            WC()->cart->add_to_cart( 6226 , 1);
        }

        echo json_encode(array('success' => true));
        wp_die();

    }

    function export_users_csv(){

        //Remove previous exports
        $files = glob(ES_PLUGIN_PATH . '/uploads/exports/*'); // get all file names
        foreach($files as $file){ // iterate files
            if(is_file($file)) {
                unlink($file); // delete file
            }
        }

        $users = get_users( array( 'role__in' => array( 'business', 'sub-account'), 'orderby' => 'ID'  ) );

        $data = [['ID', 'Email', 'Account Type', 'Parent Email']];
        foreach ($users as $user){

            $parent_email = '';

            if(in_array('sub-account' , $user->roles) && !in_array('administrator' , $user->roles)){
                $parent_id = get_user_meta($user->ID,'main_account_id', true);
                $parent_account = get_user_by('ID', $parent_id);
                if($parent_account){ $parent_email = $parent_account->user_email; }
            }

            $data[] = [
                $user->ID,
                $user->user_email,
                in_array('sub-account' , $user->roles) ? 'Secondary' : 'Primary',
                $parent_email
            ];
        }

        $path_name =   'account_export-' .  time() .'.csv';
        $csv_file_name = ES_PLUGIN_PATH . '/uploads/exports/'. $path_name ;
        $f = fopen($csv_file_name, 'w');

        foreach ($data as $row) {
            fputcsv($f, $row);
        }

        fclose($f);

        echo json_encode(array('success' => true,'path'=> ES_PLUGIN_URL . '/uploads/exports/' . $path_name));
        wp_die();

    }

	function list_users(){

		$search = trim($_POST['search']);

		global $wpdb;

		$users = $wpdb->get_results("SELECT * from " . $wpdb->prefix . "users U INNER JOIN " . $wpdb->prefix . "usermeta UM ON U.ID = UM.user_id
											WHERE U.user_email LIKE '%$search%' OR  U.display_name LIKE '%$search%'  OR (UM.meta_key IN ('_es_company', 'billing_company', '_es_address', 'billing_address_1') AND UM.meta_value LIKE '%$search%')
											 GROUP BY U.ID LIMIT 20");

		foreach ($users as $user){
			$es_company = get_user_meta($user->ID, '_es_company', true);
            $billing_company = get_user_meta($user->ID, 'billing_company', true);

            if (!empty($es_company)) {
                $user->es_company = $es_company;
            } elseif (!empty($billing_company)) {
                $user->es_company = $billing_company;
            } else {
                $user->es_company = "";
            }
			//$user->es_business_type = get_user_meta($user->ID, '_es_business_type', true);
			$user->es_address = get_user_meta($user->ID, '_es_address', true);
			if( empty($user->es_address) ){
				$user->es_address = get_user_meta($user->ID, 'billing_address_1', true);
			}
			//$user->es_referred = get_user_meta($user->ID, '_es_referred', true);
			//$user->es_description = get_user_meta($user->ID, '_es_description', true);
			//$user->es_phone = get_user_meta($user->ID, '_es_phone', true);
			//$user->es_number_of_employees = get_user_meta($user->ID, '_es_number_of_employees', true);
			//$user->es_items = get_user_meta($user->ID, '_es_items', true);
			//$user->es_find = get_user_meta($user->ID, '_es_find', true);

		}

		echo json_encode(array('success' => true,'users' => $users));
		wp_die();
	}

	function save_relationship(){
		$note = $_POST['note'];
		$user_id = $_POST['user_id'];

		update_user_meta($user_id,'es_customer_relationship',$note);
		update_user_meta($user_id,'description',$note);

		echo json_encode(array('success' => true));
		wp_die();

	}
}

<?php

/*
*
* @package Yariko
*
*/

namespace Es\Inc\Base;

class Pages{

    public function register(){

        add_action('admin_menu', function(){
            add_menu_page('Earth Savers Subs', 'Earth Savers Subs', 'edit_posts', 'es-main', array($this,'businessAccounts') , 'dashicons-universal-access',110);
            $users_page = add_menu_page('Customer Support ', 'Customer Support ', 'edit_posts', 'es-users', array($this,'users') , 'dashicons-analytics',110);

	        add_action( 'load-' . $users_page, function(){
		        add_action( 'admin_enqueue_scripts',function (){
			        wp_enqueue_script('main-js', ES_PLUGIN_URL  . '/assets/js/main.js' ,array('jquery'),'1.0', false);
			        wp_localize_script( 'main-js', 'parameters', ['ajax_url'=> admin_url('admin-ajax.php'), 'plugin_url' => ES_PLUGIN_URL]);
		        });
	        });
        });

        add_action('admin_menu',function(){
            $new_page =  add_submenu_page( 'es-main', __('Business Accounts','es_subscriptions'), __('Business Accounts','es_subscriptions'),'edit_posts', 'es-main', array($this,'businessAccounts'));
            $subs_import =  add_submenu_page( 'es-main', __('Import','es_subscriptions'), __('Import','es_subscriptions'),'edit_posts', 'es-import', array($this,'import'));
            $subs_export =  add_submenu_page( 'es-main', __('Export Accounts','es_subscriptions'), __('Export Accounts','es_subscriptions'),'edit_posts', 'es-export', array($this,'exportAccounts'));

            add_action( 'load-' . $new_page, function(){
                add_action( 'admin_enqueue_scripts',function (){

                    wp_enqueue_style('es-bootstrap-css', ES_PLUGIN_URL . '/assets/css/admin/bootstrap.min.css');

                    wp_enqueue_style('es-app-css', ES_PLUGIN_URL . '/dist/app.css'  );
                    wp_enqueue_style('es-vendors-css', ES_PLUGIN_URL . '/dist/vendors.css'  );
                   // wp_enqueue_script( 'es-bootstrap-js', ES_PLUGIN_URL . '/assets/js/admin/bootstrap.bundle.min.js');
                    wp_enqueue_style('main_admin_styles',  ES_PLUGIN_URL . '/assets/css/admin/main.css' );

                    wp_enqueue_script( 'es-runtime-js', ES_PLUGIN_URL . '/dist/runtime.wec.bundle.js', '1.00', true);
                    wp_enqueue_script( 'es-vendors-js', ES_PLUGIN_URL . '/dist/vendors.wec.bundle.js', array('es-runtime-js'),'1.00', true);

                    wp_enqueue_script( 'es-app-js', ES_PLUGIN_URL . '/dist/app.wec.bundle.js', array('es-runtime-js', 'es-vendors-js'),'1.00', true);

                    $args = array(
                        'ajax_url'=> admin_url('admin-ajax.php'),
                        'plugin_url' => ES_PLUGIN_URL,
                        'plugin_path' => ES_PLUGIN_URL,
                    );
                    wp_localize_script( 'es-app-js', 'es_parameters ', $args );

                });
            });

            add_action( 'load-' . $subs_import, function(){
                add_action( 'admin_enqueue_scripts',function (){

                    wp_enqueue_style('es-bootstrap-css', ES_PLUGIN_URL . '/assets/css/admin/bootstrap.min.css');
                    wp_enqueue_script( 'es-bootstrap-js', ES_PLUGIN_URL . '/assets/js/admin/bootstrap.bundle.min.js');

                });
            });

            add_action( 'load-' . $subs_export, function(){
                add_action( 'admin_enqueue_scripts',function (){

                    wp_enqueue_style('es-bootstrap-css', ES_PLUGIN_URL . '/assets/css/admin/bootstrap.min.css');
                    wp_enqueue_script( 'es-bootstrap-js', ES_PLUGIN_URL . '/assets/js/admin/bootstrap.bundle.min.js');

                    wp_enqueue_script('toastr-js', 'https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js');
                    wp_enqueue_script('pristine-js', ES_PLUGIN_URL  . '/assets/js/pristine.min.js');

                    wp_enqueue_script('main-js', ES_PLUGIN_URL  . '/assets/js/main.js' ,array('jquery', 'toastr-js', 'pristine-js'),'1.0', false);
                    wp_localize_script( 'main-js', 'parameters', ['ajax_url'=> admin_url('admin-ajax.php'), 'plugin_url' => ES_PLUGIN_URL]);

                });
            });

        });

    }


    /* function products(){
         require_once WRPL_PLUGIN_PATH . 'templates/products.php';
     }*/

    function businessAccounts(){
        ?>
        <style>
            #wpcontent {
                padding-left: 0 !important;
            }
            #wrpl-app{
            }
        </style>
        <div id="es-app"></div>
        <?php
    }

    function import(){
        $content = es_template(ES_PLUGIN_PATH . '/templates/import.php',array());
        echo $content;
    }

    function exportAccounts(){
        $content = es_template(ES_PLUGIN_PATH . '/templates/export-accounts.php',array());
        echo $content;
    }

    function getRecentTickets(){
	    $args = array(
		    'post_type'              => 'ticket_reply',
		    'post_status'            => array('read', 'unread', 'closed'),
		    'order'                  => 'DESC',
		    'orderby'                => 'modified',
		    'posts_per_page'         => 50,
		    'no_found_rows'          => false,
		    'cache_results'          => false,
		    'update_post_term_cache' => false,
		    'update_post_meta_cache' => false,
	    ) ;

	    $tickets = get_posts( $args );

        $replies = array();

        foreach ($tickets as $ticket){
	        $user = get_userdata( $ticket->post_author );

	        $user_roles = $user->roles;

	        if ( true ) {
                $parent_ticket = get_post($ticket->post_parent);
                array_push($replies, array(
                        'id' => $ticket->post_parent,
                        'user_name' => $user->display_name,
                        'ticket_name' => $parent_ticket->post_title,
                        'time_ago' => es_timeago($ticket->post_modified_gmt)
                ));
	        }
        }


        return $replies;
    }

    function getTicketTotalStatus(){

	    $args['meta_query'][] = array(
		    'key'     => '_wpas_assignee',
		    'compare' => 'NOT EXISTS',
	    );

        return array(
            'new' => wpas_get_ticket_count_by_status(array('queued'), 'open'),
            'hold' => wpas_get_ticket_count_by_status(array('hold'), 'open'),
            'processing' => wpas_get_ticket_count_by_status(array('processing'), 'open'),
            'unassigned' => wpas_get_ticket_count_by_status('', 'open', $args)
        );

    }

    function users(){

	    global $woocommerce;

        $user_id = isset($_GET['user_id']) ? $_GET['user_id'] : -1;
        $user = null;
        if($user_id != -1){
            $user = get_user_by('ID', $user_id);
	        $user->es_company = get_user_meta($user->ID, '_es_company', true);
	        $user->es_business_type = get_user_meta($user->ID, '_es_business_type', true);
	        $user->es_address = !empty(get_user_meta($user->ID, '_es_address', true)) ? get_user_meta($user->ID, '_es_address', true)  : '';
	        $user->es_referred = get_user_meta($user->ID, '_es_referred', true);
	        $user->es_description = get_user_meta($user->ID, '_es_description', true);
	        $user->es_phone = !empty(get_user_meta($user->ID, '_es_phone', true)) ? get_user_meta($user->ID, '_es_phone', true) : get_user_meta( $user->ID, 'billing_phone', true );
	        $user->es_number_of_employees = get_user_meta($user->ID, '_es_number_of_employees', true);
	        $user->es_items = get_user_meta($user->ID, '_es_items', true);
	        $user->es_business_type = get_user_meta($user->ID, '_es_business_type', true);
	        $user->es_user_created = get_user_meta($user->ID, '_es_user_created', true);

	        $user->es_find = get_user_meta($user->ID, '_es_find', true);
	        $user->status = get_user_meta($user->ID, '_es_status',true) ? get_user_meta($user->ID, '_es_status',true) : -1;
            $user->type = in_array('business', $user->roles) ? 'Business' : 'Residential';
            $user->relationship = get_user_meta($user->ID, 'description', true);

	        $user->avatar = $this->get_user_avatar($user->display_name);

            if(empty($user->es_address)){
	            $user->es_address .= get_user_meta( $user->ID, 'billing_address_1', true ) ?: '';
	            $user->es_address .= get_user_meta( $user->ID, 'billing_address_2', true ) ? ' ' . get_user_meta( $user->ID, 'billing_address_2', true ) : '';
	            $user->es_address .= get_user_meta( $user->ID, 'billing_city', true ) ? ' ' . get_user_meta( $user->ID, 'billing_city', true ) : '';
	            $user->es_address .= get_user_meta( $user->ID, 'billing_state', true) ? ', ' . get_user_meta( $user->ID, 'billing_state', true ) : '';
	            $user->es_address .= get_user_meta( $user->ID, 'billing_postcode', true ) ? ' ' . get_user_meta( $user->ID, 'billing_postcode', true ) : '';
            }

            if(empty($user->es_company)){
	            $user->es_company = get_user_meta( $user->ID, 'billing_company', true );
            }

            //Routes
	        $routes = get_field('route_id', 'user_' . $user->ID);
	        $user->routes = '';
	        if($routes && count($routes) > 0){
		        foreach ($routes as $route){
			        $recycling_collection_interval = get_field('recycling_collection_interval', $route->ID);
			        $recycling_collection_date = get_field('recycling_collection_date', $route->ID);

			        $user->routes .= $route->post_title . ' ';
		        }
            }
            if(empty($user->routes)){ $user->routes = 'None'; }

            $user_balance = 0;

            //ORDERS
	        $customer_orders = get_posts( array(
		        'meta_key'    => '_customer_user',
		        'meta_value'  => $user_id,
		        'post_type'   => 'shop_order',
		        'post_status' => array_keys( wc_get_order_statuses() ),
		        'numberposts' => -1
	        ));

            foreach ($customer_orders as $customer_order){
                if($customer_order->post_status == 'wc-pending'){
	                if($order = wc_get_order($customer_order->ID)){
                        $user_balance += intval($order->get_total());
                    }
                }
            }

            $user->balance = $user_balance;

            $user->orders = $customer_orders;

            //TICKETS
	        $args = array(
		        'author'                 => $user->ID,
		        'post_type'              => 'ticket',
		        'post_status'            => 'open',
		        'order'                  => 'DESC',
		        'orderby'                => 'date',
		        'posts_per_page'         => -1,
		        'no_found_rows'          => false,
		        'cache_results'          => false,
		        'update_post_term_cache' => false,
		        'update_post_meta_cache' => false,
	        ) ;

            $user->tickets = get_posts( $args );

            //SUBSCRIPTION
            $subscriptions = wcs_get_subscriptions(array(
	            'customer_id'=> $user->ID
            ));

            $user->subscriptions = $subscriptions;

        }

        if(get_current_user_id() == 6){
	       // $this->getRecentTickets();
        }

        $report = [];

	    $report['ticket_status_counts'] = $this->getTicketTotalStatus();
        $report['ticket_activities'] = $this->getRecentTickets();
        $report['today_total'] = $this->getTodayTotal();


	    $content = es_template(ES_PLUGIN_PATH . '/templates/users.php',array('user' => $user, 'report' => $report));
	    echo $content;
    }

    function getTodayTotal(){
	    $current_month = intval(date('n'));
	    $current_year = date('Y');
	    $current_day = date('d');
	    $args = array(
		    // WC orders post type
		    'post_type'   => 'shop_order',
		    'post_status' => array( 'wc-completed', 'wc-processing' ),
		    'numberposts' => -1,
		    'date_query' => array(
			    array(
				    'year' => intval($current_year),
				    'month' => $current_month,
                    'day' => 7
			    ),
		    ),
	    );

	    $orders = get_posts( $args );
	    $order_total = 0;

	    foreach ($orders as $order){
		    $order = wc_get_order($order->ID);
		    $order_total += intval($order->get_total());
	    }

        return wc_price($order_total);
    }

	function get_user_avatar($name){
		$words = explode(' ', $name);
		$avatar = '';
		foreach ($words as $word){
			$avatar .= strtoupper(substr($word, 0, 1));
		}


		return $avatar;
	}


}
?>

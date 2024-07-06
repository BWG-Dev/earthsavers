<?php

/*
*
* @package Yariko
*
*/

namespace Es\Inc\Base;

class Enqueue{

    public function register(){

        add_action( 'wp_enqueue_scripts',  array($this,'enqueue_frontend'));


	    add_action('admin_enqueue_scripts', array($this, 'admin_style'));

    }

    /**
     * Enqueueing the main scripts with all the javascript logic that this plugin offer
     */
    function enqueue_frontend(){
        wp_enqueue_style('toastr-css', 'https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css');
        wp_enqueue_style('main-css', ES_PLUGIN_URL . '/assets/css/main.css');


        wp_enqueue_script('toastr-js', 'https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js');

        wp_enqueue_script('pristine-js', ES_PLUGIN_URL  . '/assets/js/pristine.min.js');
        wp_enqueue_script('main-js', ES_PLUGIN_URL  . '/assets/js/main.js' ,array('jquery', 'toastr-js', 'pristine-js'),'v-' . strtotime(date('h:i:s')), true);

        wp_localize_script( 'main-js', 'parameters', ['ajax_url'=> admin_url('admin-ajax.php'), 'plugin_url' => ES_PLUGIN_URL]);

    }

	function admin_style() {
		global $current_user;
		if(in_array('wpas_support_manager',$current_user->roles)){
			wp_enqueue_style('admin-styles', ES_PLUGIN_URL .'/assets/css/admin/main.css');
		}

		wp_enqueue_style('full-admin-styles', ES_PLUGIN_URL .'/assets/css/admin/admin.css');

		wp_enqueue_script('chart-js', 'https://cdn.amcharts.com/lib/5/index.js');
		wp_enqueue_script('chart1-js', 'https://cdn.amcharts.com/lib/5/xy.js');
		//wp_enqueue_script('chart2-js', 'https://cdn.amcharts.com/lib/5/themes/Animated.js');

		wp_enqueue_script('main-admin-js', ES_PLUGIN_URL .'/assets/js/admin/main.js', array('jquery', 'chart-js', 'chart1-js','chart2-js'), 'v-' . strtotime(date('h:i:s')));

	}

}

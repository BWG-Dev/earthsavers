<?php
/*
*
* @package yariko


Plugin Name:  Earth Savers Subscriptions
Plugin URI:   https://thomasgbennett.com/
Description:  This plugin implement a subscription flow to support the Earth Savers business model
Version:      1.0.0
Author:       Bennet Group
Author URI:   https://thomasgbennett.com/
Tested up to: 6.0.1
Text Domain:  es_subscriptions
Domain Path:  /languages
*/

defined('ABSPATH') or die('You do not have access, sally human!!!');

define ( 'ES_PLUGIN_VERSION', '1.0.0');

if( file_exists( dirname( __FILE__ ) . '/vendor/autoload.php') ){
    require_once  dirname( __FILE__ ) . '/vendor/autoload.php';
}

define('ES_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define('ES_PLUGIN_URL' , plugin_dir_url(  __FILE__  ) );
define('ES_ADMIN_URL' , get_admin_url() );
define('ES_PLUGIN_DIR_BASENAME' , dirname(plugin_basename(__FILE__)) );

//include the helpers
include 'inc/util/helpers.php';
if( class_exists( 'Es\\Inc\\Init' ) ){
	
    register_activation_hook( __FILE__ , array('Es\\Inc\\Base\\Activate','activate') );
    Es\Inc\Init::register_services();
}




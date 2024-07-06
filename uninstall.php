<?php
/*
* Trigger this file  on Plugin Uninstall
*
* @package yariko
*/

if( ! defined('WP_UNINSTALL_PLUGIN') ){
    die;
}

global $wpdb;
/*$table_name1 = $wpdb->prefix . 'wr_price_lists';
$wpdb->query('DELETE FROM ' .$wpdb->prefix . 'options WHERE option_name LIKE "wrpl%" AND  option_name NOT LIKE "wrpl_role%"');
$wpdb->query( "DROP TABLE IF EXISTS $table_name1" );*/

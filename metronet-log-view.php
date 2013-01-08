<?php
/*
Plugin Name: Metronet Log Views
Plugin URI: http://wordpress.org/extend/plugins/metronet-log/
Description: Used in conjunction with Metronet Log, provides a view for your log data
Author: Metronet
Version: 0.1
Requires at least: 3.5
Author URI: http://www.metronet.no
Contributors: ronalfy, metronet
*/ 
class Metronet_Log_Views {
	public function __construct() {
		add_action( 'init', array( $this, 'init' ) );
	} //end constructor
	
	public function init() {
		load_plugin_textdomain( 'metronet_log', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	} //end init
	

} //end class Metronet_Log_Views

function metronet_log_views_instantiate() {
	$mt_log_views = new Metronet_Log_Views();
} //end metronet_log_views_instantiate
add_action( 'metronet_log_loaded', 'metronet_log_views_instantiate' );

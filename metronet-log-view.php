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
		add_action( 'admin_menu', array( $this, 'add_menus' ) );
		
		//For the settings link
		$plugin = plugin_basename(__FILE__); 
		add_filter("plugin_action_links_$plugin", array( $this, 'settings_link' ) );
	} //end constructor
	
	/**
	* add_menus()
	*
	* Adds a menu with a callback to tools.php in the admin panel
	*
	* @uses action admin_menu
	*
	**/
	public function add_menus() {
		$metronet_log_title = apply_filters( 'metronet_log_menu_title', __( 'Metronet Log', 'metronet_log' ) ); //Filterable for devs
		$metronet_log_slug = apply_filters( 'metronet_log_menu_slug', 'metronet-log' ); //Also filterable for devs
		add_submenu_page( 'tools.php', $metronet_log_title, $metronet_log_title, 'manage_options', $metronet_log_slug, array( $this, 'output_list_table' ) );
	} //end add_menus
	
	/**
	* init()
	*
	* Initializes languages
	*
	* @uses action init
	*
	**/
	public function init() {
		load_plugin_textdomain( 'metronet_log', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	} //end init
	
	/**
	* settings_link()
	*
	* Adds a logging link
	*
	* @uses action plugin_action_links_{$plugin_name}
	*
	* @param array $links - Array of links
	* @returns array $links - New array of links
	*
	**/
	public function settings_link( $links ) {
		$metronet_log_slug = apply_filters( 'metronet_log_menu_slug', 'metronet-log' ); //Also filterable for devs
		$settings_link = sprintf( '<a href="tools.php?page=%s.php">%s</a>', $metronet_log_slug, esc_html__( 'View Logs', 'metronet_log' ) );; 
		array_unshift($links, $settings_link); 
		return $links;
	} //end settings_link
	
	/**
	* output_list_table()
	*
	* Outputs the list table for the logs - Callback from add_submenu_page in method add_menus
	*
	**/
	public function output_list_table() {
		if( !class_exists( 'WP_List_Table' ) ) {
			require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
		}
		include( 'class-metronet-log-list-table.php' );
		$metronet_logs_list_table = new Metronet_Logs_List_Table();
		//todo - not quite ready
		//$metronet_logs_list_table->prepare_items();
		//$metronet_logs_list_table->display();
	} //end output_list_table
	

} //end class Metronet_Log_Views

function metronet_log_views_instantiate() {
	$mt_log_views = new Metronet_Log_Views();
} //end metronet_log_views_instantiate
add_action( 'metronet_log_loaded', 'metronet_log_views_instantiate' );

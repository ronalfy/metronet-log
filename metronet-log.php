<?php
/*
Plugin Name: Metronet Log
Plugin URI: http://wordpress.org/extend/plugins/metronet-log/
Description: Provides a WordPress API for date-based data storage based on a user ID.
Author: Metronet
Version: 1.0
Requires at least: 3.5
Author URI: http://www.metronet.no
Contributors: ronalfy, metronet
*/ 

class Metronet_Log	{	
	
	//private
	private $plugin_url = '';
	private $plugin_dir = '';
	private $plugin_path = '';
	private $tablename = '';
	
	/**
	* __construct()
	* 
	* Class constructor
	*
	*/
	public function __construct(){
		global $wpdb;		
		$this->plugin_path = plugin_basename( __FILE__ );
		$this->plugin_url = rtrim( plugin_dir_url(__FILE__), '/' );
		$this->plugin_dir = rtrim( plugin_dir_path(__FILE__), '/' );
		$this->tablename = Metronet_Log::get_table_name();
	} //end constructor
	
	/**
	* add_log_value()
	*
	* Inserts a log item into the database
	*
	* @param int $user_id The user id
	* @param string $type The type of data to add
	* @param mixed (string|object|array) - Value associated with the type
	* @param string $datetime - Datetime in format 0000-00-00 00:00:00
	* @return bool false on failure, true if success.
	**/
	public function add_log_value( $user_id, $type, $value = '', $datetime = false ) {
		global $wpdb;
		
		//Validate user
		$user_id = absint( $user_id );
		if ( $user_id == 0 ) return false;
		
		//Validate date
		if ( $datetime !== false ) {
			//Make sure date is in right format
			$date_timestamp = strtotime( $datetime );
			if ( !$date_timestamp ) return false;
			$datetime = date( 'Y-m-d H:i:s', $date_timestamp );
		} else {
			$datetime = current_time('mysql');
		}
		
		//Validate type
		$type = stripslashes( $type );
		$value = stripslashes_deep( $value );
		$_value = $value;
		$value = maybe_serialize( $value );

		//Perform the DB insert
		do_action( "mt_add_log_data", $user_id, $type, $_value, $datetime );
		$result = $wpdb->insert( $this->tablename, array(
			'user_id' => $user_id,
			'date' => $datetime,
			'type' => $type,
			'value' => $value
		), array(
			'%d',
			'%s',
			'%s',
			'%s'
		) );
		
		//Check result
		if ( ! $result )
			return false;
		$log_id = (int) $wpdb->insert_id;
		
		//Return
		do_action( "mt_added_log_data", $log_id, $user_id, $type, $_value, $datetime );
		return true;
	} //end add_log_value
	
	/**
	* create_table()
	*
	* Creates the table for the plugin logs
	*
	**/
	static function create_table() {
		global $wpdb;
		
		//Get collation - From /wp-admin/includes/schema.php
		$charset_collate = '';
		if ( ! empty($wpdb->charset) )
			$charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
		if ( ! empty($wpdb->collate) )
			$charset_collate .= " COLLATE $wpdb->collate";
		
		//Create table
		$tablename = Metronet_Log::get_table_name();
		$sql = "CREATE TABLE {$tablename} (
						log_id BIGINT(20) NOT NULL AUTO_INCREMENT,
						user_id BIGINT (20) NOT NULL,
						date DATETIME NOT NULL,
						type VARCHAR(255) NOT NULL,
						value LONGTEXT NULL,
						PRIMARY KEY  (log_id), 
						KEY user_id (user_id),
						KEY type (type),
						KEY date(date) ) {$charset_collate};";
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta($sql);
	} //end create_table
	
	/**
	* get_log_count()
	*
	* Retrieves a log count for a specific date range by type
	*
	* @param string $type The type of data
	* @param string $from_date date in format 0000-00-00 00:00:00
	* @param string $to_date date in format 0000-00-00 00:00:00
	* @return int $count
	**/
	public function get_log_count( $type, $from_date, $to_date ) {
		global $wpdb;
		$type = stripslashes( $type );
		if ( !$this->validate_date_time( $from_date ) || !$this->validate_date_time( $to_date ) ) return 0;
		
		$query = "SELECT COUNT(*) FROM {$this->tablename} WHERE type = %s AND date BETWEEN %s AND %s";
		$query = $wpdb->prepare( $query, $type, $from_date, $to_date );
		$result = $wpdb->get_var( $query );
		return $result;
	} //end get_user_log_count
	
	/**
	* get_log_count_by_value()
	*
	* Retrieves a log count for a specific date range by type and value
	*
	* @param string $type The type of data
	* @param string $value The value of the data
	* @param string $from_date date in format 0000-00-00 00:00:00
	* @param string $to_date date in format 0000-00-00 00:00:00
	* @return int $count
	**/
	public function get_log_count_by_value( $type, $value, $from_date, $to_date ) {
		global $wpdb;
		$type = stripslashes( $type );
		$value = stripslashes_deep( $value );
		$value = maybe_serialize( $value );
		if ( !$this->validate_date_time( $from_date ) || !$this->validate_date_time( $to_date ) ) return 0;
		
		$query = "SELECT COUNT(*) FROM {$this->tablename} WHERE type = %s AND value = %s AND date BETWEEN %s AND %s";
		$query = $wpdb->prepare( $query, $type, $value, $from_date, $to_date );
		$result = $wpdb->get_var( $query );
		return $result;
	} //end get_log_count_by_value
	
	/**
	* get_log_values()
	*
	* Retrieves log values for a specific date range by type
	*
	* @param string $type The type of data
	* @param string $from_date date in format 0000-00-00 00:00:00
	* @param string $to_date date in format 0000-00-00 00:00:00
	* @return array of objects
	**/
	public function get_log_values( $type, $from_date, $to_date ) {
		global $wpdb;
		$type = stripslashes( $type );
		if ( !$this->validate_date_time( $from_date ) || !$this->validate_date_time( $to_date ) ) return array();
		
		$query = "SELECT * FROM {$this->tablename} WHERE type = %s AND date BETWEEN %s AND %s";
		$query = $wpdb->prepare( $query, $type, $from_date, $to_date );
		$result = $wpdb->get_results( $query, OBJECT );
		return $result;
	} //end get_user_log_values
	
	/**
	* get_table_name()
	*
	* Returns the tablename for the logs
	*
	* @return string $tablename - The tablename for the logs
	**/
	static function get_table_name() {
		global $wpdb;
		$tablename = $wpdb->base_prefix . 'mt_log';
		return $tablename;
	} //end get_table_name
	
	/**
	* get_user_log_count()
	*
	* Retrieves user log count for a specific date range 
	*
	* @param int $user_id The user id
	* @param string $type The type of data
	* @param string $from_date date in format 0000-00-00 00:00:00
	* @param string $to_date date in format 0000-00-00 00:00:00
	* @return int $count
	**/
	public function get_user_log_count( $user_id, $type, $from_date, $to_date ) {
		global $wpdb;
		$user_id = absint( $user_id );
		$type = stripslashes( $type );
		if ( !$this->validate_date_time( $from_date ) || !$this->validate_date_time( $to_date ) ) return 0;
		
		$query = "SELECT COUNT(*) FROM {$this->tablename} WHERE user_id = %d AND type = %s AND date BETWEEN %s AND %s";
		$query = $wpdb->prepare( $query, $user_id, $type, $from_date, $to_date );
		$result = $wpdb->get_var( $query );
		return $result;
	} //end get_user_log_count
	
	/**
	* get_user_log_values()
	*
	* Retrieves user log values for a specific date range 
	*
	* @param int $user_id The user id
	* @param string $type The type of data
	* @param string $from_date date in format 0000-00-00 00:00:00
	* @param string $to_date date in format 0000-00-00 00:00:00
	* @return array of objects
	**/
	public function get_user_log_values( $user_id, $type, $from_date, $to_date ) {
		global $wpdb;
		$user_id = absint( $user_id );
		$type = stripslashes( $type );
		if ( !$this->validate_date_time( $from_date ) || !$this->validate_date_time( $to_date ) ) return array();
		
		$query = "SELECT * FROM {$this->tablename} WHERE user_id = %d AND type = %s AND date BETWEEN %s AND %s";
		$query = $wpdb->prepare( $query, $user_id, $type, $from_date, $to_date );
		$result = $wpdb->get_results( $query, OBJECT );
		return $result;
	} //end get_user_log_values
	
	/**
	* remove_log_value()
	*
	* Removes a log value based on the log ID
	*
	* @param int $log_id The log id
	* @return bool false on failure, true if success.
	**/
	public function remove_log_value( $log_id ) {
		global $wpdb;
		$log_id = absint( $log_id );
		$result = $wpdb->query( $wpdb->prepare( "DELETE FROM {$this->tablename} WHERE log_id = %d", $log_id ) );
		if ( $result ) return true;
		return false;
	} //end remove_log_value
	
	/**
	* remove_log_values()
	*
	* Removes log values based on user_id, type, and a date range
	*
	* @param int $user_id The user id
	* @param string $type The type of data
	* @param string $from_date date in format 0000-00-00 00:00:00
	* @param string $to_date date in format 0000-00-00 00:00:00
	* @return bool false on failure, true if success.
	**/
	public function remove_log_values( $user_id, $type, $from_date, $to_date ) {
		global $wpdb;
		$user_id = absint( $user_id );
		$type = stripslashes( $type );
		if ( !$this->validate_date_time( $from_date ) || !$this->validate_date_time( $to_date ) ) return false;
		
		$query = "DELETE FROM {$this->tablename} WHERE user_id = %d AND type = %s AND date BETWEEN %s AND %s";
		$query = $wpdb->prepare( $query, $user_id, $type, $from_date, $to_date );
		$result = $wpdb->query( $query );
		if ( $result ) return true;
		return false;
	} //end remove_log_value
	
	
	/**
	* update_log_value()
	*
	* Updates a log item into the database based on the user, type, and date
	* This function should rarely be used unless you know the exact datetime
	* Assumes user_id, type, and datetime are unique
	*
	* @param int $user_id The user id
	* @param string $type The type of data to add
	* @param mixed (string|object|array) - Value associated with the type
	* @param string $datetime - Datetime in format 0000-00-00 00:00:00
	* @return bool false on failure, true if success.
	**/
	public function update_log_value( $user_id, $type, $value = '', $datetime = '0000-00-00 00:00:00' ) {
		global $wpdb;
		
		//Validate user
		$user_id = absint( $user_id );
		if ( $user_id == 0 ) return false;
		
		//Validate date
		$date_timestamp = strtotime( $datetime );
		if ( !$date_timestamp ) return false;
		$datetime = date( 'Y-m-d H:i:s', $date_timestamp );
		
		//Validate type
		$type = stripslashes( $type );
		$value = stripslashes_deep( $value );
		$_value = $value;
		$value = maybe_serialize( $value );
		
		//Determine if we need to do an insert or update - Try to find previous logs
		$query = "SELECT COUNT(*) FROM {$this->tablename} WHERE user_id = %d AND type = %s AND date = %s";
		$query = $wpdb->prepare( $query, $user_id, $type, $datetime );
		$result = $wpdb->get_var( $query );
		if ( $result == 0 ) {
			return $this->add_log_value( $user_id, $type, $_value, $datetime );
		}

		//Perform the DB update
		do_action( "mt_update_log_data", $user_id, $type, $_value, $datetime );
		$result = $wpdb->update( $this->tablename, 
			array(
				'value' => $value
			) 
			, array(
				'user_id' => $user_id,
				'type' => $type,
				'date' => $datetime
			), array(
				'%s'
			), array(
				'%d',
				'%s',
				'%s'
		) );
		
		//Check result
		if ( ! $result )
			return false;
		
		//Return
		do_action( "mt_updated_log_data", $user_id, $type, $_value, $datetime );
		return true;
	} //end update_log_value
	
	private function validate_date_time( $datetime ) {
		if ( !is_string( $datetime ) ) return false;
		//from http://stackoverflow.com/questions/37732/what-is-the-regex-pattern-for-datetime-2008-09-01-123545
		if( preg_match( '/(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})/', $datetime ) ) {
			return true;
		} else {
			return false;
		} 
	} //end validate_date_time
	
}
	
register_activation_hook( __FILE__, 'Metronet_Log::create_table' );

//I'd say, this is a uniquely clever way of making sure a plugin is loaded.  Hook into the action before running dependencies
function metronet_log_loaded() {
	do_action( 'metronet_log_loaded' );
	$mt_log = new Metronet_Log();
	//$mt_log->add_log_value( 1, 'test_delete', array( 'blah' ), date( 'Y-m-d H:i:s' ) );
}
add_action( 'plugins_loaded', 'metronet_log_loaded' );

?>
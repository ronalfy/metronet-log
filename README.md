Metronet Log - A WordPress Developers API for User Activity Logging
============

Metronet Log provides a WordPress API for date-based data storage based on a user ID.  It's up to the developer to implement the actual logging and data retrieval, but the plugin provides many helper methods (described below).

This plugin is strictly designed as an API for data storage and retrieval.  It's up to the developer to parse and present this data to the end user.

Example Usage
---------------------
###Adding a Log Item###
```php
//Assumes $user_id variable is set
//This snippet checks to see if a user has marked their subscription as active or inactive
if ( class_exists( "Metronet_Log" ) ) {
	$log = new Metronet_Log();
	if ( $is_subscribed ) {
		$log->update_log_value( $user_id, 'active', 'true', date( 'Y-m-d 00:00:00' ) );
	} else {
		$log->update_log_value( $user_id, 'active', 'false', date( 'Y-m-d 00:00:00' ) );
	}
}
```

###Retrieving Log Values###
```php
if ( class_exists( "Metronet_Log" ) ) {
	$log = new Metronet_Log();
	$active_inactive_results = $log->get_log_values( 'active', $date_beginning, $date_end );
	foreach( $active_inactive_results as $result ) {
		if ( $result->value == 'true' ) {
			//Do something if active
		} else {
			//Do something if inactive
		}
	}
}
```

Table Structure
---------------------
Metronet Log adds a site-wide table to your WordPress database.

<table>
	<tr>
		<td>Column</td><td>Type</td><td>Null</td>
	</tr>
	<tr>
		<td>log_id</td><td>bigint(20)</td><td>No</td>
	</tr>
	<tr>
		<td>user_id</td><td>bigint(20)</td><td>No</td>
	</tr>
	<tr>
		<td>date</td><td>datetime</td><td>No</td>
	</tr>
	<tr>
		<td>type</td><td>varchar(255)</td><td>No</td>
	</tr>
	<tr>
		<td>value</td><td>longtext</td><td>Yes</td>
	</tr>
</table>

Think of the table above as the same as the WordPress usermeta table, only with dates attached.  That, and the keys (renamed type) can be repeated.

The fields user_id, type, and datetime are considered unique when querying together since it is assumed there cannot be two activity types for a user on the same date and time.

Available Class Methods
--------------------

###add_log_value###
```php
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
add_log_value( $user_id, $type, $value = '', $datetime = false )
```

###get_log_count###
```php
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
get_log_count( $type, $from_date, $to_date ) 
```

###get_log_count_by_value###
```php
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
get_log_count_by_value( $type, $value, $from_date, $to_date )
```

###get_log_values###
```php
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
public function get_log_values( $type, $from_date, $to_date )
```

###get_user_log_count###
```php
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
get_user_log_count( $user_id, $type, $from_date, $to_date )
```

###get_user_log_values###
```php
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
get_user_log_values( $user_id, $type, $from_date, $to_date )
```

###remove_log_value###
```php
/**
* remove_log_value()
*
* Removes a log value based on the log ID
*
* @param int $log_id The log id
* @return bool false on failure, true if success.
**/
public function remove_log_value( $log_id )
```

###remove_log_values###
```php
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
remove_log_values( $user_id, $type, $from_date, $to_date )
```

###update_log_value###
```php
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
update_log_value( $user_id, $type, $value = '', $datetime = '0000-00-00 00:00:00' )
```
Metronet Log - A WordPress Developers API for User Activity Logging
============

Metronet Log rovides a WordPress API for date-based data storage based on a user ID.  It's up to the developer to implement the actual logging and data retrieval, but the plugin provides many helper methods (described below).

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

Class Methods
--------------------

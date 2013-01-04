<?php
global $wpdb;
if( !defined( 'WP_UNINSTALL_PLUGIN' ) ) exit;
$tablename = $wpdb->base_prefix . 'mt_log';
$wpdb->query("DROP TABLE `$tablename`")
?>
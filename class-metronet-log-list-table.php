<?php
//Code inspired by http://wp.smashingmagazine.com/2011/11/03/native-admin-tables-wordpress/
class Metronet_Logs_List_Table extends WP_List_Table {
	public function __construct() {
		parent::__construct( array(
			'singular'=> 'singular?', //Singular label
			'plural' => 'plural?', //plural label, also this well be one of the table css class
			'ajax'	=> false //We won't support Ajax for this table
			) );
	} //end constructor
	
	public function extra_tablenav( $which ) {
		if ( $which == 'top' ) {
			echo 'yo top';
		} else {
			echo 'yo below';
		}
	} //end extra_tablenav
	
	public function get_columns( $columns ) {
		$columns = array(
			'cb' => '<input type="checkbox" />',
			'user' => esc_html__( 'User', 'metronet_log' ),
			'type' => esc_html__( 'Type', 'metronet_log' ),
			'value' => esc_html__( 'Value', 'metronet_log' ),
			'date' => esc_html__( 'Date', 'metronet_log' )
		);
	} //end get_columns
	
	public function prepare_items() {
		global $wpdb, $_wp_column_headers;
		$screen = get_current_screen();
		
		//Tablename
		$tablename = Metronet_Log::get_table_name();
		
		/* -- Preparing your query -- */
		$query = "SELECT * FROM {$tablename}"; //todo - This doesn't seem effecient
				
	    //Number of elements in the table
	    $totalitems = $wpdb->query( $query ); //return the total number of affected rows
	    //How many to display per page?
	    $perpage = 50;
	    //Which page is this?
	    $paged = !empty($_GET["paged"]) ? mysql_real_escape_string($_GET["paged"]) : '';
	    
	    //Page Number
	    if(empty($paged) || !is_numeric($paged) || $paged<=0 ){ $paged=1; }
	    //How many pages do we have in total?
	    $totalpages = ceil($totalitems/$perpage);
	    //adjust the query to take pagination into account
	    if(!empty($paged) && !empty($perpage)){
		    $offset=($paged-1)*$perpage;
			$query.=' LIMIT '.(int)$offset.','.(int)$perpage;
	    }
		
		/* -- Register the pagination -- */
		$this->set_pagination_args( array(
			"total_items" => $totalitems,
			"total_pages" => $totalpages,
			"per_page" => $perpage,
		) );
		//The pagination links are automatically built according to those parameters
		
		/* -- Register the Columns -- */
		$columns = $this->get_columns();
		$_wp_column_headers[$screen->id]=$columns;
		
		/* -- Fetch the items -- */
		$this->items = $wpdb->get_results($query);
		} //end prepare


} //end Metronet_Logs_List_Table
?>
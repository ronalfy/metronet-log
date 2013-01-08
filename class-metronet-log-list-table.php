<?php
//Code inspired by http://wp.smashingmagazine.com/2011/11/03/native-admin-tables-wordpress/
class Metronet_Logs_List_Table extends WP_List_Table {
	public function __construct() {
		parent::__construct( array(
			'singular'=> 'singular', //Singular label
			'plural' => 'plural', //plural label, also this well be one of the table css class
			'ajax'	=> false //We won't support Ajax for this table
			) );
	} //end constructor
	
	public function display_rows() {
		static $alternate;
		//Get the records registered in the prepare_items method
		$records = $this->items;
		
		//Get the columns registered in the get_columns and get_sortable_columns methods
		list( $columns, $hidden ) = $this->get_column_info();
		
		//Loop for each record
		if( !empty( $records ) ){
			foreach( $records as $rec ){
				$alternate = 'alternate' == $alternate ? '' : 'alternate';

				//Open the line
			    echo sprintf( '<tr id="record_%d" class="%s">', $rec->log_id, $alternate );
				foreach ( $columns as $column_name => $column_display_name ) {
			
					//Style attributes for each col
					$class = "class='$column_name column-$column_name'";
					$style = "";
					$attributes = $class;
						
					//Display the cell
					switch ( $column_name ) {
						case 'cb':
							echo sprintf( '<td %s>&nbsp;</td>', $attributes );
							break;
						case 'user':	
								$user = get_user_by( 'id', $rec->user_id );
								echo sprintf( '<td %s>', $attributes );
								if ( !$user ) 
									echo "User doesn't exist";
								else
									echo esc_html( $user->user_nicename );
								echo '</td>';
							break;
						case 'type':
								echo sprintf( '<td %s>', $attributes );
								echo esc_html( $rec->type );
								echo '</td>';
							break;
						case 'value':
								echo sprintf( '<td %s>', $attributes );
								echo esc_html( $rec->value ); //todo objects and arrays
								echo '</td>';
							break;
						case 'date':
								echo sprintf( '<td %s>', $attributes );
								echo esc_html( $rec->date ); //todo formatting
								echo '</td>';
							break;
							
					}
				}
			
				//Close the line
				echo'</tr>';
			}
		}
	}
	
	public function extra_tablenav( $which ) {
		if ( $which == 'top' ) {
		} else {
		}
	} //end extra_tablenav
	
	public function get_columns() {
		$columns = array(
			'cb' => '<input type="checkbox" />',
			'user' => esc_html__( 'User', 'metronet_log' ),
			'type' => esc_html__( 'Type', 'metronet_log' ),
			'value' => esc_html__( 'Value', 'metronet_log' ),
			'date' => esc_html__( 'Date', 'metronet_log' )
		);
		return $columns;
	} //end get_columns
	
	public function prepare_items() {
		global $wpdb, $_wp_column_headers;
		$screen = get_current_screen();
		
		$this->_column_headers = array( 
			$this->get_columns(),		// columns
			array(),			// hidden
			$this->get_sortable_columns(),	// sortable
		);
		
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
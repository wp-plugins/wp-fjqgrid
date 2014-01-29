<?php
if( !class_exists( 'FjqGridDB' ) )
{
	class FjqGridDB
	{
		private $tablename;
		private $fieldsnames;
		private $fieldstypes;
		private $fieldssizes;
		private $fields;
		
		public function __construct( $has_prefix, $table_name, $fields, $types )
		{
			global $wpdb;
			if ($has_prefix)
	    		$wpdb->$table_name = "{$wpdb->prefix}$table_name";
			else
				$wpdb->$table_name = "$table_name";
			$this->tablename = $wpdb->$table_name;
			
			/* this parameters are set only if a table create will be request */
			if ( $fields != null AND $types != null) {
				$this->fieldsnames = $fields;
				$this->fieldstypes = $types;
			}
			else {			
				require_once('wp-fjqgrid-dbmodel.php');
				$wpfjqgModel = new FjqGridDbModel();
				$columns = $wpfjqgModel->fjqg_colModel ( $table_name, '' );
				foreach( $columns as $col ) {
					$this->fieldsnames[] = $col['name'];
					$this->fieldstypes[] = $col['type'];
					$this->fieldssizes[] = $col['size'];
					$this->fields[$col['name']]	= $col['type'];
				}
				/*//@@@ debug
				global $wpfjqg;
				$wpfjqg->fplugin_log($this->fieldsnames);
				$wpfjqg->fplugin_log($this->fieldstypes);
				$wpfjqg->fplugin_log($this->fieldssizes);
				*/
			}
		}
		
		function create_table()
		{
			global $wpdb;
			global $charset_collate;

			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

			$fieldsdescr = '';
			$i = 1;
			foreach ($this->fieldsnames as $field )
			{
				$fieldsdescr .= $field.' '.$this->fieldstypes[$i++].', ';
			}
			
			$sql_create_table = "CREATE TABLE {$this->tablename} (
				id bigint(20) unsigned NOT NULL auto_increment,
				{$fieldsdescr} 
				PRIMARY KEY  (id)
				) $charset_collate; ";

			dbDelta( $sql_create_table );
		}
		/*
		function get_table_columns_names ()
		{
		    return array_values( $this->fieldsnames );
		}
		
		function get_table_columns()
		{
			return  $this->fieldstypes;
		}
		*/
		/**
		 * Inserts a row into the table
		 *
		 *@param $data array An array of key => value pairs to be inserted
		 *@return int The ID of the created row. Or WP_Error or false on failure.
		*/
		function insert_row( $data=array() )
		{
		    global $wpdb;
			//TODO validation for all data type fields
			
		    /*//Set default values special fileds
		    $data = wp_parse_args( $data, array(
		                 'user_id'=> get_current_user_id(),
		                 'date_now'=> current_time('timestamp'),
		    		));

		    //Check date validity
			if ( $data['date_now'] <= 0 )
		        return 0;
		        
		    //Convert date from local timestamp to GMT mysql format
		    $data['data_now'] = date_i18n( 'Y-m-d H:i:s', $data['date_now'], true );

		    //Initialise column format array
		    $column_formats = $this->fieldstypes;

		    //Force fields to lower case
		    $data = array_change_key_case ( $data );

		    //White list columns
		    $data = array_intersect_key( $data, $this->fieldsnames );

		    //Reorder $column_formats to match the order of columns given in $data
		    $data_keys = array_keys( $data );
		    $column_formats = array_merge( array_flip( $data_keys ), $column_formats );
		    */
			$column_formats = null;
		    $wpdb->insert( $this->tablename, $data, $column_formats );

		    return $wpdb->insert_id;
		}

		/**
		 * Updates a row with supplied data
		 *
		 *@param $row_id int ID of the row to be updated
		 *@param $data array An array of column=>value pairs to be updated
		 *@return bool Whether the row was successfully updated.
		*/
		function update_row( $row_id, $data=array() )
		{
		    global $wpdb;
		    if( empty( $row_id ) )
		         return false;
			//TODO validation for all data type fields
			
			/*
		    //Convert activity date from local timestamp to GMT mysql format
		    if( isset( $data['date_now'] ) )
		         $data['date_now'] = date_i18n( 'Y-m-d H:i:s', $data['date_now'], true );

		    //Initialise column format array
		    $column_names = $this->get_table_columns_names ();

		    //Force fields to lower case
		    //$data = array_change_key_case ( $data );
		    //White list columns
		    $data = array_intersect_key( $data, $this->fields );
		    //Reorder $column_formats to match the order of columns given in $data
		    $data_keys = array_keys( $data ); // $data_keys = 0=id; 1=>city; ..
		    $column_formats = array_merge( array_flip( $data_keys ), $column_names );
			*/
			$column_formats = null;
		    if ( false === $wpdb->update( $this->tablename, $data, array('id'=>$row_id), $column_formats ) ) {
		         return false;
		    }
		    return true;
		}

		/**
		 * Retrieves data from the database matching $query.
		 * $query is an array which can contain the following keys:
		 *
		 * 'fields' - an array of columns to include in returned roles. Or 'count' to count rows. Default: empty (all fields).
		 * 'where' - sql where to filter records
		 * 'orderby' - field to order by
		 * 'order' - asc or desc
		 * 'number' - nr of records to retrieve...
		 * 'offset' - ...starting from this record nr.
		 *
		 *@param $query Query array
		 *@return array Array of matching rows. False on error.
		*/
		function get_rows( $query=array() )
		{
			global $wpdb;
			/* Parse defaults */
			$defaults = array(
				'fields' =>array(),
				'where'  =>'',
				'orderby'=>'id',
				'order'  =>'asc',
				'number' =>1000,
				'offset' =>0
				);
			$query = wp_parse_args( $query, $defaults );

			/* Form a cache key from the query */
			$cache_key = 'wfjqgdb:'.$this->tablename.md5( serialize($query));
			$cache = wp_cache_get( $cache_key );
			if ( false !== $cache ) {
				$cache = apply_filters( 'get_rows', $cache, $query );
				return $cache;
			}
			extract($query);

			/* SQL Select */
			//Whitelist of allowed fields
			$allowed_fields = $this->get_table_columns_names();
			if( is_array($fields) ) {
				//Convert fields to lowercase 
				$fields = array_map( 'strtolower', $fields );
				//Sanitize by white listing
				$fields = array_intersect( $fields, $allowed_fields );
			}
			else {
				$fields = strtolower( $fields );
			}

			//Return only selected fields. Empty is interpreted as all
			if( empty($fields) ){
				$select_sql = "SELECT * FROM {$this->tablename}";
			}
			elseif( 'count' == $fields ) {
				$select_sql = "SELECT COUNT(*) FROM {$this->tablename}";
			}
			else {
				$select_sql = "SELECT ".implode(',',$fields)." FROM {$this->tablename}";
			}

			/* SQL Join */
			//We don't need this, but we'll allow it be filtered (see 'wpfjqgdb_clauses' )
			$join_sql='';

			/* SQL Where */
			//Initialise WHERE
			$where_sql = 'WHERE 1=1'.$where;

			/* SQL Order */
			//Whitelist order
			$order = strtoupper( $order );
			$order = ( 'ASC' == $order ? 'ASC' : 'DESC' );
			$order_sql = "ORDER BY $orderby $order";

			/* SQL Limit */
			$offset = absint( $offset ); //Positive integer
			if( $number == -1 ){
				$limit_sql = "";
			}
			else {
				$number = absint( $number ); //Positive integer
				$limit_sql = "LIMIT $offset, $number";
			}

			/* Filter SQL 
			$pieces = array( 'select_sql', 'join_sql', 'where_sql', 'order_sql', 'limit_sql' );
			$clauses = apply_filters( 'wpfjqgdb_clauses', compact( $pieces ), $query );
			foreach ( $pieces as $piece )
			$$piece = isset( $clauses[ $piece ] ) ? $clauses[ $piece ] : '';*/
			
			/* Form SQL statement */
			$sql = "$select_sql $where_sql $order_sql $limit_sql";
			if( 'count' == $fields ){
				$sql = "$select_sql $where_sql";
				return $wpdb->get_var( $sql );
			}

			/* Perform query */
			$rows = $wpdb->get_results( $sql );

			/* Add to cache and filter */
			wp_cache_add( $cache_key, $rows, 24*60*60 );
			$rows = apply_filters( 'get_rows', $rows, $query );

			return $rows;
		}

		/**
		 * Deletes a row from the table
		 *
		 *@param $row_id ID of the row to be deleted
		 *@return bool Whether the row was successfully deleted.
		*/
		function delete_row( $row_id )
		{
		    global $wpdb;
		    $row_id = absint( $row_id );
		    if( empty( $row_id ) )
		         return false;

		    do_action( 'delete_rwg', $row_id );
		    $sql = $wpdb->prepare( "DELETE from {$this->tablename} WHERE id = %d", $row_id );
		    if( !$wpdb->query( $sql ) )
		         return false;

		    do_action( 'deleted_row', $row_id );
		    return true;
		}
	}
}
?>
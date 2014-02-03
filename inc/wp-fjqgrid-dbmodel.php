<?php
if( !class_exists( 'FjqGridDbModel' ) )
{
	class FjqGridDbModel
	{
		private $tablename;
		private $columns;
		private $fieldsnames;
		private $fieldstypes;
		private $fieldssizes;
		private $fieldsflags;
		private $fields;
		private $keyfield;
		
		public function __construct( $table, $optionsfrmtfield=null )
		{
			global $wpfjqg;
			
			$this->tablename = $table;
			
			if ( $optionsfrmtfield ) {
				$frmfields = explode ( "|", $optionsfrmtfield );
				//table::field::align:'center',editoptions:{'size':40}|table::otherfied::otherformat
				//extract in an array the user formattings for the fields that belongs to actual table
				foreach ( $frmfields as $frmfield )
				{
					$frmarray = explode ( "::", $frmfield );
					if ($frmarray[0]==$this->tablename) {
						$frm[$frmarray[1]] = $frmarray[2];
					}
				}				
			}
			if ( isset ($frm))
				$wpfjqg->fplugin_log( 'custom frm', $frm, 3 );
		
			$query_cmd = "SELECT * FROM `{$this->tablename}` LIMIT 1 OFFSET 0";
			$fetch_recordset = mysql_query( $query_cmd );
			if ( $fetch_recordset==null ) {
				$wpfjqg->fplugin_log( "Query non valida", mysql_error(), 1 );
				return;				
			}
			$fields_cnt = mysql_num_fields( $fetch_recordset );
			for ($cnt = 0; $cnt < $fields_cnt; $cnt++){
		        $name = mysql_field_name( $fetch_recordset, $cnt );
		        $type = mysql_field_type( $fetch_recordset, $cnt );
		        $size = mysql_field_len( $fetch_recordset, $cnt );
		        $flag = mysql_field_flags( $fetch_recordset, $cnt );
				$column["name"] = $name;
				$column["type"] = $type;
				$column["size"] = $size;
				$column["flag"] = $flag;
				$column["index"] = $name;
				$column["title"] = ucwords( str_replace( "_", " ", $name ) );
 				$column["searchoptions"] = "{sopt:['bw','cn','eq','ne','ew','nu','nn']}";
				$column["editable"] = "true";
				$reqrd = ( strpos($flag, 'not_null')!== false ) ? 'required: true,':'';
				if ( !isset( $frm[$name] ) )
					$column["formatter"] = $this->fjqg_format ( $type, $size, $reqrd );
				else
					$column["formatter"] = $frm[$name];
				$columns[] = $column;
			}
			$this->columns = $columns;
			$wpfjqg->fplugin_log( 'columns', $columns, 3 );
			
			foreach( $columns as $col ) {
					$this->fieldsnames[] = $col['name']; //mykey
					$this->fieldstypes[] = $col['type']; //int
					$this->fieldssizes[] = $col['size']; //11
					$this->fieldsflags[] = $col['flag']; //not_null primary_key autoincrement
					$this->fields[$col['name']]	= $col['type'];
				}

			$keyfield = $this->fieldsnames[0];
			// search for primary_key
			//$wpfjqg->fplugin_log('flags',$this->fieldsflags);
			$keyword = 'primary_key';
			$i = 0;
			foreach ( $this->fieldsflags as $key=>$flg ) {
				if ( preg_match( "/{$keyword}/i", $flg ) ) { 
					$keyfield = $this->fieldsnames[$key];
					$i++;
				}
			}
			// only one primary_key allowed
			if ( $i!=1 )
				$wpfjqg->fplugin_log( "No one or more than one field defined as ".$keyword, 1 );
			$wpfjqg->fplugin_log( "Field ".$keyfield." assumed as ".$keyword." in table ".$table );
			$this->keyfield = $keyfield;
		}
		
		public function fjqg_getPK ()
		{
			return $this->keyfield;
		}
		
		public function fjqg_getFieldsNames()
		{
			return $this->fieldsnames;
		}
		
		public function fjqg_colNames( )
		{
			//'Id','City','Date','Population','Price','Note','Code'
			$colNames = "";
			foreach( $this->columns as $col )
				$colNames .= "'".$col['title']."',";
			return substr( $colNames, 0, strlen($colNames)-1 );
		}
		
		public function fjqg_colModels( )
		{
			//{title:'Id',name:'id',index:'id',editable:true,editoptions:{'size':20},editrules: {required: true, integer: true}
			$colModels = "";
			foreach( $this->columns as $col ) {
					$colModels .= "{title:'".$col['title']."',name:'".$col['name']."',index:'".$col['index'];
					$colModels .= "',searchoptions:".$col['searchoptions'].",editable:".$col['editable'].",".$col['formatter'];
					if ( $col['name'] == $this->keyfield ) 
						$colModels .= ",key:true";
					$colModels .= "},
					";
			}
			return $colModels;
		}
		
		private function fjqg_format ( $type, $lung, $reqrd )
		{
			/* MySql data types http://help.scibit.com/mascon/masconMySQL_Field_Types.html
			TINYINT, SMALLINT, MEDIUMINT, INT, INTEGER, BIGINT 
			FLOAT, DOUBLE, DOUBLE PRECISION, REAL, DECIMAL, NUMERIC 
			DATE - YYYY-MM-DD format 
			DATETIME - YYYY-MM-DD HH:MM:SS format 
			TIMESTAMP - YYYYMMDDHHMMSS, YYMMDDHHMMSS, YYYYMMDD or YYMMDD format 
			TIME - HH:MM:SS format
			YEAR - YYYY format
			CHAR, VARCHAR 
			TINYBLOB, BLOB, MEDIUMBLOB, LONGBLOB
			TINYTEXT, TEXT, MEDIUMTEXT, LONGTEXT 
			ENUM 
			SET 
			*/
			switch (strtoupper( $type ))
			{
				case 'TINYINT':
				case 'SMALLINT':
				case 'MEDIUMINT':
				case 'INT':
				case 'INTEGER':
				case 'BIGINT':
					if ( $lung==1 ) {
						$frm = "align:'center', formatter: 'checkbox', formatoptions: { disabled: true}, editoptions: { value: '1:0' }";
						$frm .= ", edittype: 'checkbox', editrules: {required: false}, searchoptions: { sopt: ['eq','ne','nu','nn']}, searchrules:{integer:true, minValue:0, maxValue:1}";						
					}
					else {
						$frm = "align:'right', formatter:'number', formatoptions: {thousandsSeparator: '.', decimalPlaces: 0, defaulValue: 0}";
						$frm .= ",editoptions:{'size':20}, editrules:{".$reqrd." integer: true, maxValue: 999999999 } ";						
					}
				break;
				case 'FLOAT':
				case 'DOUBLE':
				case 'DOUBLE PRECISION':
				case 'REAL':
				case 'DECIMAL':
				case 'NUMERIC':
					$frm = "align:'right', formatter:'number', formatoptions: {decimalSeparator:',', thousandsSeparator: '.', decimalPlaces: 2, defaulValue: 0}";
					$frm .= ",editoptions:{'size':20}, editrules:{".$reqrd."}";
				break;
				case 'DATE':
					$frm = "align:'right', formatter:'date', formatoptions: {srcformat:'Y-m-d',newformat:'d/m/Y'}";
					$frm .= ",editoptions:{'size':20}, editrules:{".$reqrd."}";
				break;
				case 'DATETIME':
					$frm = "align:'right', formatter:'date', formatoptions: {srcformat:'Y-m-d H:i:s',newformat:'d/m/Y H:i'}";
					$frm .= ",editoptions:{'size':20}, editrules:{".$reqrd."}";
				break;
				case 'TIME':
					$frm = "align:'right', formatter:'date', formatoptions: {srcformat:'H:i:s',newformat:'H:i:s'}";
					$frm .= ",editoptions:{'size':20}, editrules:{".$reqrd."}";
				break;
				case 'CHAR':
				case 'VARCHAR':
				case 'STRING':
					$frm = "align:'left'";
					$frm .= ",editoptions:{'size':20}, editrules:{".$reqrd."}";
				break;
				default:
					$frm = "align:'left'";
					$frm .= ",editoptions:{'size':20}";
				break;
			}
			return $frm;
		}
	}
}
		
?>
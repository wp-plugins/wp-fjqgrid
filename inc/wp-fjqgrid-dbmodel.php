<?php
if( !class_exists( 'FjqGridDbModel' ) )
{
	class FjqGridDbModel
	{
		public function __construct(  )
		{
		}
		
		public function fjqg_colModel ( $table, $optionsfrmtfield )
		{
			$frmfields = explode ( "|", $optionsfrmtfield );
			//table::field::align:\'center\',editoptions:{\'size\':40}|table::otherfied::otherformat
			//extract in an array the user formattings for the fields that belongs to actual table
			foreach ( $frmfields as $frmfield )
			{
				$frmarray = explode ( "::", $frmfield );
				if ($frmarray[0]==$table) {
					$frm[$frmarray[1]]=$frmarray[2];
				}
			}
		
			$query_cmd = "SELECT * FROM $table LIMIT 1 OFFSET 0";
			$fetch_recordset = mysql_query($query_cmd);
			$fields_cnt = mysql_num_fields($fetch_recordset);
			for ($cnt = 0; $cnt < $fields_cnt; $cnt++){
		        $name = mysql_field_name( $fetch_recordset, $cnt );
		        $type = mysql_field_type( $fetch_recordset, $cnt );
		        $size = mysql_field_len( $fetch_recordset, $cnt );
		        $flag = mysql_field_flags( $fetch_recordset, $cnt );
				$column["name"] = $name;
				$column["type"] = $type;
				$column["size"] = $size;
				$column["index"] = $name;
				$column["title"] = ucwords( str_replace( "_", " ", $name ) );
 				$column["searchoptions"] = "{sopt:['bw','cn','eq','ne','ew','nu','nn']}";
				$column["editable"] = "true";
				if ( !isset( $frm[$name] ) )
					$column["formatter"] = $this->fjqg_format ( $type, $size );
				else
					$column["formatter"] = $frm[$name];
				$columns[] = $column;
			}
			return $columns;
		}
		
		public function fjqg_colNames( $columns )
		{
			//'Id','City','Date','Population','Price','Note','Code'
			$colNames = "";
			foreach( $columns as $col )
				$colNames .= "'".$col['title']."',";
			return substr( $colNames, 0, strlen($colNames)-1 );
		}
		
		public function fjqg_colModels( $columns )
		{
			//{title:'Id',name:'id',index:'id',editable:true,editoptions:{'size':20}},
			$colModels = "";
			foreach( $columns as $col )
				$colModels .= "{title:'".$col['title']."',name:'".$col['name']."',index:'".$col['index']."',searchoptions:".$col['searchoptions'].",editable:".$col['editable'].",".$col['formatter']."},
				";				
			return $colModels;
		}
		
		private function fjqg_format ( $type, $lung )
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
					$frm = "align:'right', formatter:'number', formatoptions: {decimalSeparator:',', thousandsSeparator: '.', decimalPlaces: 0, defaulValue: 0}";
					$frm .= ",editoptions:{'size':20}";
				break;
				case 'FLOAT':
				case 'DOUBLE':
				case 'DOUBLE PRECISION':
				case 'REAL':
				case 'DECIMAL':
				case 'NUMERIC':
					$frm = "align:'right', formatter:'number', formatoptions: {decimalSeparator:',', thousandsSeparator: '.', decimalPlaces: 2, defaulValue: 0}";
					$frm .= ",editoptions:{'size':20}";
				break;
				case 'DATE':
					$frm = "align:'right', formatter:'date', formatoptions: {srcformat:'Y-m-d',newformat:'d/m/Y H:i'}";
					$frm .= ",editoptions:{'size':20}";
				break;
				case 'DATETIME':
					$frm = "align:'right', formatter:'date', formatoptions: {srcformat:'Y-m-d H:i:s',newformat:'d/m/Y H:i'}";
					$frm .= ",editoptions:{'size':20}";
				break;
				case 'TIME':
					$frm = "align:'right', formatter:'date', formatoptions: {srcformat:'H:i:s',newformat:'H:i:s'}";
					$frm .= ",editoptions:{'size':20}";
				break;
				case 'CHAR':
				case 'VARCHAR':
					$frm = "align:'left'";
					$frm .= ",editoptions:{'size':20}";
					if ($lung==1)
						$frm = "align:'center', formatter: 'checkbox', formatoptions: { disabled: true}, editoptions: { value: '1:0' }, edittype: 'checkbox', editrules: {required: false}, searchoptions: { sopt: ['eq','ne','nu','nn']}, searchrules:{integer:true, minValue:0, maxValue:1}}";
				break;
				default:
					$frm = "align:'left'";
					$frm .= ",editoptions:{'size':20}";
				break;
			}
			return $frm;
		}

		public function fjqg_strip ( $value )
		{
			$mq = get_magic_quotes_gpc();
			if( !$mq )
		  	{
		    	if( is_array( $value ) )  
					if ( array_is_associative( $value ) )
					{
						foreach( $value as $k=>$v )
							$tmp_val[$k] = stripslashes( $v );
						$value = $tmp_val; 
					}				
					else  
						for($j = 0; $j < sizeof( $value ); $j++)
		        			$value[$j] = stripslashes( $value[$j] );
				else
					$value = stripslashes( $value );
			}
			return $value;
		}
	}
}
		
?>
<?php
if ( !class_exists( 'FjqGridDbModel' ) ) {
	define( 'NOT_NULL_FLAG', '1' );    /* Field can't be NULL */
	define( 'PRI_KEY_FLAG', '2' );     /* Field is part of a primary key */
	define( 'UNIQUE_KEY_FLAG', '4' );  /* Field is part of a unique key */
	define( 'MULTIPLE_KEY_FLAG', '8' );   /* Field is part of a key */
	define( 'BLOB_FLAG', '16' );       /* Field is a blob */
	define( 'UNSIGNED_FLAG', '32' );   /* Field is unsigned */
	define( 'ZEROFILL_FLAG', '64' );   /* Field is zerofill */
	define( 'BINARY_FLAG', '128' );    /* Field is binary   */
	define( 'ENUM_FLAG', '256' );	   /* Field is an enum */
	define( 'AUTO_INCREMENT_FLAG', '512' ); /* Field is a autoincrement field */
	define( 'TIMESTAMP_FLAG', '1024' );	 /* Field is a timestamp */

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

		public function __construct( $table, $optionsfrmtfield = null )
		{
			global $wpfjqg;
			$this->tablename = $table;
			$printinfo = false;
			if ( $optionsfrmtfield ) {
				$printinfo = true; // only at first call, when js code is generated
				$frmfields = explode( "|", $optionsfrmtfield );
				//table::field::align:'center',editoptions:{'size':40}|table::otherfied::otherformat
				//extract in an array the user formattings for the fields that belongs to actual table
				foreach ( $frmfields as $frmfield ) {
					$frmarray = explode( "::", $frmfield );
					if ( $frmarray[0] == $this->tablename ) {
						$frm[$frmarray[1]] = $frmarray[2];
					}
				}
			}
			if ( isset( $frm ) ) {
				$wpfjqg->fplugin_log( 'custom frm', $frm, 3 );
			}

			$mysqli = new mysqli( DB_HOST, DB_USER, DB_PASSWORD, DB_NAME );
			if ( $mysqli->connect_errno ) {
				printf( "Connect failed: %s\n", $mysqli->connect_error );
				exit();
			}
			$query_cmd = "SELECT * FROM `{$this->tablename}` LIMIT 1 OFFSET 0";
			$result = $mysqli->query( $query_cmd );
			$fields_cnt = $mysqli->field_count;
			if ( $fields_cnt == 0 ) {
				$wpfjqg->fplugin_log( "Query non valida", mysql_error(), 1 );
				return;
			}
			for ( $cnt = 0; $cnt < $fields_cnt; $cnt++ ) {
				$finfo = $result->fetch_field_direct( $cnt );
				$name = $finfo->name;
				$type = $finfo->type;
				$size = $finfo->max_length;
				$flag = $finfo->flags;
				$column["name"] = $name;
				$column["type"] = $type;
				$column["size"] = $size;
				$column["flag"] = $flag;
				$column["index"] = $name;
				$column["title"] = ucwords( str_replace( "_", " ", $name ) );
				$column["searchoptions"] = "{sopt:['bw','cn','eq','ne','ew','nu','nn']}";
				$column["editable"] = "true";
				$reqrd = ( ( $flag & NOT_NULL_FLAG ) == NOT_NULL_FLAG ) ? 'required: true,' : '';
				if ( !isset( $frm[$name] ) ) {
					$column["formatter"] = $this->fjqg_format( $type, $size, $reqrd );
				} else {
					$column["formatter"] = $frm[$name]; //use custom formatting
				}
				$columns[] = $column;
			}
			/* free result set */
			$result->close();
			$this->columns = $columns;
			if ( $printinfo ) {
				$wpfjqg->fplugin_log( 'columns', $columns, 3 );
			}

			foreach ( $columns as $col ) {
				$this->fieldsnames[] = $col['name']; //mykey
				$this->fieldstypes[] = $col['type']; //int
				$this->fieldssizes[] = $col['size']; //11
				$this->fieldsflags[] = $col['flag']; //not_null primary_key autoincrement
				$this->fields[$col['name']] = $col['type'];
			}

			$keyfield = $this->fieldsnames[0];
			// search for primary_key
			$keyword = 'primary_key';
			$i = 0;
			foreach ( $this->fieldsflags as $key => $flg ) {
				if ( ( $flg & PRI_KEY_FLAG ) == PRI_KEY_FLAG ) {
					$keyfield = $this->fieldsnames[$key];
					$i++;
				}
			}
			// only one primary_key allowed
			if ( $i != 1 ) {
				$wpfjqg->fplugin_log( "No one or more than one field defined as " . $keyword, 1 );
			}
			if ( $printinfo ) {
				$wpfjqg->fplugin_log( "Field '" . $keyfield . "' assumed as " . $keyword . " in table " . $table );
			}
			$this->keyfield = $keyfield;
		}

		public function fjqg_getPK()
		{
			return $this->keyfield;
		}

		public function fjqg_getFieldsNames()
		{
			return $this->fieldsnames;
		}

		public function fjqg_colNames()
		{
			//'Id','City','Date','Population','Price','Note','Code'
			$colNames = "";
			foreach ( $this->columns as $col ) {
				$colNames .= "'" . $col['title'] . "',";
			}
			return substr( $colNames, 0, strlen( $colNames ) - 1 );
		}

		public function fjqg_colModels()
		{
			//{title:'Id',name:'id',index:'id',editable:true,editoptions:{'size':20},editrules: {required: true, integer: true}
			$colModels = "";
			foreach ( $this->columns as $col ) {
				$colModels .= "{title:'" . $col['title'] . "',name:'" . $col['name'] . "',index:'" . $col['index'];
				$colModels .= "',searchoptions:" . $col['searchoptions'] . ",editable:" . $col['editable'] . "," . $col['formatter'];
				if ( $col['name'] == $this->keyfield ) {
					$colModels .= ",key:true";
				}
				$colModels .= "},
					";
			}
			return $colModels;
		}

		private function fjqg_format( $type, $lung, $reqrd )
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
			 * 
			  1=>'tinyint',
			  2=>'smallint',
			  3=>'int',
			  4=>'float',
			  5=>'double',
			  7=>'timestamp',
			  8=>'bigint',
			  9=>'mediumint',
			  10=>'date',
			  11=>'time',
			  12=>'datetime',
			  13=>'year',
			  16=>'bit',
			  //252 is currently mapped to all text and blob types (MySQL 5.0.51a)
			  253=>'varchar',
			  254=>'char',
			  246=>'decimal'
			 */
			switch ( $type ) {
				case 1:
				case 2:
				case 3:
				case 8:
				case 9:
					if ( $lung == 1 ) {
						$frm = "align:'center', formatter: 'checkbox', formatoptions: { disabled: true}, editoptions: { value: '1:0' }";
						$frm .= ", edittype: 'checkbox', editrules: {required: false}, searchoptions: { sopt: ['eq','ne','nu','nn']}, searchrules:{integer:true, minValue:0, maxValue:1}";
					} else {
						$frm = "align:'right', formatter:'number', formatoptions: {thousandsSeparator: '.', decimalPlaces: 0, defaulValue: 0}";
						$frm .= ",editoptions:{'size':20}, editrules:{" . $reqrd . " integer: true, maxValue: 999999999 } ";
					}
					break;
				case 16:
					$frm = "align:'center', formatter: 'checkbox', formatoptions: { disabled: true}, editoptions: { value: '1:0' }";
					$frm .= ", edittype: 'checkbox', editrules: {required: false}, searchoptions: { sopt: ['eq','ne','nu','nn']}, searchrules:{integer:true, minValue:0, maxValue:1}";
					break;
				case 4:
				case 5:
				case 246:
					$frm = "align:'right', formatter:'number', formatoptions: {decimalSeparator:',', thousandsSeparator: '.', decimalPlaces: 2, defaulValue: 0}";
					$frm .= ",editoptions:{'size':20}, editrules:{" . $reqrd . "}";
					break;
				case 10:
					$frm = "align:'right', formatter:'date', formatoptions: {srcformat:'Y-m-d',newformat:'d/m/Y'}";
					$frm .= ",editoptions:{'size':20}, editrules:{" . $reqrd . "}";
					break;
				case 12:
					$frm = "align:'right', formatter:'date', formatoptions: {srcformat:'Y-m-d H:i:s',newformat:'d/m/Y H:i'}";
					$frm .= ",editoptions:{'size':20}, editrules:{" . $reqrd . "}";
					break;
				case 11:
					$frm = "align:'right', formatter:'date', formatoptions: {srcformat:'H:i:s',newformat:'H:i:s'}";
					$frm .= ",editoptions:{'size':20}, editrules:{" . $reqrd . "}";
					break;
				case 253:
				case 254:
					$frm = "align:'left'";
					$frm .= ",editoptions:{'size':20}, editrules:{" . $reqrd . "}";
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

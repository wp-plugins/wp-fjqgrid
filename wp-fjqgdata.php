<?php
/*
AJAX Get data from table pages with [wp-fjqgrid Table="..."]
*/
if( !class_exists( 'FjqGridData' ) )
{
	class FjqGridData
	{
		private $wpf_code;
		public function __construct( $name, $code, $VER )
		{
			$this->wpf_code = $code;
		}
		
		// Handles ajax GET/POST 
		public function fjqg_header( $tablename )
		{
			$options = stripslashes_deep( get_option( $this->wpf_code ) ); 
			$allowed_tables = explode(',',$options['allowed']);
			if ( in_array( $tablename, $allowed_tables ) ) {

				$is_search = isset( $_GET['_search'] ) ? $_GET['_search'] : false;
				$rows = isset($_GET['rows'] ) ? $_GET['rows'] : 0; //page size
				$page = isset($_GET['page'] ) ? $_GET['page'] : 0; //page to get
				$sidx = isset($_GET['sidx'] ) ? $_GET['sidx'] : 'id'; //sort by
				$sord = isset($_GET['sord'] ) ? $_GET['sord'] : 'ASC'; //sort order
				$oper = isset($_POST['oper'] ) ? $_POST['oper'] : false;
				
				// check also user right to del/edit/ins
				if ( $oper  AND ( $options['capability']=="" OR current_user_can ( $options['capability'] ) ) )
				{
					//id=2&city=Trieste&date=22%2F01%2F2014+00%3A00&population=150000&price=23.00&note=&code=&oper=edit
					$post_str = $_POST;
					require_once('inc/wp-fjqgrid-db.php');
					$fjqdb = new FjqGridDB( $tablename );
					$id = $_POST['id'];
					//$data = array();
					foreach( $post_str as $k => $v ) {
						if ( $k != 'oper' AND $k != 'id') {
							$k = addslashes( $k );
							$v = addslashes( $v );
							$dataq[$k] = $v;
							$names[] = "$k";
							$values[] = "'$v'";
						}
					}
					//$data = "(" . implode(",", $names) . ") VALUES (" . implode(",", $values) . ")";
			
					$opok = false;
					switch ( $oper ) {
						case 'del':
							$opok = $fjqdb->delete_row( $id );
						break;
						case 'edit':
							$opok = $fjqdb->update_row( $id, $dataq );
						break;
						case 'add':
							$opok = $fjqdb->insert_row( $dataq );
						break;
					}
					if ( !$opok ) {
						//TODO if error echo something...echo '[{"oper": '.$oper.'}]';
						echo "error doing operation ".$oper;
					}
					die;
				} 
				else 
				{
					$sqlwhere = $this->fjqg_builfilter( $is_search );
					$options = stripslashes_deep( get_option( $this->wpf_code ) ); 
					$allowed_tables = explode( ',',$options['allowed'] );
					ob_clean();
					if ( in_array( $tablename, $allowed_tables ) ) 
						echo $this->fjqg_data( $tablename, $sqlwhere, $page, $rows, $sidx, $sord );
					else // no rights on this table - null json object is returned
						echo '[{"id": null}]';
				}
			}
			die; 
		}			

		private function fjqg_data( $tablename, $sqlwhere, $page, $rows, $sidx, $sord )
		{
			require_once('inc/wp-fjqgrid-db.php');
			$fjqdb = new FjqGridDB( $tablename );
			
			$data = new stdClass();
			$data->page = $page;
			$query = array('fields' =>'count');
			$records = $fjqdb->get_rows( $query );
			$data->total = ceil ( $records/$rows );
			$data->records = $records;
			$query = array(
				'fields' =>'',
				'where'  =>$sqlwhere,
				'orderby'=>$sidx,
				'order'  =>$sord,
				'number' =>$rows,
				'offset' =>($page-1)*$rows);
			$data->rows = $fjqdb->get_rows( $query );
			$out = json_encode( $data );
			//$out = '{"page":"1","total":1,"records":"'.$records.'","rows":[{"id":"1","city":"Udine","date":"2014-01-21","population":"90000","price":"20.00","note":"note 1","code":""},{"id":"2","city":"Trieste","date":"2014-01-22","population":"150000","price":"23.00","note":"","code":""}]}';
			return $out;
		}

		private function fjqg_builwhere( $json_filter )
		{
			$strwhere = "";
			$oparray = array(
				'eq' => " = ",
				'ne' => " <> ",
				'lt' => " < ",
				'le' => " <= ",
				'gt' => " > ",
				'ge' => " >= ",
				'bw' => " LIKE ",
				'bn' => " NOT LIKE ",
				'in' => " IN ",
				'ni' => " NOT IN ",
				'ew' => " LIKE ",
				'en' => " NOT LIKE ",
				'cn' => " LIKE ",
				'nc' => " NOT LIKE "
			);
			if ( $json_filter ) {
				$array_filter = json_decode( $json_filter, true );
				if ( is_array( $array_filter ) ) {
					$groupOp = $array_filter['groupOp'];
					$rules_array = $array_filter['rules'];
					$cnt = 0;
					foreach($rules_array as $k => $v) {
						$field = $v['field'];
						$op = $v['op'];
						$data = $v['data'];
						if (isset( $data ) && isset( $op )) {
							$cnt++;
							$data = $this->fjqg_to_sql( $field, $op, $data );
							if ( $cnt == 1 )
								$strwhere = " AND ";
							else
								$strwhere.= " " . $groupOp . " ";
							switch ( $op ) {
								case 'in':
								case 'ni':
									$strwhere.= $field . $oparray[$op] . " (" . $data . ")";
									break;
								default:
									$strwhere.= $field . $oparray[$op] . $data;
							}
						}
					}
				}
			}
			return $strwhere;
		}

		private function fjqg_builfilter( $is_search )
		{
			global $wpfjqg;
			//_search=true
			//filters={"groupOp":"AND","rules":[{"field":"note","op":"bw","data":"n"}]}
			$session_filter = "";
			if ($is_search == 'true') {
				$search_field = isset( $_REQUEST['searchField'] ) ? $wpfjqg->fjqg_strip($_REQUEST['searchField']) : "";
				if ( $search_field == "") {
					$search_filter = $wpfjqg->fjqg_strip( $_REQUEST['filters'] );
					$session_filter = $this->fjqg_builwhere( $search_filter );
				}
				else {			
					$req_searchString = $wpfjqg->fjqg_strip( $_REQUEST['searchString'] );
					$req_searchOper = $wpfjqg->fjqg_strip( $_REQUEST['searchOper'] );
					$session_filter.= " AND " . $search_field;
					switch ( $req_searchOper ) {
					case "eq":
						if ( is_numeric( $req_searchString ) ) {
							$session_filter.= " = " . $req_searchString;
						}
						else {
							$session_filter.= " = '" . $req_searchString . "'";
						}
						break;

					case "ne":
						if ( is_numeric( $req_searchString ) ) {
							$session_filter.= " <> " . $req_searchString;
						}
						else {
							$session_filter.= " <> '" . $req_searchString . "'";
						}
						break;

					case "lt":
						if ( is_numeric( $req_searchString ) ) {
							$session_filter.= " < " . $req_searchString;
						}
						else {
							$session_filter.= " < '" . $req_searchString . "'";
						}
						break;

					case "le":
						if ( is_numeric( $req_searchString ) ) {
							$session_filter.= " <= " . $req_searchString;
						}
						else {
							$session_filter.= " <= '" . $req_searchString . "'";
						}
						break;

					case "gt":
						if ( is_numeric( $req_searchString ) ) {
							$session_filter.= " > " . $req_searchString;
						}
						else {
							$session_filter.= " > '" . $req_searchString . "'";
						}
						break;

					case "ge":
						if ( is_numeric( $req_searchString ) ) {
							$session_filter.= " >= " . $req_searchString;
						}
						else {
							$session_filter.= " >= '" . $req_searchString . "'";
						}
						break;

					case "ew":
						$session_filter.= " LIKE '%" . $req_searchString . "'";
						break;

					case "en":
						$session_filter.= " NOT LIKE '%" . $req_searchString . "'";
						break;

					case "cn":
						$session_filter.= " LIKE '%" . $req_searchString . "%'";
						break;

					case "nc":
						$session_filter.= " NOT LIKE '%" . $req_searchString . "%'";
						break;

					case "in":
						$session_filter.= " IN (" . $req_searchString . ")";
						break;

					case "ni":
						$session_filter.= " NOT IN (" . $req_searchString . ")";
						break;

					case "bw":
					default:
						$req_searchString.= "%";
						$session_filter.= " LIKE '" . $req_searchString . "'";
						break;
					}
				}
				$_SESSION["wp-fjqgrid_filter"] = $session_filter;
			}
			elseif ($is_search == 'false') {
				$_SESSION["wp-fjqgrid_filter"] = '';
			}
			return $session_filter;
		}

		private function fjqg_to_sql( $field, $oper, $v )
		{
			if ($oper == 'bw' || $oper == 'bn') return "'" . addslashes( $v ) . "%'";
			else
			if ($oper == 'ew' || $oper == 'en') return "'%" . addcslashes( $v ) . "'";
			else
			if ($oper == 'cn' || $oper == 'nc') return "'%" . addslashes( $v ) . "%'";
			else return "'" . addslashes( $v ) . "'";
		}
	}
}
?>
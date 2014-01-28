<?php
/*
Plugin Name: WP FjqGrid
Plugin URI: http://wordpress.org/extend/plugins/wp-fjqgrid/
Description: jqGrid porting to wordpress
Version: 0.01
Author: faina09
Author URI: http://profiles.wordpress.org/faina09
License: GPLv2 or later
*/
$VER = '0.01';
defined( 'ABSPATH' ) OR exit;

require_once('wp-fjqgdata.php');
require_once('wp-fjqgrid.php');

global $wpfjqg;

$wpfjqg = new FjqGrid( 'WP FjqGrid', 'wp-fjqgrid', $VER );
register_activation_hook( __FILE__, array( 'FjqGrid', 'fplugin_activate' ) );
register_uninstall_hook( __FILE__, array( 'FjqGrid', 'fplugin_uninstall' ) );
register_deactivation_hook( __FILE__, array( 'FjqGrid', 'fplugin_deactivate' ) );

class wpfjqAjax
{
	public function __construct()
	{
		if ( is_admin() ) {
			add_action( 'wp_ajax_nopriv_ajax-wpfjqg', array( &$this, 'ajax_call' ) );
			add_action( 'wp_ajax_ajax-wpfjqg', array( &$this, 'ajax_call' ) );
		}
	}

	public function ajax_call()
	{
		global $wpfjqg;
		if ( ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce( $_REQUEST['nonce'], $wpfjqg->wpf_code.'-nonce' ) )
			die ( 'Invalid Nonce' );

		header( "Content-Type: application/json" );
		if ( isset( $_GET['table'] ) ) {
			$ajax = true;
			$table = $_GET['table'];
			$fjqgrid_json = new FjqGridData($wpfjqg->wpf_name, $wpfjqg->wpf_code, $wpfjqg->VER );
			$fjqgrid_json->fjqg_header();
		}
  		else //just for debug 
			echo json_encode( array(
				'success' => 'no GET[table] set ',
				'time' => time(),
				'isajax' => $ajax,
				'table' => $table
			) );
		die(0);
	}
}

$wpfjqAjax = new wpfjqAjax();

?>
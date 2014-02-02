<?php
if( !class_exists( 'FjqGrid' ) )
{
	class FjqGrid
	{
		public $wpf_name;
		public $wpf_code;
		public $VER;
		private $wpf_path;
		private $wpf_pathshort;
		private $wpf_loglevel;
		
		/// $name=human name of plugin, used in menu etc
		/// $code=plugin code used in filenames, default shortcode, etc
		public function __construct( $name = 'WP Fxxx', $code = 'wp-fxxx', $VER = '0.01' )
		{
			$option_page = true; // display voice menu entry in 'Settings'
			$menu_page = false; // display voice menu entry in 'Tools' or any other
			$this->wpf_name = $name;
			$this->wpf_code = $code;
			$this->VER = $VER;
			$this->wpf_path = plugin_dir_url( __FILE__ ); // http://..../plugins/wp-fxxx/
			//$this->wpf_file = plugin_basename( __FILE__ ); // wp-fxxx/wp-fxxx.php
			$this->wpf_pathshort = basename( dirname( __FILE__ )); // wp-fxxx
			$options = get_option( $code );
			$this->wpf_loglevel = isset( $options['log_level'] ) ? (int)$options['log_level'] : 3;
			
			add_action( 'plugins_loaded', array( $this, 'fplugin_init' ) );
			
			// Load i18n language support
			load_plugin_textdomain( $code, false, $this->wpf_pathshort.'/languages' );
					
			// voice menu entry in 'Settings'
			if ( $option_page ) {
				//options setting
				add_action( 'admin_init', array( $this, 'add_foption_init' ) );
				//menu setting
				add_action( 'admin_menu', array( $this, 'add_foption_page' ) );
			}
			// voice menu entry in any other submenu
			if ( $menu_page ) add_action( 'admin_menu', array( $this, 'add_fsubmenu_page' ) );
			
			// add voice 'Settings' in installed plugin list
			add_filter( 'plugin_action_links', array( $this, 'fplugin_action_links' ), 10, 2 ) ;
			
			add_shortcode( $code, array( $this, 'add_fshortcode' ) );
		}

	/**
	* section 0. Plugin first run setup and uninstall routines 
	**/
		static function fplugin_activate()
		{
			//echo 'activated';
		}
		
		static function fplugin_uninstall()
		{
			//if uninstall not called from WordPress exit
			if ( !defined( 'WP_UNINSTALL_PLUGIN' ) )
				exit ();
			//echo 'uninstalled';
		}
		
		static function fplugin_deactivate()
		{
	        if ( ! current_user_can( 'activate_plugins' ) )
	            return;
	        $plugin = isset( $_REQUEST['plugin'] ) ? $_REQUEST['plugin'] : '';
			if ( $plugin=='wp-fjqgrid/index.php' ) {
				// delete options on deactivate
		    	$options = get_option( 'wp-fjqgrid' );
		    	if ( $options['do_uninstall']==1 ) {
		        	//drop tables in list
		        	global $wpdb;
		    		foreach (explode ( ",", $options['do_drop']) as $table )
		    			$wpdb->query("DROP TABLE IF EXISTS `$table`");
		        	//remove option entry
		        	delete_option( 'wp-fjqgrid' );
		    	}
			}
		}

		
	/**
	* section 1. Plugin init styles and scripts 
	**/
		public function fplugin_init()
		{
			add_action( 'wp_enqueue_scripts', array ($this, 'fplugin_load_styles') );
			add_action( 'wp_enqueue_scripts', array ($this, 'fplugin_load_scripts') );
		}
		
		public function fplugin_load_styles()
		{
			//from local, no CDN available
			wp_register_style( 'jq_ui', $this->wpf_path.'themes/sunny/jquery-ui.min.css' );
			wp_enqueue_style( 'jq_ui' );
			wp_register_style( 'jqg_ui', $this->wpf_path.'jqGrid/css/ui.jqgrid.css' );
			wp_enqueue_style( 'jqg_ui' );
			
			// styles defined in this plugin override others
			wp_register_style( $this->wpf_code, $this->wpf_path.'style.css' );
			wp_enqueue_style( $this->wpf_code );
		}
		
		public function fplugin_load_scripts()
		{
			// my script depends from jquery
			wp_register_script( $this->wpf_code, $this->wpf_path.'jscript.js', array( 'jquery' ) );
			wp_enqueue_script( $this->wpf_code );
			
			//from local, no CDN available
			//if ( $this->wpf_loglevel == 3 )//DEBUG only
			//	wp_register_script( 'jqg_code', $this->wpf_path.'jqGrid/js/jquery.jqGrid.src.js' );
			//else
				wp_register_script( 'jqg_code', $this->wpf_path.'jqGrid/js/jquery.jqGrid.min.js' );
			wp_enqueue_script( 'jqg_code' );
			$lang = substr(get_locale(), 0, 2);		
			wp_register_script( 'jqg_local',$this->wpf_path.'jqGrid/js/i18n/grid.locale-'.$lang .'.js' );
			wp_enqueue_script( 'jqg_local' );
		}

	/**
	* section 2. Plugin admin menu and links
	**/
		public function fplugin_action_links( $links, $file ) {
	        $this_plugin = plugin_basename( __FILE__ );
		    // check to make sure we are on the correct plugin
		    if ( str_replace( 'index.php', 'wp-fjqgrid.php', $file ) == $this_plugin ) {
		        // the anchor tag and href to the URL we want. For a "Settings" link, this needs to be the url of your settings page
		        $settings_link = '<a href="' . get_bloginfo('wpurl') . '/wp-admin/options-general.php?page='.$this->wpf_name.'">Settings</a>';
		        // add the link to the list
		        array_unshift( $links, $settings_link );
		    }
		    return $links;
		}
		
		// options stored in DB entry '$this->wpf_code'
		function add_foption_init()
		{
			// register_setting( $option_group, $option_name, $sanitize_callback )
			// settings_fields( $option_group )
			register_setting( $this->wpf_code.'_options', $this->wpf_code, array( $this, 'foptions_validate' ) );
		}
		
		public function add_foption_page()
		{
			add_options_page( $this->wpf_name.' settings', $this->wpf_name, 'manage_options', $this->wpf_code, array( $this, 'fplugin_configure' ) );
		}
		
		public function add_fsubmenu_page()
		{
			// ref: http://codex.wordpress.org/Function_Reference/add_submenu_page
			$parent_slug = 'tools.php';
			$page_title = $this->wpf_name;
			$menu_title = $this->wpf_name;
			$capability = 'read';
			$menu_slug  = $this->wpf_code;
			$function   = array( $this, 'echo_fsubmenu' );
			add_submenu_page( $parent_slug, $page_title, $menu_title, $capability, $menu_slug, $function );
		}

		public function echo_fsubmenu()
		{
			echo 'PAGINA SUBMENU IN TOOLS DI '.$this->wpf_name;
		}
		
	/**
	* section 3. Plugin admin configuration
	**/
		// Sanitize and validate input. Accepts an array, return a sanitized array.
		function foptions_validate( $input )
		{
			// value is either 0 or 1
			if ( !isset ( $input['active']) ) $input['active'] = 0;
			// text option must be safe text with no HTML tags, and no whitespaces
		    $input['allowed'] = preg_replace('/\s+/', '', wp_filter_nohtml_kses($input['allowed'] ) );
		    $input['do_drop'] = preg_replace('/\s+/', '', wp_filter_nohtml_kses($input['do_drop'] ) );
		    // textarea option must be safe text with the allowed tags for posts and no CRLF
		    $crlf = array("\r\n", "\n", "\r");
		    $input['frmtfield'] = str_replace( $crlf, '', wp_filter_post_kses($input['frmtfield'] ) );
		    if ( isset( $input['do_createtable'] ) AND $input['do_createtable'] ) {
		    	$createtable = str_replace( $crlf, '', wp_filter_nohtml_kses( $input['createtable'] ) );
		    	$frmfields = explode ( "|", $createtable );
				foreach ( $frmfields as $frmfield )
				{
					$frmarray = explode ( "::", $frmfield );
					if ( $frmarray[0]=="TABLENAME" ) {
						$tablename = $frmarray[1];
					}
					else if ( $frmarray[0]=="TABLEKEY" ){
						$tablekey = $frmarray[1];
					}
					else {
						//here are the fields names - fileds types couples
						$fields[] = $frmarray[0];
						$types[]  = $frmarray[1];
					}
				}
				require_once('inc/wp-fjqgrid-db.php');
		    	$fjqgridDB = new FjqGridDB( $tablename );
		    	$fjqgridDB->create_table( $fields, $types, $tablekey );
		    }
		    //do not save these two in options
		    if ( isset( $input['do_createtable'] ) ) unset ( $input['do_createtable'] ); 
		    if ( isset( $input['createtable'] ) ) unset ( $input['createtable'] ); 
		    return $input;
		}

		public function fplugin_configure()
		{
			$createtable ="TABLENAME::wpf_jqgrid_sample|
TABLEKEY::ID|
ID::int(11) NOT NULL AUTO_INCREMENT|
City::varchar(100) DEFAULT NULL|
Temp_C::decimal(10,2) DEFAULT NULL|
Date::datetime DEFAULT NULL|
High::int(5) NOT NULL|
Verified::int(1) DEFAULT NULL";
			$options = array (
				'active' => true,
				'log_level' => 0,
				'capability' => "level_10",
				'allowed' => "wpf_jqgrid_sample",
				'frmtfield' => "wpf_jqgrid_sample::Date::align:'center',formatter:'date',formatoptions:{srcformat:'Y-m-d H:i:s',newformat:'Y-m-d'},editrules: {required: false, date: true}|wpf_jqgrid_sample::ID::hidden:true",
				//'frmtfield' => "wpf_jqgrid_sample::DateTime::align:'center',editoptions:{'size':40},formatter:'date',formatoptions:{srcformat:'Y-m-d H:i:s',newformat:'d/m/Y H:i'}|wpf_jqgrid_sample::ID::hidden:true",
				'do_uninstall' => false,
				'do_drop' => "wpf_jqgrid_sample",
				'do_createtable' => false,
				'createtable' => $createtable
				);
			// current_user_can ( $options['capability'] )
			/*	User Level 0 converts to Subscriber
				User Level 1 converts to Contributor
				User Level 2 converts to Author
				User Level 3 converts to Editor
				User Level 4 converts to Editor
				User Level 5 converts to Editor
				User Level 6 converts to Editor
				User Level 7 converts to Editor
				User Level 8 converts to Administrator
				User Level 9 converts to Administrator
				User Level 10 converts to Administrator
			*/
			$cap = array (
				'subscriber' => 'level_0',
				'contributor' => 'level_1',
				'author' => 'level_2',
				'editor' => 'level_3',
				'administrator' => 'level_10',
				);
			?>
			<div class="wrap">
			<?php screen_icon(); ?>
				<h2><?php printf(__( '%1$s Configuration Options' , $this->wpf_name), $this->wpf_name);?></h2>
				<form method="post" action="options.php">
					<?php settings_fields( $this->wpf_code.'_options' );
					$options_read = stripslashes_deep( get_option( $this->wpf_code ) ); 
					if ( is_array( $options_read ) )
						$options = array_merge( $options, $options_read );
					else
						$options['do_createtable'] = true; //set this on first run
					$options['frmtfield'] = str_replace("|", "|\r\n", $options['frmtfield']);
					?>
					<table class="form-table">
						<tr valign="top"><th scope="row"><?php _e('active' , $this->wpf_code); ?></th>
							<td><input type="checkbox" name="<?php echo $this->wpf_code; ?>[active]" value="1" <?php checked('1', $options['active']); ?> /></td>
						</tr>
						<tr valign="top"><th scope="row"><?php _e('log level' , $this->wpf_code); ?></th>
							<td><select name="<?php echo $this->wpf_code; ?>[log_level]">
								  <option value="0" <?php selected( $options['log_level'], 0 ); ?>>DISABLED</option>
								  <option value="1" <?php selected( $options['log_level'], 1 ); ?>>ERROR</option>
								  <option value="2" <?php selected( $options['log_level'], 2 ); ?>>INFO</option>
								  <option value="3" <?php selected( $options['log_level'], 3 ); ?>>DEBUG</option>
								</select>
							 </td>
						</tr>
						<tr valign="top"><th scope="row"><?php _e('role required to edit tables' , $this->wpf_code); ?></th>
							<td>
							<select name="<?php echo $this->wpf_code; ?>[capability]">
								<option value="" <?php selected( $options['capability'], "" ); ?>>No login required</option>
								<?php global $wp_roles; ?>
								<?php foreach( $wp_roles->role_names as $k => $v ): ?>
									<?php if( isset($cap[$k]) ): ?>
									<option value="<?php echo $cap[$k]; ?>" <?php selected( $options['capability'], $cap[$k] ); ?>><?php echo $v; ?></option>
									<?php endif; ?>
								<?php endforeach; ?>
							</select>
							</td>
						</tr>											
						<tr valign="top"><th scope="row"><?php _e('allowed tables (comma separated)' , $this->wpf_code); ?></th>
							<td><input type="text" style="width:80%;" name="<?php echo $this->wpf_code; ?>[allowed]" value="<?php echo $options['allowed']; ?>"/></td>
						</tr>
						<tr valign="top"><th scope="row"><?php _e('custom fields formatting (| separated) es: table::field::align:\'center\',editoptions:{\'size\':40}|' , $this->wpf_code); ?></th>
							<td><textarea rows="3" cols="80" style="height: 100px; width: 60%;" name="<?php echo $this->wpf_code; ?>[frmtfield]"><?php echo $options['frmtfield']; ?></textarea></td>
						</tr>
						<tr valign="top" style="background-color:#72969F;"><th scope="row"><?php _e('clean all on deactivate' , $this->wpf_code); ?></th>
							<td><input type="checkbox" name="<?php echo $this->wpf_code; ?>[do_uninstall]" value="1" <?php checked('1', $options['do_uninstall']); ?> /></td>
						</tr>
						<tr valign="top" style="background-color:#72969F;"><th scope="row"><?php _e('tables to drop on deactivate (comma separated)' , $this->wpf_code); ?></th>
							<td><input type="text" style="width:80%;" name="<?php echo $this->wpf_code; ?>[do_drop]" value="<?php echo $options['do_drop']; ?>"/></td>
						</tr>						
						<tr valign="top" style="background-color:#558c9a;"><th scope="row"><?php _e('execute create table' , $this->wpf_code); ?></th>
							<td><input type="checkbox" name="<?php echo $this->wpf_code; ?>[do_createtable]" value="1" <?php checked('1', $options['do_createtable']); ?> /></td>
						</tr>											
						<tr valign="top" style="background-color:#558c9a;"><th scope="row"><?php _e('create sql table' , $this->wpf_code); ?></th>
							<td>
							<textarea rows="3" cols="80" style="height: 100px; width: 60%;" name="<?php echo $this->wpf_code; ?>[createtable]"><?php echo $options['createtable']; ?></textarea></td>
						</tr>
						<tr><td colspan="2">
							<p class="submit">
							<input type="submit" class="button-primary" value="<?php _e('Save Changes', $this->wpf_code) ?>" />
							</p>
						</td></tr>
					</table>
				</form>
			</div>
			<?php
		}
		
	/**
	* section 4. Shortcodes
	**/
		public function add_fshortcode( $atts )
		{
			extract( shortcode_atts( array(
				'idtable' => 1,
				'table' => null,
				'caption' => null,
				'editable' => 'false',
				'fields' => ''
				), $atts, $this->wpf_code));
			$options = get_option( $this->wpf_code );
			$options['table'] = $table;
			$options['idtable'] = $idtable;
			$options['caption'] = $caption;
			$options['editable'] = $editable;
			$allowed_tables = explode(',',$options['allowed']);
			$options['nonce'] = wp_create_nonce( $this->wpf_code.'-nonce' );
			if (in_array($table, $allowed_tables)) 
				return $this->show_fshortcode( $options );
			else
				return __('No rights to open this table', $this->wpf_code);
		}
		
		private function show_fshortcode( $options )
		{
			if ( ! $options['active'] ) {
				$this->fplugin_log( "not active shortcode - exit!" );
				return '<!-- SHORTCODE NON ATTIVO DI '.$this->wpf_name.' con ID='.$ident.' VER. '.$this->VER.' --><br/>';
			}
			else {
				require_once('inc/wp-fjqgrid-shortcodes.php');
				$fjqgris_sc = new FjqGridShortCodes($this->wpf_name, $this->wpf_code, $this->VER );
				return $fjqgris_sc->fjqgrid( $options );
			}
		}
		
				
	/** Common functions
	* Levels are: 1 for errors, 2 for normal activity, 3 for debug.
	*/
		public function fplugin_log( $text='', $level=2 ) {
		    if ( $this->wpf_loglevel < $level ) return;
		    //$db = debug_backtrace(false);
		    $time = date('d-m-Y H:i:s ');
		    switch ($level) {
		        case 1: $time .= '- ERROR';
		            break;
		        case 2: $time .= '- INFO ';
		            break;
		        case 3: $time .= '- DEBUG';
		            break;
		    }
		    if (is_array( $text ) || is_object( $text ))
		    	$text = print_r( $text, true );
		    file_put_contents(dirname(__FILE__) . '/log.txt', $time . ' - ' . $text . "\n", FILE_APPEND | FILE_TEXT);
		}
		
		public function fjqg_strip ( $value )
		{
			$mq = get_magic_quotes_gpc();
			if( !$mq )
		  	{
		    	if( is_array($value) )  
					if ( array_is_associative( $value ) )
					{
						foreach( $value as $k=>$v )
							$tmp_val[$k] = stripslashes( $v );
						$value = $tmp_val; 
					}				
					else  
						for( $j = 0; $j < sizeof( $value ); $j++ )
		        			$value[$j] = stripslashes( $value[$j] );
				else
					$value = stripslashes( $value );
			}
			return $value;
		}
	}
}
?>
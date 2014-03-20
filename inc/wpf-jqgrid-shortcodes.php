<?php
if ( !class_exists( 'FjqGridShortCodes' ) ) {

	class FjqGridShortCodes
	{

		private $wpf_name;
		private $wpf_code;
		private $VER;

		public function __construct( $name, $code, $VER )
		{
			$this->wpf_name = $name;
			$this->wpf_code = $code;
			$this->VER = $VER;
		}

		public function fjqgrid( $options )
		{
			global $wpfjqg;
			$msg = '<!--  SHORTCODE ATTIVO DI ' . $this->wpf_name . ' per la Tabella ' . $options['table'] . ' VER. ' . $this->VER . ' -->';
			$wpfjqg->fplugin_log( $msg );
			return $msg . $this->fjqg_javascript( $options );
		}

		private function fjqg_javascript( $options )
		{
			global $wpfjqg;
			require_once('wpf-jqgrid-dbmodel.php');
			$table = $options['table'];
			$idtable = $options['idtable'];
			$caption = $options['caption'] == '' ? $table : $options['caption'];
			$sortbyopt = $options['sortby'];
			$sortby = strtok ( $sortbyopt, ',' );
			$sortord = strtok ( ',' );
			$wpfjqg->fplugin_log( 'sort option3', $sortby, 3 );
			$wpfjqg->fplugin_log( 'sort option3', $sortord, 3 );
			$nonce = $options['nonce'];
			// TODO check if network and use network_admin_url()
			$url = admin_url() . "/admin-ajax.php?action=ajax-wpfjqg&nonce=" . $nonce . "&table=" . $table;
			$optionsfrmtfield = preg_replace( '/\r|\n/m', '', $wpfjqg->fjqg_strip( $options['frmtfield'] ) );
			$wpfjqgModel = new FjqGridDbModel( $table, $optionsfrmtfield );
			$colNames = $wpfjqgModel->fjqg_colNames();
			$colModels = $wpfjqgModel->fjqg_colModels();
			$navGrid = $this->fjqg_navGrid( $options );
			$out = "
			<table id='wpfjqg_$idtable'></table><div id='wpfjqgNav_$idtable'></div>
			<div id='wpfjqgSearch_$idtable' class='fm-button ui-state-default ui-corner-all fm-button-icon-right ui-reset'>" . __( 'Search', $this->wpf_code ) . "<span class='ui-icon ui-icon-search'/></div>
			<script type='text/javascript'>
			var lastSel;
			jQuery('#wpfjqg_$idtable').jqGrid({
            url: '$url',
            datatype: 'json',
            colNames: [$colNames],
            colModel: [$colModels],
            pager: '#wpfjqgNav_$idtable',
            rowNum: 10, rowList: [2, 5, 10, 25, 50, 500],
			autowidth: true,
            sortname: '".$sortby."', sortorder: '".$sortord."',
            viewrecords: true,
            jsonReader: { repeatitems: false },
            width: 640,
            caption: '$caption',
            height: '100%',
            editurl: '$url',
            edit_options:{'closeAfterEdit':true},
			/* fjqg_onlineEdit */
			onSelectRow: function(ids) {
		        if(ids == null) {
			        ids=0;
		        	}
				}
            });
			/* autofilter */
			jQuery('#wpfjqg_$idtable').jqGrid('filterToolbar',{stringResult:true, searchOnEnter:false});
			</script><script type='text/javascript'>
			$navGrid
			</script>";
			return $out;
		}

		private function fjqg_onlineEdit( $options )
		{
			$out = "/*
            loadComplete: function () {
                    //carica eventuali errori
                    $('#LastError').trigger('reloadGrid', [{ page: 1}]);
                },
			ondblClickRow:function(id) {
				if(id && id!==lastSel)
				{
					jQuery('#phpgrid1').restoreRow(lastSel);
					jQuery('#edit_row_'+lastSel).show();
					jQuery('#save_row_'+lastSel).hide();
					lastSel=id;
				} 
				jQuery('#phpgrid1').editRow(id, true,
					function(){},
					function(){
						jQuery('#edit_row_'+id).show();
						jQuery('#save_row_'+id).hide();
						return true;},
					null,null,null,null,
					function(){
						jQuery('#edit_row_'+id).show();
						jQuery('#save_row_'+id).hide();return true;
						});
					jQuery('#edit_row_'+id).hide();
					jQuery('#save_row_'+id).show();},
			*/";
			return $out;
		}

		private function fjqg_navGrid( $options )
		{
			$idtable = $options['idtable'];
			$editable = ( $options['editable'] AND ( $options['capability'] == "" OR current_user_can( $options['capability'] ) ) ) ? "true" : "false";
			$out = "masterGrid = jQuery('#wpfjqg_$idtable').jqGrid('navGrid', '#wpfjqgNav_$idtable',
				{ edit: $editable, add: $editable, del: $editable }, //options
				{recreateForm: true,
				beforeShowForm: function (form) {jQuery('#Id', form).attr('readonly', 'readonly');},
				closeAfterEdit: true, width: 400, reloadAfterSubmit: true }, // edit options
				{recreateForm: true, closeAfterAdd: true, reloadAfterSubmit: true }, // add options
				{reloadAfterSubmit: true }, // del options
				{} // search options
				);
				//standard search button
				jQuery('#wpfjqgSearch_$idtable').click(function () {
					jQuery('#wpfjqg_$idtable').jqGrid('searchGrid',
					{ sopt: ['cn', 'bw', 'eq', 'ne', 'lt', 'gt', 'ew'] }
					);
				});";
			return $out;
		}
	}

}

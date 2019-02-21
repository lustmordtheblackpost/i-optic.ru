<?php

if( !defined( "in_ochki" ) && !defined( "in_ochki_admin" ) ) die( "You can't access this file directly" );

//
// Очень важно, чтобы объявление класса, у примеру "class moduleabout extends RootModule" было именно такое, какое есть сейчас. 
// Меняется только слово about в случае указанного примера. Также четко следите за регистром.
// Касаемо регистра. В модулях должны совпадать: название каталога, название в классе и название в БД (к примеру везде about или Projects или SuBwAy и тд.)
// 

class modulelenses extends RootModule
{
	var $dbinfo = null;
	var $curFilePath = "";
	
	var $global = true;
	var $gl_dbase_string = "`shop`.";
	
	function init( $dbinfo )
	{
		$this->dbinfo = $dbinfo;
		$this->curFilePath = $this->getRootFilePath( __FILE__ );
	}
	
	function getBlock( $ochki )
	{
		global $main, $mysql, $lang, $query, $utils;
		
		$ltype = $query->gp( "t" );
		$type = $query->gp( "p" );
		$dioSelected = $query->gp( "d" );
		$lense = $query->gp( "lense" );
		$add = $query->gp( "add" );
		
		$step = $query->gp( "step" );
		
		$od_sph = $query->gp( "od_sph" );
		$os_sph = $query->gp( "os_sph" );
		$od_cyl = $query->gp( "od_cyl" );
		$os_cyl = $query->gp( "os_cyl" );
		$od_axis = $query->gp( "od_axis" );
		$os_axis = $query->gp( "os_axis" );
		$od_add = $query->gp( "od_add" );
		$os_add = $query->gp( "os_add" );
		$oculus_pd = $query->gp( "oculus_pd" );
		$oculus_pd_d = $query->gp( "oculus_pd_d" );
		$oculus_pd_s = $query->gp( "oculus_pd_s" );
		$add_move = $query->gp( "add_move" );
		$add_shadow = $query->gp( "add_shadow" );
		$add_color = $query->gp( "add_color" );
		
		$lensesTypes = "";
		$types = $main->listings->getListingElementsArraySpec( 12, "`order` DESC, `id` ASC", "", 0, true );
		foreach( $types as $k => $v ) {
			$lensesTypes .= "
			<div class='onetype".( $ltype == $k ? " onetype_selected" : "" )."' data-id='".$k."'><div class='in'>
				<h3>".str_replace( "[", "<", str_replace( "]", ">", $lang->gp( $v['value'], true ) ) )."</h3>
				<div class='mg' style='background: #fff url(".$mysql->settings['local_folder']."files/upload/listings/".$v['image'].") no-repeat; background-size: cover; background-position: 50% 50%;'></div>
				<div class='rest'>
					<p>".$main->templates->psl( $v['additional_info'], true )."</p>
				</div>
				<div class='hover'><div class='tobot'><span>".$lang->gp( 90 )."</span><label>".$lang->gp( 91 )."</label></div></div>
			</div></div>
			";
		}
		
		$lTypes = "";
		$ltypes = $main->listings->getListingElementsArraySpec( 13, "`order` DESC, `id` ASC", "", 0, true );
		foreach( $ltypes as $k => $v ) {
			/*$ltypes_child = $main->listings->getListingElementsArraySpec( 13, "`order` DESC, `id` ASC", "", $k, true );
			$rest = '<ul>';
			foreach( $ltypes_child as $kk => $vv ) {
				$rest .= "<li><span>".$lang->gp( $vv['value'], true )."</span></li>";
			}
			$rest .= '</ul>';*/
			$lTypes .= "
			<div class='onetype".( $type == $k ? " onetype_selected" : "" )."' data-id='".$k."'><div class='in'>
				<div class='toper'><h3>".$lang->gp( $v['value'], true )."<div class='q'>?</div></h3></div>
				<div class='mg' style='background: #fff url(".$mysql->settings['local_folder']."files/upload/listings/".$v['image'].") no-repeat; background-size: cover; background-position: 50% 50%;'></div>
				<div class='hover'><div class='q' data-q=1>?<div class='hover'><div class='in'>".$main->listings->getListingElementAddById( $v['additional_info'], true )."</div></div></div><div class='tobot'><span>".$lang->gp( 90 )."</span><label>".$lang->gp( 91 )."</label></div></div>
			</div></div>
			";
		}
		
		$dio = "";
		$showOSDio = "0.00";
		$showODDio = "0.00";
		$dioTypes = $main->listings->getListingElementsArraySpec( 14, "`order` DESC, `id` ASC", "", 0, true );
                $dioSelectedCheck = 0;
		foreach( $dioTypes as $k => $v ) {
			$value = str_replace( "~", "", $lang->gp( $v['value'], true ) );
                        $dv = str_replace( ",", ".", $lang->gp( $v['value'], true ) );
                        $dv = str_replace( "+", "", str_replace( "-", "", $dv ) );
			$dio .= "<a href='#' onclick=\"
                            $( this ).parent().parent().find( 'input[type=text]' ).val( $( this ).attr( 'data-val' ) );
                            $( this ).parent().parent().find( 'input[type=hidden]' ).val( $( this ).attr( 'data-id' ) );
                            $( this ).parent().slideUp(100);
                            var checkDioMore = Math.abs( $( this ).attr( 'data-check' ) );
                            if( selectedDio && dioMoreCheck >= checkDioMore ) { 
                                selectedDio = ".$k.";
                                dioMoreCheck = checkDioMore; 
                                showLensesTypes( ".$k." );
                            } else if( selectedDio && dioMoreCheck < checkDioMore ) {
                                selectedDio = ".$k.";
                                dioMoreCheck = checkDioMore; 
                                showLensesTypes( ".$k." );
                            } else { 
                                selectedDio = ".$k.";
                                dioMoreCheck = checkDioMore; 
                                showLensesTypes( ".$k." );
                            } 
                            return false;
                        \" data-val='".$value."' data-check='".$dv."' data-id='".$k."' data-add='".$v['additional_info']."'>".$value."</a>";
			if( $od_sph == $k )
				$showODDio = $value;
			if( $os_sph == $k )
				$showOSDio = $value;
                        if( $dioSelected == $k ) {
                            $dioSelectedCheck = $dv;
                        }
		}
		
		$cyl = "";
		$showOSCyl = "0.00";
		$showODCyl = "0.00";
		$cylTypes = $main->listings->getListingElementsArraySpec( 22, "`order` DESC, `id` ASC", "", 0, true );
		foreach( $cylTypes as $k => $v ) {
			$value = str_replace( "~", "", $lang->gp( $v['value'], true ) );
			$cyl .= "<a href='#' onclick=\"$( this ).parent().parent().find( 'input[type=text]' ).val( $( this ).attr( 'data-val' ) ); $( this ).parent().parent().find( 'input[type=hidden]' ).val( $( this ).attr( 'data-id' ) ); $( this ).parent().slideUp(100); return false;\" data-val='".$value."' data-id='".$k."' data-add='".$v['additional_info']."'>".$value."</a>";
			if( $od_cyl == $k )
				$showODCyl = $value;
			if( $os_cyl == $k )
				$showOSCyl = $value;
		}
		
		$osi = "";
		$showOSAxis = "000";
		$showODAxis = "000";
		$osiTypes = $main->listings->getListingElementsArraySpec( 23, "`order` DESC, `id` ASC", "", 0, true );
		foreach( $osiTypes as $k => $v ) {
			$value = str_replace( "~", "", $lang->gp( $v['value'], true ) );
			$osi .= "<a href='#' onclick=\"$( this ).parent().parent().find( 'input[type=text]' ).val( $( this ).attr( 'data-val' ) ); $( this ).parent().parent().find( 'input[type=hidden]' ).val( $( this ).attr( 'data-id' ) ); $( this ).parent().slideUp(100); return false;\" data-val='".$value."' data-id='".$k."' data-add='".$v['additional_info']."'>".$value."</a>";
			if( $od_axis == $k )
				$showODAxis = $value;
			if( $os_axis == $k )
				$showOSAxis = $value;
		}
		
		$adds = "";
                $addsJS = "";
		$showOSAdd = "0.00";
		$showODAdd = "0.00";
		$addsTypes = $main->listings->getListingElementsArraySpec( 24, "`order` DESC, `id` ASC", "", 0, true );
		foreach( $addsTypes as $k => $v ) {
			$value = str_replace( "~", "", $lang->gp( $v['value'], true ) );
			$adds .= "<a href='#' onclick=\"$( this ).parent().parent().find( 'input[type=text]' ).val( $( this ).attr( 'data-val' ) ); $( this ).parent().parent().find( 'input[type=hidden]' ).val( $( this ).attr( 'data-id' ) ); $( this ).parent().slideUp(100); return false;\" data-val='".$value."' data-id='".$k."' data-add='".$v['additional_info']."'>".$value."</a>";
                        $addsJS .= "<a href=\'#\' onclick=\"$( this ).parent().parent().find( \'input[type=text]\' ).val( $( this ).attr( \'data-val\' ) ); $( this ).parent().parent().find( \'input[type=hidden]\' ).val( $( this ).attr( \'data-id\' ) ); $( this ).parent().slideUp(100); return false;\" data-val=\'".$value."\' data-id=\'".$k."\' data-add=\'".$v['additional_info']."\'>".$value."</a>";
			if( $od_add == $k )
				$showODAdd = $value;
			if( $os_add == $k )
				$showOSAdd = $value;
		}
                
                $addsOffices = "";
                $addsOfficesJS = "";
                $addsTypesOffices = $main->listings->getListingElementsArraySpec( 29, "`order` DESC, `id` ASC", "", 0, true );
		foreach( $addsTypesOffices as $k => $v ) {
			$value = str_replace( "~", "", $lang->gp( $v['value'], true ) );
			$addsOffices .= "<a href='#' onclick=\"$( this ).parent().parent().find( 'input[type=text]' ).val( $( this ).attr( 'data-val' ) ); $( this ).parent().parent().find( 'input[type=hidden]' ).val( $( this ).attr( 'data-id' ) ); $( this ).parent().slideUp(100); return false;\" data-val='".$value."' data-id='".$k."' data-add='".$v['additional_info']."'>".$value."</a>";
                        $addsOfficesJS .= "<a href=\'#\' onclick=\"$( this ).parent().parent().find( \'input[type=text]\' ).val( $( this ).attr( \'data-val\' ) ); $( this ).parent().parent().find( \'input[type=hidden]\' ).val( $( this ).attr( \'data-id\' ) ); $( this ).parent().slideUp(100); return false;\" data-val=\'".$value."\' data-id=\'".$k."\' data-add=\'".$v['additional_info']."\'>".$value."</a>";
			if( $od_add == $k )
				$showODAdd = $value;
			if( $os_add == $k )
				$showOSAdd = $value;
		}
		
		$pds = "";
		$showPds = "00.0";
		$pdsTypes = $main->listings->getListingElementsArraySpec( 25, "`order` DESC, `id` ASC", "", 0, true );
		foreach( $pdsTypes as $k => $v ) {
			$value = str_replace( "~", "", $lang->gp( $v['value'], true ) );
			$pds .= "<a href='#' onclick=\"$( this ).parent().parent().find( 'input[type=text]' ).val( $( this ).attr( 'data-val' ) ); $( this ).parent().parent().find( 'input[type=hidden]' ).val( $( this ).attr( 'data-id' ) ); $( this ).parent().slideUp(100); return false;\" data-val='".$value."' data-id='".$k."' data-add='".$v['additional_info']."'>".$value."</a>";
			if( $oculus_pd == $k )
				$showPds = $value;
		}
		
		$pds_uniq = "";
		$showOSpds = "00.0";
		$showODpds = "00.0";
		$pdsTypes = $main->listings->getListingElementsArraySpec( 26, "`order` DESC, `id` ASC", "", 0, true );
		foreach( $pdsTypes as $k => $v ) {
			$value = str_replace( "~", "", $lang->gp( $v['value'], true ) );
			$pds_uniq .= "<a href='#' onclick=\"$( this ).parent().parent().find( 'input[type=text]' ).val( $( this ).attr( 'data-val' ) ); $( this ).parent().parent().find( 'input[type=hidden]' ).val( $( this ).attr( 'data-id' ) ); $( this ).parent().slideUp(100); return false;\" data-val='".$value."' data-id='".$k."' data-add='".$v['additional_info']."'>".$value."</a>";
			if( $oculus_pd_d == $k )
				$showODpds = $value;
			if( $oculus_pd_s == $k )
				$showOSpds = $value;
		}
		
		$t = "
		<a name='lenses'></a>
		<div class='lenses all_lines'>
			<div class='topline'>
				<div class='ask'>
					<label class='q'>?</label>
					<div class='text'>
						".$lang->gp( 84 )."<br/>
						<a href='#' onclick=\"showCallbackfloat( 400 ); return false;\">".$lang->gp( 85 )."</a>
					</div>
				</div>
				<h2>".$this->getName()."</h2>
			</div>
			<div class='steps active'>
				<div>".$lang->gp( 86 )." <span>".$lang->gp( 87 )."</span></div>
			</div>
			<div class='lensesTypes'><div class='inner'>
				".$lensesTypes."
				<div class='clear'></div>
			</div></div>
			<div class='steps' id='receipe'>
				<div>".$lang->gp( 88 )." <span>".$lang->gp( 92 )."</span></div>
			</div>
			<div class='receipeList'><div class='inner'>
				<div class='item item_firstcolumn'>
					<div class='title'></div>
					<div class='first'>OD - Правый глаз</div>
					<div class='second'>OS - Левый глаз</div>
				</div>
				<div class='item'>
					<div class='title'><div class='q' data-q=1>?<div class='hover'><div class='in'>".$main->listings->getListingElementAddById( 654, true )."</div></div></div><span>Оптическая сила<br/>Sphere (SPH)</span></div>
					<div class='first'><input type=hidden id='od_sph' value='".( $od_sph ? $od_sph : "164" )."' />
						<input type=text disabled value='".$showODDio."' />
						<div class='icon'>
							<img src='".$mysql->settings['local_folder']."images/ardown.png' />
						</div>
						<div class='sub'>
							".$dio."
						</div>
					</div>
					<div class='second'><input type=hidden id='os_sph' value='".( $os_sph ? $os_sph : "164" )."' />
						<input type=text disabled value='".$showOSDio."' />
						<div class='icon'>
							<img src='".$mysql->settings['local_folder']."images/ardown.png' />
						</div>
						<div class='sub'>
							".$dio."
						</div>
					</div>
				</div>
				<div class='item'>
					<div class='title'><div class='q' data-q=1>?<div class='hover'><div class='in'>".$main->listings->getListingElementAddById( 655, true )."</div></div></div><span>Цилиндр<br/>Cylinder (CYL)</span></div>
					<div class='first'><input type=hidden id='od_cyl' value='".( $od_cyl ? $od_cyl : "264" )."' />
						<input type=text disabled value='".$showODCyl."' />
						<div class='icon'>
							<img src='".$mysql->settings['local_folder']."images/ardown.png' />
						</div>
						<div class='sub'>
							".$cyl."
						</div>
					</div>
					<div class='second'><input type=hidden id='os_cyl' value='".( $os_cyl ? $os_cyl : "264" )."' />
						<input type=text disabled value='".$showOSCyl."' />
						<div class='icon'>
							<img src='".$mysql->settings['local_folder']."images/ardown.png' />
						</div>
						<div class='sub'>
							".$cyl."
						</div>
					</div>
				</div>
				<div class='item'>
					<div class='title'><div class='q' data-q=1>?<div class='hover'><div class='in'>".$main->listings->getListingElementAddById( 656, true )."</div></div></div><span>Ось<br/>Axis</span></div>
					<div class='first'><input type=hidden id='od_axis' value='".( $od_axis ? $od_axis : "323" )."' />
						<input type=text disabled value='".$showODAxis."' />
						<div class='icon'>
							<img src='".$mysql->settings['local_folder']."images/ardown.png' />
						</div>
						<div class='sub'>
							".$osi."
						</div>
					</div>
					<div class='second'><input type=hidden id='os_axis' value='".( $os_axis ? $os_axis : "323" )."' />
						<input type=text disabled value='".$showOSAxis."' />
						<div class='icon'>
							<img src='".$mysql->settings['local_folder']."images/ardown.png' />
						</div>
						<div class='sub'>
							".$osi."
						</div>
					</div>
				</div>
				<div class='item' id='add_block'>
					<div class='title'><div class='q' data-q=1>?<div class='hover'><div class='in'>".$main->listings->getListingElementAddById( 668, true )."</div></div></div><span>Прибавка<br/>ADD</span></div>
					<div class='first'><input type=hidden id='od_add' value='".( $od_add ? $od_add : "" )."' />
						<input type=text disabled value='".$showODAdd."' />
						<div class='icon'>
							<img src='".$mysql->settings['local_folder']."images/ardown.png' />
						</div>
						<div class='sub'>
							".( !$ltype || $ltype == 79 ? $adds : $addsOffices )."
						</div>
					</div>
					<div class='second'><input type=hidden id='os_add' value='".( $os_add ? $os_add : "" )."' />
						<input type=text disabled value='".$showOSAdd."' />
						<div class='icon'>
							<img src='".$mysql->settings['local_folder']."images/ardown.png' />
						</div>
						<div class='sub'>
							".( !$ltype || $ltype == 79 ? $adds : $addsOffices )."
						</div>
					</div>
				</div>
				<div class='item item_upload invisible'><div class='inner'>
					<h4>Загрузить рецепт</h4>
					Вы может загрузить фотографию рецепта. (jpg, png не более 1мб)
					<div class='upload'>Загрузить</div>
				</div></div>
				<div class='item'>
					<div class='title'><div class='q' data-q=1>?<div class='hover'><div class='in'>".$main->listings->getListingElementAddById( 657, true )."</div></div></div><span>Межзрачковое расстояние<br/>PD</span></div>
					<div class='first".( $oculus_pd_d || $oculus_pd_s ? " invisible" : "" )."' id='oculus_pd_div'><input type=hidden id='oculus_pd' value='".( $oculus_pd ? $oculus_pd : "" )."' />
						<input type=text disabled value='".$showPds."' id='oculus_pd_input' />
						<div class='icon'>
							<img src='".$mysql->settings['local_folder']."images/ardown.png' />
						</div>
						<div class='sub'>
							".$pds."
						</div>
					</div>
					<div id='oculus_pd_check' style='position: absolute; top: 86px; width: 260px; z-index: 400;'>
						<div class='check'><div class='box".( $oculus_pd_d || $oculus_pd_s ? " box_sel" : "" )."' onclick=\"
							if( $( this ).hasClass( 'box_sel' ) ) {
								$( this ).removeClass( 'box_sel' );
								$( '#oculus_pd_ddiv' ).hide(); $( '#oculus_pd_d' ).val( '' ); $( '#oculus_pd_dinput' ).val( '0.00' );
								$( '#oculus_pd_sdiv' ).hide(); $( '#oculus_pd_s' ).val( '' ); $( '#oculus_pd_sinput' ).val( '0.00' );
								$( '#oculus_pd_div' ).show(); $( '#oculus_pd' ).val( '' ); $( '#oculus_pd_input' ).val( '0.00' );
							} else {
								$( this ).addClass( 'box_sel' );
								$( '#oculus_pd_ddiv' ).show(); $( '#oculus_pd_d' ).val( '' ); $( '#oculus_pd_dinput' ).val( '0.00' );
								$( '#oculus_pd_sdiv' ).show(); $( '#oculus_pd_s' ).val( '' ); $( '#oculus_pd_sinput' ).val( '0.00' );
								$( '#oculus_pd_div' ).hide(); $( '#oculus_pd' ).val( '' ); $( '#oculus_pd_input' ).val( '0.00' );
							}
						\"><div class='s'></div></div>В рецепте указано два значения</div>
					</div>
					<div class='first".( $oculus_pd_d || $oculus_pd_s ? "" : " invisible" )."' id='oculus_pd_ddiv'><input type=hidden id='oculus_pd_d' value='".( $oculus_pd_d ? $oculus_pd_d : "" )."' />
						<input type=text disabled value='".$showODpds."' id='oculus_pd_dinput' />
						<div class='icon'>
							<img src='".$mysql->settings['local_folder']."images/ardown.png' />
						</div>
						<div class='sub'>
							".$pds_uniq."
						</div>
					</div>
					<div class='second".( $oculus_pd_d || $oculus_pd_s ? "" : " invisible" )."' id='oculus_pd_sdiv'><input type=hidden id='oculus_pd_s' value='".( $oculus_pd_s ? $oculus_pd_s : "" )."' />
						<input type=text disabled value='".$showOSpds."'  id='oculus_pd_sinput'/>
						<div class='icon'>
							<img src='".$mysql->settings['local_folder']."images/ardown.png' />
						</div>
						<div class='sub'>
							".$pds_uniq."
						</div>
					</div>
				</div>
				<div class='clear'></div>
			</div></div>
			<div class='steps marginTop' id='lensetype'>
				<div>".$lang->gp( 89 )." <span>".$lang->gp( 93 )."</span></div>
			</div>
			<div class='lensesSort'><div class='inner'>
				".$lTypes."
				<div class='clear'></div>
			</div></div>
			<div class='lensesOptions'></div>
			<div class='lensesReady'><img src='/images/ar_upg.png' class='arup' />
				<div class='inner'>
					
				</div>
			</div>
			<div class='itogi'>
				
			</div>
		</div>
		<script>
			var selectedType = ".( $ltype ? $ltype : 0 ).";
			var selectedDio = ".( $dioSelected ? $dioSelected : 0 ).";
                            var dioMoreCheck = ".( $dioSelectedCheck ? $dioSelectedCheck : 0 ).";
			var selectedPType = ".( $type ? $type : 0 ).";
			var beforeObject = 0;
                        
                        

			$(window).load(function()
			{
				if( selectedType )
					LTYPEClick( 1 );
					
				$( '.lensesTypes .onetype' ).click(function(){
					selectedType = $( this ).attr( 'data-id' );
					
					$( '.lenses .onetype' ).each(function(){ $(this).removeClass( 'onetype_selected' ); });
					$( this ).addClass( 'onetype_selected' );
					$( '.lensesOptions' ).hide();
					LTYPEClick();
				});
				
				$( '.lensesSort .onetype' ).click(function(){
					if( $( this ).css( 'opacity' ) != 1 || $( this ).css( 'opacity' ) != '1' )
						return;
					$( '.lensesSort .onetype' ).each(function(){ $(this).removeClass( 'onetype_selected' ); });
					$( this ).addClass( 'onetype_selected' );
					selectedPType = $( this ).attr( 'data-id' );
					beforeObject = $( this );
					processSimpleAsyncReqForModule( '".$this->dbinfo['local']."', '3', '&t=' + selectedType + '&p=' + selectedPType + '&d=' + selectedDio + '&ochkiid=".$ochki."', 'afterTypesLensesIn( data );' );
				});
                                
                                $( '.lensesSort .onetype' ).hover(function(){
                                    $( '.lensesSort .onetype' ).each(function(){
                                        $( this ).css( 'z-index', 1000 );
                                    });
                                    $( this ).css( 'z-index', 3000 );
                                    var otop = $( this ).find( '.toper .q' ).position().top;
                                    $( this ).find( '.hover .q' ).css( 'left', $( this ).find( '.toper .q' ).position().left + 12 );
                                    $( this ).find( '.hover .q' ).css( 'top', otop > 0 ? otop + 10 : otop + 18 );
                                });
                                
                                $( '.receipeList .q' ).hover(function(){
					var temp = $( this ).attr( 'data-q' );
					if( temp ) {
						$( this ).find( '.hover' ).fadeIn( 200 );
					}
				}, function(){
					var temp = $( this ).attr( 'data-q' );
					if( temp ) {
						$( this ).find( '.hover' ).fadeOut( 200 );
					}
				});
                                
                                $( '.lensesSort .q' ).hover(function(){
                                        if( $( this ).parent().parent().parent().css( 'opacity' ) != 1 )
                                            return;
					var temp = $( this ).attr( 'data-q' );
					if( temp ) {
						$( this ).find( '.hover' ).fadeIn( 200 );
					}
				}, function(){
                                        if( $( this ).parent().parent().parent().css( 'opacity' ) != 1 )
                                            return;
					var temp = $( this ).attr( 'data-q' );
					if( temp ) {
						$( this ).find( '.hover' ).fadeOut( 200 );
					}
				});
				
				$( '.receipeList .icon' ).click(function(){
					if( $( this ).parent().find( '.sub' ).is( ':visible' ) ) {
						$( this ).parent().find( '.sub' ).slideUp( 100 );
					} else {
						$( '.sub' ).hide();
						$( this ).parent().find( '.sub' ).show();
						var cur_value = parseInt( $( this ).parent().find( 'input[type=hidden]' ).val() );
						$( this ).parent().find( '.sub a' ).each(function(){
							if( parseInt( $( this ).attr( 'data-id' ) ) == cur_value ) {
								$( this ).addClass( 'selected' ).focus();
							} else 
								$( this ).removeClass( 'selected' );
						});
					}
				});
			});
                        
                        var addBlockJS = '".$addsJS."';
                        var addBlockOfficesJS = '".$addsOfficesJS."';
			
			function LTYPEClick( first )
			{
				if( selectedType == 81 ) {
					$( '.receipeList .sub a' ).each(function(){
						if( $( this ).attr( 'data-add' ) == '1' )
							return;
						$( this ).hide();
					});
					selectedDio = 164;
				} else {
					selectedDio = selectedDio != 164 && selectedDio ? selectedDio : 0;
					$( '.receipeList .sub a' ).each(function(){
						$( this ).show();
					});
				}
				if( selectedType == 78 || selectedType == 79 ) {
                                        if( selectedType == 78 ) {
                                            $( '#add_block .sub' ).each(function(){
                                                $( this ).html( addBlockOfficesJS );
                                                $( this ).parent().find( 'input[type=text]' ).val( '0.00' );
                                                $( this ).parent().find( 'input[type=hidden]' ).val( 0 );
                                            });
                                        } else {
                                            $( '#add_block .sub' ).each(function(){
                                                $( this ).html( addBlockJS );
                                                $( this ).parent().find( 'input[type=text]' ).val( '0.00' );
                                                $( this ).parent().find( 'input[type=hidden]' ).val( 0 );
                                            });
                                        }
					$( '#add_block' ).show();
				} else {
					$( '#add_block' ).hide();
				}
					
				if( ( !selectedDio || ( first != undefined && first ) ) && selectedType != 81 ) {
					$( '#receipe' ).addClass( 'active' );
					$( '.receipeList' ).slideDown( 100 );
					if( first != undefined && first && selectedDio )
						showLensesTypes( selectedDio );
				} else if( selectedType == 81 ) {
					$( '#receipe' ).removeClass( 'active' );
					$( '.receipeList' ).slideUp( 100 );
					showLensesTypes( selectedDio );
				} else {
					showLensesTypes( selectedDio );
				}
			}
			
			function showLensesTypes( selDio )
			{
				$( '#lensetype' ).addClass( 'active' );
				$( '.lensesSort' ).show();
				processSimpleAsyncReqForModule( '".$this->dbinfo['local']."', '2', '&t=' + selectedType + '&d=' + selectedDio, 'afterTypesIn( data );' );
			}
			
			function afterTypesIn( data )
			{
				var ar = data.toString().split( '~' );
				
				$( '.lensesSort .onetype' ).each(function(){
					var show = false;
					for( var a in ar ) {
						if( ar[a] == $( this ).attr( 'data-id' ) )
							show = true;
					}
					if( show ) {
						$( this ).show();
						var h3 = $( this ).find( 'h3' );
						h3.css( 'top', ( h3.parent().height() - h3.height() ) / 2 );
						$( this ).animate( { opacity: 1 }, 200 );
						if( $( this ).hasClass( 'onetype_selected' ) ) {
							beforeObject = $( this );
							processSimpleAsyncReqForModule( '".$this->dbinfo['local']."', '3', '&t=' + selectedType + '&p=' + selectedPType + '&d=' + selectedDio + '&ochkiid=".$ochki."&lense=".$lense.( $add ? "&add=".$add : "" ).( $add_color ? "&add_color=".$add_color : "" ).( $add_move ? "&add_move=".$add_move : "" ).( $add_shadow ? "&add_shadow=".$add_shadow : "" )."', 'afterTypesLensesIn( data );' );
						}
					} else {
						$( this ).show();
						var h3 = $( this ).find( 'h3' );
						h3.css( 'top', ( h3.parent().height() - h3.height() ) / 2 );
						$( this ).css( 'opacity', 0.25 );
					}
				});
				
				$( '.lensesReady' ).hide();
				$( '.lenses .itogi' ).hide();
			}
			
			function afterTypesLensesIn( data )
			{
				var ar = data.toString().split( '~' );
				
				if( ar.length > 1 && ar[1] == '1' ) {
					$( '.lensesOptions' ).html( ar[0] );
					$( '.lensesOptions .arup' ).css( 'left', ( beforeObject.position().left + ( beforeObject.width() / 2 ) ) - 16 );
					$( '.lensesReady .arup' ).css( 'left', '50%' );
					$( '.lensesOptions' ).slideDown( 100 );
					if( ar.length > 2 ) {
						$( '.lensesReady .inner' ).html( ar[2] );
						$( '.lensesReady' ).slideDown( 100 );
						if( ar.length > 3 ) {
							$( '.lenses .itogi' ).html( ar[3] );
						}
						$( '.lenses .itogi' ).slideDown( 100 );
					}
				} else if( ar.length > 1 && ar[0] == '1' ) {
					$( '.lensesReady .inner' ).html( ar[1] );
					$( '.lensesReady .arup' ).css( 'left', ( beforeObject.position().left + ( beforeObject.width() / 2 ) ) + 18 );
					$( '.lensesReady' ).slideDown( 100 );
					if( ar.length > 2 ) {
						$( '.lenses .itogi' ).html( ar[2] );
					}
					$( '.lensesOptions' ).hide();
					$( '.lenses .itogi' ).slideDown( 100 );
				}
			}
			
			function afterRecounting( data )
			{
				$( '.lenses .itogi' ).html( data );
			}
		</script>
		";
		
		/*
		
		
		*/
		
		return $t;
	}
	
	
	function getCalculateSizeFloatBlock() 
	{
		global $mysql, $query, $lang, $main, $utils;
		
		$t = "
		<div class='fixed_calculate'>
			<div class='window'>
				<div class='closer' onclick=\"hideCalculate( function() { if( old_fixed_calculatedata != '' ) $( '.fixed_calculate' ).find( '.inner' ).html( old_fixed_calculatedata ); } );\"><img src='/images/smx.png' alt='closer' /></div>
				<div class='inner'>
					<h3>".$lang->gp( 94 )."</h3>
					<p>".$lang->gp( 95 )."</p>
					<div class='center'>
						<img src='/images/scheme.png' />
					</div>
					<div class='input_div align_left'>
						<div class='comment center'><div class='in' style='top: 7px;'>Ширина линзы</div></div>
						<div class='input align_left'>
							<input type=text id='lense_width' value='' />
						</div>
					</div>
					<div class='input_div center'>
						<div class='comment center'><div class='in'>Ширина мостика<br/>на переносице</div></div>
						<div class='input center'>
							<input type=text id='lense_widthm' value='' />
						</div>
					</div>
					<div class='input_div align_right'>
						<div class='comment center'><div class='in' style='top: 7px;'>Длина заушника</div></div>
						<div class='input align_right'>
							<input type=text id='lense_height' value='' />
						</div>
					</div>
					<div class='clear'></div>					
					<div class='button' onclick=\"
						if( $( '#lense_width' ).val() == '' || $( '#lense_width' ).val() == '0' ) {
							alert( 'Укажите ширину линзы' );
							return;
						}
						if( $( '#lense_widthm' ).val() == '' || $( '#lense_widthm' ).val() == '0' ) {
							alert( 'Укажите мостика на переносице' );
							return;
						}
						if( $( '#lense_height' ).val() == '' || $( '#lense_height' ).val() == '0' ) {
							alert( 'Укажите длину заушника' );
							return;
						}
						processSimpleAsyncReqForModule( '".$this->dbinfo['local']."', '1', '&width=' + $( '#lense_width' ).val() + '&widthm=' + $( '#lense_widthm' ).val() + '&height=' + $( '#lense_height' ).val(), 'afterCllabaclassd( data );' );
					\">".$lang->gp( 96 )."</div>
				</div>
			</div>
		</div>
		
		<script>
			var old_fixed_calculatedata = '';
			function afterCllabaclassd( data )
			{
				old_fixed_calculatedata = $( '.fixed_calculate' ).find( '.inner' ).html();
				var he = $( '.fixed_calculate' ).find( '.inner' ).height();
				$( '.fixed_calculate' ).find( '.inner' ).html( data ).height( he );
				
				var he = $( '.fixed_calculate' ).find( '.window' ).height();
				$( '.fixed_calculate' ).find( '.window' ).css( 'margin-top', he / 2 * -1 );
			}
			
			$(window).resize(function()
			{
				var he = $( '.fixed_calculate' ).find( '.window' ).height();
				if( he > 0 )
					$( '.fixed_calculate' ).find( '.window' ).css( 'margin-top', he / 2 * -1 );
			});
			
			var CalculateappierSpeed = 0;
			
			function showCalculate( speed )
			{
				CalculateappierSpeed = speed;
				$( '.fixed_calculate' ).css( 'opacity', 0 ).show();
				$( '.fixed_calculate input' ).each(function(){ $(this).val(''); });
				var he = $( '.fixed_calculate' ).find( '.window' ).height();
				$( '.fixed_calculate' ).find( '.window' ).css( 'margin-top', he / 2 * -1 );
				$( '.fixed_calculate' ).animate( { opacity: 1 }, speed );
			}
			
			function hideCalculate( after )
			{
				$( '.fixed_calculate' ).animate( { opacity: 0 }, CalculateappierSpeed, function() { $( '.fixed_calculate' ).hide(); if( after != undefined ) after(); } );
			}
		</script>
		";
		
		return $t;
	}
	
	function parseExternalRequest()
	{
		global $query, $main, $utils, $lang, $mysql;
		
		$type = $query->gp( "localtype" );

		switch( $type ) {
			case 1:
				
				$width = str_replace( ",", ".", $query->gp( "width" ) );
				$widthm = $query->gp( "widthm" );
				$height = $query->gp( "height" );
				
				$found = '';
				$foundText = 0;
				$foundID = 0;
				$min = 0;
				$max = 0;
				$ll = $main->listings->getListingElementsArray( 27, 0, false, '',  true );
				foreach( $ll as $k => $v ) {
					$tttt = explode( "~", $v['additional_info'] );
					$xxx = explode( "-", $tttt[0] );
					if( intval( $width ) >= $xxx[0] && $width <= $xxx[1] ) {
						$found = $lang->gp( $v['value'], true );
						$foundID = $k;
						if( $k == 639 ) {
							$foundText = 98;
						} else if( $k == 638 ) {
							$foundText = 143;
						} else if( $k == 640 ) {
							$foundText = 144;
						}
						break;
					}
					if( $min > $xxx[0] || !$min )
						$min = $xxx[0];
					if( $max < $xxx[1] || !$max )
						$max = $xxx[1];
				}
				if( !$found ) {
					if( intval( $width ) < $min ) {
						$found = $lang->gp( $ll[638]['value'], true );
						$foundText = 143;
						$foundID = 638;
					} else if( intval( $width ) > $max ) {
						$found = $lang->gp( $ll[640]['value'], true );
						$foundText = 144;
						$foundID = 640;
					}
				}
				
				return "<h3>".$lang->gp( 97 )."</h3>
				<div class='size'>".$found."</div>
				<p>".$lang->gp( $foundText )."</p>
				<div class='button' onclick=\"urlmove('/catalog/?size=".$foundID."');\">".$lang->gp( 100 )."</div>
				<p><a href='#' onclick=\"$( '.fixed_calculate' ).find( '.inner' ).html( old_fixed_calculatedata ); return false;\">".$lang->gp( 101 )."</a></p>
				";
				
			case 2:
				
				$type = $query->gp( "t" );
				$dio = $query->gp( "d" );
				$dioInfo = $main->listings->getListingElementById( 14, $dio, true );
				
				switch( $type ) {
					case 76: // Для близи
						return "82~83";
					case 77: // Для дали
						if( intval( $dioInfo['additional_info'] ) >= 2 )
							return "82~84~85~101";
						else 
							return "82~84~85~101~102";
					case 80: // EYEZEN [sup]TM[/sup]
						return "82~83~85";
					case 78: // Офисные
						return "82~83";
					case 79: // Прогрессивные
						if( intval( $dioInfo['additional_info'] ) >= 2 )
							return "82~83~85";
						else 
							return "82~83~85~101~102";
					case 81: // Без рецепта
						if( intval( $dioInfo['additional_info'] ) >= 2 )
							return "82~83~84~85~101~102";
						else 
							return "82~83~84~85~101~102";
				}
			case 3:
				
				$ltype = $query->gp( "t" );
				$type = $query->gp( "p" );
				$dio = $query->gp( "d" );
				$ochkiId = $query->gp( "ochkiid" );
                                $lense = $query->gp( "lense" );
				
				$add = $query->gp( "add" );
				$add_move = $query->gp( "add_move" );
				$add_shadow = $query->gp( "add_shadow" );
				$add_color = $query->gp( "add_color" );
				
				if( !$add ) {
                                    switch( $type ) {
                                        case 84:
                                            $add = 131;
                                            break;
                                        case 85:
                                            $add = 134;
                                            break;
                                        case 101:
                                            $add = 677;
                                            break;
                                        case 102:
                                        default:
                                            $add = 678;
                                            break;
                                    }
                                }
					
				if( !$add_color ) {
                                    switch( $type ) {
                                        case 84:
                                            $add_color = 106;
                                            break;
                                        case 85:
                                        case 101:
                                        case 102:
                                        default:
                                            $add_color = 124;
                                            break;
                                    }
                                }
				
				switch( $ltype ) {
					case 77: // Для дали
					case 81: // Без рецепта
					case 80: // EYEZEN [sup]TM[/sup]
					case 79: // Прогрессивные					
						if( $type == 84 ) {
							
							$colors = $main->listings->getListingElementsArray( 15, 0, false, '', true );
							$colorsText = "";
							foreach( $colors as $k => $v ) {
								$colorsText .= "
								<div class='colors".( $add_color == $k ? " colors_selected" : "" )."' data-id='".$k."' onclick=\"$( '#131_color' ).val( ".$k." );\">
									<div class='color'><div class='colorname'><img src='/images/smardown.png' />".( is_numeric( $v['value'] ) ? $lang->gp( $v['value'], true ) : $v['value'] )."</div><div class='color_exact' style='background-color: #".$v['additional_info'].";'></div></div>
								</div>";
							}
							
							$colors = $main->listings->getListingElementsArray( 16, 0, false, '', true );
							$colorsTextPol = "";
							foreach( $colors as $k => $v ) {
								$colorsTextPol .= "
								<div class='colors".( $add_color == $k ? " colors_selected" : "" )."' data-id='".$k."' onclick=\"$( '#132_color' ).val( ".$k." );\">
									<div class='color'><div class='colorname'><img src='/images/smardown.png' />".( is_numeric( $v['value'] ) ? $lang->gp( $v['value'], true ) : $v['value'] )."</div><div class='color_exact' style='background-color: #".$v['additional_info'].";'></div></div>
								</div>";
							}
							
							$colors = $main->listings->getListingElementsArray( 17, 0, false, '', true );
							$colorsTextMirror = "";
							foreach( $colors as $k => $v ) {
								$colorsTextMirror .= "
								<div class='colors".( $add_color == $k ? " colors_selected" : "" )."' data-id='".$k."' onclick=\"$( '#133_color' ).val( ".$k." );\">
									<div class='color'><div class='colorname'><img src='/images/smardown.png' />".( is_numeric( $v['value'] ) ? $lang->gp( $v['value'], true ) : $v['value'] )."</div><div class='color_exact' style='background-color: #".$v['additional_info'].";'></div></div>
								</div>";
							}
							
							$percents = "";
							$selectedPercent = "0%";
							$percentsTypes = $main->listings->getListingElementsArraySpec( 19, "`order` DESC, `id` ASC", "", 0, true );
							foreach( $percentsTypes as $k => $v ) {
								$percents .= "<a href='#' onclick=\"$( this ).parent().parent().find( 'input[type=text]' ).val( $( this ).attr( 'data-val' ) ); $( this ).parent().parent().find( 'input[type=hidden]' ).val( $( this ).attr( 'data-id' ) ); $( this ).parent().slideUp(100); return false;\" data-val='".$lang->gp( $v['value'], true )."' data-id='".$k."' data-add='".$v['additional_info']."'>".$lang->gp( $v['value'], true )."</a>";
								if( $add_shadow == $k )
									$selectedPercent = $lang->gp( $v['value'], true );
							}
							
							$prices = $main->listings->getListingElementsArray( 13, 84, false, '', true );
							
							return "
								<div class='inner'>
									<img src='/images/ar_up.png' class='arup' />
									<div class='line'>
										<div class='price'>+ ".$prices[131]['additional_info']." <img src='/images/big_rubleg.png'></div>
										<div class='block block_options'>
											<div class='option".( $add == 131 ? " option_selected" : "" )."' data-id='131'><div></div></div>
											<span>".$lang->gp( $prices[131]['value'], true )."</span><div class='q' data-q=1>?<div class='hover'><div class='in'>".$main->listings->getListingElementAddById( 664, true )."</div></div></div>
											<div class='check'><div class='box".( $add_move ? " box_sel" : "" )."' onclick=\"
												if( $( this ).hasClass( 'box_sel' ) ) {
													$( this ).removeClass( 'box_sel' );
													$( '#131_move' ).val( 0 );
												} else {
													$( this ).addClass( 'box_sel' );
													$( '#131_move' ).val( 1 );
												}
											\"><div class='s'></div></div>Переход<input type=hidden id='131_move' value=".( $add_move ? 1 : 0 )." /></div>
										</div>
										<div class='block block_colors'>
											".$colorsText."<div class='clear'></div><input type=hidden id='131_color' value=".( $add == 131 && $add_color ? $add_color : 0 )." />
										</div>
										<div class='block block_add'>
											<span>Затенение</span>
											<div class='selector'><input type=hidden id='131_shadow' value=".( $add == 131 && $add_shadow ? $add_shadow : 0 )." />
												<input type=text disabled value='".$selectedPercent."' />
												<div class='icon'>
													<img src='".$mysql->settings['local_folder']."images/ardown.png' />
												</div>
												<div class='sub'>
													".$percents."
												</div>
											</div>
										</div>
									</div>
									<div class='line invisible'>
										<div class='price'>+ ".$prices[132]['additional_info']." <img src='/images/big_rubleg.png'></div>
										<div class='block block_options'>
											<div class='option".( $add == 132 ? " option_selected" : "" )."' data-id='132'><div></div></div>
											<span>".$lang->gp( $prices[132]['value'], true )."</span><div class='q' data-q=1>?<div class='hover'><div class='in'>".$main->listings->getListingElementAddById( 665, true )."</div></div></div>
										</div>
										<div class='block block_colors'>
											".$colorsTextPol."<div class='clear'></div><input type=hidden id='132_color' value=".( $add == 132 && $add_color ? $add_color : 0 )." />
										</div>
									</div>
									<div class='line line_last'>
										<div class='price'>+ ".$prices[133]['additional_info']." <img src='/images/big_rubleg.png'></div>
										<div class='block block_options'>
											<div class='option".( $add == 133 ? " option_selected" : "" )."' data-id='133'><div></div></div>
											<span>".$lang->gp( $prices[133]['value'], true )."</span><div class='q' data-q=1>?<div class='hover'><div class='in'>".$main->listings->getListingElementAddById( 666, true )."</div></div></div>
										</div>
										<div class='block block_colors'>
											".$colorsTextMirror."<div class='clear'></div><input type=hidden id='133_color' value=".( $add == 133 && $add_color ? $add_color : 0 )." />
										</div>
									</div>
								</div>
								
								<script>
									setTimeout( function(){
										$( '.block_colors .colors .colorname' ).each(function(){
											$( this ).css( 'margin-left', ( ( $( this ).width() ) / 2 * -1 ) - 3 );
										});
										
										$( '.block_colors .colors' ).click(function(){
											$( this ).parent().find( '.colors' ).removeClass( 'colors_selected' );
											if( $( this ).hasClass( 'colors_selected' ) ) {
												$( this ).removeClass( 'colors_selected' );
											} else {
												$( this ).addClass( 'colors_selected' );
											}
										});
										
										$( '.block_options .option' ).click(function(){
											var no = false;
											if( $( this ).hasClass( 'option_selected' ) ) {
												$( this ).removeClass( 'option_selected' );
												no = true;
											} else {
												$( '.block_options .option' ).removeClass( 'option_selected' );
												$( this ).addClass( 'option_selected' );
											}
											processSimpleAsyncReqForModule( '".$this->dbinfo['local']."', '4', '&t=".$ltype."&p=".$type."&d=".$dio."&ochkiid=".$ochkiId."&lense=".$lense."&add=' + ( no ? '' : $( this ).attr( 'data-id' ) ), 'afterRecounting( data );' );
										});
										
										$( '.block_add .icon' ).click(function(){
											if( $( this ).parent().find( '.sub' ).is( ':visible' ) ) {
												$( this ).parent().find( '.sub' ).slideUp( 100 );
											} else {
												$( '.sub' ).hide();
												$( this ).parent().find( '.sub' ).slideDown( 100 );
											}
										});
                                                                                
										$( '.lensesOptions .q' ).hover(function(){
                                                                                    var temp = $( this ).attr( 'data-q' );
					                                            if( temp ) {
						                                        $( this ).find( '.hover' ).fadeIn( 200 );
					                                            }
				                                                }, function(){
					                                            var temp = $( this ).attr( 'data-q' );
					                                            if( temp ) {
						                                        $( this ).find( '.hover' ).fadeOut( 200 );
					                                            }
				                                                });
									}, 100 );
								</script>
								
								~1~".$this->getItogBlock( $dio, $type, $ltype, $ochkiId, $add, $lense );
                                                        
                                                } else if( $type == 1101 ) {
							
							$colors = $main->listings->getListingElementsArray( 18, 0, false, '', true );
							$colorsText = "";
							foreach( $colors as $k => $v ) {
								$colorsText .= "
								<div class='colors".( $add_color == $k ? " colors_selected" : "" )."' data-id='".$k."' onclick=\"$( '#677_color' ).val( ".$k." );\">
									<div class='color'><div class='colorname'><img src='/images/smardown.png' />".( is_numeric( $v['value'] ) ? $lang->gp( $v['value'], true ) : $v['value'] )."</div><div class='color_exact' style='background-color: #".$v['additional_info'].";'></div></div>
								</div>";
							}
							
							$colorsTextMirror = "";
							foreach( $colors as $k => $v ) {
								$colorsTextMirror .= "
								<div class='colors".( $add_color == $k ? " colors_selected" : "" )."' data-id='".$k."' onclick=\"$( '#679_color' ).val( ".$k." );\">
									<div class='color'><div class='colorname'><img src='/images/smardown.png' />".( is_numeric( $v['value'] ) ? $lang->gp( $v['value'], true ) : $v['value'] )."</div><div class='color_exact' style='background-color: #".$v['additional_info'].";'></div></div>
								</div>";
							}
							
							$prices = $main->listings->getListingElementsArray( 13, 101, false, '', true );
							
							return "
								<div class='inner'>
									<img src='/images/ar_up.png' class='arup' />
									<div class='line'>
										<div class='price'>+ ".$prices[677]['additional_info']." <img src='/images/big_rubleg.png'></div>
										<div class='block block_options'>
											<div class='option".( $add == 677 ? " option_selected" : "" )."' data-id='677'><div></div></div>
											<span>".$lang->gp( $prices[677]['value'], true )."</span><div class='q' data-q=1>?<div class='hover'><div class='in'>".$main->listings->getListingElementAddById( 681, true )."</div></div></div>
										</div>
										<div class='block block_colors'>
											".$colorsText."<div class='clear'></div><input type=hidden id='677_color' value=".( $add == 677 && $add_color ? $add_color : 0 )." />
										</div>
									</div>
									<div class='line line_last'>
										<div class='price'>+ ".$prices[679]['additional_info']." <img src='/images/big_rubleg.png'></div>
										<div class='block block_options'>
											<div class='option".( $add == 679 ? " option_selected" : "" )."' data-id='679'><div></div></div>
											<span>".$lang->gp( $prices[679]['value'], true )."</span><div class='q' data-q=1>?<div class='hover'><div class='in'>".$main->listings->getListingElementAddById( 682, true )."</div></div></div>
										</div>
										<div class='block block_colors'>
											".$colorsTextMirror."<div class='clear'></div><input type=hidden id='679_color' value=".( $add == 679 && $add_color ? $add_color : 0 )." />
										</div>
									</div>
								</div>
								
								<script>
									setTimeout( function(){
										$( '.block_colors .colors .colorname' ).each(function(){
											$( this ).css( 'margin-left', ( ( $( this ).width() ) / 2 * -1 ) - 3 );
										});
										
										$( '.block_colors .colors' ).click(function(){
											$( this ).parent().find( '.colors' ).removeClass( 'colors_selected' );
											if( $( this ).hasClass( 'colors_selected' ) ) {
												$( this ).removeClass( 'colors_selected' );
											} else {
												$( this ).addClass( 'colors_selected' );
											}
										});
										
										$( '.block_options .option' ).click(function(){
											var no = false;
											if( $( this ).hasClass( 'option_selected' ) ) {
												$( this ).removeClass( 'option_selected' );
												no = true;
											} else {
												$( '.block_options .option' ).removeClass( 'option_selected' );
												$( this ).addClass( 'option_selected' );
											}
											processSimpleAsyncReqForModule( '".$this->dbinfo['local']."', '4', '&t=".$ltype."&p=".$type."&d=".$dio."&ochkiid=".$ochkiId."&lense=".$lense."&add=' + ( no ? '' : $( this ).attr( 'data-id' ) ), 'afterRecounting( data );' );
										});
										
										$( '.block_add .icon' ).click(function(){
											if( $( this ).parent().find( '.sub' ).is( ':visible' ) ) {
												$( this ).parent().find( '.sub' ).slideUp( 100 );
											} else {
												$( '.sub' ).hide();
												$( this ).parent().find( '.sub' ).slideDown( 100 );
											}
										});
                                                                                
										$( '.lensesOptions .q' ).hover(function(){
                                                                                    var temp = $( this ).attr( 'data-q' );
					                                            if( temp ) {
						                                        $( this ).find( '.hover' ).fadeIn( 200 );
					                                            }
				                                                }, function(){
					                                            var temp = $( this ).attr( 'data-q' );
					                                            if( temp ) {
						                                        $( this ).find( '.hover' ).fadeOut( 200 );
					                                            }
				                                                });
									}, 100 );
								</script>
								
								~1~".$this->getItogBlock( $dio, $type, $ltype, $ochkiId, $add, $lense );
                                                        
                                                } else if( $type == 1102 ) {
							
							$colors = $main->listings->getListingElementsArray( 18, 0, false, '', true );
							$colorsText = "";
							foreach( $colors as $k => $v ) {
								$colorsText .= "
								<div class='colors".( $add_color == $k ? " colors_selected" : "" )."' data-id='".$k."' onclick=\"$( '#678_color' ).val( ".$k." );\">
									<div class='color'><div class='colorname'><img src='/images/smardown.png' />".( is_numeric( $v['value'] ) ? $lang->gp( $v['value'], true ) : $v['value'] )."</div><div class='color_exact' style='background-color: #".$v['additional_info'].";'></div></div>
								</div>";
							}
							
							$colorsTextMirror = "";
							foreach( $colors as $k => $v ) {
								$colorsTextMirror .= "
								<div class='colors".( $add_color == $k ? " colors_selected" : "" )."' data-id='".$k."' onclick=\"$( '#680_color' ).val( ".$k." );\">
									<div class='color'><div class='colorname'><img src='/images/smardown.png' />".( is_numeric( $v['value'] ) ? $lang->gp( $v['value'], true ) : $v['value'] )."</div><div class='color_exact' style='background-color: #".$v['additional_info'].";'></div></div>
								</div>";
							}
							
							$prices = $main->listings->getListingElementsArray( 13, 102, false, '', true );
							
							return "
								<div class='inner'>
									<img src='/images/ar_up.png' class='arup' />
									<div class='line'>
										<div class='price'>+ ".$prices[678]['additional_info']." <img src='/images/big_rubleg.png'></div>
										<div class='block block_options'>
											<div class='option".( $add == 678 ? " option_selected" : "" )."' data-id='678'><div></div></div>
											<span>".$lang->gp( $prices[678]['value'], true )."</span><div class='q' data-q=1>?<div class='hover'><div class='in'>".$main->listings->getListingElementAddById( 683, true )."</div></div></div>
										</div>
										<div class='block block_colors'>
											".$colorsText."<div class='clear'></div><input type=hidden id='678_color' value=".( $add == 678 && $add_color ? $add_color : 0 )." />
										</div>
									</div>
									<div class='line line_last'>
										<div class='price'>+ ".$prices[680]['additional_info']." <img src='/images/big_rubleg.png'></div>
										<div class='block block_options'>
											<div class='option".( $add == 680 ? " option_selected" : "" )."' data-id='680'><div></div></div>
											<span>".$lang->gp( $prices[680]['value'], true )."</span><div class='q' data-q=1>?<div class='hover'><div class='in'>".$main->listings->getListingElementAddById( 684, true )."</div></div></div>
										</div>
										<div class='block block_colors'>
											".$colorsTextMirror."<div class='clear'></div><input type=hidden id='680_color' value=".( $add == 680 && $add_color ? $add_color : 0 )." />
										</div>
									</div>
								</div>
								
								<script>
									setTimeout( function(){
										$( '.block_colors .colors .colorname' ).each(function(){
											$( this ).css( 'margin-left', ( ( $( this ).width() ) / 2 * -1 ) - 3 );
										});
										
										$( '.block_colors .colors' ).click(function(){
											$( this ).parent().find( '.colors' ).removeClass( 'colors_selected' );
											if( $( this ).hasClass( 'colors_selected' ) ) {
												$( this ).removeClass( 'colors_selected' );
											} else {
												$( this ).addClass( 'colors_selected' );
											}
										});
										
										$( '.block_options .option' ).click(function(){
											var no = false;
											if( $( this ).hasClass( 'option_selected' ) ) {
												$( this ).removeClass( 'option_selected' );
												no = true;
											} else {
												$( '.block_options .option' ).removeClass( 'option_selected' );
												$( this ).addClass( 'option_selected' );
											}
											processSimpleAsyncReqForModule( '".$this->dbinfo['local']."', '4', '&t=".$ltype."&p=".$type."&d=".$dio."&ochkiid=".$ochkiId."&lense=".$lense."&add=' + ( no ? '' : $( this ).attr( 'data-id' ) ), 'afterRecounting( data );' );
										});
										
										$( '.block_add .icon' ).click(function(){
											if( $( this ).parent().find( '.sub' ).is( ':visible' ) ) {
												$( this ).parent().find( '.sub' ).slideUp( 100 );
											} else {
												$( '.sub' ).hide();
												$( this ).parent().find( '.sub' ).slideDown( 100 );
											}
										});
                                                                                
										$( '.lensesOptions .q' ).hover(function(){
                                                                                    var temp = $( this ).attr( 'data-q' );
					                                            if( temp ) {
						                                        $( this ).find( '.hover' ).fadeIn( 200 );
					                                            }
				                                                }, function(){
					                                            var temp = $( this ).attr( 'data-q' );
					                                            if( temp ) {
						                                        $( this ).find( '.hover' ).fadeOut( 200 );
					                                            }
				                                                });
									}, 100 );
								</script>
								
								~1~".$this->getItogBlock( $dio, $type, $ltype, $ochkiId, $add, $lense );
							
						} else if( $type == 85 || $type == 101 || $type == 102 ) {
							
							$colors = $main->listings->getListingElementsArray( 18, 0, false, '', true );
							$colorsText = "";
							foreach( $colors as $k => $v ) {
								$colorsText .= "
								<div class='colors".( $add_color == $k ? " colors_selected" : "" )."' data-id='".$k."' onclick=\"$( '#134_color' ).val( ".$k." );\">
									<div class='color'><div class='colorname'><img src='/images/smardown.png' />".( is_numeric( $v['value'] ) ? $lang->gp( $v['value'], true ) : $v['value'] )."</div><div class='color_exact' style='background-color: #".$v['additional_info'].";'></div></div>
								</div>";
							}
							
							$prices = $main->listings->getListingElementsArray( 13, $type, false, '', true );
                                                        
                                                        $code = 0;
                                                        $qcode = 0;
                                                        switch( $type ) {
                                                            case 85:
                                                                $code = 134;
                                                                $qcode = 667;
                                                                break;
                                                            case 101:
                                                                $code = 677;
                                                                $qcode = 681;
                                                                break;
                                                            case 102:
                                                                $code = 678;
                                                                $qcode = 683;
                                                                break;
                                                        }
							
							return "
								<div class='inner'>
									<img src='/images/ar_up.png' class='arup' />
									<div class='line line_last'>
										<div class='price'>+ ".$prices[$code]['additional_info']." <img src='/images/big_rubleg.png'></div>
										<div class='block block_options'>
											<div class='option".( $add == $code ? " option_selected" : "" )."' data-id='134'><div></div></div>
											<span>".$lang->gp( $prices[$code]['value'], true )."</span><div class='q' data-q=1>?<div class='hover'><div class='in'>".$main->listings->getListingElementAddById( $qcode, true )."</div></div></div>
										</div>
										<div class='block block_colors'>
											".$colorsText."<div class='clear'></div><input type=hidden id='".$code."_color' value=".( $add == $code && $add_color ? $add_color : 0 )." />
										</div>
									</div>
								</div>
								
								<script>
									setTimeout( function(){
										$( '.block_colors .colors .colorname' ).each(function(){
											$( this ).css( 'margin-left', ( ( $( this ).width() ) / 2 * -1 ) - 3 );
										});
										
										$( '.block_colors .colors' ).click(function(){
											$( this ).parent().find( '.colors' ).removeClass( 'colors_selected' );
											if( $( this ).hasClass( 'colors_selected' ) ) {
												$( this ).removeClass( 'colors_selected' );
											} else {
												$( this ).addClass( 'colors_selected' );
											}
										});
										
										$( '.block_options .option' ).click(function(){
											var no = false;
											if( $( this ).hasClass( 'option_selected' ) ) {
												$( this ).removeClass( 'option_selected' );
												no = true;
											} else {
												$( '.block_options .option' ).removeClass( 'option_selected' );
												$( this ).addClass( 'option_selected' );
											}
											processSimpleAsyncReqForModule( '".$this->dbinfo['local']."', '4', '&t=".$ltype."&p=".$type."&d=".$dio."&ochkiid=".$ochkiId."&lense=".$lense."&add=' + ( no ? '' : $( this ).attr( 'data-id' ) ), 'afterRecounting( data );' );
										});
										
										$( '.block_add .icon' ).click(function(){
											if( $( this ).parent().find( '.sub' ).is( ':visible' ) ) {
												$( this ).parent().find( '.sub' ).slideUp( 100 );
											} else {
												$( '.sub' ).hide();
												$( this ).parent().find( '.sub' ).slideDown( 100 );
											}
										});
									}, 100 );
								</script>
								
								~1~".$this->getItogBlock( $dio, $type, $ltype, $ochkiId, $add, $lense );
						}
					case 76: // Для близи
					case 78: // Офисные
						return "1~".$this->getItogBlock( $dio, $type, $ltype, $ochkiId, 0, $lense );
				}
				
			case 4:
				
				$ltype = $query->gp( "t" );
				$type = $query->gp( "p" );
				$dio = $query->gp( "d" );
				$ochkiId = $query->gp( "ochkiid" );
                                $lense = $query->gp( "lense" );
				
				$add = $query->gp( "add" );
				
				$lenses = $this->getLenseWithTypes( $dio, $type, $ltype, $lense );
				
				if( $add ) {
					$prices = $main->listings->getListingElementsArray( 13, $type, false, '', true );
					if( isset( $prices[$add] ) ) {
                                                foreach( $lenses as $v ) {
                                                    $v['price'] += $prices[$add]['additional_info'];
                                                }
					}
				}
						
				$finalPrice = intval( $lenses[$lense]['price'] );
						
				$oprava = $main->modules->gmi( "catalog" )->getItem( $ochkiId );
						
				$finalPrice += intval( $oprava['price'] );
				
				return $this->getItogBlockAfterRecount( $oprava, $lenses, $finalPrice, $dio, $type, $ltype, $ochkiId, $add, $lense );				
		}
	}
	
	function getItogBlock( $dio, $type, $ltype, $ochkiId, $add = 0, $lense = 0 )
	{
		global $query, $main, $utils, $lang, $mysql;
		
		$lenses = $this->getLenseWithTypes( $dio, $type, $ltype, $lense );
		
		if( $add ) {
			$prices = $main->listings->getListingElementsArray( 13, $type, false, '', true );
			if( isset( $prices[$add] ) ) {
                                foreach( $lenses as $v ) {
                                                    $v['price'] += $prices[$add]['additional_info'];
                                                }
			}
		}
						
		$finalPrice = intval( $lenses[$lense]['price'] );
						
		$oprava = $main->modules->gmi( "catalog" )->getItem( $ochkiId );
						
		$finalPrice += intval( $oprava['price'] );
                
                $lensesText = "";
                if( count( $lenses ) == 1 ) {
                    $lensesText = "<div class='inner_white inner_white_few' style='width: 100%;'><div class='in'>
							<div class='block block_name'><div class='innerL'>
								Линзы
								<h3>".$lenses[$lense]['name']."</h3>
								".$lenses[$lense]['country']."
							</div></div>
							<div class='block block_photo'><div class='innerL'>
								".( $lenses[$lense]['image'] ? "<img src='/files/upload/lenses/".$lenses[$lense]['image']."' alt='lense' />" : "" )."
							</div></div>
							<div class='block block_comments'><div class='innerL'>
								".$lenses[$lense]['info']."
							</div></div>
							<div class='clear' style='height: 30px;'></div>
                                                        
                                                        <div class='block_price'>
                                                            Цена:&nbsp;&nbsp;<span>".$utils->digitsToRazryadi( $lenses[$lense]['price'] )."</span> <img src='/images/rubb.png' alt='rubb' /> <label style='color:#f00; position: relative; top: -3px; left: 5px; font-size: 15px;'>(Стоимость за две линзы)</label>
                                                        </div>
						</div></div><div class='clear'></div>";
                } else {
                    foreach( $lenses as $id => $v ) {
                        $lensesText .= "<div class='inner_white inner_white_few".( $lensesText ? " inner_white_few_r" : "" )."'>
                            <div class='in'><div class='top'><div class='ii'><div class='option".( $lense == $id ? " option_selected" : "" )."' onclick=\"
                                if( $( this ).hasClass( 'option_selected' ) )
                                    return;
                                $( '.ii .option' ).each(function(){ $( this ).removeClass( 'option_selected' ); });
                                $( this ).addClass( 'option_selected' );
                                processSimpleAsyncReqForModule( '".$this->dbinfo['local']."', '4', '&t=".$ltype."&p=".$type."&d=".$dio."&ochkiid=".$ochkiId."&add=".$add."&lense=".$id."', 'afterRecounting( data );addAfterRecounting();' );
                                wahtToShowNext = '<img src=\'/images/wa.png\' alt=\'wa\' />&nbsp;&nbsp;Вы выбрали ".$lenses[$id]['country']."';
                                \"><div></div></div>".$v['country']."</div></div>
							<div class='block block_name'><div class='innerL'>
								Линзы
								<h3>".$v['name']."</h3>
								".$v['country']."
							</div></div>
							<div class='block block_photo'><div class='innerL'>
								".( $v['image'] ? "<img src='/files/upload/lenses/".$v['image']."' alt='lense' />" : "" )."
							</div></div>
							<div class='block block_comments'><div class='innerL'>
								".$v['info']."
							</div></div>
							<div class='clear'></div>
						
                                               
                                                <div class='block_price'>
                                                      Цена:&nbsp;&nbsp;<span>".$utils->digitsToRazryadi( $v['price'] )."</span> <img src='/images/rubb.png' alt='rubb' /> <label style='color:#f00; position: relative; top: -3px; left: 5px; font-size: 15px;'>(Стоимость за две линзы)</label>
                                                 </div>
</div>
                                        </div>";
                    }
                    $lensesText .= "<div class='clear'></div><div class='finally'><img src='/images/wa.png' alt='wa' />&nbsp;&nbsp;Вы выбрали ".$lenses[$lense]['country']."</div>
                    <script>
                        var lh = 0;
                        var wahtToShowNext = '';
                        setTimeout(function(){
                        $( '.inner_white_few .in' ).each(function(){
                            if( $( this ).height() > lh )
                                lh = $( this ).height();
                        });
                        $( '.inner_white_few .in' ).each(function(){
                            $( this ).height( lh );
                        });
                        },50);
                        
                        function addAfterRecounting( data )
                        {
                            $( '.finally' ).html( wahtToShowNext );
                        }
                    </script>";
                }
						
		return "<h2>".$lang->gp( count( $lenses ) == 1 ? 130 : 161, false )."</h2>
						<h3>".$lang->gp( 131, false )."</h3>
						".$lensesText."
						~
						".$this->getItogBlockAfterRecount( $oprava, $lenses, $finalPrice, $dio, $type, $ltype, $ochkiId, $add, $lense );
	}
	
	function getItogBlockAfterRecount( $oprava, $lenses, $finalPrice, $dio, $type, $ltype, $ochkiId, $add = 0, $lense = 0 )
	{
		global $query, $main, $utils, $lang, $mysql;
                
                $dopPrice = 0;
                if( $add ) {
			$prices = $main->listings->getListingElementsArray( 13, $type, false, '', true );
			if( isset( $prices[$add] ) ) {
                            $dopPrice = $prices[$add]['additional_info'];
			}
		}
                
                $discount = $main->modules->gmi( "discount" )->getDiscountForGood( $oprava );
                $oprava['discount_price'] = $discount ? $oprava['price'] - ( $oprava['price'] / 100 * $discount['percent'] ) : 0;
                $oprava['discount_asis'] = $oprava['price'] - ceil( $oprava['discount_price'] );
                
                if( $oprava['discount_price'] )
                    $finalPrice = $finalPrice - $oprava['price'] + ceil( $oprava['discount_price'] );
		
		return "
						<div class='final_price' onclick=\"
							var url = '".$mysql->settings['local_folder']."basket?ochkiid=".$oprava['id']."&t=".$ltype."&p=".$type."&d=".$dio."&lense=".$lense.( $add ? "&add=".$add : "" )."';
							url += '&od_sph=' + $( '#od_sph' ).val();
							url += '&os_sph=' + $( '#os_sph' ).val();
							url += '&od_cyl=' + $( '#od_cyl' ).val();
							url += '&os_cyl=' + $( '#os_cyl' ).val();
							url += '&od_axis=' + $( '#od_axis' ).val();
							url += '&os_axis=' + $( '#os_axis' ).val();
							url += '&od_add=' + $( '#od_add' ).val();
							url += '&os_add=' + $( '#os_add' ).val();
							url += '&oculus_pd=' + $( '#oculus_pd' ).val();
							url += '&oculus_pd_d=' + $( '#oculus_pd_d' ).val();
							url += '&oculus_pd_s=' + $( '#oculus_pd_s' ).val();
							".( $add == 131 ? "
							url += '&add_move=' + $( '#131_move' ).val();
							url += '&add_shadow=' + $( '#131_shadow' ).val();
							url += '&add_color=' + $( '#131_color' ).val();
							" : "" )."
							".( $add == 132 ? "
							url += '&add_color=' + $( '#132_color' ).val();
							" : "" )."
							".( $add == 133 ? "
							url += '&add_color=' + $( '#133_color' ).val();
							" : "" )."
							".( $add == 134 ? "
							url += '&add_color=' + $( '#134_color' ).val();
							" : "" )."
                                                            ".( $add == 677 ? "
							url += '&add_color=' + $( '#677_color' ).val();
							" : "" )."
                                                            ".( $add == 678 ? "
							url += '&add_color=' + $( '#678_color' ).val();
							" : "" )."
                                                            ".( $add == 679 ? "
							url += '&add_color=' + $( '#679_color' ).val();
							" : "" )."
                                                            ".( $add == 680 ? "
							url += '&add_color=' + $( '#680_color' ).val();
							" : "" )."
							urlmove( url );
						\">
							<img src='/images/corzinag.png'>&nbsp;&nbsp;<span class='summa'>".$utils->digitsToRazryadi( $finalPrice + $dopPrice )."</span> <img src='/images/big_rublegt.png'>
							<div>оформить заказ</div>
						</div>
						<div class='block block_title'>
							<h3>Общая стоимость очков</h3>
						</div>
						<div class='block' style='float: right !important;'>
							<table cellspacing=0 cellpadding=0 border=0>	
								<tr>
									<td class='title' nowrap width=20%>Оправа</td>
									<td width=80%>".$oprava['name']."</td>
									<td class='align_right' nowrap>".intval( ( $oprava['discount_price'] ? ceil( $oprava['discount_price'] ) : $oprava['price'] ) )." <img src='/images/rubs.png' /></td>
								</tr>
								<tr>
									<td class='title' nowrap width=20%>Линзы</td>
									<td width=80%>".$lenses[$lense]['name']."</td>
									<td class='align_right' nowrap>".intval( $lenses[$lense]['price'] )." <img src='/images/rubs.png' /></td>
								</tr>
                                                                <tr>
									<td class='title' nowrap width=20%>Допы</td>
									<td width=80%>&nbsp;</td>
									<td class='align_right' nowrap>".$dopPrice." <img src='/images/rubs.png' /></td>
								</tr>
                                                                <tr".( $oprava['discount_price'] ? "" : " style='display: none;'" ).">
									<td class='title' nowrap width=20%>Скидка</td>
									<td width=80%></td>
									<td class='align_right' nowrap>".( $oprava['discount_price'] ? "<span style='color: #ff0000; font-size: 12px; font-weight: 700;'>-".$discount['percent']."%</span> ".$oprava['discount_asis'] : "0" )." <img src='/images/rubs.png' /></td>
								</tr>
							</table>
						</div>
						<div class='clear'></div>
		";
	}
	
	function getLenseWithTypes( $dio, $type, $ltype, &$lense ) 
	{
		global $query, $main, $utils, $lang, $mysql;
		
		if( !$dio && !$type && !$ltype )
			return null;
		
		$a = $mysql->mqm( "SELECT * FROM ".( $this->global ? $this->gl_dbase_string : "" )."`".$mysql->t_prefix."lenses` WHERE `view`=1 AND (`d` LIKE '%".$dio."%' OR `d` LIKE '%-".$dio."-%') AND (`t` LIKE '%".$type."%' OR `t` LIKE '%-".$type."-%') AND (`ot` LIKE '%".$ltype."%' OR `ot` LIKE '%-".$ltype."-%') LIMIT 2" );
                
                $lenses = array();
                while( $r = @mysql_fetch_assoc( $a ) ) {
                    $lenses[$r['id']] = $r;
                }
                
                if( !count( $lenses ) ) {
                    $r = $mysql->mq( "SELECT * FROM ".( $this->global ? $this->gl_dbase_string : "" )."`".$mysql->t_prefix."lenses` WHERE `view`=1 AND `id`=1" );
                     $lenses[$r['id']] = $r;
                }
                
                if( !$lense ) {
                    foreach( $lenses as $k => $v ) {
                        $lense = $k;
                        break;
                    }
                }
		
		return $lenses;
	}
        
        function getLenseWithId( $id )
        {
            global $query, $main, $utils, $lang, $mysql;
            
            $r = $mysql->mq( "SELECT * FROM ".( $this->global ? $this->gl_dbase_string : "" )."`".$mysql->t_prefix."lenses` WHERE `view`=1 AND `id`=".$id );
            
            return $r ? $r : 0;
        }
        
        function getListForOrders( $ex = 0 )
        {
            global $query, $main, $utils, $lang, $mysql;
            
            $ar = array();
            $a = $mysql->mqm( "SELECT * FROM ".( $this->global ? $this->gl_dbase_string : "" )."`".$mysql->t_prefix."lenses` WHERE `view`=1".( $ex ? " AND `id`<>".$ex : '' ) );
            while( $r = @mysql_fetch_assoc( $a ) ) {
                $ar[$r['id']] = $r;
            }
            
            return $ar;
        }
	
	//
	// Далее администраторская область
	//
	
	function getAdminScreen( &$selectedElement, $path )
	{
		global $mysql, $query, $utils, $admin, $lang, $main;
		
		if( $query->gp( "createlense" ) && $query->gp( "process" ) ) {
			
			$name = isset( $_POST['name'] ) && $_POST['name'] ? str_replace( "&nbsp;", " ", str_replace( "'", "\\'", str_replace( '"', "&quot;", $_POST['name'] ) )  ) : '';
			$image = $query->gp( "image" );
			$country = isset( $_POST['country'] ) && $_POST['country'] ? str_replace( "&nbsp;", " ", str_replace( "'", "\\'", str_replace( '"', "&quot;", $_POST['country'] ) ) ) : '';
			$info = str_replace( "'", "\\'", $_POST['info'] );
			$price = $query->gp( "price" );
			
			$dd = $_POST['dio'];
			$dio = "";
			foreach( $dd as $v ) {
				$dio .= ( $dio ? ":" : "" )."-".$v."-";
			}
			$dd = $_POST['ot'];
			$ots = "";
			foreach( $dd as $v ) {
				$ots .= ( $ots ? ":" : "" )."-".$v."-";
			}
			$dd = $_POST['types'];
			$types = "";
			foreach( $dd as $v ) {
				$types .= ( $types ? ":" : "" )."-".$v."-";
			}
			
			$mysql->mu( "INSERT INTO ".( $this->global ? $this->gl_dbase_string : "" )."`".$mysql->t_prefix."lenses` VALUES(
				0,
				'".$name."',
				'".$price."',
				'".$country."',
				'".$info."',
				'".$image."',
				1,
				'".$dio."',
				'".$types."',
				'".$ots."'
			);" );
			
			$r = $mysql->mq( "SELECT `id` FROM ".( $this->global ? $this->gl_dbase_string : "" )."`".$mysql->t_prefix."lenses` WHERE 1 ORDER BY `id` DESC" );
			
			if( $r ) {
			
				if( $image ) {
					@copy( ROOT_PATH."tmp/".$image, ROOT_PATH."files/upload/lenses/".$image );
					@unlink( ROOT_PATH."tmp/".$image );
				}
			
			}
			
			return "<script>document.location = '".$mysql->settings['local_folder']."admin/".$path."';</script>";
			
		} else if( $query->gp( "edit" ) && $query->gp( "process" ) ) {
			
			$id = $query->gp( "edit" );
			$ep = $mysql->mq( "SELECT `image` FROM ".( $this->global ? $this->gl_dbase_string : "" )."`".$mysql->t_prefix."lenses` WHERE `id`=".$id );
			
			$name = isset( $_POST['name'] ) && $_POST['name'] ? str_replace( "&nbsp;", " ", str_replace( "'", "\\'", str_replace( '"', "&quot;", $_POST['name'] ) )  ) : '';
			$image = $query->gp( "image" );
			$country = isset( $_POST['country'] ) && $_POST['country'] ? str_replace( "&nbsp;", " ", str_replace( "'", "\\'", str_replace( '"', "&quot;", $_POST['country'] ) ) ) : '';
			$info = str_replace( "'", "\\'", $_POST['info'] );
			$price = $query->gp( "price" );
			
			$dd = $_POST['dio'];
			$dio = "";
			foreach( $dd as $v ) {
				$dio .= ( $dio ? ":" : "" )."-".$v."-";
			}
			$dd = $_POST['ot'];
			$ots = "";
			foreach( $dd as $v ) {
				$ots .= ( $ots ? ":" : "" )."-".$v."-";
			}
			$dd = $_POST['types'];
			$types = "";
			foreach( $dd as $v ) {
				$types .= ( $types ? ":" : "" )."-".$v."-";
			}
			
			if( $image ) {
				
				if( $ep['image'] )
					@unlink( ROOT_PATH."files/upload/lenses/".$ep['image'] );
				
				@copy( ROOT_PATH."tmp/".$image, ROOT_PATH."files/upload/lenses/".$image );
				@unlink( ROOT_PATH."tmp/".$image );
			
			}
			
			$mysql->mu( "UPDATE ".( $this->global ? $this->gl_dbase_string : "" )."`".$mysql->t_prefix."lenses` SET

				`name`='".$name."',
				".( $image ? "`image`='".$image."'," : "" )."
				`country`='".$country."',
				`price`='".$price."',
				`d`='".$dio."',
				`t`='".$types."',
				`ot`='".$ots."',
				`info`='".$info."'
				
			WHERE `id`=".$id );
			
			$query->setProperty( "edit", 0 );
			
		} else if( $query->gp( "turnlense" ) ) {
			
			$id = $query->gp( "turnlense" );
			$ep = $mysql->mq( "SELECT `view` FROM ".( $this->global ? $this->gl_dbase_string : "" )."`".$mysql->t_prefix."lenses` WHERE `id`=".$id );			
			if( $ep )
				$mysql->mu( "UPDATE ".( $this->global ? $this->gl_dbase_string : "" )."`".$mysql->t_prefix."lenses` SET `view`=".( $ep['view'] ? "0" : "1" )." WHERE `id`=".$id );
                        return "<script>document.location = '".$mysql->settings['local_folder']."admin/".$path."';</script>";
				
		} else if( $query->gp( "delete" ) ) {
			
			$id = $query->gp( "delete" );
			$ep = $mysql->mq( "SELECT * FROM ".( $this->global ? $this->gl_dbase_string : "" )."`".$mysql->t_prefix."lenses` WHERE `id`=".$id );			
			if( $ep ) {
				$mysql->mu( "DELETE FROM ".( $this->global ? $this->gl_dbase_string : "" )."`".$mysql->t_prefix."lenses` WHERE `id`=".$id );
				if( $ep['image'] )
					@unlink( ROOT_PATH."files/upload/lenses/".$ep['image'] );
			}
			
		}
		
		if( $query->gp( "createlense" ) ) {
			
			return $this->getExternalNewAction( $path );
			
		} else if( $query->gp( "edit" ) ) {
			
			return $this->getExternalEditAction( $path, $query->gp( "edit" ) );
			
		}
		
		$selectedElement = 1;
		$t = "
			<h1>Список лизн</h1>
			
			<a href=\"".$mysql->settings['local_folder']."admin/".$path."/createlense\">Добавить новую линзу</a><br><br>
			
			<table cellspacing=0 cellpadding=0 class='list_table' style='width: 100%;'>
				<tr class='list_table_header'>
					<td width=50>
						ID
					</td>
					<td width=70% style='text-align: left;'>
						Название линзы и страна производитель
					</td>
					<td width=10%>
						Стоимость
					</td>
					<td width=10%>
						Мини превью
					</td>
					<td width=110>
						Опции
					</td>
				</tr>
		";
		
		$counter = 0;
		$a = $mysql->mqm( "SELECT * FROM ".( $this->global ? $this->gl_dbase_string : "" )."`".$mysql->t_prefix."lenses` WHERE 1 ORDER BY `id` ASC" );
		while( $r = @mysql_fetch_assoc( $a ) ) {
			
			$t .= "
				<tr class='list_table_element'".( !$r['view'] ? " style='background-color: #fcd9d9;'" : "" ).">
					<td valign=middle>
						".$r['id']."
					</td>
					<td style='text-align: left;' valign=middle>
						<a href=\"".LOCAL_FOLDER."admin/".$path."/edit".$r['id']."\"><strong>".$r['name']."</strong></a><br>
						---<br>
						".( $r['country'] ? $r['country'] : "-" )."
					</td>
					<td valign=middle align=center>
						".$r['price']."
					</td>
					<td>
						".( $r['image'] ? "<img src=\"".$mysql->settings['local_folder']."files/upload/lenses/".$r['image']."\" style='max-width: 200px;' />" : "-" )."
					</td>
					<td nowrap>
						<a href=\"".LOCAL_FOLDER."admin/".$path."/turnlense".$r['id']."\">".( $r['view'] ? "Выключить" : "Включить" )."</a><br>
						<a href=\"".LOCAL_FOLDER."admin/".$path."/edit".$r['id']."\">Редактировать</a><br>
						<a href=\"".LOCAL_FOLDER."admin/".$path."/delete".$r['id']."\" onclick=\"if( !confirm( 'Вы уверены?' ) ) return false; return true;\">Удалить</a>
					</td>
				</tr>
			";
			$counter++;
		}
		
		$t .= "
			<tr class='list_table_footer'>
					<td colspan=5>
						Всего линз: ".$counter."
					</td>
				</tr>
		</table>
		";
		
		return $t;
	}
	
	function getExternal( $wt, $link )
	{
		global $query, $mysql;
		
		return "Unknown actions query";
	}
	
	function getExternalNewAction( $link )
	{
		global $query, $mysql, $lang, $main, $utils;
		
		foreach( $main->modules->modules as $k => $v ) {
			$rr .= "<option value='".$k."'>".$v['instance']->getName()."</option>";
		}
		
		$inner = "
				<p>
					Название линзы: <label class='red'>*</label><br>
					<input type=text name=\"name\" id=\"name\" value=\"\" class='text_input' />
				</p>
				<p>
					Страна производитель:<br>
					<input type=text name=\"country\" id=\"country\" value=\"\" class='text_input' />
				</p>
				<p>
					Стоимость линзы: <label class='red'>*</label><br>
					<input type=text name=\"price\" id=\"price\" value=\"\" class='text_input' />
				</p>
				<p>
					Типы очков:<br>
					<select name='ot[]' id='ot' multiple style='height: 120px; width: 250px;'>
						".$main->listings->getListingForSelecting( 12, 0, 0, "", "", false,  '', true )."
					</select>
				</p>	
				<p>
					Диоптрии:<br>
					<select name='dio[]' id='dio' multiple style='height: 120px; width: 250px;'>
						".$main->listings->getListingForSelecting( 14, 0, 0, "", "", false,  '', true )."
					</select>
				</p>	
				<p>
					Типы линз:<br>
					<select name='types[]' id='types' multiple style='height: 120px; width: 250px;'>
						".$main->listings->getListingForSelecting( 13, 0, 0, "", "", false,  '', true )."
					</select>
				</p>				
				<p>
					Блок ИНФО:<br>
					<textarea name=\"info\" id=\"info\" rows=25 class='textarea_input'></textarea>
				</p>
				
				<p>
					Загрузите изображение линзы. При загрузке учитывайте следующее:
					<ol>
						<li>Высота и ширина линзы может быть любая;</li>
						<li>Автоподгонка размеров НЕ используется.</li>
					</ol>
				</p>
				
				<label class=\"uploadbutton\" id=\"action_upload\">
					<span id=\"action_upload_innerspan\">
						Выберите файл
					</span>
				</label>
				
				<input type=hidden name=\"image\" id=\"image\" value=\"\" />
				<div class='error' id='action_error'></div>
				<div id='action_image' style='margin-top: 7px; margin-bottom: 7px;'></div>
				
				<script>
					$( '#action_upload' ).each(
					function()
					{
						new AjaxUpload( '#' + this.id, {
					    	name: 'action',
       						action: '".$mysql->settings['local_folder']."admin/getblock',
       						data: {
    							external: '1',
    							type: '13',
    							extensions: 'gif|jpg|jpeg|png',
    							totemp: '1',
    							fname: 'action'
  							},
			       			autoSubmit: true,
		    			   	onSubmit: function( file, ext ) {
					       		if( !( ext && /^(jpg|jpeg|gif|png)$/.test( ext ) ) ) {
        					    	$( '#action_error' ).html( 'Неверное расширение файла' ).show();
                					return false;
					            }
       		
					            $( '#action_error' ).hide();
					       	},
       						onComplete: function( file, data ) {
			       				if( data == '0' || !data || data < 0 ) {
       								$( '#action_error' ).html( 'Ошибка обработки файла' ).show();
       							} else {
       								$( '#action_image' ).html( '<img src=\"".$mysql->settings['local_folder']."tmp/' + data + '\" />' );
       								$( '#action_error' ).hide();
       								
			       					$( '#image' ).attr( 'value', data );
			       					
       							}
			       			}
						} );
					} );
				</script>
				
				<script type=\"text/javascript\" src=\"".$mysql->settings['local_folder'].$utils->javascript_files_path."tinymce/jscripts/tiny_mce/tiny_mce.js\"></script>
				<script type=\"text/javascript\">
	tinyMCE.init({
		
		mode : \"exact\",
		elements : \"info\",
		language : \"ru\",
		theme : \"advanced\",
		plugins : \"pagebreak,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template,wordcount,advlist,autosave\",

		
		theme_advanced_buttons1 : \"bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,styleselect,formatselect,fontselect,fontsizeselect,|,forecolor,backcolor\",
		theme_advanced_buttons2 : \"cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,image,cleanup,code,|,insertdate,inserttime,preview\",
		theme_advanced_buttons3 : \"tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,emotions,iespell,media,advhr,|,print,|,ltr,rtl,|,fullscreen\",
		theme_advanced_buttons4 : \"insertlayer,moveforward,movebackward,absolute,|,styleprops,|,cite,abbr,acronym,del,ins,attribs,|,visualchars,nonbreaking,template,pagebreak,restoredraft\",
		theme_advanced_toolbar_location : \"top\",
		theme_advanced_toolbar_align : \"left\",
		theme_advanced_statusbar_location : \"bottom\",
		theme_advanced_resizing : true,

		
		content_css : \"css/content.css\",

		
		template_external_list_url : \"lists/template_list.js\",
		external_link_list_url : \"lists/link_list.js\",
		external_image_list_url : \"lists/image_list.js\",
		media_external_list_url : \"lists/media_list.js\",

		
		style_formats : [
			{title : 'Bold text', inline : 'b'},
			{title : 'Red text', inline : 'span', styles : {color : '#ff0000'}},
			{title : 'Red header', block : 'h1', styles : {color : '#ff0000'}},
			{title : 'Example 1', inline : 'span', classes : 'example1'},
			{title : 'Example 2', inline : 'span', classes : 'example2'},
			{title : 'Table styles'},
			{title : 'Table row 1', selector : 'tr', classes : 'tablerow1'}
		],
		
		convert_urls : false,
        relative_urls : false,
        remove_script_host : false,  
        
		template_replace_values : {
			username : \"Some User\",
			staffid : \"991234\"
		}
	});
</script>
			";
			
		return "
				<h1 align=left>Добавление новой линзы</h1>
				
				<form action=\"".$mysql->settings['local_folder']."admin/".$link."\" method=POST id='creatings' style='text-align: left;'>
					".$inner."
					<div style='width: 300px; border-top: 1px solid #aaa; padding-top: 5px;'>
						<input type=hidden name=\"process\" id=\"process\" value=\"0\" />
						<input type=hidden name=\"createlense\" id=\"createlense\" value=\"0\" />						
						<input type=button value=\"Создать\" class='button_input' onclick=\"
							if( $( '#name' ).attr( 'value' ) == '' ) { 
								alert( 'Укажите название линзы' ); 
								return false; 
							}
							$( '#process' ).attr( 'value', 1 );
							$( '#createlense' ).attr( 'value', 1 );
							$( '#creatings' ).submit();
						\" />&nbsp;&nbsp;
						<input type=button value=\"Отменить\" class='button_input' onclick=\"$( '#creatings' ).submit();\" />
					</div>
				</form>
		";
	}
	
	function getExternalEditAction( $link, $actionid )
	{
		global $query, $mysql, $lang, $main, $utils;
		
		$data = $mysql->mq( "SELECT * FROM ".( $this->global ? $this->gl_dbase_string : "" )."`".$mysql->t_prefix."lenses` WHERE `id`=".$actionid );
		if( !$data ) {
			return "Unknown lense id";
		}
		
		$tt = explode( ':', str_replace( "-", "", $data['d'] ) );
		$d = $main->listings->getListingElementsArray( 14, 0, false, '', true );
		$dd = "";
		foreach( $d as $k => $v ) {
			$dd .= "<option value=\"".$k."\"".( $utils->searchArrayForValue( $tt, $k ) === false ? "" : " selected" ).">".$lang->gp( $v['value'], true )."</option>";
		}
		
		$tt = explode( ':', str_replace( "-", "", $data['t'] ) );
		$d = $main->listings->getListingElementsArray( 13, 0, false, '', true );
		$types = "";
		foreach( $d as $k => $v ) {
			$types .= "<option value=\"".$k."\"".( $utils->searchArrayForValue( $tt, $k ) === false ? "" : " selected" ).">".$lang->gp( $v['value'], true )."</option>";
		}
		
		$tt = explode( ':', str_replace( "-", "", $data['ot'] ) );
		$d = $main->listings->getListingElementsArray( 12, 0, false, '', true );
		$ots = "";
		foreach( $d as $k => $v ) {
			$ots .= "<option value=\"".$k."\"".( $this->searchArrayForValue( $tt, $k ) === false ? "" : " selected" ).">".$lang->gp( $v['value'], true )."</option>";
		}
		
		$inner = "
				<p>
					Название линзы: <label class='red'>*</label><br>
					<input type=text name=\"name\" id=\"name\" value=\"".$data['name']."\" class='text_input' />
				</p>
				<p>
					Страна производитель:<br>
					<input type=text name=\"country\" id=\"country\" value=\"".$data['country']."\" class='text_input' />
				</p>
				<p>
					Стоимость линзы: <label class='red'>*</label><br>
					<input type=text name=\"price\" id=\"price\" value=\"".$data['price']."\" class='text_input' />
				</p>
				<p>
					Типы очков:<br>
					<select name='ot[]' id='ot' multiple style='height: 120px; width: 250px;'>
						".$ots."
					</select>
				</p>	
				<p>
					Диоптрии:<br>
					<select name='dio[]' id='dio' multiple style='height: 120px; width: 250px;'>
						".$dd."
					</select>
				</p>	
				<p>
					Типы линз:<br>
					<select name='types[]' id='types' multiple style='height: 120px; width: 250px;'>
						".$types."
					</select>
				</p>			
				<p>
					Блок ИНФО:<br>
					<textarea name=\"info\" id=\"info\" rows=25 class='textarea_input'>".$data['info']."</textarea>
				</p>
				
				<p>
					Загрузите изображение линзы. При загрузке учитывайте следующее:
					<ol>
						<li>Высота и ширина линзы может быть любая;</li>
						<li>Автоподгонка размеров НЕ используется.</li>
					</ol>
				</p>
				
				<label class=\"uploadbutton\" id=\"action_upload\">
					<span id=\"action_upload_innerspan\">
						Выберите файл
					</span>
				</label>
				
				<input type=hidden name=\"image\" id=\"image\" value=\"\" />
				<div class='error' id='action_error'></div>
				<div id='action_image' style='margin-top: 7px; margin-bottom: 7px;'>
					".( $data['image'] ? "<img src=\"".$mysql->settings['local_folder']."files/upload/lenses/".$data['image']."\" />" : "" )."
				</div>
				
				<script>
					$( '#action_upload' ).each(
					function()
					{
						new AjaxUpload( '#' + this.id, {
					    	name: 'action',
       						action: '".$mysql->settings['local_folder']."admin/getblock',
       						data: {
    							external: '1',
    							type: '13',
    							extensions: 'gif|jpg|jpeg|png',
    							totemp: '1',
    							fname: 'action'
  							},
			       			autoSubmit: true,
		    			   	onSubmit: function( file, ext ) {
					       		if( !( ext && /^(jpg|jpeg|gif|png)$/.test( ext ) ) ) {
        					    	$( '#action_error' ).html( 'Неверное расширение файла' ).show();
                					return false;
					            }
       		
					            $( '#action_error' ).hide();
					       	},
       						onComplete: function( file, data ) {
			       				if( data == '0' || !data || data < 0 ) {
       								$( '#action_error' ).html( 'Ошибка обработки файла' ).show();
       							} else {
       								$( '#action_image' ).html( '<img src=\"".$mysql->settings['local_folder']."tmp/' + data + '\" />' );
       								$( '#action_error' ).hide();
       								
			       					$( '#image' ).attr( 'value', data );
			       					
       							}
			       			}
						} );
					} );
				</script>
				
				<script type=\"text/javascript\" src=\"".$mysql->settings['local_folder'].$utils->javascript_files_path."tinymce/jscripts/tiny_mce/tiny_mce.js\"></script>
				<script type=\"text/javascript\">
	tinyMCE.init({
		
		mode : \"exact\",
		elements : \"info\",
		language : \"ru\",
		theme : \"advanced\",
		plugins : \"pagebreak,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template,wordcount,advlist,autosave\",

		
		theme_advanced_buttons1 : \"bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,styleselect,formatselect,fontselect,fontsizeselect,|,forecolor,backcolor\",
		theme_advanced_buttons2 : \"cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,image,cleanup,code,|,insertdate,inserttime,preview\",
		theme_advanced_buttons3 : \"tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,emotions,iespell,media,advhr,|,print,|,ltr,rtl,|,fullscreen\",
		theme_advanced_buttons4 : \"insertlayer,moveforward,movebackward,absolute,|,styleprops,|,cite,abbr,acronym,del,ins,attribs,|,visualchars,nonbreaking,template,pagebreak,restoredraft,smimage,smexplorer\",
		theme_advanced_toolbar_location : \"top\",
		theme_advanced_toolbar_align : \"left\",
		theme_advanced_statusbar_location : \"bottom\",
		theme_advanced_resizing : true,

		
		content_css : \"css/content.css\",

		
		template_external_list_url : \"lists/template_list.js\",
		external_link_list_url : \"lists/link_list.js\",
		external_image_list_url : \"lists/image_list.js\",
		media_external_list_url : \"lists/media_list.js\",

		
		style_formats : [
			{title : 'Bold text', inline : 'b'},
			{title : 'Red text', inline : 'span', styles : {color : '#ff0000'}},
			{title : 'Red header', block : 'h1', styles : {color : '#ff0000'}},
			{title : 'Example 1', inline : 'span', classes : 'example1'},
			{title : 'Example 2', inline : 'span', classes : 'example2'},
			{title : 'Table styles'},
			{title : 'Table row 1', selector : 'tr', classes : 'tablerow1'}
		],
		
		convert_urls : false,
        relative_urls : false,
        remove_script_host : false,  
        
		template_replace_values : {
			username : \"Some User\",
			staffid : \"991234\"
		}
	});
</script>
			";
			
		return "
				<h1 align=left>Редактирование линзы «".$data['name']."»</h1>
				
				<form action=\"".$mysql->settings['local_folder']."admin/".$link."\" method=POST id='creatings' style='text-align: left;'>
					".$inner."
					<div style='width: 300px; border-top: 1px solid #aaa; padding-top: 5px;'>
						<input type=hidden name=\"process\" id=\"process\" value=\"0\" />
						<input type=hidden name=\"edit\" id=\"edit\" value=\"0\" />
						<input type=button value=\"Сохранить\" class='button_input' onclick=\"
							if( $( '#name' ).attr( 'value' ) == '' ) { 
								alert( 'Укажите название линзы' ); 
								return false; 
							}
							$( '#process' ).attr( 'value', 1 );
							$( '#edit' ).attr( 'value', ".$actionid." );
							$( '#creatings' ).submit();
						\" />&nbsp;&nbsp;
						<input type=button value=\"Отменить\" class='button_input' onclick=\"$( '#creatings' ).submit();\" />
					</div>
				</form>
		";
	}
}

?>
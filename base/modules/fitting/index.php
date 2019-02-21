<?php

if( !defined( "in_ochki" ) && !defined( "in_ochki_admin" ) ) die( "You can't access this file directly" );

//
// Очень важно, чтобы объявление класса, у примеру "class moduleabout extends RootModule" было именно такое, какое есть сейчас. 
// Меняется только слово about в случае указанного примера. Также четко следите за регистром.
// Касаемо регистра. В модулях должны совпадать: название каталога, название в классе и название в БД (к примеру везде about или Projects или SuBwAy и тд.)
// 

class modulefitting extends RootModule
{
	var $dbinfo = null;
	var $curFilePath = "";
	var $array = 0;
	
	var $goingLogin = '';
        
        var $gl_dbase_string = "`shop`.";
	
	function init( $dbinfo )
	{
		global $query;
		
		$this->dbinfo = $dbinfo;
		$this->curFilePath = $this->getRootFilePath( __FILE__ );
		
		if( $query->gp( "fine_fitting" ) ) {
			$this->goingLogin = $_COOKIE['ochki_websid'];
		}
	}
	
	function getMainmenuLink()
	{
		global $main, $mysql, $lang;
		
		$c = $this->getFittingsCurrentCount();
		
		return "
		<a style='display: inline !important;' href=\"".$mysql->settings['local_folder'].$this->dbinfo['local']."\" class='right top_home' onclick=\"return false;\">
			<img src='".$mysql->settings['local_folder']."images/top_home.png' /><span>".$lang->gp( 18 )."</span><div class='counter".( !$c ? " invisible" : "" )."'>".$c."</div>
			<div class='table'>".$this->getFiitingTable()."</div>
		</a>";
	}
        
        function getMobileMenuLink()
	{
		global $main, $mysql;
		
		return "<a class='mmenu' href=\"".$mysql->settings['local_folder'].$this->dbinfo['local']."\">Примерка \"на дому\"</a>";
	}
	
	function getFiitingTable()
	{
		global $main, $lang, $mysql, $utils;
		
		$curUser = $main->users->auth ? $main->users->userArray['id'] : 0;
		$curSid = $main->users->sid;
		
		$max_fitting_items = $this->getParam( "max_fitting_items" );
		$c = 0;
		$t = "";
		$a = $mysql->mqm( "SELECT * FROM `".$mysql->t_prefix."primerka` WHERE ".( $curUser ? "`user`=".$curUser : "`session`='".$curSid."'" )." ORDER BY `date` DESC" );
		while( $r = @mysql_fetch_assoc( $a ) ) {
			$good = $main->modules->gmi( "catalog" )->getItem( $r['good'] );
			if( !$good )
				continue;
			$c++;
			$props = $main->properties->getPropertiesOfGood( $r['good'] );
			if( $good['root'] ) {
				$rdata = $main->modules->gmi( "catalog" )->getItem( $good['root'] );
				$good['name'] = $rdata['name'];
			}
			$preview = $this->getElementByData( $props, "prop_id", 14 );
			$preview = $preview ? $preview['value'] : '';
			$link = "https://".$_SERVER['HTTP_HOST'].$mysql->settings['local_folder']."catalog/show".$good['id']."/".strtolower( $utils->translitIt( $good['name'] ) )."_".strtolower( $utils->translitIt( $good['article'] ) ).".html";
                        
                        $discount = $main->modules->gmi( "discount" )->getDiscountForGood( $good );
                        $good['discount_price'] = $discount ? $good['price'] - ( $good['price'] / 100 * $discount['percent'] ) : 0;
                        
			$t .= "
			<div class='elem'>
				<div class='del pointer' onclick=\"removeFittingElem( ".$r['good'].", 1 );\"><img src='/images/small_cross.png' /></div>
				<div class='price'>".$utils->digitsToRazryadi( $good['discount_price'] ? ceil( $good['discount_price'] ) : $good['price'] )." <img src='/images/rubs.png' /></div>
				<div class='img' style='background: url(".( $preview ? $mysql->settings['local_folder']."files/upload/goods/".$preview : "/images/no.png" ).") no-repeat; background-size: cover; background-position: 50% 50%;' onclick=\"urlmove('".$link."');\"></div>
				<div class='name' onclick=\"urlmove('".$link."');\">".$good['name']."</div>
				<div class='clear'></div>
			</div>
			";
		}
		while( ++$c <= $max_fitting_items ) {
			$t .= "
			<div class='elem'>
				<div class='img' style='background: url(/images/no.png) no-repeat; background-size: cover; background-position: 50% 50%;'></div>
				<div class='name empty'>Пусто</div>
				<div class='clear'></div>
			</div>
			";
		}
		
		return $t."
		<div class='table_bottom'>
			<div class='but' onclick=\"urlmove('".$mysql->settings['local_folder'].$this->dbinfo['local']."');\">Оформить</div>
			<div class='text'>Вы можете заказать не более ".$max_fitting_items." оправ для примерки на дому</div>
			<div class='clear'></div>
		</div>
		";
	}
	
	function getFittingsCurrentCount()
	{
		global $main, $mysql;
		
		$curUser = $main->users->auth ? $main->users->userArray['id'] : 0;
		$curSid = $main->users->sid;
		
		return $mysql->getTableRecordsCount( "`".$mysql->t_prefix."primerka`", ( $curUser ? "`user`=".$curUser : "`session`='".$curSid."'" ) );
	}
	
	function addFitElem( $good, $add = '' )
	{
		global $main, $lang, $mysql, $utils;
		
		$curUser = $main->users->auth ? $main->users->userArray['id'] : 0;
		$curSid = $main->users->sid;
		
		$r = $mysql->mq( "SELECT `id` FROM `".$mysql->t_prefix."primerka` WHERE ".( $curUser ? "`user`=".$curUser : "`session`='".$curSid."'" )." AND `good`=".$good );
		if( $r )
			return '';
			
		$max_fitting_items = $this->getParam( "max_fitting_items" ) - 1;
		$c = 0;
		$a = $mysql->mqm( "SELECT `id` FROM `".$mysql->t_prefix."primerka` WHERE ".( $curUser ? "`user`=".$curUser : "`session`='".$curSid."'" )." ORDER BY `date` DESC" );
		while( $r = @mysql_fetch_assoc( $a ) ) {
			if( ++$c > $max_fitting_items )
				$mysql->mu( "DELETE FROM `".$mysql->t_prefix."primerka` WHERE `id`=".$r['id'] );
		}
		
		$mysql->mu( "INSERT INTO `".$mysql->t_prefix."primerka` VALUES(
			0,
			".$curUser.",
			'".$curSid."',
			".$good.",
			".time().",
			'".$add."'
		);" );
		
		return $this->getFiitingTable();
	}
	
	function removeFitElem( $good, $add = '' )
	{
		global $main, $lang, $mysql, $utils;
		
		$curUser = $main->users->auth ? $main->users->userArray['id'] : 0;
		$curSid = $main->users->sid;
		
		$mysql->mu( "DELETE FROM `".$mysql->t_prefix."primerka` WHERE ".( $curUser ? "`user`=".$curUser : "`session`='".$curSid."'" )." AND `good`=".$good );

		return $this->getFiitingTable();
	}
	
	function isInFitting( $id )
	{
		global $main, $lang, $mysql, $utils;
		
		$curUser = $main->users->auth ? $main->users->userArray['id'] : 0;
		$curSid = $main->users->sid;
		
		if( !$this->array ) {
			$this->array = array();
			$a = $mysql->mqm( "SELECT `good` FROM `".$mysql->t_prefix."primerka` WHERE ".( $curUser ? "`user`=".$curUser : "`session`='".$curSid."'" )." ORDER BY `date` DESC" );
			while( $r = @mysql_fetch_assoc( $a ) ) {
				$this->array[$r['good']] = true;
			}
		}
		
		return isset( $this->array[$id] ) && $this->array[$id] ? true : false;
	}
	
	function getContent()
	{
		global $lang, $main, $mysql, $query, $utils;
		
		if( $query->gp( "process_make_fitting" ) ) {			
			
                    $t = $this->processOrder();
                    if( $t )
                        return $t;			
                    
		}
		
		$catalog = $main->modules->gmi( "catalog" );
		$lenses = $main->modules->gmi( "lenses" );
		
		$step = $query->gp( "step" );
		
		$curUser = $main->users->auth ? $main->users->userArray['id'] : 0;
		$curSid = $main->users->sid;
		
		$main->templates->setTitle( "Оформление примерки на дому", true );
		$localAdress = "https://".$_SERVER['HTTP_HOST'].$mysql->settings['local_folder'];
		$options = $main->users->auth ? $main->users->getUserProfile( $main->users->userArray['id'] ) : '';
		
		$max_fitting_items = $this->getParam( "max_fitting_items" );
		$c = 0;
		$adds = "";
		$a = $mysql->mqm( "SELECT * FROM `".$mysql->t_prefix."primerka` WHERE ".( $curUser ? "`user`=".$curUser : "`session`='".$curSid."'" )." ORDER BY `date` DESC" );
		while( $r = @mysql_fetch_assoc( $a ) ) {
			$good = $catalog->getItem( $r['good'] );
			if( !$good )
				continue;
			$c++;
			$props = $main->properties->getPropertiesOfGood( $r['good'] );
			if( $good['root'] ) {
				$rdata = $main->modules->gmi( "catalog" )->getItem( $good['root'] );
				$good['name'] = $rdata['name'];
			}
			$preview = $this->getElementByData( $props, "prop_id", 14 );
			$preview = $preview ? $preview['value'] : '';
			$link = "https://".$_SERVER['HTTP_HOST'].$mysql->settings['local_folder']."catalog/show".$good['id']."/".strtolower( $utils->translitIt( $good['name'] ) )."_".strtolower( $utils->translitIt( $good['article'] ) ).".html";
			
			$color = $this->getElementByData( $props, "prop_id", 1 );
			$colorName = $color && is_numeric( $color['value'] ) ? $main->listings->getListingElementValueById( 3, $color['value'], true ) : $color['value'];
		
			$adds .= "
			<div class='elem'>
				<div class='block_image'>".( $preview ? "<img src='".$mysql->settings['local_folder']."files/upload/goods/".$preview."' alt='Оправа' />" : '' )."</div>
				<div class='block_text' style='float: none; width: auto;'>
					<div class='block_edit' style='width: auto; white-space: nowrap;'>
					<a href='#' onclick=\"if( !confirm( 'Вы уверены?' ) ) return false; removeFittingElem( ".$r['good'].", 1 ); urlmove('".$mysql->settings['local_folder'].$this->dbinfo['local']."'); return false;\"><img src='/images/remove.png' /> Удалить</a>
					</div>
					<h3>".$good['name'].( $colorName ? ", цвет ".$colorName : "" )."</h3>
				</div>
				<div class='clear'></div>
			</div>
			";
		}
		
		if( !$c ) {
			return "
		<div class='sopli'><div class='all_lines'>
			<a href='".$mysql->settings['local_folder']."'>".$lang->gp( 8 )."</a><span>></span>Оформление примерки на дому
		</div></div>
			
		<div class='catalog catalog_nomargin catalog_marginbottom'>
			<div class='all_lines'>
				".$lang->gp( 142 )."
			</div>
		</div>
		";
		}
		
		$t = "
		<script src=\"/jsf/im/jquery.inputmask.bundle.js\"></script>
  		<script src=\"/jsf/im/phone.js\"></script>
  		<script src=\"/jsf/im/inputmask.binding.js\"></script>
  		
			<div class='sopli'><div class='all_lines'>
				<a href='".$mysql->settings['local_folder']."'>".$lang->gp( 8 )."</a><span>></span>Оформление примерки на дому
			</div></div>
			
			<div class='catalog catalog_nomargin catalog_marginbottom'>
				<div class='all_lines'>
					<div class='main_data' style='opacity: 0;'><div class='md_inner_marginright'>
						
						<div class='itogtable fitting_home'>
							<div class='titler'>
								<div class='one one_half current'><div class='h'></div><div class='inr'><span>1</span> Примерка на дому</div></div>
								<div class='one one_half last'><div class='h'></div><div class='inr'><span>2</span> Адрес примерки</div></div>
							</div>
							<div class='contents'>
								
								".$adds."
								
								<div class='block_button'>
									<div class='button' onclick=\"
										processSimpleAsyncReqForModule( '".$this->dbinfo['local']."', '101', '&p=".urlencode( $_SERVER['REQUEST_URI'] )."', 'updateSituation( data );' );
									\">
										Продолжить
									</div>
								</div>
								<div class='clear'></div>
							</div>
						</div>
					
					</div></div>
					<div class='clear'></div>
				</div>
			</div>
			
			<script>
				$(window).load(function()
				{
					$( '.catalog .main_data' ).width( $( '.catalog .all_lines' ).width() - $( '.catalog .itogorder' ).width() - 4 ).animate( { opacity: 1 }, 200 );
					".( $step ? "
					processSimpleAsyncReqForModule( '".$this->dbinfo['local']."', '".$step."', '&p=".urlencode( $_SERVER['REQUEST_URI'] )."', 'updateSituation( data ); $( \'.catalog .main_data\' ).animate( { opacity: 1 }, 200 ); ' );
					" : "
					$( '.catalog .main_data' ).animate( { opacity: 1 }, 200 );
					" )."
					$( '.in_itog .block_image' ).height( $( '.in_itog .block_image' ).parent().height() );
					$( '.contents .block_image' ).height( $( '.contents .block_image' ).parent().height() );
				});
			
				$(window).resize(function()
				{
					$( '.catalog .main_data' ).width( $( '.catalog .all_lines' ).width() - $( '.catalog .itogorder' ).width() - 4 );
					$( '.in_itog .block_image' ).height( $( '.in_itog .block_image' ).parent().height() );
					$( '.contents .block_image' ).height( $( '.contents .block_image' ).parent().height() );
				});
				
				function updateSituation( data )
				{
					var ar = data.toString().split( '~' );
					$( '.itogtable .titler' ).html( ar[0] );
					$( '.itogtable .contents' ).html( ar[1] );
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
			
			case 1: // Добавить товар в примерку
				$good = $query->gp( "good" );
				return $this->addFitElem( $good );
				
			case 2: // Получить кол-во элементов в примерочной
				return $this->getFittingsCurrentCount();
				
			case 3: // Удалить элемент из примерочной
				$good = $query->gp( "good" );
				return $this->removeFitElem( $good );
			
			case 101: // Получить второй этап оформления заказа
			
				$t = "<div class='one one_half passed'><div class='h'></div><div class='inr'><span>1</span> Примерка на дому</div></div>
				<div class='one one_half current last'><div class='h'></div><div class='inr'><span>2</span> Адрес доставки</div></div>~";
				
				if( $main->users->auth ) {
					$profile = $main->users->getUserProfile( $main->users->userArray['id'] );
					$p = $query->gp( "p" );
					$p = $p ? $p : $_SERVER['REQUEST_URI'];
					$t .= "
					<div class='all_forms' id='verifyform'>
						<div class='input input_low'>
							<div class='title'>Имя <span class='red'>*</span></div>
							<input type=text name='p_name' id='p_name' value=\"".( isset( $profile['name'] ) ? $profile['name']."\" class='withdata'" : '"' )." onblur=\"if( $( this ).val() ) { $( this ).addClass( 'withdata' ); } else { $( this ).removeClass( 'withdata' ); }\" onkeyup=\"$( this ).parent().find( '.error' ).fadeOut(200);\" />
							<div class='error'></div>
						</div>
						<div class='input input_low'>
							<div class='title'>Фамилия <span class='red'>*</span></div>
							<input type=text name='p_surname' id='p_surname' value=\"".( isset( $profile['suname'] ) ? $profile['suname']."\" class='withdata'" : '"' )." onblur=\"if( $( this ).val() ) { $( this ).addClass( 'withdata' ); } else { $( this ).removeClass( 'withdata' ); }\" onkeyup=\"$( this ).parent().find( '.error' ).fadeOut(200);\" />
							<div class='error'></div>
						</div>
						<div class='input input_low'>
							<div class='title'>Телефон <span class='red'>*</span></div>
							<input type=text name='p_phone' id='p_phone' value=\"".( isset( $profile['phone'] ) && $profile['phone'] ? $profile['phone']."\" class='withdata'" : '"' )." onblur=\"if( $( this ).val() ) { $( this ).addClass( 'withdata' ); } else { $( this ).removeClass( 'withdata' ); }\" placeholder='+7 ___ ___ __ __' data-inputmask=\"'mask': '+7 999 999 99 99', 'clearMaskOnLostFocus': true, 'clearIncomplete': true, 'showMaskOnHover': true, 'showMaskOnFocus': true\" onkeyup=\"$( this ).parent().find( '.error' ).fadeOut(200);\" />
							<div class='error'></div>
						</div>
						<div class='header'>Адрес доставки</div>
						<div class='input input_nonemargintop'>
							<div class='title'>Город <span class='red'>*</span></div>
							<input type=text name='p_city' id='p_city' value=\"".( isset( $profile['city'] ) && $profile['city'] ? $profile['city']."\" class='withdata'" : '"' )." onblur=\"if( $( this ).val() ) { $( this ).addClass( 'withdata' ); } else { $( this ).removeClass( 'withdata' ); }\" onkeyup=\"$( this ).parent().find( '.error' ).fadeOut(200);\" />
							<div class='error'></div>
						</div>
						<div class='input input_low'>
							<div class='title'>Адрес доставки <span class='red'>*</span></div>
							<input type=text name='p_adress' id='p_adress' value=\"".( isset( $profile['adress'] ) && $profile['adress'] ? $profile['adress']."\" class='withdata'" : '"' )." onblur=\"if( $( this ).val() ) { $( this ).addClass( 'withdata' ); } else { $( this ).removeClass( 'withdata' ); }\" onkeyup=\"$( this ).parent().find( '.error' ).fadeOut(200);\" />
							<div class='error'></div>
						</div>
						<div class='input input_textarea input_low'>
							<div class='title'>Дополнительная информация</div>
							<textarea type=text name='p_comments' id='p_comments'".( isset( $profile['comments'] ) && $profile['comments'] ? " class='withdata'" : '' )." onblur=\"if( $( this ).val() ) { $( this ).addClass( 'withdata' ); } else { $( this ).removeClass( 'withdata' ); }\">".( isset( $profile['comments'] ) && $profile['comments'] ? $profile['comments'] : "" )."</textarea>
						</div>
						<div class='clearError' style=' margin-left: 185px;'><span class='red'></span></div>
					</div>
					<div class='clear'></div>
					<div class='block_button'>
						<div class='button' onclick=\"
							if( checkFormOnline() ) {
								processSimpleAsyncReqForModule( 'profile', '4', '&p_surname=' + $( '#p_surname' ).val() + '&p_name=' + $( '#p_name' ).val() + '&p_phone=' + $( '#p_phone' ).val() + '&p_index=' + $( '#p_index' ).val() + '&p_city=' + $( '#p_city' ).val() + '&p_adress=' + $( '#p_adress' ).val() + '&p_comments=' + $( '#p_comments' ).val() , 'processStepThree();' );
							}
						\">
							Продолжить
						</div>
					</div>
					<div class='clear'></div>
					<script>
						function processStepThree()
						{
							urlmove( '".$p.( strstr( $p, "?" ) ? "&" : "?" )."process_make_fitting=1' );
						}
					
						function checkFormOnline()
						{
							if( !$( '#p_name' ).attr( 'done' ) ) {
								if( !$( '#p_name' ).val() ) {
									$( '#p_name' ).parent().find( '.error' ).width( $( '#p_name' ).parent().width() - $( '#p_name' ).parent().find( '.title' ).width() - $( '#p_name' ).width() - 50 ).html( 'Укажите имя' ).show();
									$( '#p_name' ).focus();
									return false;
								}
								$( '#p_name' ).attr( 'done', 1 );
							}
							
							if( !$( '#p_surname' ).attr( 'done' ) ) {
								if( !$( '#p_surname' ).val() ) {
									$( '#p_surname' ).parent().find( '.error' ).width( $( '#p_surname' ).parent().width() - $( '#p_surname' ).parent().find( '.title' ).width() - $( '#p_surname' ).width() - 50 ).html( 'Укажите фамилию' ).show();
									$( '#p_surname' ).focus();
									return false;
								}
								$( '#p_surname' ).attr( 'done', 1 );
							}
							
							if( !$( '#p_phone' ).attr( 'done' ) ) {
								if( !$( '#p_phone' ).val() ) {
									$( '#p_phone' ).parent().find( '.error' ).width( $( '#p_phone' ).parent().width() - $( '#p_phone' ).parent().find( '.title' ).width() - $( '#p_phone' ).width() - 50 ).html( 'Укажите телефон' ).show();
									$( '#p_phone' ).focus();
									return false;
								}
								$( '#p_phone' ).attr( 'done', 1 );
							}
							
							if( !$( '#p_city' ).attr( 'done' ) ) {
								if( !$( '#p_city' ).val() ) {
									$( '#p_city' ).parent().find( '.error' ).width( $( '#p_city' ).parent().width() - $( '#p_city' ).parent().find( '.title' ).width() - $( '#p_city' ).width() - 50 ).html( 'Укажите город' ).show();
									$( '#p_city' ).focus();
									return false;
								}
								$( '#p_city' ).attr( 'done', 1 );
							}
							
							if( !$( '#p_adress' ).attr( 'done' ) ) {
								if( !$( '#p_adress' ).val() ) {
									$( '#p_adress' ).parent().find( '.error' ).width( $( '#p_adress' ).parent().width() - $( '#p_adress' ).parent().find( '.title' ).width() - $( '#p_adress' ).width() - 50 ).html( 'Укажите адрес' ).show();
									$( '#p_adress' ).focus();
									return false;
								}
								$( '#p_adress' ).attr( 'done', 1 );
							}
							
							return true;
						}
					</script>
					";					
				} else {
					$form_id = md5( time().mt_rand( 1, 500 ) );
					$form_id_reg = md5( time().mt_rand( 1, 500 ) );
					$p = $query->gp( "p" );
					$p = $p ? $p : $_SERVER['REQUEST_URI'];
					$t .= "
					<div class='block_menu'>
						<span data-id='1'>Зарегистрироваться</span><div class='divider'></div><span class='link' data-id='2'>Войти</span>
					</div>
					<div class='all_forms' id='regform'><form action=\"".$p."?process_make_fitting=1\" method=POST id='".$form_id_reg."'><input type=hidden name='fine_regfitting' id='fine_regfitting' value='0' />
						<div class='input input_low'>
							<div class='title'>Пол</div>
							<div class='radios'><input type=hidden name='p_gender' id='p_gender' value='0' />
								<div class='option' data-param='p_gender' data-value='1'><div></div></div> Мужской&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
								<div class='option' data-param='p_gender' data-value='2'><div></div></div> Женский
							</div>
						</div>
						<div class='input input_low'>
							<div class='title'>Электронная почта <span class='red'>*</span></div>
							<input type=text name='p_login' id='p_login' value=\"\" onblur=\"if( $( this ).val() ) { $( this ).addClass( 'withdata' ); } else { $( this ).removeClass( 'withdata' ); }\" onkeyup=\"$( this ).parent().find( '.error' ).fadeOut(200);\" />
							<div class='error'></div>
						</div>
						<div class='input input_low'>
							<div class='title'>Пароль <span class='red'>*</span></div>
							<input type=password name='p_pass' id='p_pass' value=\"\" onblur=\"if( $( this ).val() ) { $( this ).addClass( 'withdata' ); } else { $( this ).removeClass( 'withdata' ); }\" onkeyup=\"$( this ).parent().find( '.error' ).fadeOut(200);\" />
							<div class='error'></div>
						</div>
						<div class='input input_low'>
							<div class='title'>Имя <span class='red'>*</span></div>
							<input type=text name='p_name' id='p_name' value=\"\" onblur=\"if( $( this ).val() ) { $( this ).addClass( 'withdata' ); } else { $( this ).removeClass( 'withdata' ); }\" onkeyup=\"$( this ).parent().find( '.error' ).fadeOut(200);\" />
							<div class='error'></div>
						</div>
						<div class='input input_low'>
							<div class='title'>Фамилия <span class='red'>*</span></div>
							<input type=text name='p_surname' id='p_surname' value=\"\" onblur=\"if( $( this ).val() ) { $( this ).addClass( 'withdata' ); } else { $( this ).removeClass( 'withdata' ); }\" onkeyup=\"$( this ).parent().find( '.error' ).fadeOut(200);\" />
							<div class='error'></div>
						</div>
						<div class='input input_low'>
							<div class='title'>Телефон <span class='red'>*</span></div>
							<input type=text name='p_phone' id='p_phone' value=\"\" onblur=\"if( $( this ).val() ) { $( this ).addClass( 'withdata' ); } else { $( this ).removeClass( 'withdata' ); }\" placeholder='+7 ___ ___ __ __' data-inputmask=\"'mask': '+7 999 999 99 99', 'clearMaskOnLostFocus': true, 'clearIncomplete': true, 'showMaskOnHover': true, 'showMaskOnFocus': true\" onkeyup=\"$( this ).parent().find( '.error' ).fadeOut(200);\" />
							<div class='error'></div>
						</div>
						<div class='input input_low'>
							<div class='title'>Дата рождения</div>
							<input type=text name='p_bdate' id='p_bdate' value=\"\" onblur=\"if( $( this ).val() ) { $( this ).addClass( 'withdata' ); } else { $( this ).removeClass( 'withdata' ); }\" placeholder='ДД/ММ/ГГГГ' data-inputmask=\"'mask': '99/99/9999', 'clearMaskOnLostFocus': true, 'clearIncomplete': false, 'showMaskOnHover': true, 'showMaskOnFocus': true\" />
						</div>
						<div class='header'>Адрес доставки</div>
						<div class='input input_nonemargintop'>
							<div class='title'>Город <span class='red'>*</span></div>
							<input type=text name='p_city' id='p_city' value=\"\" onblur=\"if( $( this ).val() ) { $( this ).addClass( 'withdata' ); } else { $( this ).removeClass( 'withdata' ); }\" onkeyup=\"$( this ).parent().find( '.error' ).fadeOut(200);\" />
							<div class='error'></div>
						</div>
						<div class='input input_low'>
							<div class='title'>Адрес доставки <span class='red'>*</span></div>
							<input type=text name='p_adress' id='p_adress' value=\"\" onblur=\"if( $( this ).val() ) { $( this ).addClass( 'withdata' ); } else { $( this ).removeClass( 'withdata' ); }\" onkeyup=\"$( this ).parent().find( '.error' ).fadeOut(200);\" />
							<div class='error'></div>
						</div>
						<div class='input input_textarea input_low'>
							<div class='title'>Дополнительная информация</div>
							<textarea type=text name='p_comments' id='p_comments' onblur=\"if( $( this ).val() ) { $( this ).addClass( 'withdata' ); } else { $( this ).removeClass( 'withdata' ); }\"></textarea>
						</div>
						<div class='input input_low' style='height: auto;'><input type=hidden name='p_ras' id='p_ras' val=0 />
							<div class='title'>&nbsp;</div>
							<div class='check'><div class='box' onclick=\"
								if( $( this ).hasClass( 'box_sel' ) ) {
									$( this ).removeClass( 'box_sel' );
									$( '#p_ras' ).val( 0 );
								} else {
									$( this ).addClass( 'box_sel' );
									$( '#p_ras' ).val( 1 );
								}
							\"><div class='s'></div></div>
								Подписаться на новости и акции сайта
							</div>
						</div>
						<div class='clearError' style=' margin-left: 185px;'><span class='red'></span></div>
					</form></div>
					<div class='all_forms invisible' id='loginform'>
						
					  <form action=\"".$p."?step=101\" method=POST id='".$form_id."'><input type=hidden name='fine_fitting' id='fine_fitting' value='0' />
						<div class='input input_low'>
							<div class='title'>Email</div>
							<input type=text name='u_login_order' id='u_login_order' value=\"".( $main->users->displayedLogin ? trim( $main->users->displayedLogin ) : "" )."\" onkeypress=\"
								var code = processKeyPress( event );
								if( code == 13 ) {
									$( '#fine_fitting' ).val( '1' );
									$( '#".$form_id."' ).submit();
								}
							\" onblur=\"if( $( this ).val() ) { $( this ).addClass( 'withdata' ); } else { $( this ).removeClass( 'withdata' ); }\" />
						</div>
						<div class='input input_password'>
							<div class='title'>Пароль</div>
							<input type=password name='u_pass_order' id='u_pass_order' value=\"".( $main->users->displayedPassword ? trim( $main->users->displayedPassword ) : "" )."\" onkeypress=\"
								var code = processKeyPress( event );
								if( code == 13 ) {
									$( '#fine_fitting' ).val( '1' );
									$( '#".$form_id."' ).submit();
								}
							\" onblur=\"if( $( this ).val() ) { $( this ).addClass( 'withdata' ); } else { $( this ).removeClass( 'withdata' ); }\" />
							<div class='showing' onclick=\"
								if( $( this ).parent().find( 'input' ).attr( 'type' ) == 'password' ) {
									$( this ).addClass( 'showing_selected' );
									$( this ).parent().find( 'input' ).attr( 'type', 'text' );
								} else {
									$( this ).removeClass( 'showing_selected' );
									$( this ).parent().find( 'input' ).attr( 'type', 'password' );
								}								
							\"></div>
						</div>
					  </form>
						<div class='button' style='margin-left: 185px; width: auto; padding-left: 40px; padding-right: 40px;' onclick=\"
							$( '#fine_fitting' ).val( '1' );
							$( '#".$form_id."' ).submit();
						\">Войти</div>
						<p style='margin-bottom: 0px; margin-left: 185px;'><a href='#' onclick=\"processSimpleAsyncReqForModule( 'profile', '3', '&p=".$p."', 'afterProfiilShow( data );' ); return false;\">Забыл пароль</a></p>
						<div class='clearError center'>".( $main->users->auth_error ? "<span class='red'>".$main->users->auth_error."</span>" : "" )."</div>
					
					</div>
					<div class='clear'></div>
					<div class='block_button'>
						<div class='button' onclick=\"
							if( checkFormOnline() ) {
								$( '#fine_regfitting' ).val( '2' ); 
								$( '#".$form_id_reg."' ).submit();
							}
						\">
							Продолжить
						</div>
					</div>
					<div class='clear'></div>
					
					<script>
						setTimeout(function()
						{
							$( '.radios .option' ).click(function(){
								$( '.radios .option' ).removeClass( 'option_selected' );
								$( this ).addClass( 'option_selected' );
								$( '#' + $( this ).attr( 'data-param' ) ).val( $( this ).attr( 'data-value' ) );
							});
							$( '.block_menu span' ).click(function(){
								if( $( this ).hasClass( 'link' ) ) {
									$( '.block_menu span' ).each(function(){
										if( !$( this ).hasClass( 'link' ) ) {
											$( this ).addClass( 'link' );
										}
									});
									$( this ).removeClass( 'link' );
									if( $( this ).attr( 'data-id' ) == '1' || $( this ).attr( 'data-id' ) == 1 ) {
										$( '#regform' ).show();
										$( '#loginform' ).hide();
										$( '.block_button' ).show();
									} else {
										$( '#regform' ).hide();
										$( '#loginform' ).show();
										$( '.block_button' ).hide();
									}
								}
							});
						},300);
						
						function checkFormOnline()
						{
							if( !$( '#p_login' ).attr( 'done' ) ) {
								if( !$( '#p_login' ).val() ) {
									$( '#p_login' ).parent().find( '.error' ).width( $( '#p_login' ).parent().width() - $( '#p_login' ).parent().find( '.title' ).width() - $( '#p_login' ).width() - 50 ).html( 'Укажите email' ).show();
									$( '#p_login' ).focus();
									return false;
								}
								if( $.ajax({ type: 'POST', url: '".$mysql->settings['local_folder']."checkdata', async: false, data: 'ext=1&type=1&v=' + $( '#p_login' ).val() }).responseText != 1 ) {
									$( '#p_login' ).parent().find( '.error' ).width( $( '#p_login' ).parent().width() - $( '#p_login' ).parent().find( '.title' ).width() - $( '#p_login' ).width() - 50 ).html( 'Неверный Email' ).show();
									$( '#p_login' ).focus();
									return false;
								}
								if( $.ajax({ type: 'POST', url: '".$mysql->settings['local_folder']."checkdata', async: false, data: 'ext=2&type=2&v=' + $( '#p_login' ).val() }).responseText != 1 ) {
									$( '#p_login' ).parent().find( '.error' ).width( $( '#p_login' ).parent().width() - $( '#p_login' ).parent().find( '.title' ).width() - $( '#p_login' ).width() - 50 ).html( 'Указанный Email уже используется' ).show();
									$( '#p_login' ).focus();
									return false;
								}
								$( '#p_login' ).attr( 'done', 1 );
							}
							
							if( !$( '#p_pass' ).attr( 'done' ) ) {
								if( !$( '#p_pass' ).val() ) {
									$( '#p_pass' ).parent().find( '.error' ).width( $( '#p_pass' ).parent().width() - $( '#p_pass' ).parent().find( '.title' ).width() - $( '#p_pass' ).width() - 50 ).html( 'Укажите пароль' ).show();
									$( '#p_pass' ).focus();
									return false;
								}
								$( '#p_pass' ).attr( 'done', 1 );
							}
							
							if( !$( '#p_name' ).attr( 'done' ) ) {
								if( !$( '#p_name' ).val() ) {
									$( '#p_name' ).parent().find( '.error' ).width( $( '#p_name' ).parent().width() - $( '#p_name' ).parent().find( '.title' ).width() - $( '#p_name' ).width() - 50 ).html( 'Укажите имя' ).show();
									$( '#p_name' ).focus();
									return false;
								}
								$( '#p_name' ).attr( 'done', 1 );
							}
							
							if( !$( '#p_surname' ).attr( 'done' ) ) {
								if( !$( '#p_surname' ).val() ) {
									$( '#p_surname' ).parent().find( '.error' ).width( $( '#p_surname' ).parent().width() - $( '#p_surname' ).parent().find( '.title' ).width() - $( '#p_surname' ).width() - 50 ).html( 'Укажите фамилию' ).show();
									$( '#p_surname' ).focus();
									return false;
								}
								$( '#p_surname' ).attr( 'done', 1 );
							}
							
							if( !$( '#p_phone' ).attr( 'done' ) ) {
								if( !$( '#p_phone' ).val() ) {
									$( '#p_phone' ).parent().find( '.error' ).width( $( '#p_phone' ).parent().width() - $( '#p_phone' ).parent().find( '.title' ).width() - $( '#p_phone' ).width() - 50 ).html( 'Укажите телефон' ).show();
									$( '#p_phone' ).focus();
									return false;
								}
								$( '#p_phone' ).attr( 'done', 1 );
							}
							
							if( !$( '#p_city' ).attr( 'done' ) ) {
								if( !$( '#p_city' ).val() ) {
									$( '#p_city' ).parent().find( '.error' ).width( $( '#p_city' ).parent().width() - $( '#p_city' ).parent().find( '.title' ).width() - $( '#p_city' ).width() - 50 ).html( 'Укажите город' ).show();
									$( '#p_city' ).focus();
									return false;
								}
								$( '#p_city' ).attr( 'done', 1 );
							}
							
							if( !$( '#p_adress' ).attr( 'done' ) ) {
								if( !$( '#p_adress' ).val() ) {
									$( '#p_adress' ).parent().find( '.error' ).width( $( '#p_adress' ).parent().width() - $( '#p_adress' ).parent().find( '.title' ).width() - $( '#p_adress' ).width() - 50 ).html( 'Укажите адрес' ).show();
									$( '#p_adress' ).focus();
									return false;
								}
								$( '#p_adress' ).attr( 'done', 1 );
							}
							
							return true;
						}
					</script>
					";
				}
				
				return $t;
		}
	}
	
	function processHeaderBlock()
	{
		global $mysql, $query, $main;
		
		if( $this->goingLogin && $main->users->auth ) {
			$mysql->mu( "DELETE FROM `".$mysql->t_prefix."primerka` WHERE `session`='".$main->users->sid."'" );
			$a = $mysql->mqm( "SELECT * FROM `".$mysql->t_prefix."primerka` WHERE `session`='".$this->goingLogin."'" );
			while( $r = @mysql_fetch_assoc( $a ) ) {
				$mysql->mu( "UPDATE `".$mysql->t_prefix."primerka` SET `user`=".$main->users->userArray['id'].", `session`='".$main->users->sid."' WHERE `id`=".$r['id'] );
			}
		}
	}
	
	function processOrder()
	{
		global $lang, $main, $mysql, $query, $utils;
		
		$catalog = $main->modules->gmi( "catalog" );
		$lenses = $main->modules->gmi( "lenses" );
		
		$userId = 0;
		if( $main->users->auth ) {
			$userId = $main->users->userArray['id'];
		} else {
			$userId = $main->users->getSessionOption( 1 );
			if( !$userId )
				return "";
		}
		$profile = $main->users->getUserProfile( $userId );
		$cd = time();
		
		$goods = '<ol>';
		$array = array();
		$a = $mysql->mqm( "SELECT * FROM `".$mysql->t_prefix."primerka` WHERE `session`='".$main->users->sid."' ORDER BY `date` DESC" );
		while( $r = @mysql_fetch_assoc( $a ) ) {
			array_push( $array, $r['good'] );
			$good = $main->modules->gmi( "catalog" )->getItem( $r['good'] );
			if( !$good )
				continue;
			$props = $main->properties->getPropertiesOfGood( $r['good'] );
			if( $good['root'] ) {
				$rdata = $main->modules->gmi( "catalog" )->getItem( $good['root'] );
				$good['name'] = $rdata['name'];
			}
			$preview = $this->getElementByData( $props, "prop_id", 14 );
			$preview = $preview ? $preview['value'] : '';
			$link = "https://".$_SERVER['HTTP_HOST'].$mysql->settings['local_folder']."catalog/show".$good['id']."/".strtolower( $utils->translitIt( $good['name'] ) )."_".strtolower( $utils->translitIt( $good['article'] ) ).".html";
			
			$color = $this->getElementByData( $props, "prop_id", 1 );
			$colorName = $color && is_numeric( $color['value'] ) ? $main->listings->getListingElementValueById( 3, $color['value'], true ) : $color['value'];
		
			$goods .= "<li>".( $preview ? "<a href='".$link."' target=_BLANK><img src='https://".$_SERVER['HTTP_HOST'].$mysql->settings['local_folder']."files/upload/goods/".$preview."' alt='Оправа' width=70 style='position: relative; top: 5px;' /></a>&nbsp;" : '' )."<a href='".$link."' target=_BLANK>".$good['name']."</a>".( $colorName ? ", цвет ".$colorName : "" )."</li>";
		}
		$goods .= '</ol>';
		
		$lastCD = $main->users->getSessionOption( 6 );
		
		if( $lastCD ) {
			if( $mysql->mq( "SELECT `id` FROM `".$mysql->t_prefix."fitting` WHERE `date`=".$lastCD ) )
				return "<script>urlmove('/');</script>";
		}
		
		$mysql->mu( "INSERT INTO `".$mysql->t_prefix."fitting` VALUES(
		
			0,
			".$userId.",
			'".$main->users->sid."',
			".$cd.",
			'".json_encode( $array )."',
			".$cd.",
			''
		
		);" );
		
		$r = $mysql->mq( "SELECT `id` FROM `".$mysql->t_prefix."fitting` WHERE `date`=".$cd." ORDER BY `id` DESC" );
		
		if( !$r )
			return "";
			
		$mysql->mu( "DELETE FROM `".$mysql->t_prefix."primerka` WHERE `session`='".$main->users->sid."'" );
			
		$mail_agent = $main->modules->gmi( "mail_agent" );
			
		$newOrderId = $r['id'];
			
		$localAdress = "http://".$_SERVER['HTTP_HOST'].$mysql->settings['local_folder'];
		
		$user = $main->users->getUserById( $userId );

		$template = $main->templates->psl( $lang->gp( 137 ), true );
		$template = str_replace( "[order_number]", "<b>".$newOrderId."</b>", $template );
		$template = str_replace( "[date]", $utils->getFullDate( $cd ), $template );
		$template = str_replace( "[goods]", $goods, $template );
					
		$mail_agent->sendMessage( $user['ulogin'], $this->getParam( "from_to_send_order" ), $main->templates->psl( $lang->gp( 138 ) ), $template );
		
		$template = $main->templates->psl( $lang->gp( 139 ), true );
		$template = str_replace( "[order_number]", "<b>".$newOrderId."</b>", $template );
		$template = str_replace( "[date]", $utils->getFullDate( $cd ), $template );
					
		$mail_agent->sendMessage( $this->getParam( "where_send_new_order" ), $this->getParam( "from_to_send_order" ), $main->templates->psl( $lang->gp( 140 ) ), $template );
		
		$template = $main->templates->psl( $lang->gp( 141 ), true );
		$template = str_replace( "[order_number]", "<b>".$newOrderId."</b>", $template );
		
		$main->users->saveSessionOption( 6, $cd );
		
		return "
		<div class='sopli'><div class='all_lines'>
			<a href='".$mysql->settings['local_folder']."'>".$lang->gp( 8 )."</a><span>></span>Оформление примерки на дому
		</div></div>
			
		<div class='catalog catalog_nomargin catalog_marginbottom'>
			<div class='all_lines'>
				".$template."
			</div>
		</div>
		";
	}
        
        
        function getAdminScreen( &$selectedElement, $path )
	{
		global $mysql, $query, $utils, $admin, $lang, $main;

		$show = $query->gp( "show" );
		$month = $query->gp( "month" );
		$catalog = $main->modules->gmi( "catalog" );
		$filter = $query->gp( "filter" );
		
		$tt = explode( "/", date( "d/m/Y" ) );
		$currentDay = intval( $tt[0] );
		$currentMonth = intval( $tt[1] );
		$currentYear = intval( $tt[2] );
		$monthes = "";
		for( $i = 1; $i <= 12; $i++ ) {
			$needDay = 1;
			$needMonth = $currentMonth - $i > 0 ? $currentMonth - $i : 13 - $i;
			$needYear = $currentMonth - $i <= 0 ? $currentYear - 1 : $currentYear;
			$d = mktime( "00", "00", "00", $needMonth, $needDay, $needYear );
			$monthes .= "<option value=".$d.( $month == $d ? " selected" : "" ).">Заказы ".$utils->getFullDate( $d, false, true )."</option>";
		}
                
                if( $query->gp( "delete" ) && $query->gp( "fitid" ) ) {
			
			$mysql->mu( "DELETE FROM `".$mysql->t_prefix."fitting` WHERE `id`=".$query->gp( "fitid" ) );
			
			$query->setProperty( "fitid", 0 );
			
		}
		
		$selectedElement = $admin->userLevel == 1 ? 1 : 0;
		
		$t = "
			<h1>Список заказов на примерку <small>(сверху последние)</small></h1>
			
			<form method=POST action='".LOCAL_FOLDER."admin/".$path."' onsubmit=\"if( $( '#show' ).val() != '0' ) this.action = '".LOCAL_FOLDER."admin/".$path."/show' + $( '#show' ).val(); return true;\" id='orders_form'>
				<input type=hidden name='fit_id' id='fit_id' value=0 />
				<select name='month' id='month' onchange=\"$( '#orders_form' ).submit();\">
					<option value=10000".( $month == 10000 ? " selected" : "" ).">Показывать все заказы</option>
					<option value=0".( !$month ? " selected" : "" ).">Заказы текущего месяца</option>
					".$monthes."
				</select>
			</form>
			
			<table cellspacing=0 cellpadding=0 class='list_table' style='width: 100%;'>
				<tr class='list_table_header'>
					<td width=40 nowrap>
						№
					</td>
					<td width=55% style='text-align: left;'>
						Дата заказа и оправы
					</td>
					<td width=30% style='text-align: left;'>
						Контактное лицо, адрес, телефон и почта
					</td>
					<td width=110>
						Опции
					</td>
				</tr>
		";
		
		$wdate = "";
		if( !$month ) {
			$d = mktime( "00", "00", "00", $currentMonth, 1, $currentYear );
			$wdate = "`date`>=".$d;
		} else if( $month == 10000 ) {
			
		} else {
			$tt = explode( "/", date( "d/m/Y", $month ) );
			$selectedMonth = intval( $tt[1] );
			$selectedYear = intval( $tt[2] );
			$needDay = 1;
			$needMonth = $selectedMonth == 12 ? 1 : $selectedMonth + 1;
			$needYear = $needMonth == 1 ? $selectedYear + 1 : $selectedYear;
			$d = mktime( "00", "00", "00", $needMonth, $needDay, $needYear );
			$wdate = "`date`>=".$month." AND `date`<=".$d;
		}
		
		$where = $wdate;
		
		$localAdress = "https://".$_SERVER['HTTP_HOST'].$mysql->settings['local_folder'];
		$ColorsList = $main->listings->getListingElementsArraySpec( 2, "`order` DESC, `id` ASC", "", -1, true );
		$counter = 0;
		if( !$where ) 
			$where = "1";
                
		$a = $mysql->mqm( "SELECT * FROM `".$mysql->t_prefix."fitting` WHERE ".$where." ORDER BY `date` DESC" );
		while( $r = @mysql_fetch_assoc( $a ) ) {
                    
                        $goods = json_decode( $r['info'] );
                        $goodss = "";
                        foreach( $goods as $v ) {
                            $goodss .= ( $goodss ? " OR " : "" )."`id`=".$v;
                        }
                        $goodsStr = "";
                        $op = $mysql->mqm( "SELECT * FROM ".$this->gl_dbase_string."`".$mysql->t_prefix."tovar` WHERE ".$goodss );
                        while( $oprava = @mysql_fetch_assoc( $op ) ) {
                            $opravaRoot = $oprava['root'] ? $catalog->getItem( $oprava['root'] ) : 0;
                            if( $opravaRoot )
                                $oprava['name'] = $opravaRoot['name'];
		
                            $oprava['properties'] = $main->properties->getPropertiesOfGood( $oprava['id'] );
                            $preview = $this->getElementByData( $oprava['properties'], "prop_id", 14 );
                            $preview = $preview ? $preview['value'] : '';
                            $color = $this->getElementByData( $oprava['properties'], "prop_id", 1 );
                            $colorName = $color && is_numeric( $color['value'] ) ? $main->listings->getListingElementValueById( 3, $color['value'], true ) : $color['value'];
                            
                            $goodsStr .= "
				<tr>
					<td align=left valign=middle style='text-align: left; line-height: 60px;'>
                                            <a href=\"".$localAdress."catalog/show".$oprava['id']."\" target=_BLANK>
                                                ".( $preview ? "<img src=\"".$localAdress."files/upload/goods/thumbs/".$preview."\" style='max-height: 60px; float: left; margin-right: 13px;' />" : "" ).$oprava['name'].", ".$colorName."
                                            </a>
					</td>
					<td iElem_no_padding' align=center valign=middle>
						".$utils->digitsToRazryadi( $oprava['price'] )."
					</td>
				</tr>
                            ";
                        }
			
			$userArray = $main->users->getUserById( $r['user'] );
			$profile = $main->users->getUserProfile( $r['user'] );
			
			$t .= "
				<tr class='list_table_element'>
					<td valign=top>
						".$r['id']."
					</td>
					<td style='text-align: left;' valign=top>
						<label style='font-weight: bold; color: #ff0000;'>".$utils->getFullDate( $r['date'], true )."</label>
						<table cellspacing=0 cellpadding=0 border=1 width=100% style='margin-top: 5px; background-color: #fff;' id='goodslisting_".$r['id']."'>
							<tr>
								<td align=center valign=middle width=75%>
									<b>Оправы</b>
								</td>
								<td align=center valign=middle width=25%>
									<b>Стоимость (руб.)</b>
								</td>
							</tr>
							".$goodsStr."
						</table>
					</td>
					<td style='text-align: left;' valign=top>
						<div style='margin-bottom: 5px;'>Заказчик: <b>".$profile['name']." ".$profile['suname']."</b></div>
						<div style='margin-bottom: 5px;'>Адрес: <b>".$profile['city'].", ".$profile['adress']."</b></div>
						<div style='margin-bottom: 5px;'>Телефон: <b>".$profile['phone']."</b></div>
						<div style='margin-bottom: 5px;'>E-Mail: <b>".$userArray['ulogin']."</b></div>
						<div style='margin-bottom: 5px;'>Комментарий: <b>".( $profile['comments'] ? $profile['comments'] : "Не указан" )."</b></div>
					</td>
					<td valign=top nowrap>						
						<div style='margin-bottom: 5px;'><a href=\"".LOCAL_FOLDER."admin/".$path.( $show ? "/show".$show : "" ).( $month ? "/month".$month : "" ).( $filter ? "/filter".$filter : "" )."/delete/fitid".$r['id']."\" onclick=\"if( !confirm( 'Вы уверены?' ) ) return false; return true;\">Удалить</a></div>
					</td>
				</tr>
			";
			$counter++;
		}
		
                $t .= "
			<tr class='list_table_footer'>
					<td colspan=4>
						Всего заказов в списке: ".$counter."
					</td>
				</tr>
		</table>
		<style>
			.allnewitemsdivs {  }
			.fromelem_div
			{ 
				padding-top: 5px;
				padding-bottom: 5px;
				cursor: pointer;
			}
			.fromelem_div:hover
			{ 
				background-color: #ddd;
			}
		</style>
		";
		
		return $t;
	}
}

?>
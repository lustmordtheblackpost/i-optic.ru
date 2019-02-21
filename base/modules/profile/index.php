<?php

if( !defined( "in_ochki" ) && !defined( "in_ochki_admin" ) ) die( "You can't access this file directly" );

//
// Очень важно, чтобы объявление класса, у примеру "class moduleabout extends RootModule" было именно такое, какое есть сейчас. 
// Меняется только слово about в случае указанного примера. Также четко следите за регистром.
// Касаемо регистра. В модулях должны совпадать: название каталога, название в классе и название в БД (к примеру везде about или Projects или SuBwAy и тд.)
// 

class moduleprofile extends RootModule
{
	var $dbinfo = null;
	var $curFilePath = "";
	
	var $options = array();
	var $user = null;
	var $save_error = "";
	var $error = 0;
	var $error_text = '';
	var $output = 0;
	var $output_text = '';
	
	function init( $dbinfo )
	{
		$this->dbinfo = $dbinfo;
		$this->curFilePath = $this->getRootFilePath( __FILE__ );
	}
	
	function getMainmenuLink()
	{
		global $main, $mysql, $lang;
		
		return $main->users->auth ? 
			"
			<a class='right enter' href=\"".$mysql->settings['local_folder']."profile\" class='right'>".$this->getName()."</a>"
				: 
			"<a class='right enter' href=\"#\" class='right' onclick=\"if( isMobile ) { urlmove('".$mysql->settings['local_folder']."profile'); return; } $( '.fixed_profile' ).fadeIn( 400 ); $( '.fixed_profile .window' ).css( 'margin-top', $( '.fixed_profile .window' ).height() / 2 * -1 ); return false;\"><img src='/images/chelp.png' /><span>".$lang->gp( 1, true )."</span></a>";
	}
	
	function getMobileMenuLink()
	{
		global $main, $mysql, $lang;
		
		return $main->users->auth ? 
			"
			<a class='mmenu' href=\"".$mysql->settings['local_folder']."logout\">".$this->getName()."</a>"
				: 
			"<a class='mmenu' href=\"#\" onclick=\"if( isMobile ) { urlmove('".$mysql->settings['local_folder']."profile'); return; } $( '.fixed_profile' ).fadeIn( 400 ); $( '.fixed_profile .window' ).css( 'margin-top', $( '.fixed_profile .window' ).height() / 2 * -1 ); return false;\">".$lang->gp( 1, true )."</a>";
	}
	
	function getPluginBlock()
	{
		global $mysql, $query, $lang, $main;
		
		$data = '';
		if( $this->error == 2 || $this->output == 2 )
			$data = $this->getRegister();
		else if( $this->error == 3 || $this->output == 3 )
			$data = $this->getForget();
		else
			$data = $this->getLogin();
		
		return "
		<div class='fixed_profile'>
			<div class='window'>
				<div class='closer' onclick=\"$( '.fixed_profile' ).fadeOut( 400, function() { if( old_fixed_profile_data != '' ) $( '.fixed_profile' ).find( '.inner' ).html( old_fixed_profile_data ); } );\"><img src='/images/smx.png' alt='closer' /></div>
				<div class='inner'>
					".$data."
				</div>
			</div>
		</div>
		<div class='fixed_alert'>
			<div class='window'>
				<div class='closer' onclick=\"$( '.fixed_alert' ).fadeOut( 400 );\"><img src='/images/smx.png' alt='closer' /></div>
				<div class='inner'>
					<p>".( $main->users->showActivated ? "<h3>Аккаунт активирован успешно!</h3>Можете продолжать работу.<br/><a href='#' onclick=\"$( '.fixed_alert' ).fadeOut( 400 ); return false;\">Закрыть окно</a>" : "" )."</p>
				</div>
			</div>
		</div>
		<script>
			var old_fixed_profile_data = '';
			function afterProfiil( data )
			{
				old_fixed_profile_data = $( '.fixed_profile' ).find( '.inner' ).html();
				$( '.fixed_profile' ).find( '.inner' ).html( data );
			}
			
			function afterProfiilShow( data )
			{
				$( '.fixed_profile' ).find( '.inner' ).html( data );
				$( '.fixed_profile' ).fadeIn( 400 );
			}
			
			".(
				( ( $this->error && $this->error < 10 ) || ( $this->output && $this->output != 2 ) || $main->users->auth_error ) && !$main->users->goingFromOrder ? "
					$(window).load(function()
					{
						$( '.fixed_profile' ).fadeIn( 400 );
					});
				" : ""
			)."
			".(
				$main->users->showActivated ? "
					$(window).load(function()
					{
						$( '.fixed_alert' ).fadeIn( 400 );
					});
				" : ""
			)."
		</script>
		";
	}
	
	function parseExternalRequest()
	{
		global $query, $main, $utils, $lang, $mysql;
		
		$type = $query->gp( "localtype" );

		switch( $type ) {
			case 1:
				return $this->getLogin();
			case 2:
				return $this->getRegister();
			case 3:
				return $this->getForget();
			case 4: // Сохранить данные профиля из заказа
			
				if( !$main->users->auth )
					return 0;
					
				$userid = $main->users->userArray['id'];
				$profile = $main->users->getUserProfile( $userid );
			
				$surname = $query->gp_post( "p_surname" );
				$name = $query->gp_post( "p_name" );
				$phone = $query->gp_post( "p_phone" );
				$index = $query->gp_post( "p_index" );
				$city = $query->gp_post( "p_city" );
				$adress = $query->gp_post( "p_adress" );
				$comments = $query->gp_post( "p_comments" );
				
				$change = "";
		
				if( isset( $profile['suname'] ) && strcmp( $profile['suname'], $surname ) )
					$change .= ( $change ? "%" : "" )."surname:".$profile['suname'].":".$surname;
		
				if( isset( $profile['name'] ) && strcmp( $profile['name'], $name ) )
					$change .= ( $change ? "%" : "" )."name:".$profile['name'].":".$name;
		
				if( isset( $profile['phone'] ) && strcmp( $profile['phone'], $phone ) )
					$change .= ( $change ? "%" : "" )."phone:".$profile['phone'].":".$phone;
			
				if( isset( $profile['index'] ) && strcmp( $profile['index'], $index ) )
					$change .= ( $change ? "%" : "" )."index:".$profile['index'].":".$index;
			
				if( isset( $profile['city'] ) && strcmp( $profile['city'], $city ) )
					$change .= ( $change ? "%" : "" )."city:".$profile['city'].":".$city;
		
				if( isset( $profile['adress'] ) && strcmp( $profile['adress'], $adress ) )
					$change .= ( $change ? "%" : "" )."adress:".$profile['adress'].":".$adress;
			
				if( isset( $profile['comments'] ) && strcmp( $profile['comments'], $comments ) )
					$change .= ( $change ? "%" : "" )."comments:".$profile['comments'].":".$comments;
			
				$main->users->updateDefaultProfileField( $userid, 'name', $name." ".$surname );
		
				$main->users->updateProfileField( $userid, 'name', $name );
				$main->users->updateProfileField( $userid, 'suname', $surname );
				$main->users->updateProfileField( $userid, 'phone', $phone );
				$main->users->updateProfileField( $userid, 'index', $index );
				$main->users->updateProfileField( $userid, 'city', $city );
				$main->users->updateProfileField( $userid, 'adress', $adress );
				$main->users->updateProfileField( $userid, 'comments', $comments );
		
				if( $change )
					$main->users->insertNewAction( $main->users->user_actions['information_change'], $userid, $main->users->sid, $change );
					
				return 1;
			default:
				return $this->getLogin();
		}
	}
	
	function getLogin()
	{
		global $mysql, $query, $lang, $main;
		
		$form_id = md5( time().mt_rand( 1, 500 ) );
		
		$p = $query->gp( "path" );
		$p = $p ? $p : $_SERVER['REQUEST_URI'];
		
		return "
					<div class='menu'>
						<div class='elem elem_selected'>Вход</div>
						<div class='elem' onclick=\"if( isMobile ) { urlmove( '/profile/reg' ); return; } processSimpleAsyncReqForModule( '".$this->dbinfo['local']."', '2', '&path=".$p."', 'afterProfiil( data );' );\">Регистрация</div>
						<div class='clear'></div>
					</div>
					<h3>".$lang->gp( 111 )."</h3>
					<form action=\"".$p."\" method=POST id='".$form_id."'><input type=hidden name='fine' id='fine' value='0' />
						<div class='input'>
							<div class='title'>Email</div>
							<input type=text name='u_login' id='u_login' value=\"".( $main->users->displayedLogin ? trim( $main->users->displayedLogin ) : "" )."\" onkeypress=\"
								var code = processKeyPress( event );
								if( code == 13 ) {
									$( '#fine' ).val( '1' );
									$( '#".$form_id."' ).submit();
								}
							\" onblur=\"if( $( this ).val() ) { $( this ).addClass( 'withdata' ); } else { $( this ).removeClass( 'withdata' ); }\" />
						</div>
						<div class='input input_password'>
							<div class='title'>Пароль</div>
							<input type=password name='u_pass' id='u_pass' value=\"".( $main->users->displayedPassword ? trim( $main->users->displayedPassword ) : "" )."\" onkeypress=\"
								var code = processKeyPress( event );
								if( code == 13 ) {
									$( '#fine' ).val( '1' );
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
					<div class='button' onclick=\"$( '#fine' ).val( '1' ); $( '#".$form_id."' ).submit();\">Войти</div>
                                        <script type=\"text/javascript\" src=\"https://vk.com/js/api/openapi.js?158\"></script>
<script type=\"text/javascript\">
  VK.init({apiId: 6655335});
</script>

<!-- VK Widget -->
<div id=\"vk_auth\"></div>
<script type=\"text/javascript\">
  VK.Widgets.Auth(\"vk_auth\", {\"onAuth\":\"function(data) {alert('user '+data['uid']+' authorized');}\"});
</script>
					<div class='clearError center'>".( $main->users->auth_error ? "<span class='red'>".$main->users->auth_error."</span>" : "" )."</div>
		";
	}
	
	function getRegister()
	{
		global $mysql, $query, $lang, $main;
		
		$form_id = md5( time().mt_rand( 1, 500 ) );
		
		$p = $query->gp( "path" );
		$p = $p ? $p : $_SERVER['REQUEST_URI'];
		
		return "
					<div class='menu'>
						<div class='elem' onclick=\"if( isMobile ) { urlmove( '/profile' ); return; } processSimpleAsyncReqForModule( '".$this->dbinfo['local']."', '1', '&path=".$p."', 'afterProfiil( data );' );\">Вход</div>
						<div class='elem elem_selected'>Регистрация</div>
						<div class='clear'></div>
					</div>
					".( $this->output == 2 ? "<h3>Спасибо!</h3><div class='success'>".$this->output_text."</div>" : "
					<h3>Регистрация</h3><p>".$lang->gp( 110 )."</p>
					<form action=\"".$p."\" method=POST id='".$form_id."'><input type=hidden name='fine' id='fine' value='0' /><input type=hidden name='ras' id='ras' value='0' />
						<div class='input input_low'>
							<div class='title'>Имя</div>
							<input type=text name='r_name' id='r_name' value=\"\" onblur=\"if( $( this ).val() ) { $( this ).addClass( 'withdata' ); } else { $( this ).removeClass( 'withdata' ); }\" />
						</div>
						<div class='input input_low'>
							<div class='title'>Фамилия</div>
							<input type=text name='r_surname' id='r_surname' value=\"\" onblur=\"if( $( this ).val() ) { $( this ).addClass( 'withdata' ); } else { $( this ).removeClass( 'withdata' ); }\" />
						</div>
						<div class='input input_low'>
							<div class='title'>Email</div>
							<input type=text name='r_login' id='r_login' value=\"\" onblur=\"if( $( this ).val() ) { $( this ).addClass( 'withdata' ); } else { $( this ).removeClass( 'withdata' ); }\" />
						</div>
						<div class='input input_low'>
							<div class='title'>Пароль</div>
							<input type=password name='r_pass' id='r_pass' value=\"\" onblur=\"if( $( this ).val() ) { $( this ).addClass( 'withdata' ); } else { $( this ).removeClass( 'withdata' ); }\" />
						</div>
						<div class='input input_low' style='height: auto;'>
							<div class='check'><div class='box' onclick=\"
								if( $( this ).hasClass( 'box_sel' ) ) {
									$( this ).removeClass( 'box_sel' );
									$( '#ras' ).val( 0 );
								} else {
									$( this ).addClass( 'box_sel' );
									$( '#ras' ).val( 1 );
								}
							\"><div class='s'></div></div>
								".$lang->gp( 108 )."
								<p>".$lang->gp( 109 )."</p>
							</div>
						</div>
					</form>
					<div class='button' onclick=\"$( '#fine' ).val( '2' ); $( '#".$form_id."' ).submit();\">Регистрация</div>" )."
					<div class='clearError center'>".( $this->error == 2 ? "<span class='red'>".$this->error_text."</span>" : "" )."</div>
		";
	}
	
	function getForget()
	{
		global $mysql, $query, $lang, $main;
		
		$form_id = md5( time().mt_rand( 1, 500 ) );
		
		$p = $query->gp( "path" );
		$p = $p ? $p : $_SERVER['REQUEST_URI'];
		
		return "
					<div class='menu'>
						<div class='elem' onclick=\"processSimpleAsyncReqForModule( '".$this->dbinfo['local']."', '1', '&path=".$p."', 'afterProfiil( data );' );\">Вход</div>
						<div class='elem' onclick=\"processSimpleAsyncReqForModule( '".$this->dbinfo['local']."', '2', '&path=".$p."', 'afterProfiil( data );' );\">Регистрация</div>
						<div class='clear'></div>
					</div>
					<h3>Забыли свой пароль?</h3>
					<p>".$main->templates->psl( $lang->gp( 112 ), true )."</p>
					".( $this->output == 3 ? "<div class='success'>".$this->output_text."</div>" : "
					<form action=\"".$p."\" method=POST id='".$form_id."'><input type=hidden name='fine' id='fine' value='0' />
						<div class='input input_high'>
							<div class='title'>Email</div>
							<input type=text name='recovery_login' id='recovery_login' value=\"".( $main->users->displayedLogin ? trim( $main->users->displayedLogin ) : "" )."\" onkeypress=\"
								var code = processKeyPress( event );
								if( code == 13 ) {
									$( '#fine' ).val( '3' );
									$( '#".$form_id."' ).submit();
								}
							\" onblur=\"if( $( this ).val() ) { $( this ).addClass( 'withdata' ); } else { $( this ).removeClass( 'withdata' ); }\" />
						</div>
					</form>
					<div class='button' onclick=\"$( '#fine' ).val( '3' ); $( '#".$form_id."' ).submit();\">Выслать пароль</div>
					<p style='margin-bottom: 0px;'><a href='#' onclick=\"processSimpleAsyncReqForModule( '".$this->dbinfo['local']."', '1', '&path=".$p."', 'afterProfiil( data );' ); return false;\">Вернуться обратно</a></p>" )."
					<div class='clearError center'>".( $this->error == 3 ? "<span class='red'>".$this->error_text."</span>" : "" )."</div>
		";
	}
	
	function processHeaderBlock()
	{
		global $mysql, $query, $lang, $main, $utils;
		
		$fine = $query->gp( "fine" );
		$fine_regorder = $query->gp( "fine_regorder" );
		if( !$fine_regorder )
			$fine_regorder = $query->gp( "fine_regfitting" );
		
		if( $fine == 2 || $fine == '2' || $fine_regorder == 2 || $fine_regorder == '2' ) { // Регистрация
			
			$r_name = $query->gp( "r_name" );
			$r_surname = $query->gp( "r_surname" );
			$r_pass = $query->gp( "r_pass" );
			$r_login = $query->gp( "r_login" );
			$ras = $query->gp( "ras" );
			
			$gender = $query->gp_post( "p_gender" );
			$phone = $query->gp_post( "p_phone" );
			$bdate = $query->gp_post( "p_bdate" );
			$index = $query->gp_post( "p_index" );
			$city = $query->gp_post( "p_city" );
			$adress = $query->gp_post( "p_adress" );
			$comments = $query->gp_post( "p_comments" );
			
			if( $fine_regorder ) {
				$r_name = $query->gp( "p_name" );
				$r_surname = $query->gp( "p_surname" );
				$r_pass = $query->gp( "p_pass" );
				$r_login = $query->gp( "p_login" );
				$ras = $query->gp( "p_ras" );
			}
			
			if( !$r_name ) {
				$this->error = $fine_regorder ? 22 : 2;
				$this->error_text = "Укажите своё имя";
				return;
			}
			
			if( !$r_surname ) {
				$this->error = $fine_regorder ? 22 : 2;
				$this->error_text = "Укажите свою фамилию";
				return;
			}
			
			if( !$r_login ) {
				$this->error = $fine_regorder ? 22 : 2;
				$this->error_text = "Укажите логин";
				return;
			}
			
			if( !$r_pass ) {
				$this->error = $fine_regorder ? 22 : 2;
				$this->error_text = "Укажите пароль";
				return;
			}
			
			if( $main->users->getUserIdByLogin( $r_login ) ) {
				$this->error = $fine_regorder ? 22 : 2;
				$this->error_text = "Указанный логин уже используется";
				return;
			}
			
			$cd = time();
			$us = array(
				'name' => $r_name." ".$r_surname,
				'ulogin' => $r_login,
				'upass' => $r_pass,
				'level' => $main->users->defaultType,
				'block' => 0,
				'date' => ( $cd + 100 )
			);
			$user = $main->users->insertNewUser( $us );
			if( $user ) {
				
				$userid = $user['id'];
				$auth = $user['auth'];
				
				$main->users->insertProfileField( $userid, "name", $r_name );
				$main->users->insertProfileField( $userid, "suname", $r_surname );
				$main->users->insertProfileField( $userid, "password", $r_pass );
				if( $ras ) {
					$main->users->insertProfileField( $userid, "ras", 1 );
					if( !$mysql->mq( "SELECT `id` FROM `".$mysql->t_prefix."sign` WHERE `mail`='".$us['ulogin']."'" ) ) {
						$mysql->mu( "INSERT INTO `".$mysql->t_prefix."sign` VALUES(0,'".$us['ulogin']."');" );
					}
				}
				
				if( $fine_regorder ) {
					if( $phone ) $main->users->insertProfileField( $userid, 'phone', $phone );
					if( $index ) $main->users->insertProfileField( $userid, 'index', $index );
					if( $city ) $main->users->insertProfileField( $userid, 'city', $city );
					if( $adress ) $main->users->insertProfileField( $userid, 'adress', $adress );
					if( $gender ) $main->users->insertProfileField( $userid, 'gender', $gender );
					if( $comments ) $main->users->insertProfileField( $userid, 'comments', $comments );
					if( $bdate ) $main->users->insertProfileField( $userid, 'bdate', $bdate );
				}
				
				$localAdress = "https://".$_SERVER['HTTP_HOST'].$mysql->settings['local_folder'];
				$link = $localAdress."?activatelogin=".$auth;
				$template = $main->templates->psl( $lang->gp( 105 ), true );
				$template = str_replace( "[user_password]", "<b>".$r_pass."</b>", str_replace( "[user_login]", "<b>".$r_login."</b>", $template ) );
				$template = str_replace( "[auth_link]", "<a href=\"".$link."\" target=_BLANK>".$link."</a>", $template );
					
				$mail_agent = $main->modules->gmi( "mail_agent" );
				$mail_agent->sendMessage( $us['ulogin'], $main->modules->gmi( "order" )->getParam( "from_to_send_reg" ), $main->templates->psl( $lang->gp( 106 ) ), $template );
				
				$this->output = 2;
				$this->output_text = $main->templates->psl( $lang->gp( 107 ), true );
				
				if( $fine_regorder ) {
					$main->users->saveSessionOption( 1, $userid );
				}
				
				return;
				
			} else {
				$this->error = 2;
				$this->error_text = $lang->gp( 113 );
				return;
			}
			
		} else if( $fine == 3 || $fine == '3' ) { // Восстановление пароля
			
			$recovery_login = $query->gp_post( "recovery_login" );
			
			if( !$recovery_login ) {
				$this->error = 3;
				$this->error_text = "Укажите логин";
				return;
			}
			
			if( ( function_exists( "filter_var" ) && !@filter_var( $recovery_login, FILTER_VALIDATE_EMAIL ) ) || !$utils->checkEmail( $recovery_login ) ) {
				$this->error = 3;
				$this->error_text = $lang->gp( 121 );
			}
			
			
			
			
			$user = $main->users->getUserById( $main->users->getUserIdByLogin( $recovery_login ), true );
		
			if( !$user ) {
				$this->error = 3;
				$this->error_text = $lang->gp( 121 );
			}
		
			$send = true;
			$last = $main->users->getSomeActionOfUser( $main->users->user_actions['password_recovery'], $user['id'] );
			if( $last && $last['add'] && strlen( $last['add'] ) == 32 ) {
				$code = $last['add'];
				if( time() - $last['date'] < $this->getParam( "recovery_emailsendrepeat_timeout" ) )
					$send = false;
				$main->users->updateActionTime( $last['id'], time() );
			} else
				$code = $main->users->generateRecoveryCode( $user );
				
			$mail_agent = $main->modules->getModuleInstanceByLocal( "mail_agent" );
			$template = $lang->gp( 122 );
		
			if( $mail_agent && $template && $send ) {
				
				$template = $main->templates->processScriptLanguage( $template, true );
				$link = "https://".$_SERVER['HTTP_HOST'].$mysql->settings['local_folder']."?recovery=".$code;
				$template = str_replace( "[recovery_link]", "<a href=\"".$link."\" target=_BLANK>".$link."</a>", $template );
					
				$subject = $main->templates->processScriptLanguage( $lang->getPh( 123 ) );
				$mail_agent->sendMessage( $user['ulogin'], $this->getParam( 'email_from' ), $subject, $template );
				
				$this->output = 3;
				$this->output_text = $main->templates->psl( $lang->gp( 125 ), true );
					
			} else if( !$mail_agent || !$template ) {
				$this->error = 3;
				$this->error_text = $lang->gp( 124 );
			}
			
			
		} else if( $query->gp( "recovery" ) && strlen( $query->gp( "recovery" ) ) == 32 ) {
			
			$code = $query->gp( "recovery" );
			$r = $mysql->mq( "SELECT * FROM `".$mysql->t_prefix."user_actions` WHERE `add`='".$code."' AND `action`=".$main->users->user_actions['password_recovery'] );
			if( $r ) {
				$user = $main->users->getUserById( $r['user'], true );
				$newpass = $main->users->makeNewPassword( $user );
				if( $newpass )
					$mysql->mu( "UPDATE `".$mysql->t_prefix."user_actions` SET `add`='".$newpass."' WHERE `id`=".$r['id'] );
				
				$mail_agent = $main->modules->getModuleInstanceByLocal( "mail_agent" );
				$template = $lang->gp( 126 );
				if( $mail_agent && $template && $newpass && $user ) {
					
					$template = $main->templates->processScriptLanguage( $template, true );
					$template = str_replace( "[newpass]", "<b>".$newpass."</b>", $template );
					
					$subject = $main->templates->processScriptLanguage( $lang->gp( 127 ) );
					$mail_agent->sendMessage( $user['ulogin'], $this->getParam( 'email_from' ), $subject, $template );
						
					$this->output = 3;
					$this->output_text = $main->templates->psl( $lang->gp( 128 ), true );
				
				}
			}
			
		}
	}
	
	function getContent()
	{
		global $mysql, $query, $lang, $main;
		
		$form_id = md5( time().mt_rand( 1, 500 ) );
		
		if( !$main->users->auth ) {
                        $main->templates->setTitle( $query->gp( "reg" ) ? "Регистрация" : "Авторизация", true );
			return "<div class='sopli'><div class='all_lines'>
			<a href='".$mysql->settings['local_folder']."'>".$lang->gp( 8 )."</a><span>></span>Авторизация
		</div></div>
		
                <div class='catalog catalog_nomargin catalog_marginbottom invisible'>
			<div class='all_lines mobilelogin'>
				".( $query->gp( "reg" ) ? $this->getRegister() : $this->getLogin() )."
			</div>
		</div>
		
		<script>
			$(window).load(function()
			{
                            if( !isMobile ) {
                                urlmove( '/' );
                                return;
                            }
                            $( '.catalog' ).show();
			});
			
			$(window).resize(function()
			{
			});
		</script>";
                }
			
		$main->templates->setTitle( $this->getName(), true );
		
		$selected = 0;
		$selectedTitle = '';
		$selectedText = "";
		foreach( $query->newGET as $qValue => $v ) {
			if( str_replace( ".html", "", strtolower( $qValue ) ) == 'edit' ) {
				$selected = 1;
				$selectedTitle = "Профиль покупателя";
				$selectedText = $this->getEditScreen();
			} else if( str_replace( ".html", "", strtolower( $qValue ) ) == 'orders' ) {
				$selected = 2;
				$selectedTitle = "Мои заказы";
                                $selectedText = $main->modules->gmi( "order" )->getOrdersScreen();
			} else if( str_replace( ".html", "", strtolower( $qValue ) ) == 'signtonews' ) {
				$selected = 3;
				$selectedTitle = "Подписка на новости";
				$selectedText = $this->getSignScreen();
			}
		}
		
		$subs = "
		<a href=\"".$mysql->settings['local_folder'].$this->dbinfo['local']."/edit.html\"".( $selected == 1 ? " class='selected'" : "" ).">Профиль</a>
		<a href=\"".$mysql->settings['local_folder'].$this->dbinfo['local']."/orders.html\"".( $selected == 2 ? " class='selected'" : "" ).">Мои заказы</a>
		<a href=\"".$mysql->settings['local_folder'].$this->dbinfo['local']."/signtonews.html\"".( $selected == 3 ? " class='selected'" : "" ).">Подписка на новости</a>
		<a href=\"".( $_SERVER['REQUEST_URI'] != '/' ? $_SERVER['REQUEST_URI']."/logout" : $_SERVER['REQUEST_URI']."logout" )."\">".$lang->gp( 2, true )."</a>
		";
		
		$t = "
		<div class='sopli'><div class='all_lines'>
			<a href='".$mysql->settings['local_folder']."'>".$lang->gp( 8 )."</a><span>></span><a href='".$mysql->settings['local_folder'].$this->dbinfo['local']."'>".$this->getName()."</a>".( $selected ? "<span>></span>".$selectedTitle : "" )."
		</div></div>
		
		<div class='catalog catalog_nomargin catalog_marginbottom'>
			<div class='all_lines'>
				<div class='filter subs'>
					".$subs."
				</div>
				<div class='main_data'><div class='md_inner'>
					".$selectedText."
				</div></div>
				<div class='clear'></div>
			</div>
		</div>
		
		<script>
			$(window).load(function()
			{
				$( '.catalog .main_data' ).width( $( '.catalog .all_lines' ).width() - $( '.catalog .filter' ).width() );
			});
			
			$(window).resize(function()
			{
				$( '.catalog .main_data' ).width( $( '.catalog .all_lines' ).width() - $( '.catalog .filter' ).width() );
			});
		</script>
		";
		
		return $t;
	}
	
	//
	// Локальные функции модуля
	//
	
	function getEditScreen()
	{
		global $query, $main, $mysql, $utils, $lang;
		
		if( $query->gp_post( "fine_profiling" ) ) {
			$this->processSavingProfile();
			$output_text = "Данные сохранены!";
		}
		
		$profile = $main->users->getUserProfile( $main->users->userArray['id'] );
		
		$form_id = md5( time() );
		
		$t = "
		<h2 class='inner_title'>Профиль покупателя</h2>
		
		<script src=\"https://rawgit.com/RobinHerbots/Inputmask/3.x/dist/jquery.inputmask.bundle.js\"></script>
  		<script src=\"https://rawgit.com/RobinHerbots/Inputmask/3.x/dist/inputmask/phone-codes/phone.js\"></script>
  		<script src=\"https://rawgit.com/RobinHerbots/Inputmask/3.x/dist/inputmask/bindings/inputmask.binding.js\"></script>

		<form action=\"".$mysql->settings['local_folder'].$this->dbinfo['local']."/edit.html\" method=POST id='".$form_id."'><input type=hidden name='fine_profiling' id='fine_profiling' value='0' />
		
			".( $output_text ? "<div class='success align_left' style='margin-left: 0px;'>".$output_text."</div>" : "" )."
		
			<div class='input input_low'>
				<div class='title'>E-Mail (Логин)</div>
				<span>".$main->users->userArray['ulogin']."</span>
			</div>
			
			<div class='input input_low'>
				<div class='title'>Пол</div>
				<div class='radios'><input type=hidden name='p_gender' id='p_gender' value='0' />
					<div class='option".( isset( $profile['gender'] ) && $profile['gender'] == 1 ? " option_selected" : '' )."' data-param='p_gender' data-value='1'><div></div></div> Мужской&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					<div class='option".( isset( $profile['gender'] ) && $profile['gender'] == 2 ? " option_selected" : '' )."' data-param='p_gender' data-value='2'><div></div></div> Женский
				</div>
			</div>
			
			<script>
				$(window).load(function()
				{
					$( '.radios .option' ).click(function(){
						$( '.radios .option' ).removeClass( 'option_selected' );
						$( this ).addClass( 'option_selected' );
						$( '#' + $( this ).attr( 'data-param' ) ).val( $( this ).attr( 'data-value' ) );
					});
				});
			</script>
			
			<div class='input input_low'>
				<div class='title'>Имя</div>
				<input type=text name='p_name' id='p_name' value=\"".( isset( $profile['name'] ) ? $profile['name']."\" class='withdata'" : '"' )." onblur=\"if( $( this ).val() ) { $( this ).addClass( 'withdata' ); } else { $( this ).removeClass( 'withdata' ); }\" />
			</div>

			<div class='input input_low'>
				<div class='title'>Фамилия</div>
				<input type=text name='p_surname' id='p_surname' value=\"".( isset( $profile['suname'] ) ? $profile['suname']."\" class='withdata'" : '"' )." onblur=\"if( $( this ).val() ) { $( this ).addClass( 'withdata' ); } else { $( this ).removeClass( 'withdata' ); }\" />
			</div>
						
			<div class='input input_low'>
				<div class='title'>Телефон</div>
				<input type=text name='p_phone' id='p_phone' value=\"".( isset( $profile['phone'] ) && $profile['phone'] ? $profile['phone']."\" class='withdata'" : '"' )." onblur=\"if( $( this ).val() ) { $( this ).addClass( 'withdata' ); } else { $( this ).removeClass( 'withdata' ); }\" placeholder='+7 ___ ___ __ __' data-inputmask=\"'mask': '+7 999 999 99 99', 'clearMaskOnLostFocus': true, 'clearIncomplete': false, 'showMaskOnHover': true, 'showMaskOnFocus': true\" />
			</div>
			
			<div class='input input_low'>
				<div class='title'>Дата рождения</div>
				<input type=text name='p_bdate' id='p_bdate' value=\"".( isset( $profile['bdate'] ) && $profile['bdate'] ? $profile['bdate']."\" class='withdata'" : '"' )." onblur=\"if( $( this ).val() ) { $( this ).addClass( 'withdata' ); } else { $( this ).removeClass( 'withdata' ); }\" placeholder='ДД/ММ/ГГГГ' data-inputmask=\"'mask': '99/99/9999', 'clearMaskOnLostFocus': true, 'clearIncomplete': false, 'showMaskOnHover': true, 'showMaskOnFocus': true\" />
			</div>
			
			<div class='input input_low'>
				<div class='title'>Индекс</div>
				<input type=text name='p_index' id='p_index' value=\"".( isset( $profile['index'] ) && $profile['index'] ? $profile['index']."\" class='withdata'" : '"' )." onblur=\"if( $( this ).val() ) { $( this ).addClass( 'withdata' ); } else { $( this ).removeClass( 'withdata' ); }\" />
			</div>
			
			<div class='input input_low'>
				<div class='title'>Город</div>
				<input type=text name='p_city' id='p_city' value=\"".( isset( $profile['city'] ) && $profile['city'] ? $profile['city']."\" class='withdata'" : '"' )." onblur=\"if( $( this ).val() ) { $( this ).addClass( 'withdata' ); } else { $( this ).removeClass( 'withdata' ); }\" />
			</div>
			
			<div class='input input_low'>
				<div class='title'>Адрес доставки</div>
				<input type=text name='p_adress' id='p_adress' value=\"".( isset( $profile['adress'] ) && $profile['adress'] ? $profile['adress']."\" class='withdata'" : '"' )." onblur=\"if( $( this ).val() ) { $( this ).addClass( 'withdata' ); } else { $( this ).removeClass( 'withdata' ); }\" />
			</div>
			
			<div class='input input_textarea input_low'>
				<div class='title'>Дополнительная информация</div>
				<textarea type=text name='p_comments' id='p_comments'".( isset( $profile['comments'] ) && $profile['comments'] ? " class='withdata'" : '' )." onblur=\"if( $( this ).val() ) { $( this ).addClass( 'withdata' ); } else { $( this ).removeClass( 'withdata' ); }\">".( isset( $profile['comments'] ) && $profile['comments'] ? $profile['comments'] : "" )."</textarea>
			</div>
			
			<div class='input input_password input_low'>
				<div class='title'>Пароль</div>
				<input type=password name='p_pass' id='p_pass' value=\"".( isset( $profile['password'] ) && $profile['password'] ? $profile['password']."\" class='withdata'" : '"' )." onblur=\"if( $( this ).val() ) { $( this ).addClass( 'withdata' ); } else { $( this ).removeClass( 'withdata' ); }\" />
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
		<div class='clear'>&nbsp;</div>
		<div class='button' onclick=\"$( '#fine_profiling' ).val( '2' ); $( '#".$form_id."' ).submit();\">Сохранить</div>
		<div class='clear'></div>
		";
		
		return $t;
	}
	
	function getSignScreen()
	{
		global $query, $main, $mysql, $utils, $lang;
		
		if( $query->gp_post( "fine_signing" ) ) {
			$this->processSavingSign();
			$output_text = "Данные сохранены!";
		}
		
		$profile = $main->users->getUserProfile( $main->users->userArray['id'] );
		
		$form_id = md5( time() );
		
		$t = "
		<h2 class='inner_title'>Подписка на новости</h2>
		
		<form action=\"".$mysql->settings['local_folder'].$this->dbinfo['local']."/signtonews.html\" method=POST id='".$form_id."'><input type=hidden name='fine_signing' id='fine_signing' value='0' />
		
			".( $output_text ? "<div class='success align_left' style='margin-left: 0px;'>".$output_text."</div>" : "" )."
		
			<div class='input input_low' style='height: auto;'><input type=hidden name='ras' id='ras' value='".( isset( $profile['ras'] ) && $profile['ras'] ? 1 : 0 )."' />
				<div class='check' style='width: 100%;'><div class='box".( isset( $profile['ras'] ) && $profile['ras'] ? " box_sel" : '' )."' onclick=\"
					if( $( this ).hasClass( 'box_sel' ) ) {
						$( this ).removeClass( 'box_sel' );
						$( '#ras' ).val( 0 );
					} else {
						$( this ).addClass( 'box_sel' );
						$( '#ras' ).val( 1 );
					}
				\"><div class='s'></div></div>
					<span>Хочу получать новости об акциях и скидках</span>
				</div>
			</div>
		</form>
		<div class='clear'>&nbsp;</div>
		<div class='button' onclick=\"$( '#fine_signing' ).val( '2' ); $( '#".$form_id."' ).submit();\">Сохранить</div>
		<div class='clear'></div>
		";
		
		return $t;
	}
	
	function processSavingProfile()
	{
		global $query, $main, $mysql, $utils, $lang;
		
		$userid = $main->users->userArray['id'];
		$profile = $main->users->getUserProfile( $userid );
		
		$gender = $query->gp_post( "p_gender" );
		$surname = $query->gp_post( "p_surname" );
		$name = $query->gp_post( "p_name" );
		$phone = $query->gp_post( "p_phone" );
		$bdate = $query->gp_post( "p_bdate" );
		$index = $query->gp_post( "p_index" );
		$city = $query->gp_post( "p_city" );
		$adress = $query->gp_post( "p_adress" );
		$comments = $query->gp_post( "p_comments" );
		
		$upass = $query->gp_post( "p_pass" );
		
		$change = "";
		
		if( isset( $profile['suname'] ) && strcmp( $profile['suname'], $surname ) )
			$change .= ( $change ? "%" : "" )."surname:".$profile['suname'].":".$surname;
		
		if( isset( $profile['name'] ) && strcmp( $profile['name'], $name ) )
			$change .= ( $change ? "%" : "" )."name:".$profile['name'].":".$name;
		
		if( isset( $profile['gender'] ) && strcmp( $profile['gender'], $gender ) )
			$change .= ( $change ? "%" : "" )."gender:".$profile['gender'].":".$gender;
		
		if( isset( $profile['phone'] ) && strcmp( $profile['phone'], $phone ) )
			$change .= ( $change ? "%" : "" )."phone:".$profile['phone'].":".$phone;
			
		if( isset( $profile['index'] ) && strcmp( $profile['index'], $index ) )
			$change .= ( $change ? "%" : "" )."index:".$profile['index'].":".$index;
			
		if( isset( $profile['city'] ) && strcmp( $profile['city'], $city ) )
			$change .= ( $change ? "%" : "" )."city:".$profile['city'].":".$city;
		
		if( isset( $profile['adress'] ) && strcmp( $profile['adress'], $adress ) )
			$change .= ( $change ? "%" : "" )."adress:".$profile['adress'].":".$adress;
			
		if( isset( $profile['bdate'] ) && strcmp( $profile['bdate'], $bdate ) )
			$change .= ( $change ? "%" : "" )."bdate:".$profile['bdate'].":".$bdate;
			
		if( isset( $profile['comments'] ) && strcmp( $profile['comments'], $comments ) )
			$change .= ( $change ? "%" : "" )."comments:".$profile['comments'].":".$comments;
		
		if( $upass && strcmp( $profile['password'], $upass ) ) {
			$main->users->updateDefaultProfileField( $userid, 'upass', $main->users->encodePassword( $upass, $main->users->userArray['salt'] ) );
			$main->users->insertNewAction( $main->users->user_actions['password_change'], $userid, $main->users->sid, $upass );
			$main->users->updateProfileField( $userid, 'password', $upass );
		}

		$main->users->updateDefaultProfileField( $userid, 'name', $name." ".$surname );
		
		$main->users->updateProfileField( $userid, 'name', $name );
		$main->users->updateProfileField( $userid, 'suname', $surname );
		$main->users->updateProfileField( $userid, 'phone', $phone );
		$main->users->updateProfileField( $userid, 'index', $index );
		$main->users->updateProfileField( $userid, 'city', $city );
		$main->users->updateProfileField( $userid, 'adress', $adress );
		$main->users->updateProfileField( $userid, 'gender', $gender );
		$main->users->updateProfileField( $userid, 'comments', $comments );
		$main->users->updateProfileField( $userid, 'bdate', $bdate );
		
		if( $change )
			$main->users->insertNewAction( $main->users->user_actions['information_change'], $userid, $main->users->sid, $change );
		
		return $userid;
	}
	
	function processSavingSign()
	{
		global $query, $main, $mysql, $utils, $lang;
		
		$userid = $main->users->userArray['id'];
		$us = $main->users->getUserById( $userid );
		$profile = $main->users->getUserProfile( $userid );
		
		$ras = $query->gp_post( "ras" );
		$change = "";
		
		if( isset( $profile['ras'] ) && $profile['ras'] != $ras )
			$change .= ( $change ? "%" : "" )."sign:".$profile['ras'].":".$ras;
		
		$main->users->updateProfileField( $userid, 'ras', $ras );
		
		if( $ras ) {
			if( !$mysql->mq( "SELECT `id` FROM `".$mysql->t_prefix."sign` WHERE `mail`='".$us['ulogin']."'" ) ) {
				$mysql->mu( "INSERT INTO `".$mysql->t_prefix."sign` VALUES(0,'".$us['ulogin']."');" );
			}
		} else {
			$mysql->mu( "DELETE FROM `".$mysql->t_prefix."sign` WHERE `mail`='".$us['ulogin']."'" );
		}
		
		if( $change )
			$main->users->insertNewAction( $main->users->user_actions['information_change'], $userid, $main->users->sid, $change );
		
		return $userid;
	}
}

?>
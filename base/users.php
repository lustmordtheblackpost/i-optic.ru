<?php

if( !defined( "in_ochki" ) && !defined( "in_ochki_admin" ) ) die( "You can't access this file directly" );

class Users
{
	var $types = array();
	var $defaultType = 0;
	
	var $auth = false;
	
	var $ip = '';
	var $currentAgent = '';
		
	var $userLogin = '';
	var $userArray = null;
	var $sid = null;
	
	var $displayedLogin = "";
	var $displayedPassword = "";
	
	var $auth_error = "";
	
	var $user_actions = array(
		'registration' => 1,			// Доступно с сайта обычным пользователям. Регистрация
		'auth' => 2,					// Доступно с сайта обычным пользователям. Авторизация
		'activation' => 3,				// Доступно с сайта обычным пользователям. Активация аккаунта
		'password_change' => 4,			// Доступно с сайта обычным пользователям. Изменение пароля
		'information_change' => 5,		// Доступно с сайта обычным пользователям. Изменение своей информации
		'password_recovery' => 6
	);
	
	var $actions_text = array(
		1 => "Регистрация",
		2 => "Авторизация",
		3 => "Активация аккаунта",
		4 => "Изменение пароля",
		5 => "Изменение своей информации",
		6 => "Восстановление пароля"
	);
	
	var $showActivated = false;
	var $forcedLogin = '';
	var $forcedPassword = '';
        
        var $goingFromOrder = false;
	
	function init()
	{
		global $mysql, $query;
		
		$a = $mysql->mqm( "SELECT * FROM `".$mysql->t_prefix."user_types` WHERE 1 ORDER BY `order` ASC" );
		while( $r = @mysql_fetch_assoc( $a ) ) {
			if( $r['default'] == 1 )
				$this->defaultType = $r['id'];
			$this->types[$r['id']] = $r;
		}
		
		if( $query->gp( "activatelogin" ) ) {
			$auth = $query->gp( "activatelogin" );
			$r = $mysql->mq( "SELECT * FROM `".$mysql->t_prefix."users` WHERE `auth`='".$auth."'" );
			if( $r ) {
				$this->showActivated = $r['id'];
				$this->activateAccount( $r['id'], $auth );
				$this->forcedLogin = $r['ulogin'];
				$this->forcedPassword = $this->getUserProfileField( $r['id'], "password" );
			}
		}
		
		$this->ip = @htmlspecialchars( @addslashes( @getenv( REMOTE_ADDR ) ? @getenv( REMOTE_ADDR ) : @getenv( HTTP_X_FORWARDED_FOR ) ) );
		$this->currentAgent = @htmlspecialchars( @addslashes( @getenv( HTTP_USER_AGENT ) ) );
		
		if( !$query->gp( "admin" ) ) {
			
			if( $query->gp( "logout" ) ) {
				@setcookie( "ochki_webusid", '', time() + 31536000, $mysql->settings['local_folder'], str_replace( "www.", "", strtolower( $_SERVER['HTTP_HOST'] ) ) );
				$this->closeSession( $this->sid );
				$this->auth = false;
				$this->userArray = null;
				header( "Location: ".$mysql->settings['local_folder'] );
				exit;
			}
			
			if( $this->checkAuth() )
				$this->auth = true;
			else {
				
				if( !isset( $_COOKIE['ochki_websid'] ) || !$_COOKIE['ochki_websid'] ) {
					$ses = $this->createNouserSession( $this->ip, $this->currentAgent );
					if( $ses ) {
						$this->sid = $ses['sid'];
						setcookie( "ochki_websid", $ses['sid'], time() + ( 3600 * 24 * 365 ), $mysql->settings['local_folder'], str_replace( "www.", "", strtolower( $_SERVER['HTTP_HOST'] ) ) );
						return true;
					}
				} else {
					
					$ls = $mysql->mq( "SELECT * FROM `".$mysql->t_prefix."user_ses` WHERE `sid`='".addslashes( $_COOKIE['ochki_websid'] )."'" );
					if( $ls ) {
						$mysql->mu( "UPDATE `".$mysql->t_prefix."user_ses` SET `last`=".time()." WHERE `id`=".$ls['id'] );
						$this->sid = $ls['sid'];
					} else {
						$ses = $this->createNouserSession( $this->ip, $this->currentAgent );
				
						if( $ses ) {
							$this->sid = $ses['sid'];
							@setcookie( "ochki_websid", $ses['sid'], time() + ( 3600 * 24 * 365 ), $mysql->settings['local_folder'], str_replace( "www.", "", strtolower( $_SERVER['HTTP_HOST'] ) ) );
							return true;
						}
					}
					
				}
				
			}
		}
		
		$this->displayedLogin = ( !$this->userLogin ? ( isset( $_COOKIE['ochki_webulogin'] ) ? $_COOKIE['ochki_webulogin'] : '' ) : $this->userLogin );
		$this->displayedPassword = ( isset( $_COOKIE['ochki_webupass'] ) ? base64_decode( strrev( $_COOKIE['ochki_webupass'] ) ) : '' );
	}
	
	function checkExistPasswordOfCurrentUser( $pass )
	{
		if( !$this->auth )
			return false;
			
		return $this->getUserByLoginAndPassword( $this->userArray['ulogin'], $pass, true );
	}
	
	function checkAuth()
	{ 
		global $query, $main, $mysql, $lang;
		
		$sid = $query->gp_post( "usid", 32 );
		$c_sid = isset( $_COOKIE['ochki_webusid'] ) && strlen( $_COOKIE['ochki_webusid'] ) === 32 ? $query->fm( $_COOKIE['ochki_webusid'] ) : null;
		
		$l = $this->forcedLogin ? $this->forcedLogin : $query->gp_post( "u_login" );
		if( strlen( $l ) > 50 )
			$l = null;
		$p = $this->forcedPassword ? $this->forcedPassword : $query->gp_post( "u_pass" );
		if( strlen( $p ) > 100 )
			$p = null;
			
		if( !$l ) {
			$l = $query->gp_post( "u_login_order" );
			if( strlen( $l ) > 50 )
				$l = null;
			$p = $query->gp_post( "u_pass_order" );
			if( strlen( $p ) > 100 )
				$p = null;
                        
                        if( $l )
                            $this->goingFromOrder = true;
                            
		}
			
		if( $sid || $c_sid ) {
			
			$usid = $sid ? $sid : $c_sid;
			$ses = $this->getLastUserSessionBySID( $usid, $this->ip, $this->currentAgent, true );
			
			if( $ses ) {
				
				$u = $this->getUserById( $ses['user'], true );
				if( $u && time() - $ses['last'] < $mysql->settings['user_session_length'] && !$u['block'] && $u['level'] >= $this->defaultType ) {
					
					$this->updateSessionTime( $ses );
					$this->userArray = $u;
					$this->sid = $ses['sid'];
					@setcookie( "ochki_webusid", $ses['sid'], time() + $mysql->settings['user_session_length'], $mysql->settings['local_folder'], str_replace( "www.", "", strtolower( $_SERVER['HTTP_HOST'] ) ) );
					
					return true;
					
				} else {
					$this->auth_error = $lang->gp( 114 );
					$this->closeSession( $usid );
				}
				
			} else {
				$this->auth_error = $lang->gp( 114 );
				$this->closeSession( $usid );
			}
			
			$ses = null;
			if( $c_sid ) {
				@setcookie( "ochki_webusid", '', time() + 31536000, $mysql->settings['local_folder'], str_replace( "www.", "", strtolower( $_SERVER['HTTP_HOST'] ) ) );
				$c_sid = "";
			}
			if( $sid )
				$sid = "";
			
		}
		
		if( $l && $p ) {
			
			$u = $this->getUserByLoginAndPassword( $l, $p, true );
			if( $u && !$u['block'] && $u['level'] >= $this->defaultType && !$u['auth'] ) {
				
                                $usid = addslashes( $_COOKIE['ochki_websid'] );
                                $ses = null;
                                if( $usid ) {
                                    $ses = $this->getLastUserSessionBySID( $usid, $this->ip, $this->currentAgent, true );
                                    if( $ses ) {
                                        $this->updateSessionTime( $ses );
                                        $mysql->mu( "UPDATE `".$mysql->t_prefix."user_ses` SET `user`=".$u['id']." WHERE `id`=".$ses['id'] );
                                        $mysql->mu( "UPDATE `".$mysql->t_prefix."basket` SET `user`=".$u['id']." WHERE `session`='".$usid."'" );
                                    }
                                }
                                if( !$ses )
                                    $ses = $this->createSession( $u, $this->ip, $this->currentAgent );
				
				if( $ses ) {
					$this->userArray = $u;
					$this->sid = $ses['sid'];
					@setcookie( "ochki_webusid", $ses['sid'], time() + $mysql->settings['user_session_length'], $mysql->settings['local_folder'], str_replace( "www.", "", strtolower( $_SERVER['HTTP_HOST'] ) ) );
					@setcookie( "ochki_webulogin", $l, time() + 31536000, $mysql->settings['local_folder'], str_replace( "www.", "", strtolower( $_SERVER['HTTP_HOST'] ) ) );
					@setcookie( "ochki_webupass", strrev( base64_encode( $p ) ), time() + 31536000, $mysql->settings['local_folder'], str_replace( "www.", "", strtolower( $_SERVER['HTTP_HOST'] ) ) );
					
					$this->insertNewAction( $this->user_actions['auth'], $u['id'], $this->sid );
					
					return true;
				} else 
					$this->auth_error = $lang->gp( 115 );
				
			} else if( !$u ) {
				$u = $this->getUserByLoginAndPasswordIgnoreAuth( $l, $p, true );
				if( !$u )
					$this->auth_error = $lang->gp( 116 );
				else
					$this->auth_error = $lang->gp( 119 );
			} else if( $u['level'] < $this->defaultType ) {
				$this->auth_error = $lang->gp( 118 );
			} else if( !$u['block'] ) {
				$this->auth_error = $lang->gp( 117 );
			}
			
		}
		
		if( $l ) {
			$this->userLogin = $l;
			@setcookie( "ochki_webulogin", $l, time() + 31536000, $mysql->settings['local_folder'], str_replace( "www.", "", strtolower( $_SERVER['HTTP_HOST'] ) ) );
			@setcookie( "ochki_webupass", '', time() + 31536000, $mysql->settings['local_folder'], str_replace( "www.", "", strtolower( $_SERVER['HTTP_HOST'] ) ) );
		}
		
		@setcookie( "ochki_webusid", '', time() + 31536000, $mysql->settings['local_folder'], str_replace( "www.", "", strtolower( $_SERVER['HTTP_HOST'] ) ) );
		
		return false;
	}
	
	function generateSID( $login, $salt )
	{
		return md5( $login.time().$salt );
	}
	
	function generatePasswordSalt( $len = 6 )
	{
		$salt = '';

		for( $i = 0; $i < $len; $i++ ) {
			$num = mt_rand( 33, 126 );
			if( $num == '92' )
				$num = 93;
			$salt .= chr( $num );
		}
		
		return str_replace( "'", "_", str_replace( "\\", "\\\\", $salt ) );
	}
	
	function encodePassword( $pass, $salt )
	{
		return md5( md5( $salt )."_".md5( $pass ) );
	}
	
	function getLastUserSessionBySID( $sid, $ip, $userAgent = '', $closed = false )
	{
		global $mysql;	
		
		//return $mysql->mq( "SELECT * FROM `".$mysql->t_prefix."user_ses` WHERE `sid`='".$sid."' AND `ip`='".$ip."'".( $userAgent ? " AND `user_agent`='".$userAgent."'" : "" ).( $closed ? " AND `closed`=0" : "" ) );
		return $mysql->mq( "SELECT * FROM `".$mysql->t_prefix."user_ses` WHERE `sid`='".$sid."'".( $userAgent ? " AND `user_agent`='".$userAgent."'" : "" ).( $closed ? " AND `closed`=0" : "" ) );
	}
	
	function getUserById( $id, $checkBlock = false, $notadmin = false )
	{
		global $mysql;
		
		if( !is_numeric( $id ) )
			return null;
			
		$r = $mysql->mq_spec( $mysql->t_prefix."users", "`id`=".$id.( $notadmin ? " AND `level`>=".$this->defaultType : "" ) );
		
		if( !$r )
			return null;
			
		if( $checkBlock && $r['block'] === 1 )
			return null;
			
		return $r;
	}
	
	function getUsernameById( $id )
	{
		global $lang;
		
		$user = $this->getUserById( $id );
		
		return $user ? ( is_numeric( $user['name'] ) ? $lang->getPh( $user['name'] ) : $user['name'] ) : "";
	}
	
	function getUserloginById( $id )
	{
		global $lang;
		
		$user = $this->getUserById( $id );
		
		return $user ? $user['ulogin'] : "";
	}
	
	function getUserIdByLogin( $login )
	{
		global $mysql;
		
		$r = $mysql->mq_spec( $mysql->t_prefix."users", "`ulogin`='".$login."'", "`id`" );
		
		if( !$r )
			return null;
			
		return $r['id'];
	}
	
	function getUserByLoginAndPassword( $l, $p, $checkBlock = false )
	{
		global $mysql;
		
		$r = $mysql->mq_spec( $mysql->t_prefix."users", "`ulogin`='".$l."' AND `auth`=''", "`salt`, `block`, `level`" );
			
		return !$r || ( $checkBlock && $r['block'] === 1 ) ? null : $mysql->mq_spec( $mysql->t_prefix."users", "`ulogin`='".$l."' AND `upass`='".$this->encodePassword( $p, $r['salt'] )."'" );
	}
	
	function getUserByLoginAndPasswordIgnoreAuth( $l, $p, $checkBlock = false )
	{
		global $mysql;
		
		$r = $mysql->mq_spec( $mysql->t_prefix."users", "`ulogin`='".$l."'", "`salt`, `block`, `level`" );
			
		return !$r || ( $checkBlock && $r['block'] === 1 ) ? null : $mysql->mq_spec( $mysql->t_prefix."users", "`ulogin`='".$l."' AND `upass`='".$this->encodePassword( $p, $r['salt'] )."'" );
	}
	
	function getLastRegistredUser()
	{
		global $mysql;
		
		$r = $mysql->mq( "SELECT * FROM `".$mysql->t_prefix."users` WHERE `level`>=".$this->defaultType." ORDER BY `rdate` DESC" );
		
		return $r;
	}
	
	function getUniqueUserslist( $where )
	{
		global $mysql;
		
		$a = $mysql->mqm( "SELECT `id`,`name` FROM `".$mysql->t_prefix."users` WHERE ".$where." ORDER BY `name` ASC" );
		$ar = array();
		while( $r = @mysql_fetch_assoc( $a ) )
			$ar[$r['id']] = $r['name'];
			
		return $ar;
	}
	
	function getUsers( $what, $where = "1", $order = "`name` ASC", $limit = '' )
	{
		global $mysql;
		
		$a = $mysql->mqm( "SELECT ".$what." FROM `".$mysql->t_prefix."users` WHERE ".$where." ORDER BY ".$order.( $limit && is_numeric( $limit ) ? " LIMIT ".$limit : "" ) );
		$ar = array();
		$index = 1;
		while( $r = @mysql_fetch_assoc( $a ) )
			$ar[$index++] = $r;
			
		return $ar;
	}
	
	function getUsersSpecialWithIds( $what, $where = "1", $order = "`name` ASC", $limit = '' )
	{
		global $mysql;
		
		$a = $mysql->mqm( "SELECT `id`".( $what ? ",".$what : "" )." FROM `".$mysql->t_prefix."users` WHERE ".$where." ORDER BY ".$order.( $limit && is_numeric( $limit ) ? " LIMIT ".$limit : "" ) );
		$ar = array();
		while( $r = @mysql_fetch_assoc( $a ) )
			$ar[$r['id']] = $r;
			
		return $ar;
	}
	
	function getUsersWithIds()
	{
		global $mysql;
		
		$a = $mysql->mqm( "SELECT * FROM `".$mysql->t_prefix."users` WHERE 1" );
		$ar = array();
		while( $r = @mysql_fetch_assoc( $a ) )
			$ar[$r['id']] = $r;
			
		return $ar;
	}
	
	function getUserLevelById( $id )
	{
		global $mysql;
		
		$r = $mysql->mq_spec( $mysql->t_prefix."users", "`id`='".$id."'", "`level`" );
		
		if( !$r )
			return null;
			
		return $r['level'];
	}
	
	function updateSessionTime( &$ses )
	{
		global $mysql;
		
		$ses['last'] = time();
		$mysql->mu( "UPDATE `".$mysql->t_prefix."user_ses` SET `last`=".$ses['last']." WHERE `id`=".$ses['id'] );
	}
	
	function closeSession( $sid )
	{
		global $mysql;
		
		$mysql->mu( "UPDATE `".$mysql->t_prefix."user_ses` SET `closed`=1 WHERE `sid`='".$sid."'" );
	}
	
	function createSession( $u, $ip, $agent )
	{
		global $mysql;
		
		do {
			$sid = md5( $u['id'].$u['ulogin'].mt_rand( 111111, 999999 ) );
		} while( $mysql->mq_spec( $mysql->t_prefix."user_ses", "`sid`='".$sid."'" ) );
		
		$mysql->mu( "INSERT INTO `".$mysql->t_prefix."user_ses` VALUES (
		0,
		'".$sid."',
		".$u['id'].",
		".time().",
		".time().",
		'".$ip."',
		'".$agent."',
		0
		);" );
		
		return $mysql->mq_spec( $mysql->t_prefix."user_ses", "`sid`='".$sid."' AND `user`=".$u['id'] );
	}
	
	function createNouserSession( $ip, $agent )
	{
		global $mysql;
		
		do {
			$sid = md5( mt_rand( 111111, 999999 ).time() );
		} while( $mysql->mq_spec( $mysql->t_prefix."user_ses", "`sid`='".$sid."'" ) );
		
		$mysql->mu( "INSERT INTO `".$mysql->t_prefix."user_ses` VALUES (
		0,
		'".$sid."',
		0,
		".time().",
		".time().",
		'".$ip."',
		'".$agent."',
		0
		);" );
		
		return $mysql->mq_spec( $mysql->t_prefix."user_ses", "`sid`='".$sid."'" );
	}
	
	function saveSessionOption( $for, $data )
	{
		global $mysql;
		
		$mysql->mu( "DELETE FROM `".$mysql->t_prefix."user_ses_options` WHERE `sid`='".$this->sid."' AND `for`=".$for );
		$mysql->mu( "INSERT INTO `".$mysql->t_prefix."user_ses_options` VALUES (
		0,
		'".$this->sid."',
		".$for.",
		'".addslashes( $data )."'
		);" );
	}
        
        function updateSessionOptionData( $sid, $for, $data )
        {
            global $mysql;
            
            $mysql->mu( "UPDATE `".$mysql->t_prefix."user_ses_options` SET `option`='".$data."' WHERE `sid`='".$sid."' AND `for`=".$for );
        }
	
	function getSessionOption( $for )
	{
		global $mysql;
		
		$r = $mysql->mq( "SELECT `option` FROM `".$mysql->t_prefix."user_ses_options` WHERE `sid`='".$this->sid."' AND `for`=".$for );
		
		return $r ? $r['option'] : 0;
	}
        
        function getSessionOptionWithSID( $sid, $for )
	{
		global $mysql;
		
		$r = $mysql->mq( "SELECT `option` FROM `".$mysql->t_prefix."user_ses_options` WHERE `sid`='".$sid."' AND `for`=".$for );
		
		return $r ? $r['option'] : 0;
	}
	
	function activateAccount( $id, $auth, $realy  = true )
	{
		global $mysql;
		
		if( $realy )
			$mysql->mu( "UPDATE `".$mysql->t_prefix."users` SET `auth`='' WHERE `id`=".$id );
		$this->insertNewAction( $this->user_actions['activation'], $id, '', $auth );
	}
	
	function getSomeActionOfUser( $action, $user )
	{
		global $mysql;
		
		$r = $mysql->mq( "SELECT * FROM `".$mysql->t_prefix."user_actions` WHERE `user`=".$user." AND `action`=".$action." ORDER BY `id` DESC" );
		
		return $r ? $r : 0;
	}
	
	function updateActionTime( $id, $newtime )
	{
		global $mysql;
		
		$mysql->mu( "UPDATE `".$mysql->t_prefix."user_actions` SET `date`=".$newtime." WHERE `id`=".$id );
	}
	
	function generateRecoveryCode( $user )
	{
		global $mysql;
		
		$auth = md5( time().$user['salt'].$user['upass'] );
		$this->insertNewAction( $this->user_actions['password_recovery'], $user['id'], '', $auth );
		
		return $auth;
	}
	
	function makeNewPassword( $user )
	{
		global $mysql, $utils;
		
		if( $user ) {
			
			$newpass = $utils->generatePassword();
			$mysql->mu( "UPDATE `".$mysql->t_prefix."users` SET `upass`='".$this->encodePassword( $newpass, $user['salt'] )."' WHERE `id`=".$user['id'] );
			
			return $newpass;
		}
		
		return "";
	}
	
	function insertNewUser( $us )
	{
		global $mysql, $main;
		
		$salt = $this->generatePasswordSalt();
		$cd = time();
		$auth = md5( time().$salt.$us['upass'] );
		
		$str = "INSERT INTO `".$mysql->t_prefix."users` VALUES (
		0,
		'".$us['name']."',
		'".$us['ulogin']."',
		'".$this->encodePassword( $us['upass'], $salt )."',
		'".$salt."',
		".( is_numeric( $us['level'] ) ? $us['level'] : $this->defaultType ).",
		0,
		'".( !isset( $us['date'] ) ? $cd : $us['date'] )."',
		'".$us['comment']."',
		'',
		1
		);";
		/*.$auth. автоактивация*/
		$mysql->mu( $str );

		$user = $mysql->mq_spec( $mysql->t_prefix."users", "`rdate`=".( !isset( $us['date'] ) ? $cd : $us['date'] ), "*", "`id`", "DESC" );
		
		if( $user ) {
			$this->insertNewAction( $this->user_actions['registration'], $user['id'], '', $auth );
			
			return $user;
		}
		
		return 0;
	}
	
	function insertNewAction( $action, $user, $sid = '', $add = '' )
	{
		global $mysql;
		
		$mysql->mu( "INSERT INTO `".$mysql->t_prefix."user_actions` VALUES (
		0,
		'".$sid."',
		'".$this->ip."',
		".$action.",
		".time().",
		".$user.",
		'".$add."'
		);" );
	}
	
	function insertProfileField( $user, $setting, $value )
	{
		global $mysql;
		
		$mysql->mu( "INSERT INTO `".$mysql->t_prefix."user_profile` VALUES (".$user.",'".$setting."','".$value."');" );
	}
	
	function updateProfileField( $user, $setting, $value )
	{
		global $mysql;
		
		if( !$mysql->mq( "SELECT `user` FROM `".$mysql->t_prefix."user_profile` WHERE `user`=".$user." AND `setting`='".$setting."'" ) ) 
			$this->insertProfileField( $user, $setting, $value );
		else if( $value === 0 || $value )
			$mysql->mu( "UPDATE `".$mysql->t_prefix."user_profile` SET `value`='".$value."' WHERE `user`=".$user." AND `setting`='".$setting."'" );
		else 
			$mysql->mu( "DELETE FROM `".$mysql->t_prefix."user_profile` WHERE `user`=".$user." AND `setting`='".$setting."'" );
	}
	
	function updateDefaultProfileField( $user, $setting, $value )
	{
		global $mysql;
		
		$mysql->mu( "UPDATE `".$mysql->t_prefix."users` SET `".$setting."`='".$value."' WHERE `id`=".$user );
	}
	
	function getUserProfile( $id )
	{
		global $mysql;
		
		$ar = array();
		$a = $mysql->mqm( "SELECT * FROM `".$mysql->t_prefix."user_profile` WHERE `user`=".$id );
		while( $r = @mysql_fetch_assoc( $a ) )
			$ar[$r['setting']] = $r['value'];
			
		return $ar;
	}
	
	function getUserProfileField( $id, $field )
	{
		global $mysql;
		
		$r = $mysql->mq( "SELECT `value` FROM `".$mysql->t_prefix."user_profile` WHERE `user`=".$id." AND `setting`='".$field."'" );
			
		return $r ? $r['value'] : null;
	}
	
	//
	// Далее функции для визуальных решених в администраторской панели
	//
	
	function getAdminListScreen( &$selectedElement, $path )
	{
		global $mysql, $query, $utils, $admin, $lang, $main;
		
		$open = "";
		if( $query->gp( "edit" ) && $query->gp( "process" ) ) {
			
			$userid = $query->gp( "edit" );
			$name = $query->gp( "u_name" );
			$login = $query->gp( "u_login" );
			$pass = $query->gp( "u_pass" );
			$type = $query->gp( "u_type" );
			$comment = $query->gp_letqbe( "u_comment" );
			$comment = $comment ? @str_replace( "'", "\\'", $comment ) : "";
			$auth = $query->gp( "u_auth" );
			$status = $query->gp( "u_status" );
			
			$codedpass = "";
			if( $pass ) {
				$u = $mysql->mq( "SELECT `salt` FROM `".$mysql->t_prefix."users` WHERE `id`=".$userid );
				$codedpass = $this->encodePassword( $pass, $u['salt'] );
			}
				
			$mysql->mu( "UPDATE `".$mysql->t_prefix."users` SET
			
			`name`='".$name."',
			`ulogin`='".$login."',
			`level`='".$type."',
			".( $pass && $codedpass ? "`upass`='".$codedpass."'," : "" )."
			`comment`='".$comment."',
			`auth`='".$auth."',
			`status`='".$status."'
			
			WHERE `id`=".$userid );
			
			if( $type >= $this->defaultType ) {
				$profile_name = $query->gp_post( "profile_name" );
				$profile_secondname = $query->gp_post( "profile_secondname" );
				$profile_surname = $query->gp_post( "profile_surname" );
				$profile_phone = $query->gp_post( "profile_phone" );
				$profile_adress = $query->gp_post( "profile_adress" );
			
				$this->updateProfileField( $userid, 'surname', $profile_surname );
				$this->updateProfileField( $userid, 'name', $profile_name );
				$this->updateProfileField( $userid, 'secondname', $profile_secondname );
				$this->updateProfileField( $userid, 'phone', $profile_phone );
				$this->updateProfileField( $userid, 'adress', $profile_adress );
			} else
				$mysql->mu( "DELETE FROM `".$mysql->t_prefix."user_profile` WHERE `user`=".$userid );
			
			if( $admin->userLevel > 1 )
				$this->insertNewAction( $this->user_actions['admin_user_edit'], $userid, $admin->sid, $userid );
			
		} else if( $query->gp( "createuser" ) && $query->gp( "process" ) ) {
			
			$name = $query->gp( "u_name" );
			$login = $query->gp( "u_login" );
			$pass = $query->gp( "u_pass" );
			$type = $query->gp( "u_type" );
			$comment = $query->gp_letqbe( "u_comment" );
			$comment = $comment ? @str_replace( "'", "\\'", $comment ) : "";
			$status = $query->gp( "u_status" );
			
			$salt = $this->generatePasswordSalt();
			$codedpass = $this->encodePassword( $pass, $salt );
			
			$mysql->mu( "INSERT INTO `".$mysql->t_prefix."users` VALUES(0,'".$name."','".$login."','".$codedpass."','".$salt."',".$type.",0,".time().",'".$comment."','',".$status.");" );
			
		} else if( $query->gp( "turnuser" ) ) {
			
			$id = $query->gp( "turnuser" );
			$ep = $mysql->mq( "SELECT `block` FROM `".$mysql->t_prefix."users` WHERE `id`=".$id );			
			if( $ep )
				$mysql->mu( "UPDATE `".$mysql->t_prefix."users` SET `block`=".( $ep['block'] ? "0" : "1" )." WHERE `id`=".$id );
				
		} else if( $query->gp( "activateuser" ) ) {
			
			$id = $query->gp( "activateuser" );
			$ep = $mysql->mq( "SELECT `ulogin` FROM `".$mysql->t_prefix."users` WHERE `id`=".$id );			
			if( $ep ) {
				$mysql->mu( "UPDATE `".$mysql->t_prefix."users` SET `auth`='' WHERE `id`=".$id );
				
				/*
				$mail_agent = $main->modules->getModuleInstanceByLocal( "mail_agent" );
				$template = $mail_agent->getTemplate( ACTIVATION_TEMPLATE );
				if( $mail_agent && $template ) {
					$reg = $main->modules->getModuleInstanceByLocal( "registration" );
					$mail_agent->sendMessage( $ep['ulogin'], $reg->getParam( 'email_from' ), $main->templates->processScriptLanguage( $lang->getPh( 49 ) ), $main->templates->processScriptLanguage( $template ) );
				}
				*/
			}
			
		} else if( $query->gp( "delete" ) ) {
			
			$id = $query->gp( "delete" );			
			$r = $mysql->mq( "SELECT * FROM `".$mysql->t_prefix."users` WHERE `id`=".$id );
			
			if( $r ) {
				
				$mysql->mu( "DELETE FROM `".$mysql->t_prefix."users` WHERE `id`=".$id );
				$mysql->mu( "DELETE FROM `".$mysql->t_prefix."user_ses` WHERE `user`=".$id );
				$mysql->mu( "DELETE FROM `".$mysql->t_prefix."user_profile` WHERE `user`=".$id );
				$mysql->mu( "DELETE FROM `".$mysql->t_prefix."user_actions` WHERE `user`=".$id );
			}
			
		}
		
		$text_search = $query->gp( "text_search" );
		
		$utype = $query->gp( "utype" );
		$utype = $utype ? ( $admin->userLevel != 1 && $utype <= 2 ? 0 : $utype ) : 0;
		
		$page = $query->gp( "page" );
		$page = $page ? $page : 0;
		
		$rs = "<option value=\"0\"".( !$utype ? " selected" : "" ).">Показывать всех</option>";
		foreach( $this->types as $k => $v ) {
			if( $admin->userLevel != 1 && $k <= 2 )
				continue;
			$rs .= "<option value=\"".$k."\"".( $utype == $k ? " selected" : "" ).">".$lang->gp( $v['name'] )."</option>";
		}
		
		$selectedElement = 2;
		
		$where = '';
		if( !$utype ) {
			$where = $admin->userLevel == 1 ? "" : "`level`>2";
		} else {
			$where = "`level`=".$utype;
		}
		
		if( $text_search )
			$where .= ( $where ? " AND " : "" )."(`id`='".$text_search."' OR `name` LIKE '%".$text_search."%' OR `ulogin` LIKE '%".$text_search."%')";
			
		if( !$where )
			$where = "1";
		
		$a = $mysql->mqm( "SELECT * FROM `".$mysql->t_prefix."users` WHERE ".$where." ORDER BY `rdate` DESC, `level` ASC, `name` ASC" );
		$items_count = @mysql_num_rows( $a );
		$total = $items_count;
		$pages = "";
		$maxonpage = 20;
		if( $items_count > $maxonpage ) {

			$pagesCount = ceil( $items_count / $maxonpage );
			
			if( $page > $pagesCount )
				$page = $pagesCount;
			else if( $page < 1 )
				$page = 1;
			
			$startFrom = ( $page - 1 ) * $maxonpage;
			
			$pages = "<div style='margin-top: 10px;'>Страницы: ";
			
			$p = array();
			for( $a = 1; $a <= $pagesCount; $a++ ) {
				$p[$a] = ( $a > 1 ? "&nbsp;" : "" ).( $page == $a ? 
					"<span><strong>".$a."</strong></span>" : 
					"<a href=\"".$mysql->settings['local_folder']."admin/".$path."".( $text_search ? "/text_search!".$text_search : "" )."".( $utype ? "/utype".$utype : "" )."/page".$a."\"><b>".$a."</b></a>" );
			}
			
			$tp = "";
			if( count( $p ) >= 20 ) {
				$tp = $p[$page];
				$a = $page - 1;
				while( isset( $p[$a] ) && $a > $page - 10 )
					$tp = $p[$a--].$tp;
				if( ++$a > 1 ) {
					if( $a > 2 )
						$tp = "&nbsp;...&nbsp;".$tp;
					$tp = $p[1].$tp;
				}
				$a = $page + 1;
				while( isset( $p[$a] ) && $a < $page + 10 )
					$tp .= $p[$a++];
				if( --$a < $pagesCount ) {
					if( $a < $pagesCount - 1 )
						$tp .= "&nbsp;...&nbsp;";
					$tp .= $p[$pagesCount];
				}
			} else {
				foreach( $p as $v )
					$tp .= $v;
			}
					
			$pages .= $tp."&nbsp;&nbsp;".( $page > 1 ? "[<a href=\"".$mysql->settings['local_folder']."admin/".$path."".( $text_search ? "/text_search!".$text_search : "" )."".( $utype ? "/utype".$utype : "" )."/page".( $page - 1 )."\"><<</a>]" : "" ).( $page < $pagesCount ? ( $page > 1 ? " | " : "" )."[<a href=\"".$mysql->settings['local_folder']."admin/".$path."".( $text_search ? "/text_search!".$text_search : "" )."".( $utype ? "/utype".$utype : "" )."/page".( $page + 1 )."\">>></a>]" : "" )."</div>";
			
			$a = $mysql->mqm( "SELECT * FROM `".$mysql->t_prefix."users` WHERE ".$where." ORDER BY `rdate` DESC, `level` ASC, `name` ASC LIMIT ".$startFrom.",".$maxonpage );
			$items_count = @mysql_num_rows( $a );
		}
		
		$t = "
			<h1>Список всех пользователей системы</h1>
			
			".( $admin->userLevel == 1 ? "<a href=\"#\" onclick=\"getWindowContent( '".$mysql->settings['local_folder']."admin/".$path."".( $utype ? "/utype".$utype : "" )."', 3, 'newuser' );\">Создать нового пользователя</a><br><br><input type=button value='Экспорт пользователей (покупателей)' onclick=\"document.location='".$mysql->settings['local_folder']."admin/".$path."/export';\" /><br><br>" : "" )."
			
			<form method=POST action=\"".$mysql->settings['local_folder']."admin/".$path."/\" id='users_form' style='padding: 0px; margin: 0px;'>
			<table border=0>
				<tr>
					<td>
						Показывать только тип пользователей:<br>
						<select name=\"utype\" id=\"utype\" class='select_input' onchange=\"ge( 'users_form' ).submit();\" style='min-width: 240px;'>
							".$rs."
						</select>
					</td>
					<td style='padding-left: 10px; white-space: nowrap;'>
						Искать пользователей по ID, логину или имени:<br>
						<input type=text name='text_search' id='text_search' value=\"".$text_search."\" class='text_input' style='min-width: 240px;' />
						<input type=button value=\"Очистить\" onclick=\"if( $( '#text_search' ).val() == '' ) return false; $( '#text_search' ).attr( 'value', '' ); ge( 'users_form' ).submit();\"".( $text_search ? "" : " style='display: none;'" )." />
					</td>
				</tr>
			</table>
			Крсным цветом фона выделены все <b>неактивные</b> аккаунты, при этом, если в «Подтверждении» стоит ДА, то была пройдена активация через E-Mail. Чтобы активировать полностью, нужно удалить «Код подтверждения»<br>
			Серым цветом текста выделены все <b>блокированные</b> аккаунты.
			</form>
			
			".$pages."
			
			<table cellspacing=0 cellpadding=0 class='list_table' style='width: 100%; margin-top: 10px;'>
				<tr class='list_table_header'>
					<td width=30>
						ID
					</td>
					<td width=25%>
						Имя и логин (доп. инфо)
					</td>
					<td width=25%>
						Тип
					</td>
					<td width=25%>
						Регистрация
					</td>
					<td width=25% nowrap>
						Последний вход
					</td>
					<td width=110>
						Блокировка?
					</td>
					<td width=110>
						Подтверждение?
					</td>
					<td width=110>
						Опции
					</td>
				</tr>
		";
		
		$query_string = ( $text_search ? "/text_search!".$text_search : "" )."".( $utype ? "/utype".$utype : "" )."".( $page ? "/page".$page : "" );
			
		while( $r = @mysql_fetch_assoc( $a ) ) {
			$lastses = $mysql->mq( "SELECT `last` FROM `".$mysql->t_prefix."user_ses` WHERE `user`=".$r['id']." ORDER BY `last` DESC" );
			
			$activated = $this->getSomeActionOfUser( $this->user_actions['activation'], $r['id'] );
			
			$style = $r['auth'] ? " style='background-color: #fde4e4;'" : "";
			
			$textStyle = '';
			if( $r['block'] )
				$textStyle = "color: #bbb;";
				
			$userProfile = $this->getUserProfile( $r['id'] );
			
			$t .= "
				<tr class='list_table_element'".$style.">
					<td valign=top style='".$textStyle."'>
						".$r['id']."
					</td>
					<td style='text-align: left;".$textStyle."'>
						<a href=\"#\" onclick=\"getWindowContent( '".$mysql->settings['local_folder']."admin/".$path."/userid".$r['id']."".$query_string."', 5, 'user_view' ); return false;\"><b>".( is_numeric( $r['name'] ) ? $lang->getPh( $r['name'] ) : $r['name'] )."</b></a>
						<p class='comment' style='".$textStyle."'>
							Логин: ".$r['ulogin']."
						</p>
						".( $r['comment'] ? "
						<p class='comment' style='".$textStyle." font-size: 0.8em;'>
							<b>".$r['comment']."</b>
						</p>
						" : "" )."
					</td>
					<td style='".$textStyle."'>
						".$lang->getPh( $this->types[$r['level']]['name'] )."
					</td>
					<td style='".$textStyle."'>
						".date( "d/m/Y H:i", $r['rdate'] )."
					</td>
					<td style='".$textStyle."'>
						".( $lastses ? date( "d/m/Y H:i", $lastses['last'] ) : "Не входил" )."
					</td>
					<td style='".$textStyle."'>
						".( $r['block'] ? "Да" : "Нет" )."
					</td>
					<td style='".$textStyle."'>
						".( !$r['auth'] || ( $activated && $r['auth'] ) ? "<b>Да</b>" : "Нет" )."
					</td>
					<td nowrap style='".$textStyle."'>
						".( $r['auth'] && $activated ? "<a href=\"".$mysql->settings['local_folder']."admin/".$path."/activateuser".$r['id']."".$query_string."\"><b>Активировать</b></a>
							<br>" : "" )."
						<a href=\"".$mysql->settings['local_folder']."admin/".$path."/turnuser".$r['id']."".$query_string."\">".( $r['block'] ? "Разблокировать" : "Заблокировать" )."</a>
							<br>
						<a href=\"#\" onclick=\"getWindowContent( '".$mysql->settings['local_folder']."admin/".$path."/userid".$r['id']."".$query_string."', 4, 'user_settings' ); return false;\">Редактировать</a>
							<br>
						<a href=\"".$mysql->settings['local_folder']."admin/".$path."/delete".$r['id']."".$query_string."\" onclick=\"if( !confirm( 'Вы уверены?' ) ) return false; return true;\">Удалить</a>
					</td>
				</tr>
			";
		}
		
		$t .= "
			<tr class='list_table_footer'>
					<td colspan=8>
						Всего пользователей/на этой странице: ".$total."/".$items_count."
					</td>
				</tr>
		</table>
		
		".$pages."
		
		<script>
			$( function() {
				$( '#user_settings' ).dialog( { closeOnEscape: false, zIndex: 3000, stack: true, minWidth: 500, minHeight: 500, maxWidth: 700, maxHeight: 750, width: 600, height: 650, autoOpen: false } );
				$( '#user_settings' ).dialog( 'option', 'titleBack', 'url(".$mysql->settings['local_folder'].$utils->javascript_files_path."jqui/themes/base/images/ui-bg_highlight-soft_75_cccccc_1x100.png)' );
				
				$( '#user_view' ).dialog( { closeOnEscape: false, zIndex: 3000, stack: true, minWidth: 500, minHeight: 500, maxWidth: 700, maxHeight: 750, width: 600, height: 650, autoOpen: false, modal: true } );
				$( '#user_view' ).dialog( 'option', 'titleBack', 'url(".$mysql->settings['local_folder'].$utils->javascript_files_path."jqui/themes/base/images/ui-bg_highlight-soft_75_cccccc_1x100.png)' );
				
				".( $admin->userLevel == 1 ? "$( '#newuser' ).dialog( { closeOnEscape: false, zIndex: 3000, stack: true, minWidth: 500, minHeight: 500, maxWidth: 700, maxHeight: 650, width: 600, height: 600, autoOpen: false } );
				$( '#newuser' ).dialog( 'option', 'titleBack', 'url(".$mysql->settings['local_folder'].$utils->javascript_files_path."jqui/themes/base/images/ui-bg_highlight-soft_75_cccccc_1x100.png)' );" : "" )."
			} );
		</script>
		
		<div id='user_settings' title='Настройки пользователя'></div>
		<div id='user_view' title='Просмотр пользователя'></div>
		".( $admin->userLevel == 1 ? "<div id='newuser' title='Новый пользователь'></div>" : "" )."
		".$open;
		
		return $t;
	}
	
	function getAdminActScreen( &$selectedElement, $path )
	{
		global $mysql, $query, $utils, $admin, $lang;
		
		$open = "";
		if( $query->gp( "edit" ) && $query->gp( "process" ) ) {
			
			$id = $query->gp( "edit" );
			$comment = $query->gp( "t_comment" );
			
			$r = $mysql->mq( "SELECT `id` FROM `".$mysql->t_prefix."user_types` WHERE `id`=".$id );
			if( $r )
				$mysql->mu( "UPDATE `".$mysql->t_prefix."user_types` SET `comment`='".$comment."' WHERE `id`=".$id );
			
		} else if( $query->gp( "createtype" ) && $query->gp( "process" ) ) {
			
			$name = $query->gp( "t_name" );
			$comment = $query->gp( "t_comment" );
			
			$r = $mysql->mq( "SELECT `order` FROM `".$mysql->t_prefix."user_types` WHERE 1 ORDER BY `order` DESC" );
			$order = $r ? $r['order'] + 1 : 1;
			
			$a = $mysql->mqm( "SELECT `id` FROM `".$mysql->t_prefix."user_types` WHERE 1 ORDER BY `id` ASC" );
			$typeid = 1;
			while( $r = @mysql_fetch_assoc( $a ) ) {
				if( $r['id'] == $typeid ) {
					$typeid++;
				} else 
					break;
			}
			
			$phrase = $lang->addNewPhrase( $name, 1 );
			
			$mysql->mu( "INSERT INTO `".$mysql->t_prefix."user_types` VALUES(".$typeid.",".$phrase.",".$order.",0,'".$comment."');" );
			
		} else if( $query->gp( "moveup" ) ) {
			
			$id = $query->gp( "moveup" );
			$r = $mysql->mq( "SELECT `order` FROM `".$mysql->t_prefix."user_types` WHERE `id`=".$id );
			if( $r ) {
				$rr = $mysql->mq( "SELECT `id` FROM `".$mysql->t_prefix."user_types` WHERE `order`=".( $r['order'] - 1 ) );
				$mysql->mu( "UPDATE `".$mysql->t_prefix."user_types` SET `order`=".( $r['order'] - 1 )." WHERE `id`=".$id );
				$mysql->mu( "UPDATE `".$mysql->t_prefix."user_types` SET `order`=".( $r['order'] )." WHERE `id`=".$rr['id'] );
			}
			
		} else if( $query->gp( "movedown" ) ) {
			
			$id = $query->gp( "movedown" );
			$r = $mysql->mq( "SELECT `order` FROM `".$mysql->t_prefix."user_types` WHERE `id`=".$id );
			if( $r ) {
				$rr = $mysql->mq( "SELECT `id` FROM `".$mysql->t_prefix."modules` WHERE `order`=".( $r['order'] + 1 ) );
				$mysql->mu( "UPDATE `".$mysql->t_prefix."user_types` SET `order`=".( $r['order'] + 1 )." WHERE `id`=".$id );
				$mysql->mu( "UPDATE `".$mysql->t_prefix."user_types` SET `order`=".( $r['order'] )." WHERE `id`=".$rr['id'] );
			}
			
		} else if( $query->gp( "delete" ) ) {
			
			$id = $query->gp( "delete" );			
			$r = $mysql->mq( "SELECT * FROM `".$mysql->t_prefix."user_types` WHERE `id`=".$id );
			
			if( $r ) {
				$a = $mysql->mqm( "SELECT `id`,`order` FROM `".$mysql->t_prefix."user_types` WHERE `order`>".$r['order']." ORDER BY `order` ASC" );
				while( $rr = @mysql_fetch_assoc( $a ) )
					$mysql->mu( "UPDATE `".$mysql->t_prefix."user_types` SET `order`=".( $rr['order'] - 1 )." WHERE `id`=".$rr['id'] );
				$mysql->mu( "DELETE FROM `".$mysql->t_prefix."user_types` WHERE `id`=".$id );
				$mysql->mu( "DELETE FROM `".$mysql->t_prefix."profile_settings` WHERE `type`=".$id );
				$mysql->mu( "UPDATE `".$mysql->t_prefix."users` SET `level`=7 WHERE `level`=".$id );
			}
			
		} else if( $query->gp( "setdefault" ) ) {
			$mysql->mu( "UPDATE `".$mysql->t_prefix."user_types` SET `default`=0 WHERE `default`=1" );
			$mysql->mu( "UPDATE `".$mysql->t_prefix."user_types` SET `default`=1 WHERE `id`=".$query->gp( "setdefault" ) );
		}
		
		$selectedElement = 2;
		$t = "
			<h1>Список типов пользователей</h1>
			
			<a href=\"#\" onclick=\"getWindowContent( '".$mysql->settings['local_folder']."admin/".$path."', 1, 'newtype' );\">Создать новый тип</a><br><br>
			
			<table cellspacing=0 cellpadding=0 class='list_table' style='width: 800px;'>
				<tr class='list_table_header'>
					<td width=50>
						ID
					</td>
					<td width=100% style='text-align: left;'>
						Название и описание
					</td>
					<td width=110>
						Порядок
					</td>
					<td width=110>
						По умолчанию
					</td>
					<td width=110>
						Опции
					</td>
				</tr>
		";
		
		$counter = 0;
		$r = $mysql->mq( "SELECT `order` FROM `".$mysql->t_prefix."user_types` WHERE 1 ORDER BY `order` DESC" );
		$lastorder = $r ? $r['order'] : 1;
		$a = $mysql->mqm( "SELECT * FROM `".$mysql->t_prefix."user_types` WHERE 1 ORDER BY `order` ASC" );
		while( $r = @mysql_fetch_assoc( $a ) ) {
			$t .= "
				<tr class='list_table_element'>
					<td valign=top>
						".$r['id']."
					</td>
					<td style='text-align: left;'>
						<b><label id='type_name_".$r['id']."'>".$lang->getPh( $r['name'] )."</label></b>
						<p class='comment'>
							".str_replace( "\n", "<br>", $r['comment'] )."
						</p>
					</td>
					<td>
						".$r['order']."
						".( $r['order'] > 1 ? "<a href=\"".$mysql->settings['local_folder']."admin/".$path."/moveup".$r['id']."\"><img src=\"".$mysql->settings['local_folder']."images/up.gif\" style='".( $r['order'] == $lastorder ? "margin-top: 2px;" : "" )."position: absolute;margin-left: 3px;' title='Поднять выше' /></a>" : "" )."
						".( $r['order'] != $lastorder ? "<a href=\"".$mysql->settings['local_folder']."admin/".$path."/movedown".$r['id']."\"><img src=\"".$mysql->settings['local_folder']."images/down.gif\" style='".( $r['order'] > 1 ? "margin-top: 7px;" : "margin-top: 2px;" )."position: absolute;margin-left: 3px;' title='Опустить ниже' /></a>" : "" )."
					</td>
					<td>
						".( $r['default'] == 1 ? "Да" : "<a href=\"".$mysql->settings['local_folder']."admin/".$path."/setdefault".$r['id']."\">Установить</a>" )."
					</td>
					<td nowrap>
						<a href=\"#\" onclick=\"getWindowContent( '".$mysql->settings['local_folder']."admin/".$path."/typeid".$r['id']."', 2, 'type_settings' );\">Настройки</a>
							<label class='line_between_links'>|</label>
						<a href=\"".$mysql->settings['local_folder']."admin/".$path."/delete".$r['id']."\" onclick=\"if( !confirm( 'Вы уверены?' ) ) return false; return true;\">Удалить</a>
					</td>
				</tr>
			";
			$counter++;
		}
		
		$t .= "
			<tr class='list_table_footer'>
					<td colspan=5>
						Всего типов: ".$counter."
					</td>
				</tr>
		</table>
		
		<script>
			$( function() {
				$( '#type_settings' ).dialog( { closeOnEscape: false, zIndex: 3000, stack: true, minWidth: 500, minHeight: 400, maxWidth: 700, maxHeight: 600, width: 500, height: 400, autoOpen: false } );
				$( '#type_settings' ).dialog( 'option', 'titleBack', 'url(".$mysql->settings['local_folder'].$utils->javascript_files_path."jqui/themes/base/images/ui-bg_highlight-soft_75_cccccc_1x100.png)' );
				
				$( '#newtype' ).dialog( { closeOnEscape: false, zIndex: 3000, stack: true, minWidth: 350, minHeight: 400, maxWidth: 500, maxHeight: 600, width: 400, height: 400, autoOpen: false } );
				$( '#newtype' ).dialog( 'option', 'titleBack', 'url(".$mysql->settings['local_folder'].$utils->javascript_files_path."jqui/themes/base/images/ui-bg_highlight-soft_75_cccccc_1x100.png)' );
			} );
		</script>
		
		<div id='type_settings' title='Настройки типа'></div>
		<div id='newtype' title='Новый тип'></div>
		".$open;
		
		return $t;
	}
	
	function getAdminActionsScreen( &$selectedElement, $path )
	{
		global $mysql, $query, $utils, $admin, $lang;

		$type = $query->gp( "type" );
		$type = $type ? $type : 0;
		$user = $query->gp( "user" );
		$user = $user ? $user : 0;
		$action = $query->gp( "action" );
		$action = $action ? $action : 0;
		
		$types = "<option value=\"0\"".( $type == 0 ? " selected" : "" ).">Выберите тип</option>";
		foreach( $this->types as $k => $v ) {
			if( $k < 2 )
				continue;
			$types .= "<option value=\"".$k."\"".( $type == $k ? " selected" : "" ).">".$lang->gp( $v['name'] )."</option>";
		}
		
		$selectedElement = 2;
		$t = "
			<h1>Список действий пользователей системы</h1>
			
			<form method=POST action=\"".$mysql->settings['local_folder']."admin/".$path."/\" id='actions_form' style='padding: 0px; margin: 0px;'>
			<table border=0 style='width: 900px;'><tr><td>
				Выберите тип пользователей:<br>
				<select name=\"type\" id=\"type\" class='select_input' onchange=\"ge( 'actions_form' ).submit();\" style='width: 100%;'>
					".$types."
				</select>
			</td><td style='padding-left: 20px; '>
				Выберите пользователя из списка:<br>
				<select name=\"user\" id=\"user\" class='select_input' onchange=\"ge( 'actions_form' ).submit();\" style='width: 100%;'>
					".$this->getUsersSelectList( $type ? "Выберите пользователя" : "Для начала выберите тип", $user, $type ? "`level`=".$type : "level`=500" )."
				</select>
			</td>
			<td style='padding-left: 20px; '>
				Выберите вид действия:<br>
				<select name=\"action\" id=\"action\" class='select_input' onchange=\"ge( 'actions_form' ).submit();\" style='width: 100%;'>
					".$this->getSelectList( $this->actions_text, "Выберите действие", $action )."
				</select>
			</td>
			</tr></table>
			</form>
			
			<table cellspacing=0 cellpadding=0 class='list_table' style='width: 900px;'>
				<tr class='list_table_header'>
					<td width=30>
						ID
					</td>
					<td width=25%>
						Действие и сессия
					</td>
					<td width=25%>
						Пользователь
					</td>
					<td width=25%>
						IP адрес
					</td>
					<td width=25%>
						Дата
					</td>
					<td width=110>
						Дополнительно
					</td>
				</tr>
		";
		
		$where = '';
		if( $user && $type )
			$where = "`user`=".$user;
		else 
			$where = "`user`=-1";
		if( $action )
			$where .= ( $where ? " AND " : "" )."`action`=".$action;
			
		if( !$where )
			$where = "1";
		
		$counter = 0;
		$a = $mysql->mqm( "SELECT * FROM `".$mysql->t_prefix."user_actions` WHERE ".$where." ORDER BY `date` DESC, `action` ASC, `user` ASC" );
		while( $r = @mysql_fetch_assoc( $a ) ) {
			$us = $this->getUserById( $r['user'] );
			$t .= "
				<tr class='list_table_element'>
					<td valign=top>
						".$r['id']."
					</td>
					<td style='text-align: left;'>
						<b>".$this->actions_text[$r['action']]."</b>
						<p class='comment'>
							SID: ".( $r['sid'] ? $r['sid'] : "Нет" )."
						</p>
					</td>
					<td>
						".( $us ? $us['name']." (".$us['ulogin'].")" : "Не указан" )."
					</td>
					<td>
						".$r['ip']."
					</td>
					<td>
						".date( "d/m/Y H:i:s", $r['date'] )."
					</td>
					<td>
						".( $r['add'] ? $r['add'] : "&nbsp;" )."
					</td>
				</tr>
			";
			$counter++;
		}
		
		$t .= "
			<tr class='list_table_footer'>
					<td colspan=6>
						Всего действий: ".$counter."
					</td>
				</tr>
		</table>
		
		";
		
		return $t;
	}
	
	function getElementValueByName( $from, $name )
	{
		foreach( $from as $v )
			if( $v['name'] == $name )
				return $v['value'];
	}
	
	function getElementIdByName( $from, $name )
	{
		foreach( $from as $v )
			if( $v['name'] == $name )
				return $v['id'];
	}
	
	function getElementById( $from, $id )
	{
		foreach( $from as $v )
			if( $v['id'] == $id )
				return $v;
	}
	
	function getExternal( $wt, $link )
	{
		global $query, $mysql;
		
		$type_id = $query->gp( "typeid" );
		
		if( $wt == 1 ) { // Новый тип
			
			return $this->getExternalNewType( $link );
			
		} else if( $wt == 2 ) { // Редактирование типа
			
			return $this->getExternalTypeSettings( $type_id, $link );
			
		} else if( $wt == 3 ) { // Новый пользователь
			
			return $this->getExternalNewUser( $link );
			
		} else if( $wt == 4 ) { // Редактирование пользователя
			
			return $this->getExternalEditUser( $link );
			
		} else if( $wt == 5 ) { // Просмотр информции о пользователе
			
			return $this->getExternalViewUser( $link );
			
		}
		
		return "Unknown type query";
	}
	
	function getExternalNewType( $link )
	{
		global $query, $mysql, $lang;
		
		$inner = "
				<p>
					Название типа: <label class='red'>*</label> (добавление перевода возможно в режиме редактирования типа)<br>
					<input type=text name=\"t_name\" id=\"t_name\" value=\"\" class='text_input' />
				</p>
				<p>
					Описание типа:<br>
					<textarea name=\"t_comment\" id=\"t_comment\" class='textarea_input' style='width: 100%; height: 60px;'></textarea>
				</p>
			";
			
		return "
				<h1 align=left>Создание нового типа</h1>
				
				<form action=\"".$mysql->settings['local_folder']."admin/".$link."/createtype\" method=POST onsubmit=\"
					if( $( '#t_name' ).attr( 'value' ) == '' ) { 
						alert( 'Укажите название типа' ); 
						return false; 
					}
					$( '#process' ).attr( 'value', 1 );
					return true;
				\" style='text-align: left;'>
					".$inner."
					<div style='width: 300px; border-top: 1px solid #aaa; padding-top: 5px;'>
						<input type=hidden name=\"process\" id=\"process\" value=\"0\" />
						<input type=submit value=\"Создать\" class='button_input' title='Создать тип и закрыть окно' />
					</div>
				</form>
		";
	}
	
	function getExternalTypeSettings( $type_id, $link )
	{
		global $query, $mysql, $lang, $utils;
		
		$m = $mysql->mq( "SELECT * FROM `".$mysql->t_prefix."user_types` WHERE `id`=".$type_id );
		if( !$m ) {
			return "Unknown type id";
		}
		
		$inner = "
				<p>
					Название типа:<br>
					<a href='#' title='Редактирование названия в отдельном окне' class='forallunknowns' onclick=\"
						getWindowContent( '".$mysql->settings['local_folder']."admin/langs/phraseid".$m['name']."/langid1/ajax/whattochange!tochange*type_name_".$type_id."/toclosename!phrase_edit', 3, 'phrase_edit' );
					\">Редактирование в отдельном окне</a>
				</p>
				<p>
					Описание типа:<br>
					<textarea name=\"t_comment\" id=\"t_comment\" class='textarea_input' style='width: 100%; height: 60px;'>".$m['comment']."</textarea>
				</p>
			";
			
		return "
				<h1 align=left>Настройки типа <b>\"<label id=\"tochange\">".$lang->getPh( $m['name'] )."</label>\"</b></h1>
				
				<form action=\"".$mysql->settings['local_folder']."admin/".$link."/edit".$type_id."\" method=POST onsubmit=\"
					$( '#process' ).attr( 'value', 1 );
					return true;
				\" style='text-align: left;'>
					".$inner."
					<div style='width: 300px; border-top: 1px solid #aaa; padding-top: 5px;'>
						<input type=hidden name=\"process\" id=\"process\" value=\"0\" />
						<input type=submit value=\"Сохранить\" class='button_input' title='Сохранить измененные данные и закрыть окно' />
					</div>
				</form>
				
				<script>
					$( function() {
						$( '#phrase_edit' ).dialog( { closeOnEscape: false, zIndex: 3000, stack: true, minWidth: 550, minHeight: 400, maxWidth: 700, maxHeight: 800, width: 600, height: 500, autoOpen: false } );
						$( '#phrase_edit' ).dialog( 'option', 'titleBack', 'url(".$mysql->settings['local_folder'].$utils->javascript_files_path."jqui/themes/base/images/ui-bg_highlight-soft_75_cccccc_1x100.png)' );
					} );
				</script>
		
				<div id='phrase_edit' title='Редактирование названия типа'></div>
		";
	}
	
	function getExternalNewUser( $link )
	{
		global $query, $mysql, $lang, $main, $admin;
		
		$utype = $query->gp( "utype" );
		
		$t = "";
		foreach( $this->types as $k => $v ) {
			if( $admin->userLevel != 1 && $k <= 2 )
				continue;
			$t .= "<option value=\"".$k."\">".$lang->getPh( $v['name'] )."</option>";
		}
		
		$inner = "
				<p>
					Имя (никнейм) пользователя: <label class='red'>*</label><br>
					<input type=text name=\"u_name\" id=\"u_name\" value=\"\" class='text_input' />
				</p>
				<p>
					Логин пользователя: <label class='red'>*</label> (для обычного пользователя это E-Mail)<br>
					<input type=text name=\"u_login\" id=\"u_login\" value=\"\" class='text_input' />
				</p>
				<p>
					Пароль пользователя: <label class='red'>*</label><br>
					<input type=text name=\"u_pass\" id=\"u_pass\" value=\"\" class='text_input' />&nbsp;&nbsp;<a href=\"#\" onclick=\"getGeneratedPassword( 'u_pass' );return false;\">Сгенерировать</a>
				</p>
				<p>
					Тип пользователя:<br>
					<select name=\"u_type\" id=\"u_type\" class='select_input'>
						".$t."
					</select>
				</p>
				<p>
					Статус пользователя:<br>
					<select name=\"u_status\" id=\"u_status\" class='select_input'>
						<option value=\"1\">Активен</option>
						<option value=\"10\">Блок</option>
					</select>
				</p>
				<p>
					Комментарий: (Чисто админская фишка, сюда можно записать пароль, чтобы не забыть)<br>
					<textarea name=\"u_comment\" id=\"u_comment\" class='textarea_input' style='width: 100%; height: 60px;'></textarea>
				</p>
			";
			
		return "
				<h1 align=left>Создание нового пользователя</h1>
				
				<form action=\"".$mysql->settings['local_folder']."admin/".$link."/createuser\" method=POST onsubmit=\"
					if( $( '#u_name' ).attr( 'value' ) == '' ) { 
						alert( 'Укажите имя пользователя' ); 
						return false; 
					}
					if( $( '#u_login' ).attr( 'value' ) == '' ) { 
						alert( 'Укажите логин пользователя' ); 
						return false; 
					}
					if( $( '#u_pass' ).attr( 'value' ) == '' ) { 
						alert( 'Укажите пароль пользователя' ); 
						return false; 
					}
					$( '#process' ).attr( 'value', 1 );
					return true;
				\" style='text-align: left;'>
					".$inner."
					<div style='width: 300px; border-top: 1px solid #aaa; padding-top: 5px;'>
						<input type=hidden name=\"process\" id=\"process\" value=\"0\" />
						<input type=hidden name=\"utype\" id=\"utype\" value=\"".$utype."\" />
						<input type=submit value=\"Создать\" class='button_input' title='Создать пользователя и закрыть окно' />
					</div>
				</form>
		";
	}
	
	function getExternalEditUser( $link )
	{
		global $query, $mysql, $lang, $main, $admin;
		
		$text_search = $query->gp( "text_search" );
		$utype = $query->gp( "utype" );
		
		$page = $query->gp( "page" );
		$page = $page ? $page : 0;
		
		$userid = $query->gp( "userid" );
		$u = $mysql->mq( "SELECT * FROM `".$mysql->t_prefix."users` WHERE `id`=".$userid );
		if( !$u ) {
			return "Unknown user id";
		}
		
		$userProfile = $this->getUserProfile( $userid );
		
		$t = "";
		foreach( $this->types as $k => $v ) {
			if( $admin->userLevel != 1 && $k <= 2 )
				continue;
			$t .= "<option value=\"".$k."\"".( $k == $u['level'] ? " selected" : "" ).">".$lang->getPh( $v['name'] )."</option>";
		}
		
		$inner = "
				<p>
					Имя (никнейм) пользователя (видно только из админки): <label class='red'>*</label><br>
					<input type=text name=\"u_name\" id=\"u_name\" value=\"".$u['name']."\" class='text_input' />
				</p>
				<p>
					Логин пользователя: <label class='red'>*</label> (для обычного пользователя это E-Mail)<br>
					<input type=text name=\"u_login\" id=\"u_login\" value=\"".$u['ulogin']."\" class='text_input' />
				</p>
				<p>
					Новый пароль пользователя: (укажите только, если хотите сменить)<br>
					<input type=text name=\"u_pass\" id=\"u_pass\" value=\"\" class='text_input' />&nbsp;&nbsp;<a href=\"#\" onclick=\"getGeneratedPassword( 'u_pass' );return false;\">Сгенерировать</a>
				</p>
				<p>
					Тип пользователя:<br>
					<select name=\"u_type\" id=\"u_type\" class='select_input'>
						".$t."
					</select>
				</p>
				<p>
					Статус пользователя:<br>
					<select name=\"u_status\" id=\"u_status\" class='select_input'>
						<option value=\"1\"".( $u['status'] == 1 ? " selected" : "" ).">Активен</option>
						<option value=\"10\"".( $u['status'] == 10 ? " selected" : "" ).">Блок</option>
					</select>
				</p>
				<p>
					Комментарий: (Чисто админская фишка, сюда можно записать пароль, чтобы не забыть)<br>
					<textarea name=\"u_comment\" id=\"u_comment\" class='textarea_input' style='width: 100%; height: 60px;'>".$u['comment']."</textarea>
				</p>
				<p>
					Код подтверждения: (Удалив можно позволить пользователю входить в систему)<br>
					<input type=text name=\"u_auth\" id=\"u_auth\" value=\"".$u['auth']."\" class='text_input' />
				</p>
				".( $u['level'] >= $this->defaultType ? "
					<div style='margin-top: 5px; padding-bottom: 5px; border-top: 1px solid #ff0000;'></div>
					<h1>Редактирование профиля</h1>
					<p>
						Фамилия:<br>
						<input type=text name=\"profile_surname\" id=\"profile_surname\" value=\"".$userProfile['surname']."\" class='text_input' />
					</p>
					<p>
						Имя:<br>
						<input type=text name=\"profile_name\" id=\"profile_name\" value=\"".$userProfile['name']."\" class='text_input' />
					</p>
					<p>
						Отчество:<br>
						<input type=text name=\"profile_secondname\" id=\"profile_secondname\" value=\"".$userProfile['secondname']."\" class='text_input' />
					</p>
					<p>
						Телефон:<br>
						<input type=text name=\"profile_phone\" id=\"profile_phone\" value=\"".$userProfile['phone']."\" class='text_input' />
					</p>
					<p>
						Адрес доставки:<br>
						<input type=text name=\"profile_adress\" id=\"profile_adress\" value=\"".( isset( $userProfile['adress'] ) ? $userProfile['adress'] : "" )."\" class='text_input' />
					</p>
				" : "" )."
			";
			
		return "
				<h1 align=left>Редактирование пользователя</h1>
				
				<form action=\"".$mysql->settings['local_folder']."admin/".$link."/edit".$userid."\" method=POST onsubmit=\"
					if( $( '#u_name' ).attr( 'value' ) == '' ) { 
						alert( 'Укажите имя пользователя' ); 
						return false; 
					}
					if( $( '#u_login' ).attr( 'value' ) == '' ) { 
						alert( 'Укажите логин пользователя' ); 
						return false; 
					}
					$( '#process' ).attr( 'value', 1 );
					return true;
				\" style='text-align: left;'>
					".$inner."
					<div style='width: 300px; border-top: 1px solid #aaa; padding-top: 5px;'>
						<input type=hidden name=\"process\" id=\"process\" value=\"0\" />
						<input type=hidden name=\"text_search\" id=\"text_search\" value=\"".$text_search."\" />
						<input type=hidden name=\"utype\" id=\"utype\" value=\"".$utype."\" />
						<input type=hidden name=\"page\" id=\"page\" value=\"".$page."\" />
						<input type=submit value=\"Сохранить\" class='button_input' title='Сохранить изменения и закрыть окно' />
					</div>
				</form>
		";
	}
	
	function getExternalViewUser( $link )
	{
		global $query, $mysql, $lang, $main, $admin;
		
		$userid = $query->gp( "userid" );
		$u = $mysql->mq( "SELECT * FROM `".$mysql->t_prefix."users` WHERE `id`=".$userid );
		if( !$u ) {
			return "Unknown user id";
		}
		
		$userProfile = $this->getUserProfile( $userid );
		
		$inner = "
				<p>
					Имя (никнейм) пользователя: <b>".$u['name']."</b>
				</p>
				<p>
					Логин пользователя: <b>".$u['ulogin']."</b>
				</p>
				<p>
					Тип пользователя: <b>".$lang->gp( $this->types[$u['level']]['name'] )."</b>
				</p>
				<p>
					Статус пользователя: <b>".( $u['status'] == 1 ? "Активен" : ( $u['status'] == 10 ? "Блок" : "" ) )."</b>
				</p>
				".( $u['level'] >= $this->defaultType ? "
					<div style='margin-top: 5px; padding-bottom: 5px; border-top: 1px solid #ff0000;'></div>
					<h1>Профиль</h1>
					<p>
						Фамилия: <b>".$userProfile['surname']."</b>
					</p>
					<p>
						Имя: <b>".$userProfile['name']."</b>
					</p>
					<p>
						Отчество: <b>".$userProfile['secondname']."</b>
					</p>
					<p>
						Телефон: <b>".$userProfile['phone']."</b>
					</p>
					<p>
						Адрес доставки: <b>".( isset( $userProfile['adress'] ) ? $userProfile['adress'] : "" )."</b>
					</p>
				" : "" )."
			";
			
		return "
				<h1 align=left>Просмотр данных пользователя</h1>
				
				<form action=\"/\" method=POST onsubmit=\"return false;\" style='text-align: left;'>
					".$inner."
					<div style='width: 300px; border-top: 1px solid #aaa; padding-top: 5px;'>
						<input type=button value=\"Закрыть\" class='button_input' onclick=\"$( '#user_view' ).dialog( 'close' );\" />
					</div>
				</form>
		";
	}
	
	function getTypesSelectList( $empty_string = "Нет", $selected = 0 )
	{
		global $mysql, $lang;

		$t = "<option value=0".( !$selected ? " selected" : "" ).">".$empty_string."</option>";
		$a = $mysql->mqm( "SELECT * FROM `".$mysql->t_prefix."user_types` WHERE 1 ORDER BY `order` ASC" );
		while( $r = @mysql_fetch_assoc( $a ) )
			$t .= "<option value=".$r['id'].( $selected == $r['id'] ? " selected" : "" ).">".$lang->getPh( $r['name'] )."</option>";
		return $t;
	}
	
	function getUsersSelectList( $empty_string = "Нет", $selected = 0, $where = "`level`>2" )
	{
		global $mysql, $lang;

		$t = "<option value=0".( !$selected ? " selected" : "" ).">".$empty_string."</option>";
		$a = $mysql->mqm( "SELECT * FROM `".$mysql->t_prefix."users` WHERE ".$where." ORDER BY `name` ASC" );
		while( $r = @mysql_fetch_assoc( $a ) )
			$t .= "<option value=".$r['id'].( $selected == $r['id'] ? " selected" : "" ).">".( is_numeric( $r['name'] ) ? $lang->getPh( $r['name'] ) : $r['name'] )." (".$r['ulogin'].")</option>";
		return $t;
	}
	
	function getSelectList( $from, $empty_string = "Нет", $selected = 0 )
	{
		global $mysql, $lang;

		$t = "<option value=0".( !$selected ? " selected" : "" ).">".$empty_string."</option>";
		foreach( $from as $k => $r ) {
			$t .= "<option value=".$k.( $selected == $k ? " selected" : "" ).">".$r."</option>";
		}
		return $t;
	}
        
        function exportUsers()
        {
            global $mysql;
            
            $t = "ID;Login;Name;Register date;Phone;BirthDay;Index;City;Adress;Additioncal info
";
            $a = $mysql->mqm( "SELECT * FROM `".$mysql->t_prefix."users` WHERE `level`=3 ORDER BY `name` ASC" );
            while( $r = @mysql_fetch_assoc( $a ) ) {
                $profile = $this->getUserProfile( $r['id'] );
                $t .= $r['id'].";".$r['ulogin'].";".$r['name'].";".date( 'd/m/Y', $r['rdate'] ).";".( isset( $profile['phone'] ) ? $profile['phone'] : '' ).";".( isset( $profile['bdate	'] ) ? $profile['bdate'] : '' ).";".( isset( $profile['index'] ) ? $profile['index'] : '' ).";".( isset( $profile['city'] ) ? $profile['city'] : '' ).";".( isset( $profile['adress'] ) ? $profile['adress'] : '' ).";".( isset( $profile['comments'] ) ? $profile['comments'] : '' )."
";
            }
                @ob_end_clean(); 
		@ini_set( 'zlib.output_compression', 'Off' );
		
		header( 'Pragma: ' );
		header( 'Expires: Sat, 26 Jul 1997 05:00:00 GMT' ); 
		header( 'Last-Modified: '.gmdate( 'D, d M Y H:i:s' ).' GMT' ); 
		header( 'Cache-Control: no-store, no-cache, must-revalidate' ); 
		header( 'Cache-Control: post-check=0, pre-check=0, max-age=0' );
		header( 'Content-Transfer-Encoding: none' );
		
		header( "Content-Type: text/csv; name=\"users.csv\"" );
                header( "Content-Disposition: inline; filename=\"users.csv\"" );
		
		echo iconv( "UTF-8", "WINDOWS-1251", $t );
        }
}

?>
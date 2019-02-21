<?php

if( !defined( "in_ochki" ) && !defined( "in_ochki_admin" ) ) die( "You can't access this file directly" );

class Ochki
{
	var $users, $modules = null, $templates, $listings, $properties;
	
	var $isIE = false;
	var $isChrome = false;
	var $isFirefox = false;
	var $isOpera = false;
	var $isSafari = false;
	
	function init()
	{
		global $lang;
		
		$ua = @getenv( HTTP_USER_AGENT );
		$this->isIE = stripos( strtolower( $ua ), "msie" ) !== false ? true : false;
		$this->isChrome = stripos( strtolower( $ua ), "chrome" ) !== false ? true : false;
		$this->isFirefox = stripos( strtolower( $ua ), "firefox" ) !== false ? true : false;
		$this->isOpera = stripos( strtolower( $ua ), "opera" ) !== false ? true : false;
		$this->isSafari = stripos( strtolower( $ua ), "safari" ) !== false ? true : false; 
		
		if( !$this->modules ) {
			if( !file_exists( ROOT_PATH."base/modules/modulesmanager.php" ) ) {
				die( $lang->getPh( 1 ) );
			}
			@include_once( ROOT_PATH."base/modules/modulesmanager.php" );
			$this->modules = new ModulesManager();
			$this->modules->init();
		}
		
		if( !file_exists( ROOT_PATH."base/users.php" ) ) {
			die( $lang->getPh( 1 ) );
		}
		@include_once( ROOT_PATH."base/users.php" );
		$this->users = new Users();
		$this->users->init();
		
		if( !file_exists( ROOT_PATH."base/templates.php" ) ) {
			die( $lang->getPh( 1 ) );
		}
		@include_once( ROOT_PATH."base/templates.php" );
		$this->templates = new Templates();
		$this->templates->init();
		
		if( !file_exists( ROOT_PATH."base/listings.php" ) ) {
			die( $lang->getPh( 1 ) );
		}
		@include_once( ROOT_PATH."base/listings.php" );
		$this->listings = new Listings();
		$this->listings->init();
		
		if( !file_exists( ROOT_PATH."base/properties.php" ) ) {
			die( $lang->getPh( 1 ) );
		}
		@include_once( ROOT_PATH."base/properties.php" );
		$this->properties = new Properties();
		$this->properties->init();
	}
	
	function checkSimpleReq()
	{
		global $query, $lang, $mysql, $utils;
		
		if( $query->gp( "clickset" ) ) {
			$id = $query->gp( "clickset" );
			$r = $mysql->mq( "SELECT `clicks`,`link` FROM `".$mysql->t_prefix."banners` WHERE `view`=1 AND `id`=".$id );
			if( $r ) {
				$mysql->mu( "UPDATE `".$mysql->t_prefix."banners` SET `clicks`=".( $r['clicks'] + 1 )." WHERE `id`=".$id );
				header( "Location: ".$r['link'] );
				exit;
			}
		}
	}
	
	function putHeaders()
	{
		global $query, $mysql;
		
		$offset = 24 * 60 * 60 * 30;
                $expire = "Expires: " . gmdate ("D, d M Y H:i:s", time() + $offset) . " GMT";
                header( $expire );
		//header( 'Last-Modified: '.gmdate('D, d M Y H:i:s' ).' GMT' );
		header( 'Cache-Control: no-cache, must-revalidate' );
		//header( 'Pragma: no-cache' );
		header( 'Content-type: text/html; charset=utf-8' );
		
		if( $query->gp( "yandex_payments" ) || strpos( $_SERVER['REQUEST_URI'], "yandex_payments" ) !== false ) {
			$t = $this->modules->gmi( "yandex_payments" )->process();
			if( $t ) {
				echo $t;
				exit;
			}
		}
		
		if( $query->gp( "sid_access" ) ) {
			
			$entry = $mysql->mq( "SELECT * FROM `".$mysql->t_prefix."sid_access` WHERE `access_sid`='".$query->gp( "sid_access" )."'" );
			if( $entry && !$entry['user'] ) {
				
				$ses = $mysql->mq( "SELECT * FROM `".$mysql->t_prefix."user_ses` WHERE `sid`='".$entry['sid']."'" );
				$ip = @htmlspecialchars( @addslashes( @getenv( REMOTE_ADDR ) ? @getenv( REMOTE_ADDR ) : @getenv( HTTP_X_FORWARDED_FOR ) ) );
				$currentAgent = @htmlspecialchars( @addslashes( @getenv( HTTP_USER_AGENT ) ) );
				$mysql->mu( "UPDATE `".$mysql->t_prefix."user_ses` SET `last`=".time().", `ip`='".$ip."', `user_agent`='".$currentAgent."' WHERE `id`=".$ses['id'] );
				
				setcookie( "ochki_websid", '', time() + ( 3600 * 24 * 365 ), $mysql->settings['local_folder'], str_replace( "www.", "", strtolower( $_SERVER['HTTP_HOST'] ) ) );
				setcookie( "ochki_websid", $entry['sid'], time() + ( 3600 * 24 * 365 ), $mysql->settings['local_folder'], str_replace( "www.", "", strtolower( $_SERVER['HTTP_HOST'] ) ) );
				header( "Location: ".$mysql->settings['local_folder']."basket" );
				exit;
				
			} else if( $entry && $entry['user'] ) {
				
				@setcookie( "ochki_webusid", $entry['sid'], time() + $mysql->settings['user_session_length'], $mysql->settings['local_folder'], str_replace( "www.", "", strtolower( $_SERVER['HTTP_HOST'] ) ) );
				$ses = $mysql->mq( "SELECT * FROM `".$mysql->t_prefix."user_ses` WHERE `sid`='".$entry['sid']."' AND `user`=".$entry['user'] );
				$ip = @htmlspecialchars( @addslashes( @getenv( REMOTE_ADDR ) ? @getenv( REMOTE_ADDR ) : @getenv( HTTP_X_FORWARDED_FOR ) ) );
				$currentAgent = @htmlspecialchars( @addslashes( @getenv( HTTP_USER_AGENT ) ) );
				$mysql->mu( "UPDATE `".$mysql->t_prefix."user_ses` SET `last`=".time().", `ip`='".$ip."', `user_agent`='".$currentAgent."' WHERE `id`=".$ses['id'] );
				$l = $this->users->getUserloginById( $entry['user'] );
				@setcookie( "ochki_webulogin", $l, time() + 31536000, $mysql->settings['local_folder'], str_replace( "www.", "", strtolower( $_SERVER['HTTP_HOST'] ) ) );
				header( "Location: ".$mysql->settings['local_folder']."basket" );
				exit;
			}
			
		}
                
		if( count( $query->newGET ) && !count( $_POST ) ) {
			$c = 0;
			foreach( $this->modules->modules as $k => $v ) {
				if( $query->gp( $v['instance']->dbinfo['local'] ) )
					$c++;
			}
			if( $query->gp( "activatelogin" ) || $query->gp( "payment_ready" ) )
				$c++;
			if( !$c ) {
				header('HTTP/1.1 404 Not Found');
				$this->templates->four = true;
			}
		}
		
		$this->modules->processHeaders();
	}
	
	function setCookies()
	{
		$this->modules->processSetCookies();
	}
	
	function printHEAD()
	{
		return "<!DOCTYPE html>
		<html>		
		<head>
			".
			$this->templates->printHEAD().
			$this->modules->printHEAD()
			."
		</head>
		
		";
	}
	
	function printHTMLHeader()
	{
		$t = "
		<body onload=\"".$this->modules->printBodyOnload()."\">
		".$this->templates->printHTMLHeader()."
		";
		
		return $t;
	}
	
	function printHTMLContext()
	{
		$t = "
		".$this->templates->printHTMLContext()."
		";
		
		return $t;
	}
	
	function printHTMLFooter()
	{
		$t = "
		".$this->templates->printHTMLFooter()."
		".$this->modules->printHTMLFooter()."
		</body>
		</html>
		";
		
		return $t;
	}
}
<?php

if( !defined( "in_ochki" ) && !defined( "in_ochki_admin" ) ) die( "You can't access this file directly" );

class Query
{
	var $queryString = "/";

	var $newGET = array();	
	var $uri = "";
	
	var $google_code = "ABQIAAAAyNjdWHRn08eBYpabhaeeTRTdaDL6abMr0DAAkp4QLbVAX3XJ2xTcuY18qX_UBC3zmra3xupTSbQ39Q";
	var $microsoft_bing_code = "EF0A49B877AEF545D1668FC1D0CB747125AE6911";

	function init()
	{
		$this->uri = isset( $_SERVER['PATH_INFO'] ) && ( isset( $_SERVER['REQUEST_URI'] ) && !$_SERVER['REQUEST_URI'] ) ? $_SERVER['PATH_INFO'] : $_SERVER['REQUEST_URI'];

		if( count( $_GET ) > 0 ) {
			foreach( $_GET as $k => $v )
				$this->newGET[strtolower( $this->fm( $k ) )] = $this->fm( $v );
			foreach( $_POST as $k => $v ) {
				$k = strtolower( $this->fm( $k ) );
				if( !isset( $this->newGET[$k] ) )
					$this->newGET[$k] = $this->fm( $v );
			}
			//return;
		}

		$tt = explode( "?", $this->uri );
		$this->uri = $tt[0];
		$uEl = array_values( array_filter( explode( '/', $this->uri ) ) );

		for( $a = 0; $a < count( $uEl ); $a++ ) {
			$elName = "";
			$c = 0;
			while( true ) {
				if( !isset( $uEl[$a][$c] ) || is_numeric( $uEl[$a][$c] ) )
					break;
				if( !isset( $uEl[$a][$c] ) || $uEl[$a][$c] == '!' ) {
					$c++;
					break;
				}
				$elName .= $uEl[$a][$c++];
			}
			$elValue = "";
			if( $c + 1 > strlen( $uEl[$a] ) ) {
				$elValue = "1";
			} else {
				while( true ) {
					if( !isset( $uEl[$a][$c] ) )
						break;
					$elValue .= $uEl[$a][$c++];
				}
			}
			$this->newGET[strtolower( $this->fm( $elName ) )] = $this->fm( $elValue );
		}

		foreach( $_POST as $k => $v ) {
			$k = strtolower( $this->fm( $k ) );
			if( !isset( $this->newGET[$k] ) )
				$this->newGET[$k] = $this->fm( $v );
		}
	}
	
	function checkSimpleReq()
	{
		if( $this->gp( "getservertime" ) ) {
			echo date( "d-m-Y H:i" );
			exit;
		} else if( $this->gp( "generate_new_password" ) ) {
			
    		$res = '';
    		$len = 8;
    		$useChars = '23456789ABCDEFGHKMNPQRSTUVWXYZabcdefghkmnpqrstuvwxyz';
    		$useChars .= $useChars;
    		for( $i = 0; $i < $len; $i++ )
       			$res .= $useChars[mt_rand( 0, strlen( $useChars ) - 1 )];
    		echo  $res;
				exit;
				
		} else if( $this->gp( "captcha" ) ) {
			
			if( file_exists( ROOT_PATH."base/mysql.php" ) ) {
				@include_once( ROOT_PATH."base/mysql.php" );
				$mysql = new MySQL();
				$mysql->init();
			}
			
			if( file_exists( ROOT_PATH."base/utils.php" ) ) {
				@include_once( ROOT_PATH."base/utils.php" );
				$utils = new Utils();
			}
			
			$utils->getCapcha( $this->gp( "captcha" ), $mysql );
			exit;
			
		} else if( $this->gp( "dateeee" ) ) {
			
			echo date( "d/m/Y", 1415209614 );
			exit;
		
		} else if( $this->gp( "deleteallsessions" ) ) {
			
			
			@include_once( ROOT_PATH."base/mysql.php" );
			$mysql = new MySQL();
			$mysql->init();
			
			$a = $mysql->mqm( "SELECT `id`,`sid` FROM `shop_real`.`shop_user_ses` WHERE 1" );
			while( $r = @mysql_fetch_assoc( $a ) ) {
				if( $mysql->mq( "SELECT `count` FROM `shop_real`.`shop_basket` WHERE `session`='".$r['sid']."'" ) || $mysql->mq( "SELECT `id` FROM `shop_real`.`shop_order` WHERE `sid`='".$r['sid']."'" ) )
					continue;
				$mysql->mu( "DELETE FROM `shop_real`.`shop_user_ses` WHERE `id`=".$r['id'] );
			}
			
			$a = $mysql->mqm( "SELECT `id`,`sid` FROM `shop_real`.`shop_seen` WHERE 1" );
			while( $r = @mysql_fetch_assoc( $a ) ) {
				if( $mysql->mq( "SELECT `id` FROM `shop_real`.`shop_user_ses` WHERE `sid`='".$r['sid']."'" ) )
					continue;
				$mysql->mu( "DELETE FROM `shop_real`.`shop_seen` WHERE `id`=".$r['id'] );
			}
			
			echo "1";
			exit;
			
		} else if( $this->gp( "correctdio" ) ) {
			
			
			@include_once( ROOT_PATH."base/mysql.php" );
			$mysql = new MySQL();
			$mysql->init();
			
			$a = $mysql->mqm( "SELECT * FROM `don`.`b_iblock_element` WHERE (`DETAIL_TEXT`<>'' OR `SEARCHABLE_CONTENT`<>'')" );
			while( $r = @mysql_fetch_assoc( $a ) ) {
				echo strip_tags( $r['DETAIL_TEXT'] ? $r['DETAIL_TEXT'] : $r['SEARCHABLE_CONTENT'] );
                                echo "<br>---<br/>";
			}
			exit;
			
		}		
	}

	function getProperty( $prop, $changeQuot = false )
	{
		if( !isset( $this->newGET[$prop] ) )
			return false;
			
		$t = $this->newGET[$prop];
		
		if( $changeQuot ) {
			$t = str_replace( '"', '&quot;', str_replace( '\\', '', $t ) );
		}
		
		return @trim( $t );
	}
	
	function gp( $prop, $changeQuot = false )
	{
		return $this->getProperty( $prop, $changeQuot );
	}
	
	function gp_letqbe( $prop, $phrase = '' )
	{
		if( $phrase )
			return @str_replace( '\\', '', $phrase );
		
		if( !isset( $this->newGET[$prop] ) )
			return false;
			
		return @str_replace( '\\', '', $this->newGET[$prop] );
	}
	
	function gp_post( $prop, $maxlen = 0 )
	{
		/*return $this->fm( isset( $_POST[$prop] )
					? ( $maxlen ? 
						( strlen( $_POST[$prop] ) === $maxlen ? $_POST[$prop] : null )
								: $_POST[$prop] 
					  ) 
					: null );
		*/
		$t = isset( $_POST[$prop] ) ? $_POST[$prop] : null;
		if( !$t )
			return $t;
		
		if( $maxlen && mb_strlen( $t, "UTF-8" ) > $maxlen )
			$t = mb_substr( $t, 0, $maxlen, "UTF-8" );
		
		return str_replace( "'", "", str_replace( '"', "&quot;", $t ) );
	}
	
	function setProperty( $prop, $value )
	{
		$this->newGET[$prop] = $value;
	}

	function fm( $str )
	{
		return @htmlspecialchars( @strip_tags( @addslashes( $str ) ) );
	}
}

?>

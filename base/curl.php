<?php

if( !defined( "in_ochki" ) && !defined( "in_ochki_admin" ) ) die( "You can't access this file directly" );

class Curl
{
	var $errorNOURL 			= -1;
	var $errorNOCURL 			= -2;
	var $errorNOINIT 			= -3;
	var $errorNOPARAMS 			= -4;
	var $errorNOEXEC 			= -5;
	
	var $lastError 				= "";
	
	var $paramURL				= 'url';
	var $paramSSL				= 'ssl';
	var $paramPOST				= 'post';
	var $paramUSERAGENT			= 'ua';
	var $paramHNTR				= 'headerneedtoreturn';
	var $paramCOOKIENEED		= 'cookieneed';
	var $paramREFERER			= 'referer';
	var $paramHEADERS			= 'headers';
	var $paramCURRENTSESSION	= 'currentsession';
	
	var $paramRESULT			= 'result';
	
	function processConnect( $params )
	{
		if( !$params )			
			return $this->errorNOPARAMS;
			
		if( !function_exists( "curl_init") )
			return $this->errorNOCURL;
			
		if( !isset( $params[$this->paramURL] ) || !$params[$this->paramURL] )
			return $this->errorNOURL;
			
		if( !isset( $params[$this->paramUSERAGENT] ) || !$params[$this->paramUSERAGENT] )
			$params[$this->paramUSERAGENT] = $_SERVER['HTTP_USER_AGENT'];
			
		$ch = @curl_init();
		if( $ch === false ) {
			$this->lastError = @curl_error( $ch );
			return $this->errorNOINIT;
		}
		
		@curl_setopt( $ch, CURLOPT_URL, $params[$this->paramURL] );
		@curl_setopt( $ch, CURLOPT_HEADER, isset( $params[$this->paramHNTR] ) && $params[$this->paramHNTR] ? 1 : 0 );
		@curl_setopt( $ch, CURLOPT_FAILONERROR, 1 );
		@curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1 );
		@curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
		@curl_setopt($ch, CURLOPT_USERAGENT, $params[$this->paramUSERAGENT] );
		
		if( isset( $params[$this->paramSSL] ) && $params[$this->paramSSL] ) {
			@curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
			@curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, 2 );
		}
		
		if( !isset( $params[$this->paramCURRENTSESSION] ) || !$params[$this->paramCURRENTSESSION] )
			$params[$this->paramCURRENTSESSION] = md5( time().$params[$this->paramURL] );
		
		if( isset( $params[$this->paramCOOKIENEED] ) && $params[$this->paramCOOKIENEED] ) {
			@curl_setopt( $ch, CURLOPT_COOKIEJAR, dirname( __FILE__ )."/tmp/cookie_".$params[$this->paramCURRENTSESSION].".txt" );
			@curl_setopt( $ch, CURLOPT_COOKIEFILE, dirname( __FILE__ )."/tmp/cookie_".$params[$this->paramCURRENTSESSION].".txt" );
		}
		
		if( isset( $params[$this->paramPOST] ) && $params[$this->paramPOST] ) {
			@curl_setopt( $ch, CURLOPT_POST, 1 );
			@curl_setopt( $ch, CURLOPT_POSTFIELDS, $params[$this->paramPOST] );
		}
		
		if( isset( $params[$this->paramHEADERS] ) && $params[$this->paramHEADERS] ) {
			@curl_setopt( $ch, CURLOPT_HTTPHEADER, $params[$this->paramHEADERS] );
		}
		
		if( isset( $params[$this->paramREFERER] ) && $params[$this->paramREFERER] ) {
			@curl_setopt( $ch, CURLOPT_REFERER, $params[$this->paramREFERER] );
		}
		
		$params[$this->paramRESULT] = @curl_exec( $ch );
		
		if( $params[$this->paramRESULT] === false ) {
			$this->lastError = @curl_error( $ch );
			return $this->errorNOEXEC;
		}
		
		@curl_close( $ch );
		
		return $params;
	}
	
	function processError( $error )
	{
		switch( $error ) {
			case $this->errorNOCURL:
				return "<font color=red>CURL не установлен! Обратитесь к администратору</font>";
			case $this->errorNOEXEC:
				return "<font color=red>".$this->lastError."</font>";
			case $this->errorNOINIT:
				return $this->lastError ? "<font color=red>".$this->lastError."</font>" : "<font color=red>Ошибка инициализации CURL</font>";
			case $this->errorNOPARAMS:
				return "<font color=red>Параметры не установлены</font>";
			case $this->errorNOURL:
				return "<font color=red>Не установлен URL для перехода</font>";
			default:
				return "<font color=red>Неизвестная ошибка</font>";
		}
	}
}

?>
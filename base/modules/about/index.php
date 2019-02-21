<?php

if( !defined( "in_ochki" ) && !defined( "in_ochki_admin" ) ) die( "You can't access this file directly" );

//
// Очень важно, чтобы объявление класса, у примеру "class moduleabout extends RootModule" было именно такое, какое есть сейчас. 
// Меняется только слово about в случае указанного примера. Также четко следите за регистром.
// Касаемо регистра. В модулях должны совпадать: название каталога, название в классе и название в БД (к примеру везде about или Projects или SuBwAy и тд.)
// 

class moduleabout extends RootModule
{
	var $dbinfo = null;
	var $curFilePath = "";
	
	function init( $dbinfo )
	{
		$this->dbinfo = $dbinfo;
		$this->curFilePath = $this->getRootFilePath( __FILE__ );
	}
}

?>
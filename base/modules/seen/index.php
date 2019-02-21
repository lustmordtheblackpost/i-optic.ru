<?php

if( !defined( "in_ochki" ) && !defined( "in_ochki_admin" ) ) die( "You can't access this file directly" );

//
// Очень важно, чтобы объявление класса, у примеру "class moduleabout extends RootModule" было именно такое, какое есть сейчас. 
// Меняется только слово about в случае указанного примера. Также четко следите за регистром.
// Касаемо регистра. В модулях должны совпадать: название каталога, название в классе и название в БД (к примеру везде about или Projects или SuBwAy и тд.)
// 

class moduleseen extends RootModule
{
	var $dbinfo = null;
	var $curFilePath = "";
	
	var $gl_dbase_string = "`shop`.";
	
	function init( $dbinfo )
	{
		$this->dbinfo = $dbinfo;
		$this->curFilePath = $this->getRootFilePath( __FILE__ );
	}
	
	function isinSmallMenu() { return true; }
	
	function getWidget()
	{
		global $main, $mysql, $lang, $query, $utils;
		
		if( $this->isSelected() )
			return "";
		
		$curUser = $main->users->auth ? $main->users->userArray['id'] : 0;
		$curSid = $main->users->sid;
		
		$a = $mysql->mqm( "SELECT * FROM `".$mysql->t_prefix."seen` WHERE (`sid`='".$curSid."'".( $curUser ? " OR `user`=".$curUser : "" ).") ORDER BY `date` DESC LIMIT 3" );
		$count = @mysql_num_rows( $a );
		if( !$count )
			return "";
		
		$t = "
		<div class='widget_blocks' id='seen_widget'><div class='inner_widget seen_widget'>
			<h3>".$this->getName().":</h3>
		";
		
		$currencyTypes = $main->listings->getListingElementsArray( 21, 0, false, '', true );
		$sides = $main->listings->getListingForSelecting( 29, 0, 0, "", "", true, '', true );
		$vendors = $main->listings->getListingElementsArray( 1, 0, false, '', true );
		$catalog = $main->modules->gmi( "catalog" );
		
		while( $r = @mysql_fetch_assoc( $a ) ) {
			$itemData = $catalog->getItem( $r['good'] );
			
			$properties = array();
			$aa = $mysql->mqm( "SELECT * FROM ".$this->gl_dbase_string."`".$mysql->t_prefix."tovar_property` WHERE `tovar_id`=".$r['good']." ORDER BY `id` ASC" );
			while( $rr = @mysql_fetch_assoc( $aa ) )
				$properties[$rr['id']] = $rr;
				
			$itemData['properties'] = $properties;
			
			$t .= $catalog->showGoodElement( $r['good'], $itemData, $itemData['sub_r'] ? $itemData['sub_r'] : $itemData['r'], $itemData['vendor'], array(), "", true, $vendors, $currencyTypes, "Просмотрено: ".$utils->getFullDate( $r['date'], true ) );
		}
		
		return $t."<div class='clear'></div>
		<div class='buttonString'>
			<input type=button onclick=\"urlmove('https://".$_SERVER['HTTP_HOST'].$mysql->settings['local_folder'].$this->dbinfo['local']."');\" value='Открыть полный список' style='width: 100%; margin-top: 10px;' /> 
		</div>
		</div></div>";
	}
	
	function getContent()
	{
		global $lang, $main, $mysql, $query, $utils;
		
		$main->templates->setTitle( $this->getName(), true );
		
		$curUser = $main->users->auth ? $main->users->userArray['id'] : 0;
		$curSid = $main->users->sid;
		
		$a = $mysql->mqm( "SELECT * FROM `".$mysql->t_prefix."seen` WHERE (`sid`='".$curSid."'".( $curUser ? " OR `user`=".$curUser : "" ).") ORDER BY `date` DESC" );
		$count = @mysql_num_rows( $a );
		if( !$count )
			return "<h2>Вы еще не просматривали товары</h2>";
		
		$t = "<h1 class='pageTitle fullyOwned'>".$this->getName()."</h1>";
		
		$currencyTypes = $main->listings->getListingElementsArray( 21, 0, false, '', true );
		$vendors = $main->listings->getListingElementsArray( 1, 0, false, '', true );
		$catalog = $main->modules->gmi( "catalog" );
		
		while( $r = @mysql_fetch_assoc( $a ) ) {
			$itemData = $catalog->getItem( $r['good'] );
			
			$properties = array();
			$aa = $mysql->mqm( "SELECT * FROM ".$this->gl_dbase_string."`".$mysql->t_prefix."tovar_property` WHERE `tovar_id`=".$r['good']." ORDER BY `id` ASC" );
			while( $rr = @mysql_fetch_assoc( $aa ) )
				$properties[$rr['id']] = $rr;
				
			$itemData['properties'] = $properties;
			
			$t .= $catalog->showGoodElement( $r['good'], $itemData, $itemData['sub_r'] ? $itemData['sub_r'] : $itemData['r'], $itemData['vendor'], array(), "", true, $vendors, $currencyTypes, "Просмотрено: ".$utils->getFullDate( $r['date'], true ) );
		}
		
		return $t;
	}
}

?>
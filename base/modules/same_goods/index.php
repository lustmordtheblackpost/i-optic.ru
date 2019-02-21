<?php

if( !defined( "in_ochki" ) && !defined( "in_ochki_admin" ) ) die( "You can't access this file directly" );

//
// Очень важно, чтобы объявление класса, у примеру "class moduleabout extends RootModule" было именно такое, какое есть сейчас. 
// Меняется только слово about в случае указанного примера. Также четко следите за регистром.
// Касаемо регистра. В модулях должны совпадать: название каталога, название в классе и название в БД (к примеру везде about или Projects или SuBwAy и тд.)
// 

class modulesame_goods extends RootModule
{
	var $dbinfo = null;
	var $curFilePath = "";
	
	var $gl_dbase_string = "`shop`.";
	
	var $reference = array(
		210 => array( 56, 15 ),
		211 => array( 18 ),
		212 => array( 7 ),
		215 => array( 226 => array( 31, 33 ), 227 => array( 40, 44 ), 228 => array( 24, 25 ) ),
		229 => array( 7 )
	);
	
	function init( $dbinfo )
	{
		$this->dbinfo = $dbinfo;
		$this->curFilePath = $this->getRootFilePath( __FILE__ );
	}
	
	function getGoods( $data, $items_source = null )
	{
		global $main, $mysql, $lang, $query, $utils;
		
		$id = $data['id'];
		$props = $main->properties->getPropertiesOfGood( $id );
		
		$catalog = $main->modules->gmi( "catalog" );
		$items = !$items_source ? $this->getItems( $data['r'], $data['sub_r'], $id, $data ) : $items_source;
		if( !count( $items ) )
			return "";
		
		$maxonpage = $this->getParam( "maxonpage" );
		
		$currencyTypes = $main->listings->getListingElementsArray( 21, 0, false, '', true );
		$sides = $main->listings->getListingForSelecting( 29, 0, 0, "", "", true, '', true );
		$vendors = $main->listings->getListingElementsArray( 1, 0, false, '', true );
		$itemsText = "";
		$i = 0;
		$c = 0;
		$ex = array();
		$exx = "";
		foreach( $items as $number => $itemData ) {
			
			$cont = true;
			$con_val = 0;
			if( isset( $this->reference[$data['r']] ) ) {
			  foreach( $this->reference[$data['r']] as $elemNumber => $elem ) {
				if( is_array( $elem ) && $data['sub_r'] ) {
					if( $elemNumber != $itemData['sub_r'] )
						continue;
					foreach( $elem as $k => $value ) {
						$first = $this->getElementByData( $props, "prop_id", $value );
						$second = $this->getElementByData( $itemData['properties'], "prop_id", $value );
						$exx .= "<div>".$value.": ".$first['value']." - ".$second['value']."</div>";
						if( $first['value'] != $second['value'] ) {
							$cont = false;
							break;
						}
						$con_val++;
					}
					if( !$cont )
						break;
				} else {
					$first = $this->getElementByData( $props, "prop_id", $elem );
					$second = $this->getElementByData( $itemData['properties'], "prop_id", $elem );
					$exx .= "<div>".$elem.": ".$first['value']." - ".$second['value']."</div>";
					if( $first['value'] != $second['value'] ) {
						$cont = false;
						break;
					}
					$con_val++;
				}
			  }
			}
			if( !$cont && !$items_source ) {
				if( $con_val > 0 )
					$ex[$number] = $itemData;
				continue;
			}
			
			$itemsText .= $catalog->showGoodElement( $itemData['id'], $itemData, $itemData['sub_r'] ? $itemData['sub_r'] : $itemData['r'], $itemData['vendor'], array(), "", true, null, $currencyTypes );
			$c++;
			if( $c >= $maxonpage )
				break;
		}		
			
		if( !$items_source && !$itemsText && count( $ex ) )
			return $this->getGoods( $data, $ex );
			
		return $itemsText;
	}
	
	function getItems( $r, $sub_r, $id, $data )
	{
		global $main, $mysql, $lang, $query, $utils;
		
		$where = "`view`=1 AND `r`=".$r." AND `sub_r`=".$sub_r." AND `id`<>".$id.( $r == 341 && mb_stripos( $data['name'], "набор", 0, "UTF-8" ) !== false ? " AND `name` LIKE '%набор%'" : " AND `name` NOT LIKE '%набор%'" );

		$a = $mysql->mqm( "SELECT * FROM ".$this->gl_dbase_string."`".$mysql->t_prefix."tovar` WHERE ".$where." ORDER BY RAND()" );
		$elems = array();
		while( $r = @mysql_fetch_assoc( $a ) ) {
			$properties = array();
			$aa = $mysql->mqm( "SELECT * FROM ".$this->gl_dbase_string."`".$mysql->t_prefix."tovar_property` WHERE `tovar_id`=".$r['id']." ORDER BY `id` ASC" );
			while( $rr = @mysql_fetch_assoc( $aa ) )
				$properties[$rr['id']] = $rr;
				
			$r['properties'] = $properties;
				
			@array_push( $elems, $r );
		}
		
		return $elems;
	}
}

?>